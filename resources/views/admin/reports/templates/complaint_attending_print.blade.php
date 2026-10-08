<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Attending Report - {{ $report->report_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 text-xs p-6 max-w-4xl mx-auto font-sans leading-tight">
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

    <!-- Official Header -->
    <div class="border-2 border-slate-900 p-4 mb-4">
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-3 mb-3">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight uppercase">{{ $report->company->name }}</h1>
                <p class="text-[11px] text-slate-600 max-w-lg mt-0.5">{{ $report->company->report_header_info ?? $report->company->address }}</p>
                <p class="text-[10px] text-slate-500">Phone: {{ $report->company->phone ?? '—' }} | Email: {{ $report->company->email ?? '—' }}</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-slate-900 text-white font-extrabold text-sm uppercase tracking-wider rounded">
                    COMPLAINT ATTENDING SHEET
                </span>
                <p class="font-mono font-bold text-xs mt-1">NO: {{ $report->report_number }}</p>
                @if($report->service)
                    <p class="text-[10px] text-slate-500">Job: #{{ $report->service->service_number }}</p>
                @endif
            </div>
        </div>

        <!-- Customer & Service Header -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50">
                <strong class="block uppercase font-bold text-[10px] text-slate-500 mb-1">Customer Name & Site Address</strong>
                <p class="font-bold text-sm text-slate-900">{{ $report->customer?->name ?? 'Customer Plant' }}</p>
                <p class="text-slate-700 mt-0.5">{{ $report->site?->name ?? 'Plant Site' }}</p>
                <p class="text-slate-600 text-[11px]">{{ $sections['plant_details']['customer_address'] ?? ($report->site?->address ?? '—') }}</p>
            </div>

            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50 space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Attending Date:</span>
                    <strong class="text-slate-900">{{ $report->created_at->format('d M Y') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Plant Capacity:</span>
                    <strong class="text-slate-900">{{ $sections['plant_details']['plant_capacity'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Attending Engineer:</span>
                    <span class="text-slate-900 font-semibold">{{ $report->engineer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Contact:</span>
                    <span class="text-slate-900">{{ $report->engineer->phone ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COMPLAINT & ATTENDED WORK SPECIFICATIONS -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Complaint Intake & Technical Rectification Details
        </div>

        <div class="p-3 space-y-3">
            <!-- 1. Complaint Intake Record -->
            <div class="border-b border-slate-200 pb-2">
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 mb-1">
                    1. Reported Fault / Breakdown Details
                </h3>
                <div class="bg-slate-50 p-2 rounded border border-slate-200">
                    <div class="flex justify-between text-[11px] mb-1">
                        <div><span class="text-slate-500">Complaint Received From:</span> <strong>{{ $sections['complaint_intake']['received_from'] ?? '—' }}</strong></div>
                        <div><span class="text-slate-500">Date Logged:</span> <strong>{{ $sections['complaint_intake']['complaint_date'] ?? '—' }}</strong></div>
                    </div>
                    <p class="text-xs text-rose-950 font-bold"><span class="text-rose-700 uppercase text-[10px]">Primary Fault:</span> {{ $sections['complaint_intake']['primary_complaint'] ?? '—' }}</p>
                </div>
                @if(!empty($sections['complaint_intake']['complaint_point_1']))
                    <ul class="list-disc list-inside text-[10px] text-slate-700 mt-1 space-y-0.5">
                        @for($cp=1; $cp<=3; $cp++)
                            @if(!empty($sections['complaint_intake']['complaint_point_' . $cp]))
                                <li>{{ $sections['complaint_intake']['complaint_point_' . $cp] }}</li>
                            @endif
                        @endfor
                    </ul>
                @endif
            </div>

            <!-- 2. Work Done & Spares Replaced -->
            <div class="border-b border-slate-200 pb-2">
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 mb-1">
                    2. Corrective Actions Performed & Spares
                </h3>
                <div class="grid grid-cols-2 gap-3 text-[10px]">
                    <div>
                        <span class="font-bold text-slate-700 block mb-0.5">Actions Carried Out:</span>
                        <ul class="list-disc list-inside space-y-0.5 text-slate-800">
                            @for($a=1; $a<=5; $a++)
                                @if(!empty($sections['attended_work']['action_' . $a]))
                                    <li>{{ $sections['attended_work']['action_' . $a] }}</li>
                                @endif
                            @endfor
                        </ul>
                    </div>
                    <div>
                        <div class="bg-slate-50 p-2 rounded border border-slate-200 mb-1">
                            <span class="text-slate-500 block text-[9px] uppercase font-bold">Spares / Components Replaced:</span>
                            <p class="text-slate-900 font-semibold">{{ !empty($sections['attended_work']['spares_replaced']) ? $sections['attended_work']['spares_replaced'] : 'None (Fixed On-Site)' }}</p>
                        </div>
                        @if(!empty($sections['attended_work']['attended_remarks']))
                            <p class="text-slate-600"><span class="font-bold text-slate-700">Remarks:</span> {{ $sections['attended_work']['attended_remarks'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. 9-Point Plant Health Checklist -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 mb-1">
                    3. 9-Point Post-Attending Plant Health Check
                </h3>
                <div class="grid grid-cols-3 gap-1.5 text-[10px]">
                    @php
                        $healthPoints = [
                            1 => 'Modules clean & intact',
                            2 => 'MC4 connectors firm',
                            3 => 'AJB / DCDB fuses & SPD OK',
                            4 => 'Cables dressed properly',
                            5 => 'Earthing verified',
                            6 => 'Lightning arrester intact',
                            7 => 'Inverter boots normal',
                            8 => 'Battery terminals greased',
                            9 => 'Plant generation normal',
                        ];
                    @endphp
                    @for($i=1; $i<=9; $i++)
                        <div class="flex items-center justify-between p-1 rounded bg-slate-50 border border-slate-200">
                            <span class="text-slate-700 truncate pr-1">{{ $i }}. {{ $healthPoints[$i] }}</span>
                            <span class="font-bold uppercase text-[9px] {{ ($sections['plant_checklist_9point']['check_' . $i] ?? '') === 'Yes' || ($sections['plant_checklist_9point']['check_' . $i] ?? '') === 'OK' ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $sections['plant_checklist_9point']['check_' . $i] ?? '—' }}
                            </span>
                        </div>
                    @endfor
                </div>

                @if(!empty($sections['handover_signoff']['followup_1']))
                    <div class="mt-2 text-[10px] text-slate-700">
                        <span class="font-bold uppercase text-slate-500 text-[9px]">Recommended Follow-Ups:</span>
                        <ul class="list-disc list-inside mt-0.5 space-y-0.5">
                            @for($f=1; $f<=3; $f++)
                                @if(!empty($sections['handover_signoff']['followup_' . $f]))
                                    <li>{{ $sections['handover_signoff']['followup_' . $f] }}</li>
                                @endif
                            @endfor
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Photo Evidences in Print -->
    @if($report->photos->count() > 0)
        <div class="border border-slate-900 mb-4 page-break-inside-avoid">
            <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
                Attending Photographic Evidence
            </div>
            <div class="p-3 grid grid-cols-4 gap-2">
                @foreach($report->photos->take(4) as $photo)
                    <div class="border border-slate-200 p-1 rounded text-center">
                        <img src="{{ $photo->url }}" alt="Evidence" class="w-full h-24 object-cover rounded mb-1">
                        <span class="font-bold text-[9px] uppercase block truncate">{{ str_replace('_', ' ', $photo->section_key) }}</span>
                        <span class="text-[8px] text-slate-500">{{ $photo->captured_at?->format('d M Y, h:i A') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Official Sign-off Table -->
    <div class="border-2 border-slate-900 grid grid-cols-3 divide-x-2 divide-slate-900 text-center">
        <!-- Service Done By -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Service Engineer</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->engineer->name }}</p>
                <p class="text-[10px] text-slate-500">{{ $report->engineer->designation }}</p>
                <p class="text-[9px] font-mono text-slate-400">ID: {{ $report->engineer->employee_code }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Engineer Signature & Date</span>
        </div>

        <!-- Checked By -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Plant Handover (Client)</span>
            <div class="my-auto text-center">
                @if(!empty($sections['handover_signoff']['client_signature']))
                    <img src="{{ $sections['handover_signoff']['client_signature'] }}" alt="Client Signature" class="max-h-9 object-contain mx-auto mb-0.5">
                @endif
                <p class="font-bold text-xs text-slate-900 leading-tight">{{ $sections['handover_signoff']['whom_shown'] ?? ($sections['handover_signoff']['whom_met'] ?? '—') }}</p>
                @if(!empty($sections['handover_signoff']['client_confirmed']))
                    <p class="text-[8px] text-emerald-700 font-semibold">(Rectification Inspected & Satisfied)</p>
                @endif
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Client Signature & Seal</span>
        </div>

        <!-- For Office Use -->
        <div class="p-3 flex flex-col justify-between h-28 bg-slate-50/60">
            <span class="font-bold text-[10px] uppercase text-slate-500">For Office Use / Approved By</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->reviewer?->name ?? 'Service Manager' }}</p>
                <p class="text-[10px] text-emerald-700 font-semibold">{{ $report->isApproved() ? 'STATUS: APPROVED' : 'STATUS: ' . strtoupper($report->status) }}</p>
                <p class="text-[9px] text-slate-400">{{ $report->approved_at?->format('d M Y, h:i A') }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Operations Director</span>
        </div>
    </div>
</body>
</html>
