@extends('layouts.admin')

@section('title', 'Customer Feedback #' . $report->report_number . ' - SolarOps')
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
                    <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-amber-200">
                        Customer Feedback & Satisfaction
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                    @if($report->service)
                        <span class="text-xs text-slate-500 font-medium">Job: #{{ $report->service->service_number }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $sections['customer_project_details']['customer_name'] ?? ($report->customer?->name ?? 'Customer') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Submitted By Engineer: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
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

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this customer feedback report? This marks verification complete.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Feedback</span>
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
        <!-- 1. Customer & Project Details -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Customer & Project Details</span>
                <span class="text-amber-700 font-bold uppercase text-[11px]">{{ $sections['customer_project_details']['type_of_visit'] ?? 'Visit' }}</span>
            </h3>
            <div class="space-y-2 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Customer Name</span> <strong class="text-slate-900 text-sm">{{ $sections['customer_project_details']['customer_name'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Site Address</span> {{ $sections['customer_project_details']['site_address'] ?? '—' }}</div>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Mobile No.</span> {{ $sections['customer_project_details']['mobile_no'] ?? '—' }}</div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Contact Person</span> {{ $sections['customer_project_details']['contact_person'] ?? '—' }}</div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Feedback Date</span> {{ $sections['customer_project_details']['feedback_date'] ?? '—' }}</div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Installation Date</span> {{ $sections['customer_project_details']['installation_date'] ?? '—' }}</div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">System Capacity</span> <strong class="text-slate-800">{{ $sections['customer_project_details']['system_capacity'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Type of Visit</span> <span class="uppercase font-bold text-slate-800">{{ $sections['customer_project_details']['type_of_visit'] ?? '—' }}</span></div>
                </div>
            </div>
        </div>

        <!-- 2. Experience Ratings Breakdown -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            @php
                $ratings = $sections['ratings_experience'] ?? [];
                $scores = array_filter(array_map('floatval', $ratings));
                $avgScore = count($scores) > 0 ? number_format(array_sum($scores) / count($scores), 1) : '—';
                $ratingLabels = [
                    'rate_quality_work' => 'Quality of installation / work',
                    'rate_quality_materials' => 'Quality of materials / components',
                    'rate_professionalism' => 'Professionalism of technical team',
                    'rate_behaviour_communication' => 'Behaviour and communication of staff',
                    'rate_punctuality' => 'Punctuality and completion of work',
                    'rate_cleanliness' => 'Cleanliness after completion of work',
                    'rate_explanation_operation' => 'Explanation of system operation',
                    'rate_explanation_safety' => 'Explanation of safety / maintenance',
                    'rate_response_questions' => 'Response to questions / concerns',
                    'rate_overall_satisfaction' => 'Overall satisfaction',
                ];
            @endphp
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. 10-Point Experience Ratings</span>
                <span class="font-mono font-bold text-xs bg-amber-100 text-amber-900 px-2.5 py-0.5 rounded-lg">
                    Avg: {{ $avgScore }} / 5.0
                </span>
            </h3>
            <div class="space-y-1.5 text-xs">
                @foreach($ratingLabels as $key => $title)
                    @php $val = (int)($ratings[$key] ?? 0); @endphp
                    <div class="flex items-center justify-between p-1.5 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="text-slate-700 text-[11px] truncate mr-2">{{ $loop->iteration }}. {{ $title }}</span>
                        <div class="flex items-center gap-1 shrink-0">
                            <span class="font-mono font-bold text-[11px] px-2 py-0.5 rounded
                                {{ $val >= 4 ? 'bg-emerald-100 text-emerald-800' : ($val === 3 ? 'bg-amber-100 text-amber-800' : ($val > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-600')) }}">
                                {{ $val > 0 ? $val . ' / 5' : '—' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. Solar System & Service Feedback -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>3. System & Service Feedback</span>
                <span class="text-xs text-slate-400 font-semibold">Q&A Audit</span>
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Solar System Feedback</span>
                    <div class="space-y-1">
                        @php
                            $solarQs = [
                                'work_satisfactory' => 'Work satisfactory?',
                                'performing_as_explained' => 'Performing as explained?',
                                'components_explained' => 'Components explained properly?',
                                'safety_explained' => 'Safety precautions explained?',
                                'maintenance_explained' => 'Routine maintenance explained?',
                            ];
                        @endphp
                        @foreach($solarQs as $k => $l)
                            @php $ans = $sections['solar_system_feedback'][$k] ?? '—'; @endphp
                            <div class="flex items-center justify-between p-1.5 rounded bg-slate-50 border border-slate-100">
                                <span class="text-slate-700 text-[11px]">{{ $l }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $ans === 'YES' ? 'bg-emerald-100 text-emerald-800' : ($ans === 'NO' ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $ans }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[10px] font-bold uppercase text-slate-500 block mb-1">Service / Complaint Attendance</span>
                    <div class="space-y-1">
                        @php
                            $serviceQs = [
                                'concern_understood' => 'Complaint understood correctly?',
                                'response_time_satisfactory' => 'Technician response time satisfactory?',
                                'issue_rectified' => 'Issue properly rectified?',
                                'solution_explained' => 'Solution explained to customer?',
                                'system_functioning' => 'System now functioning satisfactorily?',
                                'work_pending' => 'Any work/issue still pending?',
                            ];
                        @endphp
                        @foreach($serviceQs as $k => $l)
                            @php $ans = $sections['service_complaint_feedback'][$k] ?? '—'; @endphp
                            <div class="flex items-center justify-between p-1.5 rounded bg-slate-50 border border-slate-100">
                                <span class="text-slate-700 text-[11px]">{{ $l }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $ans === 'YES' || $ans === 'NO' && $k === 'work_pending' ? 'bg-emerald-100 text-emerald-800' : ($ans === 'YES — DETAILS BELOW' || $ans === 'NO' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $ans }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Comments & Recommendation -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>4. Customer Comments & Voice</span>
                <span class="text-amber-500 font-bold font-mono">
                    ★ {{ $sections['overall_recommendation']['overall_experience'] ?? '—' }} / 5
                </span>
            </h3>

            <div class="space-y-2.5 text-xs">
                @if(!empty($sections['comments_suggestions']['what_done_well']))
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold uppercase text-slate-500 block">What Did We Do Well?</span>
                        <p class="mt-0.5 text-slate-800 font-medium">{{ $sections['comments_suggestions']['what_done_well'] }}</p>
                    </div>
                @endif

                @if(!empty($sections['comments_suggestions']['what_to_improve']))
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold uppercase text-slate-500 block">What Can We Improve?</span>
                        <p class="mt-0.5 text-slate-800 font-medium">{{ $sections['comments_suggestions']['what_to_improve'] }}</p>
                    </div>
                @endif

                @if(!empty($sections['comments_suggestions']['other_feedback']))
                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold uppercase text-slate-500 block">Other Feedback / Suggestion</span>
                        <p class="mt-0.5 text-slate-800 font-medium">{{ $sections['comments_suggestions']['other_feedback'] }}</p>
                    </div>
                @endif

                @if(!empty($sections['comments_suggestions']['pending_issue_details']))
                    <div class="bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                        <span class="text-[10px] font-bold uppercase text-amber-800 block">Pending Issue Details</span>
                        <p class="mt-0.5 text-amber-950 font-medium">{{ $sections['comments_suggestions']['pending_issue_details'] }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <div class="p-2 bg-slate-50 rounded border">
                        <span class="text-[10px] uppercase text-slate-400 block font-semibold">Recommend to Others</span>
                        <strong class="text-slate-900">{{ $sections['overall_recommendation']['recommend_services'] ?? '—' }}</strong>
                    </div>
                    <div class="p-2 bg-slate-50 rounded border">
                        <span class="text-[10px] uppercase text-slate-400 block font-semibold">Future Testimonial</span>
                        <strong class="text-slate-900">{{ $sections['overall_recommendation']['contact_for_testimonial'] ?? '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Customer Confirmation & Digital Signature -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>5. Customer Confirmation</span>
                @if(!empty($sections['customer_confirmation']['client_confirmed']))
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Work Acceptance Confirmed
                    </span>
                @endif
            </h3>

            <div class="space-y-2 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Customer Signer Name</span> <strong class="text-slate-900">{{ $sections['customer_confirmation']['customer_name'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Contact No.</span> <span class="text-slate-900">{{ $sections['customer_confirmation']['contact_no'] ?? '—' }}</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Date of Signing</span> <span class="text-slate-900">{{ $sections['customer_confirmation']['feedback_date'] ?? '—' }}</span></div>
            </div>

            @if(!empty($sections['customer_confirmation']['client_signature']))
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Customer Digital Signature</span>
                    <div class="bg-slate-50 p-2 rounded-xl border border-slate-200 inline-block">
                        <img src="{{ $sections['customer_confirmation']['client_signature'] }}" alt="Client Signature" class="max-h-20 object-contain bg-white p-2 rounded border border-slate-200">
                    </div>
                </div>
            @endif
        </div>

        <!-- 6. Office Use & Audit Verification -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>6. For Office Use (Operations Review)</span>
                <span class="text-[10px] text-slate-500 font-mono">Internal</span>
            </h3>

            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Feedback Reviewed By</span>
                    <strong class="text-slate-900">{{ $report->reviewer?->name ?? (auth()->user()->name) }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Review Date</span>
                    <span class="text-slate-900">{{ $report->reviewed_at ? $report->reviewed_at->format('d M Y') : now()->format('d M Y') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Action Required Status</span>
                    @if(!empty($sections['service_complaint_feedback']['work_pending']) && $sections['service_complaint_feedback']['work_pending'] !== 'NO')
                        <span class="inline-block mt-1 px-2.5 py-1 bg-amber-100 text-amber-900 font-bold rounded-lg border border-amber-300">
                            YES — Pending customer action required
                        </span>
                    @else
                        <span class="inline-block mt-1 px-2.5 py-1 bg-emerald-100 text-emerald-900 font-bold rounded-lg border border-emerald-300">
                            NO — Customer satisfaction verified, no follow-up required
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Request Correction -->
    <div x-show="showCorrectionModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showCorrectionModal = false">
            <h3 class="text-base font-extrabold text-slate-900">Request Form Correction</h3>
            <p class="text-xs text-slate-500">Specify instructions for the field engineer to correct and resubmit this feedback sheet.</p>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-4">
                @csrf
                <textarea name="correction_notes" rows="3" required placeholder="Describe required correction..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCorrectionModal = false"
                            class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" 
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl shadow-xs">Send Correction Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Reject -->
    <div x-show="showRejectModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showRejectModal = false">
            <h3 class="text-base font-extrabold text-slate-900 text-rose-600">Reject Feedback Report</h3>
            <p class="text-xs text-slate-500">Provide reason for permanently rejecting this report.</p>
            <form action="{{ route('admin.reports.reject', $report->id) }}" method="POST" class="space-y-4">
                @csrf
                <textarea name="rejection_reason" rows="3" required placeholder="Reason for rejection..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRejectModal = false"
                            class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" 
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
