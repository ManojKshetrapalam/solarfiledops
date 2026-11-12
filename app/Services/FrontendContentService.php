<?php

namespace App\Services;

use App\Models\FrontendContent;
use App\Models\FrontendContentHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FrontendContentService
{
    public const CATEGORIES = [
        'branding' => 'Application Branding',
        'dashboard' => 'Dashboard',
        'navigation' => 'Navigation and Menus',
        'modules' => 'Module Names',
        'headings' => 'Page Headings',
        'form_labels' => 'Form Labels',
        'form_placeholders' => 'Form Placeholders',
        'buttons' => 'Buttons and Actions',
        'notifications' => 'Notifications and Messages',
        'reports_print' => 'PDF and Printable Reports',
        'auth_screens' => 'Login and Authentication Screens',
        'mobile_labels' => 'Mobile Interface Labels',
    ];

    /**
     * Resolve frontend content with multi-tier fallback:
     * 1. Company Published (or Draft in preview)
     * 2. Global Published (or Draft in preview)
     * 3. Database Default
     * 4. Hardcoded Code Fallback
     */
    public static function get(string $key, ?string $fallback = null, ?int $companyId = null): string
    {
        // 1. Resolve effective company context if not explicitly provided
        if ($companyId === null && auth()->check()) {
            $user = auth()->user();
            $companyId = $user->company_id ?? $user->primary_company?->id;
        }

        // 2. Determine if preview mode is active for this session
        $isPreview = false;
        if (auth()->check() && auth()->user()->canManageContent() && session('frontend_content_preview_mode', false)) {
            $isPreview = true;
        }

        // 3. Retrieve dictionary (draft-merged in preview, strictly published otherwise)
        $dict = self::getDictionary($companyId, $isPreview);

        if (array_key_exists($key, $dict) && $dict[$key] !== null && $dict[$key] !== '') {
            return (string) $dict[$key];
        }

        return $fallback ?? $key;
    }

    /**
     * Get compiled dictionary for the specified company scope.
     * Always merges global defaults with company-specific overrides.
     */
    public static function getDictionary(?int $companyId = null, bool $allowPreview = false): array
    {
        // Global published dictionary is cached
        $global = Cache::rememberForever('frontend_content_dict_global', function () {
            return self::buildPublishedDictionary(null);
        });

        // Company published dictionary is cached
        $company = [];
        if ($companyId !== null) {
            $company = Cache::rememberForever('frontend_content_dict_company_' . $companyId, function () use ($companyId) {
                return self::buildPublishedDictionary($companyId);
            });
        }

        $merged = array_merge($global, $company);

        // If in authorized preview session, overlay drafts in-memory without caching
        if ($allowPreview) {
            $draftOverlays = self::buildDraftOverlays($companyId);
            $merged = array_merge($merged, $draftOverlays);
        }

        return $merged;
    }

    /**
     * Build published dictionary from database.
     */
    protected static function buildPublishedDictionary(?int $companyId): array
    {
        $query = FrontendContent::query();

        if ($companyId === null) {
            $query->whereNull('company_id');
        } else {
            $query->where('company_id', $companyId);
        }

        return $query->get()->mapWithKeys(function (FrontendContent $item) {
            $value = $item->published_value ?? $item->default_value;
            return [$item->key => $value];
        })->toArray();
    }

    /**
     * Build draft overlay for preview sessions (never cached).
     */
    protected static function buildDraftOverlays(?int $companyId): array
    {
        // 1. Global drafts
        $globalDrafts = FrontendContent::whereNull('company_id')
            ->whereNotNull('custom_value')
            ->pluck('custom_value', 'key')
            ->toArray();

        // 2. Company drafts
        $companyDrafts = [];
        if ($companyId !== null) {
            $companyDrafts = FrontendContent::where('company_id', $companyId)
                ->whereNotNull('custom_value')
                ->pluck('custom_value', 'key')
                ->toArray();
        }

        return array_merge($globalDrafts, $companyDrafts);
    }

    /**
     * Save draft customized content.
     */
    public static function saveDraft(int $id, string $customValue, int $userId): FrontendContent
    {
        return DB::transaction(function () use ($id, $customValue, $userId) {
            $content = FrontendContent::findOrFail($id);
            $oldValue = $content->custom_value ?? $content->published_value ?? $content->default_value;

            $content->update([
                'custom_value' => trim($customValue),
                'last_modified_by' => $userId,
            ]);

            FrontendContentHistory::create([
                'frontend_content_id' => $content->id,
                'action' => 'draft_saved',
                'old_value' => $oldValue,
                'new_value' => trim($customValue),
                'user_id' => $userId,
            ]);

            return $content;
        });
    }

    /**
     * Publish a single content item.
     */
    public static function publish(int $id, int $userId): FrontendContent
    {
        return DB::transaction(function () use ($id, $userId) {
            $content = FrontendContent::findOrFail($id);
            $oldPublished = $content->published_value ?? $content->default_value;
            $newPublished = $content->custom_value ?? $oldPublished;

            $content->update([
                'published_value' => $newPublished,
                'published_at' => now(),
                'published_by' => $userId,
                'last_modified_by' => $userId,
            ]);

            FrontendContentHistory::create([
                'frontend_content_id' => $content->id,
                'action' => 'published',
                'old_value' => $oldPublished,
                'new_value' => $newPublished,
                'user_id' => $userId,
            ]);

            self::clearCache($content->company_id);

            return $content;
        });
    }

    /**
     * Publish all pending drafts in the specified company scope.
     */
    public static function publishAll(?int $companyId, int $userId): int
    {
        return DB::transaction(function () use ($companyId, $userId) {
            $query = FrontendContent::query();

            if ($companyId === null) {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', $companyId);
            }

            $items = $query->whereNotNull('custom_value')->get();
            $count = 0;

            foreach ($items as $item) {
                if ($item->custom_value !== $item->published_value) {
                    $oldPublished = $item->published_value ?? $item->default_value;

                    $item->update([
                        'published_value' => $item->custom_value,
                        'published_at' => now(),
                        'published_by' => $userId,
                        'last_modified_by' => $userId,
                    ]);

                    FrontendContentHistory::create([
                        'frontend_content_id' => $item->id,
                        'action' => 'published',
                        'old_value' => $oldPublished,
                        'new_value' => $item->custom_value,
                        'user_id' => $userId,
                    ]);

                    $count++;
                }
            }

            self::clearCache($companyId);

            return $count;
        });
    }

    /**
     * Reset customized content back to standard system default.
     */
    public static function resetToDefault(int $id, int $userId): FrontendContent
    {
        return DB::transaction(function () use ($id, $userId) {
            $content = FrontendContent::findOrFail($id);
            $oldValue = $content->published_value ?? $content->custom_value ?? $content->default_value;

            $content->update([
                'custom_value' => null,
                'published_value' => null,
                'published_at' => now(),
                'published_by' => $userId,
                'last_modified_by' => $userId,
            ]);

            FrontendContentHistory::create([
                'frontend_content_id' => $content->id,
                'action' => 'reset_to_default',
                'old_value' => $oldValue,
                'new_value' => $content->default_value,
                'user_id' => $userId,
            ]);

            self::clearCache($content->company_id);

            return $content;
        });
    }

    /**
     * Invalidate caches for company or global scope.
     */
    public static function clearCache(?int $companyId = null): void
    {
        if ($companyId === null) {
            Cache::forget('frontend_content_dict_global');
        } else {
            Cache::forget('frontend_content_dict_company_' . $companyId);
        }
    }

    /**
     * Clear all frontend content caches.
     */
    public static function clearAllCaches(): void
    {
        Cache::forget('frontend_content_dict_global');

        // Forget for all existing companies
        $companyIds = DB::table('companies')->pluck('id');
        foreach ($companyIds as $id) {
            Cache::forget('frontend_content_dict_company_' . $id);
        }
    }

    /**
     * Get dictionary for client-side JavaScript / React injection.
     * Never exposes draft content unless in authorized preview session.
     */
    public static function getDictionaryForUser(?User $user): array
    {
        $companyId = $user?->company_id ?? $user?->primary_company?->id;
        $isPreview = false;

        if ($user && $user->canManageContent() && session('frontend_content_preview_mode', false)) {
            $isPreview = true;
        }

        return self::getDictionary($companyId, $isPreview);
    }
}
