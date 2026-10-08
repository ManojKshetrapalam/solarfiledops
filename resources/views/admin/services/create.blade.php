@extends('layouts.admin')

@section('title', 'Create Service Job - SolarOps')
@section('header_title', 'Create Service Job')

@section('admin_content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6"
         x-data="{
            dispatchMode: '{{ old('dispatch_mode', 'single') }}',
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">New Solar Service Job Dispatch</h2>
                    <p class="text-xs text-slate-500">Dispatch individual jobs or multi-technician installation projects to field engineers.</p>
                </div>

                <!-- Mode Switcher -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-semibold">
                    <button type="button" @click="dispatchMode = 'single'"
                            :class="dispatchMode === 'single' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all">
                        Single Job
                    </button>
                    <button type="button" @click="dispatchMode = 'installation_bundle'"
                            :class="dispatchMode === 'installation_bundle' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-600"></span>
                        3-Part Installation (3 Techs)
                    </button>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="dispatch_mode" :value="dispatchMode">

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

            <!-- Single Job Service Type & Specifics -->
            <div x-show="dispatchMode === 'single'" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Service Type *</label>
                        <select name="service_type_id" :required="dispatchMode === 'single'" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
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

                <!-- Assignment to Employee (Single Job) -->
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
            </div>

            <!-- 3-Part Installation Dispatch Mode -->
            <div x-show="dispatchMode === 'installation_bundle'" class="space-y-4" x-cloak>
                <div class="bg-amber-50 p-4 rounded-xl border border-amber-300 text-xs text-slate-800">
                    <div class="flex items-center gap-2 font-bold text-amber-900 mb-1">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Multi-Technician Installation Project</span>
                    </div>
                    <p class="text-slate-600">
                        This dispatches <strong>3 separate installation jobs</strong> for this site simultaneously. Each technician will receive their own dedicated mobile report for their specialized domain.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Priority *</label>
                        <select name="priority" :required="dispatchMode === 'installation_bundle'" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Installation Target Date *</label>
                        <input type="date" name="scheduled_date" value="{{ old('scheduled_date', date('Y-m-d')) }}" :required="dispatchMode === 'installation_bundle'"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- 3 Technician Selectors -->
                <div class="space-y-3 pt-2">
                    <!-- Part 1: Structure & Mounting -->
                    <div class="bg-blue-50/70 p-4 rounded-xl border border-blue-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-900">1. Structure & Module Mounting Technician *</span>
                            <span class="text-[10px] font-semibold bg-blue-200 text-blue-800 px-2 py-0.5 rounded-full">Part 1</span>
                        </div>
                        <p class="text-[11px] text-blue-700 mb-2">Responsible for: Modules delivered, row/string mounting, wind safety fixing, and washing arrangements.</p>
                        <select name="structure_engineer_id" :required="dispatchMode === 'installation_bundle'"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="">-- Assign Structure Technician --</option>
                            @foreach($engineers as $eng)
                                <option value="{{ $eng->id }}" {{ old('structure_engineer_id') == $eng->id ? 'selected' : '' }}>
                                    {{ $eng->name }} ({{ $eng->employee_code }}) - {{ $eng->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Part 2: Electrical & Cabling -->
                    <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-900">2. Electrical & Cabling Technician *</span>
                            <span class="text-[10px] font-semibold bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full">Part 2</span>
                        </div>
                        <p class="text-[11px] text-amber-700 mb-2">Responsible for: Earthing pits, string wiring, AJB configuration, DCDB, ACDB, and cable routing.</p>
                        <select name="electrical_engineer_id" :required="dispatchMode === 'installation_bundle'"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none bg-white">
                            <option value="">-- Assign Electrical Technician --</option>
                            @foreach($engineers as $eng)
                                <option value="{{ $eng->id }}" {{ old('electrical_engineer_id') == $eng->id ? 'selected' : '' }}>
                                    {{ $eng->name }} ({{ $eng->employee_code }}) - {{ $eng->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Part 3: Inverter & Commissioning -->
                    <div class="bg-emerald-50/70 p-4 rounded-xl border border-emerald-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">3. Inverter (PCU) & Commissioning Technician *</span>
                            <span class="text-[10px] font-semibold bg-emerald-200 text-emerald-800 px-2 py-0.5 rounded-full">Part 3</span>
                        </div>
                        <p class="text-[11px] text-emerald-700 mb-2">Responsible for: Inverter/PCU electrical parameters, battery bank/stands, synchronized power-on, and plant handover.</p>
                        <select name="commissioning_engineer_id" :required="dispatchMode === 'installation_bundle'"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                            <option value="">-- Assign Commissioning Technician --</option>
                            @foreach($engineers as $eng)
                                <option value="{{ $eng->id }}" {{ old('commissioning_engineer_id') == $eng->id ? 'selected' : '' }}>
                                    {{ $eng->name }} ({{ $eng->employee_code }}) - {{ $eng->designation }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Job Description & Field Instructions</label>
                <textarea name="description" rows="3" placeholder="e.g. Solar installation / maintenance instructions for field team."
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.services.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-1.5">
                    <span x-text="dispatchMode === 'installation_bundle' ? 'Dispatch 3 Installation Jobs' : 'Create & Dispatch Service'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
