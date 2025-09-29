<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Prevent information disclosure
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        
        // Content Security Policy (adjust as needed)
        if (!$request->is('api/*')) {
            $response->headers->set('Content-Security-Policy', 
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                "style-src 'self' 'unsafe-inline'; " .
                "img-src 'self' data: blob:; " .
                "font-src 'self'; " .
                "connect-src 'self'; " .
                "media-src 'self'; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self';"
            );
        }

        return $response;
    }
}