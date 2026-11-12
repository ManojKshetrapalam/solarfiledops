<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FrontendContent;
use App\Services\FrontendContentService;
use Illuminate\Http\Request;

class ContentManagerController extends Controller
{
    protected function authorizeAccess(?int $companyId = null): void
    {
        $user = auth()->user();
        abort_unless($user && $user->canManageContent(), 403, 'Unauthorized. CMS access requires administrative permissions.');

        if ($companyId !== null && !$user->isSuperAdmin()) {
            abort_unless($user->canAccessCompany($companyId), 403, 'Unauthorized to manage content for this company.');
        }

        if ($companyId === null && !$user->isSuperAdmin()) {
            // Non-super-admins cannot alter global system defaults
            abort(403, 'Only Super Administrators can manage global system-wide wording defaults.');
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->canManageContent(), 403, 'Unauthorized CMS access.');

        // Company scope selection
        $availableCompanies = $user->isSuperAdmin() ? Company::all() : $user->companies;
        $selectedCompanyId = $request->input('company_id');

        if ($selectedCompanyId !== null && $selectedCompanyId !== '' && $selectedCompanyId !== 'global') {
            $selectedCompanyId = (int) $selectedCompanyId;
            if (!$user->isSuperAdmin() && !$user->canAccessCompany($selectedCompanyId)) {
                abort(403, 'Unauthorized company selection.');
            }
        } else {
            // Default to first company if not super admin, otherwise 'global' (null)
            if (!$user->isSuperAdmin()) {
                $selectedCompanyId = $availableCompanies->first()?->id;
            } else {
                $selectedCompanyId = ($request->input('company_id') === 'global' || !$request->has('company_id')) ? null : (int) $selectedCompanyId;
            }
        }

        $query = FrontendContent::query();

        if ($selectedCompanyId === null) {
            $query->whereNull('company_id');
        } else {
            $query->where('company_id', $selectedCompanyId);
        }

        // Category filter
        $activeCategory = $request->input('category');
        if ($activeCategory && array_key_exists($activeCategory, FrontendContentService::CATEGORIES)) {
            $query->where('category', $activeCategory);
        }

        // Status filter
        $statusFilter = $request->input('status', 'all');
        if ($statusFilter === 'pending_draft') {
            $query->whereNotNull('custom_value')->whereRaw('(custom_value != published_value OR published_value IS NULL)');
        } elseif ($statusFilter === 'customized') {
            $query->where(function ($q) {
                $q->whereNotNull('published_value')->whereRaw('published_value != default_value')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('custom_value')->whereRaw('custom_value != default_value');
                  });
            });
        } elseif ($statusFilter === 'default') {
            $query->whereNull('custom_value')->where(function ($q) {
                $q->whereNull('published_value')->orWhereRaw('published_value = default_value');
            });
        }

        // Search query
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('default_value', 'like', "%{$search}%")
                  ->orWhere('custom_value', 'like', "%{$search}%")
                  ->orWhere('published_value', 'like', "%{$search}%");
            });
        }

        $contents = $query->orderBy('category')->orderBy('key')->paginate(25)->withQueryString();

        // Counters for the current company scope
        $scopeBase = FrontendContent::query();
        if ($selectedCompanyId === null) {
            $scopeBase->whereNull('company_id');
        } else {
            $scopeBase->where('company_id', $selectedCompanyId);
        }

        $totalCount = (clone $scopeBase)->count();
        $pendingCount = (clone $scopeBase)->whereNotNull('custom_value')->whereRaw('(custom_value != published_value OR published_value IS NULL)')->count();
        $customizedCount = (clone $scopeBase)->where(function ($q) {
            $q->whereNotNull('published_value')->whereRaw('published_value != default_value')
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('custom_value')->whereRaw('custom_value != default_value');
              });
        })->count();

        $previewActive = (bool) session('frontend_content_preview_mode', false);

        return view('admin.content_manager.index', [
            'contents' => $contents,
            'categories' => FrontendContentService::CATEGORIES,
            'activeCategory' => $activeCategory,
            'statusFilter' => $statusFilter,
            'availableCompanies' => $availableCompanies,
            'selectedCompanyId' => $selectedCompanyId,
            'totalCount' => $totalCount,
            'pendingCount' => $pendingCount,
            'customizedCount' => $customizedCount,
            'previewActive' => $previewActive,
        ]);
    }

    public function update(Request $request, FrontendContent $content)
    {
        $this->authorizeAccess($content->company_id);

        $request->validate([
            'custom_value' => 'required|string|max:3000',
        ]);

        // Security sanitization: strip script tags to prevent stored XSS
        $cleanValue = strip_tags($request->input('custom_value'), '<b><strong><i><em><u><a><span><br>');

        FrontendContentService::saveDraft($content->id, $cleanValue, auth()->id());

        return back()->with('success', "Draft saved for key '{$content->key}'. Click Publish or toggle Preview to verify.");
    }

    public function publish(Request $request, FrontendContent $content)
    {
        $this->authorizeAccess($content->company_id);

        FrontendContentService::publish($content->id, auth()->id());

        return back()->with('success', "Published '{$content->key}' live! Changes are now active for users.");
    }

    public function publishAll(Request $request)
    {
        $companyId = $request->input('company_id');
        if ($companyId === 'global' || $companyId === '' || $companyId === null) {
            $companyId = null;
        } else {
            $companyId = (int) $companyId;
        }

        $this->authorizeAccess($companyId);

        $count = FrontendContentService::publishAll($companyId, auth()->id());

        $scopeName = $companyId ? 'selected company' : 'global application';
        return back()->with('success', "Successfully published {$count} pending draft changes for {$scopeName}!");
    }

    public function reset(Request $request, FrontendContent $content)
    {
        $this->authorizeAccess($content->company_id);

        FrontendContentService::resetToDefault($content->id, auth()->id());

        return back()->with('success', "Reset '{$content->key}' to standard system default.");
    }

    public function history(FrontendContent $content)
    {
        $this->authorizeAccess($content->company_id);

        $histories = $content->histories()->with('user')->get();

        return response()->json([
            'key' => $content->key,
            'category' => $content->category,
            'default_value' => $content->default_value,
            'published_value' => $content->published_value,
            'custom_value' => $content->custom_value,
            'histories' => $histories,
        ]);
    }

    public function togglePreview(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->canManageContent(), 403);

        $current = (bool) session('frontend_content_preview_mode', false);
        session(['frontend_content_preview_mode' => !$current]);

        $status = !$current ? 'enabled' : 'disabled';
        return back()->with('success', "Live draft preview mode {$status}.");
    }

    public function createCompanyOverride(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'company_id' => 'required|integer|exists:companies,id',
        ]);

        $companyId = (int) $request->input('company_id');
        $this->authorizeAccess($companyId);

        $key = $request->input('key');

        // Check if override already exists
        $existing = FrontendContent::where('key', $key)->where('company_id', $companyId)->first();
        if ($existing) {
            return back()->with('info', "An override for '{$key}' already exists for this company.");
        }

        // Get global template
        $global = FrontendContent::where('key', $key)->whereNull('company_id')->firstOrFail();

        $override = FrontendContent::create([
            'key' => $global->key,
            'category' => $global->category,
            'default_value' => $global->default_value,
            'custom_value' => $global->published_value ?? $global->default_value,
            'published_value' => null,
            'company_id' => $companyId,
            'last_modified_by' => auth()->id(),
        ]);

        return back()->with('success', "Created company override for '{$key}'. You can now customize it independently.");
    }
}
