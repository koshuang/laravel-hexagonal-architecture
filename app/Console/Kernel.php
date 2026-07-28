<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Override;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    #[Override]
    protected function schedule(Schedule $_schedule): void
    {
        // $_schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    #[Override]
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
