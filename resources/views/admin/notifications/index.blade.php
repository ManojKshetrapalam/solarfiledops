@extends('layouts.admin')

@section('title', 'Admin Notifications - SolarOps')
@section('header_title', 'Notifications Center')

@section('admin_content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">System Notifications & Operational Alerts</h2>
            <p class="text-xs text-slate-500">Track incoming field reports, correction submissions, and service job triggers.</p>
        </div>
        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                Mark all as read
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
        @forelse($notifications as $n)
            <a href="{{ route('admin.notifications.read', $n->id) }}" 
               class="p-4 flex items-start gap-4 hover:bg-slate-50/70 transition-colors {{ is_null($n->read_at) ? 'bg-amber-50/30' : '' }}">
                <div class="w-2.5 h-2.5 rounded-full mt-2 shrink-0 {{ is_null($n->read_at) ? 'bg-amber-500' : 'bg-slate-300' }}"></div>
                <div class="flex-1 min-w-0 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-slate-900 text-sm">{{ $n->title }}</span>
                        <span class="text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed font-medium">{{ $n->message }}</p>
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-slate-400 text-xs">No notifications.</div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
