<?php

namespace App\Http\Controllers;

use App\Models\LogLine;
use App\Services\CheckingService;
use App\Services\ParsingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ParsingController extends Controller
{

    public function parseLogs(Request $request)
    {
        $lines = $request->get('array');
        $insert = [];
        foreach ($lines as $line) {
            $insert[] = ParsingService::parseLog($line);
        }
        LogLine::query()->insert($insert);
        $telegramService = new \App\Services\TelegramCommunicationService();
        $telegramService->sendMessageIfNeeded($insert);
        $checkService = new CheckingService();
        $checkService->checkRecentLogs();

    }


}
