@extends('layouts.engineer')

@section('mobile_title', 'Structure: ' . $report->report_number)
@section('header_back_url', $report->service_id ? route('engineer.services.show', $report->service_id) : route('engineer.reports.index'))
@section('hide_bottom_nav', 'true')

@section('engineer_content')
<div x-data="structureWizard({
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

        <!-- STEP 1: Site & Plant Information -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-800 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Installation Part 1</span>
                        <h3 class="text-sm font-bold text-slate-900">Step 1: Site & Plant Info</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Structure & Module Mounting Technician Details</p>
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
                        <input type="text" x-model="form.site_plant_info.plant_capacity" placeholder="e.g. 100 kW / 25 kW"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Solar Modules Make / Model</label>
                        <input type="text" x-model="form.site_plant_info.module_make_model" placeholder="e.g. Waaree 540Wp Mono PERC"
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
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Structure Technician Name</label>
                        <input type="text" x-model="form.site_plant_info.technician_name"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Panels Delivered & Material Detailed -->
        <div x-show="currentStep === 2" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 2: Material & Panels Delivered</h3>
                    <p class="text-xs text-slate-500">Record panel quantities, individual wattage, and unboxing condition.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">No. of Panels Delivered *</label>
                        <input type="number" x-model="form.panels_delivered.no_of_panels" placeholder="e.g. 185"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Panel Wattage (Wp) *</label>
                        <input type="text" x-model="form.panels_delivered.panel_wattage" placeholder="e.g. 545 Wp"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Total Delivered (kWp)</label>
                        <input type="text" x-model="form.panels_delivered.total_delivered_kwp" placeholder="e.g. 100.8 kWp"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Physical Delivery Condition</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer bg-slate-50 text-xs font-semibold">
                            <input type="radio" value="Good Condition" x-model="form.panels_delivered.delivery_condition" class="text-amber-500">
                            <span>All Intact</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer bg-slate-50 text-xs font-semibold">
                            <input type="radio" value="Minor Packaging Damage" x-model="form.panels_delivered.delivery_condition" class="text-amber-500">
                            <span>Minor Outer</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer bg-slate-50 text-xs font-semibold">
                            <input type="radio" value="Glass Damage / Defect" x-model="form.panels_delivered.delivery_condition" class="text-amber-500">
                            <span>Damage/Defect</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Material Delivered Remarks</label>
                    <textarea x-model="form.panels_delivered.delivery_remarks" rows="3" placeholder="Enter pallet serials, delivery bill details, or damaged module numbers..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 3: Panels Installation & Mounting Work -->
        <div x-show="currentStep === 3" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 3: Panels Installation & Washing</h3>
                    <p class="text-xs text-slate-500">Layout geometry, maintenance space, and panel washing arrangements.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">No. of Rows Installed *</label>
                        <input type="text" x-model="form.mounting_work.no_of_rows" placeholder="e.g. 6 Rows"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">No. of Strings *</label>
                        <input type="text" x-model="form.mounting_work.no_of_strings" placeholder="e.g. 10 Strings"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Maintenance Space Available (with Measurements) *</label>
                    <textarea x-model="form.mounting_work.maintenance_space" rows="2" placeholder="e.g. 2.5 feet walkway between rows, 3 feet clear clearance from parapet wall."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-blue-900">Provision for Panel Washing Arrangements</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-blue-800 mb-1">Water Storage</label>
                            <input type="text" x-model="form.mounting_work.washing_water_storage" placeholder="e.g. 1000L Overhead tank"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 text-xs bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-blue-800 mb-1">Plumbing Line / Pipes</label>
                            <input type="text" x-model="form.mounting_work.washing_plumbing" placeholder="e.g. 1 inch CPVC with ball valves"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 text-xs bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-blue-800 mb-1">Motors / Booster Pump</label>
                            <input type="text" x-model="form.mounting_work.washing_motors" placeholder="e.g. 1 HP High pressure pump"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-slate-900 text-xs bg-white focus:outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Provision for Cabling Work</label>
                    <input type="text" x-model="form.mounting_work.cabling_provision" placeholder="e.g. UV conduits under module purlins with nylon ties"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Mounting Work Remarks</label>
                    <textarea x-model="form.mounting_work.mounting_remarks" rows="2" placeholder="Any special tilt or installation notes..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 4: Structure Work & Wind Safety -->
        <div x-show="currentStep === 4" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 4: Structure Work & Wind Safety</h3>
                    <p class="text-xs text-slate-500">Fabrication specifications, fixing method, and climatic safety.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Structure Materials *</label>
                        <input type="text" x-model="form.structure_work.materials" placeholder="e.g. Hot Dip Galvanized Iron / Alum"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Quality / Gauge *</label>
                        <input type="text" x-model="form.structure_work.quality" placeholder="e.g. 80 micron HDG / 2mm thickness"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Quantity (Columns/Purlins)</label>
                        <input type="text" x-model="form.structure_work.quantity" placeholder="e.g. 24 Columns, 48 Rafters"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Fixing Method (Civil / Chemical / Anchor) *</label>
                    <input type="text" x-model="form.structure_work.fixing" placeholder="e.g. M12 Anchor fasteners into RCC roof with waterproof chemical sealing"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div class="p-3.5 bg-amber-50/80 rounded-xl border border-amber-200">
                    <label class="block text-xs font-bold uppercase tracking-wider text-amber-950 mb-1">Structure Safety for Wind & Climatic Conditions *</label>
                    <p class="text-[11px] text-amber-800 mb-2">Detailed measures implemented to withstand wind gusting and heavy monsoon rain:</p>
                    <textarea x-model="form.structure_work.wind_safety_detailed" rows="3" placeholder="e.g. Diagonal cross-bracing installed between column rows; design rated for 150 km/h wind speed; SS 304 spring washers and nylon lock nuts installed."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Other Structure Remarks</label>
                    <textarea x-model="form.structure_work.structure_remarks" rows="2" placeholder="Any additional notes on roof waterproofing, pedestals, or orientation..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 5: Completed / Pending Work & Client Sign-off -->
        <div x-show="currentStep === 5" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 5: Completion Checklist & Sign-Off</h3>
                    <p class="text-xs text-slate-500">Record all completed structure tasks, pending items, and client verification.</p>
                </div>

                <!-- Completed Works 1 to 5 -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-800">Works All Completed (Detailed 1 to 5)</label>
                    <input type="text" x-model="form.safety_checklist.completed_1" placeholder="1. Column fixing and base anchoring completed" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.safety_checklist.completed_2" placeholder="2. Purlins and rafter frame assembled & torqued" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.safety_checklist.completed_3" placeholder="3. Modules mounted with mid and end clamps" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.safety_checklist.completed_4" placeholder="4. Washing plumbing and water connection tested" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.safety_checklist.completed_5" placeholder="5. Wind cross-bracing and roof sealing verified" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <!-- Pending Works 1 to 3 -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold uppercase tracking-wider text-rose-800">Work Pending (If Any)</label>
                    <input type="text" x-model="form.safety_checklist.pending_1" placeholder="1. (Leave blank or enter pending civil touches)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.safety_checklist.pending_2" placeholder="2." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.safety_checklist.pending_3" placeholder="3." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <!-- Client Sign-off & Signature Pad -->
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Work Acceptance Confirmation</span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Checked By (Client / Site Incharge Name)</label>
                            <input type="text" x-model="form.safety_checklist.checked_by_name" placeholder="Name of site person"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Contact Phone Number</label>
                            <input type="tel" x-model="form.safety_checklist.checked_by_phone" placeholder="Phone number"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                        </div>
                    </div>

                    <!-- Confirmation Checkbox -->
                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-amber-300 bg-amber-50/60 cursor-pointer">
                        <input type="checkbox" x-model="form.safety_checklist.client_confirmed" class="mt-0.5 w-4 h-4 text-amber-600 rounded">
                        <span class="text-xs text-slate-800 font-medium">
                            I confirm that I have inspected the structure and panel mounting work completed at the site and find it satisfactory.
                        </span>
                    </label>

                    <!-- Digital Signature Pad -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">Client / Incharge Signature</label>
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
                        <p class="text-[10px] text-slate-400 mt-1">Sign inside the box using your finger or stylus on mobile.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 6: Photos & Final Submit -->
        <div x-show="currentStep === 6" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 6: Photos & Submission</h3>
                    <p class="text-xs text-slate-500">Capture and upload site photographs for structure & module mounting.</p>
                </div>

                <!-- Photo Upload Tiles -->
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Panel Mounting</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'mounting_work', 'panel_mounting')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Structure Fixing</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'structure_work', 'structure_fixing')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Wind Bracing</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'structure_work', 'wind_safety')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Washing System</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'mounting_work', 'washing_system')">
                    </label>
                </div>

                <!-- Photos Gallery -->
                <div x-show="photos.length > 0" class="pt-3 border-t border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Uploaded Inspection Photos (<span x-text="photos.length"></span>)</span>
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

                <!-- Final Submit Card -->
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center space-y-2 mt-4">
                    <h4 class="text-sm font-extrabold text-emerald-950">Ready to Submit Structure Report?</h4>
                    <p class="text-xs text-emerald-800">Once submitted, this report will be dispatched to Admin for review and locked against edits.</p>
                    <button type="button" @click="confirmSubmit()"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-sm rounded-xl shadow-md transition-all">
                        Submit Structure Report
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
                <h3 class="text-base font-extrabold text-slate-900">Confirm Report Submission</h3>
                <p class="text-xs text-slate-600 mt-1">
                    Are you sure all structure & module mounting details and client signatures are captured?
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
function structureWizard(config) {
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
            'Site & Plant Info',
            'Panels Delivered',
            'Mounting & Washing',
            'Structure & Wind Safety',
            'Checklist & Sign-Off',
            'Photos & Submit'
        ],

        form: {
            site_plant_info: Object.assign({
                customer_name: '',
                customer_address: '',
                plant_capacity: '',
                module_make_model: '',
                service_date: '',
                technician_name: ''
            }, config.initialSections.site_plant_info || {}),

            panels_delivered: Object.assign({
                no_of_panels: '',
                panel_wattage: '',
                total_delivered_kwp: '',
                delivery_condition: 'Good Condition',
                delivery_remarks: ''
            }, config.initialSections.panels_delivered || {}),

            mounting_work: Object.assign({
                no_of_rows: '',
                no_of_strings: '',
                maintenance_space: '',
                washing_water_storage: '',
                washing_plumbing: '',
                washing_motors: '',
                cabling_provision: '',
                mounting_remarks: ''
            }, config.initialSections.mounting_work || {}),

            structure_work: Object.assign({
                materials: '',
                quality: '',
                quantity: '',
                fixing: '',
                wind_safety_detailed: '',
                structure_remarks: ''
            }, config.initialSections.structure_work || {}),

            safety_checklist: Object.assign({
                completed_1: '',
                completed_2: '',
                completed_3: '',
                completed_4: '',
                completed_5: '',
                pending_1: '',
                pending_2: '',
                pending_3: '',
                checked_by_name: '',
                checked_by_phone: '',
                client_confirmed: false,
                client_signature: '',
                client_signed_at: ''
            }, config.initialSections.safety_checklist || {})
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
                const existingData = this.form.safety_checklist.client_signature;
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
            } else if (this.form.safety_checklist.client_signature && !this.hasSignature) {
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.hasSignature = true;
                };
                img.src = this.form.safety_checklist.client_signature;
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
            this.form.safety_checklist.client_signature = canvas.toDataURL('image/png');
            this.form.safety_checklist.client_signed_at = new Date().toISOString();
            this.hasSignature = true;
            this.saveDraft(true);
        },

        clearSignature() {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            this.form.safety_checklist.client_signature = '';
            this.form.safety_checklist.client_signed_at = '';
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
