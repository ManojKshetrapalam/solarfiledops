@extends('layouts.admin')

@section('title', 'Service #' . $service->service_number . ' - SolarOps')
@section('header_title', 'Service Job Details')

@section('admin_content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono font-bold bg-slate-900 text-amber-400 px-3 py-1 rounded-lg">
                        {{ $service->service_number }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $service->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $service->status)) }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $service->priority_badge_class }}">
                        {{ ucfirst($service->priority) }} Priority
                    </span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $service->serviceType->name }} &bull; {{ $service->customer->name }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Plant Site: <strong class="text-slate-800">{{ $service->site->name }}</strong> ({{ $service->site->address }})
                </p>
            </div>

            <!-- Direct Actions -->
            <div class="flex items-center gap-2">
                @if($service->report)
                    <a href="{{ route('admin.reports.show', $service->report->id) }}" 
                       class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Review Report ({{ ucfirst($service->report->status) }})</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Corporate Entity</span>
                <span class="font-bold text-slate-800">{{ $service->company->name }} ({{ $service->company->code }})</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Scheduled Date</span>
                <span class="font-bold text-slate-800">{{ $service->scheduled_date->format('d M Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Customer Contact</span>
                <span class="font-bold text-slate-800">{{ $service->customer->contact_person }} ({{ $service->customer->phone }})</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Site Incharge</span>
                <span class="font-bold text-slate-800">{{ $service->site->contact_person ?? '—' }} ({{ $service->site->phone ?? '—' }})</span>
            </div>
        </div>

        @if($service->description)
            <div class="mt-4 pt-4 border-t border-slate-100 text-xs">
                <span class="text-slate-400 block font-semibold uppercase text-[10px] mb-1">Field Instructions</span>
                <p class="text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-200">{{ $service->description }}</p>
            </div>
        @endif
    </div>

    <!-- Assignment Management Card -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Service Assignment</h3>
            <p class="text-xs text-slate-500 mb-4">Assign or reassign this job to a certified solar field engineer.</p>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 mb-4 text-xs">
                <span class="text-slate-400 block uppercase font-semibold text-[10px]">Currently Assigned To</span>
                @if($service->assignedEngineer)
                    <div class="flex items-center gap-3 mt-2">
                        <div class="w-10 h-10 rounded-full bg-slate-900 text-slate-200 flex items-center justify-center font-bold text-xs">
                            {{ substr($service->assignedEngineer->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-900">{{ $service->assignedEngineer->name }}</p>
                            <p class="text-slate-500">{{ $service->assignedEngineer->designation }} &bull; {{ $service->assignedEngineer->phone }}</p>
                            <p class="text-[11px] text-amber-600 font-medium">Entity: {{ $service->assignedEngineer->company?->name ?? 'Global' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-amber-700 font-bold mt-1">No field engineer assigned yet.</p>
                @endif
            </div>

            <!-- Assignment Form -->
            <form action="{{ route('admin.services.assign', $service->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Select Field Engineer</label>
                    <select name="assigned_user_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="">-- Choose Engineer --</option>
                        @foreach($engineers as $eng)
                            <option value="{{ $eng->id }}" {{ $service->assigned_user_id == $eng->id ? 'selected' : '' }}>
                                {{ $eng->name }} ({{ $eng->employee_code }}) - {{ $eng->company?->name ?? 'All Entities' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Assignment Note / Remark</label>
                    <input type="text" name="notes" placeholder="Special equipment requirements, priority instructions..."
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-xs transition-colors">
                    {{ $service->assigned_user_id ? 'Reassign Engineer' : 'Assign Engineer' }}
                </button>
            </form>
        </div>

        <!-- Attached Service Report Status -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-1">Field Service Report</h3>
                <p class="text-xs text-slate-500 mb-4">Digital 10-step inspection and maintenance reporting status.</p>

                @if($service->report)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900">{{ $service->report->report_number }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $service->report->status_badge_class }}">
                                {{ strtoupper(str_replace('_', ' ', $service->report->status)) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-slate-600">
                            <div>Step Completed: <strong class="text-slate-900">{{ $service->report->current_step }} of 10</strong></div>
                            <div>Photos: <strong class="text-slate-900">{{ $service->report->photos->count() }} uploaded</strong></div>
                            <div>Submitted: <strong class="text-slate-900">{{ $service->report->submitted_at ? $service->report->submitted_at->format('d M Y, H:i') : 'Draft' }}</strong></div>
                            <div>Reviewed By: <strong class="text-slate-900">{{ $service->report->reviewer?->name ?? 'Pending' }}</strong></div>
                        </div>

                        @if($service->report->correction_notes)
                            <div class="mt-2 bg-amber-50 border border-amber-300 p-2.5 rounded-lg text-amber-900">
                                <span class="font-bold text-[11px] block">Admin Correction Remark:</span>
                                <p class="text-xs mt-0.5">{{ $service->report->correction_notes }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-xl">
                        The assigned engineer has not yet started filling the digital report for this service.
                    </div>
                @endif
            </div>

            @if($service->report)
                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-end">
                    <a href="{{ route('admin.reports.show', $service->report->id) }}" 
                       class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all">
                        Review & Approve Report &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Audit Log Timeline -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-3">Service & Report Audit History</h3>
        <div class="space-y-4 text-xs">
            @forelse($auditLogs as $log)
                <div class="flex items-start gap-3 border-l-2 border-slate-200 pl-4 py-1">
                    <div class="w-2 h-2 rounded-full bg-amber-500 -ml-[21px] mt-1 ring-4 ring-white"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800">{{ $log->description }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            By {{ $log->user?->name ?? 'System' }} &bull; {{ $log->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">No activity logged yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
