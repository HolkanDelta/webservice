<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fleet-rocket_command')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('recurso-confiable_command')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('recurso-token-change')
    ->twiceDaily(1, 13)
    ->withoutOverlapping();