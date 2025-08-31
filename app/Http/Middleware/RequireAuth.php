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
        \Log::info('=== RequireAuth middleware ENTRY ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            'route_params' => $request->route() ? $request->route()->parameters() : [],
            'session_id' => session()->getId(),
            'auth_check' => auth()->check(),
            'user_id' => auth()->id(),
            'session_data' => session()->all(),
            'request_headers' => [
                'accept' => $request->header('Accept'),
                'content-type' => $request->header('Content-Type'),
                'x-csrf-token' => $request->header('X-CSRF-Token'),
                'referer' => $request->header('Referer'),
                'user-agent' => $request->header('User-Agent')
            ],
            'request_data' => $request->method() === 'POST' ? $request->except(['_token', 'password', 'content']) : [],
            'timestamp' => now()->toDateTimeString()
        ]);
        
        if (!auth()->check()) {
            \Log::error('=== AUTHENTICATION FAILED - REDIRECTING TO LOGIN ===', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
                'session_id' => session()->getId(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'redirect_to' => route('auth.login'),
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->route('auth.login');
        }
        
        \Log::info('=== RequireAuth middleware PASSED - proceeding to next ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        $response = $next($request);
        
        \Log::info('=== RequireAuth middleware RESPONSE ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            'response_status' => $response->getStatusCode(),
            'response_headers' => [
                'location' => $response->headers->get('Location'),
                'content-type' => $response->headers->get('Content-Type')
            ],
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return $response;
    }
}
