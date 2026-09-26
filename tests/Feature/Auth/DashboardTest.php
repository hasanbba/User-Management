<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_live_user_statistics_and_recent_accounts(): void
    {
        $admin = User::factory()->create([
            'name' => 'Dashboard Admin',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->subDays(2),
        ]);
        User::factory()->create([
            'name' => 'Recent Active User',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->subDay(),
        ]);
        User::factory()->create([
            'name' => 'Recent Inactive Admin',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_INACTIVE,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => (int) $stats->total_users === 3
                && (int) $stats->active_users === 2
                && (int) $stats->inactive_users === 1
                && (int) $stats->admin_users === 2
                && (int) $stats->regular_users === 1)
            ->assertViewHas('recentUsers', fn ($users) => $users->count() === 3
                && $users->first()->name === 'Recent Inactive Admin')
            ->assertSee('Total users')
            ->assertSee('Recent Active User')
            ->assertSee('Recent Inactive Admin');
    }

    public function test_regular_user_dashboard_does_not_expose_global_user_statistics(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', null)
            ->assertViewHas('recentUsers', fn ($users) => $users->isEmpty())
            ->assertDontSee('Total users')
            ->assertDontSee('Recent users');
    }
}
