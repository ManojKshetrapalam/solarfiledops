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

        if ($request->filled('login_status')) {
            if ($request->login_status === 'pending_first_login') {
                $query->where('password_change_required', true)->where('first_login_completed', false);
            } elseif ($request->login_status === 'active') {
                $query->where('password_change_required', false)->where('status', 'active');
            } elseif ($request->login_status === 'inactive') {
                $query->where('status', 'inactive');
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('employee_code', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%")
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
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
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

        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($plainPassword);
        $validated['status'] = 'active';

        if (empty($validated['username'])) {
            $validated['username'] = User::generateUniqueUsername($validated['name']);
        }

        $employee = new User($validated);
        if ($request->boolean('require_password_change', false)) {
            $employee->setTemporaryPassword($plainPassword, 7);
            $employee->password_change_required = true;
            $employee->first_login_completed = false;
        } else {
            $employee->password_change_required = false;
            $employee->first_login_completed = true;
        }
        $employee->save();

        AuditLog::log($employee, 'created', "Employee '{$employee->name}' ({$employee->employee_code}) was created by Admin with temporary credentials");

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee '{$employee->name}' created successfully.")
            ->with('revealed_temp_password', $plainPassword)
            ->with('revealed_username', $employee->username)
            ->with('revealed_employee_id', $employee->id);
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

    public function regenerateTemporaryPassword(User $employee): RedirectResponse
    {
        $firstName = strtolower(explode(' ', trim($employee->name))[0] ?? 'solar');
        $firstName = preg_replace('/[^a-z0-9]/', '', $firstName) ?: 'solar';
        $tempPassword = $firstName . '123';

        $employee->setTemporaryPassword($tempPassword, 7);
        $employee->password = Hash::make($tempPassword);
        $employee->password_change_required = true;
        if (empty($employee->username)) {
            $employee->username = User::generateUniqueUsername($employee->name);
        }
        $employee->save();

        AuditLog::log($employee, 'EMPLOYEE_CREDENTIAL_REGENERATED', "Admin regenerated temporary onboarding password for {$employee->name} ({$employee->employee_code})");

        return back()
            ->with('success', "Temporary onboarding password regenerated for {$employee->name}: {$tempPassword}")
            ->with('revealed_temp_password', $tempPassword)
            ->with('revealed_employee_id', $employee->id);
    }

    public function toggleStatus(User $employee): RedirectResponse
    {
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);

        AuditLog::log($employee, 'status_changed', "Employee '{$employee->name}' marked as {$newStatus}");

        return back()->with('success', "Employee {$employee->name} is now {$newStatus}.");
    }
}
