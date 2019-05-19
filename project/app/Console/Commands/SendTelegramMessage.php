<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\TelegramUser;

class SendTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message to telegram';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegramUsers = TelegramUser::all();
        $telegram = new Api(env('TELEGRAM_API_KEY'));

        $message = $this->argument('message');
        if (strpos($message, 'test:') !== FALSE) {
            //send test message...

            $readyMessage = str_replace('test:', '', $message);
            $message = 'Hi! I\'m your personal telegram bot! I wil notify your about bet365 updates!' . "\r\n" . 'Good luck in your betting!';
            
            foreach ($telegramUsers as $telegramUser) {
                $telegram->sendMessage([
                    'chat_id' => $telegramUser->chat_id, 
                    'text' => str_replace('test:', '', $message)
                ]); 

                $this->info('The test message was sent to user with ID ' . $readyMessage);
            }

        } else {
            //send real messages...

        }
    }
}
