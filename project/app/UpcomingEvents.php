<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UpcomingEvents extends Model
{
    protected $table = 'upcoming_events';

    protected $fillable = [
    	'event_id',
    	'time',
    	'time_status',
    	'league_id',
    	'home_team_id',
    	'home_team_name',
    	'away_team_id',
    	'away_team_name',
    	'day',
    	'bet365_id',
        'sync_key',
    ];
}
