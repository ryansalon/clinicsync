<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private function sendTestNotification(User $user, string $message = 'Your appointment is confirmed.'): void
    {
        $user->notify(new class($message) extends Notification
        {
            public function __construct(private readonly string $message) {}

            public function via(object $notifiable): array
            {
                return ['database'];
            }

            public function toArray(object $notifiable): array
            {
                return ['type' => 'appointment_status', 'message' => $this->message];
            }
        });
    }

    public function test_guest_is_redirected_from_notification_routes(): void
    {
        $this->post('notifications/read-all')->assertRedirect(route('login'));
    }

    public function test_user_can_mark_their_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $this->sendTestNotification($user);

        $notification = $user->fresh()->notifications()->first();
        $this->assertNull($notification->read_at);

        $this->actingAs($user)
            ->post(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_someone_elses_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $this->sendTestNotification($owner);

        $notification = $owner->fresh()->notifications()->first();

        $this->actingAs($intruder)
            ->post(route('notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $this->sendTestNotification($user, 'First message');
        $this->sendTestNotification($user, 'Second message');

        $this->assertSame(2, $user->fresh()->unreadNotifications()->count());

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
