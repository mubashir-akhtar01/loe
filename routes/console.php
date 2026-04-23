<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('loe:send-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

Schedule::command('loe:send-overdue-reminders')
    ->dailyAt('09:15')
    ->withoutOverlapping();

Schedule::command('loe:auto-close-months')
    ->dailyAt('00:30')
    ->withoutOverlapping();
