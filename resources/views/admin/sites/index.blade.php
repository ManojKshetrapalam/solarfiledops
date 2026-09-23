@extends('layouts.admin')

@section('title', 'Plant Sites Management - SolarOps')
@section('header_title', 'Solar Plant Sites')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Solar Plant Sites & Locations</h2>
            <p class="text-xs text-slate-500">Each physical roof or ground mounted installation site belongs to a customer.</p>
        </div>
        <a href="{{ route('admin.sites.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add New Site</span>
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.sites.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Search Site</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Site name, address, contact..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Customer</label>
                <select name="customer_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-colors w-full">Filter</button>
                <a href="{{ route('admin.sites.index') }}" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Site Name</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Entity</th>
                        <th class="py-3.5 px-4">Site Incharge</th>
                        <th class="py-3.5 px-4">Total Jobs</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sites as $site)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $site->name }}</span>
                                <span class="text-slate-400 text-[11px] block">{{ $site->address }}</span>
                                @if($site->location_notes)
                                    <span class="text-amber-700 text-[10px] block mt-0.5">{{ $site->location_notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.customers.show', $site->customer_id) }}" class="font-bold text-slate-800 hover:text-amber-600">
                                    {{ $site->customer->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $site->customer->company->name }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-medium text-slate-800">{{ $site->contact_person ?? '—' }}</p>
                                <p class="text-slate-400 text-[11px]">{{ $site->phone ?? '' }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800">{{ $site->services_count }}</span> jobs
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.services.create', ['customer_id' => $site->customer_id, 'site_id' => $site->id]) }}" 
                                       class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold text-xs transition-colors">
                                        Create Job
                                    </a>
                                    <a href="{{ route('admin.sites.edit', $site->id) }}" 
                                       class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No plant sites found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sites->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sites->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
