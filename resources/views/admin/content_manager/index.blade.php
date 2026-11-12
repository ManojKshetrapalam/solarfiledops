@extends('layouts.admin')

@section('title', 'Frontend Content Manager')

@section('content')
<div class="space-y-6" x-data="{
    historyOpen: false,
    historyLoading: false,
    historyData: null,
    openHistory(id) {
        this.historyOpen = true;
        this.historyLoading = true;
        this.historyData = null;
        fetch('{{ url('/admin/content-manager') }}/' + id + '/history')
            .then(res => res.json())
            .then(data => {
                this.historyData = data;
                this.historyLoading = false;
            })
            .catch(() => {
                this.historyLoading = false;
            });
    }
}">
    <!-- Header & Scope Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-amber-500/10 text-amber-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </span>
                <h1 class="text-xl font-bold text-slate-900">Frontend Content Manager</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Manage user-facing terminology, system branding, labels, and report copy without redeploying code.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Company Scope Selector -->
            <form method="GET" action="{{ route('admin.content-manager.index') }}" class="flex items-center gap-2">
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                
                <label for="company_id" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Scope:</label>
                <select name="company_id" id="company_id" onchange="this.form.submit()" 
                        class="text-xs font-semibold rounded-lg border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 bg-slate-50 py-1.5 px-3">
                    @if(auth()->user()->isSuperAdmin())
                        <option value="global" {{ $selectedCompanyId === null ? 'selected' : '' }}>Global System Defaults</option>
                    @endif
                    @foreach($availableCompanies as $company)
                        <option value="{{ $company->id }}" {{ $selectedCompanyId === $company->id ? 'selected' : '' }}>
                            {{ $company->name }} (Company Override)
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Toggle Live Draft Preview -->
            <form method="POST" action="{{ route('admin.content-manager.toggle-preview') }}">
                @csrf
                <button type="submit" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border flex items-center gap-1.5 {{ $previewActive ? 'bg-amber-500 text-slate-950 border-amber-600 shadow-sm animate-pulse' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200' }}"
                        title="Isolate draft preview to your session to test wording before publishing">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>{{ $previewActive ? 'Live Preview: ON' : 'Preview Drafts: OFF' }}</span>
                </button>
            </form>

            <!-- Publish All Pending Drafts -->
            @if($pendingCount > 0)
                <form method="POST" action="{{ route('admin.content-manager.publish-all') }}" onsubmit="return confirm('Publish all {{ $pendingCount }} pending draft changes live? This will immediately update the live application for users.');">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $selectedCompanyId ?? 'global' }}">
                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Publish All Drafts ({{ $pendingCount }})</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-500">Total Managed Keys</span>
            <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $totalCount }}</p>
            <span class="text-[10px] text-slate-400">In current scope</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-500">Customized Wording</span>
            <p class="text-xl font-extrabold text-amber-600 mt-0.5">{{ $customizedCount }}</p>
            <span class="text-[10px] text-slate-400">Differing from defaults</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-500">Pending Drafts</span>
            <p class="text-xl font-extrabold {{ $pendingCount > 0 ? 'text-amber-500' : 'text-slate-400' }} mt-0.5">{{ $pendingCount }}</p>
            <span class="text-[10px] text-slate-400">Ready for review & publishing</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase text-slate-500">Active Scope</span>
            <p class="text-sm font-bold text-slate-800 truncate mt-1">
                {{ $selectedCompanyId ? ($availableCompanies->firstWhere('id', $selectedCompanyId)?->name ?? 'Company') : 'Global System Defaults' }}
            </p>
            <span class="text-[10px] text-slate-400">{{ $selectedCompanyId ? 'Company-specific override' : 'All companies fallback' }}</span>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Search Input -->
            <form method="GET" action="{{ route('admin.content-manager.index') }}" class="flex-1 max-w-md">
                @if($selectedCompanyId) <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}"> @endif
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search keys, original wording, or custom text..."
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 bg-slate-50">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>

            <!-- Status Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] font-bold text-slate-400 mr-1 uppercase">Status:</span>
                @php
                    $statuses = [
                        'all' => 'All',
                        'pending_draft' => 'Drafts Pending (' . $pendingCount . ')',
                        'customized' => 'Customized',
                        'default' => 'System Defaults',
                    ];
                @endphp
                @foreach($statuses as $sKey => $sLabel)
                    <a href="{{ route('admin.content-manager.index', array_merge(request()->query(), ['status' => $sKey, 'page' => 1])) }}"
                       class="px-2.5 py-1 rounded-md text-xs font-semibold transition-colors {{ $statusFilter === $sKey ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $sLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Category Pills (12 Required Categories) -->
        <div class="border-t border-slate-100 pt-3 flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <span class="text-[11px] font-bold text-slate-400 mr-1 uppercase shrink-0">Category:</span>
            <a href="{{ route('admin.content-manager.index', array_merge(request()->query(), ['category' => null, 'page' => 1])) }}"
               class="px-2.5 py-1 rounded-md shrink-0 font-medium transition-colors {{ empty($activeCategory) ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Categories ({{ $totalCount }})
            </a>
            @foreach($categories as $catKey => $catName)
                <a href="{{ route('admin.content-manager.index', array_merge(request()->query(), ['category' => $catKey, 'page' => 1])) }}"
                   class="px-2.5 py-1 rounded-md shrink-0 font-medium transition-colors {{ $activeCategory === $catKey ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $catName }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Content Items List -->
    <div class="space-y-3">
        @forelse($contents as $content)
            @php
                $isPending = $content->isPendingPublication();
                $isCustomized = $content->isCustomized();
            @endphp
            <div class="bg-white rounded-xl border {{ $isPending ? 'border-amber-300 ring-1 ring-amber-100' : 'border-slate-200' }} shadow-sm p-4 transition-all">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <code class="text-xs font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 select-all">{{ $content->key }}</code>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                {{ $categories[$content->category] ?? $content->category }}
                            </span>
                            @if($content->company_id)
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                    {{ $content->company?->name ?? 'Company Override' }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                    Global
                                </span>
                            @endif

                            @if($isPending)
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 animate-pulse">
                                    Draft Pending
                                </span>
                            @elseif($isCustomized)
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                    Customized (Live)
                                </span>
                            @else
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                    System Default
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right meta & History button -->
                    <div class="flex items-center gap-2 shrink-0 text-xs">
                        @if($content->published_at)
                            <span class="text-[11px] text-slate-400">Published: {{ $content->published_at->diffForHumans() }}</span>
                        @endif
                        <button type="button" @click="openHistory({{ $content->id }})" 
                                class="px-2.5 py-1 text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 text-[11px] font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>History</span>
                        </button>
                    </div>
                </div>

                <!-- Form & Wording Editor -->
                <div class="pt-3 grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                    <!-- Default System Value (Reference) -->
                    <div class="lg:col-span-4 bg-slate-50/80 p-2.5 rounded-lg border border-slate-200">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Original / Default Value:</span>
                        <p class="text-xs text-slate-700 font-medium leading-relaxed select-all">{{ $content->default_value }}</p>
                    </div>

                    <!-- Customized Edit Input -->
                    <div class="lg:col-span-8">
                        <form method="POST" action="{{ route('admin.content-manager.update', $content->id) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <div class="flex-1">
                                    <input type="text" name="custom_value" value="{{ old('custom_value', $content->custom_value ?? $content->published_value ?? $content->default_value) }}"
                                           placeholder="Enter customized wording..."
                                           class="w-full text-xs font-medium rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 {{ $isPending ? 'bg-amber-50/40 border-amber-300' : 'bg-white' }} py-2 px-3">
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                        Save Draft
                                    </button>
                        </form>

                        @if($isPending)
                            <form method="POST" action="{{ route('admin.content-manager.publish', $content->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Publish</span>
                                </button>
                            </form>
                        @endif

                        @if($isCustomized)
                            <form method="POST" action="{{ route('admin.content-manager.reset', $content->id) }}" 
                                  onsubmit="return confirm('Reset \'{{ $content->key }}\' back to system default?');" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg text-xs font-semibold transition-all border border-transparent hover:border-rose-200">
                                    Reset
                                </button>
                            </form>
                        @endif
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white p-12 text-center rounded-xl border border-slate-200 shadow-sm">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-sm font-bold text-slate-800">No content entries match your filter</h3>
                <p class="text-xs text-slate-500 mt-1">Try clearing your search query or selecting a different category.</p>
                <a href="{{ route('admin.content-manager.index', ['company_id' => $selectedCompanyId ?? 'global']) }}" 
                   class="inline-block mt-3 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold">
                    Clear Filters
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $contents->links() }}
    </div>

    <!-- Audit History Modal -->
    <div x-show="historyOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="historyOpen = false"></div>

            <div class="inline-block w-full max-w-xl p-6 my-8 text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Content Audit History</h3>
                        <p class="text-xs text-slate-500" x-text="historyData ? historyData.key : 'Loading...'"></p>
                    </div>
                    <button type="button" @click="historyOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    <template x-if="historyLoading">
                        <div class="py-12 text-center text-xs text-slate-400">Loading audit history...</div>
                    </template>

                    <template x-if="!historyLoading && historyData">
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                            <template x-for="item in historyData.histories" :key="item.id">
                                <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50 space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-bold uppercase tracking-wider" 
                                              :class="{
                                                  'text-amber-600': item.action === 'draft_saved',
                                                  'text-emerald-700': item.action === 'published',
                                                  'text-rose-600': item.action === 'reset_to_default'
                                              }"
                                              x-text="item.action.replace('_', ' ')">
                                        </span>
                                        <span class="text-slate-400" x-text="new Date(item.created_at).toLocaleString()"></span>
                                    </div>
                                    <div class="text-xs text-slate-700">
                                        <span class="text-slate-400 font-normal">New Value: </span>
                                        <span class="font-semibold" x-text="item.new_value || '(None)'"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500" x-show="item.old_value">
                                        <span class="text-slate-400">Previous: </span>
                                        <span class="line-through text-slate-400" x-text="item.old_value"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 pt-1" x-show="item.user">
                                        Modified by <strong class="text-slate-600" x-text="item.user ? item.user.name : 'System'"></strong>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!historyData.histories || historyData.histories.length === 0">
                                <div class="py-8 text-center text-xs text-slate-400">No modification history recorded yet for this key.</div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
