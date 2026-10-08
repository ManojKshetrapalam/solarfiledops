<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Work Timesheet - {{ $report->report_number }}</title>
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
                    DAILY WORK TIMESHEET & CONVEYANCE
                </span>
                <p class="font-mono font-bold text-xs mt-1">NO: {{ $report->report_number }}</p>
            </div>
        </div>

        <!-- Employee Shift Header -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50">
                <strong class="block uppercase font-bold text-[10px] text-slate-500 mb-1">Employee Details</strong>
                <p class="font-bold text-sm text-slate-900">{{ $sections['shift_details']['employee_name'] ?? $report->engineer->name }}</p>
                <p class="text-slate-700 mt-0.5">{{ $sections['shift_details']['designation'] ?? $report->engineer->designation }}</p>
                <p class="text-slate-500 text-[10px] font-mono">Employee Code: {{ $report->engineer->employee_code }}</p>
            </div>

            <div class="border border-slate-300 p-2.5 rounded bg-slate-50/50 space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Report Date:</span>
                    <strong class="text-slate-900">{{ $sections['shift_details']['report_date'] ?? $report->created_at->format('d M Y') }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Shift Hours:</span>
                    <strong class="text-slate-900 font-mono">{{ $sections['shift_details']['work_started_time'] ?? '09:00' }} to {{ $sections['shift_details']['work_stopped_time'] ?? '18:00' }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-semibold">Place(s) Visited:</span>
                    <span class="text-slate-900 font-medium truncate max-w-[200px]">{{ $sections['shift_details']['place_of_work'] ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- DAILY WORK ACTIVITIES & EXPENSES -->
    <div class="border border-slate-900 mb-4">
        <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
            Daily Activities Log & Travel Expense Voucher
        </div>

        <div class="p-3 space-y-3">
            <!-- 1. Hourly Log Table -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    1. Hourly Work Activity Log
                </h3>
                <table class="w-full text-left text-[10px] border border-slate-200">
                    <thead class="bg-slate-100 font-bold uppercase text-[9px] text-slate-600">
                        <tr class="border-b border-slate-200">
                            <th class="p-1.5 w-44">Time Slot</th>
                            <th class="p-1.5">Tasks & Field Accomplishments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $timeSlots = [
                                'slot_630_900' => '06:30 AM - 09:00 AM (Early/Travel)',
                                'slot_900_1000' => '09:00 AM - 10:00 AM',
                                'slot_1000_1100' => '10:00 AM - 11:00 AM',
                                'slot_1100_1200' => '11:00 AM - 12:00 PM',
                                'slot_1200_1300' => '12:00 PM - 01:00 PM',
                                'slot_1300_1400' => '01:00 PM - 02:00 PM (Lunch Break)',
                                'slot_1400_1500' => '02:00 PM - 03:00 PM',
                                'slot_1500_1600' => '03:00 PM - 04:00 PM',
                                'slot_1600_1700' => '04:00 PM - 05:00 PM',
                                'slot_1700_1830' => '05:00 PM - 06:30 PM',
                                'slot_1830_1930' => '06:30 PM - 07:30 PM (Handover/Travel)',
                                'slot_1930_2030' => '07:30 PM - 08:30 PM',
                                'slot_2030_2130' => '08:30 PM - 09:30 PM (Late Work)',
                            ];
                        @endphp
                        @foreach($timeSlots as $slotKey => $slotLabel)
                            @if(!empty($sections['hourly_activity_log'][$slotKey]))
                                <tr>
                                    <td class="p-1.5 font-semibold text-slate-700 whitespace-nowrap">{{ $slotLabel }}</td>
                                    <td class="p-1.5 text-slate-900">{{ $sections['hourly_activity_log'][$slotKey] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 2. Conveyance & Meals Claims -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    2. Conveyance & Meal Expenses
                </h3>
                <div class="grid grid-cols-2 gap-4 text-[10px]">
                    <div class="border border-slate-200 p-2 rounded bg-slate-50/50">
                        <span class="font-bold uppercase text-[9px] text-slate-500 block mb-1">Travel Conveyance ({{ $sections['travel_conveyance']['vehicle_used'] ?? 'Bike' }})</span>
                        <div class="grid grid-cols-4 gap-1 text-center font-mono">
                            <div><span class="text-slate-500 text-[8px] block">Start KM</span>{{ $sections['travel_conveyance']['starting_km'] ?? '0' }}</div>
                            <div><span class="text-slate-500 text-[8px] block">End KM</span>{{ $sections['travel_conveyance']['ending_km'] ?? '0' }}</div>
                            <div><span class="text-slate-500 text-[8px] block">Total KM</span><strong>{{ $sections['travel_conveyance']['diff_km'] ?? '0' }}</strong></div>
                            <div><span class="text-slate-500 text-[8px] block">Amount</span><strong class="text-slate-900">₹{{ $sections['travel_conveyance']['amount'] ?? '0' }}</strong></div>
                        </div>
                    </div>

                    <div class="border border-slate-200 p-2 rounded bg-slate-50/50 space-y-1">
                        <span class="font-bold uppercase text-[9px] text-slate-500 block">Food & Refreshments</span>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Tiffin / Breakfast:</span>
                            <span class="font-mono">{{ !empty($sections['meals_allowance']['tiffin_yes']) ? '₹' . ($sections['meals_allowance']['tiffin_amount'] ?? '0') : '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Lunch Allowance:</span>
                            <span class="font-mono">{{ !empty($sections['meals_allowance']['lunch_yes']) ? '₹' . ($sections['meals_allowance']['lunch_amount'] ?? '0') : '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Dinner Allowance:</span>
                            <span class="font-mono">{{ !empty($sections['meals_allowance']['dinner_yes']) ? '₹' . ($sections['meals_allowance']['dinner_amount'] ?? '0') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Work Summary -->
            <div>
                <h3 class="font-bold text-[11px] uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1 mb-1.5">
                    3. Day Work Summary
                </h3>
                <div class="grid grid-cols-3 gap-2 text-[10px]">
                    <div><span class="text-slate-500 block text-[9px]">Allocated:</span> {{ $sections['work_summary_signoff']['work_allocated'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Completed:</span> {{ $sections['work_summary_signoff']['work_completed'] ?? '—' }}</div>
                    <div><span class="text-slate-500 block text-[9px]">Pending:</span> {{ $sections['work_summary_signoff']['pending_work'] ?? 'None' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Evidences in Print -->
    @if($report->photos->count() > 0)
        <div class="border border-slate-900 mb-4 page-break-inside-avoid">
            <div class="bg-slate-900 text-white px-3 py-1 font-bold text-xs uppercase tracking-wider">
                Odometer Reading & Expense Receipts
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
    <div class="border-2 border-slate-900 grid grid-cols-2 divide-x-2 divide-slate-900 text-center">
        <!-- Employee Signature -->
        <div class="p-3 flex flex-col justify-between h-28">
            <span class="font-bold text-[10px] uppercase text-slate-500">Employee Signature & Submission</span>
            <div class="my-auto text-center">
                @if(!empty($sections['work_summary_signoff']['employee_signature']))
                    <img src="{{ $sections['work_summary_signoff']['employee_signature'] }}" alt="Employee Signature" class="max-h-9 object-contain mx-auto mb-0.5">
                @endif
                <p class="font-bold text-xs text-slate-900 leading-tight">{{ $report->engineer->name }}</p>
                <p class="text-[9px] text-slate-500">{{ $sections['shift_details']['report_date'] ?? $report->created_at->format('d M Y') }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Field Engineer Affirmation</span>
        </div>

        <!-- Manager Approval -->
        <div class="p-3 flex flex-col justify-between h-28 bg-slate-50/60">
            <span class="font-bold text-[10px] uppercase text-slate-500">For Office Use / Timesheet Approved By</span>
            <div class="my-auto">
                <p class="font-bold text-sm text-slate-900">{{ $report->reviewer?->name ?? 'Service Operations Manager' }}</p>
                <p class="text-[10px] text-emerald-700 font-semibold">{{ $report->isApproved() ? 'STATUS: APPROVED' : 'STATUS: ' . strtoupper($report->status) }}</p>
                <p class="text-[9px] text-slate-400">{{ $report->approved_at?->format('d M Y, h:i A') }}</p>
            </div>
            <span class="text-[9px] text-slate-400 border-t pt-0.5">Authorized Signatory</span>
        </div>
    </div>
</body>
</html>
