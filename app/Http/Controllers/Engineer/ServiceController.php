<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\ReportData;
use App\Models\ReportTemplate;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Service::with(['company', 'customer', 'site', 'serviceType', 'report'])
            ->where('assigned_user_id', $user->id);

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereIn('status', ['assigned', 'in_progress', 'correction_required']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $services = $query->latest('scheduled_date')->paginate(10)->withQueryString();

        return view('engineer.services.index', compact('services'));
    }

    public function show(Service $service): View|RedirectResponse
    {
        // Security check
        if ($service->assigned_user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You are not assigned to this service order.');
        }

        $service->load(['company', 'customer', 'site', 'serviceType', 'report.photos', 'assignments']);

        return view('engineer.services.show', compact('service'));
    }

    public function startService(Service $service): RedirectResponse
    {
        if ($service->assigned_user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if ($service->status === 'assigned') {
            $service->update(['status' => 'in_progress']);
            AuditLog::log($service, 'started', "Field work started by {$service->assignedEngineer->name}");
        }

        // Check if report already exists, or create new Draft report
        $report = $service->report;
        if (!$report) {
            $template = ReportTemplate::where('slug', 'service_report')->first();

            // Generate report number e.g. SOE-REP-0001
            $count = Report::where('company_id', $service->company_id)->count() + 1;
            $reportNumber = sprintf('%s-REP-%04d', strtoupper($service->company->code), $count);
            while (Report::where('report_number', $reportNumber)->exists()) {
                $count++;
                $reportNumber = sprintf('%s-REP-%04d', strtoupper($service->company->code), $count);
            }

            $report = Report::create([
                'report_number' => $reportNumber,
                'service_id' => $service->id,
                'template_id' => $template?->id,
                'company_id' => $service->company_id,
                'customer_id' => $service->customer_id,
                'site_id' => $service->site_id,
                'engineer_id' => auth()->id(),
                'status' => 'draft',
                'current_step' => 1,
            ]);

            // Seed initial Step 1 data
            ReportData::create([
                'report_id' => $report->id,
                'section_key' => 'customer_details',
                'data_json' => [
                    'customer_name' => $service->customer->name,
                    'customer_address' => $service->site->address,
                    'service_date' => now()->format('Y-m-d'),
                    'service_time' => now()->format('H:i'),
                    'phone_head' => $service->customer->phone,
                    'phone_incharge' => $service->site->phone ?? '',
                    'phone_maintenance' => '',
                    'phone_others' => '',
                ],
            ]);

            AuditLog::log($report, 'draft_created', "Initial report draft created by {$service->assignedEngineer->name}");
        }

        return redirect()->route('engineer.reports.edit', $report->id);
    }
}
