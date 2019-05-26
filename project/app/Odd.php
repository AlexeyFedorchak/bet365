<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    protected $table = 'odds';

    protected $fillable = [
    	'odd_id',
    	'home_od',
    	'away_od',
    	'odd_ss',
    	'time_str',
    	'add_time',
    	'handicap',
        'odd_market',
        'event_id',
        'is_checked',
    ];
}
