<?php

use App\Services\FrontendContentService;

if (!function_exists('frontend_content')) {
    /**
     * Resolve user-facing frontend content with fallback hierarchy.
     */
    function frontend_content(string $key, ?string $fallback = null, ?int $companyId = null): string
    {
        return FrontendContentService::get($key, $fallback, $companyId);
    }
}
