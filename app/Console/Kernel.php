<?php

namespace App\Console;

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
        Commands\InstallmentReminder::class,
        Commands\InsuranceReminder::class,
        Commands\LicenseReminder::class,
        Commands\InspectionVisitReminder::class,
        Commands\SigningVisitReminder::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('remind:installment')
            ->everyMinute();
        $schedule->command('remind:insurance')
            ->everyMinute();
        $schedule->command('remind:license')
            ->everyMinute();
        $schedule->command('remind:inspection_visit')
            ->everyMinute();
        $schedule->command('remind:signing_visit')
            ->everyMinute();
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
