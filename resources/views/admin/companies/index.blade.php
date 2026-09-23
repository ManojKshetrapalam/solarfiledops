@extends('layouts.admin')

@section('title', 'Company & Entity Management - SolarOps')
@section('header_title', 'Companies & Entities')

@section('admin_content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Registered Corporate Entities</h2>
            <p class="text-xs text-slate-500">Manage multiple legal entities (e.g. Sun on Earth, Sabha) for isolated operations and reporting.</p>
        </div>
        <a href="{{ route('admin.companies.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-xs transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add New Company</span>
        </a>
    </div>

    <!-- Companies Grid / Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Entity</th>
                        <th class="py-3.5 px-4">Contact Info</th>
                        <th class="py-3.5 px-4">Employees</th>
                        <th class="py-3.5 px-4">Customers</th>
                        <th class="py-3.5 px-4">Total Services</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($companies as $company)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    @if($company->logo_path)
                                        <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="w-9 h-9 rounded-lg object-contain bg-slate-50 border border-slate-200 p-0.5">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-slate-900 text-amber-400 font-black flex items-center justify-center text-xs">
                                            {{ $company->code }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-bold text-slate-900 block text-sm">{{ $company->name }}</span>
                                        <span class="text-slate-400 text-[11px]">Code: <span class="font-mono font-semibold text-slate-600">{{ $company->code }}</span></span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-medium text-slate-800">{{ $company->contact_person ?? '—' }}</p>
                                <p class="text-slate-500">{{ $company->phone ?? '—' }}</p>
                                <p class="text-slate-400 text-[11px]">{{ $company->email ?? '' }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800">{{ $company->employees_count }}</span> staff
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800">{{ $company->customers_count }}</span> clients
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800">{{ $company->services_count }}</span> jobs
                                <span class="text-[11px] text-slate-400 block">({{ $company->reports_count }} reports)</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($company->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.companies.edit', $company->id) }}" 
                                       class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.companies.toggle-status', $company->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-1 text-[11px] font-medium rounded-lg {{ $company->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                            {{ $company->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No company entities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
