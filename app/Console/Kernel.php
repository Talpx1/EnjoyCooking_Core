<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule): void {
        $schedule->command('telescope:prune')->daily();
        $schedule->command('activitylog:clean')->monthly();
        $schedule->command('passport:purge')->hourly();
        $schedule->command('cache:prune-stale-tags')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
