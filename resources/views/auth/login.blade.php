@extends('layouts.app')

@section('title', 'Sign In - Solar Field Operations')

@section('content')
<div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-slate-900 selection:bg-amber-500 selection:text-slate-950">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 items-center justify-center font-black shadow-xl shadow-amber-500/20 mb-3">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Solar Field Operations</h1>
            <p class="text-xs text-amber-400 font-semibold tracking-wider uppercase mt-1">Digital Reporting & Service Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-2xl border border-slate-800">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Account Login</h2>
            <p class="text-xs text-slate-500 mb-6">Sign in to access your operations dashboard or assigned field services.</p>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Password</label>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-amber-500 rounded border-slate-300 focus:ring-amber-500">
                        <span class="text-xs text-slate-600 font-medium">Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full mt-2 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Quick Demo One-Click Access -->
            <div class="mt-8 pt-6 border-t border-slate-200">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 text-center">Quick Demo Accounts</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <button type="button" 
                            onclick="fillCredentials('admin@solar.local', 'password123')"
                            class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-amber-50 hover:border-amber-300 text-left transition-all">
                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Admin Portal
                        </div>
                        <div class="text-[11px] text-slate-500 truncate">admin@solar.local</div>
                    </button>

                    <button type="button" 
                            onclick="fillCredentials('raj@solar.local', 'password123')"
                            class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-amber-50 hover:border-amber-300 text-left transition-all">
                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Engineer (Sun on Earth)
                        </div>
                        <div class="text-[11px] text-slate-500 truncate">raj@solar.local</div>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            Multi-Entity Solar Field Operations System &copy; {{ date('Y') }}
        </p>
    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
