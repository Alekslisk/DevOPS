<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Closure;
use Illuminate\Http\Request;

class CheckBlockedUser
{
    public function handle($request, Closure $next)
    {
        if ($request->is('blocked')) {
            return $next($request);
        }  

        $user = Auth::user();

        if ($user && $user->is_blocked && $user->block_level === 2 && $user->blocked_until > now()) {
            return redirect('blocked');
        }
  

        return $next($request);
    }
}