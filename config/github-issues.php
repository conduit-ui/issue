<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Issues Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the GitHub Issues package.
    |
    */

    'token' => env('GITHUB_TOKEN'),

    'base_url' => env('GITHUB_API_URL', 'https://api.github.com'),

    'timeout' => env('GITHUB_ISSUES_TIMEOUT', 30),

    'retry' => [
        'times' => env('GITHUB_ISSUES_RETRY_TIMES', 3),
        'sleep' => env('GITHUB_ISSUES_RETRY_SLEEP', 500),
    ],

    'cache' => [
        'enabled' => env('GITHUB_ISSUES_CACHE_ENABLED', false),
        'ttl' => env('GITHUB_ISSUES_CACHE_TTL', 300),
        'prefix' => env('GITHUB_ISSUES_CACHE_PREFIX', 'github_issues'),
    ],
];
