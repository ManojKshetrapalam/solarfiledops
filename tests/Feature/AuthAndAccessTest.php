<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sun on Earth');
    }

    public function test_super_admin_has_full_administrative_access(): void
    {
        $admin = User::where('role', 'super_admin')->first() ?: User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('admin.companies.index'))->assertStatus(200);
        $this->get(route('admin.companies.create'))->assertStatus(200);
        $this->get(route('admin.employees.create'))->assertStatus(200);
        $this->get(route('admin.data-management.index'))->assertStatus(200);
        $this->get(route('admin.audit-logs'))->assertStatus(200);
        $this->get(route('admin.services.create'))->assertStatus(200);
        $this->get(route('admin.reports.index'))->assertStatus(200);
    }

    public function test_admin_operations_has_operational_access_but_is_restricted_from_governance(): void
    {
        $opsAdmin = User::create([
            'name' => 'Operations Officer',
            'username' => 'opsadmin',
            'email' => 'opsadmin@solar.local',
            'password' => bcrypt('password123'),
            'role' => 'admin_operations',
            'employee_code' => 'OPS-001',
            'phone' => '+91 99999 00001',
            'designation' => 'Admin Operations',
            'status' => 'active',
            'first_login_completed' => true,
            'password_change_required' => false,
        ]);

        $this->actingAs($opsAdmin);

        // Permitted operational routes
        $this->get(route('admin.dashboard'))->assertStatus(200);
        $this->get(route('admin.companies.index'))->assertStatus(200); // Read-only list
        $this->get(route('admin.employees.index'))->assertStatus(200); // Read-only technician view
        $this->get(route('admin.services.index'))->assertStatus(200);
        $this->get(route('admin.services.create'))->assertStatus(200);
        $this->get(route('admin.reports.index'))->assertStatus(200);

        // Prohibited governance routes (Must reject with 403 Forbidden)
        $this->get(route('admin.companies.create'))->assertStatus(403);
        $this->post(route('admin.companies.store'), ['name' => 'Unauthorized Co', 'code' => 'UAC'])->assertStatus(403);
        $this->get(route('admin.companies.edit', 1))->assertStatus(403);
        $this->get(route('admin.employees.create'))->assertStatus(403);
        $this->post(route('admin.employees.store'), ['name' => 'Unauthorized Emp'])->assertStatus(403);
        $this->get(route('admin.data-management.index'))->assertStatus(403);
        $this->get(route('admin.audit-logs'))->assertStatus(403);
    }

    public function test_admin_can_login_and_redirect_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@solar.local',
            'password' => 'Admin@123$$',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_super_admin_can_login_with_default_credentials(): void
    {
        $response = $this->post('/login', [
            'login' => 'superadmin',
            'password' => 'SAdmin@123$$',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_engineer_can_login_and_redirect_to_engineer_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'raj@solar.local',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('engineer.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_engineer_cannot_access_admin_dashboard(): void
    {
        $engineer = User::where('email', 'raj@solar.local')->first();

        $response = $this->actingAs($engineer)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'Admin@123$$',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_engineer_can_login_with_username_and_employee_code(): void
    {
        // Login with username
        $response = $this->post('/login', [
            'login' => 'raj',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('engineer.dashboard'));
        $this->assertAuthenticated();

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Login with employee code
        $response2 = $this->post('/login', [
            'login' => 'ENG-101',
            'password' => 'password123',
        ]);
        $response2->assertRedirect(route('engineer.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response2 = $this->get(route('engineer.dashboard'));
        $response2->assertRedirect(route('login'));
    }
}

