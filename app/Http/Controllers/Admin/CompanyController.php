<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::withCount(['employees', 'customers', 'services', 'reports'])
            ->latest()
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:companies,code'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'report_header_info' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('companies/logos', 'public');
            $validated['logo_path'] = $path;
        }
        unset($validated['logo']);

        $company = Company::create($validated);

        AuditLog::log($company, 'created', "Company '{$company->name}' was created", null, $company->toArray());

        return redirect()->route('admin.companies.index')->with('success', "Company '{$company->name}' created successfully.");
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('companies', 'code')->ignore($company->id)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'report_header_info' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('companies/logos', 'public');
        }
        unset($validated['logo']);
        $validated['is_active'] = $request->boolean('is_active');

        $old = $company->toArray();
        $company->update($validated);

        AuditLog::log($company, 'updated', "Company '{$company->name}' updated", $old, $company->toArray());

        return redirect()->route('admin.companies.index')->with('success', "Company '{$company->name}' updated successfully.");
    }

    public function toggleStatus(Company $company): RedirectResponse
    {
        $company->update(['is_active' => !$company->is_active]);

        $statusStr = $company->is_active ? 'activated' : 'deactivated';
        AuditLog::log($company, 'status_changed', "Company '{$company->name}' {$statusStr}");

        return back()->with('success', "Company '{$company->name}' {$statusStr}.");
    }
}
