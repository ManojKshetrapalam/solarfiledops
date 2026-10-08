@extends('layouts.engineer')

@section('mobile_title', 'Complaint: ' . $report->report_number)
@section('header_back_url', $report->service_id ? route('engineer.services.show', $report->service_id) : route('engineer.reports.index'))
@section('hide_bottom_nav', 'true')

@section('engineer_content')
<div x-data="complaintWizard({
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
            <span class="text-xs font-bold text-amber-400 shrink-0 ml-2" x-text="'Step ' + currentStep + ' of 5'"></span>
        </div>

        <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
            <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-300" 
                 :style="'width: ' + ((currentStep / 5) * 100) + '%'"></div>
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

        <!-- STEP 1: Customer & Plant Details -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-rose-100 text-rose-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Service Ticket</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Step 1: Customer & Solar Plant Details</h3>
                    <p class="text-xs text-slate-500">Record customer details, existing capacity, and hardware specifications.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Name & Complete Address *</label>
                    <input type="text" x-model="form.plant_details.customer_name" readonly
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-medium">
                    <textarea x-model="form.plant_details.customer_address" rows="2" readonly
                              class="w-full mt-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-medium"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Solar Plant Capacity Detailed *</label>
                        <input type="text" x-model="form.plant_details.plant_capacity" placeholder="e.g. 50 kW Grid-Tied / 20 kW Hybrid"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Solar Modules Detailed *</label>
                        <input type="text" x-model="form.plant_details.modules_detailed" placeholder="e.g. 100 x 540Wp Mono PERC (54 kWp)"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">System Configuration (Inverter/PCU) *</label>
                        <input type="text" x-model="form.plant_details.system_configuration" placeholder="e.g. Sungrow 50kW 3-Phase Inverter"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Batteries Detailed</label>
                        <input type="text" x-model="form.plant_details.batteries_detailed" placeholder="e.g. 16 x 200Ah Tubular / None"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Others Detailed / Balance of System</label>
                    <input type="text" x-model="form.plant_details.others_detailed" placeholder="e.g. Dual source changeover, data logger serial, etc."
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- STEP 2: Complaint Intake & History -->
        <div x-show="currentStep === 2" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 2: Complaint Intake & History</h3>
                    <p class="text-xs text-slate-500">Record customer reported issue and symptom details.</p>
                </div>

                <div class="p-3.5 bg-rose-50/70 rounded-xl border border-rose-200 space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-rose-950">Reported Complaint Intake</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-rose-900 mb-1">Complaint Received From Whom (Name & Phone) *</label>
                            <input type="text" x-model="form.complaint_intake.received_from" placeholder="e.g. Rajesh (Plant Engineer) - 9876543210"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase text-rose-900 mb-1">Date & Time Complaint Reported</label>
                            <input type="text" x-model="form.complaint_intake.complaint_date" placeholder="e.g. 05 Oct 2026, 11:30 AM"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-rose-900 mb-1">What is the Primary Complaint / Fault? *</label>
                        <textarea x-model="form.complaint_intake.primary_complaint" rows="2" placeholder="e.g. Inverter showing Error E014 (Grid Undervoltage) and tripping continuously after 2 PM."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white focus:outline-none"></textarea>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Detailed Complaint Points (1 to 3)</label>
                    <input type="text" x-model="form.complaint_intake.complaint_point_1" placeholder="1. Inverter red fault light blinking continuously" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.complaint_intake.complaint_point_2" placeholder="2. Zero kWh power generation logged on monitoring app" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.complaint_intake.complaint_point_3" placeholder="3. Burning smell noticed near DCDB isolator switch" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>
            </div>
        </div>

        <!-- STEP 3: Attended Work & Troubleshooting -->
        <div x-show="currentStep === 3" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 3: Attended Work Detailed</h3>
                    <p class="text-xs text-slate-500">Record all corrective maintenance and component repair actions taken.</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800">Attended Work Actions (Detailed 1 to 5) *</label>
                    <input type="text" x-model="form.attended_work.action_1" placeholder="1. Inspected DCDB; found loose termination on DC fuse terminal block" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.attended_work.action_2" placeholder="2. Re-stripped damaged cable end and crimped with new heavy-duty copper lug" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.attended_work.action_3" placeholder="3. Cleaned fuse holder contacts with electrical contact cleaner" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.attended_work.action_4" placeholder="4. Reset inverter error log and checked grid voltage synchronization" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.attended_work.action_5" placeholder="5. Plant restarted smoothly; verified 38kW output generation on load" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Spares / Components Replaced</label>
                    <input type="text" x-model="form.attended_work.spares_replaced" placeholder="e.g. 1 x 32A 1000V DC Fuse, 2 x 16 sq.mm copper lugs"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Attended Work Remarks</label>
                    <textarea x-model="form.attended_work.attended_remarks" rows="2" placeholder="Root cause diagnosis, preventive recommendations..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 4: 9-Point Solar Plant Checklist -->
        <div x-show="currentStep === 4" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 4: 9-Point Plant Health Checklist</h3>
                    <p class="text-xs text-slate-500">Thorough inspection of all plant systems following complaint resolution.</p>
                </div>

                <div class="space-y-2">
                    <span class="block text-xs font-bold uppercase tracking-wider text-emerald-800">Complete Solar Plant Checked Thoroughly (1 to 9)</span>

                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2">
                        <input type="text" x-model="form.plant_checklist_9point.check_1" placeholder="1. Solar Modules: Physical condition & cleaning verified intact" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_2" placeholder="2. Array Structure: Mounting fasteners & wind safety tight" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_3" placeholder="3. Cabling & Conduits: UV sleeves & MC4 connectors secure" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_4" placeholder="4. AJB / Junction Boxes: Fuses and SPDs tested operational" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_5" placeholder="5. Inverter / PCU: Operating normally without error codes" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_6" placeholder="6. Battery Bank: Terminals clean, voltage balanced" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_7" placeholder="7. ACDB / DCDB: Protection breakers and isolators OK" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_8" placeholder="8. Earthing & LA: Pit resistance and continuity intact" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" x-model="form.plant_checklist_9point.check_9" placeholder="9. Power Output: Synchronized generation feeding plant load" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Checklist Overall Remarks</label>
                    <textarea x-model="form.plant_checklist_9point.checklist_remarks" rows="2" placeholder="Detailed notes on plant health..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- STEP 5: Handover, Sign-off & Photos -->
        <div x-show="currentStep === 5" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 5: Handover & Sign-Off</h3>
                    <p class="text-xs text-slate-500">Record whom shown, future pending work, client signature, and photos.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Whom Met (Name & Phone Number) *</label>
                        <input type="text" x-model="form.handover_signoff.whom_met" placeholder="e.g. Ramesh (Site Incharge) - 9876543210" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Whom Shown After Completion *</label>
                        <input type="text" x-model="form.handover_signoff.whom_shown" placeholder="e.g. Ramesh (Site Incharge) - 9876543210" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-800">Work to be Attended in Future / Follow-Up (1 to 3)</label>
                    <input type="text" x-model="form.handover_signoff.followup_1" placeholder="1. (Leave blank or enter scheduled module cleaning date)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.handover_signoff.followup_2" placeholder="2." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                    <input type="text" x-model="form.handover_signoff.followup_3" placeholder="3." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900">
                </div>

                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-amber-300 bg-amber-50/60 cursor-pointer">
                    <input type="checkbox" x-model="form.handover_signoff.client_confirmed" class="mt-0.5 w-4 h-4 text-amber-600 rounded">
                    <span class="text-xs text-slate-800 font-medium">
                        Work Acceptance Confirmation: I confirm that the reported complaint has been inspected, attended, and the solar plant is back in satisfactory working operation.
                    </span>
                </label>

                <!-- Signature Pad -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">Client / Incharge Digital Signature</label>
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

                <!-- Photos Grid -->
                <div class="pt-3 border-t border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Complaint & Rectification Photos</span>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                            <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span class="text-xs font-bold text-slate-800">Fault Display / Symptom</span>
                            <span class="text-[10px] text-slate-500">Snap photo</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'complaint_intake', 'fault_photo')">
                        </label>

                        <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                            <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span class="text-xs font-bold text-slate-800">Rectified / Running Plant</span>
                            <span class="text-[10px] text-slate-500">Snap photo</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'attended_work', 'rectified_photo')">
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
                </div>

                <!-- Submit Card -->
                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center space-y-2 mt-4">
                    <h4 class="text-sm font-extrabold text-emerald-950">Ready to Submit Complaint Report?</h4>
                    <p class="text-xs text-emerald-800">This closes the ticket and marks work completed for Admin verification.</p>
                    <button type="button" @click="confirmSubmit()"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-sm rounded-xl shadow-md transition-all">
                        Submit Complaint Attending Report
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

        <template x-if="currentStep < 5">
            <button type="button" @click="nextStep()"
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm flex items-center gap-1">
                <span>Next</span>
                &rarr;
            </button>
        </template>

        <template x-if="currentStep === 5">
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
                <h3 class="text-base font-extrabold text-slate-900">Confirm Complaint Report Submission</h3>
                <p class="text-xs text-slate-600 mt-1">
                    Are you sure all attended work details, 9-point checks, and client acceptance confirmation are complete?
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
function complaintWizard(config) {
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
            'Customer & Plant',
            'Complaint Intake',
            'Attended Work',
            '9-Point Health Check',
            'Handover & Sign-Off'
        ],

        form: {
            plant_details: Object.assign({
                customer_name: '',
                customer_address: '',
                plant_capacity: '',
                modules_detailed: '',
                system_configuration: '',
                batteries_detailed: '',
                others_detailed: ''
            }, config.initialSections.plant_details || {}),

            complaint_intake: Object.assign({
                received_from: '',
                complaint_date: '',
                primary_complaint: '',
                complaint_point_1: '',
                complaint_point_2: '',
                complaint_point_3: ''
            }, config.initialSections.complaint_intake || {}),

            attended_work: Object.assign({
                action_1: '', action_2: '', action_3: '', action_4: '', action_5: '',
                spares_replaced: '',
                attended_remarks: ''
            }, config.initialSections.attended_work || {}),

            plant_checklist_9point: Object.assign({
                check_1: '', check_2: '', check_3: '', check_4: '', check_5: '',
                check_6: '', check_7: '', check_8: '', check_9: '',
                checklist_remarks: ''
            }, config.initialSections.plant_checklist_9point || {}),

            handover_signoff: Object.assign({
                whom_met: '',
                whom_shown: '',
                followup_1: '', followup_2: '', followup_3: '',
                client_confirmed: false,
                client_signature: '',
                client_signed_at: ''
            }, config.initialSections.handover_signoff || {})
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
                const existingData = this.form.handover_signoff.client_signature;
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
            } else if (this.form.handover_signoff.client_signature && !this.hasSignature) {
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.hasSignature = true;
                };
                img.src = this.form.handover_signoff.client_signature;
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
            this.form.handover_signoff.client_signature = canvas.toDataURL('image/png');
            this.form.handover_signoff.client_signed_at = new Date().toISOString();
            this.hasSignature = true;
            this.saveDraft(true);
        },

        clearSignature() {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            this.form.handover_signoff.client_signature = '';
            this.form.handover_signoff.client_signed_at = '';
            this.hasSignature = false;
            this.saveDraft(true);
        },

        nextStep() {
            this.saveDraft(true);
            if (this.currentStep < 5) {
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
