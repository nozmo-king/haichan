<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BitcoinAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('bitcoin_auth_id')) {
            return redirect('/auth/login');
        }

        return $next($request);
    }
}