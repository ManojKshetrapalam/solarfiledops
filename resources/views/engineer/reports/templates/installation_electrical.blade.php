@extends('layouts.engineer')

@section('mobile_title', 'Electrical: ' . $report->report_number)
@section('header_back_url', $report->service_id ? route('engineer.services.show', $report->service_id) : route('engineer.reports.index'))
@section('hide_bottom_nav', 'true')

@section('engineer_content')
<div x-data="electricalWizard({
        reportId: {{ $report->id }},
        currentStep: {{ $report->current_step ?? 1 }},
        saveDraftUrl: '{{ route('engineer.reports.save-draft', $report->id) }}',
        uploadPhotoUrl: '{{ route('engineer.reports.upload-photo', $report->id) }}',
        csrfToken: '{{ csrf_token() }}',
        initialSections: {{ Js::from($sections) }},
        existingPhotos: {{ Js::from($report->photos->map(fn($p) => [
            'id' => $p->id,
            'section_key' => $p->section_key,
            'photo_type' => $p->photo_type,
            'url' => $p->url,
            'filename' => $p->original_filename,
            'captured_at' => $p->captured_at ? $p->captured_at->format('d M Y, h:i A') : '',
            'caption' => $p->caption ?? '',
        ])) }}
    })"
    class="pb-36 sm:pb-40">

    <!-- Top Sticky Progress Bar -->
    <div class="sticky top-14 z-30 bg-slate-900 text-white -mx-4 px-4 py-2.5 shadow-md border-b border-slate-800">
        <div class="flex items-center justify-between text-xs mb-1.5">
            <div class="flex items-center gap-1.5 truncate">
                <span class="font-mono text-amber-400 font-bold shrink-0">{{ $report->report_number }}</span>
                <span class="text-slate-500">&bull;</span>
                <span class="text-slate-300 font-semibold truncate" x-text="stepTitles[currentStep - 1]"></span>
            </div>
            <span class="text-xs font-bold text-amber-400 shrink-0 ml-2" x-text="'Step ' + currentStep + ' of 6'"></span>
        </div>

        <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
            <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-300" 
                 :style="'width: ' + ((currentStep / 6) * 100) + '%'"></div>
        </div>

        <div class="flex items-center justify-between text-[11px] mt-1.5 text-slate-400">
            <span x-text="saveStatus" :class="saveStatusColor"></span>
            <span class="text-slate-400 truncate max-w-[200px]">{{ $report->customer?->name ?? $report->company->name }}</span>
        </div>
    </div>

    <!-- Admin Correction Note Warning Banner -->
    @if($report->status === 'correction_required' && $report->correction_notes)
        <div class="mt-3 bg-amber-500 text-slate-950 p-3.5 rounded-2xl shadow-sm border border-amber-600">
            <div class="flex items-center gap-2 font-bold text-xs">
                <svg class="w-4 h-4 text-slate-950 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Admin Correction Request:</span>
            </div>
            <p class="text-xs mt-1 bg-amber-400 p-2 rounded-lg font-medium text-slate-950">
                "{{ $report->correction_notes }}"
            </p>
        </div>
    @endif

    <form id="reportForm" action="{{ route('engineer.reports.submit', $report->id) }}" method="POST" class="mt-4">
        @csrf
        <input type="hidden" name="current_step" :value="currentStep">

        <!-- STEP 1: Site & Plant Info -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Installation Part 2</span>
                        <h3 class="text-sm font-bold text-slate-900">Step 1: Electrical Job Info</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Electrical & Cabling Technician Details</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Name & Site Address</label>
                    <input type="text" x-model="form.site_plant_info.customer_name" readonly
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-medium">
                    <textarea x-model="form.site_plant_info.customer_address" rows="2" readonly
                              class="w-full mt-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-medium"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Plant Capacity (kW) *</label>
                        <input type="text" x-model="form.site_plant_info.plant_capacity" placeholder="e.g. 100 kW / 50 kW"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Electrical Technician Name</label>
                        <input type="text" x-model="form.site_plant_info.technician_name"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Installation Date *</label>
                        <input type="date" x-model="form.site_plant_info.service_date"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Technician Phone</label>
                        <input type="tel" x-model="form.site_plant_info.technician_phone"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Earthing Work -->
        <div x-show="currentStep === 2" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 2: Earthing Work Detailed</h3>
                    <p class="text-xs text-slate-500">Record earthing materials, pit depths, and measured resistance values.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Earthing Materials Used *</label>
                    <input type="text" x-model="form.earthing_work.materials" placeholder="e.g. Copper bonded rods (17.2mm x 3m) with chemical compound"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Way of Work & Pit Depth *</label>
                    <textarea x-model="form.earthing_work.way_of_work" rows="2" placeholder="e.g. 3.0m deep bore augured; 2 bags of chemical backfill compound slurry filled; masonry inspection chamber built."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Resistance Readings Grid -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Measured Earth Resistance Readings (Ohms)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">DC Inverter Earth Pit (&Omega;)</label>
                            <input type="text" x-model="form.earthing_work.dc_resistance" placeholder="e.g. 1.8 &Omega;"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">AC Grid Earth Pit (&Omega;)</label>
                            <input type="text" x-model="form.earthing_work.ac_resistance" placeholder="e.g. 2.1 &Omega;"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">LA Earth Pit (&Omega;)</label>
                            <input type="text" x-model="form.earthing_work.la_resistance" placeholder="e.g. 1.5 &Omega;"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Earthing Work Remarks</label>
                    <textarea x-model="form.earthing_work.remarks" rows="2" placeholder="Any chamber cover or copper strip connection remarks..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 3: AJB (Array Junction Box) Work -->
        <div x-show="currentStep === 3" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 3: AJB Work & String Configuration</h3>
                    <p class="text-xs text-slate-500">Record junction box specifications and electrical string measurements.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Materials Used (Enclosure / Fuses) *</label>
                        <input type="text" x-model="form.ajb_work.materials_used" placeholder="e.g. IP65 Polycarbonate with 1000V DC fuses"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">How Fixed *</label>
                        <input type="text" x-model="form.ajb_work.how_fixed" placeholder="e.g. Clamped to module mounting structure leg"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Safety Precautions & Measures *</label>
                    <input type="text" x-model="form.ajb_work.safety_measures" placeholder="e.g. DC Surge Protection Device (SPD Class II), MC4 connectors crimped"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <!-- String Configuration Grid -->
                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="block text-xs font-bold uppercase tracking-wider text-amber-950">String Configuration & Readings</span>
                        <span class="text-[10px] text-amber-800 font-semibold">Amps, Volts, Panels, Wattage</span>
                    </div>

                    <!-- String 1 -->
                    <div class="grid grid-cols-4 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 1 Volts</label>
                            <input type="text" x-model="form.ajb_work.str1_volts" placeholder="e.g. 680V" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 1 Amps</label>
                            <input type="text" x-model="form.ajb_work.str1_amps" placeholder="e.g. 13.2A" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">No. Panels</label>
                            <input type="text" x-model="form.ajb_work.str1_panels" placeholder="e.g. 16" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Wattage</label>
                            <input type="text" x-model="form.ajb_work.str1_wattage" placeholder="e.g. 8.7kW" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                    </div>

                    <!-- String 2 -->
                    <div class="grid grid-cols-4 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 2 Volts</label>
                            <input type="text" x-model="form.ajb_work.str2_volts" placeholder="e.g. 682V" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 2 Amps</label>
                            <input type="text" x-model="form.ajb_work.str2_amps" placeholder="e.g. 13.1A" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">No. Panels</label>
                            <input type="text" x-model="form.ajb_work.str2_panels" placeholder="e.g. 16" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Wattage</label>
                            <input type="text" x-model="form.ajb_work.str2_wattage" placeholder="e.g. 8.7kW" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                    </div>

                    <!-- String 3 & 4 -->
                    <div class="grid grid-cols-4 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 3 Volts</label>
                            <input type="text" x-model="form.ajb_work.str3_volts" placeholder="e.g. 680V" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 3 Amps</label>
                            <input type="text" x-model="form.ajb_work.str3_amps" placeholder="e.g. 13.2A" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 4 Volts</label>
                            <input type="text" x-model="form.ajb_work.str4_volts" placeholder="e.g. 679V" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Str 4 Amps</label>
                            <input type="text" x-model="form.ajb_work.str4_amps" placeholder="e.g. 13.0A" class="w-full px-2 py-1 border border-slate-300 rounded">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cabling Used & Remarks</label>
                    <textarea x-model="form.ajb_work.remarks" rows="2" placeholder="e.g. 4 sq.mm solar DC cable used; glanding and ferrule tagging completed."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 4: Cabling Work & Tray Laying -->
        <div x-show="currentStep === 4" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 4: Cabling Work Detailed</h3>
                    <p class="text-xs text-slate-500">Cable specifications (Material, Size, Core, Measurement, Laying method).</p>
                </div>

                <div class="space-y-3">
                    <!-- Line 1: Solar DC Array Cable -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <span class="font-bold text-slate-800 block uppercase text-[10px]">1. String / DC Solar Cable</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <input type="text" x-model="form.cabling_work.cable1_material" placeholder="Material: Copper" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable1_size" placeholder="Size: 4 sq.mm" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable1_length" placeholder="Length: 120m" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable1_laying" placeholder="Laying: UV Conduit" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        </div>
                    </div>

                    <!-- Line 2: Main DC Cable -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <span class="font-bold text-slate-800 block uppercase text-[10px]">2. Main DC Bus Cable (AJB to DCDB)</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <input type="text" x-model="form.cabling_work.cable2_material" placeholder="Material: Cu/Al" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable2_size" placeholder="Size: 16 sq.mm" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable2_length" placeholder="Length: 35m" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable2_laying" placeholder="Laying: GI Cable Tray" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        </div>
                    </div>

                    <!-- Line 3: AC Output Cable -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <span class="font-bold text-slate-800 block uppercase text-[10px]">3. AC Inverter Output Cable</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <input type="text" x-model="form.cabling_work.cable3_material" placeholder="Material: Armored Cu" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable3_size" placeholder="Size: 4C x 35 sq.mm" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable3_length" placeholder="Length: 45m" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable3_laying" placeholder="Laying: Underground trench" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        </div>
                    </div>

                    <!-- Line 4 & 5 -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <span class="font-bold text-slate-800 block uppercase text-[10px]">4 & 5. Earthing & Communication Cabling</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" x-model="form.cabling_work.cable4_notes" placeholder="4. Earthing strip: 25x3mm GI strip clamped" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <input type="text" x-model="form.cabling_work.cable5_notes" placeholder="5. RS485 communication cable in shielded conduit" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Cabling Work Remarks</label>
                    <textarea x-model="form.cabling_work.cabling_remarks" rows="2" placeholder="Cable tray covers, UV protection, tagging details..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 5: DCDB & ACDB Distribution Boxes & Sign-off -->
        <div x-show="currentStep === 5" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 5: DCDB / ACDB & Sign-Off</h3>
                    <p class="text-xs text-slate-500">Distribution boards, protection devices, and client confirmation.</p>
                </div>

                <!-- DCDB Work -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">DCDB Work Detailed</span>
                    <input type="text" x-model="form.dcdb_acdb_work.dcdb_materials" placeholder="Materials: DC SPD 1000V, DC Isolator 63A, IP65 box" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" x-model="form.dcdb_acdb_work.dcdb_fixing" placeholder="Fixing: Wall mounted with rawl plugs at 1.5m height" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                </div>

                <!-- ACDB Work (Input & Output) -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">ACDB Work (Input & Output)</span>
                    <input type="text" x-model="form.dcdb_acdb_work.acdb_input" placeholder="ACDB Input: 63A 4-Pole MCB with Type II AC SPD" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" x-model="form.dcdb_acdb_work.acdb_output" placeholder="ACDB Output: Energy meter, CT coil, and main feeder interconnect" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                </div>

                <!-- Completed Works 1 to 5 -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-800">Works Completed (Items 1 to 5)</label>
                    <input type="text" x-model="form.dcdb_acdb_work.completed_1" placeholder="1. Earthing pits dug, chemical slurry filled and resistance tested" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" x-model="form.dcdb_acdb_work.completed_2" placeholder="2. AJB mounted and string cables terminated with MC4" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" x-model="form.dcdb_acdb_work.completed_3" placeholder="3. DCDB and ACDB installed with SPDs and circuit breakers" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" x-model="form.dcdb_acdb_work.completed_4" placeholder="4. Main AC & DC cables laid in cable trays with tagging" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" x-model="form.dcdb_acdb_work.completed_5" placeholder="5. Megger insulation resistance & continuity verified" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                </div>

                <!-- Client Sign-off & Signature Pad -->
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Work Acceptance Confirmation</span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Checked By (Client / Site Incharge)</label>
                            <input type="text" x-model="form.dcdb_acdb_work.checked_by_name" placeholder="Name of site person" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Contact Phone</label>
                            <input type="tel" x-model="form.dcdb_acdb_work.checked_by_phone" placeholder="Phone number" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                        </div>
                    </div>

                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-amber-300 bg-amber-50/60 cursor-pointer">
                        <input type="checkbox" x-model="form.dcdb_acdb_work.client_confirmed" class="mt-0.5 w-4 h-4 text-amber-600 rounded">
                        <span class="text-xs text-slate-800 font-medium">
                            I confirm that the electrical, earthing, AJB, and distribution cabling work has been inspected and verified.
                        </span>
                    </label>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">Client Digital Signature</label>
                            <button type="button" @click="clearSignature()" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Clear</button>
                        </div>
                        <canvas id="signaturePad" 
                                class="w-full h-36 bg-slate-50 border border-slate-300 rounded-xl touch-none cursor-crosshair block" 
                                style="touch-action: none;"
                                @pointerdown="sigStart($event)"
                                @pointermove="sigMove($event)"
                                @pointerup="sigEnd($event)"
                                @pointercancel="sigEnd($event)"
                                @touchstart.prevent="sigStart($event)"
                                @touchmove.prevent="sigMove($event)"
                                @touchend.prevent="sigEnd($event)">
                        </canvas>
                        <p class="text-[10px] text-slate-400 mt-1">Sign inside the box using finger or stylus.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 6: Photos & Final Submit -->
        <div x-show="currentStep === 6" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 6: Electrical Photos & Submission</h3>
                    <p class="text-xs text-slate-500">Snap clear site photos of earth pits, AJB, DCDB, ACDB, and cable laying.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Earth Pit & Chamber</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'earthing_work', 'earth_pit')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">AJB Wiring</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'ajb_work', 'ajb_wiring')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">DCDB / ACDB Boxes</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'dcdb_acdb_work', 'dcdb_acdb')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Cable Tray Routing</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'cabling_work', 'cable_tray')">
                    </label>
                </div>

                <!-- Photos Gallery -->
                <div x-show="photos.length > 0" class="pt-3 border-t border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Uploaded Electrical Photos (<span x-text="photos.length"></span>)</span>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="p in photos" :key="p.id">
                            <div class="relative group rounded-xl overflow-hidden border border-slate-200 aspect-square bg-slate-100">
                                <img :src="p.url" :alt="p.filename" class="w-full h-full object-cover">
                                <button type="button" @click="deletePhoto(p.id)"
                                        class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-1 shadow-md hover:bg-rose-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Submit Card -->
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center space-y-2 mt-4">
                    <h4 class="text-sm font-extrabold text-emerald-950">Ready to Submit Electrical Report?</h4>
                    <p class="text-xs text-emerald-800">Once submitted, this electrical report will be sent to Admin for review.</p>
                    <button type="button" @click="confirmSubmit()"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-sm rounded-xl shadow-md transition-all">
                        Submit Electrical Report
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Bottom Sticky Navigation Buttons -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 max-w-lg mx-auto shadow-lg flex items-center justify-between gap-3">
        <button type="button" @click="prevStep()" :disabled="currentStep === 1"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 disabled:opacity-30 disabled:pointer-events-none flex items-center gap-1">
            &larr; Previous
        </button>

        <button type="button" @click="saveDraft()"
                class="px-3 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <span>Save</span>
        </button>

        <template x-if="currentStep < 6">
            <button type="button" @click="nextStep()"
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm flex items-center gap-1">
                <span>Next</span>
                &rarr;
            </button>
        </template>

        <template x-if="currentStep === 6">
            <button type="button" @click="confirmSubmit()"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm">
                Review & Submit
            </button>
        </template>
    </div>

    <!-- Submit Confirmation Modal -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="text-center">
                <h3 class="text-base font-extrabold text-slate-900">Confirm Electrical Submission</h3>
                <p class="text-xs text-slate-600 mt-1">
                    Are you sure all earthing readings, AJB strings, cabling details, and client sign-off are complete?
                </p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" @click="showConfirmModal = false" class="w-1/2 py-2.5 border border-slate-300 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button" @click="doSubmit()" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md">
                    Confirm & Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function electricalWizard(config) {
    return {
        reportId: config.reportId,
        currentStep: config.currentStep || 1,
        saveDraftUrl: config.saveDraftUrl,
        uploadPhotoUrl: config.uploadPhotoUrl,
        csrfToken: config.csrfToken,
        saveStatus: 'All changes saved',
        saveStatusColor: 'text-slate-400',
        photos: config.existingPhotos || [],
        showConfirmModal: false,
        hasSignature: false,
        sigDrawing: false,
        sigLastX: 0,
        sigLastY: 0,

        stepTitles: [
            'Job Info',
            'Earthing Work',
            'AJB & Strings',
            'Cabling & Trays',
            'DCDB / ACDB & Sign-Off',
            'Photos & Submit'
        ],

        form: {
            site_plant_info: Object.assign({
                customer_name: '',
                customer_address: '',
                plant_capacity: '',
                service_date: '',
                technician_name: '',
                technician_phone: ''
            }, config.initialSections.site_plant_info || {}),

            earthing_work: Object.assign({
                materials: '',
                way_of_work: '',
                dc_resistance: '',
                ac_resistance: '',
                la_resistance: '',
                remarks: ''
            }, config.initialSections.earthing_work || {}),

            ajb_work: Object.assign({
                materials_used: '',
                how_fixed: '',
                safety_measures: '',
                str1_volts: '', str1_amps: '', str1_panels: '', str1_wattage: '',
                str2_volts: '', str2_amps: '', str2_panels: '', str2_wattage: '',
                str3_volts: '', str3_amps: '',
                str4_volts: '', str4_amps: '',
                remarks: ''
            }, config.initialSections.ajb_work || {}),

            cabling_work: Object.assign({
                cable1_material: '', cable1_size: '', cable1_length: '', cable1_laying: '',
                cable2_material: '', cable2_size: '', cable2_length: '', cable2_laying: '',
                cable3_material: '', cable3_size: '', cable3_length: '', cable3_laying: '',
                cable4_notes: '',
                cable5_notes: '',
                cabling_remarks: ''
            }, config.initialSections.cabling_work || {}),

            dcdb_acdb_work: Object.assign({
                dcdb_materials: '',
                dcdb_fixing: '',
                acdb_input: '',
                acdb_output: '',
                completed_1: '', completed_2: '', completed_3: '', completed_4: '', completed_5: '',
                checked_by_name: '',
                checked_by_phone: '',
                client_confirmed: false,
                client_signature: '',
                client_signed_at: ''
            }, config.initialSections.dcdb_acdb_work || {})
        },

        init() {
            this.$watch('currentStep', (val) => {
                if (val === 5) {
                    this.$nextTick(() => this.setupCanvas());
                }
            });
            if (this.currentStep === 5) {
                this.$nextTick(() => this.setupCanvas());
            }
        },

        setupCanvas() {
            const canvas = document.getElementById('signaturePad');
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const targetW = Math.round(rect.width * dpr);
            const targetH = Math.round(rect.height * dpr);

            if (canvas.width !== targetW || canvas.height !== targetH) {
                const existingData = this.form.dcdb_acdb_work.client_signature;
                canvas.width = targetW;
                canvas.height = targetH;
                const ctx = canvas.getContext('2d');
                if (existingData && existingData.startsWith('data:image')) {
                    const img = new Image();
                    img.onload = () => {
                        ctx.drawImage(img, 0, 0, targetW, targetH);
                        this.hasSignature = true;
                    };
                    img.src = existingData;
                }
            } else if (this.form.dcdb_acdb_work.client_signature && !this.hasSignature) {
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.hasSignature = true;
                };
                img.src = this.form.dcdb_acdb_work.client_signature;
            }
        },

        getSigPos(e, canvas) {
            const rect = canvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else if (e.changedTouches && e.changedTouches.length > 0) {
                clientX = e.changedTouches[0].clientX;
                clientY = e.changedTouches[0].clientY;
            }
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        },

        sigStart(e) {
            if (e.button !== undefined && e.button !== 0) return;
            if (this.sigDrawing && e.type === 'touchstart') return;
            if (e.cancelable) e.preventDefault();
            const canvas = document.getElementById('signaturePad') || e.target;
            this.setupCanvas();
            if (canvas.setPointerCapture && e.pointerId !== undefined) {
                try { canvas.setPointerCapture(e.pointerId); } catch(err) {}
            }
            this.sigDrawing = true;
            const pos = this.getSigPos(e, canvas);
            this.sigLastX = pos.x;
            this.sigLastY = pos.y;
            const ctx = canvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            ctx.beginPath();
            ctx.arc(pos.x, pos.y, 1.8 * dpr, 0, Math.PI * 2);
            ctx.fillStyle = '#0f172a';
            ctx.fill();
            this.hasSignature = true;
        },

        sigMove(e) {
            if (!this.sigDrawing) return;
            if (e.cancelable) e.preventDefault();
            const canvas = document.getElementById('signaturePad') || e.target;
            const pos = this.getSigPos(e, canvas);
            const ctx = canvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            ctx.beginPath();
            ctx.moveTo(this.sigLastX, this.sigLastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 3 * dpr;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
            this.sigLastX = pos.x;
            this.sigLastY = pos.y;
            this.hasSignature = true;
        },

        sigEnd(e) {
            if (!this.sigDrawing) return;
            this.sigDrawing = false;
            const canvas = document.getElementById('signaturePad') || e.target;
            if (canvas.releasePointerCapture && e.pointerId !== undefined) {
                try { canvas.releasePointerCapture(e.pointerId); } catch(err) {}
            }
            this.form.dcdb_acdb_work.client_signature = canvas.toDataURL('image/png');
            this.form.dcdb_acdb_work.client_signed_at = new Date().toISOString();
            this.hasSignature = true;
            this.saveDraft(true);
        },

        clearSignature() {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            this.form.dcdb_acdb_work.client_signature = '';
            this.form.dcdb_acdb_work.client_signed_at = '';
            this.hasSignature = false;
            this.saveDraft(true);
        },

        nextStep() {
            this.saveDraft(true);
            if (this.currentStep < 6) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prevStep() {
            this.saveDraft(true);
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        confirmSubmit() {
            this.showConfirmModal = true;
        },

        doSubmit() {
            document.getElementById('reportForm').submit();
        },

        saveDraft(silent = false) {
            if (!silent) {
                this.saveStatus = 'Saving draft...';
                this.saveStatusColor = 'text-amber-400';
            }
            fetch(this.saveDraftUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    current_step: this.currentStep,
                    sections: this.form
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.saveStatus = 'Saved ' + (data.saved_at || 'just now');
                    this.saveStatusColor = 'text-emerald-400';
                }
            })
            .catch(() => {
                this.saveStatus = 'Saved offline';
                this.saveStatusColor = 'text-amber-400';
            });
        },

        handlePhotoUpload(event, sectionKey, photoType) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('section_key', sectionKey);
            formData.append('photo_type', photoType);
            formData.append('_token', this.csrfToken);

            this.saveStatus = 'Uploading photo...';
            this.saveStatusColor = 'text-amber-400';

            fetch(this.uploadPhotoUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.photo) {
                    this.photos.push(data.photo);
                    this.saveStatus = 'Photo uploaded';
                    this.saveStatusColor = 'text-emerald-400';
                } else {
                    alert('Photo upload failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(() => alert('Photo upload failed: Check connection.'));
        },

        deletePhoto(photoId) {
            if (!confirm('Are you sure you want to delete this photo?')) return;
            fetch('/engineer/reports/photos/' + photoId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.photos = this.photos.filter(p => p.id !== photoId);
                }
            });
        }
    };
}
</script>
@endsection
