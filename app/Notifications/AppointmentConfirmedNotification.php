<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject('ClinicSync Appointment '.ucfirst($appointment->status))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your appointment has been '.$appointment->status.'.')
            ->line('Date: '.$appointment->appointment_date->isoFormat('dddd, MMM D, YYYY'))
            ->line('Time: '.date('g:i A', strtotime($appointment->start_time)))
            ->line('Physician: Dr. '.$appointment->physician->user->name)
            ->line('Queue number: #'.$appointment->queue_number)
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
            '[ClinicSync] Hi %s, your appointment on %s at %s with Dr. %s is %s. Queue #%d.',
            $notifiable->name,
            $appointment->appointment_date->isoFormat('MMM D'),
            date('g:i A', strtotime($appointment->start_time)),
            $appointment->physician->user->name,
            $appointment->status,
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
            'type' => 'appointment_status',
            'appointment_id' => $this->appointment->id,
            'status' => $this->appointment->status,
            'message' => 'Your appointment is '.$this->appointment->status.'.',
        ];
    }
}
