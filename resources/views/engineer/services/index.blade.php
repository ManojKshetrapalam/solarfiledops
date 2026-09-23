@extends('layouts.engineer')

@section('mobile_title', 'My Assigned Work')

@section('engineer_content')
<div class="space-y-4">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('engineer.services.index') }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            All Jobs
        </a>
        <a href="{{ route('engineer.services.index', ['status' => 'assigned']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'assigned' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            New Assigned
        </a>
        <a href="{{ route('engineer.services.index', ['status' => 'in_progress']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'in_progress' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            In Progress
        </a>
        <a href="{{ route('engineer.services.index', ['status' => 'completed']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'completed' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Completed
        </a>
    </div>

    <!-- Jobs List -->
    <div class="space-y-3">
        @forelse($services as $srv)
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">
                        {{ $srv->service_number }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $srv->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $srv->status)) }}
                    </span>
                </div>

                <h4 class="text-sm font-bold text-slate-900 leading-tight">
                    {{ $srv->customer->name }}
                </h4>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    {{ $srv->site->name }} &bull; {{ $srv->serviceType->name }}
                </p>

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Date: <strong class="text-slate-800">{{ $srv->scheduled_date->format('d M Y') }}</strong></span>
                    <a href="{{ route('engineer.services.show', $srv->id) }}" 
                       class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-xs text-xs">
                        View Details &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                No service work orders found.
            </div>
        @endforelse
    </div>

    @if($services->hasPages())
        <div class="pt-2">
            {{ $services->links() }}
        </div>
    @endif
</div>
@endsection
