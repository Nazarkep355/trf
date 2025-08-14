<?php

namespace App\Console\Commands;

use App\Models\BadUser;
use App\Models\BadUserAgent;
use App\Models\CheckLog;
use App\Models\LogLine;
use App\Models\TelegramUser;
use App\Models\WhiteListUser;
use App\Services\BanService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery\Exception;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $delays = [
        30 => 4,
        60 => 10,
        120 => 25,
        180 => 40,
        300 => 80,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->testBan();
    }

    protected function testBan()
    {
//        dd(BadUserAgent::query()->get()->toArray());

        $userAgent = 'quant_test2';
        $agentModel = BadUserAgent::query()->where('user_agent', $userAgent)->firstOrFail();
        $response = Http::withHeaders(['Content-Type' => 'application/json',
            'X-Auth-Email' => config('services.cloudflare.email'),
            'X-Auth-Key' => config('services.cloudflare.auth_key')]);
        $rule = array_values(array_filter($response['result']['rules'], function ($rule) use ($agentModel, $userAgent) {
            return $rule['id'] === $agentModel->cloud_id;
        }))[0];
        $allExprssions = explode(' or ', $rule['expression']);
        $otherExpressions = [];
        $hasExpr = false;
        foreach ($allExprssions as $expr) {
            if (trim($expr) === '(http.user_agent contains "' . $userAgent . '")') {
                $hasExpr = true;
            } else {
                $otherExpressions[] = trim($expr);
            }
        }
//        dd($hasExpr, implode(' or ',$otherExpressions));
        if ($hasExpr) {
            $response = Http::withHeaders(['Content-Type' => 'application/json',
                'X-Auth-Email' => config('services.cloudflare.email'),
                'X-Auth-Key' => config('services.cloudflare.auth_key')]);
            if (isset($response['success']) && $response['success'] == true) {
                $banRequest = [
                    'action' => "block",
                    "action_parameters" => [
                        "response" => [
                            "content" => "sorry, you are banned for test",
//                    "content" => "sorry, you are banned",
                            "content_type" => "text/plain",
                            "status_code" => 403
                        ]
                    ],
                    "description" => "some bots and frames",
                    "enabled" => true,
                    "ref" => "my_ref2",
                    'expression' => implode(' or ', $otherExpressions),
                ];
                $response = Http::withHeaders(['Content-Type' => 'application/json',
                    'X-Auth-Email' => config('services.cloudflare.email'),
                    'X-Auth-Key' => config('services.cloudflare.auth_key')]);
                $agents = BadUserAgent::query()
                    ->where('cloud_id', $agentModel->cloud_id)->pluck('user_agent')->toArray();
                $now = Carbon::now();
                if (isset($response['success']) && $response['success'] == true) {

                    $rules = $response['result']['rules'];

                    $agentsUpsert = [];

                    foreach ($agents as $agent) {
                        if ($agent == $userAgent) {
                            $agentsUpsert[] = [
                                'user_agent' => $agent,
                                'status' => 'unbanned',
                                'banned_at' => $now,
                                'cloud_id' => null
                            ];
                        } else {
                            $agentsUpsert[] = [
                                'user_agent' => $agent,
                                'status' => 'banned',
                                'banned_at' => $now,
                                'cloud_id' => $rule['id'],
                            ];
                        }

                    }
                    BadUserAgent::query()->upsert($agentsUpsert, ['user_agent'], ['status', 'banned_at', 'cloud_id']);
                    return ['banned_at' => $now,
                        'cloud_id' => $rule['id']];
                } else {

                    \Log::error($response);
                    $agentsUpsert = [];

                    foreach ($agents as $agent) {

                        $agentsUpsert[] = [
                            'user_agent' => $agent,
                            'status' => 'unbanned',
                            'banned_at' => $now,
                            'cloud_id' => null
                        ];
                    }
                    BadUserAgent::query()->upsert($agentsUpsert, ['user_agent'], ['status', 'banned_at', 'cloud_id']);
                    throw new Exception("Error banning user agent: " . $response);
                }
            }
        } else {
            \Log::info($userAgent . ' User agent is not banned');
        }
        BadUserAgent::query()->where('user_agent', $userAgent)->update(['status' => 'unbanned', 'cloud_id' => null]);
    }
}
