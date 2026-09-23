@extends('layouts.admin')

@section('title', 'Review Report #' . $report->report_number . ' - SolarOps')
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
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Job: #{{ $report->service->service_number }}</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $report->customer->name }} &bull; {{ $report->site->name }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Engineer: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.reports.print', $report->id) }}" target="_blank"
                   class="px-3.5 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printable Report</span>
                </a>

                @if(!$report->isApproved())
                    <!-- Request Correction Button -->
                    <button type="button" @click="showCorrectionModal = true"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Request Correction</span>
                    </button>

                    <!-- Approve Button -->
                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this service report? This will mark the service as completed and lock the report permanently.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Report</span>
                        </button>
                    </form>

                    <!-- Reject Button -->
                    <button type="button" @click="showRejectModal = true"
                            class="px-3 py-2 text-rose-600 hover:bg-rose-50 font-bold text-xs rounded-xl transition-all">
                        Reject
                    </button>
                @else
                    <span class="px-3 py-2 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Report Approved by {{ $report->reviewer?->name ?? 'Admin' }} ({{ $report->approved_at?->format('d M Y') }})</span>
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

    <!-- 10 Section Data Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Customer & Service Details -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Customer & Service Details</span>
                <span class="text-amber-600 font-mono text-[11px]">{{ $sections['customer_details']['service_date'] ?? '—' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Customer</span> <strong class="text-slate-800">{{ $report->customer->name }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Service Time</span> <strong class="text-slate-800">{{ $sections['customer_details']['service_time'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Head Phone</span> {{ $sections['customer_details']['phone_head'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Site Incharge Phone</span> {{ $sections['customer_details']['phone_incharge'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Maintenance Phone</span> {{ $sections['customer_details']['phone_maintenance'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Others Phone</span> {{ $sections['customer_details']['phone_others'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 2. System Details -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                2. System Details
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Capacity Installed</span> <strong class="text-indigo-700 text-sm">{{ $sections['system_details']['system_capacity'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Date of Installation</span> {{ $sections['system_details']['date_of_installation'] ?? '—' }}</div>
                <div class="col-span-2"><span class="text-slate-400 block text-[10px] uppercase font-semibold">Plant Configuration</span> {{ strtoupper(str_replace('_', ' ', $sections['system_details']['plant_type'] ?? '—')) }}</div>
            </div>
        </div>

        <!-- 3. Solar Module Inspection -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                3. Solar Module Inspection & Testing
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Module Condition</span> <strong class="text-slate-800">{{ strtoupper(str_replace('_', ' ', $sections['module_inspection']['condition'] ?? '—')) }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Operating Current</span> <span class="font-mono font-bold text-slate-800">{{ $sections['module_inspection']['meter_amps'] ?? '—' }} ({{ $sections['module_inspection']['meter_amps_time'] ?? '' }})</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Operating Voltage</span> <span class="font-mono font-bold text-slate-800">{{ $sections['module_inspection']['meter_volts'] ?? '—' }} ({{ $sections['module_inspection']['meter_volts_time'] ?? '' }})</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Remarks</span> {{ $sections['module_inspection']['remarks'] ?? 'No remarks' }}</div>
            </div>
        </div>

        <!-- 4. Structure Inspection -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                4. Structure Inspection
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Condition</span> <strong class="text-slate-800">{{ strtoupper(str_replace('_', ' ', $sections['structure_inspection']['condition'] ?? '—')) }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Materials Used</span> {{ $sections['structure_inspection']['materials_used'] ?? '—' }}</div>
                <div class="col-span-2"><span class="text-slate-400 block text-[10px] uppercase font-semibold">Remarks</span> {{ $sections['structure_inspection']['remarks'] ?? 'No remarks' }}</div>
            </div>
        </div>

        <!-- 5. PCU / Inverter -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                5. Power Conditioning Unit (PCU / Inverter)
            </h3>
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Capacity</span> {{ $sections['pcu_inspection']['capacity'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Phase</span> {{ $sections['pcu_inspection']['phase'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Condition</span> {{ $sections['pcu_inspection']['condition'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Phase I Voltage</span> <span class="font-mono font-bold">{{ $sections['pcu_inspection']['voltage_phase_1'] ?? '—' }} V</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Phase II Voltage</span> <span class="font-mono font-bold">{{ $sections['pcu_inspection']['voltage_phase_2'] ?? '—' }} V</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Phase III Voltage</span> <span class="font-mono font-bold">{{ $sections['pcu_inspection']['voltage_phase_3'] ?? '—' }} V</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Current</span> {{ $sections['pcu_inspection']['current'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Array String</span> {{ $sections['pcu_inspection']['array_voltage'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Solar Readings</span> {{ $sections['pcu_inspection']['solar_readings'] ?? '—' }}</div>
            </div>
            @if(!empty($sections['pcu_inspection']['remarks']))
                <p class="text-slate-500 bg-slate-50 p-2.5 rounded-lg text-xs mt-3">Remark: {{ $sections['pcu_inspection']['remarks'] }}</p>
            @endif
        </div>

        <!-- 6. Battery Inspection -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                6. Battery Bank Inspection
            </h3>
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Capacity</span> {{ $sections['battery_inspection']['battery_capacity'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">No of Batteries</span> {{ $sections['battery_inspection']['number_of_batteries'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Voltage</span> <span class="font-mono font-bold">{{ $sections['battery_inspection']['battery_voltage'] ?? '—' }} V</span></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Water Before</span> {{ $sections['battery_inspection']['distilled_water_before'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Water After</span> {{ $sections['battery_inspection']['distilled_water_after'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Connectors</span> {{ $sections['battery_inspection']['battery_connectors'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 7. Complaint & Rectification -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                7. Complaint Details & Rectification
            </h3>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-0.5">Details of Complaint</span>
                    <p class="text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $sections['complaint_details']['complaint_details'] ?? 'No complaint recorded (Routine preventive service).' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-0.5">Rectified Report Detailed</span>
                    <p class="text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $sections['complaint_details']['rectified_report_detailed'] ?? 'All components verified according to preventive checklist.' }}</p>
                </div>
            </div>
        </div>

        <!-- 8. Remarks & Sign-off -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                8. General Remarks & Verification Sign-Off
            </h3>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-0.5">General Remarks</span>
                    <p class="text-slate-800 bg-slate-50 p-2.5 rounded-lg border border-slate-200">{{ $sections['remarks']['general_remarks'] ?? 'None' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Service Done By</span>
                        <strong class="text-slate-900">{{ $report->engineer->name }}</strong>
                        <span class="text-slate-400 block text-[11px]">{{ $report->engineer->employee_code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Checked By (Client)</span>
                        <strong class="text-slate-900">{{ $sections['remarks']['checked_by_name'] ?? 'Pending' }}</strong>
                        <span class="text-slate-400 block text-[11px]">{{ $sections['remarks']['checked_by_phone'] ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photographs Gallery (Section 9) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Inspection & Cleaning Photographs ({{ $report->photos->count() }})</span>
            <span class="text-xs text-slate-400 font-normal">Associated with Company: {{ $report->company->name }} &bull; Job: {{ $report->service->service_number }}</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @forelse($report->photos as $photo)
                <div class="group bg-slate-50 rounded-xl border border-slate-200 overflow-hidden shadow-xs hover:border-amber-400 transition-all">
                    <a href="{{ $photo->url }}" target="_blank" class="block relative">
                        <img src="{{ $photo->url }}" alt="{{ $photo->original_filename }}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-200">
                        <div class="absolute inset-0 bg-slate-950/20 group-hover:bg-transparent transition-colors"></div>
                    </a>
                    <div class="p-2.5 text-xs">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-800 inline-block mb-1">
                            {{ str_replace('_', ' ', $photo->section_key) }}
                        </span>
                        <p class="font-medium text-slate-700 truncate text-[11px]">{{ $photo->original_filename }}</p>
                        <p class="text-[10px] text-slate-400">{{ $photo->captured_at ? $photo->captured_at->format('d M Y, h:i A') : '' }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-xs text-slate-400 border border-dashed border-slate-200 rounded-xl">
                    No inspection photographs were uploaded for this report.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Audit History -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-3">Audit Trail & Verification History</h3>
        <div class="space-y-3 text-xs">
            @forelse($auditLogs as $log)
                <div class="flex items-start gap-3 border-l-2 border-slate-200 pl-4 py-1">
                    <div class="w-2 h-2 rounded-full bg-amber-500 -ml-[21px] mt-1 ring-4 ring-white"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800">{{ $log->description }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            By {{ $log->user?->name ?? 'System' }} &bull; {{ $log->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">No activity logged.</p>
            @endforelse
        </div>
    </div>

    <!-- Request Correction Modal -->
    <div x-show="showCorrectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900">Request Field Correction</h3>
                <p class="text-xs text-slate-500">Specify what details, readings, or photographs the engineer must correct and resubmit.</p>
            </div>

            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Correction Instructions / Reason *</label>
                    <textarea name="correction_notes" rows="4" required placeholder="e.g. Please upload the battery cleaning photograph and enter the battery voltage."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showCorrectionModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs">
                        Dispatch Correction Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-rose-700">Reject Service Report</h3>
                <p class="text-xs text-slate-500">Provide an administrative reason for rejecting this report.</p>
            </div>

            <form action="{{ route('admin.reports.reject', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rejection Reason *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Reason for report rejection..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs">
                        Reject Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
