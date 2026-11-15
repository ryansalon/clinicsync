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
        $count = 0;

        Appointment::query()
            ->with(['patient.user', 'physician.user'])
            ->forDate($date)
            ->active()
            ->whereNull('reminder_sent_at')
            ->chunkById(100, function ($appointments) use (&$count): void {
                $queuedIds = [];

                foreach ($appointments as $appointment) {
                    $user = $appointment->patient->user;

                    if ($user === null || ! $user->is_active) {
                        continue;
                    }

                    $user->notify(new AppointmentReminderNotification($appointment));
                    $queuedIds[] = $appointment->id;
                    $count++;
                }

                if ($queuedIds !== []) {
                    Appointment::query()
                        ->whereIn('id', $queuedIds)
                        ->update(['reminder_sent_at' => now()]);
                }
            });

        $this->info("Queued {$count} reminder(s) for {$date}.");

        return self::SUCCESS;
    }
}
