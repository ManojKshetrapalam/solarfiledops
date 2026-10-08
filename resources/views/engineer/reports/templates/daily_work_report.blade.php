@extends('layouts.engineer')

@section('mobile_title', 'Daily Log: ' . $report->report_number)
@section('header_back_url', route('engineer.daily-reports.index'))
@section('hide_bottom_nav', 'true')

@section('engineer_content')
<div x-data="dailyWorkWizard({
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
            <span class="text-slate-400 truncate max-w-[200px]">{{ $report->company->name }}</span>
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

        <!-- STEP 1: Personnel & Shift Details -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-indigo-100 text-indigo-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Timesheet</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Step 1: Personnel & Shift Details</h3>
                    <p class="text-xs text-slate-500">Record employee profile, shift start/end times, and place of work.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Employee Name *</label>
                        <input type="text" x-model="form.shift_details.employee_name" readonly
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Role / Designation *</label>
                        <select x-model="form.shift_details.designation" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs bg-white">
                            <option value="Employee / Field Engineer">Employee / Field Engineer</option>
                            <option value="Manager / Supervisor">Manager / Supervisor</option>
                            <option value="Technician / Worker">Technician / Worker</option>
                            <option value="Helper">Helper</option>
                            <option value="Driver">Driver</option>
                            <option value="Director">Director</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Report Date *</label>
                        <input type="date" x-model="form.shift_details.report_date" required
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Time Started *</label>
                        <input type="time" x-model="form.shift_details.work_started_time" required
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Time Stopped *</label>
                        <input type="time" x-model="form.shift_details.work_stopped_time" required
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Place of Work / Location / Site *</label>
                    <input type="text" x-model="form.shift_details.place_of_work" placeholder="e.g. Bangalore Peenya Plant Roof / Travel / Office"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- STEP 2: Hourly Activity Log -->
        <div x-show="currentStep === 2" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 2: Hourly Activity Log</h3>
                    <p class="text-xs text-slate-500">Record your activities in the specific hourly time slots from the paper timesheet.</p>
                </div>

                <div class="space-y-2.5">
                    <!-- Morning slots -->
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                        <span class="font-bold text-slate-600 uppercase text-[10px] block">Morning Schedule</span>
                        
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">6:30 AM to 9:00 AM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_630_900" placeholder="e.g. Traveled from residence to yard; loading mounting tools" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">9:00 AM to 10:00 AM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_900_1000" placeholder="e.g. Reached customer site; tool setup and safety briefing" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">10:00 AM to 11:00 AM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1000_1100" placeholder="e.g. Mounting structure column alignment and fastener torquing" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">11:00 AM to 12:00 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1100_1200" placeholder="e.g. Module placement on rows 1 and 2" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">12:00 PM to 1:00 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1200_1300" placeholder="e.g. Clamping mid-clamps and string cable routing" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                    </div>

                    <!-- Afternoon slots -->
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                        <span class="font-bold text-slate-600 uppercase text-[10px] block">Afternoon Schedule</span>
                        
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">1:00 PM to 2:00 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1300_1400" placeholder="e.g. Lunch break" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">2:00 PM to 3:00 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1400_1500" placeholder="e.g. DC cable laying in UV conduit" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">3:00 PM to 4:00 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1500_1600" placeholder="e.g. DCDB termination and string Voc testing" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">4:00 PM to 5:00 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1600_1700" placeholder="e.g. ACDB connection and earthing pit check" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">5:00 PM to 6:30 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1700_1830" placeholder="e.g. Final inspection, cleanup, site incharge briefing" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                    </div>

                    <!-- Evening slots -->
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                        <span class="font-bold text-slate-600 uppercase text-[10px] block">Evening Schedule (Overtime / Return)</span>
                        
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">6:30 PM to 7:30 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1830_1930" placeholder="e.g. Return journey / travel to yard" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">7:30 PM to 8:30 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_1930_2030" placeholder="e.g. Material unloading at warehouse" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 block mb-0.5">8:30 PM to 9:30 PM</span>
                            <input type="text" x-model="form.hourly_activity_log.slot_2030_2130" placeholder="e.g. Day close / reporting" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg bg-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: Meals & Daily Allowances -->
        <div x-show="currentStep === 3" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 3: Meals & Daily Allowances</h3>
                    <p class="text-xs text-slate-500">Record Tiffin, Lunch, and Dinner allowance claims.</p>
                </div>

                <div class="space-y-3">
                    <!-- Tiffin -->
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="form.meals_allowance.tiffin_yes" class="w-4 h-4 text-amber-500 rounded">
                            <div>
                                <strong class="text-slate-900 block font-bold">Breakfast / Tiffin</strong>
                                <span class="text-[10px] text-slate-500">Claimed for morning shift</span>
                            </div>
                        </div>
                        <div class="w-28" x-show="form.meals_allowance.tiffin_yes">
                            <input type="number" x-model="form.meals_allowance.tiffin_amount" placeholder="Rs. 80" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-right">
                        </div>
                    </div>

                    <!-- Lunch -->
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="form.meals_allowance.lunch_yes" class="w-4 h-4 text-amber-500 rounded">
                            <div>
                                <strong class="text-slate-900 block font-bold">Lunch</strong>
                                <span class="text-[10px] text-slate-500">Claimed for day shift</span>
                            </div>
                        </div>
                        <div class="w-28" x-show="form.meals_allowance.lunch_yes">
                            <input type="number" x-model="form.meals_allowance.lunch_amount" placeholder="Rs. 150" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-right">
                        </div>
                    </div>

                    <!-- Dinner -->
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" x-model="form.meals_allowance.dinner_yes" class="w-4 h-4 text-amber-500 rounded">
                            <div>
                                <strong class="text-slate-900 block font-bold">Dinner</strong>
                                <span class="text-[10px] text-slate-500">Claimed for night / overtime shift</span>
                            </div>
                        </div>
                        <div class="w-28" x-show="form.meals_allowance.dinner_yes">
                            <input type="number" x-model="form.meals_allowance.dinner_amount" placeholder="Rs. 150" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs bg-white text-right">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Meal Notes / Remarks</label>
                    <input type="text" x-model="form.meals_allowance.remarks" placeholder="e.g. Outstation stay in Mysore for 2 days" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                </div>
            </div>
        </div>

        <!-- STEP 4: Travel & Vehicle Conveyance -->
        <div x-show="currentStep === 4" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 4: Travel & Vehicle Conveyance</h3>
                    <p class="text-xs text-slate-500">Odometer readings, kilometer difference calculation, and fuel/conveyance claim.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Vehicle Used *</label>
                    <select x-model="form.travel_conveyance.vehicle_used" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs bg-white">
                        <option value="Personal Two-Wheeler (Bike)">Personal Two-Wheeler (Bike)</option>
                        <option value="Company Vehicle">Company Vehicle</option>
                        <option value="Personal Four-Wheeler (Car)">Personal Four-Wheeler (Car)</option>
                        <option value="Public Transport / Auto">Public Transport / Bus / Auto</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Starting KM</label>
                        <input type="number" x-model="form.travel_conveyance.starting_km" @input="calcKmDiff()" placeholder="e.g. 14200" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 mb-1">Ending KM</label>
                        <input type="number" x-model="form.travel_conveyance.ending_km" @input="calcKmDiff()" placeholder="e.g. 14265" class="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-amber-700 mb-1">Difference KM</label>
                        <input type="text" x-model="form.travel_conveyance.diff_km" readonly class="w-full px-2.5 py-1.5 border border-amber-300 rounded-lg bg-amber-50 font-bold text-amber-900">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rate per KM / Fare (Rs.)</label>
                        <input type="number" x-model="form.travel_conveyance.rate_per_km" @input="calcConveyanceAmount()" placeholder="e.g. 4.5" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Total Conveyance Claim (Rs.)</label>
                        <input type="number" x-model="form.travel_conveyance.amount" placeholder="e.g. 292" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold text-slate-900">
                    </div>
                </div>

                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800">Conveyance Paid by Office</span>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1">
                            <input type="radio" value="Yes" x-model="form.travel_conveyance.paid" class="text-amber-500">
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center gap-1">
                            <input type="radio" value="No" x-model="form.travel_conveyance.paid" class="text-amber-500">
                            <span>No (Claim Pending)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 5: Work Summary & Sign-off -->
        <div x-show="currentStep === 5" class="space-y-4" x-cloak>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Step 5: Work Summary & Sign-Off</h3>
                    <p class="text-xs text-slate-500">Record overall allocated tasks, completed work, and your daily signature.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Work Allocated for Today *</label>
                    <textarea x-model="form.work_summary_signoff.work_allocated" rows="2" placeholder="e.g. Complete module mounting at Peenya site shed 3; inspect cable trays."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Work Completed Today *</label>
                    <textarea x-model="form.work_summary_signoff.work_completed" rows="3" placeholder="e.g. Completed 100 panels mounting, torqued mid clamps, laid UV conduits, and tested strings 1 through 4."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Pending Work / Challenges</label>
                    <textarea x-model="form.work_summary_signoff.pending_work" rows="2" placeholder="e.g. Need 4 extra mid-clamps and 2 boxes of cable ties for tomorrow."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <!-- Digital Signature Pad -->
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">Employee Digital Signature *</label>
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
                    <p class="text-[10px] text-slate-400 mt-1">Sign inside the box to certify your daily work log.</p>
                </div>

                <!-- Photos & Receipts -->
                <div class="pt-3 border-t border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Daily Photos / Receipts (Optional)</span>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col items-center justify-center p-3 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                            <svg class="w-5 h-5 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span class="text-xs font-bold text-slate-800">Odometer / Site Photo</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'travel_conveyance', 'odometer')">
                        </label>

                        <label class="flex flex-col items-center justify-center p-3 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 cursor-pointer text-center">
                            <svg class="w-5 h-5 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            <span class="text-xs font-bold text-slate-800">Toll / Meal Receipt</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoUpload($event, 'meals_allowance', 'receipt')">
                        </label>
                    </div>

                    <div x-show="photos.length > 0" class="mt-3">
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="p in photos" :key="p.id">
                                <div class="relative group rounded-xl overflow-hidden border border-slate-200 aspect-square bg-slate-100">
                                    <img :src="p.url" :alt="p.filename" class="w-full h-full object-cover">
                                    <button type="button" @click="deletePhoto(p.id)" class="absolute top-1 right-1 bg-rose-600 text-white rounded-full p-1 shadow-md">
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
                    <h4 class="text-sm font-extrabold text-emerald-950">Submit Daily Work Timesheet?</h4>
                    <p class="text-xs text-emerald-800">Your timesheet and travel claim will be submitted for manager approval.</p>
                    <button type="button" @click="confirmSubmit()"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-sm rounded-xl shadow-md transition-all">
                        Submit Daily Work Report
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
                <h3 class="text-base font-extrabold text-slate-900">Submit Daily Timesheet?</h3>
                <p class="text-xs text-slate-600 mt-1">
                    Confirm that your hourly activities, conveyance, and meal claims for today are accurate.
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
function dailyWorkWizard(config) {
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
            'Shift & Personnel',
            'Hourly Log',
            'Meals & Allowances',
            'Travel & Conveyance',
            'Summary & Sign-Off'
        ],

        form: {
            shift_details: Object.assign({
                employee_name: '',
                designation: 'Employee / Field Engineer',
                report_date: '',
                work_started_time: '09:00',
                work_stopped_time: '18:00',
                place_of_work: ''
            }, config.initialSections.shift_details || {}),

            hourly_activity_log: Object.assign({
                slot_630_900: '',
                slot_900_1000: '',
                slot_1000_1100: '',
                slot_1100_1200: '',
                slot_1200_1300: '',
                slot_1300_1400: '',
                slot_1400_1500: '',
                slot_1500_1600: '',
                slot_1600_1700: '',
                slot_1700_1830: '',
                slot_1830_1930: '',
                slot_1930_2030: '',
                slot_2030_2130: ''
            }, config.initialSections.hourly_activity_log || {}),

            meals_allowance: Object.assign({
                tiffin_yes: false,
                tiffin_amount: '',
                lunch_yes: false,
                lunch_amount: '',
                dinner_yes: false,
                dinner_amount: '',
                remarks: ''
            }, config.initialSections.meals_allowance || {}),

            travel_conveyance: Object.assign({
                vehicle_used: 'Personal Two-Wheeler (Bike)',
                starting_km: '',
                ending_km: '',
                diff_km: '',
                rate_per_km: '',
                amount: '',
                paid: 'No'
            }, config.initialSections.travel_conveyance || {}),

            work_summary_signoff: Object.assign({
                work_allocated: '',
                work_completed: '',
                pending_work: '',
                employee_signature: '',
                signed_at: ''
            }, config.initialSections.work_summary_signoff || {})
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

        calcKmDiff() {
            const start = parseFloat(this.form.travel_conveyance.starting_km) || 0;
            const end = parseFloat(this.form.travel_conveyance.ending_km) || 0;
            if (end > start) {
                this.form.travel_conveyance.diff_km = (end - start).toFixed(1);
            } else {
                this.form.travel_conveyance.diff_km = '0';
            }
            this.calcConveyanceAmount();
        },

        calcConveyanceAmount() {
            const diff = parseFloat(this.form.travel_conveyance.diff_km) || 0;
            const rate = parseFloat(this.form.travel_conveyance.rate_per_km) || 0;
            if (diff > 0 && rate > 0) {
                this.form.travel_conveyance.amount = Math.round(diff * rate);
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
                const existingData = this.form.work_summary_signoff.employee_signature;
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
            } else if (this.form.work_summary_signoff.employee_signature && !this.hasSignature) {
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.hasSignature = true;
                };
                img.src = this.form.work_summary_signoff.employee_signature;
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
            this.form.work_summary_signoff.employee_signature = canvas.toDataURL('image/png');
            this.form.work_summary_signoff.signed_at = new Date().toISOString();
            this.hasSignature = true;
            this.saveDraft(true);
        },

        clearSignature() {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            this.form.work_summary_signoff.employee_signature = '';
            this.form.work_summary_signoff.signed_at = '';
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
