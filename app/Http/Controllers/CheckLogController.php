<?php

namespace App\Http\Controllers;

use App\Models\LogLine;
use Illuminate\Http\Request;

class CheckLogController extends Controller
{
    public function getLastUserAgent(Request $request)
    {
        $ip = $request->input('ip');
        $user_agent = LogLine::where('address', $ip)->orderBy('id', 'desc')
            ->limit(1)->pluck('user_agent')->first();
        if ($user_agent == null) {
            return response()->json(['error' => 'Not found'], 404);
        } else
            return $user_agent;
    }
}
