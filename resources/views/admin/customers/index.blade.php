@extends('layouts.admin')

@section('title', 'Customer Management - SolarOps')
@section('header_title', 'Customers')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Commercial & Industrial Clients</h2>
            <p class="text-xs text-slate-500">Solar installations are linked to customers, and each customer can have multiple physical plant sites.</p>
        </div>
        <a href="{{ route('admin.customers.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add New Customer</span>
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Search Customer</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, contact person, phone..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Company / Entity</label>
                <select name="company_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">All Companies</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800 transition-colors w-full">Filter</button>
                <a href="{{ route('admin.customers.index') }}" class="px-3 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-center">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Customer Name</th>
                        <th class="py-3.5 px-4">Company Entity</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Sites</th>
                        <th class="py-3.5 px-4">Services</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customers as $cust)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.customers.show', $cust->id) }}" class="font-bold text-slate-900 hover:text-amber-600 text-sm block">
                                    {{ $cust->name }}
                                </a>
                                <span class="text-slate-400 text-[11px] truncate max-w-xs block">{{ $cust->address ?? 'No address entered' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $cust->company->name }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-medium text-slate-800">{{ $cust->contact_person ?? '—' }}</p>
                                <p class="text-slate-500">{{ $cust->phone }}</p>
                                <p class="text-slate-400 text-[11px]">{{ $cust->email ?? '' }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-indigo-700 text-sm">{{ $cust->sites_count }}</span> sites
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 text-sm">{{ $cust->services_count }}</span> jobs
                            </td>
                            <td class="py-3 px-4">
                                @if($cust->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.customers.show', $cust->id) }}" 
                                       class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors">
                                        View
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $cust->id) }}" 
                                       class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
