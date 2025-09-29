<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'auth_attempts:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'error' => 'Too many authentication attempts. Try again in '.$seconds.' seconds.',
            ], 429);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        $response = $next($request);

        // Clear rate limit on successful authentication
        if ($response->getStatusCode() === 200 || $response->isRedirection()) {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
