@extends('layouts.admin')

@section('title', 'System Audit Trail - SolarOps')
@section('header_title', 'Activity & Audit Log')

@section('admin_content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Immutable Field Operations Audit Trail</h2>
            <p class="text-xs text-slate-500">Every service creation, assignment, report draft save, submission, and approval event is permanently preserved.</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Search Details</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Description, user, action..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Event Type</label>
                <select name="event" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Events</option>
                    <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="assigned" {{ request('event') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="started" {{ request('event') === 'started' ? 'selected' : '' }}>Started</option>
                    <option value="draft_saved" {{ request('event') === 'draft_saved' ? 'selected' : '' }}>Draft Saved</option>
                    <option value="submitted" {{ request('event') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="correction_requested" {{ request('event') === 'correction_requested' ? 'selected' : '' }}>Correction Requested</option>
                    <option value="resubmitted" {{ request('event') === 'resubmitted' ? 'selected' : '' }}>Resubmitted</option>
                    <option value="approved" {{ request('event') === 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-colors w-full">Filter</button>
                <a href="{{ route('admin.audit-logs') }}" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Event</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Entity</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-mono">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800 border">
                                    {{ str_replace('_', ' ', $log->event) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $log->user?->name ?? 'System' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $log->description }}
                            </td>
                            <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
