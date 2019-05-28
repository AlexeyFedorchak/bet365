<?php

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use App\TelegramUser;
use App\Odd;
use App\Notification;
use Carbon\Carbon;

class SendTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send {notificationId}';

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

        $notification = Notification::where('id', $this->argument('notificationId'))->first();

        $isRed = false;
        if (strpos($notification->message, 'RED')) {
            $EmojiUtf8Byte = '\xF0\x9F\x94\xB4';
            $isRed = true;    
        } else{
            $EmojiUtf8Byte = '\xF0\x9F\x94\xB5';    
        }
        
        $pattern = '@\\\x([0-9a-fA-F]{2})@x';
        $emoji = preg_replace_callback($pattern, function ($captures) {
            return chr(hexdec($captures[1]));
            },$EmojiUtf8Byte
        );

        if ($isRed) {
            $message = str_replace('RED', $emoji, $notification->message ?? 'Undefined message. Contact support.');
        } else{
            $message = str_replace('GREEN', $emoji, $notification->message ?? 'Undefined message. Contact support.');
        }
        
        $chatIds = [];
        foreach ($telegramUsers as $telegramUser) {
            $telegram->sendMessage([
                'chat_id' => $telegramUser->chat_id, 
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            $chatIds[] = $telegramUser->chat_id;

            $this->info('The test message was sent to user with ID ' . $telegramUser->chat_id);
        }

        $notification->is_done = 1;
        $notification->chat_ids = json_encode($chatIds);
        $notification->save();

        $this->info('Notifications successfully updated!');
        \Log::info('Message sent - ' . Carbon::now());
    }
}