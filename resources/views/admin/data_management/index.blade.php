@extends('layouts.admin')

@section('title', 'Data Migration Center - SolarOps')
@section('header_title', 'Data Migration Center')

@section('admin_content')
<div class="space-y-6" x-data="{
    uploadModal: false,
    selectedType: 'companies',
    selectedTitle: 'Companies / Entities',
    fileChosen: false,
    fileName: '',
    openUpload(type, title) {
        this.selectedType = type;
        this.selectedTitle = title;
        this.fileChosen = false;
        this.fileName = '';
        this.uploadModal = true;
    },
    handleFile(e) {
        if (e.target.files.length > 0) {
            this.fileChosen = true;
            this.fileName = e.target.files[0].name;
        }
    }
}">
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Enterprise Data Migration & Onboarding</h2>
            <p class="text-xs text-slate-500 mt-0.5">Migrate historical operational records, register site architectures, and auto-provision field engineer credentials.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.data-management.download-all-templates') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 shadow-2xs transition-all">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download All Templates (.ZIP)</span>
            </a>
            <a href="{{ route('admin.data-management.history') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Import History</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <p class="font-bold mb-1">Please correct the following errors:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Migration Order Guidance Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-5 text-white shadow-md border border-slate-700/50">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    Recommended Sequence
                </span>
                <h3 class="text-base font-bold text-white mt-1.5">Strict Foreign Key Migration Hierarchy</h3>
                <p class="text-xs text-slate-300 mt-0.5 max-w-2xl">
                    SolarOps enforces multi-entity isolation. To prevent relationship errors, upload your files in the following order:
                </p>
            </div>
            <a href="{{ route('admin.setup-wizard') }}" class="inline-flex items-center gap-1.5 text-xs text-amber-400 hover:text-amber-300 font-bold underline">
                <span>View Full Onboarding Guide</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 mt-4 pt-4 border-t border-slate-700/60 text-xs">
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700">
                <div class="text-[10px] font-bold text-amber-400">Step 1</div>
                <div class="font-bold text-white">Companies</div>
                <div class="text-[10px] text-slate-400">Base legal entities</div>
            </div>
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700">
                <div class="text-[10px] font-bold text-amber-400">Step 2</div>
                <div class="font-bold text-white">Employees</div>
                <div class="text-[10px] text-slate-400">Staff & Logins</div>
            </div>
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700">
                <div class="text-[10px] font-bold text-amber-400">Step 3</div>
                <div class="font-bold text-white">Customers</div>
                <div class="text-[10px] text-slate-400">Linked to Company</div>
            </div>
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700">
                <div class="text-[10px] font-bold text-amber-400">Step 4</div>
                <div class="font-bold text-white">Sites</div>
                <div class="text-[10px] text-slate-400">Customer & Plant</div>
            </div>
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700 col-span-2 sm:col-span-1">
                <div class="text-[10px] font-bold text-amber-400">Step 5</div>
                <div class="font-bold text-white">Field Operations</div>
                <div class="text-[10px] text-slate-400">Jobs, Reports & Logs</div>
            </div>
        </div>
    </div>

    <!-- 10 Migration Entity Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($configs as $type => $cfg)
            @php
                $count = $counts[$type] ?? 0;
                $step = match($type) {
                    'companies' => 'Step 1',
                    'employees' => 'Step 2',
                    'customers' => 'Step 3',
                    'sites' => 'Step 4',
                    default => 'Step 5',
                };
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:shadow-xs transition-all flex flex-col justify-between p-5 relative overflow-hidden group">
                <div class="absolute top-0 right-0 h-1.5 w-full {{ $count > 0 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider {{ $step === 'Step 1' || $step === 'Step 2' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ $step }}
                        </span>
                        <span class="text-[11px] font-bold {{ $count > 0 ? 'text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200' : 'text-slate-400' }}">
                            {{ number_format($count) }} in database
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 group-hover:text-amber-600 transition-colors">
                        {{ $cfg['title'] ?? $cfg['name'] }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ $cfg['description'] }}
                    </p>

                    @if(!empty($cfg['requires']))
                        <div class="mt-3 flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                            <span class="text-slate-500 font-bold">Prerequisites:</span>
                            <span class="text-indigo-600 font-semibold">{{ implode(', ', array_map('ucfirst', $cfg['requires'])) }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="{{ route('admin.data-management.download-template', $type) }}" 
                       class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-slate-900 font-bold transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Template</span>
                    </a>

                    <button type="button" 
                            @click="openUpload('{{ $type }}', '{{ $cfg['title'] ?? $cfg['name'] }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-lg shadow-2xs transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Import</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Upload Modal -->
    <div x-show="uploadModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="uploadModal = false">
        <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150"
             @click.outside="uploadModal = false">
            
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Import <span x-text="selectedTitle"></span>
                    </h3>
                    <p class="text-[11px] text-slate-300 mt-0.5">Upload completed Excel spreadsheet (.xlsx format only)</p>
                </div>
                <button type="button" @click="uploadModal = false" class="text-slate-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.data-management.preview') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="import_type" :value="selectedType">

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Select Spreadsheet File</label>
                    
                    <div class="border-2 border-dashed border-slate-300 hover:border-amber-500 rounded-xl p-6 text-center cursor-pointer transition-colors bg-slate-50/50"
                         @click="$refs.fileInput.click()">
                        <input type="file" 
                               name="excel_file" 
                               x-ref="fileInput" 
                               accept=".xlsx" 
                               @change="handleFile($event)" 
                               class="hidden" 
                               required>
                        
                        <div x-show="!fileChosen" class="space-y-2">
                            <svg class="w-10 h-10 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-xs text-slate-600 font-semibold">Click to select or drag & drop .xlsx file</p>
                            <p class="text-[11px] text-slate-400">Only genuine Excel (.xlsx) files up to 10MB supported</p>
                        </div>

                        <div x-show="fileChosen" class="space-y-1" x-cloak>
                            <svg class="w-10 h-10 text-emerald-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs font-bold text-slate-800" x-text="fileName"></p>
                            <p class="text-[11px] text-emerald-600 font-medium">File ready for validation</p>
                        </div>
                    </div>
                </div>

                <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3 text-[11px] text-amber-900 leading-relaxed">
                    <p class="font-bold flex items-center gap-1 text-amber-800">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Automated Validation & Preview Step
                    </p>
                    <p class="mt-0.5">
                        Uploading will <strong>not</strong> write directly to the database. SolarOps will parse headers, validate rows, check duplicates, and display a comprehensive preview before you confirm.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" @click="uploadModal = false" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!fileChosen" 
                            :class="{'opacity-50 cursor-not-allowed': !fileChosen}"
                            class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs rounded-xl shadow-xs transition-all">
                        Validate & Preview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
