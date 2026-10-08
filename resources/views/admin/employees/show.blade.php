@extends('layouts.admin')

@section('title', $employee->name . ' - Employee Profile - SolarOps')
@section('header_title', 'Employee Profile')

@section('admin_content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 text-amber-400 font-extrabold text-xl flex items-center justify-center border-2 border-slate-800 shadow-md">
                    {{ substr($employee->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $employee->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $employee->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        {{ $employee->designation ?? 'Field Operations' }} &bull; 
                        <span class="font-mono font-bold text-slate-700">ID: {{ $employee->employee_code }}</span>
                    </p>
                    <p class="text-xs text-amber-600 font-semibold mt-1">
                        Entity: {{ $employee->company?->name ?? 'All Entities (Global)' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.employees.edit', $employee->id) }}" 
                   class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all">
                    Edit Profile
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Email</span>
                <span class="font-bold text-slate-800">{{ $employee->email }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Phone</span>
                <span class="font-bold text-slate-800">{{ $employee->phone ?? '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Joining Date</span>
                <span class="font-bold text-slate-800">{{ $employee->joining_date?->format('d M Y') ?? '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Total Assigned Jobs</span>
                <span class="font-bold text-indigo-700">{{ $employee->assignedServices->count() }} Services</span>
            </div>
        </div>
    </div>

    <!-- Login & Onboarding Credentials Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6" x-data="{
        tempPassword: '{{ session('revealed_temp_password') && session('revealed_employee_id') == $employee->id ? session('revealed_temp_password') : '' }}',
        showPassword: {{ session('revealed_temp_password') && session('revealed_employee_id') == $employee->id ? 'true' : 'false' }},
        revealing: false,
        copiedMsg: false,
        async reveal() {
            if (this.tempPassword) {
                this.showPassword = !this.showPassword;
                return;
            }
            this.revealing = true;
            try {
                const res = await fetch('{{ route('admin.data-management.reveal-credential', $employee->id) }}');
                const data = await res.json();
                if (data.success && data.password) {
                    this.tempPassword = data.password;
                    this.showPassword = true;
                } else {
                    alert('Unable to retrieve temporary password. It may have expired or been changed.');
                }
            } catch(e) {
                alert('Network error retrieving credential.');
            } finally {
                this.revealing = false;
            }
        },
        async copyText(text, type) {
            navigator.clipboard.writeText(text);
            this.copiedMsg = type;
            setTimeout(() => { this.copiedMsg = false; }, 2000);
            try {
                await fetch('{{ route('admin.data-management.audit-copy-credential') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ employee_id: {{ $employee->id }} })
                });
            } catch(e) {}
        },
        copyCredentials() {
            const pass = this.tempPassword || '••••••••';
            const text = `SolarOps Login\n\nName: {{ $employee->name }}\nUser ID: {{ $employee->username ?? $employee->email }}\nTemporary Password: ${pass}\n\nPlease change your password after your first login.`;
            this.copyText(text, 'Full Credentials');
        }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Authentication & Onboarding Credentials
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Manage user login credentials, onboarding temporary passwords, and security status.</p>
            </div>
            <div>
                @if($employee->status === 'inactive')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">Account Deactivated</span>
                @elseif($employee->password_change_required)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Pending First Login (Password Change Required)</span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Active — Password Changed</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5 text-xs">
            <!-- Left: Identity Info -->
            <div class="space-y-3 bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">User ID / Username:</span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $employee->username ?? 'Not set' }}</span>
                        @if($employee->username)
                            <button type="button" @click="copyText('{{ $employee->username }}', 'User ID')" class="text-amber-600 hover:text-amber-700 font-semibold text-[11px]">Copy</button>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Primary Email:</span>
                    <span class="font-bold text-slate-800">{{ $employee->email }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Employee Code:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $employee->employee_code }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Role:</span>
                    <span class="font-bold uppercase tracking-wider text-slate-700">{{ $employee->role }}</span>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-200/60">
                    <span class="text-slate-500 font-medium">Last Login:</span>
                    <span class="text-slate-700 font-medium">{{ $employee->last_login_at ? $employee->last_login_at->format('d M Y, h:i A') : 'Never logged in' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Password Changed:</span>
                    <span class="text-slate-700 font-medium">{{ $employee->password_changed_at ? $employee->password_changed_at->format('d M Y, h:i A') : 'Not changed yet' }}</span>
                </div>
            </div>

            <!-- Right: Temporary Credential Handover -->
            <div class="space-y-4 bg-amber-50/40 p-4 rounded-xl border border-amber-200/60">
                <div>
                    <h4 class="font-bold text-amber-900 text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Temporary Onboarding Credential
                    </h4>
                    <p class="text-[11px] text-amber-800/80 mt-0.5">
                        Admins can reveal and share initial credentials. Once the employee signs in and sets a permanent password, temporary credentials are permanently deleted.
                    </p>
                </div>

                @if($employee->password_change_required)
                    <div class="bg-white p-3.5 rounded-lg border border-amber-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-600">Temporary Password:</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900" x-text="showPassword && tempPassword ? tempPassword : '••••••••'"></span>
                                <button type="button" @click="reveal()" :disabled="revealing" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[11px] font-bold text-slate-700 transition-colors">
                                    <span x-show="!revealing" x-text="showPassword ? 'Hide' : 'Show'"></span>
                                    <span x-show="revealing">Loading...</span>
                                </button>
                            </div>
                        </div>

                        @if($employee->temporary_password_expires_at)
                            <div class="text-[11px] text-slate-500 flex items-center justify-between pt-1 border-t border-slate-100">
                                <span>Expires:</span>
                                <span class="{{ $employee->hasExpiredTemporaryPassword() ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                    {{ $employee->temporary_password_expires_at->format('d M Y, h:i A') }}
                                    @if($employee->hasExpiredTemporaryPassword()) (Expired) @endif
                                </span>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="copyText('{{ $employee->username ?? $employee->email }}', 'User ID')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-semibold text-[11px]">
                                Copy User ID
                            </button>
                            <button type="button" @click="tempPassword ? copyText(tempPassword, 'Password') : reveal()" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded font-semibold text-[11px]">
                                Copy Password
                            </button>
                            <button type="button" @click="copyCredentials()" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded font-bold text-[11px]">
                                Copy Credentials
                            </button>
                        </div>
                        <div x-show="copiedMsg" x-cloak class="text-[11px] font-bold text-emerald-600">
                            Copied <span x-text="copiedMsg"></span> to clipboard!
                        </div>
                    </div>
                @else
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-emerald-800 text-xs">
                        <p class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Password Initialized & Verified
                        </p>
                        <p class="text-[11px] text-emerald-700 mt-1">
                            The employee changed their password on first login. The temporary credential has been securely consumed and destroyed.
                        </p>
                    </div>
                @endif

                <div class="pt-2 flex items-center justify-between">
                    <form method="POST" action="{{ route('admin.employees.regenerate-temp-password', $employee->id) }}" onsubmit="return confirm('Regenerate a new temporary password for {{ $employee->name }}? The user will be required to change it on their next login.');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold text-xs shadow-xs transition-all">
                            Regenerate Temporary Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Services & Submitted Reports Tabs/Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assigned Services -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Assigned Services ({{ $employee->assignedServices->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($employee->assignedServices as $srv)
                    <div class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-slate-900">{{ $srv->service_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $srv->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                                </span>
                            </div>
                            <p class="text-slate-600 font-medium">{{ $srv->customer->name }} &bull; {{ $srv->site->name }}</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">Scheduled: {{ $srv->scheduled_date->format('d M Y') }}</p>
                        </div>
                        <a href="{{ route('admin.services.show', $srv->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold shrink-0">
                            View
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">No services assigned yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Submitted Reports -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Submitted Reports ({{ $employee->reports->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                @forelse($employee->reports as $rep)
                    <div class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-slate-900">{{ $rep->report_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $rep->status_badge_class }}">
                                    {{ strtoupper(str_replace('_', ' ', $rep->status)) }}
                                </span>
                            </div>
                            <p class="text-slate-600 font-medium">{{ $rep->customer->name }} &bull; {{ $rep->site->name }}</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">Updated: {{ $rep->updated_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('admin.reports.show', $rep->id) }}" class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold shrink-0">
                            Review
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">No reports submitted yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
