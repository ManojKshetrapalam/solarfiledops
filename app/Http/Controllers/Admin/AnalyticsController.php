<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Report;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'all'); // 'today', 'week', 'month', 'custom', 'all'
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Base service query with filters
        $serviceQuery = Service::with(['company', 'customer', 'site', 'serviceType', 'assignedEngineer', 'report']);
        $reportQuery = Report::with(['company', 'customer', 'site', 'engineer']);

        // Date range filtering
        if ($period === 'today') {
            $serviceQuery->whereDate('scheduled_date', Carbon::today());
            $reportQuery->whereDate('created_at', Carbon::today());
        } elseif ($period === 'week') {
            $serviceQuery->whereBetween('scheduled_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $reportQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $serviceQuery->whereMonth('scheduled_date', Carbon::now()->month)->whereYear('scheduled_date', Carbon::now()->year);
            $reportQuery->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $serviceQuery->whereBetween('scheduled_date', [$dateFrom, $dateTo]);
            $reportQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        // Entity / Master filters
        if ($request->filled('company_id')) {
            $serviceQuery->where('company_id', $request->company_id);
            $reportQuery->where('company_id', $request->company_id);
        }

        if ($request->filled('assigned_user_id')) {
            $serviceQuery->where('assigned_user_id', $request->assigned_user_id);
            $reportQuery->where('engineer_id', $request->assigned_user_id);
        }

        if ($request->filled('customer_id')) {
            $serviceQuery->where('customer_id', $request->customer_id);
            $reportQuery->where('customer_id', $request->customer_id);
        }

        if ($request->filled('site_id')) {
            $serviceQuery->where('site_id', $request->site_id);
            $reportQuery->where('site_id', $request->site_id);
        }

        if ($request->filled('service_type_id')) {
            $serviceQuery->where('service_type_id', $request->service_type_id);
        }

        if ($request->filled('status')) {
            $serviceQuery->where('status', $request->status);
        }

        $servicesList = (clone $serviceQuery)->latest('scheduled_date')->paginate(15)->withQueryString();

        // High-level KPI aggregations
        $kpi = [
            'total_services' => (clone $serviceQuery)->count(),
            'completed_services' => (clone $serviceQuery)->where('status', 'completed')->count(),
            'active_services' => (clone $serviceQuery)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'unassigned_services' => (clone $serviceQuery)->where('status', 'unassigned')->count(),
            'total_reports' => (clone $reportQuery)->count(),
            'approved_reports' => (clone $reportQuery)->where('status', 'approved')->count(),
            'pending_review' => (clone $reportQuery)->whereIn('status', ['submitted', 'resubmitted'])->count(),
            'correction_required' => (clone $reportQuery)->where('status', 'correction_required')->count(),
        ];

        // Company-wise calculated table (Section 19 requirements)
        $companyBreakdown = Company::where('is_active', true)->get()->map(function($comp) use ($period, $dateFrom, $dateTo) {
            $sq = Service::where('company_id', $comp->id);
            $rq = Report::where('company_id', $comp->id);

            if ($period === 'today') {
                $sq->whereDate('scheduled_date', Carbon::today());
                $rq->whereDate('created_at', Carbon::today());
            } elseif ($period === 'week') {
                $sq->whereBetween('scheduled_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $rq->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($period === 'month') {
                $sq->whereMonth('scheduled_date', Carbon::now()->month)->whereYear('scheduled_date', Carbon::now()->year);
                $rq->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            } elseif ($period === 'custom' && $dateFrom && $dateTo) {
                $sq->whereBetween('scheduled_date', [$dateFrom, $dateTo]);
                $rq->whereBetween('created_at', [$dateFrom, $dateTo]);
            }

            return [
                'company' => $comp,
                'services_count' => (clone $sq)->count(),
                'completed_count' => (clone $sq)->where('status', 'completed')->count(),
                'pending_services' => (clone $sq)->whereIn('status', ['unassigned', 'assigned', 'in_progress'])->count(),
                'reports_count' => (clone $rq)->count(),
                'reports_submitted' => (clone $rq)->whereIn('status', ['submitted', 'resubmitted'])->count(),
                'approved_count' => (clone $rq)->where('status', 'approved')->count(),
                'correction_count' => (clone $rq)->where('status', 'correction_required')->count(),
            ];
        });

        // Filter dropdown options
        $companies = Company::where('is_active', true)->get();
        $engineers = User::where('role', 'engineer')->get();
        $customers = Customer::where('status', 'active')->get();
        $sites = Site::all();
        $serviceTypes = ServiceType::where('is_active', true)->get();

        return view('admin.analytics.index', compact(
            'servicesList',
            'kpi',
            'companyBreakdown',
            'companies',
            'engineers',
            'customers',
            'sites',
            'serviceTypes',
            'period',
            'dateFrom',
            'dateTo'
        ));
    }
}
