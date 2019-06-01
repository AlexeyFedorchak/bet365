<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Odd;
use App\UpcomingEvents;
use App\Notification;
use Carbon\Carbon;
use App\CheckedOdds;

class Truncate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate events, odds, notifications';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Odd::truncate();
        UpcomingEvents::truncate();
        Notification::truncate();
        CheckedOdds::truncate();

        $this->info('Done');
    }
}
