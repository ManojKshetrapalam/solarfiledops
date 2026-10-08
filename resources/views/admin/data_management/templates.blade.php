@extends('layouts.admin')

@section('title', 'Download Migration Templates - SolarOps')
@section('header_title', 'Migration Templates')

@section('admin_content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Official Excel Migration Templates</h2>
            <p class="text-xs text-slate-500 mt-0.5">Standardized 2-sheet spreadsheets formatted with exact column headers and guidance notes.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.data-management.index') }}" 
               class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-colors">
                Back to Migration Center
            </a>
            <a href="{{ route('admin.data-management.download-all-templates') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold rounded-xl shadow-xs transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download All (.ZIP Bundle)</span>
            </a>
        </div>
    </div>

    <!-- Template Guidelines Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-xs text-blue-900">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-1.5">
                <h4 class="font-extrabold text-blue-950">Important Excel Formatting Rules:</h4>
                <ul class="list-disc pl-4 space-y-1 text-blue-800">
                    <li><strong>Sheet Structure:</strong> Each template contains two tabs: <code>Data</code> (where you enter rows) and <code>Instructions</code> (field specifications).</li>
                    <li><strong>Column Headers:</strong> Do not rename, reorder, or delete the header row in row 1.</li>
                    <li><strong>Entity Codes:</strong> Always use exact natural codes (e.g., Company Code <code>ENG-CORP</code>, Customer Code <code>CUST-001</code>) so SolarOps can automatically resolve database relations.</li>
                    <li><strong>Employee Logins:</strong> Employee import automatically creates user accounts, generating usernames and temporary passwords.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Templates List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-5">Order / Step</th>
                        <th class="py-3.5 px-5">Entity & Purpose</th>
                        <th class="py-3.5 px-5">File Name</th>
                        <th class="py-3.5 px-5">Columns / Headers</th>
                        <th class="py-3.5 px-5">Prerequisites</th>
                        <th class="py-3.5 px-5 text-right">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($configs as $type => $cfg)
                        @php
                            $step = match($type) {
                                'companies' => 'Step 1',
                                'employees' => 'Step 2',
                                'customers' => 'Step 3',
                                'sites' => 'Step 4',
                                default => 'Step 5',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 px-5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider {{ $step === 'Step 1' || $step === 'Step 2' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $step }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <h4 class="font-extrabold text-slate-900 text-sm">{{ $cfg['title'] }}</h4>
                                <p class="text-slate-500 text-[11px] mt-0.5 max-w-xs">{{ $cfg['description'] }}</p>
                            </td>
                            <td class="py-4 px-5">
                                <span class="font-mono text-slate-600 bg-slate-100 px-2 py-1 rounded text-[11px]">
                                    {{ $cfg['file_name'] }}
                                </span>
                            </td>
                            <td class="py-4 px-5 max-w-xs">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($cfg['headers'], 0, 4) as $h)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-medium">{{ $h }}</span>
                                    @endforeach
                                    @if(count($cfg['headers']) > 4)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-400 text-[10px] font-medium">+{{ count($cfg['headers']) - 4 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                @if(!empty($cfg['requires']))
                                    <span class="text-indigo-600 font-semibold text-[11px]">{{ implode(', ', array_map('ucfirst', $cfg['requires'])) }}</span>
                                @else
                                    <span class="text-slate-400 text-[11px]">None (Root)</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <a href="{{ route('admin.data-management.download-template', $type) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs transition-colors shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
