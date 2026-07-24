<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks (§10 step 20, §17 checklist).
Schedule::command('otp:prune')->daily();
Schedule::command('matches:prune-stale')->weekly();
