<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Report;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Global KPI Counters
        $totalEmployees = User::where('role', 'engineer')->where('status', 'active')->count();
        $totalActiveServices = Service::whereIn('status', ['assigned', 'in_progress', 'correction_required'])->count();
        $pendingAssignments = Service::where('status', 'unassigned')->count();
        $reportsSubmitted = Report::whereIn('status', ['submitted', 'resubmitted'])->count();
        $reportsPendingReview = Report::whereIn('status', ['submitted', 'resubmitted'])->count();
        $correctionRequired = Report::where('status', 'correction_required')->count();
        $approvedReports = Report::where('status', 'approved')->count();

        // Company-wise activity breakdown (Dynamic calculated DB values)
        $companies = Company::where('is_active', true)->withCount([
            'services as active_services_count' => fn($q) => $q->whereIn('status', ['assigned', 'in_progress', 'correction_required']),
            'services as completed_services_count' => fn($q) => $q->where('status', 'completed'),
            'reports as pending_reports_count' => fn($q) => $q->whereIn('status', ['submitted', 'resubmitted']),
            'reports as approved_reports_count' => fn($q) => $q->where('status', 'approved'),
            'reports as correction_reports_count' => fn($q) => $q->where('status', 'correction_required'),
        ])->get();

        // Recent services
        $recentServices = Service::with(['company', 'customer', 'site', 'assignedEngineer'])
            ->latest()
            ->take(6)
            ->get();

        // Reports requiring review
        $pendingReports = Report::with(['company', 'customer', 'site', 'engineer', 'service'])
            ->whereIn('status', ['submitted', 'resubmitted', 'correction_required'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalActiveServices',
            'pendingAssignments',
            'reportsSubmitted',
            'reportsPendingReview',
            'correctionRequired',
            'approvedReports',
            'companies',
            'recentServices',
            'pendingReports'
        ));
    }
}
