<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('appointments:send-reminders {--date= : Reminder target date (Y-m-d), defaults to tomorrow}')]
#[Description('Queue appointment reminders for upcoming confirmed/pending appointments')]
class SendAppointmentReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ?: now()->addDay()->toDateString();

        $appointments = Appointment::query()
            ->with(['patient.user', 'physician.user'])
            ->forDate($date)
            ->active()
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            $user = $appointment->patient->user;

            if ($user === null || ! $user->is_active) {
                continue;
            }

            $user->notify(new AppointmentReminderNotification($appointment));
            $count++;
        }

        $this->info("Queued {$count} reminder(s) for {$date}.");

        return self::SUCCESS;
    }
}
