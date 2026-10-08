<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commissioning Report - {{ $report->report_number }}</title>
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
                    INSTALLATION: PCU & COMMISSIONING
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
                    <span class="text-slate-500 font-semibold">Commissioning Date:</span>
                    <strong class="text-slate-900">{{ $sections['site_plant_info']['service_date'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Plant Capacity:</span>
                    <strong class="text-slate-900">{{ $sections['site_plant_info']['plant_capacity'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Engineer Name:</span>
                    <span class="text-slate-900 font-semibold">{{ $sections['site_plant_info']['technician_name'] ?? $report->engineer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">System Type:</span>
                    <span class="text-slate-900">{{ $sections['pcu_installation']['system_type'] ?? 'On-Grid' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COMMISSIONING SPECIFICATIONS & CHECKS -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Inverter Performance & Commissioning Test Matrix
        </div>

        <div class="p-3 space-y-3">
            <!-- 1. PCU Inverter Operating Parameters -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    1. Power Conditioning Unit (PCU / Inverter)
                </h3>
                <div class="grid grid-cols-4 gap-2 mb-2 text-[11px]">
                    <div><span class="text-slate-500 block text-[9px] uppercase">Make & Model</span> <strong>{{ $sections['pcu_installation']['pcu_make_model'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Serial Number</span> <strong class="font-mono">{{ $sections['pcu_installation']['pcu_serial_no'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Voltage Rating</span> {{ $sections['pcu_installation']['volts_rating'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Mounting</span> {{ $sections['pcu_installation']['mounting_details'] ?? '—' }}</div>
                </div>

                <div class="grid grid-cols-4 gap-2 text-center bg-slate-50 p-2 rounded border border-slate-200">
                    <div><span class="text-slate-500 block text-[9px] uppercase">Solar DC Volts</span> <strong>{{ $sections['pcu_installation']['solar_dc_volts'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Solar DC Amps</span> <strong>{{ $sections['pcu_installation']['solar_dc_current'] ?? '—' }} A</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Grid AC Volts</span> <strong>{{ $sections['pcu_installation']['grid_ac_volts'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[9px] uppercase">Frequency</span> <strong>{{ $sections['pcu_installation']['output_freq'] ?? '—' }} Hz</strong></div>
                </div>

                <div class="flex gap-4 mt-1.5 text-[10px] text-slate-600">
                    <span>Ventilation: <strong>{{ !empty($sections['pcu_installation']['ventilation_ok']) ? 'OK' : 'No' }}</strong></span>
                    <span>Rain Protection: <strong>{{ !empty($sections['pcu_installation']['rain_protection_ok']) ? 'OK' : 'No' }}</strong></span>
                    <span>Temperature: <strong>{{ !empty($sections['pcu_installation']['temp_ok']) ? 'OK' : 'No' }}</strong></span>
                </div>
            </div>

            <!-- 2. Battery Bank Installation -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    2. Battery Bank Installation & Stands
                </h3>
                <div class="grid grid-cols-6 gap-2 text-[10px]">
                    <div><span class="text-slate-500 block text-[9px]">Battery Type:</span> {{ $sections['battery_installation']['battery_type'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Capacity:</span> <strong>{{ $sections['battery_installation']['ah_rating'] ?? '—' }} Ah</strong></div>
                    <div><span class="text-slate-500 block text-[9px]">Voltage:</span> <strong>{{ $sections['battery_installation']['volts_rating'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[9px]">No. of Units:</span> <strong>{{ $sections['battery_installation']['no_of_batteries'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[9px]">Stand Type:</span> {{ $sections['battery_installation']['battery_stand'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Cabins:</span> {{ $sections['battery_installation']['battery_cabins'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 3. 9-Point Commissioning Checklist -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    3. 9-Point Commissioning Verification Matrix
                </h3>
                <div class="grid grid-cols-3 gap-2 text-[10px]">
                    @php
                        $commChecks = [
                            1 => 'Cables connected & tagged',
                            2 => 'Lugs crimped properly',
                            3 => 'Polarity verified',
                            4 => 'Terminals torqued (no loose contacts)',
                            5 => 'Earthing spring washers used',
                            6 => 'Earth resistance verified (< 5 &Omega;)',
                            7 => 'Inverter boots zero alarms',
                            8 => 'Grid load transfer smooth',
                            9 => 'Isolators tested operational',
                        ];
                    @endphp
                    @for($i=1; $i<=9; $i++)
                        <div class="flex items-center justify-between p-1.5 rounded bg-slate-50 border border-slate-200">
                            <span class="text-slate-700 truncate pr-1">{{ $i }}. {!! $commChecks[$i] !!}</span>
                            <span class="font-bold uppercase text-[9px] {{ ($sections['commissioning_testing']['check_' . $i] ?? '') === 'Yes' || ($sections['commissioning_testing']['check_' . $i] ?? '') === 'OK' ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $sections['commissioning_testing']['check_' . $i] ?? '—' }}
                            </span>
                        </div>
                    @endfor
                </div>

                @if(!empty($sections['commissioning_testing']['pending_1']))
                    <div class="mt-2 p-2 bg-slate-50 rounded border border-slate-200 text-[10px]">
                        <span class="font-bold text-rose-800 uppercase block">Pending Items:</span>
                        <ul class="list-disc list-inside mt-0.5 space-y-0.5 text-slate-700">
                            @for($p=1; $p<=3; $p++)
                                @if(!empty($sections['commissioning_testing']['pending_' . $p]))
                                    <li>{{ $sections['commissioning_testing']['pending_' . $p] }}</li>
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
                Commissioning Photographic Evidence
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
            <span class="font-bold text-[10px] uppercase text-slate-500">Commissioned By</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->engineer->name }}</p>
                <p class="text-[10px] text-slate-500">{{ $report->engineer->designation }}</p>
                <p class="text-[9px] font-mono text-slate-400">ID: {{ $report->engineer->employee_code }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Engineer Signature & Date</span>
        </div>

        <!-- Checked By -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Checked By (Client)</span>
            <div class="my-auto text-center">
                @if(!empty($sections['handover_signoff']['client_signature']))
                    <img src="{{ $sections['handover_signoff']['client_signature'] }}" alt="Client Signature" class="max-h-9 object-contain mx-auto mb-0.5">
                @endif
                <p class="font-bold text-xs text-slate-900 leading-tight">{{ $sections['handover_signoff']['whom_shown_name'] ?? '—' }}</p>
                <p class="text-[9px] text-slate-500">Phone: {{ $sections['handover_signoff']['whom_shown_phone'] ?? '—' }}</p>
                @if(!empty($sections['handover_signoff']['client_confirmed']))
                    <p class="text-[8px] text-emerald-700 font-semibold">(Commissioning Verified & Accepted)</p>
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
