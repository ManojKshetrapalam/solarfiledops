<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FrontendContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FrontendContentApiController extends Controller
{
    /**
     * Retrieve published frontend content dictionary.
     * Drafts are strictly withheld unless in an authorized manager's preview session.
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');
        if ($companyId !== null && $companyId !== '') {
            $companyId = (int) $companyId;
        } else {
            $companyId = auth()->user()?->company_id ?? auth()->user()?->primary_company?->id;
        }

        // Preview is ONLY permitted if user is authenticated and authorized with preview session active
        $allowPreview = false;
        if (auth()->check() && auth()->user()->canManageContent() && session('frontend_content_preview_mode', false)) {
            $allowPreview = true;
        }

        $dictionary = FrontendContentService::getDictionary($companyId, $allowPreview);

        $response = response()->json([
            'status' => 'success',
            'company_id' => $companyId,
            'is_preview' => $allowPreview,
            'count' => count($dictionary),
            'content' => $dictionary,
        ]);

        // If not in preview, send cache headers for fast client caching
        if (!$allowPreview) {
            $response->header('Cache-Control', 'public, max-age=300');
        } else {
            $response->header('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
