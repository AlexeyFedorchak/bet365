<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\LiveScores;
use Carbon\Carbon;

class CheckScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:scores:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save scores every 5 seconds';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	\Log::debug('+++');

        $client = new Client();
        $token = env('BETS_TOKEN');
        $sportId = env('SPORT_ID');

        try {
            $response = $client->request('GET', 'https://api.betsapi.com/v2/events/inplay?sport_id=' . $sportId . '&token=' . $token . '&day=' . Carbon::now()->format('Ymd'));

            $events = json_decode($response->getBody()->getContents(), true)['results'];            
        } catch (\Exception $e) {
            $events = [];
        }

        foreach ($events as $event) {
            LiveScores::create([
                'event_id' => $event['id'],
                'scores' => $event['ss'],
            ]);
        }

        \Log::debug('===');
    }
}
