@extends('layouts.admin')

@section('title', 'Import Completed - SolarOps')
@section('header_title', 'Import Completed')

@section('admin_content')
<div class="space-y-6" x-data="{
    showAllPasswords: false,
    revealedRowIds: {},
    copiedMessage: '',
    toggleRowPassword(idx) {
        this.revealedRowIds[idx] = !this.revealedRowIds[idx];
    },
    isPasswordRevealed(idx) {
        return this.showAllPasswords || !!this.revealedRowIds[idx];
    },
    async copyText(text, label, empId = null) {
        navigator.clipboard.writeText(text);
        this.copiedMessage = label;
        setTimeout(() => { this.copiedMessage = ''; }, 2500);

        try {
            await fetch('{{ route('admin.data-management.audit-copy-credential') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ employee_id: empId })
            });
        } catch(e) {}
    },
    copyRowCredentials(emp, idx) {
        const pass = emp.temporary_password;
        const text = `SolarOps Login\n\nName: ${emp.name}\nUser ID: ${emp.username}\nTemporary Password: ${pass}\n\nPlease change your password after your first login.`;
        this.copyText(text, `${emp.name}'s Credentials`, emp.id);
    }
}">
    <!-- Result Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-2xl border border-emerald-500/20 shrink-0">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Import Completed Successfully</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Processed
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        File: <span class="font-mono font-bold text-slate-700">{{ $fileName }}</span> &bull; 
                        Type: <span class="font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.data-management.index') }}" 
                   class="px-4 py-2 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-colors">
                    Migration Center
                </a>
                <a href="{{ route('admin.data-management.history') }}" 
                   class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-2xs transition-all">
                    View Import History
                </a>
            </div>
        </div>

        <!-- Metrics Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rows</span>
                <div class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($result['total_rows']) }}</div>
            </div>
            <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Records Imported</span>
                <div class="text-xl font-black text-emerald-600 mt-0.5">{{ number_format($result['imported_count']) }}</div>
            </div>
            <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Duplicates Skipped</span>
                <div class="text-xl font-black text-slate-600 mt-0.5">{{ number_format($result['skipped_count']) }}</div>
            </div>
            <div class="bg-rose-50/70 p-3 rounded-xl border border-rose-100">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Failed / Errors</span>
                <div class="text-xl font-black text-rose-600 mt-0.5">{{ number_format($result['failed_count']) }}</div>
            </div>
        </div>
    </div>

    <!-- Copied Toast Banner -->
    <div x-show="copiedMessage" x-cloak class="p-3 bg-slate-900 text-white text-xs font-bold rounded-xl flex items-center justify-between shadow-lg">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="`Copied ${copiedMessage} to clipboard!`"></span>
        </span>
    </div>

    <!-- EMPLOYEE CREDENTIAL HANDOVER SECTION (CRITICAL) -->
    @if($type === 'employees' && !empty($credentials))
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-amber-500/10 via-transparent to-transparent flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-amber-500 text-slate-950 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </span>
                        <h3 class="text-base font-extrabold text-slate-900">Employee Onboarding Credential Handover</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Secure temporary passwords generated for <strong class="text-slate-800">{{ count($credentials) }}</strong> employees. Provide these to your field staff for initial login.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" 
                            @click="showAllPasswords = !showAllPasswords" 
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                        <span x-text="showAllPasswords ? 'Hide All Passwords' : 'Show All Passwords'"></span>
                    </button>
                    <a href="{{ route('admin.data-management.download-credential-sheet') }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-extrabold rounded-xl shadow-xs transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download Credential Sheet (.XLSX)</span>
                    </a>
                </div>
            </div>

            <!-- Credential Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-5">Employee Name</th>
                            <th class="py-3.5 px-5">Code</th>
                            <th class="py-3.5 px-5">User ID / Username</th>
                            <th class="py-3.5 px-5">Temporary Password</th>
                            <th class="py-3.5 px-5">Status</th>
                            <th class="py-3.5 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($credentials as $idx => $cred)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-5 font-bold text-slate-900">
                                    {{ $cred['name'] }}
                                    <span class="block text-[11px] font-normal text-slate-400">{{ $cred['email'] }}</span>
                                </td>
                                <td class="py-3.5 px-5 font-mono text-slate-600">
                                    {{ $cred['employee_code'] }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded text-xs">
                                            {{ $cred['username'] }}
                                        </span>
                                        <button type="button" 
                                                @click="copyText('{{ $cred['username'] }}', 'User ID', {{ $cred['id'] ?? 'null' }})" 
                                                class="text-slate-400 hover:text-indigo-600 font-semibold text-[10px]" 
                                                title="Copy User ID">
                                            Copy
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 font-mono">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 bg-slate-100 px-2.5 py-1 rounded text-xs"
                                              x-text="isPasswordRevealed({{ $idx }}) ? '{{ $cred['temporary_password'] }}' : '••••••••'">
                                        </span>
                                        <button type="button" 
                                                @click="toggleRowPassword({{ $idx }})" 
                                                class="text-xs text-amber-700 hover:text-amber-800 font-sans font-semibold underline"
                                                x-text="isPasswordRevealed({{ $idx }}) ? 'Hide' : 'Show'">
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                        Pending Password Change
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                @click="copyText('{{ $cred['temporary_password'] }}', 'Password', {{ $cred['id'] ?? 'null' }})"
                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-colors">
                                            Copy Password
                                        </button>
                                        <button type="button" 
                                                @click="copyRowCredentials({{ Js::from($cred) }}, {{ $idx }})"
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg text-xs transition-colors">
                                            Copy Credentials
                                        </button>
                                    </div>
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
