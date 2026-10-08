@extends('layouts.engineer')

@section('mobile_title', 'Daily Work Reports')

@section('engineer_content')
<div class="space-y-4">
    <!-- Header Card -->
    <div class="bg-slate-900 text-white p-4 rounded-2xl shadow-md border border-slate-800 flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold tracking-wider text-amber-400">Field Activity Log</span>
            <h2 class="text-base font-extrabold text-white leading-tight mt-0.5">Daily Timesheets & Travel</h2>
            <p class="text-xs text-slate-300 font-medium mt-0.5">Track your hourly tasks, conveyance, and meal allowances.</p>
        </div>
        <a href="{{ route('engineer.daily-reports.create') }}" 
           class="px-3.5 py-2 bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-sm flex items-center gap-1.5 shrink-0 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Log Today</span>
        </a>
    </div>

    <!-- Reports List -->
    <div class="space-y-3">
        @forelse($reports as $r)
            @php
                $shift = $r->getSectionData('shift_details');
                $travel = $r->getSectionData('travel_conveyance');
                $summary = $r->getSectionData('work_summary_signoff');
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">
                        {{ $r->report_number }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $r->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $r->status)) }}
                    </span>
                </div>

                <div class="space-y-1 mb-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Log Date:</span>
                        <strong class="text-slate-900">{{ $shift['report_date'] ?? $r->created_at->format('d M Y') }}</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Working Hours:</span>
                        <span class="text-slate-800 font-semibold">{{ $shift['work_started_time'] ?? '09:00' }} - {{ $shift['work_stopped_time'] ?? '18:00' }}</span>
                    </div>
                    @if(!empty($travel['diff_km']))
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Travel:</span>
                            <span class="text-amber-700 font-bold font-mono">{{ $travel['diff_km'] }} KM (Rs. {{ $travel['amount'] ?? '0' }})</span>
                        </div>
                    @endif
                    @if(!empty($summary['work_completed']))
                        <p class="text-slate-600 line-clamp-2 mt-1 text-[11px] bg-slate-50 p-2 rounded-lg border border-slate-100">
                            {{ $summary['work_completed'] }}
                        </p>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">
                        {{ $r->submitted_at ? 'Submitted ' . $r->submitted_at->format('d M, h:i A') : 'Draft in progress' }}
                    </span>

                    @if($r->isEditableByEngineer())
                        <a href="{{ route('engineer.reports.edit', $r->id) }}" 
                           class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                            <span>Edit / Submit</span>
                            <span>&rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('engineer.reports.show', $r->id) }}" 
                           class="px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 transition-all">
                            View Summary
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900">No Daily Work Reports Logged</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                    Record your daily field tasks, site activities, meal allowances, and travel conveyance.
                </p>
                <a href="{{ route('engineer.daily-reports.create') }}" 
                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-slate-800">
                    <span>Log First Timesheet</span>
                    <span>&rarr;</span>
                </a>
            </div>
        @endforelse
    </div>

    @if($reports->hasPages())
        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection
