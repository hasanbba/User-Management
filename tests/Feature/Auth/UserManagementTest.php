<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_paginated_user_list(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(17)->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->total() === 18 && $users->perPage() === 15)
            ->assertSee('User directory');
    }

    public function test_admin_can_search_by_name_email_and_phone(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['name' => 'John Searchable', 'email' => 'john@example.test', 'phone' => '5550101']);
        User::factory()->create(['name' => 'Jane Other', 'email' => 'jane@example.test', 'phone' => '5550202']);

        foreach ([
            ['query' => 'John Searchable', 'match' => 'John Searchable', 'exclude' => 'Jane Other'],
            ['query' => 'jane@example.test', 'match' => 'Jane Other', 'exclude' => 'John Searchable'],
            ['query' => '5550101', 'match' => 'John Searchable', 'exclude' => 'Jane Other'],
        ] as $case) {
            $this->actingAs($admin)
                ->get(route('users.index', ['search' => $case['query']]))
                ->assertOk()
                ->assertSee($case['match'])
                ->assertDontSee($case['exclude']);
        }
    }

    public function test_admin_can_filter_by_role_and_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['name' => 'Active Admin', 'role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]);
        User::factory()->create(['name' => 'Inactive Member', 'role' => User::ROLE_USER, 'status' => User::STATUS_INACTIVE]);
        User::factory()->create(['name' => 'Active Member', 'role' => User::ROLE_USER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->get(route('users.index', ['role' => User::ROLE_USER, 'status' => User::STATUS_ACTIVE]))
            ->assertOk()
            ->assertSee('Active Member')
            ->assertDontSee('Inactive Member')
            ->assertDontSee('Active Admin');
    }

    public function test_sorting_and_pagination_preserve_search_and_filters(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(17)->create(['role' => User::ROLE_USER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->get(route('users.index', [
                'page' => 2,
                'search' => 'example',
                'role' => User::ROLE_USER,
                'status' => User::STATUS_ACTIVE,
                'sort' => 'name',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->currentPage() === 2 && $users->count() === 2)
            ->assertSee('search=example', false)
            ->assertSee('role=user', false)
            ->assertSee('status=active', false)
            ->assertSee('sort=name', false)
            ->assertSee('direction=asc', false);
    }

    public function test_admin_can_activate_and_deactivate_another_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $member = User::factory()->create(['status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->patch(route('users.status', $member))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'User account deactivated successfully.');

        $this->assertSame(User::STATUS_INACTIVE, $member->fresh()->status);

        $this->patch(route('users.status', $member))
            ->assertSessionHas('status', 'User account activated successfully.');

        $this->assertSame(User::STATUS_ACTIVE, $member->fresh()->status);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->patch(route('users.status', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'You cannot deactivate your own account.');

        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);
    }

    public function test_admin_cannot_deactivate_themselves_through_the_edit_form(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), $this->validUserData([
                'email' => $admin->email,
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_INACTIVE,
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHasErrors('status');

        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_regular_user_cannot_change_an_account_status(): void
    {
        $member = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($member)
            ->patch(route('users.status', $target))
            ->assertForbidden();

        $this->assertSame(User::STATUS_ACTIVE, $target->fresh()->status);
    }

    public function test_empty_search_results_have_a_clear_filters_state(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('users.index', ['search' => 'no-match-at-all']))
            ->assertOk()
            ->assertSee('No users found.')
            ->assertSee('Clear filters');
    }

    public function test_admin_can_create_user_with_hashed_password_and_profile_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('users.store'), $this->validUserData([
                'email' => 'created@example.test',
                'profile_image' => $this->validPng(),
            ]))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'User created successfully.');

        $created = User::where('email', 'created@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Password123', $created->password));
        $this->assertSame(User::ROLE_USER, $created->role);
        $this->assertSame(User::STATUS_ACTIVE, $created->status);
        Storage::disk('public')->assertExists($created->profile_image);
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['name' => 'Sam Example', 'email' => 'sam@example.test']);

        $this->actingAs($admin)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertSee('Sam Example')
            ->assertSee('sam@example.test');
    }

    public function test_admin_can_update_user_without_replacing_blank_password_or_existing_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $imagePath = 'profiles/keep.png';
        Storage::disk('public')->put($imagePath, $this->pngContents());
        $user = User::factory()->create([
            'name' => 'Before Name',
            'email' => 'before@example.test',
            'password' => 'ExistingPass123',
            'profile_image' => $imagePath,
        ]);
        $passwordHash = $user->password;

        $this->actingAs($admin)
            ->put(route('users.update', $user), $this->validUserData([
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
                'password' => '',
                'password_confirmation' => '',
                'profile_image' => null,
                'status' => User::STATUS_INACTIVE,
                'role' => User::ROLE_ADMIN,
            ]))
            ->assertRedirect(route('users.show', $user));

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertSame(User::STATUS_INACTIVE, $user->status);
        $this->assertSame($passwordHash, $user->password);
        $this->assertSame($imagePath, $user->profile_image);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_admin_can_change_password_when_supplied(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['password' => 'BeforePass123']);

        $this->actingAs($admin)
            ->put(route('users.update', $user), $this->validUserData([
                'email' => $user->email,
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ]))
            ->assertRedirect(route('users.show', $user));

        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
    }

    public function test_duplicate_email_is_rejected_when_creating_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['email' => 'duplicate@example.test']);

        $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validUserData(['email' => 'duplicate@example.test']))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_delete_user_and_stored_profile_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $imagePath = 'profiles/remove.png';
        Storage::disk('public')->put($imagePath, $this->pngContents());
        $user = User::factory()->create(['profile_image' => $imagePath]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_invalid_profile_image_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validUserData([
                'profile_image' => UploadedFile::fake()->createWithContent('notes.txt', 'not an image'),
            ]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('profile_image');
    }

    /** @return array<string, mixed> */
    private function validUserData(array $overrides = []): array
    {
        return array_replace([
            'name' => 'New Account',
            'email' => 'new-account@example.test',
            'phone' => '+1 (555) 010-0200',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ], $overrides);
    }

    private function validPng(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('avatar.png', $this->pngContents());
    }

    private function pngContents(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
    }
}
