<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use App\League;

class ParseBet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse-leagues {page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Common link for league parsing
     *
     * @var string
     */
    protected $leagueLink = 'https://betsapi.com/c/basketball/p.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $page = $this->argument('page');

        $crawler = $client->request('GET', $this->leagueLink . $page);
        $res = $crawler->filter('.league_n');
        foreach ($res as $item) {
            $name = $item->textContent;
            $fullUrl = $item->getElementsByTagName('a')->item(0)->getAttribute('href');
            $urlId = explode('/', $fullUrl)[1] ?? null;

            $league = League::updateOrCreate(
                [
                    'name' => $name,
                    'url_id' => $urlId,
                    'full_url' => $fullUrl,
                ],
                [
                    'name' => $name,
                    'url_id' => $urlId,
                    'full_url' => $fullUrl,
                ]);

            $this->info('Found league ' . $league->name . '. Added to db.');
        }

        $this->info('League list parsing is finished');
    }
}
