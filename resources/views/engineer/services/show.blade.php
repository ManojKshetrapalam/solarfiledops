@extends('layouts.engineer')

@section('mobile_title', 'Job #' . $service->service_number)

@section('engineer_content')
<div class="space-y-4">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-mono font-bold bg-slate-900 text-amber-400 px-3 py-1 rounded-lg">
                {{ $service->service_number }}
            </span>
            <div class="flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $service->priority_badge_class }}">
                    {{ ucfirst($service->priority) }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $service->status_badge_class }}">
                    {{ strtoupper(str_replace('_', ' ', $service->status)) }}
                </span>
            </div>
        </div>

        <h2 class="text-lg font-extrabold text-slate-900 leading-tight">
            {{ $service->serviceType->name }}
        </h2>
        <p class="text-xs text-amber-600 font-semibold mt-0.5">
            Entity: {{ $service->company->name }} ({{ $service->company->code }})
        </p>

        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold text-[10px] uppercase">Scheduled Date</span>
                <span class="font-bold text-slate-800 text-sm">{{ $service->scheduled_date->format('l, d F Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold text-[10px] uppercase">Customer & Billing Entity</span>
                <span class="font-bold text-slate-800">{{ $service->customer->name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold text-[10px] uppercase">Plant Site & Physical Address</span>
                <span class="font-bold text-slate-800">{{ $service->site->name }}</span>
                <p class="text-slate-600 text-xs mt-0.5">{{ $service->site->address }}</p>
                @if($service->site->location_notes)
                    <p class="text-amber-800 bg-amber-50 p-2 rounded-lg border border-amber-200 text-[11px] mt-1 font-medium">
                        Access: {{ $service->site->location_notes }}
                    </p>
                @endif
            </div>
        </div>

        @if($service->description)
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs">
                <span class="text-slate-400 block font-semibold text-[10px] uppercase mb-1">Field Instructions</span>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700 font-medium">
                    {{ $service->description }}
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Call Contacts Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">On-Site Contacts (Tap to Call)</h3>
        <div class="space-y-2 text-xs">
            @if($service->site->contact_person || $service->site->phone)
                <a href="tel:{{ $service->site->phone }}" 
                   class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-colors">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Site Incharge</span>
                        <span class="font-bold text-slate-900">{{ $service->site->contact_person ?? 'Plant Incharge' }}</span>
                        <span class="text-slate-500 block text-[11px]">{{ $service->site->phone ?? 'No phone' }}</span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </a>
            @endif

            @if($service->customer->phone)
                <a href="tel:{{ $service->customer->phone }}" 
                   class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition-colors">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Customer Head / Coordinator</span>
                        <span class="font-bold text-slate-900">{{ $service->customer->contact_person ?? $service->customer->name }}</span>
                        <span class="text-slate-500 block text-[11px]">{{ $service->customer->phone }}</span>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </a>
            @endif
        </div>
    </div>

    <!-- Main Action Button -->
    <div class="pt-2">
        @if($service->status === 'assigned')
            <form action="{{ route('engineer.services.start', $service->id) }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full py-4 px-6 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-sm rounded-2xl shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>START SERVICE & OPEN REPORT</span>
                </button>
            </form>
        @elseif($service->status === 'in_progress' && $service->report)
            <a href="{{ route('engineer.reports.edit', $service->report->id) }}" 
               class="w-full py-4 px-6 bg-slate-900 hover:bg-slate-800 text-white font-black text-sm rounded-2xl shadow-lg flex items-center justify-center gap-2 transition-all">
                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>CONTINUE REPORT (Step {{ $service->report->current_step }} of 10)</span>
            </a>
        @elseif($service->report && $service->report->status === 'correction_required')
            <a href="{{ route('engineer.reports.edit', $service->report->id) }}" 
               class="w-full py-4 px-6 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-sm rounded-2xl shadow-lg flex items-center justify-center gap-2 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>EDIT & RESUBMIT CORRECTION</span>
            </a>
        @elseif($service->report)
            <a href="{{ route('engineer.reports.show', $service->report->id) }}" 
               class="w-full py-4 px-6 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-sm rounded-2xl border border-slate-200 flex items-center justify-center gap-2 transition-all">
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>VIEW SUBMITTED REPORT</span>
            </a>
        @endif
    </div>
</div>
@endsection
