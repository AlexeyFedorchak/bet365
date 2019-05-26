<?php

namespace App\Console\Commands\Parser;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\League;

class DispatchMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:dispatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch all commands for pulling leagues';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $leagues = League::all();

        $counter = 0;
        foreach($leagues as $league) {

            if ($counter === 150) break;

            $process = new Process('php artisan get:matches ' . $league->id); 
            $process->start();
            $this->info('Dispatched command with league: ' . $league->id);            

            $counter++;
        }
    }
}
