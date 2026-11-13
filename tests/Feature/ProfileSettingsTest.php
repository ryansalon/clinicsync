<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile_settings(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_profile_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_user_can_update_name_and_phone(): void
    {
        $user = User::factory()->create(['phone' => null]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Juan Updated Cruz',
                'phone' => '09171234567',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertSame('Juan Updated Cruz', $user->name);
        $this->assertSame('09171234567', $user->phone);
        $this->assertTrue(AuditLog::where('action', 'user.profile_updated')->where('user_id', $user->id)->exists());
    }

    public function test_profile_requires_a_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), ['name' => '', 'phone' => ''])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('name');
    }

    public function test_non_image_photo_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'phone' => $user->phone,
                'photo' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('photo');
    }

    public function test_profile_photos_are_stored_privately_and_served_to_the_owner_only(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        // Before any sign-in, the photo route must bounce guests to login.
        $this->get(route('profile.photo'))->assertRedirect(route('login'));

        $user = $this->createUserWithRole('patient');
        $this->makePatientProfile($user);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'phone' => $user->phone,
                'photo' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertRedirect(route('profile.edit'));

        $path = $user->patient->fresh()->photo_path;

        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);

        // The owner can fetch their photo through the authorized route.
        $this->actingAs($user)->get(route('profile.photo'))->assertOk();
    }

    public function test_photo_route_returns_a_404_when_the_user_has_no_photo(): void
    {
        $user = $this->createUserWithRole('patient');
        $this->makePatientProfile($user);

        $this->actingAs($user)->get(route('profile.photo'))->assertNotFound();
    }

    public function test_user_can_change_password_with_the_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('password.change'), [
                'current_password' => 'password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
        $this->assertTrue(AuditLog::where('action', 'user.password_changed')->where('user_id', $user->id)->exists());
    }

    public function test_password_change_rejects_a_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('password.change'), [
                'current_password' => 'wrong-password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
