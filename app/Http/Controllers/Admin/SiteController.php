<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Site::with(['customer.company'])
            ->withCount(['services', 'reports']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%");
            });
        }

        $sites = $query->latest()->paginate(15)->withQueryString();
        $customers = Customer::where('status', 'active')->get();

        return view('admin.sites.index', compact('sites', 'customers'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::where('status', 'active')->with('company')->get();
        $selectedCustomerId = $request->query('customer_id');

        return view('admin.sites.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location_notes' => ['nullable', 'string'],
        ]);

        $site = Site::create($validated);

        AuditLog::log($site, 'created', "Plant Site '{$site->name}' created for Customer '{$site->customer->name}'", null, $site->toArray());

        return redirect()->route('admin.customers.show', $site->customer_id)
            ->with('success', "Site '{$site->name}' created successfully.");
    }

    public function edit(Site $site): View
    {
        $customers = Customer::where('status', 'active')->with('company')->get();
        return view('admin.sites.edit', compact('site', 'customers'));
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location_notes' => ['nullable', 'string'],
        ]);

        $old = $site->toArray();
        $site->update($validated);

        AuditLog::log($site, 'updated', "Plant Site '{$site->name}' updated", $old, $site->toArray());

        return redirect()->route('admin.customers.show', $site->customer_id)
            ->with('success', "Site '{$site->name}' updated successfully.");
    }
}
