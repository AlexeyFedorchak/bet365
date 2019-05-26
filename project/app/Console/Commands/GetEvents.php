<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\UpcomingEvents;
use Carbon\Carbon;
use App\SyncKey;

class GetEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get upcoming events';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('running get:events - ' . Carbon::now());
        $client = new Client();
        $token = env('BETS_TOKEN');
        $sportId = env('SPORT_ID');

        $now = Carbon::now();
        $days = [
            'dayNow' => $now->format('Ymd'),
            'dayTomorrow' => $now->addDays(1)->format('Ymd'),
            'dayAfterTomorrow' => $now->addDays(2)->format('Ymd'),
        ];

        // $sync = new SyncKey();
        // $sync->getSync('App\UpcomingEvents');

        UpcomingEvents::truncate();
        $this->info('Old events removed successfully');

        foreach ($days as $day) {
            $response = $client->request('GET', 'https://api.betsapi.com/v2/events/upcoming?sport_id=' . $sportId . '&day=' . $day . '&token=' . $token);

            $events = json_decode($response->getBody()->getContents(), true);

            foreach ($events['results'] as $event) {

                UpcomingEvents::create(
                    [
                        'event_id' => $event['id'],
                        'time' => $event['time'] ?? '',
                        'time_status' => $event['time_status'],
                        'league_id' => $event['league']['id'],
                        'home_team_id' => $event['home']['id'],
                        'home_team_name' => $event['home']['name'],
                        'away_team_id' => $event['away']['id'],
                        'away_team_name' => $event['away']['name'],
                        'day' => $day,
                        'bet365_id' => $event['bet365_id'] ?? 0,
                        'sync_key' => '',
                    ]);

                $this->info(' Event ' . $event['id'] . ' saved (day: ' . $day . ')!');
            }            
        }

        \Log::info('get:events is finished - ' . Carbon::now());
    }
}
