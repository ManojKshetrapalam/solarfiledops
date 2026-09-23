@extends('layouts.admin')

@section('title', 'Admin Operations Dashboard - SolarOps')
@section('header_title', 'Operations Overview')

@section('admin_content')
<div class="space-y-8">
    <!-- Top KPI Grid -->
    <div>
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">System-Wide Operational Metrics</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            <!-- Total Employees -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Field Engineers</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $totalEmployees }}</span>
                <span class="text-[11px] text-emerald-600 font-medium flex items-center gap-1 mt-1">Active staff</span>
            </div>

            <!-- Total Active Services -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Active Jobs</span>
                <span class="text-2xl font-extrabold text-indigo-600 mt-1 block">{{ $totalActiveServices }}</span>
                <span class="text-[11px] text-slate-500 font-medium mt-1 block">In progress</span>
            </div>

            <!-- Pending Assignments -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Unassigned</span>
                <span class="text-2xl font-extrabold text-amber-600 mt-1 block">{{ $pendingAssignments }}</span>
                <span class="text-[11px] text-amber-600 font-medium mt-1 block">Needs engineer</span>
            </div>

            <!-- Reports Submitted -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Submitted</span>
                <span class="text-2xl font-extrabold text-blue-600 mt-1 block">{{ $reportsSubmitted }}</span>
                <span class="text-[11px] text-blue-600 font-medium mt-1 block">Awaiting review</span>
            </div>

            <!-- Reports Pending Review -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Pending Review</span>
                <span class="text-2xl font-extrabold text-purple-600 mt-1 block">{{ $reportsPendingReview }}</span>
                <span class="text-[11px] text-purple-600 font-medium mt-1 block">Action required</span>
            </div>

            <!-- Correction Required -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Corrections</span>
                <span class="text-2xl font-extrabold text-rose-600 mt-1 block">{{ $correctionRequired }}</span>
                <span class="text-[11px] text-rose-600 font-medium mt-1 block">With engineer</span>
            </div>

            <!-- Approved Reports -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Approved</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">{{ $approvedReports }}</span>
                <span class="text-[11px] text-emerald-600 font-medium mt-1 block">Locked & signed</span>
            </div>
        </div>
    </div>

    <!-- Company-Wise Activity Breakdown -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Company / Entity Activity Breakdown</h2>
            <a href="{{ route('admin.companies.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">Manage Entities &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($companies as $company)
                <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 hover:border-slate-300 transition-all">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-900 text-amber-400 font-black flex items-center justify-center text-sm shadow-xs">
                                {{ $company->code }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-tight">{{ $company->name }}</h3>
                                <p class="text-xs text-slate-400">{{ $company->contact_person ?? 'Corporate Unit' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[11px]">Active Services</span>
                            <span class="text-lg font-bold text-indigo-700 mt-0.5 block">{{ $company->active_services_count }}</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[11px]">Completed</span>
                            <span class="text-lg font-bold text-emerald-700 mt-0.5 block">{{ $company->completed_services_count }}</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[11px]">Pending Reports</span>
                            <span class="text-lg font-bold text-blue-700 mt-0.5 block">{{ $company->pending_reports_count }}</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block text-[11px]">Approved Reports</span>
                            <span class="text-lg font-bold text-emerald-700 mt-0.5 block">{{ $company->approved_reports_count }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Corrections: <strong class="text-amber-600">{{ $company->correction_reports_count }}</strong></span>
                        <a href="{{ route('admin.analytics', ['company_id' => $company->id]) }}" class="font-semibold text-slate-700 hover:text-amber-600">View Full Stats &rarr;</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white p-8 rounded-xl border border-dashed border-slate-300 text-center text-slate-500">
                    No active company entities configured.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Two Column Section: Pending Reviews & Recent Services -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Reports Pending Review -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <h3 class="text-sm font-bold text-slate-900">Reports Requiring Review</h3>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">View All &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($pendingReports as $rep)
                    <div class="p-4 hover:bg-slate-50/70 transition-colors flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-xs text-slate-900">{{ $rep->report_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $rep->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $rep->status)) }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">({{ $rep->company->name }})</span>
                            </div>
                            <p class="text-xs text-slate-600 truncate font-medium">
                                {{ $rep->customer->name }} &bull; {{ $rep->site->name }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Engineer: <strong class="text-slate-700">{{ $rep->engineer->name }}</strong> &bull; 
                                {{ $rep->submitted_at ? $rep->submitted_at->diffForHumans() : $rep->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('admin.reports.show', $rep->id) }}" 
                           class="shrink-0 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-all">
                            Review
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        No reports currently pending review.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Services -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-sm font-bold text-slate-900">Recent Service Jobs</h3>
                </div>
                <a href="{{ route('admin.services.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">View All &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentServices as $srv)
                    <div class="p-4 hover:bg-slate-50/70 transition-colors flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-xs text-slate-900">{{ $srv->service_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $srv->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">({{ $srv->company->code }})</span>
                            </div>
                            <p class="text-xs text-slate-600 truncate font-medium">
                                {{ $srv->customer->name }} &bull; {{ $srv->site->name }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Assigned: <strong class="text-slate-700">{{ $srv->assignedEngineer?->name ?? 'Unassigned' }}</strong> &bull;
                                Date: {{ $srv->scheduled_date->format('d M Y') }}
                            </p>
                        </div>
                        <a href="{{ route('admin.services.show', $srv->id) }}" 
                           class="shrink-0 px-3 py-1.5 border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition-all">
                            Details
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        No service jobs created yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
