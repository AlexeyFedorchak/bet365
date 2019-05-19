<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    protected $table = 'leagues';

    protected $fillable = [
    	'name',
    	'url_id',
    	'full_url',
    ];
}
