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

class CheckOddsEventsLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:odds:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check odds in live';

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
        \Log::debug('@@@');
        
        $client = new Client();
        $token = env('BETS_TOKEN');
        $sportId = env('SPORT_ID');
        $telegram = new Api(env('TELEGRAM_API_KEY_LIVE'));
        $telegramUsers = TelegramUsersLive::all();

        try {
            $response = $client->request('GET', 'https://api.betsapi.com/v2/events/inplay?sport_id=' . $sportId . '&token=' . $token . '&day=' . Carbon::now()->format('Ymd'));

            $events = json_decode($response->getBody()->getContents(), true)['results'];            
        } catch (\Exception $e) {
            $events = [];
        }

        $liveScores = LiveScores::all();
        $checkedOddsIds = CheckedOddsLive::all()->pluck('odd_id')->toArray();

        foreach ($events as $event) {
            LiveScores::create([
                'event_id' => $event['id'],
                'scores' => $event['ss'],
            ]);

            $currentLiveScore = new LiveScores();
            $currentLiveScore->event_id = $event['id']; 
            $currentLiveScore->scores = $event['ss'];
            $currentLiveScore->created_at = Carbon::now();
            $liveScores->push($currentLiveScore);

            try {
                $response = $client->request('GET', 'https://api.betsapi.com/v2/event/odds?token=' . $token . '&event_id=' . $event['id']);

                $oddsOfMarkets = json_decode($response->getBody()->getContents(), true)['results']['odds'] ?? [];
            } catch(\Exception $e) {
                $oddsOfMarkets = [];
            }

            foreach ($oddsOfMarkets as $market => $oddsOfMarket) {
                if (!in_array($market, $this->oddMarkets)) continue;

                foreach ($oddsOfMarket as $key => $odd) {
                    if (in_array($odd['id'], $checkedOddsIds)) continue;
                    if (!isset($odd['handicap']) || is_null($odd['handicap'])) continue;
                    if ($odd['add_time'] < $event['time']) continue;
                    if ($oddsOfMarket[$key + 1]['add_time'] < $event['time']) continue;

                    $to = (float) $odd['handicap'];
                    $from = (float) $oddsOfMarket[$key + 1]['handicap'];
                    $handicapDiff = abs($to - $from);

                    if ($handicapDiff < 2) continue;

                    $eventScores = $liveScores->where('event_id', $event['id']);
                            
                    $currentOddTime = Carbon::createFromTimestampUTC($odd['add_time']);
                    $previousOddTime = Carbon::createFromTimestampUTC($oddsOfMarket[$key + 1]['add_time']);
                    $scoresBeforePrev = $eventScores->where('created_at', '<', $previousOddTime)->last();
                    $scoresBeforeCurrent = $eventScores->where('created_at', '<', $currentOddTime)->last();

                    if (is_null($scoresBeforePrev) || is_null($scoresBeforeCurrent))
                        continue;

                    if ($scoresBeforePrev->created_at == $scoresBeforeCurrent->created_at)
                        continue;

                    $currentScoresStamp = explode('-', $scoresBeforePrev->scores);
                    $prevScoresStamp = explode('-', $scoresBeforeCurrent->scores);
                    
                    $marketOdd = MarketsOddConverter::convert($market);
                    if (strpos($marketOdd, 'Total Points')) {
                        $scoreDiff = abs(($currentScoresStamp[0] + $currentScoresStamp[1])
                            - ($prevScoresStamp[0] + $prevScoresStamp[1]));
                    } else {
                        $scoreDiff = abs(abs($currentScoresStamp[0] - $currentScoresStamp[1])
                            - abs($prevScoresStamp[0] - $prevScoresStamp[1]));
                    }

                    if ($scoreDiff < 2) continue;
                    if (abs($handicapDiff - $scoreDiff) < 2) continue;
                    \Log::debug('Diff: ' . ($handicapDiff - $scoreDiff));

                    $isNotified = NotifiedLiveEvents::where('event_id', $event['id'])
                        ->where('market_odd', $market)
                        ->exists();

                    $isRed = false;
                    if ($isNotified) $isRed = true;

                    $this->sendMessage($isRed, $event, $market, $handicapDiff, $from, $to, $telegramUsers, $telegram, $scoreDiff, $currentScoresStamp, $prevScoresStamp);

                    CheckedOddsLive::create(
                        [
                            'odd_id' => $odd['id'],
                            'market_odd' => $market,
                        ]);

                    NotifiedLiveEvents::create(
                        [
                            'event_id' => $event['id'],
                            'market_odd' => $market,
                        ]);
                }
            }
        }

        \Log::debug('...');
    }

    private function sendMessage($isRed, $event, $key, $handicapDiff, $from, $to, $telegramUsers, $telegram, $scoreDiff, $currentScoresStamp, $prevScoresStamp)
    {
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
            . 'Scores: (' . ($currentScoresStamp[0] ?? 0) . '-' . ($currentScoresStamp[1] ?? 0) . ')'
            . ' => ' . '(' . ($prevScoresStamp[0] ?? 0) . '-' . ($prevScoresStamp[1] ?? 0) . '). '
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
