<?php

namespace App\Http\Middleware;

use Closure;
//use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (str_contains($request->url(), 'admin') || str_contains($request->url(), 'login')
            || str_contains($request->url(), 'register') || ($request->path() == '') || ($request->path() == '/') ){
                // Redirect to the desired URL
            return $next($request);
        // Change to your desired URL
    } else {
        return redirect()->to('/admin');
    }
    }

}
