@extends('layouts.admin')

@section('title', 'Review Complaint #' . $report->report_number . ' - SolarOps')
@section('header_title', 'Report Verification & Approval')

@section('admin_content')
<div class="space-y-6" x-data="{ showCorrectionModal: false, showRejectModal: false }">
    <!-- Header Review Action Bar -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-bold bg-slate-900 text-amber-400 px-3 py-1 rounded-lg">
                        {{ $report->report_number }}
                    </span>
                    <span class="bg-rose-100 text-rose-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-rose-200">
                        Complaint Attending Sheet
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                    @if($report->service)
                        <span class="text-xs text-slate-500 font-medium">Job: #{{ $report->service->service_number }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $report->customer?->name ?? 'Service Customer' }} &bull; {{ $report->site?->name ?? 'Plant Site' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Attending Engineer: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.reports.print', $report->id) }}" target="_blank"
                   class="px-3.5 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printable Sheet</span>
                </a>

                @if(!$report->isApproved())
                    <button type="button" @click="showCorrectionModal = true"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Request Correction</span>
                    </button>

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this complaint resolution report? This marks the ticket resolved.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Resolution</span>
                        </button>
                    </form>

                    <button type="button" @click="showRejectModal = true"
                            class="px-3 py-2 text-rose-600 hover:bg-rose-50 font-bold text-xs rounded-xl transition-all">
                        Reject
                    </button>
                @else
                    <span class="px-3 py-2 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Approved by {{ $report->reviewer?->name ?? 'Admin' }} ({{ $report->approved_at?->format('d M Y') }})</span>
                    </span>
                @endif
            </div>
        </div>

        @if($report->correction_notes)
            <div class="mt-4 p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                <span class="font-bold block uppercase tracking-wider text-[10px] text-amber-700">Latest Correction Request Notes</span>
                <p class="mt-0.5 font-medium">{{ $report->correction_notes }}</p>
            </div>
        @endif
    </div>

    <!-- Section Data Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Customer & Plant Configuration -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Plant Configuration</span>
                <span class="text-rose-700 font-mono text-[11px]">{{ $sections['plant_details']['plant_capacity'] ?? '—' }}</span>
            </h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Modules Specifications</span> {{ $sections['plant_details']['modules_detailed'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">System Configuration</span> {{ $sections['plant_details']['system_configuration'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Battery Bank Detailed</span> {{ $sections['plant_details']['batteries_detailed'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Other Components</span> {{ $sections['plant_details']['others_detailed'] ?? '—' }}</div>
                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-600">
                    Address: {{ $sections['plant_details']['customer_address'] ?? '—' }}
                </div>
            </div>
        </div>

        <!-- 2. Complaint Intake Details -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. Complaint Intake Record</span>
                <span class="text-xs font-semibold text-slate-500">{{ $sections['complaint_intake']['complaint_date'] ?? '—' }}</span>
            </h3>
            <div class="bg-rose-50 p-3 rounded-xl border border-rose-200 text-xs text-rose-950 mb-3">
                <span class="text-rose-700 block text-[10px] uppercase font-bold">Primary Problem Reported</span>
                <p class="font-bold text-sm mt-0.5">{{ $sections['complaint_intake']['primary_complaint'] ?? '—' }}</p>
                <p class="text-[11px] text-rose-800 mt-1">Received from: <strong>{{ $sections['complaint_intake']['received_from'] ?? '—' }}</strong></p>
            </div>

            <div class="space-y-1.5 text-xs">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Reported Points</span>
                <ul class="space-y-1 list-disc list-inside text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    @for($cp=1; $cp<=3; $cp++)
                        @if(!empty($sections['complaint_intake']['complaint_point_' . $cp]))
                            <li>{{ $sections['complaint_intake']['complaint_point_' . $cp] }}</li>
                        @endif
                    @endfor
                </ul>
            </div>
        </div>

        <!-- 3. Work Carried Out & Spares Replaced -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>3. Attended Work & Corrective Actions</span>
                <span class="text-xs font-semibold text-slate-500">Service Engineering</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <h4 class="font-bold text-slate-900 uppercase text-[10px] mb-2">Actions Performed on Site</h4>
                    <ul class="space-y-1.5 list-disc list-inside bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700">
                        @for($a=1; $a<=5; $a++)
                            @if(!empty($sections['attended_work']['action_' . $a]))
                                <li><strong>#{{ $a }}:</strong> {{ $sections['attended_work']['action_' . $a] }}</li>
                            @endif
                        @endfor
                    </ul>
                </div>

                <div class="space-y-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Spares / Components Replaced</span>
                        <p class="text-slate-800 font-medium mt-0.5">{{ !empty($sections['attended_work']['spares_replaced']) ? $sections['attended_work']['spares_replaced'] : 'None (Rectified on site without replacement)' }}</p>
                    </div>

                    @if(!empty($sections['attended_work']['attended_remarks']))
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Attending Remarks:</span>
                            <p class="text-slate-800 mt-0.5">{{ $sections['attended_work']['attended_remarks'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. 9-Point Plant Health Checklist -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>4. 9-Point Plant Post-Attending Check</span>
                <span class="text-xs font-semibold text-slate-500">Quality Checklist</span>
            </h3>

            <div class="space-y-1.5 text-xs">
                @php
                    $checkLabels = [
                        1 => 'All modules clean, undamaged, and mounting tight',
                        2 => 'Array connections & MC4 connectors secure',
                        3 => 'AJB / DCDB fuses, SPD & isolator operational',
                        4 => 'Cables properly dressed and protected against weather',
                        5 => 'Earthing pits watered and resistance verified',
                        6 => 'Lightning arrester connections secure',
                        7 => 'Inverter LCD display normal with zero active error codes',
                        8 => 'Battery terminals clean, greased, and torqued',
                        9 => 'Overall generation output verified against sunlight levels',
                    ];
                @endphp
                @for($i=1; $i<=9; $i++)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-800 text-[11px] font-medium">{{ $i }}. {{ $checkLabels[$i] }}</span>
                        @php $statusVal = $sections['plant_checklist_9point']['check_' . $i] ?? '—'; @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $statusVal === 'Yes' || $statusVal === 'OK' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ $statusVal }}
                        </span>
                    </div>
                @endfor
            </div>

            @if(!empty($sections['plant_checklist_9point']['checklist_remarks']))
                <div class="mt-2 text-xs bg-slate-50 p-2 rounded-lg border border-slate-200 text-slate-700">
                    <span class="font-bold text-[10px] uppercase text-slate-400">Checklist Remarks:</span> {{ $sections['plant_checklist_9point']['checklist_remarks'] }}
                </div>
            @endif
        </div>

        <!-- 5. Handover & Sign-Off -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>5. Handover & Client Verification</span>
                <span class="text-xs font-semibold text-slate-500">Work Acceptance</span>
            </h3>

            <div class="space-y-2 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Whom Met at Site</span> <strong class="text-slate-900">{{ $sections['handover_signoff']['whom_met'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Whom Demonstrated Operation</span> <strong class="text-slate-900">{{ $sections['handover_signoff']['whom_shown'] ?? '—' }}</strong></div>
            </div>

            @if(!empty($sections['handover_signoff']['followup_1']))
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs mb-3">
                    <span class="font-bold uppercase text-[10px] block text-slate-500">Recommended Follow-Ups</span>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-slate-700">
                        @for($f=1; $f<=3; $f++)
                            @if(!empty($sections['handover_signoff']['followup_' . $f]))
                                <li>{{ $sections['handover_signoff']['followup_' . $f] }}</li>
                            @endif
                        @endfor
                    </ul>
                </div>
            @endif

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <span class="font-bold text-slate-900 block uppercase text-[10px]">Work Acceptance Confirmation</span>
                @if(!empty($sections['handover_signoff']['client_confirmed']))
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Client Rectification Verified & Operational
                    </span>
                @endif

                @if(!empty($sections['handover_signoff']['client_signature']))
                    <div class="pt-2 border-t border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Client Digital Signature</span>
                        <div class="bg-white p-2 rounded-lg border border-slate-200 inline-block">
                            <img src="{{ $sections['handover_signoff']['client_signature'] }}" alt="Client Signature" class="max-h-16 object-contain">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Photographs Gallery -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Complaint Attending Photographic Evidence ({{ $report->photos->count() }})</span>
            <span class="text-xs text-slate-400 font-normal">Job: #{{ $report->service?->service_number ?? '—' }}</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @forelse($report->photos as $photo)
                <div class="group bg-slate-50 rounded-xl border border-slate-200 overflow-hidden shadow-xs hover:border-amber-400 transition-all">
                    <a href="{{ $photo->url }}" target="_blank" class="block relative">
                        <img src="{{ $photo->url }}" alt="{{ $photo->original_filename }}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-200">
                    </a>
                    <div class="p-2.5 text-xs">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-800 inline-block mb-1">
                            {{ str_replace('_', ' ', $photo->section_key) }}
                        </span>
                        <p class="font-medium text-slate-700 truncate text-[11px]">{{ $photo->original_filename }}</p>
                        <p class="text-[10px] text-slate-400">{{ $photo->captured_at ? $photo->captured_at->format('d M Y, h:i A') : '' }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-6 text-center text-slate-400 text-xs">
                    No inspection photographs uploaded.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modals for Correction and Reject -->
    <div x-show="showCorrectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Request Complaint Resolution Corrections</h3>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="correction_notes" rows="4" required placeholder="Specify what notes, tests, or actions need correction..."
                          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCorrectionModal = false" class="px-4 py-2 border rounded-xl text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 font-bold text-xs rounded-xl text-slate-950">Dispatch Request</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-rose-700">Reject Report</h3>
            <form action="{{ route('admin.reports.reject', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="rejection_reason" rows="3" required placeholder="Reason for rejection..."
                          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-rose-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 border rounded-xl text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 font-bold text-xs rounded-xl text-white">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
