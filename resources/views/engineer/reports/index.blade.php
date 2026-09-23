@extends('layouts.engineer')

@section('mobile_title', 'My Field Reports')

@section('engineer_content')
<div class="space-y-4">
    <!-- Status Filter Pills -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('engineer.reports.index') }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            All Reports
        </a>
        <a href="{{ route('engineer.reports.index', ['status' => 'draft']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'draft' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Drafts
        </a>
        <a href="{{ route('engineer.reports.index', ['status' => 'correction_required']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'correction_required' ? 'bg-amber-500 text-slate-950' : 'bg-white text-slate-600 border border-slate-200' }}">
            Needs Correction
        </a>
        <a href="{{ route('engineer.reports.index', ['status' => 'submitted']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'submitted' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Submitted
        </a>
        <a href="{{ route('engineer.reports.index', ['status' => 'approved']) }}" 
           class="px-3.5 py-1.5 rounded-full font-bold transition-colors whitespace-nowrap {{ request('status') === 'approved' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Approved
        </a>
    </div>

    <!-- Reports List -->
    <div class="space-y-3">
        @forelse($reports as $rep)
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">
                        {{ $rep->report_number }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $rep->status_badge_class }}">
                        {{ strtoupper(str_replace('_', ' ', $rep->status)) }}
                    </span>
                </div>

                <h4 class="text-sm font-bold text-slate-900 leading-tight">
                    {{ $rep->customer->name }}
                </h4>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    {{ $rep->site->name }} &bull; Step {{ $rep->current_step }} of 10
                </p>

                @if($rep->status === 'correction_required' && $rep->correction_notes)
                    <div class="mt-2 bg-amber-50 border border-amber-300 p-2 rounded-lg text-amber-900 text-[11px] font-medium">
                        <strong>Admin note:</strong> {{ $rep->correction_notes }}
                    </div>
                @endif

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">
                        {{ $rep->updated_at->diffForHumans() }}
                    </span>

                    @if($rep->isEditableByEngineer())
                        <a href="{{ route('engineer.reports.edit', $rep->id) }}" 
                           class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl shadow-xs text-xs flex items-center gap-1">
                            <span>Edit Report</span>
                            <span>&rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('engineer.reports.show', $rep->id) }}" 
                           class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs">
                            View Report
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400 text-xs">
                No reports found matching criteria.
            </div>
        @endforelse
    </div>

    @if($reports->hasPages())
        <div class="pt-2">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection
