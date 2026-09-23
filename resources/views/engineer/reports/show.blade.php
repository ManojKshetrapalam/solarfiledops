@extends('layouts.engineer')

@section('mobile_title', 'Report #' . $report->report_number)

@section('engineer_content')
<div class="space-y-4 pb-12">
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
            {{ $report->customer->name }}
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ $report->site->name }} &bull; {{ $report->company->name }}
        </p>

        @if($report->status === 'approved')
            <div class="mt-4 p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold block">Approved & Permanently Locked</span>
                    <span class="text-[11px] text-emerald-700">Verified by {{ $report->reviewer?->name ?? 'Admin' }} on {{ $report->approved_at?->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        @elseif($report->status === 'correction_required')
            <div class="mt-4 p-3.5 bg-amber-500 text-slate-950 rounded-xl border border-amber-600 text-xs">
                <span class="font-bold block">Correction Requested:</span>
                <p class="mt-1 bg-amber-400 p-2 rounded-lg font-medium text-slate-950">"{{ $report->correction_notes }}"</p>
                <div class="mt-3">
                    <a href="{{ route('engineer.reports.edit', $report->id) }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-950 text-white rounded-lg font-bold text-xs shadow-xs">
                        <span>Edit & Resubmit Report</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @else
            <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-200 text-blue-900 text-xs">
                <span class="font-bold block">Submitted for Admin Verification</span>
                <span class="text-[11px] text-blue-700">Submitted on {{ $report->submitted_at?->format('d M Y, h:i A') }}</span>
            </div>
        @endif
    </div>

    <!-- 10 Section Breakdown View -->
    <div class="space-y-3 text-xs">
        <!-- 1. Customer Details -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">1. Customer & Service Details</h3>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div><span class="text-slate-400 block text-[10px]">Service Date</span> {{ $sections['customer_details']['service_date'] ?? '—' }} {{ $sections['customer_details']['service_time'] ?? '' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Head Phone</span> {{ $sections['customer_details']['phone_head'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Site Phone</span> {{ $sections['customer_details']['phone_incharge'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Maintenance Phone</span> {{ $sections['customer_details']['phone_maintenance'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 2. System Details -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">2. System Details</h3>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div><span class="text-slate-400 block text-[10px]">Capacity Installed</span> {{ $sections['system_details']['system_capacity'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Installation Date</span> {{ $sections['system_details']['date_of_installation'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Configuration</span> {{ $sections['system_details']['plant_type'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 3. Solar Module Inspection -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">3. Solar Module Inspection</h3>
            <div class="grid grid-cols-2 gap-2 text-slate-700 mb-2">
                <div><span class="text-slate-400 block text-[10px]">Condition</span> {{ ucfirst($sections['module_inspection']['condition'] ?? '—') }}</div>
                <div><span class="text-slate-400 block text-[10px]">Current / Voltage</span> {{ $sections['module_inspection']['meter_amps'] ?? '—' }} / {{ $sections['module_inspection']['meter_volts'] ?? '—' }}</div>
            </div>
            @if(!empty($sections['module_inspection']['remarks']))
                <p class="text-slate-500 bg-slate-50 p-2 rounded text-[11px]">Remark: {{ $sections['module_inspection']['remarks'] }}</p>
            @endif
        </div>

        <!-- 4. Structure Inspection -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">4. Structure Inspection</h3>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div><span class="text-slate-400 block text-[10px]">Condition</span> {{ $sections['structure_inspection']['condition'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Materials Used</span> {{ $sections['structure_inspection']['materials_used'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 5. PCU / Inverter -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">5. Power Conditioning Unit</h3>
            <div class="grid grid-cols-3 gap-2 text-slate-700">
                <div><span class="text-slate-400 block text-[10px]">Capacity</span> {{ $sections['pcu_inspection']['capacity'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Phase I</span> {{ $sections['pcu_inspection']['voltage_phase_1'] ?? '—' }} V</div>
                <div><span class="text-slate-400 block text-[10px]">Phase II</span> {{ $sections['pcu_inspection']['voltage_phase_2'] ?? '—' }} V</div>
                <div><span class="text-slate-400 block text-[10px]">Phase III</span> {{ $sections['pcu_inspection']['voltage_phase_3'] ?? '—' }} V</div>
                <div><span class="text-slate-400 block text-[10px]">Current</span> {{ $sections['pcu_inspection']['current'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Condition</span> {{ $sections['pcu_inspection']['condition'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 6. Battery Bank -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">6. Battery Bank</h3>
            <div class="grid grid-cols-3 gap-2 text-slate-700">
                <div><span class="text-slate-400 block text-[10px]">Capacity</span> {{ $sections['battery_inspection']['battery_capacity'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Voltage</span> {{ $sections['battery_inspection']['battery_voltage'] ?? '—' }} V</div>
                <div><span class="text-slate-400 block text-[10px]">Count</span> {{ $sections['battery_inspection']['number_of_batteries'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Water Before</span> {{ $sections['battery_inspection']['distilled_water_before'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Water After</span> {{ $sections['battery_inspection']['distilled_water_after'] ?? '—' }}</div>
                <div><span class="text-slate-400 block text-[10px]">Connectors</span> {{ $sections['battery_inspection']['battery_connectors'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 7. Photographs Gallery -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">Photographs ({{ $report->photos->count() }})</h3>
            <div class="grid grid-cols-2 gap-2">
                @forelse($report->photos as $p)
                    <div class="rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                        <img src="{{ $p->url }}" alt="{{ $p->caption }}" class="w-full h-28 object-cover">
                        <div class="p-1.5 text-[10px]">
                            <span class="font-bold text-slate-800 block truncate">{{ strtoupper(str_replace('_', ' ', $p->section_key)) }}</span>
                            <span class="text-slate-400">{{ $p->captured_at ? $p->captured_at->format('d M, h:i A') : '' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 col-span-2 text-center py-4">No photos attached.</p>
                @endforelse
            </div>
        </div>

        <!-- 8. Signatures & Sign-off -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs">
            <h3 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-2 border-b pb-1">Sign-Off & Verifications</h3>
            <div class="grid grid-cols-2 gap-2 text-slate-700">
                <div>
                    <span class="text-slate-400 block text-[10px]">Service Done By</span>
                    <strong class="text-slate-900">{{ $report->engineer->name }}</strong>
                    <span class="block text-slate-400 text-[10px]">{{ $report->engineer->designation }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Checked By</span>
                    <strong class="text-slate-900">{{ $sections['remarks']['checked_by_name'] ?? 'Pending' }}</strong>
                    <span class="block text-slate-400 text-[10px]">{{ $sections['remarks']['checked_by_phone'] ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
