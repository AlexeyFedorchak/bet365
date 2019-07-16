<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckedOddsLive extends Model
{
    protected $table = 'checked_odds_live';

    protected $fillable = [
    	'odd_id',
    	'market_odd',
    ];
}
