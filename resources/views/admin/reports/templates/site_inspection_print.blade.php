<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Inspection Feasibility - {{ $report->report_number }}</title>
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
                    SITE INSPECTION SURVEY REPORT
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
                <strong class="block uppercase font-bold text-[10px] text-slate-500 mb-1">Customer & Proposed Site</strong>
                <p class="font-bold text-sm text-slate-900">{{ $report->customer?->name ?? 'Survey Project' }}</p>
                <p class="text-slate-700 mt-0.5">{{ $report->site?->name ?? 'Proposed Site' }}</p>
                <p class="text-slate-600 text-[11px]">{{ $sections['customer_site_details']['customer_address'] ?? ($report->site?->address ?? '—') }}</p>
            </div>

            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50 space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Survey Date:</span>
                    <strong class="text-slate-900">{{ $report->created_at->format('d M Y') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Building / Site Type:</span>
                    <strong class="text-slate-900">{{ $sections['customer_site_details']['building_type'] ?? 'Commercial' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Site Condition:</span>
                    <span class="text-slate-900 font-semibold">{{ $sections['customer_site_details']['site_condition'] ?? 'Existing' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Survey Engineer:</span>
                    <span class="text-slate-900">{{ $report->engineer->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SITE FEASIBILITY & POWER SPECIFICATIONS -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Site Survey Measurements & Technical Infrastructure
        </div>

        <div class="p-3 space-y-3">
            <!-- 1. Contacts & Site Directory -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    1. Site Contacts Directory
                </h3>
                <div class="grid grid-cols-4 gap-2 text-[10px]">
                    <div><span class="text-slate-500 block text-[9px]">Client Contact:</span> {{ $sections['customer_site_details']['client_contact'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Main Incharge:</span> {{ $sections['customer_site_details']['main_incharge'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Site Incharge:</span> {{ $sections['customer_site_details']['site_incharge'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Caretaker / Security:</span> {{ $sections['customer_site_details']['caretaker_contact'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 2. Power Requirements & EB Sanctioned Meters -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    2. Power Requirements & Electricity Board (EB) Meters
                </h3>
                <p class="text-[11px] mb-1.5"><strong class="text-slate-700">Solar Requirement / Capacity Proposal:</strong> {{ $sections['power_req_meters']['solar_req_details'] ?? '—' }}</p>

                <div class="grid grid-cols-3 gap-2 text-center bg-slate-50 p-2 rounded border border-slate-200 mb-1.5 text-[10px]">
                    <div class="border-r border-slate-200">
                        <span class="font-bold block text-indigo-900">EB Meter #1</span>
                        <div class="font-mono font-bold text-xs">{{ $sections['power_req_meters']['meter1_kw'] ?? '—' }} kW</div>
                        <div class="text-[9px] text-slate-500">{{ $sections['power_req_meters']['meter1_phase'] ?? '—' }} &bull; {{ $sections['power_req_meters']['meter1_number'] ?? '—' }}</div>
                    </div>
                    <div class="border-r border-slate-200">
                        <span class="font-bold block text-indigo-900">EB Meter #2</span>
                        <div class="font-mono font-bold text-xs">{{ $sections['power_req_meters']['meter2_kw'] ?? '—' }} kW</div>
                        <div class="text-[9px] text-slate-500">{{ $sections['power_req_meters']['meter2_phase'] ?? '—' }} &bull; {{ $sections['power_req_meters']['meter2_number'] ?? '—' }}</div>
                    </div>
                    <div>
                        <span class="font-bold block text-indigo-900">EB Meter #3</span>
                        <div class="font-mono font-bold text-xs">{{ $sections['power_req_meters']['meter3_kw'] ?? '—' }} kW</div>
                        <div class="text-[9px] text-slate-500">{{ $sections['power_req_meters']['meter3_phase'] ?? '—' }} &bull; {{ $sections['power_req_meters']['meter3_number'] ?? '—' }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[10px]">
                    <div><span class="text-slate-500">Day Load Pattern:</span> {{ $sections['power_req_meters']['day_load_details'] ?? '—' }}</div>
                    <div><span class="text-slate-500">Night Load Pattern:</span> {{ $sections['power_req_meters']['night_load_details'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 3. Cabling & Conduit Runways -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    3. Cabling & Conduit Infrastructure Estimates
                </h3>
                <div class="grid grid-cols-2 gap-2 text-[10px]">
                    <div>
                        <span class="font-bold text-slate-700 block mb-0.5">Estimated Cable Runs:</span>
                        <ul class="list-disc list-inside space-y-0.5 text-slate-800">
                            @for($c=1; $c<=5; $c++)
                                @if(!empty($sections['cabling_conduits']['cable_req_' . $c]))
                                    <li><strong>Run #{{ $c }}:</strong> {{ $sections['cabling_conduits']['cable_req_' . $c] }}</li>
                                @endif
                            @endfor
                        </ul>
                    </div>
                    <div>
                        <span class="font-bold text-slate-700 block mb-0.5">Estimated Conduit / Piping:</span>
                        <ul class="list-disc list-inside space-y-0.5 text-slate-800">
                            @for($p=1; $p<=5; $p++)
                                @if(!empty($sections['cabling_conduits']['conduit_req_' . $p]))
                                    <li><strong>Pipe #{{ $p }}:</strong> {{ $sections['cabling_conduits']['conduit_req_' . $p] }}</li>
                                @endif
                            @endfor
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 4. Rooftop & Transport Logistics -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    4. Rooftop Condition, Earthing & Logistics
                </h3>
                <div class="grid grid-cols-4 gap-2 text-[10px]">
                    <div><span class="text-slate-500 block text-[9px]">Roof Type:</span> <strong>{{ $sections['rooftop_logistics']['roof_type'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[9px]">Available Area:</span> <strong>{{ $sections['rooftop_logistics']['roof_measurements'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[9px]">Lifting Method:</span> {{ $sections['rooftop_logistics']['lifting_method'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Vehicle Access:</span> {{ $sections['rooftop_logistics']['vehicle_entrance'] ?? '—' }}</div>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-1.5 pt-1 border-t border-slate-100 text-[10px]">
                    <div><span class="text-slate-500">DC / AC Earthing:</span> {{ $sections['earthing_rooms_protection']['dc_earthing'] ?? '—' }} / {{ $sections['earthing_rooms_protection']['ac_earthing'] ?? '—' }}</div>
                    <div><span class="text-slate-500">Lightning Arrester:</span> {{ $sections['earthing_rooms_protection']['la_status'] ?? '—' }}</div>
                    <div><span class="text-slate-500">PCU / Battery Room:</span> {{ $sections['earthing_rooms_protection']['pcu_room_details'] ?? '—' }} / {{ $sections['earthing_rooms_protection']['battery_room_details'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Evidences in Print -->
    @if($report->photos->count() > 0)
        <div class="border border-slate-900 mb-4 page-break-inside-avoid">
            <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
                Site Survey Photographic Evidence & Rooftop Views
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
        <!-- Survey Done By -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Survey Done By</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->engineer->name }}</p>
                <p class="text-[10px] text-slate-500">{{ $report->engineer->designation }}</p>
                <p class="text-[9px] font-mono text-slate-400">ID: {{ $report->engineer->employee_code }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Surveyor Signature & Date</span>
        </div>

        <!-- Checked By -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Joint Survey With (Client)</span>
            <div class="my-auto text-center">
                @if(!empty($sections['rooftop_logistics']['client_signature']))
                    <img src="{{ $sections['rooftop_logistics']['client_signature'] }}" alt="Client Signature" class="max-h-9 object-contain mx-auto mb-0.5">
                @endif
                <p class="font-bold text-xs text-slate-900 leading-tight">{{ $sections['rooftop_logistics']['survey_with_name'] ?? '—' }}</p>
                <p class="text-[9px] text-slate-500">Phone: {{ $sections['rooftop_logistics']['survey_with_phone'] ?? '—' }}</p>
                @if(!empty($sections['rooftop_logistics']['client_confirmed']))
                    <p class="text-[8px] text-emerald-700 font-semibold">(Joint Feasibility Verified)</p>
                @endif
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Client Signature & Seal</span>
        </div>

        <!-- For Office Use -->
        <div class="p-3 flex flex-col justify-between h-28 bg-slate-50/60">
            <span class="font-bold text-[10px] uppercase text-slate-500">For Office Use / Feasibility Approved</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->reviewer?->name ?? 'Design Head' }}</p>
                <p class="text-[10px] text-emerald-700 font-semibold">{{ $report->isApproved() ? 'STATUS: APPROVED' : 'STATUS: ' . strtoupper($report->status) }}</p>
                <p class="text-[9px] text-slate-400">{{ $report->approved_at?->format('d M Y, h:i A') }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Projects Director</span>
        </div>
    </div>
</body>
</html>
