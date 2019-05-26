<?php

namespace App\Console\Commands\Parser;

use Illuminate\Console\Command;
use Goutte\Client;

class ParseMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:matchdata {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse every match';

    protected $baseLeagueUrl = 'https://betsapi.com';

    protected $downloadParams = '?download_csv=18_2';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('url');

        $client = new Client();
        $crawler = $client->request('GET', $this->baseLeagueUrl . $url . $this->downloadParams);
    }
}
