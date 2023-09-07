<?php

namespace App\Console;

use App\Console\Commands\EventCleanupReservationsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Bugsnag\BugsnagLaravel\Commands\DeployCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(EventCleanupReservationsCommand::class)->everyFiveMinutes();

        $schedule->command('activitylog:clean')->daily();

        if (config('telescope.enabled')) {
            $schedule->command('telescope:prune')->daily();
        }

        if (config('queue.default') == 'redis') {
            $schedule->command('horizon:snapshot')->everyFiveMinutes();
        }

        if (config('queue.default') == 'redis') {
            $schedule->command('cache:prune-stale-tags')->hourly();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
