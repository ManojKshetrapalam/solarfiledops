@extends('layouts.admin')

@section('title', 'Review Structure Installation #' . $report->report_number . ' - SolarOps')
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
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-blue-200">
                        Installation: Structure & Mounting
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                    @if($report->service)
                        <span class="text-xs text-slate-500 font-medium">Job: #{{ $report->service->service_number }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $report->customer?->name ?? 'Installation Project' }} &bull; {{ $report->site?->name ?? 'Plant Site' }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Structure Technician: <strong class="text-slate-800">{{ $report->engineer->name }}</strong>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.reports.print', $report->id) }}" target="_blank"
                   class="px-3.5 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Printable Sheet</span>
                </a>

                @if(!$report->isApproved())
                    <button type="button" @click="showCorrectionModal = true"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Request Correction</span>
                    </button>

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this structure installation report? This marks the task completed.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Report</span>
                        </button>
                    </form>

                    <button type="button" @click="showRejectModal = true"
                            class="px-3 py-2 text-rose-600 hover:bg-rose-50 font-bold text-xs rounded-xl transition-all">
                        Reject
                    </button>
                @else
                    <span class="px-3 py-2 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Approved by {{ $report->reviewer?->name ?? 'Admin' }} ({{ $report->approved_at?->format('d M Y') }})</span>
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

    <!-- Section Data Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Site & Plant Info -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Site & Plant Information</span>
                <span class="text-amber-600 font-mono text-[11px]">{{ $sections['site_plant_info']['service_date'] ?? '—' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Plant Capacity</span> <strong class="text-slate-800">{{ $sections['site_plant_info']['plant_capacity'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Modules Model</span> <strong class="text-slate-800">{{ $sections['site_plant_info']['module_make_model'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Technician</span> {{ $sections['site_plant_info']['technician_name'] ?? $report->engineer->name }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Site Address</span> {{ $sections['site_plant_info']['customer_address'] ?? '—' }}</div>
            </div>
        </div>

        <!-- 2. Panels & Materials Delivered -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                2. Material & Panels Delivered
            </h3>
            <div class="grid grid-cols-3 gap-2 text-xs mb-3">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">No. of Panels</span>
                    <strong class="text-slate-900 text-sm">{{ $sections['panels_delivered']['no_of_panels'] ?? '—' }}</strong>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Wattage</span>
                    <strong class="text-slate-900 text-sm">{{ $sections['panels_delivered']['panel_wattage'] ?? '—' }}</strong>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Delivered Total</span>
                    <strong class="text-slate-900 text-sm">{{ $sections['panels_delivered']['total_delivered_kwp'] ?? '—' }}</strong>
                </div>
            </div>
            <div class="text-xs space-y-1">
                <div><span class="text-slate-400 text-[10px] uppercase font-semibold">Condition:</span> <span class="font-medium text-slate-800">{{ $sections['panels_delivered']['delivery_condition'] ?? 'Good' }}</span></div>
                <div><span class="text-slate-400 text-[10px] uppercase font-semibold">Remarks:</span> <span class="text-slate-700">{{ $sections['panels_delivered']['delivery_remarks'] ?? 'None' }}</span></div>
            </div>
        </div>

        <!-- 3. Panels Mounting & Washing -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                3. Panels Installation Work & Washing
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">No. of Rows</span> <strong class="text-slate-800">{{ $sections['mounting_work']['no_of_rows'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">No. of Strings</span> <strong class="text-slate-800">{{ $sections['mounting_work']['no_of_strings'] ?? '—' }}</strong></div>
                <div class="col-span-2"><span class="text-slate-400 block text-[10px] uppercase font-semibold">Maintenance Space Available</span> <p class="text-slate-800 bg-slate-50 p-2 rounded border border-slate-200">{{ $sections['mounting_work']['maintenance_space'] ?? '—' }}</p></div>
            </div>
            <div class="p-3 bg-blue-50/70 rounded-lg border border-blue-200 text-xs space-y-1">
                <span class="font-bold text-blue-900 block text-[10px] uppercase">Washing Arrangements</span>
                <div>Storage: <strong class="text-slate-800">{{ $sections['mounting_work']['washing_water_storage'] ?? '—' }}</strong></div>
                <div>Plumbing: <strong class="text-slate-800">{{ $sections['mounting_work']['washing_plumbing'] ?? '—' }}</strong></div>
                <div>Motors: <strong class="text-slate-800">{{ $sections['mounting_work']['washing_motors'] ?? '—' }}</strong></div>
            </div>
        </div>

        <!-- 4. Structure Work & Wind Safety -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                4. Structure Work & Climatic Safety
            </h3>
            <div class="grid grid-cols-3 gap-2 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Materials</span> <strong class="text-slate-800">{{ $sections['structure_work']['materials'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Quality</span> <strong class="text-slate-800">{{ $sections['structure_work']['quality'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Quantity</span> <strong class="text-slate-800">{{ $sections['structure_work']['quantity'] ?? '—' }}</strong></div>
                <div class="col-span-3"><span class="text-slate-400 block text-[10px] uppercase font-semibold">Fixing Method</span> {{ $sections['structure_work']['fixing'] ?? '—' }}</div>
            </div>
            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs">
                <span class="font-bold text-amber-950 block text-[10px] uppercase mb-1">Wind & Climatic Safety Measures</span>
                <p class="text-slate-800">{{ $sections['structure_work']['wind_safety_detailed'] ?? '—' }}</p>
            </div>
        </div>

        <!-- 5. Checklist & Sign-off -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100">
                5. Completed Works & Work Acceptance Confirmation
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="font-bold text-emerald-800 block uppercase text-[10px] mb-2">Completed Structure Works</span>
                    <ul class="space-y-1 list-disc list-inside text-slate-700">
                        @for($i=1; $i<=5; $i++)
                            @if(!empty($sections['safety_checklist']['completed_' . $i]))
                                <li>{{ $sections['safety_checklist']['completed_' . $i] }}</li>
                            @endif
                        @endfor
                    </ul>

                    @if(!empty($sections['safety_checklist']['pending_1']))
                        <span class="font-bold text-rose-800 block uppercase text-[10px] mt-3 mb-1">Pending Items</span>
                        <ul class="space-y-1 list-disc list-inside text-slate-700">
                            @for($i=1; $i<=3; $i++)
                                @if(!empty($sections['safety_checklist']['pending_' . $i]))
                                    <li>{{ $sections['safety_checklist']['pending_' . $i] }}</li>
                                @endif
                            @endfor
                        </ul>
                    @endif
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-900 block uppercase text-[10px]">Work Acceptance Confirmation</span>
                    <div>Checked By: <strong class="text-slate-900">{{ $sections['safety_checklist']['checked_by_name'] ?? '—' }}</strong> ({{ $sections['safety_checklist']['checked_by_phone'] ?? '—' }})</div>
                    
                    @if(!empty($sections['safety_checklist']['client_confirmed']))
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Client Inspected & Satisfied
                        </span>
                    @endif

                    @if(!empty($sections['safety_checklist']['client_signature']))
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Client Digital Signature</span>
                            <div class="bg-white p-2 rounded-lg border border-slate-200 inline-block">
                                <img src="{{ $sections['safety_checklist']['client_signature'] }}" alt="Client Signature" class="max-h-16 object-contain">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Photographs Gallery -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Structure & Module Mounting Photographs ({{ $report->photos->count() }})</span>
            <span class="text-xs text-slate-400 font-normal">Job: #{{ $report->service?->service_number ?? '—' }}</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @forelse($report->photos as $photo)
                <div class="group bg-slate-50 rounded-xl border border-slate-200 overflow-hidden shadow-xs hover:border-amber-400 transition-all">
                    <a href="{{ $photo->url }}" target="_blank" class="block relative">
                        <img src="{{ $photo->url }}" alt="{{ $photo->original_filename }}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-200">
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
                <div class="col-span-full py-6 text-center text-slate-400 text-xs">
                    No inspection photographs uploaded.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modals for Correction and Reject -->
    <div x-show="showCorrectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Request Structure Corrections</h3>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="correction_notes" rows="4" required placeholder="Specify what needs correction on structure work..."
                          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCorrectionModal = false" class="px-4 py-2 border rounded-xl text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 font-bold text-xs rounded-xl text-slate-950">Dispatch Request</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-rose-700">Reject Report</h3>
            <form action="{{ route('admin.reports.reject', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="rejection_reason" rows="3" required placeholder="Reason for rejection..."
                          class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-rose-500"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 border rounded-xl text-xs">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 font-bold text-xs rounded-xl text-white">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
