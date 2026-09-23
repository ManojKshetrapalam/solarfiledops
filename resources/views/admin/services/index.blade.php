@extends('layouts.admin')

@section('title', 'Service & Job Management - SolarOps')
@section('header_title', 'Field Services & Jobs')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Field Service Orders & Job Dispatch</h2>
            <p class="text-xs text-slate-500">Create, schedule and assign solar field operations to engineers across corporate entities.</p>
        </div>
        <a href="{{ route('admin.services.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Create New Service Job</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.services.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Job #, customer, site..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Company / Entity</label>
                <select name="company_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Companies</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="unassigned" {{ request('status') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="report_submitted" {{ request('status') === 'report_submitted' ? 'selected' : '' }}>Report Submitted</option>
                    <option value="correction_required" {{ request('status') === 'correction_required' ? 'selected' : '' }}>Correction Required</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Assigned Engineer</label>
                <select name="assigned_user_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Engineers</option>
                    @foreach($engineers as $eng)
                        <option value="{{ $eng->id }}" {{ request('assigned_user_id') == $eng->id ? 'selected' : '' }}>{{ $eng->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-colors w-full">Filter</button>
                <a href="{{ route('admin.services.index') }}" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Services Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Service Job</th>
                        <th class="py-3.5 px-4">Customer & Site</th>
                        <th class="py-3.5 px-4">Entity</th>
                        <th class="py-3.5 px-4">Assigned Engineer</th>
                        <th class="py-3.5 px-4">Scheduled Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($services as $srv)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.services.show', $srv->id) }}" class="font-bold text-slate-900 hover:text-amber-600 text-sm block">
                                    {{ $srv->service_number }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-slate-500 text-[11px]">{{ $srv->serviceType->name }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $srv->priority_badge_class }}">
                                        {{ ucfirst($srv->priority) }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-bold text-slate-800">{{ $srv->customer->name }}</p>
                                <p class="text-slate-500 text-[11px]">{{ $srv->site->name }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $srv->company->code }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($srv->assignedEngineer)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-800 text-slate-200 flex items-center justify-center font-bold text-[10px]">
                                            {{ substr($srv->assignedEngineer->name, 0, 2) }}
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $srv->assignedEngineer->name }}</span>
                                    </div>
                                @else
                                    <span class="text-amber-600 font-bold flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Unassigned
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $srv->scheduled_date->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $srv->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.services.show', $srv->id) }}" 
                                       class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors">
                                        Manage
                                    </a>
                                    @if($srv->report)
                                        <a href="{{ route('admin.reports.show', $srv->report->id) }}" 
                                           class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg transition-colors">
                                            Report
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No service jobs found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($services->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
