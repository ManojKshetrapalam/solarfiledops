<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FrontendContent;
use App\Models\User;
use App\Services\FrontendContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FrontendContentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Check 1: Default fallback works
     */
    public function test_default_fallback_resolves_canonical_defaults_and_code_fallbacks(): void
    {
        // Canonical default from DB
        $resolved = frontend_content('navigation.technicians');
        $this->assertEquals('Engineers / Technicians', $resolved);

        // Code fallback when key is not in database
        $fallback = frontend_content('custom.unseeded.key', 'My Dynamic Fallback');
        $this->assertEquals('My Dynamic Fallback', $fallback);
    }

    /**
     * Check 2: Authorized client can edit wording (Draft -> Preview -> Publish)
     */
    public function test_authorized_content_manager_can_save_draft_preview_and_publish(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?: User::where('role', 'admin')->first();
        $content = FrontendContent::where('key', 'navigation.technicians')->whereNull('company_id')->firstOrFail();

        // 1. Save draft
        $response = $this->actingAs($superAdmin)->patch(route('cms.update', $content->id), [
            'custom_value' => 'Senior Solar Specialists',
        ]);
        $response->assertSessionHas('success');
        $content->refresh();
        $this->assertEquals('Senior Solar Specialists', $content->custom_value);

        // 2. Draft is strictly isolated to preview session - unauthenticated or normal user still sees live default
        $this->assertEquals('Engineers / Technicians', FrontendContentService::get('navigation.technicians'));

        // 3. Toggle preview in session
        session(['frontend_content_preview_mode' => true]);
        $this->assertEquals('Senior Solar Specialists', FrontendContentService::get('navigation.technicians'));
        session(['frontend_content_preview_mode' => false]);

        // 4. Publish draft live
        $pubResponse = $this->actingAs($superAdmin)->post(route('cms.publish', $content->id));
        $pubResponse->assertSessionHas('success');

        $content->refresh();
        $this->assertEquals('Senior Solar Specialists', $content->published_value);

        // 5. Live resolution now reflects published value everywhere
        $this->assertEquals('Senior Solar Specialists', frontend_content('navigation.technicians'));
    }

    /**
     * Check 3: Published changes appear in React/API and Blade
     */
    public function test_published_changes_appear_in_api_and_blade(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?: User::where('role', 'admin')->first();
        $content = FrontendContent::where('key', 'branding.app_name')->whereNull('company_id')->firstOrFail();

        // Update & publish
        $content->update([
            'custom_value' => 'Apex Solar Power Systems',
            'published_value' => 'Apex Solar Power Systems',
            'published_at' => now(),
        ]);
        FrontendContentService::clearAllCaches();

        // Verify API endpoint (used by React / external components)
        $apiResponse = $this->getJson(route('api.frontend-content'));
        $apiResponse->assertOk();
        $this->assertEquals('Apex Solar Power Systems', $apiResponse->json('content')['branding.app_name']);
        $this->assertFalse($apiResponse->json('is_preview'));

        // Verify Blade render (e.g. login screen branding)
        $bladeResponse = $this->get(route('login'));
        $bladeResponse->assertOk();
        $bladeResponse->assertSee('Apex Solar Power Systems');
    }

    /**
     * Check 4: Company-specific overrides work correctly and do not leak
     */
    public function test_company_specific_overrides_work_and_do_not_leak(): void
    {
        $company1 = Company::first();
        $company2 = Company::skip(1)->first();
        $this->assertNotNull($company2);

        // Create company-specific override for Company 1
        $override = FrontendContent::create([
            'key' => 'navigation.technicians',
            'category' => 'navigation',
            'default_value' => 'Engineers / Technicians',
            'custom_value' => 'Company 1 Solar Engineers',
            'published_value' => 'Company 1 Solar Engineers',
            'company_id' => $company1->id,
            'published_at' => now(),
        ]);
        FrontendContentService::clearAllCaches();

        // 1. Explicit company 1 context resolves company override
        $resolvedCo1 = FrontendContentService::get('navigation.technicians', null, $company1->id);
        $this->assertEquals('Company 1 Solar Engineers', $resolvedCo1);

        // 2. Company 2 does NOT see Company 1 override (falls back to global)
        $resolvedCo2 = FrontendContentService::get('navigation.technicians', null, $company2->id);
        $this->assertEquals('Engineers / Technicians', $resolvedCo2);

        // 3. Global context without company still gets global default
        $resolvedGlobal = FrontendContentService::get('navigation.technicians', null, null);
        $this->assertEquals('Engineers / Technicians', $resolvedGlobal);
    }

    /**
     * Check 5: Unauthorized users cannot modify CMS content
     */
    public function test_unauthorized_users_are_forbidden_from_cms_operations(): void
    {
        // 1. Regular engineer cannot access CMS
        $engineer = User::where('role', 'engineer')->first();
        $this->actingAs($engineer)->get(route('cms.index'))->assertStatus(403);

        // 2. Admin Operations WITHOUT can_manage_content is forbidden
        $opsAdmin = User::create([
            'name' => 'Ops Officer',
            'username' => 'opsofficer',
            'email' => 'opsofficer@solar.local',
            'password' => bcrypt('password123'),
            'role' => 'admin_operations',
            'can_manage_content' => false,
            'status' => 'active',
            'first_login_completed' => true,
        ]);

        $this->actingAs($opsAdmin)->get(route('cms.index'))->assertStatus(403);

        $content = FrontendContent::first();
        $this->actingAs($opsAdmin)->patch(route('cms.update', $content->id), [
            'custom_value' => 'Hacked Wording',
        ])->assertStatus(403);

        // 3. Client content manager with can_manage_content CAN access
        $clientManager = User::create([
            'name' => 'Client Content Mgr',
            'username' => 'contentmgr',
            'email' => 'contentmgr@solar.local',
            'password' => bcrypt('password123'),
            'role' => 'admin_operations',
            'can_manage_content' => true,
            'company_id' => Company::first()->id,
            'status' => 'active',
            'first_login_completed' => true,
        ]);

        $this->actingAs($clientManager)->get(route('cms.index'))->assertStatus(200);
    }

    /**
     * Check 6: Published changes and resets invalidate cached content
     */
    public function test_cache_is_invalidated_upon_publishing_and_reset(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?: User::where('role', 'admin')->first();
        $content = FrontendContent::where('key', 'buttons.create_service')->whereNull('company_id')->firstOrFail();

        // Populate cache
        $initial = FrontendContentService::get('buttons.create_service');
        $this->assertEquals('+ Create Service/ Maintainance', $initial);
        $this->assertTrue(Cache::has('frontend_content_dict_global'));

        // Publish new customized value
        $content->update(['custom_value' => '+ Book Solar Service']);
        FrontendContentService::publish($content->id, $superAdmin->id);

        // Cache must be refreshed with new value
        $updated = FrontendContentService::get('buttons.create_service');
        $this->assertEquals('+ Book Solar Service', $updated);

        // Reset to default
        FrontendContentService::resetToDefault($content->id, $superAdmin->id);

        // Cache must reflect standard default again
        $resetVal = FrontendContentService::get('buttons.create_service');
        $this->assertEquals('+ Create Service/ Maintainance', $resetVal);
    }

    /**
     * Check 7: SQLite partial uniqueness prevents duplicate global keys
     */
    public function test_sqlite_partial_uniqueness_prevents_duplicate_global_keys(): void
    {
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        // Attempt to insert duplicate global key (company_id = NULL)
        FrontendContent::create([
            'key' => 'branding.app_name',
            'category' => 'branding',
            'default_value' => 'Duplicate App Name',
            'company_id' => null,
        ]);
    }
}
