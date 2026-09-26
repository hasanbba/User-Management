<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_user_management_pages(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('User directory');

        $this->get(route('users.create'))
            ->assertOk()
            ->assertSee('Create user');
    }

    public function test_regular_user_gets_forbidden_for_admin_pages(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();
    }

    public function test_guests_are_redirected_to_login_before_admin_authorization(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_regular_user_dashboard_hides_user_management_links(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $response->assertDontSee(route('users.index'), false);
        $response->assertDontSee(route('users.create'), false);
    }

    public function test_admin_dashboard_shows_user_management_links(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $response->assertSee(route('users.index'), false);
        $response->assertSee(route('users.create'), false);
    }

    public function test_regular_user_is_forbidden_on_state_changing_admin_routes(): void
    {
        $this->registerAdminWriteProbe();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->call($method, '/__admin-authorization-probe')->assertForbidden();
        }
    }

    public function test_admin_can_pass_the_admin_guard_on_state_changing_routes(): void
    {
        $this->registerAdminWriteProbe();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->call($method, '/__admin-authorization-probe')->assertNoContent();
        }
    }

    private function registerAdminWriteProbe(): void
    {
        Route::match(
            ['POST', 'PUT', 'PATCH', 'DELETE'],
            '/__admin-authorization-probe',
            fn () => response()->noContent()
        )->middleware(['auth', 'active', 'admin']);
    }
}
