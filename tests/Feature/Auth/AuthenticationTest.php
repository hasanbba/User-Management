<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to your account');
    }

    public function test_admin_can_login_and_last_login_is_recorded(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'role' => User::ROLE_ADMIN,
            'password' => 'admin-password',
        ]);

        $this->post(route('login.store'), [
            'email' => ' ADMIN@EXAMPLE.TEST ',
            'password' => 'admin-password',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_remember_me_sets_a_recaller_cookie(): void
    {
        $user = User::factory()->create(['password' => 'remember-password']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'remember-password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertNotNull($response->getCookie(Auth::guard('web')->getRecallerName()));
        $this->assertAuthenticatedAs($user);
    }

    public function test_regular_user_is_redirected_to_their_profile_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.test',
            'role' => User::ROLE_USER,
            'password' => 'user-password',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'user-password',
        ])->assertRedirect(route('profile.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'known@example.test']);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'known@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_with_correct_credentials_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.test',
            'status' => User::STATUS_INACTIVE,
            'password' => 'inactive-password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'inactive@example.test',
                'password' => 'inactive-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This account is inactive. Contact an administrator for help.']);

        $this->assertGuest();
    }

    public function test_inactive_user_with_wrong_password_gets_generic_credentials_error(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.test',
            'status' => User::STATUS_INACTIVE,
            'password' => 'inactive-password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'inactive@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_logged_in_inactive_account_is_signed_out_on_its_next_request(): void
    {
        $inactiveUser = User::factory()->create(['status' => User::STATUS_INACTIVE]);

        $this->actingAs($inactiveUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['marker' => 'before-logout'])
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing('marker');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $email = 'throttle@example.test';
        $key = Str::transliterate(Str::lower($email).'|127.0.0.1');
        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))->post(route('login.store'), [
                'email' => $email,
                'password' => 'wrong-password',
            ]);
        }

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }
}
