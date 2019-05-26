<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Odd;
use App\UpcomingEvents;
use Carbon\Carbon;
use App\SyncKey;
use App\Notification;
use Symfony\Component\Process\Process;
use App\MarketsOddConverter;

class CheckOdds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:odds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check odds to find out is there something worthy';

    protected $filter = 2;

    protected $baseLink = 'https://betsapi.com/rs/bet365/';

    /**
     * Execute the console command.
     * @TODO Rewrite this! Make it more complex and easier   
     *
     * @return mixed
     */
    public function handle()
    {
        //$sync = SyncKey::all()->last();
        \Log::info('running check:odds - ' . Carbon::now());
        $events = UpcomingEvents::all();

        foreach ($events as $event) {

            $now = Carbon::now();
            $startTime = Carbon::parse(date('Y-m-d h:i:s', $event->time));
            $diffInHours = $startTime->diffInHours($now);

            if ($diffInHours > 12) continue;

            $this->info('Processing event: ' . $event->event_id);

            $lastCheckedOdd = Odd::where('event_id', $event->event_id)
                ->where('is_checked', 1)
                ->orderBy('id', 'DESC')
                ->first();

            $notCheckedOdds = Odd::where('event_id', $event->event_id)
                ->where('is_checked', 0)
                ->orderBy('id', 'ASC')
                ->get();

            $isSentMessage = true;
            if (is_null($lastCheckedOdd)) $isSentMessage = false;

            $oddsHistory = [];
            foreach ($notCheckedOdds as $key => $odd) {

                $oddsHistory[] = $odd;
                if ($key > 0) $lastCheckedOdd = $oddsHistory[$key-1];

                $homeOd = (float) $odd->home_od - ((float) ($lastCheckedOdd->home_od ?? 0));
                $awayOd = (float) $odd->away_od - ((float) ($lastCheckedOdd->away_od ?? 0));
                $handicap = (float) $odd->handicap - ((float) ($lastCheckedOdd->handicap ?? 0));

                $sustainableDiffs = [];
                if ($homeOd > $this->filter) $sustainableDiffs['home_od'] = $homeOd;
                if ($awayOd > $this->filter) $sustainableDiffs['away_od'] = $awayOd;
                if ($handicap > $this->filter) $sustainableDiffs['handicap'] = $handicap;

                if (count($sustainableDiffs) > 0 && $isSentMessage) {
                    foreach ($sustainableDiffs as $key => $diff) {
                        $isNotificationsSent = Notification::where('odd_type', $key)->where('event_id', $event->event_id)->exists();

                            $color = 'GREEN';
                            if ($isNotificationsSent) $color = 'RED';
                            
                            $link = $this->baseLink 
                                . $event->event_id 
                                . '/' 
                                . str_replace(' ', '-', $event->home_team_name)
                                . '-v-'
                                . str_replace(' ', '-', $event->away_team_name);

                            $marketOdd = MarketsOddConverter::convert($odd->odd_market);

                            $messageForDB = 
                              '<i>' . $color . '</i>' . "\r\n"
                            . '<i>It seems, there is something worthy to check...</i>' . "\r\n" . '<b>' . $key . ' (' . $marketOdd . ')</b> has been changed in <b>' . $diff . '</b> points (<a href="' . $link . '">Link to the event</a>)
                            ';

                            $notification = Notification::create([
                                'event_id' => $event->event_id,
                                'odd_id' => $odd->odd_id,
                                'chat_ids' => '',
                                'odd_type' => $key,
                                'message' => $messageForDB,
                                'is_done' => 0,
                            ]);

                            $this->info('Found sustainable changes. Sending notification to users.');

                            $process = new Process('php artisan telegram:send ' . $notification->id); 
                            $process->start();
                    }
                }

                $odd->is_checked = 1;
                $odd->save();
                $this->info('Odd is checked');
            }
        }

        \Log::info('check:odds if finished - ' . Carbon::now());
    }
}
