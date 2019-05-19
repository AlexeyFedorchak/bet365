<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use App\League;
use App\Match;
use App\SyncKey;

class ParseLeague extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-league-matches {leagueId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get future matches of some league';

    protected $baseLeagueUrl = 'https://betsapi.com';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $league = League::where('id', $this->argument('leagueId'))->first();

        if (is_null($league)) die('No correct league provided');

        $client = new Client();
        $crawler = $client->request('GET', $this->baseLeagueUrl . $league->full_url);
        $tr = $crawler->filter('.table-sm')->filter('tbody')->filter('tr');

        foreach ($tr as $item) {
            $children = $item->childNodes;
            $date = $children->item(0)->getAttribute('data-dt');

            $lastChild = $children->item(6);
            $matchStatus = trim(str_replace("\n", '', $lastChild->textContent));
            $fullUrl = $lastChild->getElementsByTagName('a')
                            ->item(0)
                            ->getAttribute('href');

    
            $syncKey = SyncKey::where('id', '>', 0)
                            ->orderBy('id', 'DESC')
                            ->first()
                            ->key ?? 'error';


            $matchName = trim(str_replace("\n", '', $children->item(4)->textContent));
            
            if ($matchStatus === 'View') {
                $slug = explode('/', $fullUrl)[3] ?? '';

                Match::updateOrCreate(
                    [
                        'slug' => $slug,
                        'date' => $date,
                        'league_id' => $league->id,
                        'full_url' => $fullUrl,
                    ], 
                    [
                        'slug' => $slug,
                        'date' => $date,
                        'league_id' => $league->id,
                        'full_url' => $fullUrl,
                        'sync_key' => $syncKey
                    ]);

                $this->info('Found future match: ' . $slug . '. Saved.');
            }
        }
    }
}
