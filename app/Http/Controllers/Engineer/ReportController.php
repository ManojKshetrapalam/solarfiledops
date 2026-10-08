<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportData;
use App\Models\ReportDocument;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Report::with(['company', 'customer', 'site', 'service', 'template'])
            ->where('engineer_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('template_slug')) {
            $query->whereHas('template', fn($q) => $q->where('slug', $request->template_slug));
        }

        $reports = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('engineer.reports.index', compact('reports'));
    }

    public function dailyReportsIndex(Request $request): View
    {
        $user = auth()->user();
        $template = \App\Models\ReportTemplate::where('slug', 'daily_work_report')->first();

        $query = Report::with(['company', 'customer', 'site', 'service', 'template'])
            ->where('engineer_id', $user->id)
            ->where('template_id', $template?->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest('created_at')->paginate(10)->withQueryString();

        return view('engineer.daily_reports.index', compact('reports'));
    }

    public function createDailyReport(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $template = \App\Models\ReportTemplate::where('slug', 'daily_work_report')->first();
        $companyId = $user->company_id ?? \App\Models\Company::first()?->id;

        $count = Report::where('company_id', $companyId)->count() + 1;
        $companyCode = $user->company?->code ?? 'SOL';
        $reportNumber = sprintf('%s-DWR-%04d', strtoupper($companyCode), $count);
        while (Report::where('report_number', $reportNumber)->exists()) {
            $count++;
            $reportNumber = sprintf('%s-DWR-%04d', strtoupper($companyCode), $count);
        }

        $report = Report::create([
            'report_number' => $reportNumber,
            'service_id' => null,
            'template_id' => $template?->id,
            'company_id' => $companyId,
            'customer_id' => null,
            'site_id' => null,
            'engineer_id' => $user->id,
            'status' => 'draft',
            'current_step' => 1,
        ]);

        ReportData::create([
            'report_id' => $report->id,
            'section_key' => 'shift_details',
            'data_json' => [
                'employee_name' => $user->name,
                'designation' => $user->designation ?? 'Solar Field Operations',
                'report_date' => now()->format('Y-m-d'),
                'work_started_time' => '09:00',
                'work_stopped_time' => '18:00',
                'place_of_work' => '',
            ],
        ]);

        AuditLog::log($report, 'draft_created', "Daily work report draft started by {$user->name}");

        return redirect()->route('engineer.reports.edit', $report->id);
    }

    public function edit(Report $report): View|RedirectResponse
    {
        if ($report->engineer_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if (!$report->isEditableByEngineer()) {
            return redirect()->route('engineer.reports.show', $report->id)
                ->with('info', 'This report is submitted or approved and cannot be edited.');
        }

        $report->load(['service.serviceType', 'company', 'customer', 'site', 'sections', 'photos', 'documents', 'template']);

        // Format sections into a convenient associative array
        $sections = $this->formatSections($report);

        $templateSlug = $report->template?->slug ?? 'service_report';
        $viewName = view()->exists("engineer.reports.templates.{$templateSlug}")
            ? "engineer.reports.templates.{$templateSlug}"
            : "engineer.reports.edit";

        return view($viewName, compact('report', 'sections'));
    }

    public function show(Report $report): View
    {
        if ($report->engineer_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $report->load(['service.serviceType', 'company', 'customer', 'site', 'sections', 'photos', 'documents', 'reviewer', 'template']);

        $sections = $this->formatSections($report);

        $templateSlug = $report->template?->slug ?? 'service_report';
        $viewName = view()->exists("engineer.reports.templates.{$templateSlug}_show")
            ? "engineer.reports.templates.{$templateSlug}_show"
            : "engineer.reports.show";

        return view($viewName, compact('report', 'sections'));
    }

    public function saveDraft(Request $request, Report $report): JsonResponse|RedirectResponse
    {
        if (!$report->isEditableByEngineer()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Report is locked.'], 403);
            }
            return back()->with('error', 'Report is locked.');
        }

        if ($request->filled('current_step')) {
            $report->update(['current_step' => (int) $request->current_step]);
        }

        // Save each section sent in payload
        if ($request->has('sections') && is_array($request->sections)) {
            foreach ($request->sections as $sectionKey => $data) {
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $data = $decoded;
                    }
                }

                ReportData::updateOrCreate(
                    [
                        'report_id' => $report->id,
                        'section_key' => $sectionKey,
                    ],
                    [
                        'data_json' => $data,
                    ]
                );
            }
        }

        AuditLog::log($report, 'draft_saved', "Draft saved at Step {$report->current_step} by {$report->engineer->name}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully.',
                'saved_at' => now()->format('h:i:s A'),
                'current_step' => $report->current_step,
            ]);
        }

        return back()->with('success', 'Draft saved successfully.');
    }

    public function uploadPhoto(Request $request, Report $report): JsonResponse
    {
        if (!$report->isEditableByEngineer()) {
            return response()->json(['success' => false, 'message' => 'Report is locked.'], 403);
        }

        $request->validate([
            'photo' => ['required', 'image', 'max:10240'], // up to 10MB
            'section_key' => ['required', 'string'],
            'photo_type' => ['nullable', 'string'],
            'caption' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $file = $request->file('photo');
        $path = $file->store("reports/{$report->id}/photos", 'public');

        $photo = ReportPhoto::create([
            'report_id' => $report->id,
            'section_key' => $request->section_key,
            'photo_type' => $request->photo_type ?? 'general',
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'caption' => $request->caption,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'captured_at' => now(),
            'uploaded_by_id' => auth()->id(),
        ]);

        AuditLog::log($report, 'photo_uploaded', "Uploaded photo '{$photo->original_filename}' in section '{$photo->section_key}'");

        return response()->json([
            'success' => true,
            'photo' => [
                'id' => $photo->id,
                'section_key' => $photo->section_key,
                'photo_type' => $photo->photo_type,
                'url' => $photo->url,
                'filename' => $photo->original_filename,
                'captured_at' => $photo->captured_at->format('d M Y, h:i A'),
                'caption' => $photo->caption,
            ],
        ]);
    }

    public function deletePhoto(ReportPhoto $photo): JsonResponse|RedirectResponse
    {
        $report = $photo->report;
        if (!$report->isEditableByEngineer()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Report is locked.'], 403);
            }
            return back()->with('error', 'Report is locked.');
        }

        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $photo->delete();

        AuditLog::log($report, 'photo_deleted', "Photo deleted by {$report->engineer->name}");

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Photo deleted.']);
        }

        return back()->with('success', 'Photo removed.');
    }

    public function uploadDocument(Request $request, Report $report): JsonResponse|RedirectResponse
    {
        if (!$report->isEditableByEngineer()) {
            return back()->with('error', 'Report is locked.');
        }

        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'document_type' => ['nullable', 'string'],
        ]);

        $file = $request->file('document');
        $path = $file->store("reports/{$report->id}/documents", 'public');

        $doc = ReportDocument::create([
            'report_id' => $report->id,
            'document_type' => $request->document_type ?? 'Site Document',
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by_id' => auth()->id(),
        ]);

        AuditLog::log($report, 'document_uploaded', "Uploaded document '{$doc->original_filename}'");

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function submit(Request $request, Report $report): RedirectResponse
    {
        if (!$report->isEditableByEngineer()) {
            return redirect()->route('engineer.reports.show', $report->id)
                ->with('error', 'This report cannot be submitted in its current state.');
        }

        // Save any final changes from the form
        if ($request->has('sections') && is_array($request->sections)) {
            foreach ($request->sections as $sectionKey => $data) {
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $data = $decoded;
                    }
                }

                ReportData::updateOrCreate(
                    [
                        'report_id' => $report->id,
                        'section_key' => $sectionKey,
                    ],
                    [
                        'data_json' => $data,
                    ]
                );
            }
        }

        $isResubmission = ($report->status === 'correction_required');
        $newStatus = $isResubmission ? 'resubmitted' : 'submitted';

        $report->update([
            'status' => $newStatus,
            'current_step' => (int) ($request->current_step ?? $report->current_step ?? 6),
            'submitted_at' => now(),
        ]);

        $report->service?->update([
            'status' => 'report_submitted',
        ]);

        // Notify Admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        $templateName = $report->template?->name ?? 'Service Report';
        $title = $isResubmission ? "{$templateName} Resubmitted" : "New {$templateName} Submitted";
        $customerInfo = $report->customer ? "for {$report->customer->name}" : '';
        $message = "Engineer {$report->engineer->name} has " . ($isResubmission ? 'resubmitted corrected' : 'submitted') . " {$templateName} #{$report->report_number} ({$report->company->name}) {$customerInfo}.";

        foreach ($admins as $adm) {
            Notification::create([
                'user_id' => $adm->id,
                'title' => $title,
                'message' => $message,
                'type' => $isResubmission ? 'report_resubmitted' : 'report_submitted',
                'action_url' => route('admin.reports.show', $report->id),
            ]);
        }

        AuditLog::log($report, $newStatus, "Report #{$report->report_number} ({$templateName}) {$newStatus} by {$report->engineer->name}");

        return redirect()->route('engineer.reports.show', $report->id)
            ->with('success', 'Report submitted successfully! The admin operations team has been notified for verification.');
    }

    protected function formatSections(Report $report): array
    {
        $sections = [];
        foreach ($report->sections as $sec) {
            $val = $sec->data_json;
            if (is_string($val)) {
                $val = json_decode($val, true) ?? [];
            }
            $sections[$sec->section_key] = is_array($val) ? $val : [];
        }

        $templateSlug = $report->template?->slug ?? 'service_report';
        $knownSections = match ($templateSlug) {
            'installation_structure' => ['site_plant_info', 'panels_delivery', 'structure_installation', 'module_mounting', 'safety_checklist'],
            'installation_electrical' => ['site_plant_info', 'earthing_work', 'ajb_work', 'cabling_work', 'dcdb_acdb_work'],
            'installation_commissioning' => ['site_plant_info', 'pcu_installation', 'battery_installation', 'commissioning_testing', 'handover_signoff'],
            'site_inspection' => ['customer_site_details', 'power_req_meters', 'cabling_conduits', 'earthing_rooms_protection', 'rooftop_logistics'],
            'complaint_attending' => ['plant_details', 'complaint_intake', 'attended_work', 'plant_checklist_9point', 'handover_signoff'],
            'daily_work_report' => ['shift_details', 'hourly_activity_log', 'meals_allowance', 'travel_conveyance', 'work_summary_signoff'],
            default => ['customer_details', 'system_details', 'module_inspection', 'structure_inspection', 'pcu_inspection', 'battery_inspection', 'complaint_details', 'remarks'],
        };

        foreach ($knownSections as $k) {
            if (!isset($sections[$k])) {
                $sections[$k] = [];
            }
        }

        return $sections;
    }
}
