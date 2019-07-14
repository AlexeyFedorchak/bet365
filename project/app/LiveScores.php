<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiveScores extends Model
{
    protected $table = 'live_scores';

    protected $fillable = [
    	'event_id',
    	'scores'
    ];
}
