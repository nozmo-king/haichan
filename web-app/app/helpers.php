<?php

if (!function_exists('csp_nonce')) {
    /**
     * Get the CSP nonce for the current request
     */
    function csp_nonce(): string
    {
        return app('csp_nonce') ?? '';
    }
}

if (!function_exists('nonce_attr')) {
    /**
     * Generate nonce attribute for script/style tags
     */
    function nonce_attr(): string
    {
        $nonce = csp_nonce();
        return $nonce ? "nonce=\"{$nonce}\"" : '';
    }
}