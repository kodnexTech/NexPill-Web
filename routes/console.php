<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('nexpill:materialize-schedules --days=14')->dailyAt('00:05')->withoutOverlapping();
Schedule::command('nexpill:process-dose-reminders')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('nexpill:finalize-missed-doses')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('nexpill:process-appointment-reminders')->everyTenMinutes()->withoutOverlapping();
Schedule::command('nexpill:check-low-stock')->hourly()->withoutOverlapping();
