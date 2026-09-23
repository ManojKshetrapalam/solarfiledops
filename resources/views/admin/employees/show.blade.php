@extends('layouts.admin')

@section('title', $employee->name . ' - Employee Profile - SolarOps')
@section('header_title', 'Employee Profile')

@section('admin_content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-amber-400 font-extrabold text-xl flex items-center justify-center border-2 border-slate-800 shadow-md">
                    {{ substr($employee->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $employee->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $employee->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        {{ $employee->designation ?? 'Field Operations' }} &bull; 
                        <span class="font-mono font-bold text-slate-700">ID: {{ $employee->employee_code }}</span>
                    </p>
                    <p class="text-xs text-amber-600 font-semibold mt-1">
                        Entity: {{ $employee->company?->name ?? 'All Entities (Global)' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.employees.edit', $employee->id) }}" 
                   class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all">
                    Edit Profile
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Email</span>
                <span class="font-bold text-slate-800">{{ $employee->email }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Phone</span>
                <span class="font-bold text-slate-800">{{ $employee->phone ?? '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Joining Date</span>
                <span class="font-bold text-slate-800">{{ $employee->joining_date?->format('d M Y') ?? '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Total Assigned Jobs</span>
                <span class="font-bold text-indigo-700">{{ $employee->assignedServices->count() }} Services</span>
            </div>
        </div>
    </div>

    <!-- Assigned Services & Submitted Reports Tabs/Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assigned Services -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Assigned Services ({{ $employee->assignedServices->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($employee->assignedServices as $srv)
                    <div class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-slate-900">{{ $srv->service_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $srv->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                                </span>
                            </div>
                            <p class="text-slate-600 font-medium">{{ $srv->customer->name }} &bull; {{ $srv->site->name }}</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">Scheduled: {{ $srv->scheduled_date->format('d M Y') }}</p>
                        </div>
                        <a href="{{ route('admin.services.show', $srv->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold shrink-0">
                            View
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">No services assigned yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Submitted Reports -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Submitted Reports ({{ $employee->reports->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($employee->reports as $rep)
                    <div class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-slate-900">{{ $rep->report_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $rep->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $rep->status)) }}
                                </span>
                            </div>
                            <p class="text-slate-600 font-medium">{{ $rep->customer->name }} &bull; {{ $rep->site->name }}</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">Updated: {{ $rep->updated_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('admin.reports.show', $rep->id) }}" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold shrink-0">
                            Review
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">No reports submitted yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
