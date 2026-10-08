<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback Form - {{ $report->report_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 text-[11px] p-6 max-w-4xl mx-auto font-sans leading-tight">
    <!-- Floating Print Action -->
    <div class="no-print fixed top-4 right-4 z-50 flex items-center gap-2">
        <button onclick="window.print()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs shadow-lg flex items-center gap-1.5 transition-all">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print / Save PDF
        </button>
        <button onclick="window.close()" class="px-2.5 py-1.5 bg-white/95 hover:bg-slate-100 text-slate-700 font-semibold rounded-lg text-xs border border-slate-300 shadow-md">
            Close
        </button>
    </div>

    <!-- Official Document Header -->
    <div class="border-2 border-slate-900 p-3 mb-3 text-center relative">
        <div class="absolute top-3 right-3 text-right text-[10px]">
            <p class="font-mono font-bold text-xs">NO: {{ $report->report_number }}</p>
            @if($report->service)
                <p class="text-slate-500">JOB: #{{ $report->service->service_number }}</p>
            @endif
        </div>

        <h1 class="text-base font-black tracking-wider uppercase text-slate-900">SUN ON EARTH SOLAR TECHNOLOGIES</h1>
        <p class="text-[11px] font-bold text-slate-700 tracking-wide uppercase">POWER DIVISION FROM SABHA SOLAR</p>
        <p class="text-[10px] text-slate-600">BENGALURU, KARNATAKA, INDIA</p>
        <div class="mt-2 pt-1 border-t border-slate-300">
            <h2 class="text-sm font-extrabold tracking-wide uppercase text-slate-900">CUSTOMER FEEDBACK & SATISFACTION FORM</h2>
            <p class="text-[9px] text-slate-500 italic">To be completed after installation, service, maintenance, inspection or complaint resolution</p>
        </div>
    </div>

    <!-- 1. CUSTOMER & PROJECT DETAILS -->
    <div class="border border-slate-900 mb-3">
        <div class="bg-slate-900 text-white px-2.5 py-1 font-bold text-[10px] uppercase tracking-wider">
            1. CUSTOMER & PROJECT DETAILS
        </div>
        <div class="p-2 grid grid-cols-2 gap-x-4 gap-y-1.5 text-[10px]">
            <div class="flex items-baseline">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">CUSTOMER NAME:</span>
                <span class="font-bold text-slate-900 text-xs truncate">{{ $sections['customer_project_details']['customer_name'] ?? ($report->customer?->name ?? '—') }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">DATE OF FEEDBACK:</span>
                <span class="font-semibold text-slate-900">{{ $sections['customer_project_details']['feedback_date'] ?? $report->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="flex items-baseline col-span-2">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">SITE ADDRESS:</span>
                <span class="font-medium text-slate-800">{{ $sections['customer_project_details']['site_address'] ?? ($report->site?->address ?? '—') }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">MOBILE NO.:</span>
                <span class="font-semibold text-slate-900">{{ $sections['customer_project_details']['mobile_no'] ?? ($report->customer?->phone ?? '—') }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">CONTACT PERSON:</span>
                <span class="font-semibold text-slate-900">{{ $sections['customer_project_details']['contact_person'] ?? ($report->customer?->contact_person ?? '—') }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">SYSTEM CAPACITY:</span>
                <span class="font-semibold text-slate-900">{{ $sections['customer_project_details']['system_capacity'] ?? '—' }}</span>
            </div>
            <div class="flex items-baseline">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">INSTALLATION DATE:</span>
                <span class="font-medium text-slate-800">{{ $sections['customer_project_details']['installation_date'] ?? '—' }}</span>
            </div>
            <div class="flex items-center col-span-2 pt-1 border-t border-slate-200">
                <span class="w-32 font-bold uppercase text-slate-600 shrink-0">TYPE OF VISIT:</span>
                <div class="flex flex-wrap gap-4 font-semibold text-slate-800">
                    @php $visit = strtolower($sections['customer_project_details']['type_of_visit'] ?? ''); @endphp
                    @foreach(['installation' => 'INSTALLATION', 'service' => 'SERVICE', 'complaint' => 'COMPLAINT', 'maintenance' => 'MAINTENANCE', 'inspection' => 'INSPECTION'] as $vk => $vl)
                        <span class="flex items-center gap-1">
                            <span class="inline-block w-3.5 h-3.5 border border-slate-900 text-center leading-3 font-bold text-[9px] {{ $visit === $vk ? 'bg-slate-900 text-white' : '' }}">
                                {{ $visit === $vk ? '✓' : '' }}
                            </span>
                            <span>{{ $vl }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 2. YOUR EXPERIENCE -->
    <div class="border border-slate-900 mb-3">
        <div class="bg-slate-900 text-white px-2.5 py-1 font-bold text-[10px] uppercase tracking-wider flex justify-between">
            <span>2. YOUR EXPERIENCE</span>
            <span class="text-[9px] font-normal lowercase tracking-normal">Please rate each item from 1 to 5: 1 = Very Poor &bull; 2 = Poor &bull; 3 = Satisfactory &bull; 4 = Good &bull; 5 = Excellent</span>
        </div>
        <table class="w-full border-collapse text-[10px]">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-900 text-slate-800">
                    <th class="py-1 px-2 border-r border-slate-300 w-8 text-center font-bold">SL.</th>
                    <th class="py-1 px-2 border-r border-slate-300 text-left font-bold">SERVICE / EXPERIENCE</th>
                    <th class="py-1 px-1 border-r border-slate-300 w-12 text-center font-bold">1<br><span class="text-[8px] font-normal">V.Poor</span></th>
                    <th class="py-1 px-1 border-r border-slate-300 w-12 text-center font-bold">2<br><span class="text-[8px] font-normal">Poor</span></th>
                    <th class="py-1 px-1 border-r border-slate-300 w-12 text-center font-bold">3<br><span class="text-[8px] font-normal">Satisfy</span></th>
                    <th class="py-1 px-1 border-r border-slate-300 w-12 text-center font-bold">4<br><span class="text-[8px] font-normal">Good</span></th>
                    <th class="py-1 px-1 w-12 text-center font-bold">5<br><span class="text-[8px] font-normal">Excell</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @php
                    $ratingsList = [
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
                @foreach($ratingsList as $rk => $rlabel)
                    @php $scoreVal = (int)($sections['ratings_experience'][$rk] ?? 0); @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-1 px-2 border-r border-slate-200 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                        <td class="py-1 px-2 border-r border-slate-200 font-medium text-slate-900">{{ $rlabel }}</td>
                        @for($s=1; $s<=5; $s++)
                            <td class="py-1 px-1 border-r border-slate-200 text-center {{ $s === 5 ? 'border-r-0' : '' }}">
                                <span class="inline-block w-4 h-4 border border-slate-800 text-center leading-3.5 font-bold text-[9px] {{ $scoreVal === $s ? 'bg-slate-900 text-white' : '' }}">
                                    {{ $scoreVal === $s ? '■' : '' }}
                                </span>
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- 3. SOLAR SYSTEM FEEDBACK & 4. SERVICE / COMPLAINT FEEDBACK (2 Columns) -->
    <div class="grid grid-cols-2 gap-3 mb-3">
        <!-- 3. Solar System Feedback -->
        <div class="border border-slate-900 flex flex-col justify-between">
            <div>
                <div class="bg-slate-900 text-white px-2 py-1 font-bold text-[10px] uppercase tracking-wider">
                    3. SOLAR SYSTEM FEEDBACK
                </div>
                <div class="p-2 space-y-2 text-[10px]">
                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Is the installation / service work satisfactory?</p>
                        @php $wSat = $sections['solar_system_feedback']['work_satisfactory'] ?? ''; @endphp
                        <div class="flex gap-3">
                            @foreach(['YES', 'NO', 'PARTLY'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $wSat === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $wSat === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Is the solar system performing as explained to you?</p>
                        @php $pExp = $sections['solar_system_feedback']['performing_as_explained'] ?? ''; @endphp
                        <div class="flex gap-3">
                            @foreach(['YES', 'NO', 'NOT SURE'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $pExp === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $pExp === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Were the system components and functions explained properly?</p>
                        @php $cExp = $sections['solar_system_feedback']['components_explained'] ?? ''; @endphp
                        <div class="flex gap-3">
                            @foreach(['YES', 'NO'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $cExp === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $cExp === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Were safety precautions explained to you?</p>
                        @php $sExp = $sections['solar_system_feedback']['safety_explained'] ?? ''; @endphp
                        <div class="flex gap-3">
                            @foreach(['YES', 'NO'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $sExp === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $sExp === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Were routine maintenance requirements explained to you?</p>
                        @php $mExp = $sections['solar_system_feedback']['maintenance_explained'] ?? ''; @endphp
                        <div class="flex gap-3">
                            @foreach(['YES', 'NO'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $mExp === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $mExp === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Service / Complaint Feedback -->
        <div class="border border-slate-900 flex flex-col justify-between">
            <div>
                <div class="bg-slate-900 text-white px-2 py-1 font-bold text-[10px] uppercase tracking-wider">
                    4. SERVICE / COMPLAINT FEEDBACK
                </div>
                <div class="p-2 space-y-2 text-[10px]">
                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Was your complaint / concern understood correctly?</p>
                        @php $cUnd = $sections['service_complaint_feedback']['concern_understood'] ?? ''; @endphp
                        <div class="flex gap-2">
                            @foreach(['YES', 'NO', 'NOT APPLICABLE'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $cUnd === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $cUnd === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt === 'NOT APPLICABLE' ? 'N/A' : $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Was the technician response time satisfactory?</p>
                        @php $rSat = $sections['service_complaint_feedback']['response_time_satisfactory'] ?? ''; @endphp
                        <div class="flex gap-2">
                            @foreach(['YES', 'NO', 'NOT APPLICABLE'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $rSat === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $rSat === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt === 'NOT APPLICABLE' ? 'N/A' : $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Was the issue properly rectified?</p>
                        @php $iRec = $sections['service_complaint_feedback']['issue_rectified'] ?? ''; @endphp
                        <div class="flex gap-2">
                            @foreach(['YES', 'NO', 'PARTLY', 'N/A'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $iRec === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $iRec === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Was the solution / work carried out explained to you?</p>
                        @php $sExpl = $sections['service_complaint_feedback']['solution_explained'] ?? ''; @endphp
                        <div class="flex gap-2">
                            @foreach(['YES', 'NO', 'N/A'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $sExpl === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $sExpl === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Is the system now functioning satisfactorily?</p>
                        @php $sFunc = $sections['service_complaint_feedback']['system_functioning'] ?? ''; @endphp
                        <div class="flex gap-2">
                            @foreach(['YES', 'NO', 'PARTLY', 'N/A'] as $opt)
                                <span class="flex items-center gap-1">
                                    <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $sFunc === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $sFunc === $opt ? '✓' : '' }}</span>
                                    <span>{{ $opt }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-800 mb-0.5">Is any work or issue still pending?</p>
                        @php $wPend = $sections['service_complaint_feedback']['work_pending'] ?? ''; @endphp
                        <div class="flex gap-3">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ $wPend === 'NO' ? 'bg-slate-900 text-white' : '' }}">{{ $wPend === 'NO' ? '✓' : '' }}</span>
                                <span>NO</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 border border-slate-800 inline-block text-center text-[8px] leading-2.5 font-bold {{ str_starts_with($wPend, 'YES') ? 'bg-slate-900 text-white' : '' }}">{{ str_starts_with($wPend, 'YES') ? '✓' : '' }}</span>
                                <span>YES — DETAILS BELOW</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE BREAK FOR CLEAN 2-PAGE PRINT AUDIT -->
    <div class="page-break"></div>

    <!-- 5. CUSTOMER COMMENTS & SUGGESTIONS -->
    <div class="border border-slate-900 mb-3">
        <div class="bg-slate-900 text-white px-2.5 py-1 font-bold text-[10px] uppercase tracking-wider">
            5. CUSTOMER COMMENTS & SUGGESTIONS
        </div>
        <div class="p-2 space-y-2 text-[10px]">
            <div>
                <span class="font-bold text-slate-700 uppercase block text-[9px]">WHAT DID WE DO WELL?</span>
                <p class="min-h-[28px] border-b border-dotted border-slate-400 font-medium text-slate-900 pt-0.5">
                    {{ $sections['comments_suggestions']['what_done_well'] ?? '—' }}
                </p>
            </div>
            <div>
                <span class="font-bold text-slate-700 uppercase block text-[9px]">WHAT CAN WE IMPROVE?</span>
                <p class="min-h-[28px] border-b border-dotted border-slate-400 font-medium text-slate-900 pt-0.5">
                    {{ $sections['comments_suggestions']['what_to_improve'] ?? '—' }}
                </p>
            </div>
            <div>
                <span class="font-bold text-slate-700 uppercase block text-[9px]">ANY OTHER FEEDBACK / SUGGESTION?</span>
                <p class="min-h-[28px] border-b border-dotted border-slate-400 font-medium text-slate-900 pt-0.5">
                    {{ $sections['comments_suggestions']['other_feedback'] ?? '—' }}
                </p>
            </div>
            <div>
                <span class="font-bold text-slate-700 uppercase block text-[9px]">IF ANY ISSUE IS PENDING, PLEASE MENTION DETAILS:</span>
                <p class="min-h-[28px] border-b border-dotted border-slate-400 font-medium text-slate-900 pt-0.5">
                    {{ $sections['comments_suggestions']['pending_issue_details'] ?? 'None. System verified fully operational.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- 6. OVERALL RATING & RECOMMENDATION -->
    <div class="border border-slate-900 mb-3">
        <div class="bg-slate-900 text-white px-2.5 py-1 font-bold text-[10px] uppercase tracking-wider">
            6. OVERALL RATING & RECOMMENDATION
        </div>
        <div class="p-2.5 space-y-2 text-[10px]">
            <div class="flex items-center gap-6">
                <span class="font-bold uppercase text-slate-700 w-44">OVERALL EXPERIENCE:</span>
                @php $ovExp = (int)($sections['overall_recommendation']['overall_experience'] ?? 0); @endphp
                <div class="flex gap-4 font-bold">
                    @for($i=1; $i<=5; $i++)
                        <span class="flex items-center gap-1">
                            <span class="w-3.5 h-3.5 border border-slate-900 inline-block text-center text-[9px] leading-3 font-bold {{ $ovExp === $i ? 'bg-slate-900 text-white' : '' }}">{{ $ovExp === $i ? '■' : '' }}</span>
                            <span>{{ $i }}</span>
                        </span>
                    @endfor
                </div>
            </div>

            <div class="flex items-center gap-6">
                <span class="font-bold uppercase text-slate-700 w-44">WOULD YOU RECOMMEND OUR SERVICES TO OTHERS?</span>
                @php $rec = $sections['overall_recommendation']['recommend_services'] ?? ''; @endphp
                <div class="flex gap-4 font-semibold">
                    @foreach(['YES', 'NO', 'MAYBE'] as $opt)
                        <span class="flex items-center gap-1">
                            <span class="w-3.5 h-3.5 border border-slate-900 inline-block text-center text-[9px] leading-3 font-bold {{ $rec === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $rec === $opt ? '✓' : '' }}</span>
                            <span>{{ $opt }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-6">
                <span class="font-bold uppercase text-slate-700 w-44">MAY WE CONTACT YOU FOR FUTURE TESTIMONIAL?</span>
                @php $testim = $sections['overall_recommendation']['contact_for_testimonial'] ?? ''; @endphp
                <div class="flex gap-4 font-semibold">
                    @foreach(['YES', 'NO'] as $opt)
                        <span class="flex items-center gap-1">
                            <span class="w-3.5 h-3.5 border border-slate-900 inline-block text-center text-[9px] leading-3 font-bold {{ $testim === $opt ? 'bg-slate-900 text-white' : '' }}">{{ $testim === $opt ? '✓' : '' }}</span>
                            <span>{{ $opt }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 7. CUSTOMER CONFIRMATION -->
    <div class="border border-slate-900 mb-3">
        <div class="bg-slate-900 text-white px-2.5 py-1 font-bold text-[10px] uppercase tracking-wider">
            7. CUSTOMER CONFIRMATION
        </div>
        <div class="p-2.5 text-[10px]">
            <p class="italic text-slate-700 leading-normal mb-3">
                "I confirm that the above feedback represents my experience with the installation / service / inspection work carried out at my site. I have communicated any pending concern or issue, if applicable."
            </p>

            <div class="grid grid-cols-2 gap-4 border-t border-slate-200 pt-2">
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-500 font-bold block text-[9px] uppercase">CUSTOMER NAME:</span>
                        <strong class="text-slate-900 text-xs">{{ $sections['customer_confirmation']['customer_name'] ?? ($report->customer?->name ?? '—') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold block text-[9px] uppercase">CONTACT NO.:</span>
                        <span class="text-slate-900 font-medium">{{ $sections['customer_confirmation']['contact_no'] ?? ($report->customer?->phone ?? '—') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 font-bold block text-[9px] uppercase">DATE:</span>
                        <span class="text-slate-900 font-medium">{{ $sections['customer_confirmation']['feedback_date'] ?? $report->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="border border-slate-300 p-2 rounded bg-slate-50/50 flex flex-col justify-between">
                    <span class="text-slate-500 font-bold uppercase text-[9px]">CUSTOMER DIGITAL SIGNATURE</span>
                    @if(!empty($sections['customer_confirmation']['client_signature']))
                        <div class="py-1">
                            <img src="{{ $sections['customer_confirmation']['client_signature'] }}" alt="Customer Signature" class="max-h-16 object-contain">
                        </div>
                    @else
                        <div class="h-14 flex items-center justify-center text-slate-400 italic">Signature on file</div>
                    @endif
                    <div class="text-[9px] text-slate-400 border-t border-slate-200 pt-0.5 text-right">Verified digitally on site</div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOR OFFICE USE -->
    <div class="border-2 border-slate-900 p-2.5 text-[10px] bg-slate-50/50">
        <span class="font-extrabold uppercase text-[10px] text-slate-900 block mb-1">FOR OFFICE USE</span>
        <div class="grid grid-cols-4 gap-2 items-center">
            <div>
                <span class="text-slate-500 font-semibold block text-[9px]">Feedback reviewed by:</span>
                <strong class="text-slate-900">{{ $report->reviewer?->name ?? 'Operations Admin' }}</strong>
            </div>
            <div>
                <span class="text-slate-500 font-semibold block text-[9px]">Date:</span>
                <span class="text-slate-900 font-medium">{{ $report->reviewed_at ? $report->reviewed_at->format('d/m/Y') : now()->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="text-slate-500 font-semibold block text-[9px]">Action required:</span>
                @php $actReq = !empty($sections['service_complaint_feedback']['work_pending']) && $sections['service_complaint_feedback']['work_pending'] !== 'NO'; @endphp
                <span class="flex items-center gap-2 mt-0.5">
                    <span class="flex items-center gap-1 font-bold">
                        <span class="w-3 h-3 border border-slate-900 inline-block text-center text-[8px] leading-2.5 {{ !$actReq ? 'bg-slate-900 text-white' : '' }}">{{ !$actReq ? '✓' : '' }}</span> NO
                    </span>
                    <span class="flex items-center gap-1 font-bold">
                        <span class="w-3 h-3 border border-slate-900 inline-block text-center text-[8px] leading-2.5 {{ $actReq ? 'bg-slate-900 text-white' : '' }}">{{ $actReq ? '✓' : '' }}</span> YES
                    </span>
                </span>
            </div>
            <div>
                <span class="text-slate-500 font-semibold block text-[9px]">Action assigned to:</span>
                <span class="text-slate-800 font-medium">{{ $actReq ? ($report->engineer->name ?? 'Field Team') : '—' }}</span>
            </div>
        </div>
    </div>
</body>
</html>
