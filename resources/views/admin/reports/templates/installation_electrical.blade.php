@extends('layouts.admin')

@section('title', 'Review Electrical Installation #' . $report->report_number . ' - SolarOps')
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
                    <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-cyan-200">
                        Installation: Electrical & Cabling
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
                    Electrical Technician: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
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

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this electrical installation report? This marks the task completed.')">
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
                <span class="text-cyan-700 font-mono text-[11px]">{{ $sections['site_plant_info']['service_date'] ?? '—' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Plant Capacity</span> <strong class="text-slate-800">{{ $sections['site_plant_info']['plant_capacity'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Technician Phone</span> <strong class="text-slate-800">{{ $sections['site_plant_info']['technician_phone'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Technician Name</span> {{ $sections['site_plant_info']['technician_name'] ?? $report->engineer->name }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Site Address</span> {{ $sections['site_plant_info']['customer_address'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 2. Earthing Work Detailed -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. Earthing System Detailed</span>
                <span class="text-xs font-semibold text-slate-500">Pit Resistance Values</span>
            </h3>
            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">DC Earthing Pit</span>
                    <strong class="text-slate-900 text-sm font-mono">{{ $sections['earthing_work']['dc_resistance'] ?? '—' }} &Omega;</strong>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">AC Earthing Pit</span>
                    <strong class="text-slate-900 text-sm font-mono">{{ $sections['earthing_work']['ac_resistance'] ?? '—' }} &Omega;</strong>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Lightning Arrester</span>
                    <strong class="text-slate-900 text-sm font-mono">{{ $sections['earthing_work']['la_resistance'] ?? '—' }} &Omega;</strong>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Materials Used</span> {{ $sections['earthing_work']['materials'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Way of Work / Depths</span> {{ $sections['earthing_work']['way_of_work'] ?? '—' }}</div>
            </div>
            @if(!empty($sections['earthing_work']['remarks']))
                <div class="mt-2 text-xs text-slate-600 bg-slate-50 p-2 rounded-lg">
                    <span class="font-bold text-[10px] uppercase text-slate-400">Remarks:</span> {{ $sections['earthing_work']['remarks'] }}
                </div>
            @endif
        </div>

        <!-- 3. AJB & Array Configuration -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>3. Array Junction Box (AJB) & String Configuration</span>
                <span class="text-xs font-semibold text-slate-500">String Voltage & Amperage</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-cyan-800 font-bold block text-xs mb-1">String #1</span>
                    <div class="space-y-0.5 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Voltage:</span> <strong>{{ $sections['ajb_work']['str1_volts'] ?? '—' }} V</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Current:</span> <strong>{{ $sections['ajb_work']['str1_amps'] ?? '—' }} A</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Panels:</span> <strong>{{ $sections['ajb_work']['str1_panels'] ?? '—' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Wattage:</span> <strong>{{ $sections['ajb_work']['str1_wattage'] ?? '—' }} W</strong></div>
                    </div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-cyan-800 font-bold block text-xs mb-1">String #2</span>
                    <div class="space-y-0.5 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Voltage:</span> <strong>{{ $sections['ajb_work']['str2_volts'] ?? '—' }} V</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Current:</span> <strong>{{ $sections['ajb_work']['str2_amps'] ?? '—' }} A</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Panels:</span> <strong>{{ $sections['ajb_work']['str2_panels'] ?? '—' }}</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Wattage:</span> <strong>{{ $sections['ajb_work']['str2_wattage'] ?? '—' }} W</strong></div>
                    </div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-cyan-800 font-bold block text-xs mb-1">String #3</span>
                    <div class="space-y-0.5 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Voltage:</span> <strong>{{ $sections['ajb_work']['str3_volts'] ?? '—' }} V</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Current:</span> <strong>{{ $sections['ajb_work']['str3_amps'] ?? '—' }} A</strong></div>
                    </div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-cyan-800 font-bold block text-xs mb-1">String #4</span>
                    <div class="space-y-0.5 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Voltage:</span> <strong>{{ $sections['ajb_work']['str4_volts'] ?? '—' }} V</strong></div>
                        <div class="flex justify-between"><span class="text-slate-500">Current:</span> <strong>{{ $sections['ajb_work']['str4_amps'] ?? '—' }} A</strong></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Materials Used</span> {{ $sections['ajb_work']['materials_used'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">How Fixed</span> {{ $sections['ajb_work']['how_fixed'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Safety Measures</span> {{ $sections['ajb_work']['safety_measures'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 4. Cabling & Conduit Routing -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>4. Cabling Lines & Conduit Specifications</span>
                <span class="text-xs font-semibold text-slate-500">5 Distinct Cable Lines</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 text-[10px] uppercase font-bold bg-slate-50">
                            <th class="py-2 px-3">Cabling Segment</th>
                            <th class="py-2 px-3">Material</th>
                            <th class="py-2 px-3">Core & Size (Sq mm)</th>
                            <th class="py-2 px-3">Total Length (Meters)</th>
                            <th class="py-2 px-3">Laying Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-slate-800">Line 1: Solar Array to AJB</td>
                            <td class="py-2.5 px-3">{{ $sections['cabling_work']['cable1_material'] ?? '—' }}</td>
                            <td class="py-2.5 px-3 font-mono font-semibold">{{ $sections['cabling_work']['cable1_size'] ?? '—' }}</td>
                            <td class="py-2.5 px-3 font-mono">{{ $sections['cabling_work']['cable1_length'] ?? '—' }} m</td>
                            <td class="py-2.5 px-3">{{ $sections['cabling_work']['cable1_laying'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-slate-800">Line 2: AJB to DCDB</td>
                            <td class="py-2.5 px-3">{{ $sections['cabling_work']['cable2_material'] ?? '—' }}</td>
                            <td class="py-2.5 px-3 font-mono font-semibold">{{ $sections['cabling_work']['cable2_size'] ?? '—' }}</td>
                            <td class="py-2.5 px-3 font-mono">{{ $sections['cabling_work']['cable2_length'] ?? '—' }} m</td>
                            <td class="py-2.5 px-3">{{ $sections['cabling_work']['cable2_laying'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-slate-800">Line 3: DCDB to PCU (Inverter)</td>
                            <td class="py-2.5 px-3">{{ $sections['cabling_work']['cable3_material'] ?? '—' }}</td>
                            <td class="py-2.5 px-3 font-mono font-semibold">{{ $sections['cabling_work']['cable3_size'] ?? '—' }}</td>
                            <td class="py-2.5 px-3 font-mono">{{ $sections['cabling_work']['cable3_length'] ?? '—' }} m</td>
                            <td class="py-2.5 px-3">{{ $sections['cabling_work']['cable3_laying'] ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-100 text-xs">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Line 4: PCU to ACDB / Inverter to Mains</span>
                    <p class="text-slate-800 mt-0.5">{{ $sections['cabling_work']['cable4_notes'] ?? '—' }}</p>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Line 5: ACDB to Mains LT Panel</span>
                    <p class="text-slate-800 mt-0.5">{{ $sections['cabling_work']['cable5_notes'] ?? '—' }}</p>
                </div>
            </div>
            @if(!empty($sections['cabling_work']['cabling_remarks']))
                <div class="mt-2 text-xs text-slate-600 bg-slate-50 p-2 rounded-lg">
                    <span class="font-bold text-[10px] uppercase text-slate-400">Cabling Remarks:</span> {{ $sections['cabling_work']['cabling_remarks'] }}
                </div>
            @endif
        </div>

        <!-- 5. DCDB, ACDB & Sign-Off -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>5. Distribution Boards & Client Work Acceptance</span>
                <span class="text-xs font-semibold text-slate-500">Sign-Off & Handover</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <h4 class="font-bold text-slate-900 uppercase text-[10px] mb-2">DCDB & ACDB Installation</h4>
                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">DCDB Materials</span> {{ $sections['dcdb_acdb_work']['dcdb_materials'] ?? '—' }}</div>
                        <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">DCDB Fixing</span> {{ $sections['dcdb_acdb_work']['dcdb_fixing'] ?? '—' }}</div>
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">ACDB Input</span> {{ $sections['dcdb_acdb_work']['acdb_input'] ?? '—' }}
                        </div>
                        <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">ACDB Output</span> {{ $sections['dcdb_acdb_work']['acdb_output'] ?? '—' }}</div>
                    </div>

                    @if(!empty($sections['dcdb_acdb_work']['completed_1']))
                        <span class="font-bold text-slate-900 block uppercase text-[10px] mt-3 mb-1">Key Milestones Completed</span>
                        <ul class="space-y-1 list-disc list-inside text-slate-700">
                            @for($i=1; $i<=5; $i++)
                                @if(!empty($sections['dcdb_acdb_work']['completed_' . $i]))
                                    <li>{{ $sections['dcdb_acdb_work']['completed_' . $i] }}</li>
                                @endif
                            @endfor
                        </ul>
                    @endif
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 flex flex-col justify-between">
                    <div>
                        <span class="font-bold text-slate-900 block uppercase text-[10px] mb-2">Work Acceptance Confirmation</span>
                        <div class="text-xs text-slate-700">
                            Checked By: <strong class="text-slate-900">{{ $sections['dcdb_acdb_work']['checked_by_name'] ?? '—' }}</strong>
                            @if(!empty($sections['dcdb_acdb_work']['checked_by_phone']))
                                ({{ $sections['dcdb_acdb_work']['checked_by_phone'] }})
                            @endif
                        </div>

                        @if(!empty($sections['dcdb_acdb_work']['client_confirmed']))
                            <div class="mt-2">
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Client Inspected & Satisfied
                                </span>
                            </div>
                        @endif
                    </div>

                    @if(!empty($sections['dcdb_acdb_work']['client_signature']))
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Client Digital Signature</span>
                            <div class="bg-white p-2 rounded-lg border border-slate-200 inline-block">
                                <img src="{{ $sections['dcdb_acdb_work']['client_signature'] }}" alt="Client Signature" class="max-h-16 object-contain">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Photographs Gallery -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Electrical & Cabling Photographs ({{ $report->photos->count() }})</span>
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
            <h3 class="text-base font-bold text-slate-900">Request Electrical Corrections</h3>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="correction_notes" rows="4" required placeholder="Specify what needs correction on cabling or earthing..."
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
