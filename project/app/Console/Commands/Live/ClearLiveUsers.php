<?php

namespace App\Console\Commands\Live;

use Illuminate\Console\Command;
use App\TelegramUsersLive;

class ClearLiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:clear:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate live users table';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TelegramUsersLive::truncate();
        $this->info('Done');
    }
}
