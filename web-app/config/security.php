<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Security Settings
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 12,
        'require_mixed_case' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'max_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'login_attempts' => 5,
        'registration_attempts' => 3,
        'api_calls_per_minute' => 60,
        'proof_submissions_per_minute' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'regenerate_interval' => 30, // minutes
        'max_lifetime' => 1440, // 24 hours
        'require_https' => env('SESSION_SECURE_COOKIE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_file_size' => 25600, // 25MB in KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/bmp',
            'image/tiff',
            'image/avif',
            'image/heic',
            'image/heif',
            'video/mp4',
            'video/webm',
            'video/quicktime',
            'video/x-msvideo',
        ],
        'scan_for_malware' => true,
        'strip_metadata' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    'csp' => [
        'enabled' => true,
        'report_only' => false,
        'report_uri' => '/csp-report',
    ],
];