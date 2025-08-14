<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAgentController extends Controller
{

    public function banUserAgent(Request $request){
        $userAgent = $request->input('user_agent');
        $banService = new \App\Services\BanService();
        $banService->banUserAgent($userAgent);
        return response(['success' => true, 'message' => 'User agent banned successfully.']);
    }
}
