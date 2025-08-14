<?php

namespace App\Services;

use Carbon\Carbon;

class ParsingService
{
    public static function parseLog(string $log) : ?array
    {
        $regex = <<<'R'
^
(?P<ip>\S+)
\s+\S+\s+\S+
\s+\[(?P<dt>[^\]]+)\]
\s+"(?P<method>\w+)
\s+(?P<url>\S+)
\s+(?P<proto>[^"]+)"
\s+(?P<status>\d{3})
\s+(?P<size>\d+)
\s+"(?P<ref>[^"]*)"
\s+"(?P<ua>[^"]*)"
$
R;
        if (!preg_match("/$regex/x", $log, $m)) {
            return false;
        }

        // Parse the timestamp (e.g. "23/Jun/2025:00:00:06 +0300")
        $dt = Carbon::createFromFormat('d/M/Y:H:i:s O', $m['dt']);
        if (!$dt) {
            return false;
        }
//        if(filter_var($m['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
//            $db = new \IP2Location\Database('/www/quant/traffic2/traffic/IP2LOCATION-LITE-DB3.BIN', \IP2Location\Database::FILE_IO);
//        } else {
            $db = new \IP2Location\Database('/www/quant/traffic2/traffic/IP2LOCATION-LITE-DB1.IPV6.BIN', \IP2Location\Database::FILE_IO);
//        }
        try{
            $geo = $db->lookup($m['ip'], \IP2Location\Database::ALL);
        } catch (\Throwable $e) {
            \Log::error($m['ip'] . " " . $e->getMessage());
//            throw $e;
        }

        return [
            'address' => $m['ip'],
            'timestamp' => $dt,
            'method' => $m['method'],
            'url' => $m['url'],
            'protocol' => $m['proto'],
            'status_code' => (int)$m['status'],
            'response_size' => (int)$m['size'],
            'referer' => $m['ref'],
            'country' => $geo['countryName'] ?? null,
            'city' => $geo['cityName'] ?? null,
            'user_agent' => $m['ua'],
        ];
    }
}
