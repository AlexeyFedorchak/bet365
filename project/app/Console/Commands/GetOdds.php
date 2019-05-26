<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\UpcomingEvents;
use App\Odd;
use App\SyncKey;

class GetOdds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:odds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get spread values for upcoming events';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $token = env('BETS_TOKEN');
        $sportId = env('SPORT_ID');

        //$sync = SyncKey::all()->last();
        $events = UpcomingEvents::all();
        foreach ($events as $event) {
            try {
                $response = $client->request('GET', 'https://api.betsapi.com/v2/event/odds?token=' . $token . '&event_id=' . $event->event_id);

                $odds = json_decode($response->getBody()->getContents(), true);
            } catch(\Exception $e) {
                $this->info($e->getMessage());
            }

            if (isset($odds['results']['odds'])) {
                foreach ($odds['results']['odds'] as $key => $oddMarket) {
                    foreach ($oddMarket as $odd) {

                        $isOddExists = Odd::where('odd_id', $odd['id'])->exists();
                        //$this->info('Processing odd: ' . $odd['id'] . '. Status: ' . (string) $isOddExists);

                        if (!$isOddExists) {
                            Odd::create([
                                'odd_id' => $odd['id'],
                                'home_od' => $odd['home_od'] ?? $odd['over_od'] ?? null,
                                'away_od' => $odd['away_od'] ?? $odd['under_od'] ?? null,
                                'odd_ss' => $odd['ss'] ?? null,
                                'time_str' => $odd['time_str'] ?? null,
                                'add_time' => $odd['add_time'] ?? null,
                                'handicap' => $odd['handicap'] ?? null,
                                'odd_market' => $key,
                                'event_id' => $event->event_id,
                                'is_checked' => 0,
                            ]);         

                            $this->info('Odd ' . $odd['id'] . '(' . ($odd['handicap'] ?? 'missing handicap') . ') is added!');
                        }
                    }
                }                
            }
        }
    }
}
