@extends('layouts.engineer')

@section('mobile_title', 'Feedback: ' . $report->report_number)
@section('header_back_url', $report->service_id ? route('engineer.services.show', $report->service_id) : route('engineer.reports.index'))
@section('hide_bottom_nav', 'true')

@section('engineer_content')
<div x-data="feedbackWizard({
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

        <!-- STEP 1: Customer & Project Details -->
        <div x-show="currentStep === 1" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Feedback Intake</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Step 1: Customer & Project Details</h3>
                    <p class="text-xs text-slate-500">Record customer project details, visit category, and capacity.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Name *</label>
                    <input type="text" x-model="form.customer_project_details.customer_name"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Site Address *</label>
                    <textarea x-model="form.customer_project_details.site_address" rows="2"
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Person</label>
                        <input type="text" x-model="form.customer_project_details.contact_person" placeholder="e.g. Mr. Rajesh Sharma"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Mobile No. *</label>
                        <input type="tel" x-model="form.customer_project_details.mobile_no" placeholder="e.g. +91 98765 43210"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">System Capacity</label>
                        <input type="text" x-model="form.customer_project_details.system_capacity" placeholder="e.g. 10 kW Rooftop"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Date of Feedback *</label>
                        <input type="date" x-model="form.customer_project_details.feedback_date"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Installation Date</label>
                        <input type="date" x-model="form.customer_project_details.installation_date"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Type of Visit *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        @foreach(['installation' => 'Installation', 'service' => 'Service', 'complaint' => 'Complaint', 'maintenance' => 'Maintenance', 'inspection' => 'Inspection'] as $k => $label)
                            <button type="button" 
                                    @click="form.customer_project_details.type_of_visit = '{{ $k }}'; triggerAutoSave()"
                                    :class="form.customer_project_details.type_of_visit === '{{ $k }}' 
                                        ? 'bg-amber-500 text-slate-950 font-bold border-amber-500 shadow-xs' 
                                        : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                    class="py-2.5 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: 10-Point Experience Ratings -->
        <div x-show="currentStep === 2" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <span class="bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Scale: 1 = Very Poor &bull; 5 = Excellent</span>
                        <h3 class="text-sm font-bold text-slate-900 mt-1">Step 2: Customer Experience Ratings</h3>
                        <p class="text-xs text-slate-500">Rate each service touchpoint from 1 to 5.</p>
                    </div>
                    <div class="flex items-center gap-1.5 self-start sm:self-auto bg-slate-900 text-amber-400 px-3 py-1.5 rounded-xl font-mono text-xs font-bold">
                        <span>Avg Score:</span>
                        <span x-text="calculateAverageRating()"></span>
                        <span>/ 5.0</span>
                    </div>
                </div>

                @php
                    $ratingItems = [
                        'rate_quality_work' => ['label' => '1. Quality of installation / work', 'desc' => 'Workmanship, precision, and structural fit'],
                        'rate_quality_materials' => ['label' => '2. Quality of materials / components', 'desc' => 'Panels, cabling, structures & protection gear'],
                        'rate_professionalism' => ['label' => '3. Professionalism of technical team', 'desc' => 'Expertise, methodology, and tools handling'],
                        'rate_behaviour_communication' => ['label' => '4. Behaviour and communication of staff', 'desc' => 'Politeness, clarity, and respect on site'],
                        'rate_punctuality' => ['label' => '5. Punctuality and completion of work', 'desc' => 'Timely arrival and adhering to agreed schedule'],
                        'rate_cleanliness' => ['label' => '6. Cleanliness after completion of work', 'desc' => 'Debris removal and site restoration'],
                        'rate_explanation_operation' => ['label' => '7. Explanation of system operation', 'desc' => 'Guidance on meter reading, PCU LCD & switches'],
                        'rate_explanation_safety' => ['label' => '8. Explanation of safety / maintenance', 'desc' => 'Emergency cut-off and routine cleaning steps'],
                        'rate_response_questions' => ['label' => '9. Response to questions / concerns', 'desc' => 'Attentiveness to customer inquiries'],
                        'rate_overall_satisfaction' => ['label' => '10. Overall satisfaction', 'desc' => 'Total customer experience with the visit'],
                    ];
                @endphp

                <div class="space-y-3.5">
                    @foreach($ratingItems as $field => $meta)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $meta['label'] }}</h4>
                                    <p class="text-[11px] text-slate-500">{{ $meta['desc'] }}</p>
                                </div>
                                <span class="text-xs font-black font-mono shrink-0 px-2 py-0.5 rounded-md"
                                      :class="{
                                          'bg-rose-100 text-rose-800': form.ratings_experience.{{ $field }} == 1,
                                          'bg-orange-100 text-orange-800': form.ratings_experience.{{ $field }} == 2,
                                          'bg-amber-100 text-amber-800': form.ratings_experience.{{ $field }} == 3,
                                          'bg-lime-100 text-lime-800': form.ratings_experience.{{ $field }} == 4,
                                          'bg-emerald-100 text-emerald-800': form.ratings_experience.{{ $field }} == 5,
                                          'bg-slate-200 text-slate-600': !form.ratings_experience.{{ $field }}
                                      }"
                                      x-text="form.ratings_experience.{{ $field }} ? form.ratings_experience.{{ $field }} + ' / 5' : 'Not Rated'"></span>
                            </div>

                            <div class="grid grid-cols-5 gap-1.5 sm:gap-2">
                                @for($score = 1; $score <= 5; $score++)
                                    @php
                                        $descText = match($score) {
                                            1 => '1 - Very Poor',
                                            2 => '2 - Poor',
                                            3 => '3 - Satisfactory',
                                            4 => '4 - Good',
                                            5 => '5 - Excellent',
                                        };
                                    @endphp
                                    <button type="button"
                                            @click="form.ratings_experience.{{ $field }} = {{ $score }}; triggerAutoSave()"
                                            :class="form.ratings_experience.{{ $field }} == {{ $score }}
                                                ? 'bg-amber-500 text-slate-950 font-bold border-amber-600 shadow-xs'
                                                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                            class="py-2 px-1 rounded-lg border text-center transition-all flex flex-col items-center justify-center">
                                        <span class="text-xs font-black">{{ $score }}</span>
                                        <span class="text-[9px] uppercase tracking-tighter opacity-80 hidden sm:inline">
                                            @if($score === 1) Poor @elseif($score === 3) OK @elseif($score === 5) Excl @endif
                                        </span>
                                    </button>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- STEP 3: Solar System & Service Feedback -->
        <div x-show="currentStep === 3" class="space-y-4">
            <!-- Part A: Solar System Feedback -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-blue-100 text-blue-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Section 3</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Solar System Handover Feedback</h3>
                    <p class="text-xs text-slate-500">Feedback regarding solar installation operations and briefing.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Is the installation / service work satisfactory? *</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'PARTLY' => 'PARTLY'] as $v => $l)
                                <button type="button" @click="form.solar_system_feedback.work_satisfactory = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.solar_system_feedback.work_satisfactory === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Is the solar system performing as explained to you? *</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'NOT SURE' => 'NOT SURE'] as $v => $l)
                                <button type="button" @click="form.solar_system_feedback.performing_as_explained = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.solar_system_feedback.performing_as_explained === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Were the system components and their functions explained properly? *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO'] as $v => $l)
                                <button type="button" @click="form.solar_system_feedback.components_explained = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.solar_system_feedback.components_explained === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Were safety precautions explained to you? *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO'] as $v => $l)
                                <button type="button" @click="form.solar_system_feedback.safety_explained = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.solar_system_feedback.safety_explained === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Were routine maintenance requirements explained to you? *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO'] as $v => $l)
                                <button type="button" @click="form.solar_system_feedback.maintenance_explained = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.solar_system_feedback.maintenance_explained === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part B: Service / Complaint Feedback -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-rose-100 text-rose-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Section 4</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Service / Complaint Attendance Feedback</h3>
                    <p class="text-xs text-slate-500">Feedback for troubleshooting, repairs, or maintenance attendance.</p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Was your complaint / concern understood correctly? *</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'NOT APPLICABLE' => 'NOT APPLICABLE'] as $v => $l)
                                <button type="button" @click="form.service_complaint_feedback.concern_understood = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.service_complaint_feedback.concern_understood === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Was the technician response time satisfactory? *</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'NOT APPLICABLE' => 'NOT APPLICABLE'] as $v => $l)
                                <button type="button" @click="form.service_complaint_feedback.response_time_satisfactory = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.service_complaint_feedback.response_time_satisfactory === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Was the issue properly rectified? *</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'PARTLY' => 'PARTLY', 'N/A' => 'N/A'] as $v => $l)
                                <button type="button" @click="form.service_complaint_feedback.issue_rectified = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.service_complaint_feedback.issue_rectified === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-1 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Was the solution / work carried out explained to you? *</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'N/A' => 'N/A'] as $v => $l)
                                <button type="button" @click="form.service_complaint_feedback.solution_explained = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.service_complaint_feedback.solution_explained === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Is the system now functioning satisfactorily? *</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'PARTLY' => 'PARTLY', 'N/A' => 'N/A'] as $v => $l)
                                <button type="button" @click="form.service_complaint_feedback.system_functioning = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.service_complaint_feedback.system_functioning === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-1 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Is any work or issue still pending? *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['NO' => 'NO', 'YES — DETAILS BELOW' => 'YES (DETAILS BELOW)'] as $v => $l)
                                <button type="button" @click="form.service_complaint_feedback.work_pending = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.service_complaint_feedback.work_pending === '{{ $v }}' 
                                            ? ($v === 'NO' ? 'bg-emerald-600 text-white font-bold border-emerald-700' : 'bg-amber-500 text-slate-950 font-bold border-amber-600') 
                                            : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4: Comments, Suggestions & Recommendation -->
        <div x-show="currentStep === 4" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-purple-100 text-purple-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Customer Voice</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Step 4: Comments & Recommendation</h3>
                    <p class="text-xs text-slate-500">Record customer feedback verbatim and recommendation interest.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">What did we do well?</label>
                    <textarea x-model="form.comments_suggestions.what_done_well" rows="2" placeholder="e.g. Prompt arrival, neat cable dressing, courteous team"
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">What can we improve?</label>
                    <textarea x-model="form.comments_suggestions.what_to_improve" rows="2" placeholder="e.g. Earlier communication on arrival time, faster delivery of spare MC4"
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Any other feedback / suggestion?</label>
                    <textarea x-model="form.comments_suggestions.other_feedback" rows="2" placeholder="Additional observations, general suggestions..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1"
                           :class="form.service_complaint_feedback.work_pending === 'YES — DETAILS BELOW' ? 'text-amber-700' : 'text-slate-700'">
                        If any issue is pending, please mention details:
                    </label>
                    <textarea x-model="form.comments_suggestions.pending_issue_details" rows="2" placeholder="Mention pending parts, follow-up dates, or technician visits needed..."
                              :class="form.service_complaint_feedback.work_pending === 'YES — DETAILS BELOW' ? 'border-amber-400 bg-amber-50/40 ring-1 ring-amber-400' : 'border-slate-300'"
                              class="w-full px-3 py-2.5 rounded-xl text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Overall Experience Rating (1 to 5) *</label>
                        <div class="grid grid-cols-5 gap-2">
                            @for($rate=1; $rate<=5; $rate++)
                                <button type="button" @click="form.overall_recommendation.overall_experience = {{ $rate }}; triggerAutoSave()"
                                        :class="form.overall_recommendation.overall_experience == {{ $rate }} ? 'bg-amber-500 text-slate-950 font-bold border-amber-600 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-3 px-2 rounded-xl border text-sm font-black text-center transition-all flex flex-col items-center">
                                    <span>{{ $rate }}</span>
                                    <span class="text-[9px] uppercase font-bold opacity-75">
                                        @if($rate === 1) ★ @elseif($rate === 2) ★★ @elseif($rate === 3) ★★★ @elseif($rate === 4) ★★★★ @else ★★★★★ @endif
                                    </span>
                                </button>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Would you recommend our services to others? *</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO', 'MAYBE' => 'MAYBE'] as $v => $l)
                                <button type="button" @click="form.overall_recommendation.recommend_services = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.overall_recommendation.recommend_services === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2.5 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">May we contact you for future feedback / testimonial? *</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['YES' => 'YES', 'NO' => 'NO'] as $v => $l)
                                <button type="button" @click="form.overall_recommendation.contact_for_testimonial = '{{ $v }}'; triggerAutoSave()"
                                        :class="form.overall_recommendation.contact_for_testimonial === '{{ $v }}' ? 'bg-amber-500 text-slate-950 font-bold border-amber-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                                        class="py-2.5 px-2 rounded-xl border text-xs font-medium text-center transition-all">
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 5: Customer Confirmation & Digital Signature -->
        <div x-show="currentStep === 5" class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <span class="bg-emerald-100 text-emerald-900 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md">Customer Sign-Off</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-1">Step 5: Work Acceptance & Customer Confirmation</h3>
                    <p class="text-xs text-slate-500">Capture customer confirmation and digital touch signature on device.</p>
                </div>

                <!-- Official Affirmation Box -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs text-slate-800 leading-relaxed">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <p class="font-medium">
                            "I confirm that the above feedback represents my experience with the installation / service / inspection work carried out at my site. I have communicated any pending concern or issue, if applicable."
                        </p>
                    </div>

                    <label class="mt-3.5 flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-200 cursor-pointer">
                        <input type="checkbox" x-model="form.customer_confirmation.client_confirmed"
                               class="w-5 h-5 text-amber-500 rounded border-slate-300 focus:ring-amber-500 shrink-0">
                        <span class="text-xs font-bold text-slate-900">Work Acceptance Confirmation (Customer Confirmed)</span>
                    </label>
                </div>

                <!-- Signer Details -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer Signer Name *</label>
                        <input type="text" x-model="form.customer_confirmation.customer_name" placeholder="Full name of client"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact No. *</label>
                        <input type="tel" x-model="form.customer_confirmation.contact_no" placeholder="Mobile number"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Date *</label>
                        <input type="date" x-model="form.customer_confirmation.feedback_date"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Touchscreen Canvas Signature Pad -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Client Digital Signature (Sign on Screen) *
                        </label>
                        <button type="button" @click="clearSignature()" 
                                class="text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg border border-rose-200 transition-colors">
                            Clear Signature
                        </button>
                    </div>

                    <div class="relative bg-white rounded-xl border-2 border-dashed border-slate-300 overflow-hidden shadow-inner" style="touch-action: none;">
                        <canvas id="signaturePad" 
                                class="w-full h-44 cursor-crosshair block"
                                style="touch-action: none;"
                                @mousedown="sigStart($event)"
                                @mousemove="sigMove($event)"
                                @mouseup="sigEnd($event)"
                                @mouseleave="sigEnd($event)"
                                @touchstart.passive="false"
                                @touchstart="sigStart($event)"
                                @touchmove.passive="false"
                                @touchmove="sigMove($event)"
                                @touchend="sigEnd($event)"
                                @touchcancel="sigEnd($event)"
                                @pointerdown="sigStart($event)"
                                @pointermove="sigMove($event)"
                                @pointerup="sigEnd($event)"
                                @pointercancel="sigEnd($event)"></canvas>

                        <div x-show="!hasSignature" class="pointer-events-none absolute inset-0 flex items-center justify-center text-slate-400 text-xs font-medium">
                            <span>Draw client signature here with finger or stylus</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Works smoothly on mobile touchscreens, tablets, and desktop mice.</p>
                </div>

                <!-- Summary Checklist for Submission -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                    <span class="font-bold text-slate-700 block uppercase text-[10px]">Feedback Audit Summary</span>
                    <div class="flex justify-between text-slate-600">
                        <span>10-Point Average Rating:</span>
                        <strong class="text-slate-900 font-mono" x-text="calculateAverageRating() + ' / 5.0'"></strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Overall Experience:</span>
                        <strong class="text-slate-900" x-text="form.overall_recommendation.overall_experience ? form.overall_recommendation.overall_experience + ' Stars' : 'Not Set'"></strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Would Recommend:</span>
                        <strong class="text-slate-900" x-text="form.overall_recommendation.recommend_services || 'Not Set'"></strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Confirmation Status:</span>
                        <strong :class="form.customer_confirmation.client_confirmed ? 'text-emerald-700' : 'text-rose-600'" 
                                x-text="form.customer_confirmation.client_confirmed ? 'Confirmed by Client' : 'Pending Confirmation Checkbox'"></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Navigation Controls -->
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-3 shadow-lg">
            <div class="max-w-2xl mx-auto flex items-center justify-between gap-3">
                <button type="button" 
                        @click="prevStep()" 
                        x-show="currentStep > 1"
                        class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-colors flex items-center gap-1">
                    <span>&larr;</span>
                    <span>Back</span>
                </button>

                <div x-show="currentStep === 1" class="text-xs text-slate-400 font-medium">
                    <span>Step 1 of 5</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="saveDraftManual()"
                            class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors">
                        Save Draft
                    </button>

                    <button type="button" 
                            @click="nextStep()" 
                            x-show="currentStep < 5"
                            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs shadow-xs transition-colors flex items-center gap-1">
                        <span>Next Step</span>
                        <span>&rarr;</span>
                    </button>

                    <button type="button" 
                            @click="confirmAndSubmit()" 
                            x-show="currentStep === 5"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Submit Feedback Form</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Final Submission Confirmation Modal -->
    <div x-show="showConfirmModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4" @click.away="showConfirmModal = false">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="text-center">
                <h3 class="text-base font-extrabold text-slate-900">Submit Customer Feedback?</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Once submitted, this customer feedback sheet is locked for review by the admin operations team.
                </p>
            </div>

            <div class="space-y-2 pt-2">
                <button type="button" @click="submitFinal()"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Yes, Submit Official Feedback
                </button>
                <button type="button" @click="showConfirmModal = false"
                        class="w-full py-2.5 text-slate-600 hover:bg-slate-50 font-bold text-xs rounded-xl border border-slate-200 transition-all">
                    Cancel & Review
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function feedbackWizard(config) {
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
            'Customer & Project',
            'Your Experience',
            'System & Service',
            'Comments & Rating',
            'Client Confirmation'
        ],

        form: {
            customer_project_details: Object.assign({
                customer_name: '',
                site_address: '',
                contact_person: '',
                mobile_no: '',
                system_capacity: '',
                feedback_date: new Date().toISOString().split('T')[0],
                installation_date: '',
                type_of_visit: 'service'
            }, config.initialSections.customer_project_details || {}),

            ratings_experience: Object.assign({
                rate_quality_work: 5,
                rate_quality_materials: 5,
                rate_professionalism: 5,
                rate_behaviour_communication: 5,
                rate_punctuality: 5,
                rate_cleanliness: 5,
                rate_explanation_operation: 5,
                rate_explanation_safety: 5,
                rate_response_questions: 5,
                rate_overall_satisfaction: 5
            }, config.initialSections.ratings_experience || {}),

            solar_system_feedback: Object.assign({
                work_satisfactory: 'YES',
                performing_as_explained: 'YES',
                components_explained: 'YES',
                safety_explained: 'YES',
                maintenance_explained: 'YES'
            }, config.initialSections.solar_system_feedback || {}),

            service_complaint_feedback: Object.assign({
                concern_understood: 'YES',
                response_time_satisfactory: 'YES',
                issue_rectified: 'YES',
                solution_explained: 'YES',
                system_functioning: 'YES',
                work_pending: 'NO'
            }, config.initialSections.service_complaint_feedback || {}),

            comments_suggestions: Object.assign({
                what_done_well: '',
                what_to_improve: '',
                other_feedback: '',
                pending_issue_details: ''
            }, config.initialSections.comments_suggestions || {}),

            overall_recommendation: Object.assign({
                overall_experience: 5,
                recommend_services: 'YES',
                contact_for_testimonial: 'YES'
            }, config.initialSections.overall_recommendation || {}),

            customer_confirmation: Object.assign({
                client_confirmed: true,
                customer_name: '',
                contact_no: '',
                feedback_date: new Date().toISOString().split('T')[0],
                client_signature: '',
                reviewed_by: '',
                review_date: '',
                action_required: 'NO',
                action_assigned_to: ''
            }, config.initialSections.customer_confirmation || {})
        },

        init() {
            // Prepopulate signer name & mobile from project details if empty
            if (!this.form.customer_confirmation.customer_name) {
                this.form.customer_confirmation.customer_name = this.form.customer_project_details.contact_person || this.form.customer_project_details.customer_name;
            }
            if (!this.form.customer_confirmation.contact_no) {
                this.form.customer_confirmation.contact_no = this.form.customer_project_details.mobile_no;
            }

            this.$watch('currentStep', (val) => {
                if (val === 5) {
                    this.$nextTick(() => this.setupCanvas());
                }
            });
            if (this.currentStep === 5) {
                this.$nextTick(() => this.setupCanvas());
            }
        },

        calculateAverageRating() {
            const ratings = this.form.ratings_experience;
            const keys = Object.keys(ratings);
            if (keys.length === 0) return '0.0';
            let sum = 0;
            let count = 0;
            for (let k of keys) {
                const val = parseFloat(ratings[k]);
                if (!isNaN(val) && val > 0) {
                    sum += val;
                    count++;
                }
            }
            if (count === 0) return '0.0';
            return (sum / count).toFixed(1);
        },

        setupCanvas() {
            const canvas = document.getElementById('signaturePad');
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            const targetW = Math.round(rect.width * dpr);
            const targetH = Math.round(rect.height * dpr);

            if (canvas.width !== targetW || canvas.height !== targetH) {
                const existingData = this.form.customer_confirmation.client_signature;
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
            } else if (this.form.customer_confirmation.client_signature && !this.hasSignature) {
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    this.hasSignature = true;
                };
                img.src = this.form.customer_confirmation.client_signature;
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
            const canvas = document.getElementById('signaturePad') || e?.target;
            if (canvas) {
                if (canvas.releasePointerCapture && e?.pointerId !== undefined) {
                    try { canvas.releasePointerCapture(e.pointerId); } catch(err) {}
                }
                this.form.customer_confirmation.client_signature = canvas.toDataURL('image/png');
                this.triggerAutoSave();
            }
        },

        clearSignature() {
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            this.form.customer_confirmation.client_signature = '';
            this.hasSignature = false;
            this.triggerAutoSave();
        },

        nextStep() {
            if (this.currentStep < 5) {
                this.currentStep++;
                this.saveDraft();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        triggerAutoSave() {
            clearTimeout(this._saveTimer);
            this.saveStatus = 'Unsaved changes...';
            this.saveStatusColor = 'text-amber-400';
            this._saveTimer = setTimeout(() => this.saveDraft(), 800);
        },

        saveDraftManual() {
            this.saveDraft(true);
        },

        saveDraft(isManual = false) {
            this.saveStatus = 'Saving...';
            this.saveStatusColor = 'text-blue-400';

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
                    this.saveStatus = isManual ? 'Saved successfully!' : 'All changes saved';
                    this.saveStatusColor = 'text-emerald-400';
                } else {
                    this.saveStatus = 'Error saving draft';
                    this.saveStatusColor = 'text-rose-400';
                }
            })
            .catch(() => {
                this.saveStatus = 'Saved locally (offline)';
                this.saveStatusColor = 'text-slate-400';
            });
        },

        confirmAndSubmit() {
            if (!this.form.customer_confirmation.client_confirmed) {
                alert('Please check the Work Acceptance Confirmation box before submitting.');
                return;
            }
            if (!this.hasSignature && !this.form.customer_confirmation.client_signature) {
                alert('Please capture the client digital signature on screen before submitting.');
                return;
            }
            this.showConfirmModal = true;
        },

        submitFinal() {
            this.saveStatus = 'Submitting report...';
            // Final sync of signature before form POST
            const canvas = document.getElementById('signaturePad');
            if (canvas && this.hasSignature) {
                this.form.customer_confirmation.client_signature = canvas.toDataURL('image/png');
            }

            // Save draft first then submit
            fetch(this.saveDraftUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    current_step: 5,
                    sections: this.form
                })
            })
            .finally(() => {
                document.getElementById('reportForm').submit();
            });
        }
    };
}
</script>
@endsection
