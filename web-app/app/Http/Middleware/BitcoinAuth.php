<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BitcoinAuth
{
    public function handle(Request $request, Closure $next)
    {
        $userId = session('bitcoin_auth_id');

        // Check if session exists and user ID is valid
        if (! $userId || ! is_numeric($userId)) {
            session()->flush();

            return redirect('/auth/login')->withErrors(['auth' => 'Session expired. Please log in again.']);
        }

        // Verify user still exists and is active
        $user = \App\Models\BitcoinAuth::find($userId);
        if (! $user || $user->is_banned) {
            session()->flush();

            return redirect('/auth/login')->withErrors(['auth' => 'Account access denied.']);
        }

        // Keep session user data fresh
        session(['bitcoin_auth_user' => $user]);

        // Regenerate session ID periodically for security
        if (! session('last_regeneration') || session('last_regeneration') < now()->subMinutes(30)) {
            session()->regenerate();
            session(['last_regeneration' => now()]);
        }

        return $next($request);
    }
}
