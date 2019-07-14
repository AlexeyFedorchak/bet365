<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\TelegramUsersLive;

class AddTelegramLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:update:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add telegram users to live bot';

    protected $password = 'One life, one bet!';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('###');

        $telegram = new Api(env('TELEGRAM_API_KEY_LIVE'));
        $response = $telegram->getUpdates();
        $usersChatIds = TelegramUsersLive::all()->pluck('chat_id')->toArray() ?? [];
        
        $collected = collect($response)->pluck('message') ?? [];

        $usersMessages = [];
        foreach ($collected as $message) {
            $usersMessages[$message['chat']['id']] = $message['text'];
        }

        $usedKeys = [];
        foreach ($usersMessages as $key => $userMessage) {
            if (in_array($key, $usersChatIds)) continue;
            if (in_array($key, $usedKeys)) continue;

            if ($userMessage != $this->password) {
                $telegram->sendMessage([
                    'chat_id' => $key, 
                    'text' => 'The key is not correct. You are not attached.',
                ]);

                continue;
            }

            TelegramUsersLive::updateOrCreate(
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

            $usedKeys[] = $key;
            $this->info('Added/updated user (ID: ' . $key . ').');
            \Log::info('New user: ' . $key);
        }
    }
}
