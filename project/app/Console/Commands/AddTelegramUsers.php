<?php

namespace App\Console\Commands;

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegram = new Api(env('TELEGRAM_API_KEY'));
        $response = $telegram->getUpdates();
        
        foreach ($response as $item) {
            if (isset($item['message']['chat']['id'])) {
                $chatId = $item['message']['chat']['id'];
                TelegramUser::updateOrCreate(
                    [
                        'chat_id' => $chatId,
                    ], 
                    [
                        'chat_id' => $chatId,
                    ]);                

                $this->info('Added/updated user (ID: ' . $chatId . ').');
            }
        }
    }
}
