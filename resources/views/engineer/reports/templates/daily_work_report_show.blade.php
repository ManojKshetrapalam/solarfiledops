@extends('layouts.engineer')

@section('mobile_title', 'Timesheet: ' . $report->report_number)
@section('header_back_url', route('engineer.reports.index'))

@section('engineer_content')
<div class="space-y-4 pb-32 sm:pb-36">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-mono font-bold bg-slate-900 text-amber-400 px-3 py-1 rounded-lg">
                {{ $report->report_number }}
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                {{ strtoupper(str_replace('_', ' ', $report->status)) }}
            </span>
        </div>

        <h2 class="text-lg font-extrabold text-slate-900 leading-tight">
            Daily Worklog & Time Sheet
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
            Logged by <strong class="text-slate-800">{{ $report->engineer?->name }}</strong> ({{ $report->engineer?->employee_code }}) &bull; {{ $report->company?->name }}
        </p>

        @if($report->status === 'approved')
            <div class="mt-4 p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold block">Approved by Management</span>
                    <span class="text-[11px] text-emerald-700">Verified by {{ $report->reviewer?->name ?? 'Admin' }} on {{ $report->approved_at?->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        @elseif($report->status === 'correction_required')
            <div class="mt-4 p-3.5 bg-amber-500 text-slate-950 rounded-xl border border-amber-600 text-xs">
                <span class="font-bold block">Correction Requested:</span>
                <p class="mt-1 bg-amber-400 p-2 rounded-lg font-medium text-slate-950">"{{ $report->correction_notes }}"</p>
                <div class="mt-3">
                    <a href="{{ route('engineer.reports.edit', $report->id) }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-950 text-white rounded-lg font-bold text-xs shadow-xs">
                        <span>Edit & Resubmit Worklog</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @else
            <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-200 text-blue-900 text-xs">
                <span class="font-bold block">Submitted for Admin Approval</span>
                <span class="text-[11px] text-blue-700">Submitted on {{ $report->submitted_at?->format('d M Y, h:i A') ?? 'Draft' }}</span>
            </div>
        @endif
    </div>

    <!-- Section 1: Shift & Personnel Details -->
    @php
        $shift = $sections['shift_details'] ?? [];
        $hourly = $sections['hourly_activity_log'] ?? [];
        $meals = $sections['meals_allowance'] ?? [];
        $travel = $sections['travel_conveyance'] ?? [];
        $summary = $sections['work_summary_signoff'] ?? [];
    @endphp

    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2 flex items-center justify-between">
            <span>1. Shift & Personnel Details</span>
            <span class="text-amber-600 font-mono">{{ $shift['report_date'] ?? $report->created_at->format('Y-m-d') }}</span>
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Employee</span>
                <strong class="text-slate-900">{{ $shift['employee_name'] ?? $report->engineer?->name }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Designation</span>
                <span class="text-slate-800">{{ $shift['designation'] ?? $report->engineer?->designation ?? 'Field Engineer' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Shift Hours</span>
                <span class="text-slate-800 font-bold font-mono">{{ $shift['work_started_time'] ?? '09:00' }} - {{ $shift['work_stopped_time'] ?? '18:00' }}</span>
            </div>
            <div class="col-span-2 sm:col-span-3">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Place of Work / Sites</span>
                <span class="text-slate-800 font-medium">{{ $shift['place_of_work'] ?? 'General Operations' }}</span>
            </div>
        </div>
    </div>

    <!-- Section 2: Hourly Activity Log -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2">
            2. Hourly Activity Log
        </h3>
        @if(!empty($hourly['activities']) && is_array($hourly['activities']))
            <div class="divide-y divide-slate-100 text-xs">
                @foreach($hourly['activities'] as $act)
                    <div class="py-2.5 flex items-start gap-3">
                        <span class="px-2 py-0.5 rounded bg-slate-100 font-mono font-bold text-slate-700 text-[10px] shrink-0">
                            {{ $act['time_slot'] ?? ($act['from_time'] . ' - ' . $act['to_time']) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900">{{ $act['task_description'] ?? ($act['work_done'] ?? '—') }}</p>
                            @if(!empty($act['location']))
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $act['location'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-400 py-2">No hourly activities recorded.</p>
        @endif
    </div>

    <!-- Section 3: Meal & Food Allowance -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2 flex items-center justify-between">
            <span>3. Meals & Allowance</span>
            <span class="text-slate-900 font-black">₹{{ $meals['total_meals_amount'] ?? ($meals['amount'] ?? '0') }}</span>
        </h3>
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Breakfast / Tea</span>
                <span class="text-slate-800">{{ !empty($meals['breakfast_claimed']) ? 'Claimed (₹' . ($meals['breakfast_amount'] ?? '50') . ')' : 'Not claimed' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Lunch / Dinner</span>
                <span class="text-slate-800">{{ !empty($meals['lunch_claimed']) ? 'Claimed (₹' . ($meals['lunch_amount'] ?? '150') . ')' : 'Not claimed' }}</span>
            </div>
        </div>
    </div>

    <!-- Section 4: Travel & Conveyance -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2 flex items-center justify-between">
            <span>4. Travel & Conveyance</span>
            <span class="text-slate-900 font-black">₹{{ $travel['amount'] ?? '0' }}</span>
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Mode</span>
                <span class="text-slate-800 font-semibold">{{ ucfirst($travel['mode_of_travel'] ?? 'Two-Wheeler') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Start / End KM</span>
                <span class="font-mono text-slate-800">{{ $travel['start_km'] ?? '0' }} - {{ $travel['end_km'] ?? '0' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Distance</span>
                <strong class="text-amber-600 font-mono">{{ $travel['diff_km'] ?? '0' }} KM</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Claim Amount</span>
                <strong class="text-slate-900 font-mono">₹{{ $travel['amount'] ?? '0' }}</strong>
            </div>
        </div>
    </div>

    <!-- Section 5: Work Summary & Sign-off -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b border-slate-100 pb-2">
            5. Work Summary & Sign-Off
        </h3>
        <div class="space-y-2 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Work Done Today</span>
                <p class="text-slate-800 mt-0.5 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    {{ $summary['work_completed'] ?? ($summary['tasks_summary'] ?? 'Tasks successfully completed for the day.') }}
                </p>
            </div>
            @if(!empty($summary['pending_work']))
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Pending Tasks for Tomorrow</span>
                    <p class="text-slate-800 mt-0.5 leading-relaxed bg-amber-50/60 p-2.5 rounded-xl border border-amber-200/60 text-amber-950">
                        {{ $summary['pending_work'] }}
                    </p>
                </div>
            @endif
        </div>

        @if(!empty($summary['signature']))
            <div class="pt-3 border-t border-slate-100">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Employee Signature</span>
                <div class="bg-slate-50 rounded-lg p-2 border border-slate-200 inline-block max-w-full">
                    <img src="{{ $summary['signature'] }}" alt="Employee Digital Signature" class="max-h-16 object-contain bg-white rounded border border-slate-200 px-3 py-1">
                </div>
            </div>
        @endif
    </div>

    @if($report->isEditableByEngineer())
        <div class="pt-2">
            <a href="{{ route('engineer.reports.edit', $report->id) }}" 
               class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Continue Editing Timesheet</span>
            </a>
        </div>
    @endif
</div>
@endsection
