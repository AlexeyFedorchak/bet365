<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use App\CheckedOdds;
use Carbon\Carbon;

class ClearCheckedOddsLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:odds:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old odds to prevent exceed memory exception';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::debug('Cleaner is running!');

        $checkedClearOddsQuery = CheckedOdds::where('created_at', '<', Carbon::now()->subHours(3));

        \Log::debug('Clearing - ' . $checkedClearOddsQuery->count() . ' odds!');

        $checkedClearOddsQuery->delete();

        \Log::debug('Cleared');
    }
}
