<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DataImport;
use App\Models\Report;
use App\Models\Service;
use App\Models\Site;
use App\Models\User;
use App\Services\DataMigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataManagementController extends Controller
{
    public function __construct(
        protected DataMigrationService $migrationService
    ) {}

    public function index(): View
    {
        $configs = $this->migrationService->getEntityConfigs();

        // Calculate counts for each entity
        $counts = [
            'companies' => Company::count(),
            'employees' => User::where('role', 'engineer')->count(),
            'customers' => Customer::count(),
            'sites' => Site::count(),
            'installations' => Service::whereHas('serviceType', fn($q) => $q->where('report_template_slug', 'like', 'installation_%'))->count(),
            'services' => Service::whereHas('serviceType', fn($q) => $q->where('report_template_slug', 'service_report'))->count(),
            'complaints' => Service::whereHas('serviceType', fn($q) => $q->where('report_template_slug', 'complaint_attending'))->count(),
            'daily_work_reports' => Report::whereHas('template', fn($q) => $q->where('slug', 'daily_work_report'))->count(),
            'site_inspections' => Report::whereHas('template', fn($q) => $q->where('slug', 'site_inspection'))->count(),
            'feedback' => Report::whereHas('template', fn($q) => $q->where('slug', 'customer_feedback'))->count(),
        ];

        // Last import info
        $lastImport = DataImport::latest()->first();

        return view('admin.data_management.index', compact('configs', 'counts', 'lastImport'));
    }

    public function templates(): View
    {
        $configs = $this->migrationService->getEntityConfigs();
        return view('admin.data_management.templates', compact('configs'));
    }

    public function downloadTemplate(string $type): Response
    {
        $configs = $this->migrationService->getEntityConfigs();
        if (!isset($configs[$type])) {
            abort(404, "Invalid template type");
        }

        $spreadsheet = $this->migrationService->generateTemplate($type);
        $fileName = $configs[$type]['file_name'];

        $tempPath = storage_path("app/temp_" . uniqid() . ".xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $content = file_get_contents($tempPath);
        unlink($tempPath);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function downloadAllTemplates(): BinaryFileResponse
    {
        $zipPath = $this->migrationService->generateAllTemplatesZip();

        return response()->download($zipPath, 'SolarOps_Data_Migration_Templates.zip')
            ->deleteFileAfterSend(true);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'import_type' => ['required', 'string'],
            'excel_file' => ['required', 'file', 'max:10240'], // 10MB
        ]);

        $file = $request->file('excel_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'xlsx') {
            return back()->withErrors(['excel_file' => 'Only .xlsx files are allowed.'])->withInput();
        }

        $type = $request->input('import_type');
        $previewData = $this->migrationService->validateAndPreview($file, $type);

        if (!$previewData['success']) {
            return back()->withErrors(['excel_file' => $previewData['error']])->withInput();
        }

        // Save preview state in session
        $previewKey = 'migration_preview_' . uniqid();
        session([
            $previewKey => [
                'type' => $type,
                'file_name' => $file->getClientOriginalName(),
                'preview' => $previewData,
            ]
        ]);

        return view('admin.data_management.preview', compact('previewData', 'previewKey'));
    }

    public function confirm(Request $request): View|RedirectResponse
    {
        $previewKey = $request->input('preview_key');
        $sessionData = session($previewKey);

        if (!$sessionData) {
            return redirect()->route('admin.data-management.index')
                ->with('error', 'Import session expired. Please re-upload your Excel file.');
        }

        $type = $sessionData['type'];
        $fileName = $sessionData['file_name'];
        $rows = $sessionData['preview']['rows'];

        $result = $this->migrationService->executeImport(
            $type,
            $rows,
            auth()->id(),
            $fileName
        );

        if (!$result['success']) {
            return redirect()->route('admin.data-management.index')
                ->with('error', $result['error'] ?? 'Import failed due to database error.');
        }

        // Clear preview session
        session()->forget($previewKey);

        // Store result credentials in session for download
        session(['last_import_credentials' => $result['credentials']]);

        return view('admin.data_management.completed', [
            'type' => $type,
            'fileName' => $fileName,
            'result' => $result,
            'credentials' => $result['credentials'],
        ]);
    }

    public function downloadErrorReport(Request $request, ?DataImport $import = null): Response|RedirectResponse
    {
        $previewKey = $request->input('preview_key');
        $sessionData = $previewKey ? session($previewKey) : null;

        if ($sessionData) {
            $type = $sessionData['type'];
            $rows = $sessionData['preview']['rows'];
        } elseif ($import && !empty($import->error_log)) {
            $type = $import->import_type;
            $rows = $import->error_log;
        } else {
            return back()->with('error', 'Error report session expired or not found.');
        }

        $filePath = $this->migrationService->generateErrorReport($type, $rows);
        $content = file_get_contents($filePath);
        unlink($filePath);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$type}_Import_Errors.xlsx\"",
        ]);
    }

    public function downloadCredentialSheet(Request $request, ?DataImport $import = null): Response|RedirectResponse
    {
        $credentials = session('last_import_credentials');
        if (empty($credentials) && $import && $import->import_type === 'employees') {
            $userIds = $import->summary_data['created_user_ids'] ?? [];
            if (!empty($userIds)) {
                $users = User::whereIn('id', $userIds)->where('password_change_required', true)->get();
                $credentials = [];
                foreach ($users as $u) {
                    $plain = $u->getDecryptedTemporaryPassword();
                    if ($plain) {
                        $credentials[] = [
                            'name' => $u->name,
                            'employee_code' => $u->employee_code,
                            'username' => $u->username,
                            'email' => $u->email,
                            'phone' => $u->phone,
                            'temporary_password' => $plain,
                        ];
                    }
                }
            }
        }

        if (empty($credentials)) {
            return back()->with('error', 'No temporary credentials available for download (credentials may have already been consumed or expired).');
        }

        $filePath = $this->migrationService->generateCredentialSheet($credentials, auth()->id());
        $content = file_get_contents($filePath);
        unlink($filePath);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"SolarOps_Employee_Credentials_" . date('Y-m-d') . ".xlsx\"",
        ]);
    }

    public function history(): View
    {
        $imports = DataImport::with('user')->latest()->paginate(15);

        return view('admin.data_management.history', compact('imports'));
    }

    public function showHistory(DataImport $import): View
    {
        $import->load('user');

        return view('admin.data_management.show', compact('import'));
    }

    public function auditCopyCredential(Request $request): JsonResponse
    {
        $employeeId = $request->input('employee_id');
        $employee = User::find($employeeId);

        AuditLog::create([
            'auditable_type' => User::class,
            'auditable_id' => $employee?->id ?? auth()->id(),
            'user_id' => auth()->id(),
            'event' => 'EMPLOYEE_CREDENTIAL_COPIED',
            'description' => "Admin copied login credentials for employee " . ($employee?->name ?? 'bulk batch'),
            'ip_address' => request()->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function revealCredential(User $employee): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $plainPassword = $employee->getDecryptedTemporaryPassword();

        AuditLog::create([
            'auditable_type' => User::class,
            'auditable_id' => $employee->id,
            'user_id' => auth()->id(),
            'event' => 'EMPLOYEE_CREDENTIAL_VIEWED',
            'description' => "Admin viewed temporary password for employee {$employee->name} ({$employee->employee_code})",
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'success' => true,
            'password' => $plainPassword ?? '—',
            'is_consumed' => !$employee->password_change_required,
        ]);
    }
}
