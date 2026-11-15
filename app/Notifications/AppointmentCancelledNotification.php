<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Appointment $appointment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'sms'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment;

        return (new MailMessage)
            ->subject('ClinicSync Appointment Cancelled')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your clinic appointment has been cancelled.')
            ->line('Date: '.$appointment->appointment_date->isoFormat('dddd, MMM D, YYYY'))
            ->line('Time: '.date('g:i A', strtotime($appointment->start_time)))
            ->line('Physician: Dr. '.$appointment->physician->user->name)
            ->action('Book a new appointment', route('patient.appointments.create'));
    }

    public function toSms(object $notifiable): string
    {
        $appointment = $this->appointment;

        return sprintf(
            '[ClinicSync] Hi %s, your appointment on %s at %s with Dr. %s has been cancelled.',
            $notifiable->name,
            $appointment->appointment_date->isoFormat('MMM D'),
            date('g:i A', strtotime($appointment->start_time)),
            $appointment->physician->user->name,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'appointment_status',
            'appointment_id' => $this->appointment->id,
            'message' => 'Your appointment on '.$this->appointment->appointment_date->isoFormat('MMM D')
                .' at '.date('g:i A', strtotime($this->appointment->start_time)).' was cancelled.',
        ];
    }
}
