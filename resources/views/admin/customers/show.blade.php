@extends('layouts.admin')

@section('title', $customer->name . ' - Customer Details - SolarOps')
@section('header_title', 'Customer Details')

@section('admin_content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-extrabold text-slate-900">{{ $customer->name }}</h2>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                        {{ $customer->company->name }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $customer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($customer->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $customer->address ?? 'No address provided' }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sites.create', ['customer_id' => $customer->id]) }}" 
                   class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add New Site</span>
                </a>
                <a href="{{ route('admin.customers.edit', $customer->id) }}" 
                   class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-xl hover:bg-slate-50 transition-all">
                    Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Contact Person</span>
                <span class="font-bold text-slate-800">{{ $customer->contact_person ?? '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Phone</span>
                <span class="font-bold text-slate-800">{{ $customer->phone }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-semibold uppercase text-[10px]">Email</span>
                <span class="font-bold text-slate-800">{{ $customer->email ?? '—' }}</span>
            </div>
        </div>
    </div>

    <!-- Sites List -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Physical Plant Sites ({{ $customer->sites->count() }})</h3>
            <a href="{{ route('admin.sites.create', ['customer_id' => $customer->id]) }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">+ Add Site</a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($customer->sites as $site)
                <div class="p-4 hover:bg-slate-50/60 transition-colors flex items-center justify-between gap-4 text-xs">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-sm text-slate-900">{{ $site->name }}</span>
                            <span class="text-[11px] text-slate-400">Contact: {{ $site->contact_person ?? '—' }} ({{ $site->phone ?? '—' }})</span>
                        </div>
                        <p class="text-slate-600 font-medium">{{ $site->address }}</p>
                        @if($site->location_notes)
                            <p class="text-slate-400 text-[11px] mt-0.5">Notes: {{ $site->location_notes }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.services.create', ['customer_id' => $customer->id, 'site_id' => $site->id]) }}" 
                           class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold text-xs transition-colors">
                            Create Job
                        </a>
                        <a href="{{ route('admin.sites.edit', $site->id) }}" 
                           class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold text-xs transition-colors">
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400">
                    No physical plant sites added for this customer yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
