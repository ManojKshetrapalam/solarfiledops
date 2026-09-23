<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('company')
            ->withCount([
                'assignedServices as active_services_count' => fn($q) => $q->whereIn('status', ['assigned', 'in_progress', 'correction_required']),
                'assignedServices as completed_services_count' => fn($q) => $q->where('status', 'completed'),
                'reports as submitted_reports_count' => fn($q) => $q->whereIn('status', ['submitted', 'resubmitted', 'approved']),
            ]);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            $query->where('role', 'engineer');
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('employee_code', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $companies = Company::where('is_active', true)->get();

        return view('admin.employees.index', compact('employees', 'companies'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->get();
        return view('admin.employees.create', compact('companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'employee_code' => ['required', 'string', 'max:50', 'unique:users,employee_code'],
            'phone' => ['required', 'string', 'max:50'],
            'designation' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'role' => ['required', 'in:admin,engineer'],
            'joining_date' => ['nullable', 'date'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo_path'] = $request->file('profile_photo')->store('employees/photos', 'public');
        }
        unset($validated['profile_photo']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        $employee = User::create($validated);

        AuditLog::log($employee, 'created', "Employee '{$employee->name}' ({$employee->employee_code}) was created by Admin");

        return redirect()->route('admin.employees.index')->with('success', "Employee '{$employee->name}' created successfully.");
    }

    public function show(User $employee): View
    {
        $employee->load([
            'company',
            'assignedServices.company',
            'assignedServices.customer',
            'assignedServices.site',
            'reports.service',
        ]);

        return view('admin.employees.show', compact('employee'));
    }

    public function edit(User $employee): View
    {
        $companies = Company::where('is_active', true)->get();
        return view('admin.employees.edit', compact('employee', 'companies'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->id)],
            'employee_code' => ['required', 'string', 'max:50', Rule::unique('users', 'employee_code')->ignore($employee->id)],
            'phone' => ['required', 'string', 'max:50'],
            'designation' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'role' => ['required', 'in:admin,engineer'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo_path && Storage::disk('public')->exists($employee->profile_photo_path)) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('profile_photo')->store('employees/photos', 'public');
        }
        unset($validated['profile_photo']);

        $old = $employee->toArray();
        $employee->update($validated);

        AuditLog::log($employee, 'updated', "Employee '{$employee->name}' updated", $old, $employee->toArray());

        return redirect()->route('admin.employees.index')->with('success', "Employee '{$employee->name}' updated successfully.");
    }

    public function resetPassword(Request $request, User $employee): RedirectResponse
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $employee->update([
            'password' => Hash::make($request->new_password),
        ]);

        AuditLog::log($employee, 'password_reset', "Admin reset password for {$employee->name}");

        return back()->with('success', "Password for {$employee->name} has been reset successfully.");
    }

    public function toggleStatus(User $employee): RedirectResponse
    {
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);

        AuditLog::log($employee, 'status_changed', "Employee '{$employee->name}' marked as {$newStatus}");

        return back()->with('success', "Employee {$employee->name} is now {$newStatus}.");
    }
}
