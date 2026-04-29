<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireServerAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('server_sudo_password')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
