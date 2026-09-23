<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Service;
use App\Models\ServiceAssignment;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::with(['company', 'customer', 'site', 'serviceType', 'assignedEngineer', 'report']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('assigned_user_id')) {
            $query->where('assigned_user_id', $request->assigned_user_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('service_number', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('site', fn($sq) => $sq->where('name', 'like', "%{$s}%"));
            });
        }

        $services = $query->latest('scheduled_date')->paginate(15)->withQueryString();
        $companies = Company::where('is_active', true)->get();
        $engineers = User::where('role', 'engineer')->where('status', 'active')->get();

        return view('admin.services.index', compact('services', 'companies', 'engineers'));
    }

    public function create(Request $request): View
    {
        $companies = Company::where('is_active', true)->get();
        $serviceTypes = ServiceType::where('is_active', true)->get();
        $engineers = User::where('role', 'engineer')->where('status', 'active')->with('company')->get();

        // Pass customers and sites with their relations for reactive selection
        $customers = Customer::where('status', 'active')->with(['sites', 'company'])->get();

        $selectedCompanyId = $request->query('company_id');
        $selectedCustomerId = $request->query('customer_id');
        $selectedSiteId = $request->query('site_id');

        return view('admin.services.create', compact(
            'companies',
            'serviceTypes',
            'engineers',
            'customers',
            'selectedCompanyId',
            'selectedCustomerId',
            'selectedSiteId'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'site_id' => ['required', 'exists:sites,id'],
            'service_type_id' => ['required', 'exists:service_types,id'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'scheduled_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $company = Company::findOrFail($validated['company_id']);

        // Generate unique service number e.g. SOE-SRV-0001
        $count = Service::where('company_id', $company->id)->count() + 1;
        $serviceNumber = sprintf('%s-SRV-%04d', strtoupper($company->code), $count);
        while (Service::where('service_number', $serviceNumber)->exists()) {
            $count++;
            $serviceNumber = sprintf('%s-SRV-%04d', strtoupper($company->code), $count);
        }

        $status = !empty($validated['assigned_user_id']) ? 'assigned' : 'unassigned';

        $service = Service::create([
            'service_number' => $serviceNumber,
            'company_id' => $validated['company_id'],
            'customer_id' => $validated['customer_id'],
            'site_id' => $validated['site_id'],
            'service_type_id' => $validated['service_type_id'],
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'priority' => $validated['priority'],
            'scheduled_date' => $validated['scheduled_date'],
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'created_by_id' => auth()->id(),
        ]);

        AuditLog::log($service, 'created', "Service job {$service->service_number} created with status '{$status}'", null, $service->toArray());

        if (!empty($validated['assigned_user_id'])) {
            $engineer = User::findOrFail($validated['assigned_user_id']);
            ServiceAssignment::create([
                'service_id' => $service->id,
                'user_id' => $engineer->id,
                'assigned_by_id' => auth()->id(),
                'assigned_at' => now(),
                'status' => 'assigned',
                'notes' => 'Assigned upon creation.',
            ]);

            Notification::create([
                'user_id' => $engineer->id,
                'title' => 'New Service Assigned',
                'message' => "Service #{$service->service_number} at {$service->customer->name} ({$service->site->name}) has been assigned to you for {$service->scheduled_date->format('d M Y')}.",
                'type' => 'service_assigned',
                'action_url' => route('engineer.services.show', $service->id),
            ]);

            AuditLog::log($service, 'assigned', "Service {$service->service_number} assigned to {$engineer->name}");
        }

        return redirect()->route('admin.services.show', $service->id)
            ->with('success', "Service Job {$service->service_number} created successfully.");
    }

    public function show(Service $service): View
    {
        $service->load([
            'company',
            'customer',
            'site',
            'serviceType',
            'assignedEngineer',
            'creator',
            'assignments.engineer',
            'assignments.assignedBy',
            'report.reviewer',
            'report.photos',
        ]);

        $engineers = User::where('role', 'engineer')->where('status', 'active')->get();
        $auditLogs = AuditLog::where(function($q) use ($service) {
            $q->where('auditable_type', Service::class)->where('auditable_id', $service->id);
            if ($service->report) {
                $q->orWhere(fn($rq) => $rq->where('auditable_type', \App\Models\Report::class)->where('auditable_id', $service->report->id));
            }
        })->latest()->take(20)->get();

        return view('admin.services.show', compact('service', 'engineers', 'auditLogs'));
    }

    public function assign(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_user_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $engineer = User::findOrFail($validated['assigned_user_id']);
        $prevEngineerName = $service->assignedEngineer?->name ?? 'Unassigned';

        $service->update([
            'assigned_user_id' => $engineer->id,
            'status' => in_array($service->status, ['unassigned', 'cancelled']) ? 'assigned' : $service->status,
        ]);

        ServiceAssignment::create([
            'service_id' => $service->id,
            'user_id' => $engineer->id,
            'assigned_by_id' => auth()->id(),
            'assigned_at' => now(),
            'status' => 'assigned',
            'notes' => $validated['notes'] ?? 'Assigned by Admin.',
        ]);

        Notification::create([
            'user_id' => $engineer->id,
            'title' => 'New Service Assigned',
            'message' => "Service #{$service->service_number} at {$service->customer->name} ({$service->site->name}) has been assigned to you.",
            'type' => 'service_assigned',
            'action_url' => route('engineer.services.show', $service->id),
        ]);

        AuditLog::log($service, 'assigned', "Service assigned to {$engineer->name} (previously {$prevEngineerName})");

        return back()->with('success', "Service {$service->service_number} assigned to {$engineer->name}.");
    }

    public function updateStatus(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:unassigned,assigned,in_progress,report_submitted,correction_required,completed,cancelled'],
        ]);

        $oldStatus = $service->status;
        $service->update(['status' => $validated['status']]);

        AuditLog::log($service, 'status_updated', "Service status changed from {$oldStatus} to {$validated['status']}");

        return back()->with('success', "Service status updated to " . strtoupper(str_replace('_', ' ', $validated['status'])));
    }
}
