<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'sent_notifications';

    protected $fillable = [
    	'odd_id',
    	'event_id',
    	'chat_ids',
    	'odd_type',
    	'message',
    	'is_done',
    ];
}

