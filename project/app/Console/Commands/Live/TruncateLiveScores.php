<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use App\LiveScores;

class TruncateLiveScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truncate:scores:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily truncate all scores';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        LiveScores::truncate();
    }
}
