<?php

namespace App\Services;

use App\Models\BadUser;
use App\Models\CheckLog;
use App\Models\LogLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckingService
{
    private array $delays = [
        30 => 4,
        60 => 10,
        120 => 25,
        180 => 40,
        300 => 80,
    ];

    private array $dangerLevel = [
        4 => 'suspicious',
        10 => 'very suspicious',
        25 => 'require inspection',
        40 => 'enormous',
        80 => 'critical'
    ];
    private Carbon|null $now = null;

    public function checkRecentLogs()
    {
        $this->now = Carbon::now();
        $this->checkLogsForSomeTime(30);
        $this->checkLogsForSomeTime(60);
        $this->checkLogsForSomeTime(120);
        $this->checkLogsForSomeTime(180);
        $this->checkLogsForSomeTime(300);
    }

    private function checkLogsForSomeTime($seconds)
    {

        $before = Carbon::parse($this->now)->subSeconds($seconds);
        $innerQuery = LogLine::query()->where('timestamp','<',$this->now)
            ->where('timestamp','>',$before)
            ->groupBy('address','city','country')
            ->select(['address','city','country', \DB::raw('count(*) as count')]);
        $res = DB::query()->fromSub($innerQuery, 'logs')
            ->where('logs.count','>',$this->delays[$seconds])->get();
//        dd($res);
        $badUserUpsert = [];
        $checkLogUpsert = [];

        foreach ($res as $check) {
            $badUserUpsert[] = [
                'ip_address' => $check->address,
                'status' => $this->getDangerLevel($check->count),
                'city' => $check->city,
                'country' => $check->country,
//                'created_at' => $this->now,
                'updated_at' => $this->now
            ];
            $checkLogUpsert[] = [
                'address' => $check->address,
                'time_scope' => $seconds,
                'check_time' => $this->now,
                'number_of_requests' => $check->count,
            ];
        }
        foreach (array_chunk($badUserUpsert,3000) as $chunk) {
            BadUser::query()->whereNotIn('status',['banned','unbanned'])->upsert($chunk,['ip_address'],['status','updated_at']);
        }
        foreach (array_chunk($checkLogUpsert,3000) as $chunk) {
            CheckLog::query()->upsert($chunk,['address','check_time'],['number_of_requests']);
        }


    }

    private function getDangerLevel($count)
    {
        if($count >= 80) {
            return $this->dangerLevel[80];
        } elseif ($count >= 40) {
            return $this->dangerLevel[40];
        } elseif ($count >= 25) {
            return $this->dangerLevel[25];
        } elseif ($count >= 10) {
            return $this->dangerLevel[10];
        } elseif ($count >= 4) {
            return $this->dangerLevel[4];
        }
        else return  'suspicious';
    }



}
