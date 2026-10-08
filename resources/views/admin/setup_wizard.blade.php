@extends('layouts.admin')

@section('title', 'Initial Setup Wizard - SolarOps')
@section('header_title', 'System Provisioning & Onboarding')

@section('admin_content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Welcome Header Card -->
    <div class="bg-slate-900 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-xl border border-slate-800">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold text-xs uppercase tracking-wider rounded-lg mb-3">
                First-Time Setup
            </span>
            <h2 class="text-2xl font-black tracking-tight text-white">Welcome to SolarOps Field Operations</h2>
            <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                Your administrator password has been successfully configured. You can now onboard your existing historical company records, employees, clients, and solar plants using our Excel migration tools.
            </p>
        </div>
        <div class="absolute right-6 -bottom-6 opacity-10 hidden sm:block pointer-events-none">
            <svg class="w-64 h-64 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
    </div>

    <!-- 5-Step Provisioning Progress Flow -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">System Provisioning Steps</h3>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <!-- Step 1 -->
            <div class="p-3.5 rounded-xl border border-emerald-300 bg-emerald-50/60 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                    ✓
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Step 1: Admin Password</h4>
                    <p class="text-[11px] text-emerald-800 font-medium mt-0.5">Secure Password Set</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-slate-900 text-amber-400 flex items-center justify-center font-bold text-xs shrink-0">
                    2
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Step 2: Company Entity</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Corporate Profiles</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-3.5 rounded-xl border border-amber-300 bg-amber-50/40 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-xs shrink-0">
                    3
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Step 3: Data Migration</h4>
                    <p class="text-[11px] text-amber-800 font-medium mt-0.5">Excel Bulk Imports</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-slate-900 text-slate-300 flex items-center justify-center font-bold text-xs shrink-0">
                    4
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Step 4: Audit Status</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Verify Records</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-slate-900 text-slate-300 flex items-center justify-center font-bold text-xs shrink-0">
                    5
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Step 5: Operational</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Live Dispatch</p>
                </div>
            </div>
        </div>

        <!-- Action Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div class="p-5 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold mb-3 shadow-xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">Import Existing Records Now</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        Upload your existing Excel files for Companies, Field Engineers, Customers, Sites, and historical service reports.
                    </p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.data-management.index') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all">
                        <span>Open Data Migration Center</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <div class="p-5 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold mb-3 shadow-xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">Skip to Main Dashboard</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        You can perform data migration anytime later from the Data Management section in the admin sidebar.
                    </p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all">
                        <span>Go to Dashboard</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
