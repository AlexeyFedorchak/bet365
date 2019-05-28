<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TelegramUser;
use Telegram\Bot\Api;

class TestTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test telegram';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegramUsers = TelegramUser::all();
        $telegram = new Api(env('TELEGRAM_API_KEY'));

        foreach ($telegramUsers as $telegramUser) {
            $telegram->sendMessage([
                'chat_id' => $telegramUser->chat_id, 
                'text' => 'Hello!',
            ]);

            $this->info('The test message was sent to user with ID ' . $telegramUser->chat_id);
        }
    }
}
