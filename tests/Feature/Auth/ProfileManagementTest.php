<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Taylor User',
            'email' => 'taylor@example.test',
            'phone' => '5550100',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Taylor User')
            ->assertSee('taylor@example.test')
            ->assertSee('5550100')
            ->assertSee('Change password');
    }

    public function test_user_can_update_their_profile_and_replace_profile_image(): void
    {
        Storage::fake('public');
        $oldImage = 'profiles/old-photo.png';
        Storage::disk('public')->put($oldImage, $this->pngContents());
        $user = User::factory()->create([
            'name' => 'Before Name',
            'email' => 'before@example.test',
            'phone' => '5550100',
            'profile_image' => $oldImage,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
                'phone' => '+1 555 0101',
                'profile_image' => $this->validPng(),
            ])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status', 'Your profile was updated successfully.');

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertSame('+1 555 0101', $user->phone);
        $this->assertNull($user->email_verified_at);
        $this->assertNotSame($oldImage, $user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
        Storage::disk('public')->assertMissing($oldImage);
    }

    public function test_unchanged_email_keeps_its_existing_verification(): void
    {
        $verifiedAt = now()->subDay()->startOfSecond();
        $user = User::factory()->create([
            'email' => 'same@example.test',
            'email_verified_at' => $verifiedAt,
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ])
            ->assertRedirect(route('profile.index'));

        $this->assertSame($verifiedAt->toDateTimeString(), $user->fresh()->email_verified_at->toDateTimeString());
    }

    public function test_profile_email_must_remain_unique(): void
    {
        $user = User::factory()->create(['email' => 'member@example.test']);
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'taken@example.test',
                'phone' => $user->phone,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');
    }

    public function test_profile_rejects_invalid_images(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => UploadedFile::fake()->createWithContent('not-image.txt', 'plain text'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('profile_image');
    }

    public function test_user_can_change_password_with_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'CurrentPass123']);

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'CurrentPass123',
                'password' => 'UpdatedPass456',
                'password_confirmation' => 'UpdatedPass456',
            ])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('status', 'Your password was changed successfully.');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('UpdatedPass456', $user->fresh()->password));
    }

    public function test_incorrect_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'CurrentPass123']);

        $this->actingAs($user)
            ->from(route('profile.password'))
            ->put(route('profile.password.update'), [
                'current_password' => 'WrongPass000',
                'password' => 'UpdatedPass456',
                'password_confirmation' => 'UpdatedPass456',
            ])
            ->assertRedirect(route('profile.password'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('CurrentPass123', $user->fresh()->password));
    }

    public function test_guest_cannot_access_profile_or_password_forms(): void
    {
        $this->get(route('profile.index'))->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->get(route('profile.password'))->assertRedirect(route('login'));
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
