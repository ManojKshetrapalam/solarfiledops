@extends('layouts.admin')

@section('title', 'Reports & Verification - SolarOps')
@section('header_title', 'Field Service Reports')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Submitted Field Service Reports</h2>
            <p class="text-xs text-slate-500">Verify digital inspection reports, inspect photographs, approve completed work, or request field corrections.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Report #, customer, engineer..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Pending Review)</option>
                    <option value="resubmitted" {{ request('status') === 'resubmitted' ? 'selected' : '' }}>Resubmitted (Corrected)</option>
                    <option value="correction_required" {{ request('status') === 'correction_required' ? 'selected' : '' }}>Correction Required</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved & Locked</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (In Field)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Company / Entity</label>
                <select name="company_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Companies</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-colors w-full">Filter</button>
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Report Number</th>
                        <th class="py-3.5 px-4">Customer & Site</th>
                        <th class="py-3.5 px-4">Entity</th>
                        <th class="py-3.5 px-4">Engineer</th>
                        <th class="py-3.5 px-4">Submission Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($reports as $rep)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.reports.show', $rep->id) }}" class="font-bold text-slate-900 hover:text-amber-600 text-sm block">
                                    {{ $rep->report_number }}
                                </a>
                                <span class="text-slate-400 text-[11px]">Job: {{ $rep->service->service_number }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-bold text-slate-800">{{ $rep->customer->name }}</p>
                                <p class="text-slate-500 text-[11px]">{{ $rep->site->name }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $rep->company->name }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800">{{ $rep->engineer->name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $rep->engineer->employee_code }}</span>
                            </td>
                            <td class="py-3 px-4">
                                {{ $rep->submitted_at ? $rep->submitted_at->format('d M Y, h:i A') : 'Not submitted' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $rep->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $rep->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.reports.show', $rep->id) }}" 
                                       class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg transition-colors text-xs">
                                        Review
                                    </a>
                                    @if($rep->isApproved())
                                        <a href="{{ route('admin.reports.print', $rep->id) }}" target="_blank"
                                           class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors text-xs">
                                            Print
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No reports found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
