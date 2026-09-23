@extends('layouts.admin')

@section('title', 'Reporting & Analytics - SolarOps')
@section('header_title', 'Operations Analytics & Reporting')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Multi-Entity Field Operations Intelligence</h2>
            <p class="text-xs text-slate-500">Filter, analyze and export field operations metrics across companies, engineers, customers, and time horizons.</p>
        </div>

        <!-- Quick Horizon Switcher -->
        <div class="flex items-center gap-1.5 bg-white p-1 rounded-xl border border-slate-200 shadow-xs text-xs font-bold">
            <a href="{{ route('admin.analytics', array_merge(request()->query(), ['period' => 'today'])) }}" 
               class="px-3 py-1.5 rounded-lg transition-colors {{ $period === 'today' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Today
            </a>
            <a href="{{ route('admin.analytics', array_merge(request()->query(), ['period' => 'week'])) }}" 
               class="px-3 py-1.5 rounded-lg transition-colors {{ $period === 'week' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                This Week
            </a>
            <a href="{{ route('admin.analytics', array_merge(request()->query(), ['period' => 'month'])) }}" 
               class="px-3 py-1.5 rounded-lg transition-colors {{ $period === 'month' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                This Month
            </a>
            <a href="{{ route('admin.analytics', array_merge(request()->query(), ['period' => 'all'])) }}" 
               class="px-3 py-1.5 rounded-lg transition-colors {{ $period === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                All Time
            </a>
        </div>
    </div>

    <!-- Multi-Dimensional Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.analytics') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
            <input type="hidden" name="period" value="{{ $period }}">

            <div>
                <label class="block font-bold text-slate-600 mb-1">Company / Entity</label>
                <select name="company_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Entities</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Field Engineer</label>
                <select name="assigned_user_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Engineers</option>
                    @foreach($engineers as $eng)
                        <option value="{{ $eng->id }}" {{ request('assigned_user_id') == $eng->id ? 'selected' : '' }}>{{ $eng->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Customer</label>
                <select name="customer_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Customers</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>{{ $cust->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Service Type</label>
                <select name="service_type_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Service Types</option>
                    @foreach($serviceTypes as $st)
                        <option value="{{ $st->id }}" {{ request('service_type_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="report_submitted" {{ request('status') === 'report_submitted' ? 'selected' : '' }}>Report Submitted</option>
                    <option value="correction_required" {{ request('status') === 'correction_required' ? 'selected' : '' }}>Correction Required</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-colors w-full">Apply Filters</button>
                <a href="{{ route('admin.analytics') }}" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Company-Wise Performance Table (Core Requirement: Section 19) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                Company-Wise Performance & Verification Metrics (Calculated Database Values)
            </h3>
            <span class="text-xs font-semibold text-amber-600 uppercase">{{ strtoupper($period) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Company / Entity</th>
                        <th class="py-3 px-4 text-center">Services Created</th>
                        <th class="py-3 px-4 text-center">Completed</th>
                        <th class="py-3 px-4 text-center">In Progress / Pending</th>
                        <th class="py-3 px-4 text-center">Reports Submitted</th>
                        <th class="py-3 px-4 text-center">Approved</th>
                        <th class="py-3 px-4 text-center">Correction Required</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($companyBreakdown as $b)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-amber-400 font-bold flex items-center justify-center text-xs">
                                        {{ $b['company']->code }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 text-sm block">{{ $b['company']->name }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $b['company']->contact_person ?? 'Corporate Office' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-900 text-sm">
                                {{ $b['services_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-emerald-700 text-sm">
                                {{ $b['completed_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-indigo-700 text-sm">
                                {{ $b['pending_services'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-blue-700 text-sm">
                                {{ $b['reports_submitted'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-emerald-700 text-sm">
                                {{ $b['approved_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-amber-600 text-sm">
                                {{ $b['correction_count'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No company breakdown data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Filtered Service Order Results Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                Detailed Operations Record ({{ $servicesList->total() }} matching services)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Job #</th>
                        <th class="py-3 px-4">Company</th>
                        <th class="py-3 px-4">Customer & Site</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Engineer</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Job Status</th>
                        <th class="py-3 px-4">Report Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($servicesList as $srv)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-bold font-mono text-slate-900">
                                {{ $srv->service_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border">
                                    {{ $srv->company->code }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-bold text-slate-800">{{ $srv->customer->name }}</p>
                                <p class="text-slate-400 text-[11px]">{{ $srv->site->name }}</p>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ $srv->serviceType->name }}
                            </td>
                            <td class="py-3 px-4">
                                {{ $srv->assignedEngineer?->name ?? 'Unassigned' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $srv->scheduled_date->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $srv->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($srv->report)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $srv->report->status_badge_class }}">
                                        {{ strtoupper(str_replace('_', ' ', $srv->report->status)) }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">No Report</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.services.show', $srv->id) }}" class="text-amber-600 hover:text-amber-700 font-bold">
                                    View &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">No operations found matching current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($servicesList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $servicesList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
