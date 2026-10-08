@extends('layouts.admin')

@section('title', 'Review Site Inspection #' . $report->report_number . ' - SolarOps')
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
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-indigo-200">
                        Site Inspection Report
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                    @if($report->service)
                        <span class="text-xs text-slate-500 font-medium">Job: #{{ $report->service->service_number }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $report->customer?->name ?? 'Survey Project' }} &bull; {{ $report->site?->name ?? 'Proposed Site' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Survey Engineer: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
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

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this site inspection survey? This validates feasibility for design.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Survey</span>
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
        <!-- 1. Customer & Key Site Contacts -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Site Information & Contact Directory</span>
                <span class="text-indigo-700 font-mono text-[11px]">{{ $sections['customer_site_details']['site_condition'] ?? 'Existing' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Building Type</span> <strong class="text-slate-900">{{ $sections['customer_site_details']['building_type'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Client Contact</span> {{ $sections['customer_site_details']['client_contact'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Main Incharge</span> {{ $sections['customer_site_details']['main_incharge'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Site Incharge</span> {{ $sections['customer_site_details']['site_incharge'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Caretaker / Security</span> {{ $sections['customer_site_details']['caretaker_contact'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Survey Engineer</span> {{ $report->engineer->name }}</div>
            </div>
            <div class="text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Address / Location</span>
                <p class="text-slate-800 mt-0.5">{{ $sections['customer_site_details']['customer_address'] ?? '—' }}</p>
            </div>
        </div>

        <!-- 2. Power Requirements & EB Sanction Meters -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. Power Requirements & EB Meters</span>
                <span class="text-xs font-semibold text-slate-500">Sanctioned Loads</span>
            </h3>
            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs mb-3">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Solar Requirement / Capacity Proposal</span>
                <p class="text-slate-800 font-semibold mt-0.5">{{ $sections['power_req_meters']['solar_req_details'] ?? '—' }}</p>
            </div>

            <div class="grid grid-cols-3 gap-2 text-xs mb-3">
                <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 text-center">
                    <span class="text-indigo-800 block text-[10px] font-bold">EB Meter #1</span>
                    <strong class="text-slate-900 font-mono text-xs block">{{ $sections['power_req_meters']['meter1_kw'] ?? '—' }} kW</strong>
                    <span class="text-[9px] text-slate-500">{{ $sections['power_req_meters']['meter1_phase'] ?? '—' }}</span>
                    <span class="text-[8px] font-mono text-slate-400 block truncate">{{ $sections['power_req_meters']['meter1_number'] ?? '' }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 text-center">
                    <span class="text-indigo-800 block text-[10px] font-bold">EB Meter #2</span>
                    <strong class="text-slate-900 font-mono text-xs block">{{ $sections['power_req_meters']['meter2_kw'] ?? '—' }} kW</strong>
                    <span class="text-[9px] text-slate-500">{{ $sections['power_req_meters']['meter2_phase'] ?? '—' }}</span>
                    <span class="text-[8px] font-mono text-slate-400 block truncate">{{ $sections['power_req_meters']['meter2_number'] ?? '' }}</span>
                </div>
                <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 text-center">
                    <span class="text-indigo-800 block text-[10px] font-bold">EB Meter #3</span>
                    <strong class="text-slate-900 font-mono text-xs block">{{ $sections['power_req_meters']['meter3_kw'] ?? '—' }} kW</strong>
                    <span class="text-[9px] text-slate-500">{{ $sections['power_req_meters']['meter3_phase'] ?? '—' }}</span>
                    <span class="text-[8px] font-mono text-slate-400 block truncate">{{ $sections['power_req_meters']['meter3_number'] ?? '' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Day Load Pattern</span> {{ $sections['power_req_meters']['day_load_details'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Night Load Pattern</span> {{ $sections['power_req_meters']['night_load_details'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 3. Cabling & Conduit Routing Requirements -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>3. Cable & Conduit Infrastructure Estimates</span>
                <span class="text-xs font-semibold text-slate-500">Runway Requirements</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <h4 class="font-bold text-slate-900 uppercase text-[10px] mb-2">Cable Requirements (1 - 5)</h4>
                    <ul class="space-y-1.5 list-disc list-inside bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700">
                        @for($c=1; $c<=5; $c++)
                            @if(!empty($sections['cabling_conduits']['cable_req_' . $c]))
                                <li><strong>#{{ $c }}:</strong> {{ $sections['cabling_conduits']['cable_req_' . $c] }}</li>
                            @endif
                        @endfor
                        @if(empty($sections['cabling_conduits']['cable_req_1']))
                            <li class="text-slate-400 list-none">No specific cable runs estimated.</li>
                        @endif
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 uppercase text-[10px] mb-2">Conduit & Piping Requirements (1 - 5)</h4>
                    <ul class="space-y-1.5 list-disc list-inside bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700">
                        @for($p=1; $p<=5; $p++)
                            @if(!empty($sections['cabling_conduits']['conduit_req_' . $p]))
                                <li><strong>#{{ $p }}:</strong> {{ $sections['cabling_conduits']['conduit_req_' . $p] }}</li>
                            @endif
                        @endfor
                        @if(empty($sections['cabling_conduits']['conduit_req_1']))
                            <li class="text-slate-400 list-none">No specific conduits listed.</li>
                        @endif
                    </ul>
                </div>
            </div>

            @if(!empty($sections['cabling_conduits']['other_req']))
                <div class="mt-3 text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Other Infrastructure Requirements:</span>
                    <p class="text-slate-800 mt-0.5">{{ $sections['cabling_conduits']['other_req'] }}</p>
                </div>
            @endif
        </div>

        <!-- 4. Earthing, Plant Rooms & Protection -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>4. Earthing & Plant Room Feasibility</span>
                <span class="text-xs font-semibold text-slate-500">Protection Systems</span>
            </h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">DC Earthing Route / Location</span> {{ $sections['earthing_rooms_protection']['dc_earthing'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">AC Earthing Route / Location</span> {{ $sections['earthing_rooms_protection']['ac_earthing'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Lightning Arrester Status</span> <strong class="text-slate-900">{{ $sections['earthing_rooms_protection']['la_status'] ?? '—' }}</strong></div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Inverter (PCU) Room</span> {{ $sections['earthing_rooms_protection']['pcu_room_details'] ?? '—' }}</div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Battery Room</span> {{ $sections['earthing_rooms_protection']['battery_room_details'] ?? '—' }}</div>
                </div>
            </div>
        </div>

        <!-- 5. Rooftop, Logistics & Sign-Off -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>5. Rooftop & Transport Feasibility</span>
                <span class="text-xs font-semibold text-slate-500">{{ $sections['rooftop_logistics']['roof_type'] ?? 'RCC Flat' }}</span>
            </h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Rooftop Area / Measurements</span> <strong class="text-slate-900">{{ $sections['rooftop_logistics']['roof_measurements'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Shadow Analysis</span> {{ $sections['rooftop_logistics']['shadow_analysis'] ?? '—' }}</div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Module Lifting Method</span> {{ $sections['rooftop_logistics']['lifting_method'] ?? '—' }}</div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Vehicle Access</span> {{ $sections['rooftop_logistics']['vehicle_entrance'] ?? '—' }}</div>
                </div>
            </div>

            <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <span class="font-bold text-slate-900 block uppercase text-[10px]">Survey Joint Sign-Off</span>
                <div class="text-xs text-slate-700">
                    Joint Surveyed With: <strong class="text-slate-900">{{ $sections['rooftop_logistics']['survey_with_name'] ?? '—' }}</strong>
                    @if(!empty($sections['rooftop_logistics']['survey_with_phone']))
                        ({{ $sections['rooftop_logistics']['survey_with_phone'] }})
                    @endif
                </div>

                @if(!empty($sections['rooftop_logistics']['client_confirmed']))
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Site Survey Verified with Client
                    </span>
                @endif

                @if(!empty($sections['rooftop_logistics']['client_signature']))
                    <div class="pt-2 border-t border-slate-200">
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Client Signature / Acknowledgement</span>
                        <div class="bg-white p-2 rounded-lg border border-slate-200 inline-block">
                            <img src="{{ $sections['rooftop_logistics']['client_signature'] }}" alt="Client Signature" class="max-h-16 object-contain">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Photographs Gallery -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Site Survey Photographs & Layout Views ({{ $report->photos->count() }})</span>
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
            <h3 class="text-base font-bold text-slate-900">Request Survey Corrections</h3>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="correction_notes" rows="4" required placeholder="Specify what measurements or photos need correction..."
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
