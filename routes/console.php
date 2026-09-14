<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tasks:check-deadlines')
    ->daily()
    ->description('Check task deadlines and dispatch in-app notifications daily');

Schedule::command('tasks:smart-reminders')
    ->everyMinute()
    ->description('Check smart deadline reminders (1 hour, 10 min, overdue)');
