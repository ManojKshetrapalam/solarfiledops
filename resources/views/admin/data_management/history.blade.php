@extends('layouts.admin')

@section('title', 'Import History - SolarOps')
@section('header_title', 'Data Import History')

@section('admin_content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Audit Trail of Data Imports</h2>
            <p class="text-xs text-slate-500 mt-0.5">Historical log of all migration jobs, volume metrics, and generated reports.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.data-management.index') }}" 
               class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-2xs transition-all">
                New Data Import
            </a>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-5">Date & Time</th>
                        <th class="py-3.5 px-5">Entity / Type</th>
                        <th class="py-3.5 px-5">File Name</th>
                        <th class="py-3.5 px-5">Imported By</th>
                        <th class="py-3.5 px-5">Processed / Total</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($imports as $imp)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-5">
                                <span class="font-bold text-slate-900 block">{{ $imp->created_at->format('d M Y') }}</span>
                                <span class="text-slate-400 text-[11px]">{{ $imp->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ ucfirst(str_replace('_', ' ', $imp->import_type)) }}
                                </span>
                            </td>
                            <td class="py-4 px-5 font-mono text-slate-600">
                                {{ $imp->file_name }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-semibold text-slate-800">{{ $imp->user?->name ?? 'Admin' }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="space-y-0.5">
                                    <div class="font-bold text-slate-900">
                                        <span class="text-emerald-600">{{ $imp->imported_count }}</span> / {{ $imp->total_rows }} rows
                                    </div>
                                    @if($imp->failed_count > 0 || $imp->skipped_count > 0)
                                        <div class="text-[11px] text-slate-400">
                                            @if($imp->skipped_count > 0) {{ $imp->skipped_count }} skipped @endif
                                            @if($imp->failed_count > 0) <span class="text-rose-600 font-semibold">{{ $imp->failed_count }} failed</span> @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                @if($imp->status === 'completed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">Completed</span>
                                @elseif($imp->status === 'partially_completed')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">Partial</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">Failed</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.data-management.history.show', $imp->id) }}" 
                                       class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-colors">
                                        Details
                                    </a>

                                    @if($imp->import_type === 'employees')
                                        <a href="{{ route('admin.data-management.download-credential-sheet', $imp->id) }}" 
                                           class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold rounded-lg text-xs transition-colors"
                                           title="Download Onboarding Credentials Sheet">
                                            Credentials
                                        </a>
                                    @endif

                                    @if(!empty($imp->error_log) && count($imp->error_log) > 0)
                                        <a href="{{ route('admin.data-management.download-error-report', $imp->id) }}" 
                                           class="px-2.5 py-1 bg-rose-100 hover:bg-rose-200 text-rose-800 font-bold rounded-lg text-xs transition-colors"
                                           title="Download Error Report">
                                            Errors
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                No imports have been executed yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($imports->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $imports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
