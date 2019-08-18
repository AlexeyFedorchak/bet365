<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Telegram\Bot\Api;
use App\TelegramUsersLive;
use Carbon\Carbon;
use App\LiveScores;
use App\CheckedOddsLive;
use App\NotifiedLiveEvents;
use App\MarketsOddConverter;
use App\CheckedOdds;

class CheckOddsOptimizedLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:odds:optimized:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimized checking odds by using ss in odds';

    protected $oddMarkets = [
        '18_2',
        '18_3',
        '18_5',
        '18_6',
        '18_8',
        '18_9',
    ];

    protected $baseLink = 'https://betsapi.com/rs/bet365/';

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
        $telegram = new Api(env('TELEGRAM_API_KEY_LIVE'));

        try {
            $response = $client->request('GET', 'https://api.betsapi.com/v2/events/inplay?sport_id=' . $sportId . '&token=' . $token . '&day=' . Carbon::now()->format('Ymd'));

            $events = json_decode($response->getBody()->getContents(), true)['results'];            
        } catch (\Exception $e) {
            	try {
            		\Log::debug('Response error: events!' . $e->getMessage());	
            	} catch(\Exception $e) {}

            $events = [];
        }

        $checkedOdds = CheckedOdds::all();
        $telegramUsers = TelegramUsersLive::all();
        $checkedOddsId = $checkedOdds->pluck('checked_odds_id')->toArray();

        $now = Carbon::now();
        
        $checkedButNotSaved = collect();
        $oddCounter = 0;
        foreach ($events as $event) {
            try {
                $response = $client->request('GET', 'https://api.betsapi.com/v2/event/odds?token=' . $token . '&event_id=' . $event['id']);

                $odds = json_decode($response->getBody()->getContents(), true)['results']['odds'] ?? [];

            } catch(\Exception $e) {
            	try {
            		\Log::debug('Response error: odds!' . $e->getMessage());	
            	} catch(\Exception $e) {}
            	
                $odds = [];
            }

            foreach ($odds as $key => $oddMarket) {
                if (!in_array($key, $this->oddMarkets)) continue;
                
                if (count($oddMarket) > 0 && 
                    is_null($oddMarket[count($oddMarket) - 1]['handicap'])) 
                    array_pop($oddMarket);

                foreach ($oddMarket as $oddKey => $odd) {
                    if (in_array($odd['id'], $checkedOddsId)) continue;
                    if (is_null($odd['handicap'])) continue;

                    $addOddTime = Carbon::createFromTimestampUTC($odd['add_time']);
                    $startEventTime = Carbon::createFromTimestampUTC($event['time']);
                    if ($addOddTime->diffInHours($startEventTime) > 12) continue;
                    
                    if (!isset($oddMarket[$oddKey + 1])) continue;

                    $to = (float) ($odd['handicap'] ?? 0);
                    $from = (float) ($oddMarket[$oddKey + 1]['handicap'] ?? 0);

                    $handicapDiff = abs($to - $from);

                    if ($handicapDiff >= 2) {
                        $eventId = $event['id'];
                        $checkedOddsFiltered = $checkedOdds
                            ->filter(function ($item) use ($key, $eventId) {
                                return $item->odd_market == $key && $item->event_id == $eventId;
                            });

                        $oddNotSavedChecked = $checkedButNotSaved
                            ->filter(function ($item) use ($key, $event) {
                                return $item['odd_market'] == $key && $item['event_id'] == $event['id'];
                            });

                        $isRed = false;
                        if (($oddNotSavedChecked->count() >= 1)
                            || ($checkedOddsFiltered->count() >= 1)) {
                            $isRed = true;
                        }

                        $checkedButNotSaved->push([
                            'odd_market' => $key,
                            'event_id' => $event['id']
                        ]);

						$currentScoresStamp = explode(':', $odd['ss']);

						if (count($currentScoresStamp) < 2)
							continue;

						$prevScoresStamp = explode(':', $oddMarket[$oddKey + 1]['ss']);

						if (count($prevScoresStamp) < 2)
							continue;

	                    $marketOdd = MarketsOddConverter::convert($oddKey);
	                    if (strpos($marketOdd, 'Total Points')) {
	                        $scoreDiff = abs(($currentScoresStamp[0] + $currentScoresStamp[1])
	                            - ($prevScoresStamp[0] + $prevScoresStamp[1]));
	                    } else {
	                        $scoreDiff = abs(abs($currentScoresStamp[0] - $currentScoresStamp[1])
	                            - abs($prevScoresStamp[0] - $prevScoresStamp[1]));
	                    }

	                    if (($handicapDiff - $scoreDiff) < 2)
	                    	continue;
                        
                        $this->sendMessage(
                        	$isRed, 
                        	$event, 
                        	$key, 
                        	$handicapDiff, 
                        	$from, 
                        	$to, 
                        	$telegramUsers, 
                        	$telegram,
					    	$scoreDiff, 
					    	$currentScoresStamp, 
					    	$prevScoresStamp
                        );
                    }

                    \Log::debug($event['id'] . ' - ' . $handicapDiff . ' (' . $odd['id'] . ')');

                    CheckedOdds::create([
                         'checked_odds_id' => $odd['id'],
                         'odd_market' => $key,
                         'event_id' => $event['id']
                    ]);

                    $oddCounter++;
                    $this->info('Odd with ID ' . $odd['id'] . ' is checked and saved!)');
                }
            }
        }

        \Log::debug('...');
    }

    private function sendMessage(
    	$isRed, 
    	$event, 
    	$key, 
    	$handicapDiff, 
    	$from, 
    	$to, 
    	$telegramUsers, 
    	$telegram,
    	$scoreDiff, 
    	$currentScoresStamp, 
    	$prevScoresStamp
    ) {
        if ($isRed) {
            $EmojiUtf8Byte = '\xF0\x9F\x94\xB4';
        } else {
            $EmojiUtf8Byte = '\xF0\x9F\x94\xB5';
        }

        $pattern = '@\\\x([0-9a-fA-F]{2})@x';
        $emoji = preg_replace_callback($pattern, function ($captures) {
                return chr(hexdec($captures[1]));
            },$EmojiUtf8Byte
        );

        $link = $this->baseLink 
            . $event['id']
            . '/' 
            . str_replace(' ', '-', $event['home']['name'])
            . '-v-'
            . str_replace(' ', '-', $event['away']['name']);

        $marketOdd = MarketsOddConverter::convert($key);

        $message = 
            '<i>' . $emoji . '</i>' . "\r\n"
            . '<i>It seems, there is something worthy to check...</i>' . "\r\n" 
            . 'The difference between scores and handicap for <b>' . $marketOdd . ': '
            . abs($handicapDiff - $scoreDiff) . '</b>' . '. '
            . 'Scores: (' . ($prevScoresStamp[0] ?? 0) . '-' . ($prevScoresStamp[1] ?? 0) . ')'
            . ' => ' . '(' . ($currentScoresStamp[0] ?? 0) . '-' . ($currentScoresStamp[1] ?? 0) . '). '
            . 'Handicap range: (' . $from . ') => (' . $to . '). '
            . $event['home']['name'] . ' vs ' . $event['away']['name'] . ' - ' 
            . Carbon::createFromTimestampUTC($event['time']) . ' (UTC). '
            . 'League: ' . $event['league']['name'] . '. (<a href="' . $link . '">Link to the event</a>).';

        foreach ($telegramUsers as $telegramUser) {
            try {
                $telegram->sendMessage([
                    'chat_id' => $telegramUser->chat_id, 
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);

                $this->info('The notification message was sent to user with ID ' . $telegramUser->chat_id);

            } catch (\Exception $e) {
                $this->info('BROKEN MESSAGE: ' . $telegramUser->chat_id);
            }
        }

        \Log::info('Message sent - ' . Carbon::now());
    }
}