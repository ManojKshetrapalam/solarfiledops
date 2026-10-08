@extends('layouts.admin')

@section('title', 'Review Commissioning #' . $report->report_number . ' - SolarOps')
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
                    <span class="bg-purple-100 text-purple-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-purple-200">
                        Installation: PCU & Commissioning
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                    @if($report->service)
                        <span class="text-xs text-slate-500 font-medium">Job: #{{ $report->service->service_number }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $report->customer?->name ?? 'Installation Project' }} &bull; {{ $report->site?->name ?? 'Plant Site' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Commissioning Engineer: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
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

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this commissioning report? This marks the job completed.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Report</span>
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
        <!-- 1. Site & Plant Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Site & Job Information</span>
                <span class="text-purple-700 font-mono text-[11px]">{{ $sections['site_plant_info']['service_date'] ?? '—' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Plant Capacity</span> <strong class="text-slate-800">{{ $sections['site_plant_info']['plant_capacity'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Engineer Phone</span> <strong class="text-slate-800">{{ $sections['site_plant_info']['technician_phone'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Engineer Name</span> {{ $sections['site_plant_info']['technician_name'] ?? $report->engineer->name }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Site Address</span> {{ $sections['site_plant_info']['customer_address'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 2. PCU Inverter Parameters -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. Power Conditioning Unit (PCU)</span>
                <span class="text-xs font-semibold text-slate-500">{{ $sections['pcu_installation']['system_type'] ?? 'On-Grid' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Make & Model</span> <strong class="text-slate-900">{{ $sections['pcu_installation']['pcu_make_model'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Serial Number</span> <strong class="text-slate-900 font-mono">{{ $sections['pcu_installation']['pcu_serial_no'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Voltage Rating</span> {{ $sections['pcu_installation']['volts_rating'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Mounting Details</span> {{ $sections['pcu_installation']['mounting_details'] ?? '—' }}</div>
            </div>

            <div class="grid grid-cols-4 gap-2 text-center bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs">
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Solar DC Volts</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['pcu_installation']['solar_dc_volts'] ?? '—' }} V</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Solar DC Amps</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['pcu_installation']['solar_dc_current'] ?? '—' }} A</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Grid AC Volts</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['pcu_installation']['grid_ac_volts'] ?? '—' }} V</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Output Freq</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['pcu_installation']['output_freq'] ?? '—' }} Hz</strong>
                </div>
            </div>

            <div class="flex gap-4 mt-3 text-[11px] text-slate-700">
                <span class="inline-flex items-center gap-1 font-semibold {{ !empty($sections['pcu_installation']['ventilation_ok']) ? 'text-emerald-700' : 'text-slate-400' }}">
                    &bull; Proper Ventilation
                </span>
                <span class="inline-flex items-center gap-1 font-semibold {{ !empty($sections['pcu_installation']['rain_protection_ok']) ? 'text-emerald-700' : 'text-slate-400' }}">
                    &bull; Rain & Moisture Protected
                </span>
                <span class="inline-flex items-center gap-1 font-semibold {{ !empty($sections['pcu_installation']['temp_ok']) ? 'text-emerald-700' : 'text-slate-400' }}">
                    &bull; Ambient Temperature OK
                </span>
            </div>
            @if(!empty($sections['pcu_installation']['pcu_remarks']))
                <div class="mt-2 text-xs text-slate-600 bg-slate-50 p-2 rounded-lg">
                    <span class="font-bold text-[10px] uppercase text-slate-400">PCU Remarks:</span> {{ $sections['pcu_installation']['pcu_remarks'] }}
                </div>
            @endif
        </div>

        <!-- 3. Battery Bank Installation -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>3. Battery Bank Installation</span>
                <span class="text-xs font-semibold text-slate-500">{{ $sections['battery_installation']['battery_type'] ?? 'Tall Tubular' }}</span>
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Ah Rating</span> <strong class="text-slate-900">{{ $sections['battery_installation']['ah_rating'] ?? '—' }} Ah</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Volts Rating</span> <strong class="text-slate-900">{{ $sections['battery_installation']['volts_rating'] ?? '—' }} V</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Total Batteries</span> <strong class="text-slate-900 font-mono">{{ $sections['battery_installation']['no_of_batteries'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">No. of Banks</span> {{ $sections['battery_installation']['no_of_banks'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Maintenance Space</span> {{ $sections['battery_installation']['maintenance_space'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Inter-Battery Gap</span> {{ $sections['battery_installation']['gap_between_batteries'] ?? '—' }}</div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Battery Stand</span> {{ $sections['battery_installation']['battery_stand'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Battery Cabins</span> {{ $sections['battery_installation']['battery_cabins'] ?? '—' }}</div>
            </div>
            @if(!empty($sections['battery_installation']['remarks']))
                <div class="mt-2 text-xs text-slate-600 bg-slate-50 p-2 rounded-lg">
                    <span class="font-bold text-[10px] uppercase text-slate-400">Battery Remarks:</span> {{ $sections['battery_installation']['remarks'] }}
                </div>
            @endif
        </div>

        <!-- 4. 9-Point Commissioning Checklist & Handover -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>4. Commissioning Checklist & Acceptance</span>
                <span class="text-xs font-semibold text-slate-500">9-Point Test Matrix</span>
            </h3>

            <div class="space-y-1.5 text-xs">
                @php
                    $commLabels = [
                        1 => 'All cables connected properly and tagged',
                        2 => 'Cables crimped with appropriate lugs',
                        3 => 'Polarity checked for strings and battery bank',
                        4 => 'No loose contacts / all terminals torqued',
                        5 => 'Earthing connections tightened with spring washers',
                        6 => 'Earthing resistance within acceptable limits (< 5 &Omega;)',
                        7 => 'Inverter boots normally with zero fault alarms',
                        8 => 'Load transfer from grid to solar functioning smoothly',
                        9 => 'Emergency DC/AC isolators tested and operational',
                    ];
                @endphp
                @for($i=1; $i<=9; $i++)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-800 text-[11px] font-medium">{{ $i }}. {!! $commLabels[$i] !!}</span>
                        @php $statusVal = $sections['commissioning_testing']['check_' . $i] ?? '—'; @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $statusVal === 'Yes' || $statusVal === 'OK' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ $statusVal }}
                        </span>
                    </div>
                @endfor
            </div>

            @if(!empty($sections['commissioning_testing']['pending_1']))
                <div class="mt-3 bg-amber-50 p-2.5 rounded-lg border border-amber-200 text-xs text-amber-900">
                    <span class="font-bold uppercase text-[10px] block text-amber-700">Pending Actions</span>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @for($p=1; $p<=3; $p++)
                            @if(!empty($sections['commissioning_testing']['pending_' . $p]))
                                <li>{{ $sections['commissioning_testing']['pending_' . $p] }}</li>
                            @endif
                        @endfor
                    </ul>
                </div>
            @endif

            <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <span class="font-bold text-slate-900 block uppercase text-[10px]">Work Acceptance Confirmation</span>
                <div class="text-xs text-slate-700">
                    Client Representative: <strong class="text-slate-900">{{ $sections['handover_signoff']['whom_shown_name'] ?? '—' }}</strong>
                    @if(!empty($sections['handover_signoff']['whom_shown_phone']))
                        ({{ $sections['handover_signoff']['whom_shown_phone'] }})
                    @endif
                </div>

                @if(!empty($sections['handover_signoff']['client_confirmed']))
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Commissioning Inspected & Accepted
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
            <span>Commissioning & Inverter Photographs ({{ $report->photos->count() }})</span>
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
            <h3 class="text-base font-bold text-slate-900">Request Commissioning Corrections</h3>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="correction_notes" rows="4" required placeholder="Specify what needs correction on inverter or battery parameters..."
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
