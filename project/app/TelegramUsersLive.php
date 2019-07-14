<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramUsersLive extends Model
{
    protected $table = 'telegram_live_users';

    protected $fillable = [
    	'chat_id',
    ];
}
