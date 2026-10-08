<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electrical Installation Report - {{ $report->report_number }}</title>
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
                    INSTALLATION: ELECTRICAL & CABLING
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
                <p class="font-bold text-sm text-slate-900">{{ $report->customer?->name ?? 'Installation Project' }}</p>
                <p class="text-slate-700 mt-0.5">{{ $report->site?->name ?? 'Plant Site' }}</p>
                <p class="text-slate-600 text-[11px]">{{ $sections['site_plant_info']['customer_address'] ?? ($report->site?->address ?? '—') }}</p>
            </div>

            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50 space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Installation Date:</span>
                    <strong class="text-slate-900">{{ $sections['site_plant_info']['service_date'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Plant Capacity:</span>
                    <strong class="text-slate-900">{{ $sections['site_plant_info']['plant_capacity'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Technician Name:</span>
                    <span class="text-slate-900 font-semibold">{{ $sections['site_plant_info']['technician_name'] ?? $report->engineer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Technician Contact:</span>
                    <span class="text-slate-900">{{ $sections['site_plant_info']['technician_phone'] ?? $report->engineer->phone }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ELECTRICAL TEST CONDUCTED & INSPECTION DETAILS -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Earthing, Cabling & Electrical Distribution Work
        </div>

        <div class="p-3 space-y-3">
            <!-- 1. Earthing System Detailed -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    1. Earthing System & Measured Resistance
                </h3>
                <div class="grid grid-cols-3 gap-2 text-center bg-slate-50 p-2 rounded border border-slate-200 mb-1.5">
                    <div><span class="text-slate-500 block text-[9px] uppercase">DC Pit Resistance</span> <strong>{{ $sections['earthing_work']['dc_resistance'] ?? '—' }} &Omega;</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">AC Pit Resistance</span> <strong>{{ $sections['earthing_work']['ac_resistance'] ?? '—' }} &Omega;</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Lightning Arrester (LA)</span> <strong>{{ $sections['earthing_work']['la_resistance'] ?? '—' }} &Omega;</strong></div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <div><span class="text-slate-500">Materials Used:</span> {{ $sections['earthing_work']['materials'] ?? '—' }}</div>
                    <div><span class="text-slate-500">Way of Work:</span> {{ $sections['earthing_work']['way_of_work'] ?? '—' }}</div>
                </div>
                @if(!empty($sections['earthing_work']['remarks']))
                    <p class="text-[10px] text-slate-600 mt-1"><span class="font-semibold">Earthing Remarks:</span> {{ $sections['earthing_work']['remarks'] }}</p>
                @endif
            </div>

            <!-- 2. Array Junction Box & Strings -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    2. Array Junction Box (AJB) & String Configuration
                </h3>
                <div class="grid grid-cols-4 gap-2 text-center bg-slate-50 p-2 rounded border border-slate-200 mb-1.5">
                    <div class="border-r border-slate-200">
                        <span class="font-bold text-[10px] block text-cyan-900">String #1</span>
                        <div class="text-[10px] mt-0.5 space-y-0.5">
                            <div>{{ $sections['ajb_work']['str1_volts'] ?? '—' }} V &bull; {{ $sections['ajb_work']['str1_amps'] ?? '—' }} A</div>
                            <div>{{ $sections['ajb_work']['str1_panels'] ?? '—' }} Mod &bull; {{ $sections['ajb_work']['str1_wattage'] ?? '—' }} W</div>
                        </div>
                    </div>
                    <div class="border-r border-slate-200">
                        <span class="font-bold text-[10px] block text-cyan-900">String #2</span>
                        <div class="text-[10px] mt-0.5 space-y-0.5">
                            <div>{{ $sections['ajb_work']['str2_volts'] ?? '—' }} V &bull; {{ $sections['ajb_work']['str2_amps'] ?? '—' }} A</div>
                            <div>{{ $sections['ajb_work']['str2_panels'] ?? '—' }} Mod &bull; {{ $sections['ajb_work']['str2_wattage'] ?? '—' }} W</div>
                        </div>
                    </div>
                    <div class="border-r border-slate-200">
                        <span class="font-bold text-[10px] block text-cyan-900">String #3</span>
                        <div class="text-[10px] mt-0.5 space-y-0.5">
                            <div>{{ $sections['ajb_work']['str3_volts'] ?? '—' }} V</div>
                            <div>{{ $sections['ajb_work']['str3_amps'] ?? '—' }} A</div>
                        </div>
                    </div>
                    <div>
                        <span class="font-bold text-[10px] block text-cyan-900">String #4</span>
                        <div class="text-[10px] mt-0.5 space-y-0.5">
                            <div>{{ $sections['ajb_work']['str4_volts'] ?? '—' }} V</div>
                            <div>{{ $sections['ajb_work']['str4_amps'] ?? '—' }} A</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-[10px]">
                    <div><span class="text-slate-500">Materials:</span> {{ $sections['ajb_work']['materials_used'] ?? '—' }}</div>
                    <div><span class="text-slate-500">Mounting:</span> {{ $sections['ajb_work']['how_fixed'] ?? '—' }}</div>
                    <div><span class="text-slate-500">Safety Measures:</span> {{ $sections['ajb_work']['safety_measures'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 3. Cabling Matrix (Lines 1 to 5) -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    3. Cabling Lines & Conduit Specifications
                </h3>
                <table class="w-full text-left text-[10px] border border-slate-200">
                    <thead class="bg-slate-100 font-bold uppercase text-[9px] text-slate-600">
                        <tr class="border-b border-slate-200">
                            <th class="p-1.5">Line</th>
                            <th class="p-1.5">Material</th>
                            <th class="p-1.5">Size (Sq mm)</th>
                            <th class="p-1.5">Length (m)</th>
                            <th class="p-1.5">Laying Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="p-1.5 font-semibold">1: Solar Array to AJB</td>
                            <td class="p-1.5">{{ $sections['cabling_work']['cable1_material'] ?? '—' }}</td>
                            <td class="p-1.5 font-mono">{{ $sections['cabling_work']['cable1_size'] ?? '—' }}</td>
                            <td class="p-1.5 font-mono">{{ $sections['cabling_work']['cable1_length'] ?? '—' }}</td>
                            <td class="p-1.5">{{ $sections['cabling_work']['cable1_laying'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="p-1.5 font-semibold">2: AJB to DCDB</td>
                            <td class="p-1.5">{{ $sections['cabling_work']['cable2_material'] ?? '—' }}</td>
                            <td class="p-1.5 font-mono">{{ $sections['cabling_work']['cable2_size'] ?? '—' }}</td>
                            <td class="p-1.5 font-mono">{{ $sections['cabling_work']['cable2_length'] ?? '—' }}</td>
                            <td class="p-1.5">{{ $sections['cabling_work']['cable2_laying'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="p-1.5 font-semibold">3: DCDB to PCU (Inverter)</td>
                            <td class="p-1.5">{{ $sections['cabling_work']['cable3_material'] ?? '—' }}</td>
                            <td class="p-1.5 font-mono">{{ $sections['cabling_work']['cable3_size'] ?? '—' }}</td>
                            <td class="p-1.5 font-mono">{{ $sections['cabling_work']['cable3_length'] ?? '—' }}</td>
                            <td class="p-1.5">{{ $sections['cabling_work']['cable3_laying'] ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="grid grid-cols-2 gap-2 mt-1.5 text-[10px]">
                    <div><span class="font-semibold text-slate-700">Line 4 (PCU to ACDB):</span> {{ $sections['cabling_work']['cable4_notes'] ?? '—' }}</div>
                    <div><span class="font-semibold text-slate-700">Line 5 (ACDB to Mains):</span> {{ $sections['cabling_work']['cable5_notes'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 4. Distribution Boards -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    4. DCDB & ACDB Distribution Boards
                </h3>
                <div class="grid grid-cols-4 gap-2 text-[10px]">
                    <div><span class="text-slate-500 block text-[9px]">DCDB Materials:</span> {{ $sections['dcdb_acdb_work']['dcdb_materials'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">DCDB Fixing:</span> {{ $sections['dcdb_acdb_work']['dcdb_fixing'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">ACDB Input:</span> {{ $sections['dcdb_acdb_work']['acdb_input'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">ACDB Output:</span> {{ $sections['dcdb_acdb_work']['acdb_output'] ?? '—' }}</div>
                </div>
                @if(!empty($sections['dcdb_acdb_work']['completed_1']))
                    <div class="mt-1.5 pt-1 border-t border-slate-100">
                        <span class="font-bold text-[9px] uppercase text-slate-500 block mb-0.5">Completed Milestones:</span>
                        <ul class="list-disc list-inside text-[10px] text-slate-700 space-y-0.5">
                            @for($i=1; $i<=5; $i++)
                                @if(!empty($sections['dcdb_acdb_work']['completed_' . $i]))
                                    <li>{{ $sections['dcdb_acdb_work']['completed_' . $i] }}</li>
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
                Electrical Work Photographic Evidence
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
            <span class="font-bold text-[10px] uppercase text-slate-500">Electrical Done By</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->engineer->name }}</p>
                <p class="text-[10px] text-slate-500">{{ $report->engineer->designation }}</p>
                <p class="text-[9px] font-mono text-slate-400">ID: {{ $report->engineer->employee_code }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Technician Signature & Date</span>
        </div>

        <!-- Checked By -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Checked By (Client)</span>
            <div class="my-auto text-center">
                @if(!empty($sections['dcdb_acdb_work']['client_signature']))
                    <img src="{{ $sections['dcdb_acdb_work']['client_signature'] }}" alt="Client Signature" class="max-h-9 object-contain mx-auto mb-0.5">
                @endif
                <p class="font-bold text-xs text-slate-900 leading-tight">{{ $sections['dcdb_acdb_work']['checked_by_name'] ?? '—' }}</p>
                <p class="text-[9px] text-slate-500">Phone: {{ $sections['dcdb_acdb_work']['checked_by_phone'] ?? '—' }}</p>
                @if(!empty($sections['dcdb_acdb_work']['client_confirmed']))
                    <p class="text-[8px] text-emerald-700 font-semibold">(Work Acceptance Verified)</p>
                @endif
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Client Signature & Seal</span>
        </div>

        <!-- For Office Use -->
        <div class="p-3 flex flex-col justify-between h-28 bg-slate-50/60">
            <span class="font-bold text-[10px] uppercase text-slate-500">For Office Use / Approved By</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->reviewer?->name ?? 'Authorized Signatory' }}</p>
                <p class="text-[10px] text-emerald-700 font-semibold">{{ $report->isApproved() ? 'STATUS: APPROVED' : 'STATUS: ' . strtoupper($report->status) }}</p>
                <p class="text-[9px] text-slate-400">{{ $report->approved_at?->format('d M Y, h:i A') }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Operations Director</span>
        </div>
    </div>
</body>
</html>
