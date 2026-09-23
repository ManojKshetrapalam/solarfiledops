@extends('layouts.engineer')

@section('mobile_title', 'Fill Service Report')

@section('engineer_content')
<div x-data="reportWizard({
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
    class="pb-24">

    <!-- Top Sticky Progress Bar & Step Header -->
    <div class="sticky top-14 z-30 bg-slate-900 text-white -mx-4 px-4 py-2.5 shadow-md border-b border-slate-800">
        <div class="flex items-center justify-between text-xs mb-1.5">
            <div class="flex items-center gap-1.5">
                <span class="font-mono text-amber-400 font-bold">{{ $report->report_number }}</span>
                <span class="text-slate-400">&bull;</span>
                <span class="text-slate-300 font-semibold" x-text="stepTitles[currentStep - 1]"></span>
            </div>
            <span class="text-xs font-bold text-amber-400" x-text="'Step ' + currentStep + ' of 10'"></span>
        </div>

        <!-- Progress Bar Line -->
        <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
            <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-300" 
                 :style="'width: ' + ((currentStep / 10) * 100) + '%'"></div>
        </div>

        <!-- Draft status toast badge -->
        <div class="flex items-center justify-between text-[11px] mt-1.5 text-slate-400">
            <span x-text="saveStatus" :class="saveStatusColor"></span>
            <span class="text-slate-400 truncate max-w-[200px]">{{ $report->customer->name }}</span>
        </div>
    </div>

    <!-- Admin Correction Note Warning Banner (if resubmitting) -->
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

        <!-- STEP 1: Customer & Service Details -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 1: Customer & Service Details</h3>
                    <p class="text-xs text-slate-500">Verify customer information and key contact phone numbers.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Name & Site Address</label>
                    <input type="text" x-model="form.customer_details.customer_name" readonly
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-medium">
                    <textarea x-model="form.customer_details.customer_address" rows="2" readonly
                              class="w-full mt-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-medium"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Service Date *</label>
                        <input type="date" x-model="form.customer_details.service_date"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Service Time *</label>
                        <input type="time" x-model="form.customer_details.service_time"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Phone Directory / Contacts</label>
                    
                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block mb-0.5">Head / Management Phone</span>
                        <input type="tel" x-model="form.customer_details.phone_head" placeholder="e.g. +91 94444 11111"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block mb-0.5">Site In Charge Phone</span>
                        <input type="tel" x-model="form.customer_details.phone_incharge" placeholder="e.g. +91 94444 22222"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block mb-0.5">Maintenance Department Phone</span>
                        <input type="tel" x-model="form.customer_details.phone_maintenance" placeholder="e.g. Maintenance manager phone"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block mb-0.5">Others / Security Phone</span>
                        <input type="tel" x-model="form.customer_details.phone_others" placeholder="Optional additional phone"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: System Details -->
        <div x-show="currentStep === 2" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 2: System Details</h3>
                    <p class="text-xs text-slate-500">Record plant installation metrics and commissioned capacity.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">System Capacity Installed *</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="form.system_details.system_capacity" placeholder="e.g. 100 kW / 25 kWp"
                               class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Date of Installation / Commissioning</label>
                    <input type="date" x-model="form.system_details.date_of_installation"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Plant Type / Configuration</label>
                    <select x-model="form.system_details.plant_type"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                        <option value="grid_tied">Grid-Tied (On-Grid)</option>
                        <option value="off_grid">Off-Grid with Battery Bank</option>
                        <option value="hybrid">Hybrid (Grid + Storage)</option>
                        <option value="solar_pump">Solar Water Pump</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- STEP 3: Solar Module Inspection -->
        <div x-show="currentStep === 3" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 3: Solar Module Inspection & Testing</h3>
                    <p class="text-xs text-slate-500">Record PV panel physical condition, meter readings, and cleaning status.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Solar Module Condition *</label>
                    <select x-model="form.module_inspection.condition"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                        <option value="good">Good Condition / Clean</option>
                        <option value="fair_dusty">Fair (Dust / Soil Accumulation)</option>
                        <option value="heavy_soiling">Heavy Soiling (Cleaning Required)</option>
                        <option value="cracked_damaged">Micro-Cracks / Hotspots / Physical Damage</option>
                    </select>
                </div>

                <!-- Meter Readings -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Meter Readings Conducted</span>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-1">Current (Amps)</span>
                            <input type="text" x-model="form.module_inspection.meter_amps" placeholder="e.g. 18.4 A"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-1">Reading Time (Amps)</span>
                            <input type="time" x-model="form.module_inspection.meter_amps_time"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-1">Voltage (Volts)</span>
                            <input type="text" x-model="form.module_inspection.meter_volts" placeholder="e.g. 415 V"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-1">Reading Time (Volts)</span>
                            <input type="time" x-model="form.module_inspection.meter_volts_time"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Cleaning Verification & Camera Capture -->
                <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Module Cleaning Verification Photo</span>
                    <p class="text-xs text-slate-600">Take a high-resolution photograph of the cleaned module array.</p>

                    <!-- Camera Upload Control -->
                    <div class="flex items-center gap-2">
                        <label class="flex-1 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>+ Take / Upload Photo</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" 
                                   @change="handlePhotoUpload($event, 'module_inspection', 'cleaning_photo')">
                        </label>
                    </div>

                    <!-- Photo Thumbnails in this section -->
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <template x-for="p in getPhotosBySection('module_inspection')" :key="p.id">
                            <div class="relative bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs p-1">
                                <img :src="p.url" class="w-full h-24 object-cover rounded-lg">
                                <button type="button" @click="deletePhoto(p.id)" class="absolute top-2 right-2 bg-rose-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-md">✕</button>
                                <span class="text-[10px] text-slate-500 block truncate mt-1 px-1" x-text="p.filename"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Module Remarks</label>
                    <textarea x-model="form.module_inspection.remarks" rows="2" placeholder="Observations regarding cabling, shading, hotspots or junction boxes"
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 4: Structure Inspection -->
        <div x-show="currentStep === 4" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 4: Structure Inspection</h3>
                    <p class="text-xs text-slate-500">Inspect mounting structure rigidity, materials, and fastener torque.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Structure Condition *</label>
                    <select x-model="form.structure_inspection.condition"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                        <option value="stable_rigid">Stable & Rigid (No Corrosion)</option>
                        <option value="mild_rust">Mild Surface Rust / Oxidation</option>
                        <option value="loose_fasteners">Loose Fasteners / Bolts Detected</option>
                        <option value="structurally_compromised">Structurally Compromised / Needs Reinforcement</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Materials Used *</label>
                    <input type="text" x-model="form.structure_inspection.materials_used" placeholder="e.g. Hot Dip Galvanized Steel / Aluminium Rails"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Structure Remarks</label>
                    <textarea x-model="form.structure_inspection.remarks" rows="3" placeholder="Notes on wind deflection, foundation anchor bolts, or grouting stability"
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 5: Power Conditioning Unit (PCU / Inverter) -->
        <div x-show="currentStep === 5" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 5: Power Conditioning Unit (PCU / Inverter)</h3>
                    <p class="text-xs text-slate-500">Record inverter 3-phase output voltages, solar readings, and operating condition.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Capacity (kVA/kW) *</label>
                        <input type="text" x-model="form.pcu_inspection.capacity" placeholder="e.g. 50 kVA"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phase Type *</label>
                        <select x-model="form.pcu_inspection.phase"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                            <option value="three_phase">Three Phase (415V)</option>
                            <option value="single_phase">Single Phase (230V)</option>
                        </select>
                    </div>
                </div>

                <!-- 3-Phase Output Voltage readings -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Output Voltages</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-0.5">Phase I (V)</span>
                            <input type="text" x-model="form.pcu_inspection.voltage_phase_1" placeholder="e.g. 235"
                                   class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-0.5">Phase II (V)</span>
                            <input type="text" x-model="form.pcu_inspection.voltage_phase_2" placeholder="e.g. 238"
                                   class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-600 font-semibold block mb-0.5">Phase III (V)</span>
                            <input type="text" x-model="form.pcu_inspection.voltage_phase_3" placeholder="e.g. 236"
                                   class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Operating Current (A)</label>
                        <input type="text" x-model="form.pcu_inspection.current" placeholder="e.g. 42 A"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Inverter Condition</label>
                        <select x-model="form.pcu_inspection.condition"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                            <option value="normal_operational">Normal / Operational</option>
                            <option value="warning_derating">High Temperature / Derating</option>
                            <option value="error_fault">Error Code / Tripped</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Solar Readings</label>
                        <input type="text" x-model="form.pcu_inspection.solar_readings" placeholder="e.g. 48.2 kWh"
                               class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Array String (V)</label>
                        <input type="text" x-model="form.pcu_inspection.array_voltage" placeholder="e.g. 620 V"
                               class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Battery (V)</label>
                        <input type="text" x-model="form.pcu_inspection.battery_voltage" placeholder="e.g. 54.2 V"
                               class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">PCU Remarks</label>
                    <textarea x-model="form.pcu_inspection.remarks" rows="2" placeholder="Heat sink ventilation, error logs, firmware version"
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 6: Battery Inspection -->
        <div x-show="currentStep === 6" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 6: Battery Bank Inspection</h3>
                    <p class="text-xs text-slate-500">Record battery bank health, electrolyte levels, and connector integrity.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Battery Capacity</label>
                        <input type="text" x-model="form.battery_inspection.battery_capacity" placeholder="e.g. 150 Ah / 48V"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Number of Batteries</label>
                        <input type="number" x-model="form.battery_inspection.number_of_batteries" placeholder="e.g. 4 / 16"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Battery Voltage (V)</label>
                        <input type="text" x-model="form.battery_inspection.battery_voltage" placeholder="e.g. 52.8 V"
                               class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Water Before (L)</label>
                        <input type="text" x-model="form.battery_inspection.distilled_water_before" placeholder="e.g. Low / 2L"
                               class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Water After (L)</label>
                        <input type="text" x-model="form.battery_inspection.distilled_water_after" placeholder="e.g. Full / 5L"
                               class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Battery Connectors</label>
                        <select x-model="form.battery_inspection.battery_connectors"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                            <option value="good_greased">Clean & Petroleum Jelly Applied</option>
                            <option value="sulphated">Sulphation / White Residue Present</option>
                            <option value="loose_corroded">Loose / Corroded Connectors</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Battery Stand Condition</label>
                        <select x-model="form.battery_inspection.battery_stand_condition"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                            <option value="stable">Sturdy & Free of Acid Damage</option>
                            <option value="needs_painting">Acid Stained / Needs Painting</option>
                            <option value="damaged">Damaged / Unstable</option>
                        </select>
                    </div>
                </div>

                <!-- Battery Cleaning Photo Upload -->
                <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200 space-y-2">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Battery Cleaning & Terminal Photo</span>
                    <label class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span>+ Capture Battery Photo</span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" 
                               @change="handlePhotoUpload($event, 'battery_inspection', 'battery_photo')">
                    </label>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <template x-for="p in getPhotosBySection('battery_inspection')" :key="p.id">
                            <div class="relative bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs p-1">
                                <img :src="p.url" class="w-full h-24 object-cover rounded-lg">
                                <button type="button" @click="deletePhoto(p.id)" class="absolute top-2 right-2 bg-rose-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-md">✕</button>
                                <span class="text-[10px] text-slate-500 block truncate mt-1 px-1" x-text="p.filename"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Battery Remarks</label>
                    <textarea x-model="form.battery_inspection.remarks" rows="2" placeholder="Specific gravity readings, cell temperature, or equalization status"
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 7: Complaint Details -->
        <div x-show="currentStep === 7" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 7: Customer Complaint & Breakdown Record</h3>
                    <p class="text-xs text-slate-500">If attending a complaint or repair call, record reported fault and rectification actions.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Details of Complaint</label>
                    <textarea x-model="form.complaint_details.complaint_details" rows="3" placeholder="Customer reported issues (e.g. Inverter tripping during peak midday hours)"
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rectified Report (Detailed Actions Taken)</label>
                    <textarea x-model="form.complaint_details.rectified_report_detailed" rows="4" placeholder="Describe root cause diagnosis, components replaced, electrical re-wiring or firmware adjustments made"
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 8: General Remarks & Sign-off -->
        <div x-show="currentStep === 8" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 8: General Remarks & Sign-Off</h3>
                    <p class="text-xs text-slate-500">Record overall plant health notes and customer representative identity.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">General Observations & Recommendations</label>
                    <textarea x-model="form.remarks.general_remarks" rows="3" placeholder="e.g. Recommended tree trimming on south border to eliminate 15% afternoon shading loss."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Service Done By (Auto-populated engineer identity) -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                    <span class="block font-bold text-slate-900 uppercase tracking-wider mb-2">Service Done By (Engineer)</span>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div>
                            <span class="font-bold text-slate-900 block text-sm">{{ auth()->user()->name }}</span>
                            <span class="text-slate-500 text-[11px]">{{ auth()->user()->designation }} &bull; {{ auth()->user()->employee_code }}</span>
                        </div>
                    </div>
                </div>

                <!-- Checked By (Customer Representative on Site) -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <span class="block font-bold text-slate-900 uppercase tracking-wider text-xs">Checked By (Customer / Site Representative)</span>
                    
                    <div>
                        <span class="text-[11px] font-semibold text-slate-600 block mb-1">Representative Name *</span>
                        <input type="text" x-model="form.remarks.checked_by_name" placeholder="e.g. Plant Manager Suresh"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <span class="text-[11px] font-semibold text-slate-600 block mb-1">Representative Phone Number *</span>
                        <input type="tel" x-model="form.remarks.checked_by_phone" placeholder="e.g. +91 94444 22222"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <span class="text-[11px] font-semibold text-slate-600 block mb-1">Customer Sign-off Notes</span>
                        <input type="text" x-model="form.remarks.checked_by_notes" placeholder="e.g. System tested and found in full working order"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 9: Photos & Documents Gallery -->
        <div x-show="currentStep === 9" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 9: Photos & Field Documents</h3>
                    <p class="text-xs text-slate-500">Capture plant site photos, inverters, meters, and upload checklists.</p>
                </div>

                <!-- Add Photo Action -->
                <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-900">Capture Site Photo</span>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <select x-model="uploadCategory" class="px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="general">General Site Photo</option>
                            <option value="module_inspection">Module Array</option>
                            <option value="pcu_inspection">Inverter / PCU</option>
                            <option value="battery_inspection">Battery Bank</option>
                            <option value="meter_readings">Meters & Display</option>
                        </select>

                        <label class="py-2.5 px-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span>+ Take Photo</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" 
                                   @change="handlePhotoUpload($event, uploadCategory, uploadCategory)">
                        </label>
                    </div>
                </div>

                <!-- Photo Gallery Grid -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Uploaded Photographs (<span x-text="photos.length"></span>)</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="p in photos" :key="p.id">
                            <div class="relative bg-slate-50 rounded-xl border border-slate-200 overflow-hidden shadow-xs">
                                <img :src="p.url" class="w-full h-32 object-cover">
                                <button type="button" @click="deletePhoto(p.id)" 
                                        class="absolute top-2 right-2 bg-rose-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-md">✕</button>
                                <div class="p-2">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-200 text-slate-800 block mb-0.5" x-text="p.section_key"></span>
                                    <p class="text-[10px] text-slate-400" x-text="p.captured_at"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 10: Review & Submit -->
        <div x-show="currentStep === 10" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 10: Final Review & Submission</h3>
                    <p class="text-xs text-slate-500">Please review all report details below before final submission to operations admin.</p>
                </div>

                <!-- Review Accordion Cards -->
                <div class="space-y-2 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">1. Customer & Contacts</span>
                        <p class="text-slate-600">Customer: <strong class="text-slate-800" x-text="form.customer_details.customer_name"></strong></p>
                        <p class="text-slate-600">Date/Time: <span x-text="form.customer_details.service_date + ' ' + form.customer_details.service_time"></span></p>
                        <p class="text-slate-600">Site Phone: <span x-text="form.customer_details.phone_incharge || 'N/A'"></span></p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">2. System Details</span>
                        <p class="text-slate-600">Capacity: <strong class="text-slate-800" x-text="form.system_details.system_capacity || 'Not specified'"></strong></p>
                        <p class="text-slate-600">Type: <span x-text="form.system_details.plant_type"></span></p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">3. Module & Meter Readings</span>
                        <p class="text-slate-600">Condition: <span x-text="form.module_inspection.condition"></span></p>
                        <p class="text-slate-600">Current: <span x-text="form.module_inspection.meter_amps || '—'"></span> &bull; Voltage: <span x-text="form.module_inspection.meter_volts || '—'"></span></p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">4. Power Conditioning Unit</span>
                        <p class="text-slate-600">Phase Voltages: <span x-text="form.pcu_inspection.voltage_phase_1 + 'V / ' + form.pcu_inspection.voltage_phase_2 + 'V / ' + form.pcu_inspection.voltage_phase_3 + 'V'"></span></p>
                        <p class="text-slate-600">Condition: <span x-text="form.pcu_inspection.condition"></span></p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">5. Battery Bank</span>
                        <p class="text-slate-600">Capacity / Voltage: <span x-text="(form.battery_inspection.battery_capacity || '—') + ' / ' + (form.battery_inspection.battery_voltage || '—')"></span></p>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">6. Checked & Sign-off</span>
                        <p class="text-slate-600">Representative: <strong class="text-slate-800" x-text="form.remarks.checked_by_name || 'Pending'"></strong> (<span x-text="form.remarks.checked_by_phone || '—'"></span>)</p>
                        <p class="text-slate-600">Photos Uploaded: <strong class="text-amber-600" x-text="photos.length + ' photos attached'"></strong></p>
                    </div>
                </div>

                <!-- Submit Warning -->
                <div class="bg-amber-50 border border-amber-200 p-3.5 rounded-xl text-amber-900 text-xs">
                    <p class="font-bold">Important Notice:</p>
                    <p class="mt-0.5">Once submitted, this report will be forwarded to the Administrator for verification and approval. You will not be able to modify it unless corrections are explicitly requested by Admin.</p>
                </div>
            </div>
        </div>

        <!-- Hidden input for JSON synchronization -->
        <input type="hidden" name="sections[customer_details]" :value="JSON.stringify(form.customer_details)">
        <input type="hidden" name="sections[system_details]" :value="JSON.stringify(form.system_details)">
        <input type="hidden" name="sections[module_inspection]" :value="JSON.stringify(form.module_inspection)">
        <input type="hidden" name="sections[structure_inspection]" :value="JSON.stringify(form.structure_inspection)">
        <input type="hidden" name="sections[pcu_inspection]" :value="JSON.stringify(form.pcu_inspection)">
        <input type="hidden" name="sections[battery_inspection]" :value="JSON.stringify(form.battery_inspection)">
        <input type="hidden" name="sections[complaint_details]" :value="JSON.stringify(form.complaint_details)">
        <input type="hidden" name="sections[remarks]" :value="JSON.stringify(form.remarks)">
    </form>

    <!-- Bottom Sticky Navigation Toolbar -->
    <div class="fixed bottom-14 inset-x-0 z-40 bg-white border-t border-slate-200 p-3 shadow-lg max-w-lg mx-auto">
        <div class="flex items-center justify-between gap-2">
            <!-- Previous Button -->
            <button type="button" @click="prevStep()" :disabled="currentStep === 1"
                    class="py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors">
                &larr; Prev
            </button>

            <!-- Save Draft (Always Visible) -->
            <button type="button" @click="saveDraft(false)" 
                    class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Save Draft</span>
            </button>

            <!-- Next or Submit -->
            <template x-if="currentStep < 10">
                <button type="button" @click="nextStep()"
                        class="py-2.5 px-5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                    Next &rarr;
                </button>
            </template>

            <template x-if="currentStep === 10">
                <button type="button" @click="confirmSubmit()"
                        class="py-2.5 px-5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md transition-all">
                    Submit Report &check;
                </button>
            </template>
        </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-200 space-y-4">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Submit Service Report?</h3>
                <p class="text-xs text-slate-500 mt-1">Are you sure you want to submit this report? It will be sent to the administrator for review.</p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" @click="showConfirmModal = false" class="flex-1 py-2.5 px-4 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button" @click="doSubmit()" class="flex-1 py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-md">
                    Yes, Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function reportWizard(config) {
    return {
        reportId: config.reportId,
        currentStep: config.currentStep || 1,
        saveDraftUrl: config.saveDraftUrl,
        uploadPhotoUrl: config.uploadPhotoUrl,
        csrfToken: config.csrfToken,
        showConfirmModal: false,
        saveStatus: 'All changes saved',
        saveStatusColor: 'text-slate-400',
        uploadCategory: 'general',
        photos: config.existingPhotos || [],

        stepTitles: [
            '1. Customer Details',
            '2. System Details',
            '3. Solar Module Inspection',
            '4. Structure Inspection',
            '5. PCU / Inverter',
            '6. Battery Inspection',
            '7. Complaint Details',
            '8. Remarks & Sign-off',
            '9. Photos & Documents',
            '10. Review & Submit'
        ],

        form: {
            customer_details: {
                customer_name: '',
                customer_address: '',
                service_date: '{{ date('Y-m-d') }}',
                service_time: '{{ date('H:i') }}',
                phone_head: '',
                phone_incharge: '',
                phone_maintenance: '',
                phone_others: '',
                ...(config.initialSections.customer_details || {})
            },
            system_details: {
                system_capacity: '100 kW',
                date_of_installation: '',
                plant_type: 'grid_tied',
                ...(config.initialSections.system_details || {})
            },
            module_inspection: {
                condition: 'good',
                meter_amps: '',
                meter_amps_time: '',
                meter_volts: '',
                meter_volts_time: '',
                remarks: '',
                ...(config.initialSections.module_inspection || {})
            },
            structure_inspection: {
                condition: 'stable_rigid',
                materials_used: 'Hot Dip Galvanized Steel',
                remarks: '',
                ...(config.initialSections.structure_inspection || {})
            },
            pcu_inspection: {
                capacity: '',
                phase: 'three_phase',
                voltage_phase_1: '',
                voltage_phase_2: '',
                voltage_phase_3: '',
                current: '',
                condition: 'normal_operational',
                solar_readings: '',
                array_voltage: '',
                battery_voltage: '',
                remarks: '',
                ...(config.initialSections.pcu_inspection || {})
            },
            battery_inspection: {
                battery_capacity: '',
                number_of_batteries: '',
                battery_voltage: '',
                distilled_water_before: '',
                distilled_water_after: '',
                battery_connectors: 'good_greased',
                battery_stand_condition: 'stable',
                remarks: '',
                ...(config.initialSections.battery_inspection || {})
            },
            complaint_details: {
                complaint_details: '',
                rectified_report_detailed: '',
                ...(config.initialSections.complaint_details || {})
            },
            remarks: {
                general_remarks: '',
                checked_by_name: '',
                checked_by_phone: '',
                checked_by_notes: '',
                ...(config.initialSections.remarks || {})
            }
        },

        init() {
            // Restore from localStorage if available and newer
            const storageKey = 'solar_draft_' + this.reportId;
            const savedLocal = localStorage.getItem(storageKey);
            if (savedLocal) {
                try {
                    const parsed = JSON.parse(savedLocal);
                    if (parsed && parsed.form) {
                        this.form = Object.assign(this.form, parsed.form);
                    }
                } catch(e) {}
            }
        },

        getPhotosBySection(sectionKey) {
            return this.photos.filter(p => p.section_key === sectionKey);
        },

        nextStep() {
            this.saveDraft(true);
            if (this.currentStep < 10) {
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

            // Save to localStorage for instant offline safety
            localStorage.setItem('solar_draft_' + this.reportId, JSON.stringify({
                form: this.form,
                step: this.currentStep,
                timestamp: Date.now()
            }));

            // Sync with backend
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
                headers: {
                    'Accept': 'application/json'
                },
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
            .catch(err => {
                alert('Photo upload failed: Check network connection.');
            });
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
