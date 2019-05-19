<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\SyncKey;

class DispatchMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leagues:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run parse of every league as single process';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sync = new SyncKey();
        $sync->getSync('App\Match');

        //150 - is limitation of comp | on server it has to be set on 1000...
        for($i = 0; $i < 150; $i++) {
            $process = new Process('php artisan parse-leagues ' . $i); 
            $process->start();
            $this->info('Dispatched command with page: ' . $i);
        }
    }
}
