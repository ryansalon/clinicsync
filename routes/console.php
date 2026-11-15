<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('appointments:send-reminders')
    ->dailyAt('18:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
 * Queued notifications fall back to a once-per-minute drain when no dedicated
 * worker process (or Laravel Cloud Managed Queue) is running. Set
 * QUEUE_CRON_FALLBACK=false when running dedicated workers.
 */
Schedule::command('queue:work', [
    '--stop-when-empty' => true,
    '--max-time' => 55,
])
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer()
    ->when(fn () => (bool) env('QUEUE_CRON_FALLBACK', true));
