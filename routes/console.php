<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('scheduler:heartbeat')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('presensi:send-daily-class-report')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('presensi:send-school-summary')->everyMinute();
\Illuminate\Support\Facades\Schedule::command('presensi:auto-alpa')->everyMinute()->withoutOverlapping()->runInBackground();
