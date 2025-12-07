<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CheckPopularPeople;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CheckPopularPeople::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run every hour to check for popular people (more than 50 likes)
        $schedule->command('people:check-popular')->hourly();
    }

    protected function commands(): void
    {
        // Load commands from routes/console.php if present
        if (file_exists(base_path('routes/console.php'))) {
            $this->load(__DIR__.'/../../routes/console.php');
        }
    }
}
