<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Schedule the queue:restart command to run every five minutes if you want to use shared hosting, otherwise comment below lines
        $schedule->command('queue:restart')
            ->everyFiveMinutes();
        $schedule->command('queue:work --tries=3')
            ->everyMinute()
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/queue-jobs.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
