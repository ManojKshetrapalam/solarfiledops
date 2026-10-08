@extends('layouts.engineer')

@section('mobile_title', 'Site Survey: ' . $report->report_number)
@section('header_back_url', $report->service_id ? route('engineer.services.show', $report->service_id) : route('engineer.reports.index'))
@section('hide_bottom_nav', 'true')

@section('engineer_content')
<div x-data="siteInspectionWizard({
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

        <!-- STEP 1: Customer & Site Details -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Site Feasibility</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Step 1: Customer & Site Contact Details</h3>
                    <p class="text-xs text-slate-500">Record complete premises address, contact numbers, and site condition.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Name & Complete Address with Pincode *</label>
                    <input type="text" x-model="form.customer_site_details.customer_name" placeholder="Customer Name"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <textarea x-model="form.customer_site_details.customer_address" rows="2" placeholder="Full postal address and pincode..."
                              class="w-full mt-2 px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Directory Contacts -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-800">Site Stakeholders & Phone Directory</span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">Client / Owner Name & Phone</label>
                            <input type="text" x-model="form.customer_site_details.client_contact" placeholder="e.g. Ramesh Kumar - 9876543210" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">Main Incharge Name & Phone</label>
                            <input type="text" x-model="form.customer_site_details.main_incharge" placeholder="e.g. Suresh (Manager) - 9876500001" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">Site Incharge Name & Phone</label>
                            <input type="text" x-model="form.customer_site_details.site_incharge" placeholder="e.g. Anand - 9876500002" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-600 mb-1">Maintenance / Caretaker Name & Phone</label>
                            <input type="text" x-model="form.customer_site_details.caretaker_contact" placeholder="e.g. Manjunath - 9876500003" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Site Condition *</label>
                        <select x-model="form.customer_site_details.site_condition" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs bg-white">
                            <option value="Existing">Existing Built Premises</option>
                            <option value="Under Construction">Under Construction</option>
                            <option value="Proposed">Proposed Project</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Building Details *</label>
                        <select x-model="form.customer_site_details.building_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs bg-white">
                            <option value="Residence">Residence</option>
                            <option value="School / College">School / College</option>
                            <option value="Convent / Hostel">Convent / Hostel</option>
                            <option value="Commercial">Commercial Facility</option>
                            <option value="Industrial / Factory">Industrial / Factory</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Power Requirements & Load Sanctioned / EB Bills -->
        <div x-show="currentStep === 2" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 2: Solar Requirements & EB Sanction</h3>
                    <p class="text-xs text-slate-500">Proposed plant sizing, EB sanction load, meters, and day/night load consumption.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Solar Power Requirements *</label>
                    <textarea x-model="form.power_req_meters.solar_req_details" rows="2" placeholder="e.g. 50 kW On-Grid Solar Power Plant with Net Metering / Solar Street Lights..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Sanction Load & Meter Details (With 12/6/3 months EB bills) -->
                <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="block text-xs font-bold uppercase tracking-wider text-blue-950">Load Sanctioned with Meter Numbers</span>
                        <span class="text-[10px] text-blue-800 font-semibold">Attach 12m / 6m / 3m EB Bills</span>
                    </div>

                    <!-- Meter 1 -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">1) Sanctioned kW</label>
                            <input type="text" x-model="form.power_req_meters.meter1_kw" placeholder="e.g. 65 kW" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Phase (3P / 1P)</label>
                            <select x-model="form.power_req_meters.meter1_phase" class="w-full px-2 py-1.5 border border-slate-300 rounded bg-white">
                                <option value="3-Phase">3-Phase (415V)</option>
                                <option value="1-Phase">1-Phase (230V)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Meter Number</label>
                            <input type="text" x-model="form.power_req_meters.meter1_number" placeholder="e.g. EB-893140" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        </div>
                    </div>

                    <!-- Meter 2 -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">2) Sanctioned kW</label>
                            <input type="text" x-model="form.power_req_meters.meter2_kw" placeholder="e.g. 25 kW" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Phase</label>
                            <select x-model="form.power_req_meters.meter2_phase" class="w-full px-2 py-1.5 border border-slate-300 rounded bg-white">
                                <option value="3-Phase">3-Phase</option>
                                <option value="1-Phase">1-Phase</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Meter Number</label>
                            <input type="text" x-model="form.power_req_meters.meter2_number" placeholder="Meter 2 No." class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        </div>
                    </div>

                    <!-- Meter 3 -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">3) Sanctioned kW</label>
                            <input type="text" x-model="form.power_req_meters.meter3_kw" placeholder="kW" class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Phase</label>
                            <select x-model="form.power_req_meters.meter3_phase" class="w-full px-2 py-1.5 border border-slate-300 rounded bg-white">
                                <option value="3-Phase">3-Phase</option>
                                <option value="1-Phase">1-Phase</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500">Meter Number</label>
                            <input type="text" x-model="form.power_req_meters.meter3_number" placeholder="Meter 3 No." class="w-full px-2 py-1.5 border border-slate-300 rounded">
                        </div>
                    </div>
                </div>

                <!-- Load Consumption (Day & Night) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Day Time Load Details *</label>
                        <textarea x-model="form.power_req_meters.day_load_details" rows="2" placeholder="e.g. HVAC, machine motors, manufacturing shed (avg 38 kW continuous)"
                                  class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Night Time Load Details *</label>
                        <textarea x-model="form.power_req_meters.night_load_details" rows="2" placeholder="e.g. Lighting, cold room refrigeration, perimeter security (avg 12 kW)"
                                  class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: Cabling, Conduits & Routing Requirements -->
        <div x-show="currentStep === 3" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 3: Cabling & Conduit Routing</h3>
                    <p class="text-xs text-slate-500">Measurements, cable sizing, and conduit piping requirements.</p>
                </div>

                <!-- Cabling Required 1 to 5 -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800">Cabling Required (Configuration & Measurements 1 to 5)</label>
                    <input type="text" x-model="form.cabling_conduits.cable_req_1" placeholder="1. Solar DC 4 sq.mm copper (approx 250 meters)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.cable_req_2" placeholder="2. Main DC cable 16 sq.mm (approx 40 meters from roof to inverter)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.cable_req_3" placeholder="3. AC cable 4C x 35 sq.mm Al armored (approx 55 meters to main LT panel)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.cable_req_4" placeholder="4. Earthing copper/GI strip 25x3mm (approx 60 meters)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.cable_req_5" placeholder="5. Communication cable CAT6 / RS485 (approx 30 meters)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <!-- Conduit Piping Requirements 1 to 5 -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800">Conduit Piping Requirements (Sizes & Measurements 1 to 5)</label>
                    <input type="text" x-model="form.cabling_conduits.conduit_req_1" placeholder="1. 25mm UV resistant rigid PVC conduits (120 meters)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.conduit_req_2" placeholder="2. 50mm GI cable tray with covers (40 meters)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.conduit_req_3" placeholder="3. Flexible conduits with glands at entry/exit points" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.conduit_req_4" placeholder="4." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.cabling_conduits.conduit_req_5" placeholder="5." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Other Cabling & Routing Requirements</label>
                    <textarea x-model="form.cabling_conduits.other_req" rows="2" placeholder="Wall puncture, riser shafts, civil core cutting requirements..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 4: Earthing, Protection & Installation Rooms -->
        <div x-show="currentStep === 4" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 4: Earthing & Room Feasibility</h3>
                    <p class="text-xs text-slate-500">Earthing pits space, lightning arrestor, PCU and battery room ventilation.</p>
                </div>

                <!-- Earthing Requirements -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">DC Earthing Requirements *</label>
                        <textarea x-model="form.earthing_rooms_protection.dc_earthing" rows="2" placeholder="e.g. 2 Chemical earth pits (2m deep) required in North-East courtyard"
                                  class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">AC Earthing Requirements *</label>
                        <textarea x-model="form.earthing_rooms_protection.ac_earthing" rows="2" placeholder="e.g. 1 Dedicated earth pit near main LT panel room"
                                  class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                    </div>
                </div>

                <!-- Lightning Arrestor -->
                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="block text-xs font-bold uppercase tracking-wider text-amber-950">Lightning Arrestor (LA) Details</span>
                        <select x-model="form.earthing_rooms_protection.la_status" class="px-2 py-1 text-xs rounded border border-slate-300 bg-white">
                            <option value="To Be Done / New">To Be Installed (New)</option>
                            <option value="Existing Adequate">Existing Adequate</option>
                            <option value="Existing Needs Upgrade">Existing Needs Upgrade</option>
                        </select>
                    </div>
                    <input type="text" x-model="form.earthing_rooms_protection.la_req_1" placeholder="1. ESE Early Streamer / Conventional Franklin rod required on roof mast" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                    <input type="text" x-model="form.earthing_rooms_protection.la_req_2" placeholder="2. Down conductor 25x3mm GI strip route to dedicated LA pit" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                </div>

                <!-- System (PCU) Installation Place -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">System (PCU) Room Feasibility & Measurements *</label>
                    <textarea x-model="form.earthing_rooms_protection.pcu_room_details" rows="2" placeholder="e.g. Electrical room available on ground floor (10ft x 8ft x 10ft height); good cross ventilation; protected from rain and direct sunlight."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Battery Installation Place -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Battery Installation Area Feasibility & Measurements *</label>
                    <textarea x-model="form.earthing_rooms_protection.battery_room_details" rows="2" placeholder="e.g. Ground floor ventilated storeroom with exhaust provision; cool dry ambient room; space for 2-tier stand (6ft x 4ft space)."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 5: Rooftop Structure, Logistics & Sign-off -->
        <div x-show="currentStep === 5" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 5: Rooftop Structure & Logistics</h3>
                    <p class="text-xs text-slate-500">Roof type, usable area measurements, crane/lifting access, and technical notes.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rooftop Type *</label>
                        <select x-model="form.rooftop_logistics.roof_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs bg-white">
                            <option value="RCC Flat Roof">RCC Flat Roof</option>
                            <option value="PUFF Sheet Roof">PUFF Sheet Roof</option>
                            <option value="Tin / Metal Trapezoidal Shed">Tin / Metal Shed</option>
                            <option value="Elevated Structured">Elevated Structure</option>
                            <option value="Ground Mounted">Ground Mounted</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rooftop Measurements / Usable Area *</label>
                        <input type="text" x-model="form.rooftop_logistics.roof_measurements" placeholder="e.g. 5500 sq.ft clear south-facing area"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rooftop Condition & Shadow Analysis</label>
                    <textarea x-model="form.rooftop_logistics.shadow_analysis" rows="2" placeholder="e.g. Parapet wall 3ft on East; water tank shadow on NW corner between 9am-10am; 0% shadow from 10am to 4:30pm."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Logistics & Lifting -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Materials Lifting Method *</label>
                        <input type="text" x-model="form.rooftop_logistics.lifting_method" placeholder="e.g. Hydraulic crane from road / Internal service staircase"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Vehicle Entrance & Unloading</label>
                        <input type="text" x-model="form.rooftop_logistics.vehicle_entrance" placeholder="e.g. 20-ton truck can enter main gate directly to loading dock"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Technical Notes -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800">T.C. / Technical Inspection Report Notes</label>
                    <input type="text" x-model="form.rooftop_logistics.tc_notes_1" placeholder="1. Roof structural stability verified; suitable for anchor fastner fixing" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.rooftop_logistics.tc_notes_2" placeholder="2. Parapet waterproofing needs chemical coat around pedestal locations" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <!-- Client Sign-off -->
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Survey Sign-Off & Confirmation</span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Survey Done With (Client / Incharge)</label>
                            <input type="text" x-model="form.rooftop_logistics.survey_with_name" placeholder="Name of person present" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Contact Phone</label>
                            <input type="tel" x-model="form.rooftop_logistics.survey_with_phone" placeholder="Phone number" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                        </div>
                    </div>

                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-amber-300 bg-amber-50/60 cursor-pointer">
                        <input type="checkbox" x-model="form.rooftop_logistics.client_confirmed" class="mt-0.5 w-4 h-4 text-amber-600 rounded">
                        <span class="text-xs text-slate-800 font-medium">
                            I confirm that the site inspection survey for solar feasibility has been conducted at our premises.
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
                    <h3 class="text-sm font-bold text-slate-900">Step 6: Site Photographs & EB Bills</h3>
                    <p class="text-xs text-slate-500">Upload rooftop photos, inverter room, battery room, lifting area, and EB bills.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Rooftop Survey</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'rooftop_logistics', 'rooftop')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Inverter / PCU Room</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'earthing_rooms_protection', 'pcu_room')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">Lifting & Unloading</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'rooftop_logistics', 'lifting_place')">
                    </label>

                    <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                        <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-800">EB Electricity Bill</span>
                        <span class="text-[10px] text-slate-500">Snap photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'power_req_meters', 'eb_bill')">
                    </label>
                </div>

                <div x-show="photos.length > 0" class="mt-3">
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
                    <h4 class="text-sm font-extrabold text-emerald-950">Ready to Submit Site Survey?</h4>
                    <p class="text-xs text-emerald-800">This sends the complete site feasibility report to operations for plant design approval.</p>
                    <button type="button" @click="confirmSubmit()"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-sm rounded-xl shadow-md transition-all">
                        Submit Site Inspection Report
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
                <h3 class="text-base font-extrabold text-slate-900">Confirm Site Inspection Submission</h3>
                <p class="text-xs text-slate-600 mt-1">
                    Are you sure all load sanctioned data, rooftop measurements, and photos are complete?
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
function siteInspectionWizard(config) {
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
            'Site & Contacts',
            'Power & EB Meters',
            'Cabling & Conduits',
            'Earthing & Rooms',
            'Rooftop & Logistics',
            'Photos & Submit'
        ],

        form: {
            customer_site_details: Object.assign({
                customer_name: '',
                customer_address: '',
                client_contact: '',
                main_incharge: '',
                site_incharge: '',
                caretaker_contact: '',
                site_condition: 'Existing',
                building_type: 'Commercial'
            }, config.initialSections.customer_site_details || {}),

            power_req_meters: Object.assign({
                solar_req_details: '',
                meter1_kw: '', meter1_phase: '3-Phase', meter1_number: '',
                meter2_kw: '', meter2_phase: '3-Phase', meter2_number: '',
                meter3_kw: '', meter3_phase: '3-Phase', meter3_number: '',
                day_load_details: '',
                night_load_details: ''
            }, config.initialSections.power_req_meters || {}),

            cabling_conduits: Object.assign({
                cable_req_1: '', cable_req_2: '', cable_req_3: '', cable_req_4: '', cable_req_5: '',
                conduit_req_1: '', conduit_req_2: '', conduit_req_3: '', conduit_req_4: '', conduit_req_5: '',
                other_req: ''
            }, config.initialSections.cabling_conduits || {}),

            earthing_rooms_protection: Object.assign({
                dc_earthing: '',
                ac_earthing: '',
                la_status: 'To Be Done / New',
                la_req_1: '', la_req_2: '',
                pcu_room_details: '',
                battery_room_details: ''
            }, config.initialSections.earthing_rooms_protection || {}),

            rooftop_logistics: Object.assign({
                roof_type: 'RCC Flat Roof',
                roof_measurements: '',
                shadow_analysis: '',
                lifting_method: '',
                vehicle_entrance: '',
                tc_notes_1: '', tc_notes_2: '',
                survey_with_name: '',
                survey_with_phone: '',
                client_confirmed: false,
                client_signature: '',
                client_signed_at: ''
            }, config.initialSections.rooftop_logistics || {})
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
                const existingData = this.form.rooftop_logistics.client_signature;
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
            } else if (this.form.rooftop_logistics.client_signature && !this.hasSignature) {
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.hasSignature = true;
                };
                img.src = this.form.rooftop_logistics.client_signature;
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
            this.form.rooftop_logistics.client_signature = canvas.toDataURL('image/png');
            this.form.rooftop_logistics.client_signed_at = new Date().toISOString();
            this.hasSignature = true;
            this.saveDraft(true);
        },

        clearSignature() {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            this.form.rooftop_logistics.client_signature = '';
            this.form.rooftop_logistics.client_signed_at = '';
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
