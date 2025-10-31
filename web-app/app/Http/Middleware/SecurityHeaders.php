<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Generate cryptographically secure nonce for this request
        $nonce = $this->generateNonce();
        
        // Store nonce in request for use in views
        $request->attributes->set('csp_nonce', $nonce);
        app()->instance('csp_nonce', $nonce);

        $response = $next($request);

        // Enhanced Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '0'); // Disabled in favor of CSP
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Prevent information disclosure
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        
        // Strict Content Security Policy with nonce-based security
        if (!$request->is('api/*')) {
            $csp = $this->buildContentSecurityPolicy($nonce, $request);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    /**
     * Generate cryptographically secure nonce for CSP
     */
    private function generateNonce(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Build strict Content Security Policy with nonce-based security
     */
    private function buildContentSecurityPolicy(string $nonce, Request $request): string
    {
        $isProduction = app()->environment('production');
        
        // Base strict policy
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
        ];

        // Script sources with nonce-based security
        $scriptSrc = ["'self'", "'nonce-{$nonce}'"];
        
        // Allow development server in non-production
        if (!$isProduction) {
            $scriptSrc[] = 'http://localhost:5173';
            $scriptSrc[] = 'ws://localhost:5173';
        }
        
        // Allow unsafe-hashes for inline event handlers (onclick, etc)
        $scriptSrc[] = "'unsafe-hashes'";
        
        // Add specific hashes for critical inline scripts if needed
        $scriptSrc = array_merge($scriptSrc, $this->getCriticalScriptHashes());
        
        $directives[] = "script-src " . implode(' ', $scriptSrc);

        // Style sources with nonce and specific trusted sources
        $styleSrc = ["'self'"];
        
        // Allow Google Fonts
        $styleSrc[] = 'https://fonts.googleapis.com';
        
        // Allow unsafe-inline for styles (needed for landing page and existing inline styles)
        $styleSrc[] = "'unsafe-inline'";
        
        // Add specific hashes for critical inline styles
        $styleSrc = array_merge($styleSrc, $this->getCriticalStyleHashes());
        
        $directives[] = "style-src " . implode(' ', $styleSrc);

        // Font sources
        $directives[] = "font-src 'self' https://fonts.gstatic.com";

        // Image sources - allow data URLs for base64 images and blob for canvas
        $directives[] = "img-src 'self' data: blob: https:";

        // Connection sources
        $connectSrc = ["'self'"];
        if (!$isProduction) {
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'ws://localhost:5173';
        }
        $directives[] = "connect-src " . implode(' ', $connectSrc);

        // Media sources
        $directives[] = "media-src 'self'";

        // Worker sources for WASM and service workers
        $directives[] = "worker-src 'self' blob:";

        // Child sources for frames
        $directives[] = "child-src 'self'";

        // Upgrade insecure requests in production
        if ($isProduction) {
            $directives[] = "upgrade-insecure-requests";
        }

        return implode('; ', $directives) . ';';
    }

    /**
     * Get SHA-256 hashes for critical inline scripts that cannot use nonces
     */
    private function getCriticalScriptHashes(): array
    {
        return [
            // Add hashes for any critical inline scripts here
            // Example: "'sha256-hash_of_critical_script'"
        ];
    }

    /**
     * Get SHA-256 hashes for critical inline styles that cannot use nonces
     */
    private function getCriticalStyleHashes(): array
    {
        return [
            // Add hashes for any critical inline styles here
            // Example: "'sha256-hash_of_critical_style'"
        ];
    }
}