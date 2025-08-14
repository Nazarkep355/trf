<?php

namespace App\Services;

use App\Models\BadUser;
use App\Models\BadUserAgent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Mockery\Exception;

class BanService
{


    public function banIP(string $ip): bool
    {

        if (BadUser::where('ip_address', $ip)->first()->status == 'banned') {
            return false;
        }

        $config = [
            'target' => 'ip',
            'value' => $ip
        ];

        $data = [
            'configuration' => $config,
            'mode' => 'block',
            'note' => 'This rule is enabled because of an event that occurred on date ' . Carbon::now() . '.'
        ];
        $authEmail = config('services.cloudflare.email');
        $authKey = config('services.cloudflare.auth_key');

        $response = Http::withHeaders(['Content-Type' => 'application/json',
            'X-Auth-Email' => $authEmail,
            'X-Auth-Key' => $authKey])
            ->post( config('cloudflare.url'),
                $data);

        if (isset($response['success']) && $response['success'] == true) {

            BadUser::where('ip_address', $ip)->update([
                'status' => 'banned',
                'banned_at' => Carbon::now(),
                'cloud_id' => $response['result']['id']
            ]);
            return true;
        } else {
            \Log::error($response->json());
            return false;
        }

    }

    public function unBanIp(string $ip): bool
    {

        $cloud_id = BadUser::where('ip_address', $ip)->first()->cloud_id;
        if (!empty($cloud_id)) {
            $response = Http::withHeaders(['Content-Type' => 'application/json',
                'X-Auth-Email' => config('services.cloudflare.email'),
                'X-Auth-Key' => config('services.cloudflare.auth_key')])
                ->delete( config('cloudflare.url') . '/' . $cloud_id);
            if (isset($response['success']) && $response['success'] == true) {
                BadUser::where('ip_address', $ip)->update(['status' => 'unbanned', 'cloud_id' => null]);
                return true;
            } else {
                \Log::error($response->json());
                return false;
            }

        }
        return true;
    }

    public function banUserAgent(string $userAgent, $insert = true)
    {
        $agentsRes = BadUserAgent::query()->whereNotNull('cloud_id')->get();

        $agents = $agentsRes->where('status','banned')->pluck('user_agent')->toArray();
//        if (in_array($userAgent, $agents)) {
//            throw new Exception("User agent already banned");
//        }

        $agents[] = $userAgent;
        $expression = '';
        $conditions = [];
        foreach ($agents as $agent) {
            $conditions[] = '(http.user_agent contains "' . $agent . '")';
        }
        $unbanList = $agentsRes->groupBy('cloud_id')->keys()->toArray();
//        \Log::info($unbanList);
//        dd($unbanList);
        if(!empty($unbanList)){
            foreach ($unbanList as $unban) {
                $response = Http::withHeaders(['Content-Type' => 'application/json',
                    'X-Auth-Email' => config('services.cloudflare.email'),
                    'X-Auth-Key' => config('services.cloudflare.auth_key')])
                    ->delete('https://api.cloudflare.com/client/v4/zones/b8c735bda31d18c7424ad9a2ae9224fc/rulesets/af6640ec345a4d6297226c4408f2e0d7/rules/'.$unban)
                    ->json();
                if(isset($response['success']) && $response['success'] == true) {
                    BadUserAgent::query()->where('cloud_id', $unban)->update(['status' => 'unbanned', 'cloud_id' => null]);
                } else {
                    \Log::error($response);
                    throw new Exception("Error banning user agent: ");
                }
            }

        }
        $expression = implode(' or ', $conditions);
        $banRquest = [
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
            "ref"=> "my_ref2",
            'expression' => $expression,
        ];
        $response = Http::withHeaders(['Content-Type' => 'application/json',
            'X-Auth-Email' => config('services.cloudflare.email'),
            'X-Auth-Key' => config('services.cloudflare.auth_key')])
            ->post('https://api.cloudflare.com/client/v4/zones/b8c735bda31d18c7424ad9a2ae9224fc/rulesets/af6640ec345a4d6297226c4408f2e0d7/rules',$banRquest)
            ->json();
//        dump($response);
        if (isset($response['success']) && $response['success'] == true) {

            $rules = $response['result']['rules'];
           $rule = array_values(array_filter($rules, function ($rule) use ($expression) {
                return $rule['expression'] == $expression;
            }))[0];
           \Log::info($rule);
           $agentsUpsert = [];
           $now = Carbon::now();
            foreach ($agents as $agent) {
                if(!$insert && $agent == $userAgent) {
                    continue;
                }
                $agentsUpsert[] = [
                    'user_agent' => $agent,
                    'status' => 'banned',
                    'banned_at' => $now,
                    'cloud_id' => $rule['id'],
                ];
            }
            \Log::info($agentsUpsert);
            BadUserAgent::query()->upsert($agentsUpsert, ['user_agent'], ['status', 'banned_at', 'cloud_id']);
            return ['banned_at' => $now,
                'cloud_id' => $rule['id']];
        } else {
            \Log::error($response);
            throw new Exception("Error banning user agent: ");
        }
    }


    public function unbanAgent(string $userAgent)
    {
        $agentModel = BadUserAgent::query()->where('user_agent', $userAgent)->firstOrFail();
        $response = Http::withHeaders(['Content-Type' => 'application/json',
            'X-Auth-Email' => config('services.cloudflare.email'),
            'X-Auth-Key' => config('services.cloudflare.auth_key')])
            ->get('https://api.cloudflare.com/client/v4/zones/b8c735bda31d18c7424ad9a2ae9224fc/rulesets/af6640ec345a4d6297226c4408f2e0d7')
            ->json();
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
                'X-Auth-Key' => config('services.cloudflare.auth_key')])
                ->delete('https://api.cloudflare.com/client/v4/zones/b8c735bda31d18c7424ad9a2ae9224fc/rulesets/af6640ec345a4d6297226c4408f2e0d7/rules/e9b6e2aae2674e239e3f39c63b569a2f')
                ->json();
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
                    'X-Auth-Key' => config('services.cloudflare.auth_key')])
                    ->post('https://api.cloudflare.com/client/v4/zones/b8c735bda31d18c7424ad9a2ae9224fc/rulesets/af6640ec345a4d6297226c4408f2e0d7/rules', $banRequest)
                    ->json();
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
