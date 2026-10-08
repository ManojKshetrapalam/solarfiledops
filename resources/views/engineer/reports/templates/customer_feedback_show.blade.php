@extends('layouts.engineer')

@section('mobile_title', 'Feedback #' . $report->report_number)
@section('header_back_url', route('engineer.reports.index'))

@section('engineer_content')
<div class="space-y-4 pb-32 sm:pb-36">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-mono font-bold bg-slate-900 text-amber-400 px-3 py-1 rounded-lg">
                {{ $report->report_number }}
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                {{ strtoupper(str_replace('_', ' ', $report->status)) }}
            </span>
        </div>

        <h2 class="text-lg font-extrabold text-slate-900 leading-tight">
            Customer Feedback & Satisfaction Survey
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ $sections['customer_project_details']['customer_name'] ?? ($report->customer?->name ?? 'Customer') }} &bull; {{ $report->company->name }}
        </p>

        @if($report->status === 'approved')
            <div class="mt-4 p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold block">Feedback Approved & Verified</span>
                    <span class="text-[11px] text-emerald-700">Reviewed by {{ $report->reviewer?->name ?? 'Admin' }} on {{ $report->approved_at?->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        @elseif($report->status === 'correction_required')
            <div class="mt-4 p-3.5 bg-amber-500 text-slate-950 rounded-xl border border-amber-600 text-xs">
                <span class="font-bold block">Correction Requested:</span>
                <p class="mt-1 bg-amber-400 p-2 rounded-lg font-medium text-slate-950">"{{ $report->correction_notes }}"</p>
                <div class="mt-3">
                    <a href="{{ route('engineer.reports.edit', $report->id) }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-950 text-white rounded-lg font-bold text-xs shadow-xs">
                        <span>Edit & Resubmit Form</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @else
            <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-200 text-blue-900 text-xs">
                <span class="font-bold block">Submitted for Operations Review</span>
                <span class="text-[11px] text-blue-700">Submitted on {{ $report->submitted_at?->format('d M Y, h:i A') }}</span>
            </div>
        @endif
    </div>

    <!-- 1. Customer & Project Details -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-2 text-xs">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">1. Customer & Project Details</h3>
        <div class="grid grid-cols-2 gap-2 text-slate-700">
            <div><span class="text-slate-400 block text-[10px]">Customer Name</span> <strong class="text-slate-900">{{ $sections['customer_project_details']['customer_name'] ?? '—' }}</strong></div>
            <div><span class="text-slate-400 block text-[10px]">Date of Feedback</span> {{ $sections['customer_project_details']['feedback_date'] ?? '—' }}</div>
            <div class="col-span-2"><span class="text-slate-400 block text-[10px]">Site Address</span> {{ $sections['customer_project_details']['site_address'] ?? '—' }}</div>
            <div><span class="text-slate-400 block text-[10px]">Mobile No.</span> {{ $sections['customer_project_details']['mobile_no'] ?? '—' }}</div>
            <div><span class="text-slate-400 block text-[10px]">Contact Person</span> {{ $sections['customer_project_details']['contact_person'] ?? '—' }}</div>
            <div><span class="text-slate-400 block text-[10px]">System Capacity</span> {{ $sections['customer_project_details']['system_capacity'] ?? '—' }}</div>
            <div><span class="text-slate-400 block text-[10px]">Type of Visit</span> <span class="uppercase font-bold text-amber-600">{{ $sections['customer_project_details']['type_of_visit'] ?? '—' }}</span></div>
        </div>
    </div>

    <!-- 2. 10-Point Experience Ratings -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-2 text-xs">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1 flex items-center justify-between">
            <span>2. Customer Experience Ratings</span>
            <span class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded text-[10px] font-bold">1 to 5 Scale</span>
        </h3>
        @php
            $ratingLabels = [
                'rate_quality_work' => 'Quality of installation / work',
                'rate_quality_materials' => 'Quality of materials / components',
                'rate_professionalism' => 'Professionalism of technical team',
                'rate_behaviour_communication' => 'Behaviour and communication of staff',
                'rate_punctuality' => 'Punctuality and completion of work',
                'rate_cleanliness' => 'Cleanliness after completion of work',
                'rate_explanation_operation' => 'Explanation of system operation',
                'rate_explanation_safety' => 'Explanation of safety / maintenance requirements',
                'rate_response_questions' => 'Response to questions / concerns',
                'rate_overall_satisfaction' => 'Overall satisfaction',
            ];
        @endphp
        <div class="space-y-1.5">
            @foreach($ratingLabels as $key => $title)
                @php $val = (int)($sections['ratings_experience'][$key] ?? 0); @endphp
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200">
                    <span class="text-slate-700 text-[11px]">{{ $loop->iteration }}. {{ $title }}</span>
                    <span class="font-mono font-bold text-xs px-2 py-0.5 rounded
                        {{ $val >= 4 ? 'bg-emerald-100 text-emerald-800' : ($val === 3 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                        {{ $val > 0 ? $val . ' / 5' : '—' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. System & Service Questions -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b pb-1">3. Handover & Service Briefing</h3>
        
        <div class="space-y-2">
            <span class="font-bold uppercase text-[10px] text-slate-500 block">Solar System Questions</span>
            <div class="grid grid-cols-1 gap-1.5">
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Work satisfactory?</span>
                    <strong class="font-mono">{{ $sections['solar_system_feedback']['work_satisfactory'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Performing as explained?</span>
                    <strong class="font-mono">{{ $sections['solar_system_feedback']['performing_as_explained'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Components explained properly?</span>
                    <strong class="font-mono">{{ $sections['solar_system_feedback']['components_explained'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Safety precautions explained?</span>
                    <strong class="font-mono">{{ $sections['solar_system_feedback']['safety_explained'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Routine maintenance explained?</span>
                    <strong class="font-mono">{{ $sections['solar_system_feedback']['maintenance_explained'] ?? '—' }}</strong>
                </div>
            </div>
        </div>

        <div class="space-y-2 pt-2 border-t border-slate-100">
            <span class="font-bold uppercase text-[10px] text-slate-500 block">Service & Complaint Attendance</span>
            <div class="grid grid-cols-1 gap-1.5">
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Complaint understood correctly?</span>
                    <strong class="font-mono">{{ $sections['service_complaint_feedback']['concern_understood'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Response time satisfactory?</span>
                    <strong class="font-mono">{{ $sections['service_complaint_feedback']['response_time_satisfactory'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Issue properly rectified?</span>
                    <strong class="font-mono">{{ $sections['service_complaint_feedback']['issue_rectified'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>System functioning satisfactorily?</span>
                    <strong class="font-mono">{{ $sections['service_complaint_feedback']['system_functioning'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between p-2 bg-slate-50 rounded border">
                    <span>Any work or issue still pending?</span>
                    <strong class="font-mono text-amber-700">{{ $sections['service_complaint_feedback']['work_pending'] ?? '—' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Comments & Recommendation -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b pb-1">4. Feedback & Recommendation</h3>
        
        @if(!empty($sections['comments_suggestions']['what_done_well']))
            <div>
                <span class="font-semibold text-slate-500 block text-[10px] uppercase">What did we do well?</span>
                <p class="mt-0.5 text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $sections['comments_suggestions']['what_done_well'] }}</p>
            </div>
        @endif

        @if(!empty($sections['comments_suggestions']['what_to_improve']))
            <div>
                <span class="font-semibold text-slate-500 block text-[10px] uppercase">What can we improve?</span>
                <p class="mt-0.5 text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $sections['comments_suggestions']['what_to_improve'] }}</p>
            </div>
        @endif

        @if(!empty($sections['comments_suggestions']['other_feedback']))
            <div>
                <span class="font-semibold text-slate-500 block text-[10px] uppercase">Other Feedback / Suggestions</span>
                <p class="mt-0.5 text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $sections['comments_suggestions']['other_feedback'] }}</p>
            </div>
        @endif

        @if(!empty($sections['comments_suggestions']['pending_issue_details']))
            <div>
                <span class="font-bold text-amber-700 block text-[10px] uppercase">Pending Issue Details</span>
                <p class="mt-0.5 text-slate-900 bg-amber-50 p-2.5 rounded-lg border border-amber-200">{{ $sections['comments_suggestions']['pending_issue_details'] }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
            <div>
                <span class="text-slate-400 block text-[10px]">Overall Experience</span>
                <strong class="text-amber-500 font-mono text-sm">★ {{ $sections['overall_recommendation']['overall_experience'] ?? '—' }} / 5</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px]">Recommend Services</span>
                <strong class="text-slate-900 font-bold">{{ $sections['overall_recommendation']['recommend_services'] ?? '—' }}</strong>
            </div>
        </div>
    </div>

    <!-- 5. Client Confirmation & Signature -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs space-y-3 text-xs">
        <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider border-b pb-1 flex items-center justify-between">
            <span>5. Client Confirmation & Signature</span>
            @if(!empty($sections['customer_confirmation']['client_confirmed']))
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirmed by Client
                </span>
            @endif
        </h3>

        <div class="grid grid-cols-2 gap-2 text-slate-700">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Signer Name</span>
                <strong class="text-slate-900">{{ $sections['customer_confirmation']['customer_name'] ?? '—' }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Contact No.</span>
                <span class="text-slate-900">{{ $sections['customer_confirmation']['contact_no'] ?? '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Date</span>
                <span class="text-slate-900">{{ $sections['customer_confirmation']['feedback_date'] ?? '—' }}</span>
            </div>
        </div>

        @if(!empty($sections['customer_confirmation']['client_signature']))
            <div class="pt-2 border-t border-slate-100">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Customer Digital Signature</span>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 inline-block max-w-full">
                    <img src="{{ $sections['customer_confirmation']['client_signature'] }}" alt="Client Digital Signature" class="max-h-20 object-contain bg-white rounded-lg border border-slate-200 px-4 py-1.5">
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
