<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class AnonymousPosting
{
    /**
     * Handle an incoming request for anonymous posting with daily IP limits.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to anonymous users
        if ($request->has('anon') || $request->session()->get('anonymous_mode')) {
            $ip = $request->ip();
            $cacheKey = "anon_post_limit:{$ip}";
            $dailyLimit = 1;

            // Check if IP has already posted today
            $postsToday = Cache::get($cacheKey, 0);

            if ($postsToday >= $dailyLimit) {
                return response()->json([
                    'error' => 'Anonymous posting limit reached. You can only post once per day.',
                    'limit_reached' => true
                ], 429);
            }

            // Set anonymous mode session
            $request->session()->put('anonymous_mode', true);
            $request->session()->put('anonymous_ip', $ip);
        }

        return $next($request);
    }

    /**
     * Increment the anonymous post count for an IP
     */
    public static function incrementPostCount($ip)
    {
        $cacheKey = "anon_post_limit:{$ip}";
        $currentCount = Cache::get($cacheKey, 0);

        // Store for 24 hours
        Cache::put($cacheKey, $currentCount + 1, now()->addDay());
    }
}
