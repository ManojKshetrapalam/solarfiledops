@extends('layouts.admin')

@section('title', 'Create Service Job - SolarOps')
@section('header_title', 'Create Service Job')

@section('admin_content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6"
         x-data="{
            allCustomers: {{ Js::from($customers) }},
            selectedCompanyId: '{{ old('company_id', $selectedCompanyId ?? '') }}',
            selectedCustomerId: '{{ old('customer_id', $selectedCustomerId ?? '') }}',
            selectedSiteId: '{{ old('site_id', $selectedSiteId ?? '') }}',
            
            get filteredCustomers() {
                if (!this.selectedCompanyId) return [];
                return this.allCustomers.filter(c => c.company_id == this.selectedCompanyId);
            },
            
            get filteredSites() {
                if (!this.selectedCustomerId) return [];
                const cust = this.allCustomers.find(c => c.id == this.selectedCustomerId);
                return cust ? cust.sites : [];
            },

            onCompanyChange() {
                this.selectedCustomerId = '';
                this.selectedSiteId = '';
            },

            onCustomerChange() {
                this.selectedSiteId = '';
            }
         }">

        <div class="border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-base font-bold text-slate-900">New Solar Service Job Dispatch</h2>
            <p class="text-xs text-slate-500">Assign routine maintenance, breakdown calls, or inspection services to field engineers.</p>
        </div>

        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Entity & Hierarchy -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <!-- 1. Select Company -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">1. Company Entity *</label>
                    <select name="company_id" required x-model="selectedCompanyId" @change="onCompanyChange()"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                        <option value="">-- Select Entity --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Select Customer -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">2. Customer *</label>
                    <select name="customer_id" required x-model="selectedCustomerId" @change="onCustomerChange()" :disabled="!selectedCompanyId"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white disabled:opacity-50">
                        <option value="">-- Select Customer --</option>
                        <template x-for="cust in filteredCustomers" :key="cust.id">
                            <option :value="cust.id" x-text="cust.name" :selected="cust.id == selectedCustomerId"></option>
                        </template>
                    </select>
                </div>

                <!-- 3. Select Site -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">3. Plant Site *</label>
                    <select name="site_id" required x-model="selectedSiteId" :disabled="!selectedCustomerId"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white disabled:opacity-50">
                        <option value="">-- Select Site --</option>
                        <template x-for="site in filteredSites" :key="site.id">
                            <option :value="site.id" x-text="site.name" :selected="site.id == selectedSiteId"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Job Specifics -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Service Type *</label>
                    <select name="service_type_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @foreach($serviceTypes as $st)
                            <option value="{{ $st->id }}" {{ old('service_type_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Priority *</label>
                    <select name="priority" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent / Emergency</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low Priority</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Scheduled Date *</label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <!-- Assignment to Employee -->
            <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1">
                    Assign Field Engineer / Employee
                </label>
                <p class="text-xs text-slate-600 mb-2">The selected employee will receive an instant in-app assignment notification.</p>
                <select name="assigned_user_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                    <option value="">-- Leave Unassigned (Assign Later) --</option>
                    @foreach($engineers as $eng)
                        <option value="{{ $eng->id }}" {{ old('assigned_user_id') == $eng->id ? 'selected' : '' }}>
                            {{ $eng->name }} ({{ $eng->employee_code }}) - {{ $eng->company?->name ?? 'All Entities' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Job Description & Field Instructions</label>
                <textarea name="description" rows="3" placeholder="e.g. Solar plant inspection and maintenance. Check modules, inverter readings, structure stability and battery condition."
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.services.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all">
                    Create & Dispatch Service
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
