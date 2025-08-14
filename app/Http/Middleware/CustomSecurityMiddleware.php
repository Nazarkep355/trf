<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomSecurityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if($request->header('my-sec-header') == null || $request->header('my-sec-header') != 'secure' ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }



}
