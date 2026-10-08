@extends('layouts.admin')

@section('title', 'Review Daily Timesheet #' . $report->report_number . ' - SolarOps')
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
                    <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full border border-emerald-200">
                        Daily Work Timesheet
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $report->status)) }}
                    </span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 mt-2">
                    {{ $report->engineer->name }} &bull; {{ $sections['shift_details']['report_date'] ?? $report->created_at->format('d M Y') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Corporate Entity: <strong class="text-slate-800">{{ $report->company->name }} ({{ $report->company->code }})</strong> &bull;
                    Designation: <strong class="text-slate-800">{{ $sections['shift_details']['designation'] ?? $report->engineer->designation }}</strong>
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

                    <form action="{{ route('admin.reports.approve', $report->id) }}" method="POST" onsubmit="return confirm('Approve this daily work timesheet & conveyance claim?')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Approve Timesheet</span>
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
                        <span>Approved by {{ $report->reviewer?->name ?? 'Manager' }} ({{ $report->approved_at?->format('d M Y') }})</span>
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
        <!-- 1. Shift & Hours Overview -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Shift Timings & Location</span>
                <span class="text-emerald-700 font-mono text-[11px]">{{ $sections['shift_details']['report_date'] ?? '—' }}</span>
            </h3>
            <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Employee Name</span> <strong class="text-slate-900">{{ $sections['shift_details']['employee_name'] ?? $report->engineer->name }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Designation</span> {{ $sections['shift_details']['designation'] ?? 'Field Engineer' }}</div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Shift In / Start</span> <strong class="text-slate-900 font-mono">{{ $sections['shift_details']['work_started_time'] ?? '—' }}</strong></div>
                <div><span class="text-slate-400 block text-[10px] uppercase font-semibold">Shift Out / Stop</span> <strong class="text-slate-900 font-mono">{{ $sections['shift_details']['work_stopped_time'] ?? '—' }}</strong></div>
            </div>
            <div class="text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Place / Plant Visited</span>
                <p class="text-slate-800 mt-0.5">{{ $sections['shift_details']['place_of_work'] ?? '—' }}</p>
            </div>
        </div>

        <!-- 2. Allowances & Travel Claim -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. Conveyance & Meal Claims</span>
                <span class="text-xs font-semibold text-slate-500">{{ $sections['travel_conveyance']['vehicle_used'] ?? 'Bike' }}</span>
            </h3>

            <!-- Travel metrics -->
            <div class="grid grid-cols-4 gap-2 text-center bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs mb-3">
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Start KM</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['travel_conveyance']['starting_km'] ?? '0' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">End KM</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['travel_conveyance']['ending_km'] ?? '0' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Total Run</span>
                    <strong class="text-emerald-700 font-mono text-sm">{{ $sections['travel_conveyance']['diff_km'] ?? '0' }} km</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Conveyance</span>
                    <strong class="text-slate-900 font-mono">₹{{ $sections['travel_conveyance']['amount'] ?? '0' }}</strong>
                </div>
            </div>

            <!-- Meals Breakdown -->
            <div class="text-xs space-y-1.5 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <span class="font-bold text-[10px] uppercase text-slate-500 block mb-1">Meals Allowance Breakdown</span>
                <div class="flex justify-between py-0.5 border-b border-slate-100">
                    <span class="text-slate-600">Morning Tiffin / Breakfast:</span>
                    <strong class="text-slate-900 font-mono">
                        {{ !empty($sections['meals_allowance']['tiffin_yes']) ? '₹' . ($sections['meals_allowance']['tiffin_amount'] ?? '0') : 'No' }}
                    </strong>
                </div>
                <div class="flex justify-between py-0.5 border-b border-slate-100">
                    <span class="text-slate-600">Lunch Allowance:</span>
                    <strong class="text-slate-900 font-mono">
                        {{ !empty($sections['meals_allowance']['lunch_yes']) ? '₹' . ($sections['meals_allowance']['lunch_amount'] ?? '0') : 'No' }}
                    </strong>
                </div>
                <div class="flex justify-between py-0.5">
                    <span class="text-slate-600">Dinner Allowance:</span>
                    <strong class="text-slate-900 font-mono">
                        {{ !empty($sections['meals_allowance']['dinner_yes']) ? '₹' . ($sections['meals_allowance']['dinner_amount'] ?? '0') : 'No' }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- 3. Hourly Activity Log (Full Span) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>3. Hourly Field Activity Log</span>
                <span class="text-xs font-semibold text-slate-500">6:30 AM to 9:30 PM Timesheet</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 text-[10px] uppercase font-bold bg-slate-50">
                            <th class="py-2 px-3 w-44">Time Slot</th>
                            <th class="py-2 px-3">Activity & Tasks Performed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $timeSlots = [
                                'slot_630_900' => '06:30 AM - 09:00 AM (Travel / Early Start)',
                                'slot_900_1000' => '09:00 AM - 10:00 AM',
                                'slot_1000_1100' => '10:00 AM - 11:00 AM',
                                'slot_1100_1200' => '11:00 AM - 12:00 PM',
                                'slot_1200_1300' => '12:00 PM - 01:00 PM',
                                'slot_1300_1400' => '01:00 PM - 02:00 PM (Lunch Break / Work)',
                                'slot_1400_1500' => '02:00 PM - 03:00 PM',
                                'slot_1500_1600' => '03:00 PM - 04:00 PM',
                                'slot_1600_1700' => '04:00 PM - 05:00 PM',
                                'slot_1700_1830' => '05:00 PM - 06:30 PM',
                                'slot_1830_1930' => '06:30 PM - 07:30 PM (Return Travel / Handover)',
                                'slot_1930_2030' => '07:30 PM - 08:30 PM',
                                'slot_2030_2130' => '08:30 PM - 09:30 PM (Late Shift)',
                            ];
                        @endphp
                        @foreach($timeSlots as $slotKey => $slotLabel)
                            @if(!empty($sections['hourly_activity_log'][$slotKey]))
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 font-semibold text-slate-700 whitespace-nowrap">{{ $slotLabel }}</td>
                                    <td class="py-2 px-3 text-slate-800">{{ $sections['hourly_activity_log'][$slotKey] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Work Summary & Employee Sign-Off (Full Span) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 lg:col-span-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>4. Day Summary & Employee Affirmation</span>
                <span class="text-xs font-semibold text-slate-500">Sign-Off</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs mb-4">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Work Allocated Today</span>
                    <p class="text-slate-800 mt-1 font-medium">{{ $sections['work_summary_signoff']['work_allocated'] ?? '—' }}</p>
                </div>
                <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-200">
                    <span class="text-emerald-800 block text-[10px] uppercase font-bold">Work Completed Today</span>
                    <p class="text-slate-800 mt-1 font-medium">{{ $sections['work_summary_signoff']['work_completed'] ?? '—' }}</p>
                </div>
                <div class="bg-amber-50/50 p-3 rounded-xl border border-amber-200">
                    <span class="text-amber-800 block text-[10px] uppercase font-bold">Pending / Carried Forward Work</span>
                    <p class="text-slate-800 mt-1 font-medium">{{ $sections['work_summary_signoff']['pending_work'] ?? 'None' }}</p>
                </div>
            </div>

            @if(!empty($sections['work_summary_signoff']['employee_signature']))
                <div class="pt-3 border-t border-slate-100 flex items-center gap-4">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold mb-1">Employee Signature</span>
                        <div class="bg-white p-2 rounded-lg border border-slate-200 inline-block">
                            <img src="{{ $sections['work_summary_signoff']['employee_signature'] }}" alt="Employee Signature" class="max-h-16 object-contain">
                        </div>
                    </div>
                    <div class="text-xs text-slate-500">
                        Signed by <strong class="text-slate-900">{{ $report->engineer->name }}</strong> on {{ $sections['work_summary_signoff']['signed_at'] ? \Carbon\Carbon::parse($sections['work_summary_signoff']['signed_at'])->timezone('Asia/Kolkata')->format('d M Y, h:i A') : 'Submission' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Photographs / Receipts Gallery -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
            <span>Odometer Photos & Expense Receipts ({{ $report->photos->count() }})</span>
            <span class="text-xs text-slate-400 font-normal">Report: #{{ $report->report_number }}</span>
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
                    No odometer or expense receipt photographs uploaded.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modals for Correction and Reject -->
    <div x-show="showCorrectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-900">Request Timesheet Corrections</h3>
            <form action="{{ route('admin.reports.request-correction', $report->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="correction_notes" rows="4" required placeholder="Specify what entries or kilometer claims need correction..."
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
            <h3 class="text-base font-bold text-rose-700">Reject Timesheet</h3>
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
