<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\WhiteListUser;
use Illuminate\Support\Facades\Http;

class TelegramCommunicationService
{

    public function sendMessageIfNeeded($logs): void {

            $defaultLogs = 0;
            $addresses = WhiteListUser::query()->pluck('ip_address')->toArray();
            foreach ($logs as $log) {
                if($log['address'] && !in_array($log['address'], $addresses)) {
                    $defaultLogs++;
                }
            }

            if ($defaultLogs > 300) {
                $token = config('services.telegram.bot_token');
                $admins = TelegramUser::query()->pluck('id')->toArray();
                $message = 'Щойно за 30 секунд було отримано '.$defaultLogs.' запитів';
                foreach ($admins as $admin) {
                    Http::get('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$admin.'&text='.$message);
                }

            }

    }

}
