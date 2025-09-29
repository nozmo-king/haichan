<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            if (app()->environment('local', 'staging')) {
                \Log::warning('Unauthorized access attempt', [
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                ]);
            }
            
            return redirect()->route('login');
        }

        return $next($request);
    }
}
