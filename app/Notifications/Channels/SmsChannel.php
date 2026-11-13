<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * SMS channel stub. Swap the logging call for a gateway API
 * (e.g., Semaphore, Twilio) when an SMS provider is provisioned.
 */
class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $phone = $notifiable->phone ?? null;

        if (blank($phone)) {
            return;
        }

        Log::channel('sms')->info((string) $notification->toSms($notifiable), [
            'to' => $phone,
            'notification' => $notification::class,
        ]);
    }
}
