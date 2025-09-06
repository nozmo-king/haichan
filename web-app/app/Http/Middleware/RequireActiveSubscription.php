<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        \Log::info('=== RequireActiveSubscription middleware ENTRY ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            'route_params' => $request->route() ? $request->route()->parameters() : [],
            'user_id' => $user ? $user->id : null,
            'user_exists' => !!$user,
            'has_active_subscription' => $user ? $user->hasActiveSubscription() : false,
            'expects_json' => $request->expectsJson(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toDateTimeString()
        ]);

        if (!$user || !$user->hasActiveSubscription()) {
            \Log::error('=== SUBSCRIPTION CHECK FAILED - REDIRECTING ===', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
                'user_id' => $user ? $user->id : null,
                'user_exists' => !!$user,
                'has_active_subscription' => $user ? $user->hasActiveSubscription() : false,
                'expects_json' => $request->expectsJson(),
                'redirect_to' => $request->expectsJson() ? 'JSON_403' : route('subscription.plans'),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Active subscription required.',
                    'message' => 'You need an active subscription to access this feature.'
                ], 403);
            }

            $message = $user ? 
                'You need an active subscription to access the forum. Please choose a plan below.' :
                'You need to log in and have an active subscription to access this feature.';
                
            return redirect()->route('subscription.plans')
                ->with('info', $message);
        }

        \Log::info('=== RequireActiveSubscription middleware PASSED - proceeding to next ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            'user_id' => $user->id,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        $response = $next($request);
        
        \Log::info('=== RequireActiveSubscription middleware RESPONSE ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown',
            'response_status' => $response->getStatusCode(),
            'response_headers' => [
                'location' => $response->headers->get('Location'),
                'content-type' => $response->headers->get('Content-Type')
            ],
            'user_id' => $user->id,
            'timestamp' => now()->toDateTimeString()
        ]);

        return $response;
    }
}
