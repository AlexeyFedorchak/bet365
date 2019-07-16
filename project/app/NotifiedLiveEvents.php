<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotifiedLiveEvents extends Model
{
    protected $table = 'notified_live_events';

    protected $fillable = [
    	'event_id',
    	'market_odd',
    ];
}
