<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use App\NotifiedLiveEvents;
use App\CheckedOddsLive;
use App\LiveScores;

class TruncateLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truncate:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate live tables';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        NotifiedLiveEvents::truncate();
        CheckedOddsLive::truncate();
        LiveScores::truncate();

        $this->info('Done');
    }
}
