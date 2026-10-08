@extends('layouts.admin')

@section('title', 'Import Details #' . $import->id . ' - SolarOps')
@section('header_title', 'Import Job Details')

@section('admin_content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.data-management.history') }}" class="text-xs text-slate-500 hover:text-slate-800 font-bold">&larr; Back to Import History</a>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Import Job #{{ $import->id }}: {{ ucfirst(str_replace('_', ' ', $import->import_type)) }}</h2>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @if($import->import_type === 'employees')
                <a href="{{ route('admin.data-management.download-credential-sheet', $import->id) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl shadow-2xs transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download Credential Sheet</span>
                </a>
            @endif

            @if(!empty($import->error_log) && count($import->error_log) > 0)
                <a href="{{ route('admin.data-management.download-error-report', $import->id) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-xs font-bold rounded-xl transition-all shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download Error Report</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Job Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">File Name</span>
                <span class="font-mono font-bold text-slate-900 mt-0.5 block truncate">{{ $import->file_name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Imported By</span>
                <span class="font-bold text-slate-900 mt-0.5 block">{{ $import->user?->name ?? 'Admin' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Execution Time</span>
                <span class="font-bold text-slate-900 mt-0.5 block">{{ $import->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Job Status</span>
                <span class="mt-0.5 inline-block">
                    @if($import->status === 'completed')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">Completed</span>
                    @elseif($import->status === 'partially_completed')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">Partial</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">Failed</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rows</span>
                <div class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($import->total_rows) }}</div>
            </div>
            <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Imported</span>
                <div class="text-xl font-black text-emerald-600 mt-0.5">{{ number_format($import->imported_count) }}</div>
            </div>
            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Skipped (Duplicates)</span>
                <div class="text-xl font-black text-slate-600 mt-0.5">{{ number_format($import->skipped_count) }}</div>
            </div>
            <div class="bg-rose-50/70 p-3 rounded-xl border border-rose-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Failed (Errors)</span>
                <div class="text-xl font-black text-rose-600 mt-0.5">{{ number_format($import->failed_count) }}</div>
            </div>
        </div>
    </div>

    <!-- Error Log Table if errors exist -->
    @if(!empty($import->error_log) && count($import->error_log) > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-rose-800">
                    Failed Rows & Error Log ({{ count($import->error_log) }})
                </h3>
            </div>
            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Row #</th>
                            <th class="py-3 px-4">Error Details</th>
                            <th class="py-3 px-4">Payload Snapshot</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($import->error_log as $err)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-500">
                                    {{ $err['row_index'] ?? '—' }}
                                </td>
                                <td class="py-3 px-4 text-rose-700 font-semibold max-w-md">
                                    @if(is_array($err['errors'] ?? null))
                                        {{ implode('; ', $err['errors']) }}
                                    @else
                                        {{ $err['error'] ?? 'Validation failure' }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500 max-w-sm truncate">
                                    {{ json_encode($err['data'] ?? []) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
