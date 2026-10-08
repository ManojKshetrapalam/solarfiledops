@extends('layouts.app')

@section('title', 'Change Password - SolarOps')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-slate-900 selection:bg-amber-500 selection:text-slate-950">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 items-center justify-center font-black shadow-xl shadow-amber-500/20 mb-3">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Welcome to SolarOps</h1>
            <p class="text-xs text-amber-400 font-semibold tracking-wider uppercase mt-1">First Login Password Setup</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-2xl border border-slate-800">
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200">
                <div class="flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h3 class="text-xs font-bold text-amber-900">Mandatory Security Verification</h3>
                        <p class="text-xs text-amber-800 mt-0.5">
                            For security, you must change your temporary password before continuing to your account dashboard.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-4 pb-3 border-b border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Logged in as:</span>
                <span class="font-bold text-slate-900">{{ $user->name }} ({{ $user->username ?? $user->email }})</span>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                    @foreach($errors->all() as $err)
                        <p>&bull; {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('auth.change-password.update') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Current (Temporary) Password *</label>
                    <input type="password" name="current_password" id="current_password" required autofocus
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                </div>

                <div>
                    <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Permanent Password *</label>
                    <input type="password" name="new_password" id="new_password" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password *</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                </div>

                <!-- Password Requirements Checklist -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                    <p class="font-bold text-slate-700 uppercase text-[10px]">Password Requirements:</p>
                    <ul class="space-y-0.5 list-disc list-inside">
                        <li>Minimum 8 characters</li>
                        <li>At least one uppercase letter (A-Z)</li>
                        <li>At least one lowercase letter (a-z)</li>
                        <li>At least one number (0-9)</li>
                        <li>At least one special character (!@#$%^&*...)</li>
                    </ul>
                </div>

                <button type="submit" 
                        class="w-full mt-2 py-3 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Update Password & Continue</span>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition-colors">
                        Cancel & Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
