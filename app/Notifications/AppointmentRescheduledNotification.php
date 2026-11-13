<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledNotification extends Notification implements ShouldQueue
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
            ->subject('ClinicSync Appointment Rescheduled')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your appointment has been moved to a new slot.')
            ->line('New date: '.$appointment->appointment_date->isoFormat('dddd, MMM D, YYYY'))
            ->line('New time: '.date('g:i A', strtotime($appointment->start_time)))
            ->line('Physician: Dr. '.$appointment->physician->user->name)
            ->line('New queue number: #'.$appointment->queue_number)
            ->action('View my appointments', route('patient.appointments.index'))
            ->line('Please arrive at least 15 minutes before your scheduled time.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        $appointment = $this->appointment;

        return sprintf(
            '[ClinicSync] Hi %s, your appointment was moved to %s at %s with Dr. %s. New queue #%d.',
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
            'type' => 'appointment_rescheduled',
            'appointment_id' => $this->appointment->id,
            'message' => 'Your appointment was moved to '.$this->appointment->appointment_date->isoFormat('MMM D').' at '
                .date('g:i A', strtotime($this->appointment->start_time))
                .'. New queue number #'.$this->appointment->queue_number.'.',
        ];
    }
}
