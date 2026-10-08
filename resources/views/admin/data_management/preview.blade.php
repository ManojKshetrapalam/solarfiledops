@extends('layouts.admin')

@section('title', 'Validate & Preview Import - SolarOps')
@section('header_title', 'Import Validation & Preview')

@section('admin_content')
<div class="space-y-6" x-data="{
    filterStatus: 'all',
    confirmChecked: false,
    rows: {{ Js::from($previewData['rows']) }},
    get filteredRows() {
        if (this.filterStatus === 'all') return this.rows;
        return this.rows.filter(r => r.status === this.filterStatus);
    }
}">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-900 text-amber-400">
                    {{ $previewData['config']['title'] ?? ($previewData['config']['name'] ?? ($previewData['type_name'] ?? 'Data Import')) }}
                </span>
                <span class="text-xs text-slate-400 font-medium">Pre-Import Validation</span>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Review Validation Results Before Importing</h2>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.data-management.index') }}" 
               class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-colors">
                Cancel & Re-upload
            </a>
            @if($previewData['error_count'] > 0)
                <a href="{{ route('admin.data-management.download-error-report', ['preview_key' => $previewKey]) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-xs font-bold rounded-xl transition-all shadow-2xs">
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download Error Report (.XLSX)</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
            <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Total Rows</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($previewData['total_rows']) }}</div>
            <span class="text-[10px] text-slate-400">In uploaded file</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-2xs bg-emerald-50/20">
            <span class="text-[11px] font-bold uppercase text-emerald-700 tracking-wider">Ready to Import</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($previewData['valid_count']) }}</div>
            <span class="text-[10px] text-emerald-600 font-medium">Valid records</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-2xs bg-amber-50/20">
            <span class="text-[11px] font-bold uppercase text-amber-700 tracking-wider">Warnings</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($previewData['warning_count']) }}</div>
            <span class="text-[10px] text-amber-600 font-medium">Needs attention</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs bg-slate-50/50">
            <span class="text-[11px] font-bold uppercase text-slate-500 tracking-wider">Duplicates</span>
            <div class="text-2xl font-black text-slate-700 mt-1">{{ number_format($previewData['duplicate_count']) }}</div>
            <span class="text-[10px] text-slate-500">Will be skipped</span>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-2xs bg-rose-50/20 col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase text-rose-700 tracking-wider">Errors</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($previewData['error_count']) }}</div>
            <span class="text-[10px] text-rose-600 font-medium">Invalid rows</span>
        </div>
    </div>

    @if($previewData['import_type'] === 'employees')
        <!-- Special Employee Onboarding Notice -->
        <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-300 rounded-2xl p-4.5 text-xs text-amber-950">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-amber-500 text-slate-950 rounded-xl font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-amber-950">Automated Employee Credential Generation</h4>
                    <p class="text-amber-900 mt-0.5 leading-relaxed">
                        For each imported employee, SolarOps has automatically pre-computed a clean lowercase username (e.g. <code>manoj</code>) and a temporary onboarding password (e.g. <code>manoj123</code>). After you confirm import, an Employee Credential Handover screen and downloadable Excel sheet will be provided immediately.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Filter Pills -->
    <div class="flex items-center justify-between gap-3 pt-2">
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" @click="filterStatus = 'all'" 
                    :class="filterStatus === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                All ({{ $previewData['total_rows'] }})
            </button>
            <button type="button" @click="filterStatus = 'valid'" 
                    :class="filterStatus === 'valid' ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                Ready ({{ $previewData['valid_count'] }})
            </button>
            <button type="button" @click="filterStatus = 'warning'" 
                    :class="filterStatus === 'warning' ? 'bg-amber-600 text-white' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                Warnings ({{ $previewData['warning_count'] }})
            </button>
            <button type="button" @click="filterStatus = 'duplicate'" 
                    :class="filterStatus === 'duplicate' ? 'bg-slate-600 text-white' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                Duplicates ({{ $previewData['duplicate_count'] }})
            </button>
            <button type="button" @click="filterStatus = 'error'" 
                    :class="filterStatus === 'error' ? 'bg-rose-600 text-white' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                Errors ({{ $previewData['error_count'] }})
            </button>
        </div>
        <span class="text-xs text-slate-400 font-medium hidden sm:inline" x-text="`Showing ${filteredRows.length} rows`"></span>
    </div>

    <!-- Preview Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto max-h-[500px]">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="sticky top-0 bg-slate-100/95 backdrop-blur-xs z-10 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Row #</th>
                        <th class="py-3 px-4">Validation Status</th>
                        <th class="py-3 px-4">Issues / Notes</th>
                        @if($previewData['import_type'] === 'employees')
                            <th class="py-3 px-4 text-indigo-700">Generated User ID</th>
                            <th class="py-3 px-4 text-indigo-700">Temp Password</th>
                        @endif
                        @foreach(array_slice($previewData['headers'], 0, 5) as $header)
                            <th class="py-3 px-4">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <template x-for="row in filteredRows" :key="row.row_index">
                        <tr :class="{
                            'bg-emerald-50/30': row.status === 'valid',
                            'bg-amber-50/40': row.status === 'warning',
                            'bg-slate-50/60': row.status === 'duplicate',
                            'bg-rose-50/40': row.status === 'error'
                        }" class="hover:bg-slate-100/50 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-500" x-text="row.row_index"></td>
                            <td class="py-3 px-4">
                                <template x-if="row.status === 'valid'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Ready
                                    </span>
                                </template>
                                <template x-if="row.status === 'warning'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                        Warning
                                    </span>
                                </template>
                                <template x-if="row.status === 'duplicate'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-300">
                                        Duplicate
                                    </span>
                                </template>
                                <template x-if="row.status === 'error'">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                        Error
                                    </span>
                                </template>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <template x-if="row.errors && row.errors.length > 0">
                                    <div class="text-rose-700 font-semibold text-[11px]" x-text="row.errors.join('; ')"></div>
                                </template>
                                <template x-if="row.warnings && row.warnings.length > 0">
                                    <div class="text-amber-700 font-medium text-[11px]" x-text="row.warnings.join('; ')"></div>
                                </template>
                                <template x-if="(!row.errors || row.errors.length === 0) && (!row.warnings || row.warnings.length === 0)">
                                    <span class="text-slate-400 text-[11px]">—</span>
                                </template>
                            </td>
                            @if($previewData['import_type'] === 'employees')
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded" x-text="row.username || '—'"></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono text-slate-600 bg-slate-100 px-2 py-0.5 rounded" x-text="row.temp_password ? '••••••••' : '—'"></span>
                                </td>
                            @endif
                            @foreach(array_slice($previewData['headers'], 0, 5) as $header)
                                <td class="py-3 px-4 max-w-[200px] truncate" x-text="row.data['{{ $header }}'] || '—'"></td>
                            @endforeach
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Final Confirmation Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
        <form method="POST" action="{{ route('admin.data-management.confirm') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="preview_key" value="{{ $previewKey }}">

            <div class="flex items-start gap-3">
                <input type="checkbox" 
                       id="confirm_checkbox" 
                       x-model="confirmChecked" 
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                <label for="confirm_checkbox" class="text-xs text-slate-700 cursor-pointer leading-relaxed">
                    <strong>I confirm that I have verified the data preview.</strong> I authorize SolarOps to import 
                    <span class="text-emerald-700 font-extrabold">{{ $previewData['valid_count'] }}</span> 
                    valid records into the database. 
                    @if($previewData['error_count'] > 0 || $previewData['duplicate_count'] > 0)
                        <span>({{ $previewData['error_count'] + $previewData['duplicate_count'] }} invalid/duplicate rows will be safely bypassed).</span>
                    @endif
                </label>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100">
                <span class="text-xs text-slate-500">
                    Operation is atomic and transactional. Any fatal error will automatically roll back cleanly.
                </span>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.data-management.index') }}" 
                       class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            :disabled="!confirmChecked || {{ $previewData['valid_count'] }} === 0" 
                            :class="{'opacity-50 cursor-not-allowed': !confirmChecked || {{ $previewData['valid_count'] }} === 0}"
                            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs rounded-xl shadow-xs transition-all">
                        Confirm & Execute Import ({{ $previewData['valid_count'] }} Records)
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
