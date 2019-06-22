<?php

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\TelegramUser;

class AddTelegramUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add telegram users';

    protected $password = 'Bet for future!';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('###');

        $telegram = new Api(env('TELEGRAM_API_KEY'));
        $response = $telegram->getUpdates();
        $usersChatIds = TelegramUser::all()->pluck('chat_id')->toArray() ?? [];
        
        $collected = collect($response)->pluck('message') ?? [];

        $usersMessages = [];
        foreach ($collected as $message) {
            $usersMessages[$message['chat']['id']] = $message['text'];
        }

        foreach ($usersMessages as $key => $userMessage) {

            if (in_array($key, $usersChatIds)) {
                $telegram->sendMessage([
                    'chat_id' => $key, 
                    'text' => 'You\'ve been attached already. You don\'t need to do it again.',
                ]);

                continue;
            }

            if ($userMessage != $this->password) {
                $telegram->sendMessage([
                    'chat_id' => $key, 
                    'text' => 'The key is not correct. You are not attached.',
                ]);

                continue;
            }

            TelegramUser::updateOrCreate(
                [
                    'chat_id' => $key,
                ], 
                [
                    'chat_id' => $key,
                ]);

                $telegram->sendMessage([
                    'chat_id' => $key, 
                    'text' => 'Welcome! You\'ve been attached! The message notfications are comming..' ,
                ]);

            $this->info('Added/updated user (ID: ' . $key . ').');
            \Log::info('New user: ' . $key);
        }
    }
}
