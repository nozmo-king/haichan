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
            // Log for debugging
            \Log::info('BitcoinAuth failed', [
                'user_id' => $userId,
                'path' => $request->path(),
                'method' => $request->method(),
                'expects_json' => $request->expectsJson(),
                'is_ajax' => $request->ajax(),
                'content_type' => $request->header('Content-Type'),
            ]);
            
            session()->flush();

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Session expired. Please log in again.'], 401);
            }

            return redirect('/auth/login')->withErrors(['auth' => 'Session expired. Please log in again.']);
        }

        // Verify user still exists and is active
        $user = \App\Models\BitcoinAuth::find($userId);
        if (! $user || $user->is_banned) {
            session()->flush();

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Account access denied.'], 401);
            }

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
