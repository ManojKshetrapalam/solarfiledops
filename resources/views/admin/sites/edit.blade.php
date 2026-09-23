@extends('layouts.admin')

@section('title', 'Edit Plant Site - SolarOps')
@section('header_title', 'Edit Plant Site')

@section('admin_content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-base font-bold text-slate-900">Edit Site: {{ $site->name }}</h2>
            <p class="text-xs text-slate-500">Update customer association, location details, and notes.</p>
        </div>

        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Customer / Organization *</label>
                <select name="customer_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id', $site->customer_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->company->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Site / Plant Name *</label>
                <input type="text" name="name" value="{{ old('name', $site->name) }}" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Site Incharge / Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $site->contact_person) }}"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $site->phone) }}"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Site Location & Physical Address *</label>
                <textarea name="address" rows="3" required
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('address', $site->address) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Site Technical Notes / Access Instructions</label>
                <textarea name="location_notes" rows="2"
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('location_notes', $site->location_notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-xs">
                    Update Site
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
