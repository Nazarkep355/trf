<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CheckAdminMiddleware extends Middleware
{
//    protected function redirectTo($request)
//    {
//        if (!empty($request->user()) && $request->user()->isAdmin()) {
//            return route('login');
//        }
//    }
    public function handle($request, Closure $next, ...$guards)
    {
        if (!empty($request->user()) && $request->user()->isAdmin()) {
            return $next($request);
        } else {
            return response()->redirectTo( route('login'));
        }

    }
}
