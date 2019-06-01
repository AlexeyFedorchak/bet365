<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckedOdds extends Model
{
    protected $table = 'checked_odds';

    protected $fillable = [
    	'checked_odds_id',
    	'odd_market',
    	'event_id',
    ];
}
