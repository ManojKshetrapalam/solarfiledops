@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col bg-slate-100 pb-20 select-none">
    <!-- Top Mobile App Header -->
    <header class="sticky top-0 z-40 bg-slate-900 text-white shadow-md border-b border-slate-800 px-4 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-black">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-tight text-white leading-tight">
                        @yield('mobile_title', 'Solar Field Ops')
                    </h1>
                    <p class="text-[10px] text-amber-400 font-medium">
                        {{ auth()->user()->company?->name ?? 'Solar Field Operations' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Notifications Bell -->
                <a href="{{ route('engineer.notifications.index') }}" class="relative p-2 text-slate-300 hover:text-white rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @php $unreadCount = auth()->user()->unreadNotificationsCount(); @endphp
                    @if($unreadCount > 0)
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-amber-400 rounded-full ring-2 ring-slate-900"></span>
                    @endif
                </a>

                <!-- Profile Initials -->
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 text-slate-200 flex items-center justify-center font-bold text-xs">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
            </div>
        </div>

        <!-- Network connectivity monitor indicator -->
        <div x-data="{ online: navigator.onLine }" 
             x-init="window.addEventListener('online', () => online = true); window.addEventListener('offline', () => online = false)"
             x-show="!online"
             class="mt-2 bg-amber-600/90 text-white text-[11px] font-semibold px-2.5 py-1 rounded-md flex items-center gap-1.5 justify-center"
             x-cloak>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-2.829-2.829m0 0L3 21m2.829-5.657a5 5 0 010-7.072m0 0l2.829 2.829" />
            </svg>
            <span>Offline mode. Draft changes are stored locally on your device.</span>
        </div>
    </header>

    <!-- Main Mobile Content Area -->
    <main class="flex-1 p-4 max-w-lg mx-auto w-full">
        @yield('engineer_content')
    </main>

    <!-- Sticky Bottom Navigation Bar -->
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200 px-2 py-1 shadow-lg max-w-lg mx-auto">
        <div class="grid grid-cols-4 gap-1">
            <a href="{{ route('engineer.dashboard') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('engineer.dashboard') ? 'text-amber-600' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('engineer.services.index') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('engineer.services.*') ? 'text-amber-600' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>My Work</span>
            </a>

            <a href="{{ route('engineer.reports.index') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('engineer.reports.*') ? 'text-amber-600' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Reports</span>
            </a>

            <a href="{{ route('engineer.profile') }}" 
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-lg text-xs font-semibold transition-colors {{ request()->routeIs('engineer.profile') ? 'text-amber-600' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profile</span>
            </a>
        </div>
    </nav>
</div>
@endsection
