@extends('layouts.engineer')

@section('mobile_title', 'My Profile')

@section('engineer_content')
<div class="space-y-4">
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs text-center">
        <div class="w-16 h-16 rounded-full bg-slate-900 text-amber-400 font-extrabold text-xl mx-auto flex items-center justify-center shadow-md mb-3">
            {{ substr($user->name, 0, 2) }}
        </div>
        <h2 class="text-lg font-bold text-slate-900">{{ $user->name }}</h2>
        <p class="text-xs text-slate-500 font-medium">{{ $user->designation }}</p>
        <span class="inline-block mt-2 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200">
            {{ $user->company?->name ?? 'All Entities (Global)' }}
        </span>

        <div class="mt-6 pt-4 border-t border-slate-100 text-left space-y-2.5 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400">Employee ID:</span>
                <span class="font-mono font-bold text-slate-800">{{ $user->employee_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Email:</span>
                <span class="font-bold text-slate-800">{{ $user->email }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Phone:</span>
                <span class="font-bold text-slate-800">{{ $user->phone }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Joined:</span>
                <span class="font-bold text-slate-800">{{ $user->joining_date?->format('d M Y') ?? '—' }}</span>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="w-full py-3 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
