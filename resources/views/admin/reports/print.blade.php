<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Report - {{ $report->report_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 text-xs p-6 max-w-4xl mx-auto font-sans leading-tight">
    <!-- Print toolbar -->
    <div class="no-print mb-6 p-4 bg-slate-900 text-white rounded-xl flex items-center justify-between shadow-lg">
        <div>
            <h2 class="font-bold text-sm">Official Service Report: {{ $report->report_number }}</h2>
            <p class="text-xs text-slate-400">Click Print to print or save as PDF via your browser.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg text-xs shadow-md">
                Print / Save PDF
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-slate-800 text-slate-300 rounded-lg text-xs hover:text-white">
                Close
            </button>
        </div>
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
                    SERVICE REPORT
                </span>
                <p class="font-mono font-bold text-xs mt-1">NO: {{ $report->report_number }}</p>
                <p class="text-[10px] text-slate-500">Job: #{{ $report->service->service_number }}</p>
            </div>
        </div>

        <!-- Customer & Service Header -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50">
                <strong class="block uppercase font-bold text-[10px] text-slate-500 mb-1">Customer Name & Address</strong>
                <p class="font-bold text-sm text-slate-900">{{ $report->customer->name }}</p>
                <p class="text-slate-700 mt-0.5">{{ $report->site->name }}</p>
                <p class="text-slate-600 text-[11px]">{{ $report->site->address }}</p>
            </div>

            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50 space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Service Date & Time:</span>
                    <strong class="text-slate-900">{{ $sections['customer_details']['service_date'] ?? '—' }} {{ $sections['customer_details']['service_time'] ?? '' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">System Capacity Installed:</span>
                    <strong class="text-slate-900">{{ $sections['system_details']['system_capacity'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Date of Installation:</span>
                    <span class="text-slate-900">{{ $sections['system_details']['date_of_installation'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Contact Phones:</span>
                    <span class="text-slate-900">{{ $sections['customer_details']['phone_head'] ?? '' }} / {{ $sections['customer_details']['phone_incharge'] ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TEST CONDUCTED -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Test Conducted & Inspection Details
        </div>

        <div class="p-3 space-y-3">
            <!-- Solar Module Inspection -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    1. Solar Module Inspection
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Condition:</span> <strong>{{ strtoupper(str_replace('_', ' ', $sections['module_inspection']['condition'] ?? '—')) }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Current (Amps):</span> <strong>{{ $sections['module_inspection']['meter_amps'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Voltage (Volts):</span> <strong>{{ $sections['module_inspection']['meter_volts'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Remarks:</span> <span>{{ $sections['module_inspection']['remarks'] ?? '—' }}</span></div>
                </div>
            </div>

            <!-- Structure Inspection -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    2. Structure Inspection
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Condition (Rigidity):</span> <strong>{{ strtoupper(str_replace('_', ' ', $sections['structure_inspection']['condition'] ?? '—')) }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Materials Used:</span> <strong>{{ $sections['structure_inspection']['materials_used'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Condition (Coating/Fasteners):</span> <strong>{{ strtoupper(str_replace('_', ' ', $sections['structure_inspection']['coating_condition'] ?? '—')) }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Remarks:</span> <span>{{ $sections['structure_inspection']['remarks'] ?? '—' }}</span></div>
                </div>
            </div>

            <!-- Power Conditioning Unit -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    3. Power Conditioning Unit (PCU)
                </h3>
                <div class="grid grid-cols-6 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Capacity:</span> <strong>{{ $sections['pcu_inspection']['capacity'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Phase:</span> <span>{{ $sections['pcu_inspection']['phase'] ?? '—' }}</span></div>
                    <div><span class="text-slate-500 block text-[10px]">Voltage Phase I:</span> <strong>{{ $sections['pcu_inspection']['voltage_phase_1'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Voltage Phase II:</span> <strong>{{ $sections['pcu_inspection']['voltage_phase_2'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Voltage Phase III:</span> <strong>{{ $sections['pcu_inspection']['voltage_phase_3'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Current:</span> <strong>{{ $sections['pcu_inspection']['current'] ?? '—' }}</strong></div>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-2 pt-1 border-t border-slate-100">
                    <div><span class="text-slate-500 block text-[10px]">Condition:</span> {{ $sections['pcu_inspection']['condition'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Solar Readings:</span> {{ $sections['pcu_inspection']['solar_readings'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Array / Battery V:</span> {{ $sections['pcu_inspection']['array_voltage'] ?? '—' }} / {{ $sections['pcu_inspection']['battery_voltage'] ?? '—' }}</div>
                </div>
            </div>

            <!-- Battery Inspection -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    4. Battery Inspection
                </h3>
                <div class="grid grid-cols-6 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Capacity:</span> <strong>{{ $sections['battery_inspection']['battery_capacity'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">No of Batteries:</span> <strong>{{ $sections['battery_inspection']['number_of_batteries'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Voltage:</span> <strong>{{ $sections['battery_inspection']['battery_voltage'] ?? '—' }} V</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Water Before:</span> {{ $sections['battery_inspection']['distilled_water_before'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Water After:</span> {{ $sections['battery_inspection']['distilled_water_after'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Connectors:</span> {{ $sections['battery_inspection']['battery_connectors'] ?? '—' }}</div>
                </div>
            </div>

            <!-- Complaint Details -->
            @if(!empty($sections['complaint_details']['complaint_details']) || !empty($sections['complaint_details']['rectified_report_detailed']))
                <div>
                    <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                        5. Complaint & Rectification Report
                    </h3>
                    <p class="text-[11px]"><strong class="text-slate-600">Complaint:</strong> {{ $sections['complaint_details']['complaint_details'] }}</p>
                    <p class="text-[11px] mt-0.5"><strong class="text-slate-600">Rectified Report:</strong> {{ $sections['complaint_details']['rectified_report_detailed'] }}</p>
                </div>
            @endif

            <!-- General Remarks -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    6. General Remarks
                </h3>
                <p class="text-[11px]">{{ $sections['remarks']['general_remarks'] ?? 'Routine inspection completed successfully.' }}</p>
            </div>
        </div>
    </div>

    <!-- Photo Evidences in Print -->
    @if($report->photos->count() > 0)
        <div class="border border-slate-900 mb-4 page-break-inside-avoid">
            <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
                Inspection Photographic Evidence
            </div>
            <div class="p-3 grid grid-cols-4 gap-2">
                @foreach($report->photos->take(4) as $photo)
                    <div class="border border-slate-200 p-1 rounded text-center">
                        <img src="{{ $photo->url }}" alt="Evidence" class="w-full h-24 object-cover rounded mb-1">
                        <span class="font-bold text-[9px] uppercase block">{{ str_replace('_', ' ', $photo->section_key) }}</span>
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
            <span class="font-bold text-[10px] uppercase text-slate-500">Service Done By</span>
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
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $sections['remarks']['checked_by_name'] ?? '—' }}</p>
                <p class="text-[10px] text-slate-500">Phone: {{ $sections['remarks']['checked_by_phone'] ?? '—' }}</p>
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
