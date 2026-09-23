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
        $response->assertSee('Solar Field Operations');
    }

    public function test_admin_can_login_and_redirect_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@solar.local',
            'password' => 'password123',
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

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response2 = $this->get(route('engineer.dashboard'));
        $response2->assertRedirect(route('login'));
    }
}
