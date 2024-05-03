<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckSubscription;
use App\Jobs\AddOfferPoints;
use App\Jobs\SubscriptionReminder;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('otp:clean')->daily();
        // $schedule->job(new CheckSubscription)->everyMinute()->withoutOverlapping();
        $schedule->job(new AddOfferPoints)->everyMinute()->withoutOverlapping();
        $schedule->job(new SubscriptionReminder)->everyMinute();
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
