<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Parser\ParseBet::class,
        Commands\Parser\DispatchMatches::class,
        Commands\Parser\ParseLeague::class,
        Commands\Parser\DispatchLeagues::class,
        Commands\Telegram\SendTelegramMessage::class,
        Commands\Telegram\AddTelegramUsers::class,
        Commands\Parser\GetCsv::class,
        Commands\Parser\ParseMatch::class,
        Commands\GetEvents::class,
        Commands\GetOdds::class,
        Commands\CheckOdds::class,
        Commands\TestCronLogs::class,
        Commands\Truncate::class,
        Commands\TestTelegram::class,
        Commands\CheckOddsOptimized::class,
        Commands\CheckOddsEventsRealTime::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('check:odds:events:realtime')
                 ->everyMinute();

        $schedule->command('telegram:update')
                 ->everyThirtyMinutes();
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