<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Structure Installation Report - {{ $report->report_number }}</title>
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
                    INSTALLATION: STRUCTURE & MODULES
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
                    <span class="text-slate-500 font-semibold">Modules Make/Model:</span>
                    <span class="text-slate-900 font-semibold">{{ $sections['site_plant_info']['module_make_model'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Technician:</span>
                    <span class="text-slate-900">{{ $sections['site_plant_info']['technician_name'] ?? $report->engineer->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- STRUCTURE INSPECTION DETAILS -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Structure & Module Mounting Specifications
        </div>

        <div class="p-3 space-y-3">
            <!-- 1. Panels Material Receipt -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    1. Solar Modules & Material Receipt
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Total Received:</span> <strong>{{ $sections['panels_delivery']['panels_received'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Damaged Count:</span> <strong>{{ $sections['panels_delivery']['panels_damaged'] ?? '0' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Safe Storage:</span> <strong>{{ !empty($sections['panels_delivery']['safe_storage']) ? 'Yes' : 'No' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Wattage Each:</span> <strong>{{ $sections['panels_delivery']['wattage_each'] ?? '—' }} W</strong></div>
                </div>
                @if(!empty($sections['panels_delivery']['storage_remarks']))
                    <p class="text-[11px] mt-1 text-slate-600"><strong class="text-slate-700">Storage Remarks:</strong> {{ $sections['panels_delivery']['storage_remarks'] }}</p>
                @endif
            </div>

            <!-- 2. Structure Installation Details -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    2. Structure Assembly & Alignment
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Structure Material:</span> <strong>{{ $sections['structure_installation']['structure_material'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Fixing Type:</span> <strong>{{ $sections['structure_installation']['fixing_type'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Tilt Angle:</span> <strong>{{ $sections['structure_installation']['tilt_angle'] ?? '—' }}&deg;</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">South True Orientation:</span> <strong>{{ !empty($sections['structure_installation']['true_south']) ? 'Verified' : 'No' }}</strong></div>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-2 pt-1 border-t border-slate-100">
                    <div><span class="text-slate-500 block text-[10px]">Civil Work / Anchoring:</span> {{ $sections['structure_installation']['civil_work'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Coating / Anti-Rust:</span> {{ $sections['structure_installation']['rust_protection'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Fasteners / Hardware:</span> {{ $sections['structure_installation']['hardware_type'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 3. Module Mounting & Wind Safety -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    3. Module Mounting & Wind Safety
                </h3>
                <div class="grid grid-cols-4 gap-2">
                    <div><span class="text-slate-500 block text-[10px]">Total Installed:</span> <strong>{{ $sections['module_mounting']['modules_installed'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Clamp Types:</span> <strong>{{ $sections['module_mounting']['clamp_type'] ?? '—' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Torque Checked:</span> <strong>{{ !empty($sections['module_mounting']['torque_checked']) ? 'Yes (Verified)' : 'No' }}</strong></div>
                    <div><span class="text-slate-500 block text-[10px]">Module Alignment:</span> <strong>{{ $sections['module_mounting']['module_alignment'] ?? '—' }}</strong></div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2 pt-1 border-t border-slate-100">
                    <div><span class="text-slate-500 block text-[10px]">Wind Bracing / Safety:</span> {{ $sections['module_mounting']['wind_speed_safety'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[10px]">Array Washing Pathway:</span> {{ $sections['module_mounting']['washing_arrangement'] ?? '—' }}</div>
                </div>
            </div>

            <!-- 4. Quality Checklist & Milestones -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    4. Milestones Completed & Quality Verification
                </h3>
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <ul class="list-disc list-inside space-y-0.5 text-slate-800">
                        @for($i=1; $i<=5; $i++)
                            @if(!empty($sections['safety_checklist']['completed_' . $i]))
                                <li>{{ $sections['safety_checklist']['completed_' . $i] }}</li>
                            @endif
                        @endfor
                    </ul>
                    @if(!empty($sections['safety_checklist']['pending_1']))
                        <div class="bg-slate-50 p-2 rounded border border-slate-200">
                            <strong class="text-rose-800 text-[10px] uppercase block">Pending Items:</strong>
                            <ul class="list-disc list-inside space-y-0.5 text-rose-900 mt-0.5">
                                @for($p=1; $p<=3; $p++)
                                    @if(!empty($sections['safety_checklist']['pending_' . $p]))
                                        <li>{{ $sections['safety_checklist']['pending_' . $p] }}</li>
                                    @endif
                                @endfor
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Evidences in Print -->
    @if($report->photos->count() > 0)
        <div class="border border-slate-900 mb-4 page-break-inside-avoid">
            <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
                Structure Inspection Photographic Evidence
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
            <span class="font-bold text-[10px] uppercase text-slate-500">Structure Work Done By</span>
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
                @if(!empty($sections['safety_checklist']['client_signature']))
                    <img src="{{ $sections['safety_checklist']['client_signature'] }}" alt="Client Signature" class="max-h-9 object-contain mx-auto mb-0.5">
                @endif
                <p class="font-bold text-xs text-slate-900 leading-tight">{{ $sections['safety_checklist']['checked_by_name'] ?? '—' }}</p>
                <p class="text-[9px] text-slate-500">Phone: {{ $sections['safety_checklist']['checked_by_phone'] ?? '—' }}</p>
                @if(!empty($sections['safety_checklist']['client_confirmed']))
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
