<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /**
     * Test that security headers are properly set
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');
        
        // Test basic security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        
        // Test that information disclosure headers are removed
        $this->assertNull($response->headers->get('X-Powered-By'));
        $this->assertNull($response->headers->get('Server'));
    }
    
    /**
     * Test that CSP header is present and strict
     */
    public function test_content_security_policy_header(): void
    {
        $response = $this->get('/');
        
        $response->assertHeader('Content-Security-Policy');
        
        $csp = $response->headers->get('Content-Security-Policy');
        
        // Test that CSP contains required directives
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        
        // Test that unsafe directives are NOT present
        $this->assertStringNotContainsString("'unsafe-inline'", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        
        // Test that nonce is present in script-src
        $this->assertMatchesRegularExpression("/script-src.*'nonce-[A-Za-z0-9+\/=]+'/", $csp);
    }
    
    /**
     * Test that nonce is unique per request
     */
    public function test_nonce_is_unique_per_request(): void
    {
        $response1 = $this->get('/');
        $response2 = $this->get('/');
        
        $csp1 = $response1->headers->get('Content-Security-Policy');
        $csp2 = $response2->headers->get('Content-Security-Policy');
        
        // Extract nonces from CSP headers
        preg_match("/'nonce-([A-Za-z0-9+\/=]+)'/", $csp1, $matches1);
        preg_match("/'nonce-([A-Za-z0-9+\/=]+)'/", $csp2, $matches2);
        
        $this->assertNotEmpty($matches1);
        $this->assertNotEmpty($matches2);
        
        // Nonces should be different
        $this->assertNotEquals($matches1[1], $matches2[1]);
    }
    
    /**
     * Test CSP for API routes (should not have CSP)
     */
    public function test_api_routes_do_not_have_csp(): void
    {
        $response = $this->get('/api/mining/stats');
        
        // API routes should not have CSP
        $this->assertNull($response->headers->get('Content-Security-Policy'));
        
        // But should still have other security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }
}