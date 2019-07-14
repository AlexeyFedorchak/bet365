<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use App\TelegramUsersLive;
use Telegram\Bot\Api;

class TelegramTestLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test telegram: live bot';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegramUsers = TelegramUsersLive::all();
        $telegram = new Api(env('TELEGRAM_API_KEY_LIVE'));

        foreach ($telegramUsers as $telegramUser) {
            $telegram->sendMessage([
                'chat_id' => $telegramUser->chat_id, 
                'text' => 'Hello!',
            ]);

            $this->info('The test message was sent to user with ID ' . $telegramUser->chat_id);
        }
    }
}
