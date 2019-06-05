<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\UpcomingEvents;
use App\Odd;
use App\SyncKey;
use Carbon\Carbon;
use App\CheckedOdds;
use App\MarketsOddConverter;
use Telegram\Bot\Api;
use App\TelegramUser;

class CheckOddsOptimized extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:odds:optimized';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimized check odds command for slow server';

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
        \Log::info('Running check:odds:optimized - ' . Carbon::now());
        $client = new Client();
        $token = env('BETS_TOKEN');
        $sportId = env('SPORT_ID');
        $telegram = new Api(env('TELEGRAM_API_KEY'));

        $inPlayEventsIds = [];
        try {
            $response = $client->request('GET', 'https://api.betsapi.com/v2/events/inplay?sport_id=' .  $sportId . '&token=' . $token);
            $inPlayEvents = json_decode($response->getBody()->getContents(), true)['results'];
            $inPlayEventsIds = collect($inPlayEvents)->pluck('id')->toArray();
        } catch (\Exception $e) {
            $this->info($e->getMessage());
        }

        $inPlayEventsIds = array_map(function ($item) { return (int) $item; }, $inPlayEventsIds);

        UpcomingEvents::whereIn('event_id', $inPlayEventsIds)->get();
        $this->info('Inplay events are removed! Continue...');

        $events = UpcomingEvents::all();
        $checkedOdds = CheckedOdds::all();
        $telegramUsers = TelegramUser::all();
        $checkedOddsId = $checkedOdds->pluck('checked_odds_id')->toArray();

        $now = Carbon::now();

        $checkedButNotSaved = collect();
        foreach ($events as $event) {
            $startTime = Carbon::parse(date('Y-m-d h:i:s', $event->time));
            $diffInHours = $startTime->diffInHours($now);

            if ($diffInHours > 12) continue;

            try {
                $response = $client->request('GET', 'https://api.betsapi.com/v2/event/odds?token=' . $token . '&event_id=' . $event->event_id);

                $odds = json_decode($response->getBody()->getContents(), true)['results']['odds'] ?? [];
            } catch(\Exception $e) {
                $this->info($e->getMessage());
                $odds = [];
            }

            foreach ($odds as $key => $oddMarket) {
                if (!in_array($key, $this->oddMarkets)) continue;

                foreach ($oddMarket as $oddKey => $odd) {
                    if (in_array($odd['id'], $checkedOddsId)) continue;

                    $addOddTime = Carbon::createFromTimestampUTC($odd['add_time']);
                    $startEventTime = Carbon::createFromTimestampUTC($event->time);
                    if ($addOddTime >= $startEventTime) continue;

                    if ($oddKey > 0) {
                        $to = (float) ($odd['handicap'] ?? 0);
                        $from = (float) ($oddMarket[$oddKey - 1]['handicap'] ?? 0);

                        $handicapDiff = abs($to - $from);

                        if ($handicapDiff >= 2) {
                            $eventId = $event->event_id;
                            $checkedOddsFiltered = $checkedOdds
                                ->filter(function ($item) use ($key, $eventId) {
                                    return $item->odd_market == $key && $item->event_id == $eventId;
                                });

                            $oddNotSavedChecked = $checkedButNotSaved
                                ->filter(function ($item) use ($key, $event) {
                                    return $item['odd_market'] == $key && $item['event_id'] == $event->event_id;
                                });

                            $isRed = false;
                            if (($oddNotSavedChecked->count() >= 1)
                                || ($checkedOddsFiltered->count() >= 1)) {
                                $isRed = true;
                            }

                            $checkedButNotSaved->push([
                                'odd_market' => $key,
                                'event_id' => $event->event_id
                            ]);

                            $this->sendMessage($isRed, $event, $key, $handicapDiff, $from, $to, $telegramUsers, $telegram);
                        }                        
                    }

                    CheckedOdds::create([
                         'checked_odds_id' => $odd['id'],
                         'odd_market' => $key,
                         'event_id' => $event->event_id
                    ]);

                    $this->info('Odd with ID ' . $odd['id'] . ' is checked and saved!');
                }
            }
        }

        \Log::info('Finished check:odds:optimized - ' . Carbon::now());
    }

    private function sendMessage($isRed, $event, $key, $handicapDiff, $from, $to, $telegramUsers, $telegram)
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
            . $event->event_id 
            . '/' 
            . str_replace(' ', '-', $event->home_team_name)
            . '-v-'
            . str_replace(' ', '-', $event->away_team_name);

        $marketOdd = MarketsOddConverter::convert($key);

        $message = 
            '<i>' . $emoji . '</i>' . "\r\n"
            . '<i>It seems, there is something worthy to check...</i>' . "\r\n" . '<b>' . $marketOdd . '</b> has been changed in <b>' . $handicapDiff . '</b> points. Range: from ' . $to . ' to ' . $from . '. ' . $event->home_team_name . ' vs ' . $event->away_team_name . ' - ' . Carbon::createFromTimestampUTC($event->time) . ' (UTC). (<a href="' . $link . '">Link to the event</a>)';

        foreach ($telegramUsers as $telegramUser) {
            $telegram->sendMessage([
                'chat_id' => $telegramUser->chat_id, 
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            $this->info('The notification message was sent to user with ID ' . $telegramUser->chat_id);
        }

        \Log::info('Message sent - ' . Carbon::now());
    }
}
