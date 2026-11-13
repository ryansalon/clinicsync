<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('appointments:send-reminders')
    ->dailyAt('18:00')
    ->withoutOverlapping();

/*
 * Queued notifications (confirmations, reschedules, reminders) need a worker.
 * Draining the queue every minute keeps notifications flowing without a
 * long-running process, as long as the scheduler itself is ticking.
 */
Schedule::command('queue:work', [
    '--stop-when-empty' => true,
    '--max-time' => 55,
])
    ->everyMinute()
    ->withoutOverlapping();
