<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CheckAdminPermissions::class,
        \App\Console\Commands\ReconcilePaymobPayments::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        if (config('services.paymob.enabled')) {
            $schedule->command('payments:reconcile-paymob --minutes=15 --limit=100')
                ->everyFifteenMinutes()
                ->withoutOverlapping(10);
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
