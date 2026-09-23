<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Service;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $assignedCount = Service::where('assigned_user_id', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count();

        $needsCorrectionCount = Report::where('engineer_id', $user->id)
            ->where('status', 'correction_required')
            ->count();

        $completedCount = Service::where('assigned_user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $activeServices = Service::with(['company', 'customer', 'site', 'serviceType', 'report'])
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', ['assigned', 'in_progress', 'correction_required'])
            ->latest('scheduled_date')
            ->get();

        $correctionReports = Report::with(['service', 'customer', 'site', 'company'])
            ->where('engineer_id', $user->id)
            ->where('status', 'correction_required')
            ->latest()
            ->get();

        return view('engineer.dashboard', compact(
            'user',
            'assignedCount',
            'needsCorrectionCount',
            'completedCount',
            'activeServices',
            'correctionReports'
        ));
    }

    public function profile(): View
    {
        $user = auth()->user()->load('company');
        return view('engineer.profile', compact('user'));
    }
}
