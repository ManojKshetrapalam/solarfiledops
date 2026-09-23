@extends('layouts.admin')

@section('title', 'Edit Employee - SolarOps')
@section('header_title', 'Edit Employee Profile')

@section('admin_content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Main Edit Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Edit Profile: {{ $employee->name }}</h2>
                <p class="text-xs text-slate-500">Update employee details, designation, and company assignment.</p>
            </div>
            @if($employee->profile_photo_path)
                <img src="{{ asset('storage/' . $employee->profile_photo_path) }}" alt="{{ $employee->name }}" class="w-12 h-12 rounded-full object-cover border p-0.5">
            @endif
        </div>

        <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Employee ID / Code *</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none uppercase font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address (Login) *</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Designation *</label>
                    <input type="text" name="designation" value="{{ old('designation', $employee->designation) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Assigned Company / Entity</label>
                    <select name="company_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="">-- Unassigned / All Entities --</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('company_id', $employee->company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Role *</label>
                    <select name="role" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="engineer" {{ old('role', $employee->role) == 'engineer' ? 'selected' : '' }}>Field Engineer</option>
                        <option value="admin" {{ old('role', $employee->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Status *</label>
                    <select name="status" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Change Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.employees.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold text-xs rounded-lg hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-xs">
                    Update Employee
                </button>
            </div>
        </form>
    </div>

    <!-- Password Reset Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 mb-1">Reset Password</h3>
        <p class="text-xs text-slate-500 mb-4">Set a new password for {{ $employee->name }}.</p>

        <form action="{{ route('admin.employees.reset-password', $employee->id) }}" method="POST" class="flex items-center gap-3">
            @csrf
            <input type="password" name="new_password" required minlength="6" placeholder="Enter new password (min 6 chars)"
                   class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-lg shadow-xs transition-colors shrink-0">
                Reset Password
            </button>
        </form>
    </div>
</div>
@endsection
