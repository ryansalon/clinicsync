<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Appointment $appointment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'sms'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        return (new MailMessage)
            ->subject('Appointment Reminder - ClinicSync')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('This is a reminder for your upcoming appointment.')
            ->line('Date: '.$appointment->appointment_date->isoFormat('dddd, MMM D, YYYY'))
            ->line('Time: '.date('g:i A', strtotime($appointment->start_time)))
            ->line('Physician: Dr. '.$appointment->physician->user->name)
            ->line('Queue number: #'.$appointment->queue_number)
            ->action('View appointment', route('patient.appointments.index'))
            ->line('If you cannot make it, please cancel ahead of time so others may use the slot.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $appointment = $this->appointment;

        return sprintf(
            '[ClinicSync] Reminder: Hi %s, your appointment is on %s at %s with Dr. %s (Queue #%d).',
            $notifiable->name,
            $appointment->appointment_date->isoFormat('MMM D'),
            date('g:i A', strtotime($appointment->start_time)),
            $appointment->physician->user->name,
            $appointment->queue_number,
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'message' => 'Reminder: appointment on '.$this->appointment->appointment_date->isoFormat('MMM D').' at '.date('g:i A', strtotime($this->appointment->start_time)).'.',
        ];
    }
}
