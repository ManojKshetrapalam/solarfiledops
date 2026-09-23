@extends('layouts.engineer')

@section('mobile_title', 'My Field Dashboard')

@section('engineer_content')
<div class="space-y-4">
    <!-- Welcome Card -->
    <div class="bg-slate-900 text-white p-4 rounded-2xl shadow-md border border-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-amber-400">Welcome Back</span>
                <h2 class="text-lg font-extrabold text-white leading-tight mt-0.5">{{ $user->name }}</h2>
                <p class="text-xs text-slate-300 font-medium mt-0.5">
                    {{ $user->designation ?? 'Solar Field Engineer' }} &bull; <span class="font-mono text-amber-300">{{ $user->employee_code }}</span>
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-black text-sm">
                {{ substr($user->name, 0, 2) }}
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-slate-800 text-center text-xs">
            <div class="bg-slate-800/80 p-2 rounded-xl">
                <span class="text-[10px] text-slate-400 block font-semibold">Active Jobs</span>
                <span class="text-lg font-bold text-amber-400">{{ $assignedCount }}</span>
            </div>
            <div class="bg-slate-800/80 p-2 rounded-xl">
                <span class="text-[10px] text-slate-400 block font-semibold">Corrections</span>
                <span class="text-lg font-bold text-rose-400">{{ $needsCorrectionCount }}</span>
            </div>
            <div class="bg-slate-800/80 p-2 rounded-xl">
                <span class="text-[10px] text-slate-400 block font-semibold">Completed</span>
                <span class="text-lg font-bold text-emerald-400">{{ $completedCount }}</span>
            </div>
        </div>
    </div>

    <!-- Urgent Correction Warning Banner -->
    @if($correctionReports->count() > 0)
        @foreach($correctionReports as $cr)
            <div class="bg-amber-500 text-slate-950 p-4 rounded-2xl shadow-md flex items-start gap-3 border-2 border-amber-600 animate-pulse">
                <div class="w-8 h-8 rounded-full bg-slate-950 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-900 block">Correction Requested by Admin</span>
                    <p class="text-xs font-bold text-slate-950 mt-0.5">
                        Report #{{ $cr->report_number }} &bull; {{ $cr->customer->name }}
                    </p>
                    <p class="text-[11px] text-slate-900 mt-1 bg-amber-400/80 p-2 rounded-lg font-medium">
                        "{{ $cr->correction_notes }}"
                    </p>
                    <a href="{{ route('engineer.reports.edit', $cr->id) }}" 
                       class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-950 text-white rounded-lg font-bold text-xs shadow-xs hover:bg-slate-900">
                        <span>Edit & Resubmit Report</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Active Assigned Work Orders -->
    <div>
        <div class="flex items-center justify-between mb-2 px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">My Assigned Work Orders</h3>
            <a href="{{ route('engineer.services.index') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">View All &rarr;</a>
        </div>

        <div class="space-y-3">
            @forelse($activeServices as $srv)
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $srv->service_number }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $srv->priority_badge_class }}">
                                {{ ucfirst($srv->priority) }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $srv->status_badge_class }}">
                                {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                            </span>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-slate-900 leading-tight">
                        {{ $srv->customer->name }}
                    </h4>
                    <p class="text-xs text-slate-500 font-medium flex items-center gap-1 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span class="truncate">{{ $srv->site->name }} ({{ $srv->site->address }})</span>
                    </p>

                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">
                            Date: <strong class="text-slate-700">{{ $srv->scheduled_date->format('d M Y') }}</strong>
                        </span>

                        <a href="{{ route('engineer.services.show', $srv->id) }}" 
                           class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1 transition-all">
                            <span>Open Job</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                    No active service jobs currently assigned.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
