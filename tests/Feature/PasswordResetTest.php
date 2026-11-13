<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_forgot_password_page(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_forgot_password_reports_success_for_unknown_addresses(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'nobody@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_registered_user_receives_a_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_form_is_displayed(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk();
    }

    public function test_user_can_reset_password_with_a_valid_token_and_is_signed_in(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-clinic-password',
            'password_confirmation' => 'new-clinic-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
        $this->assertTrue(Hash::check('new-clinic-password', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.store'), [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'new-clinic-password',
            'password_confirmation' => 'new-clinic-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_reset_is_throttled(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('password.email'), ['email' => 'someone@example.com'])
                ->assertSessionDoesntHaveErrors();
        }

        $this->post(route('password.email'), ['email' => 'someone@example.com'])
            ->assertStatus(429);
    }
}
