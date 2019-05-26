<?php

namespace App\Console\Commands\Parser;

use Illuminate\Console\Command;
use App\Matches;

use League\Csv\Reader;
use League\Csv\Writer;


class GetCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:csv {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get csv for some table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');
        
        //@TODO ot important but useful...
    }
}
