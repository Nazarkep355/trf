<?php

namespace App\Models;

use App\Services\BanService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mockery\Exception;

class BadUserAgent extends Model
{
    use HasFactory;
    public $timestamps = false;


    public static function boot()
    {
        parent::boot();
        static::creating(function (BadUserAgent $agent) {
            $status = $agent->status ?? 'unbanned';
//            dd($agent);
            if (!in_array($status, ['banned', 'unbanned'])) {
                $status = 'unbanned';
            }
//            dd('BadUserAgent::creating', $status, $agent->useragent);
            if ($status === 'banned') {
                $banService = new BanService();
                $result = $banService->banUserAgent($agent->user_agent, false);
                $agent->cloud_id = $result['cloud_id'] ?? null;
                $agent->banned_at = $result['banned_at'] ?? null;
            }

        });

        static::updating(function (BadUserAgent $agent) {
            \Log::info('updating');
            $changed = $agent->getDirty();
            if(in_array('status',array_keys($changed))){
                if($changed['status'] === 'banned') {
                    $banService = new BanService();
                    $banService->banUserAgent($agent->user_agent);
//                    $updated = BadUserAgent::query()->where('user_agent', $agent->user_agent)->firstOrFail();
//                    \Log::info($updated);
//                    $agent->cloud_id = $updated->cloud_id;
//                    $agent->banned_at = $updated->banned_at;
                }
                //todo :make unban
                elseif ($changed['status'] === 'unbanned') {
                    $banService = new BanService();
                    $banService->unbanAgent($agent->user_agent);
                }
            }
        });
    }
}
