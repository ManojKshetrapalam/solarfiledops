<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::with('company')
            ->withCount(['sites', 'services', 'reports']);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $companies = Company::where('is_active', true)->get();

        return view('admin.customers.index', compact('customers', 'companies'));
    }

    public function create(): View
    {
        $companies = Company::where('is_active', true)->get();
        return view('admin.customers.create', compact('companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'active';
        $customer = Customer::create($validated);

        AuditLog::log($customer, 'created', "Customer '{$customer->name}' created by Admin", null, $customer->toArray());

        return redirect()->route('admin.customers.index')->with('success', "Customer '{$customer->name}' created successfully.");
    }

    public function show(Customer $customer): View
    {
        $customer->load(['company', 'sites', 'services.assignedEngineer', 'services.site']);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        $companies = Company::where('is_active', true)->get();
        return view('admin.customers.edit', compact('customer', 'companies'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $old = $customer->toArray();
        $customer->update($validated);

        AuditLog::log($customer, 'updated', "Customer '{$customer->name}' updated", $old, $customer->toArray());

        return redirect()->route('admin.customers.index')->with('success', "Customer '{$customer->name}' updated successfully.");
    }
}
