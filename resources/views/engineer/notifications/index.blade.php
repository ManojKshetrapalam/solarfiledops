@extends('layouts.engineer')

@section('mobile_title', 'Notifications')

@section('engineer_content')
<div class="space-y-4">
    <div class="flex items-center justify-between px-1">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">In-App Alerts</h2>
        <form action="{{ route('engineer.notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="text-xs font-bold text-amber-600 hover:text-amber-700">Mark all read</button>
        </form>
    </div>

    <div class="space-y-2">
        @forelse($notifications as $n)
            <a href="{{ route('engineer.notifications.read', $n->id) }}" 
               class="block bg-white p-4 rounded-2xl border transition-colors shadow-xs {{ is_null($n->read_at) ? 'border-amber-400 bg-amber-50/20' : 'border-slate-200' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-xs text-slate-900">{{ $n->title }}</span>
                    <span class="text-[10px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-slate-600 font-medium leading-relaxed">{{ $n->message }}</p>
            </a>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                No notifications right now.
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
