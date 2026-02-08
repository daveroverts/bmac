<?php

use App\Console\Commands\EventCleanupReservationsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(EventCleanupReservationsCommand::class)->everyFiveMinutes();

Schedule::command('activitylog:clean --force')->daily();

if (config('telescope.enabled')) {
    Schedule::command('telescope:prune')->daily();
}

if (config('queue.default') == 'redis') {
    Schedule::command('horizon:snapshot')->everyFiveMinutes();
    Schedule::command('cache:prune-stale-tags')->hourly();
}
