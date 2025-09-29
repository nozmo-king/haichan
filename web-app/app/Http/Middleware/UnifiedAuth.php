<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UnifiedAuth
{
    /**
     * Handle an incoming request.
     *
     * This middleware handles both web and API authentication seamlessly
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is an API request
        $isApiRequest = $request->is('api/*') || $request->wantsJson();

        if ($isApiRequest) {
            // For API requests, use Sanctum token authentication
            if (! Auth::guard('sanctum')->check()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Authentication token required',
                ], 401);
            }

            // Set the authenticated user for the request
            $request->setUserResolver(function () {
                return Auth::guard('sanctum')->user();
            });
        } else {
            // For web requests, use session-based authentication
            if (! Auth::check()) {
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
