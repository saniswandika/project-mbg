<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    public function handle($request, Closure $next)
    {
        $expire_time = 900; // 900 detik = 15 menit

        if (Session::has('lastActivityTime')) {
            if (time() - Session::get('lastActivityTime') > $expire_time) {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')
                    ->withErrors(['message' => 'Session expired, please login again.']);
            }
        }

        Session::put('lastActivityTime', time());

        return $next($request);
    }
}
