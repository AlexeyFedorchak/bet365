<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $table = 'matches';

    protected $fillable = [
    	'slug',
    	'sync_key',
    	'full_url',
    	'league_id',
    	'date',
    ];
}
