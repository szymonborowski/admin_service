<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'users' => [
        'url' => env('USERS_API_URL_INTERNAL', 'http://users-nginx'),
        'api_key' => env('USERS_SERVICE_API_KEY'),
    ],

    'blog' => [
        'url' => env('BLOG_API_URL_INTERNAL', 'http://blog-nginx'),
        'api_key' => env('BLOG_INTERNAL_API_KEY'),
    ],

    'analytics' => [
        'url'              => env('ANALYTICS_API_URL_INTERNAL', 'http://analytics-nginx'),
        'internal_api_key' => env('ANALYTICS_INTERNAL_API_KEY'),
    ],

    'frontend' => [
        'url' => env('FRONTEND_API_URL_INTERNAL', 'http://frontend-nginx'),
        'api_key' => env('FRONTEND_INTERNAL_API_KEY'),
    ],

    'sso' => [
        'url' => env('SSO_URL', 'https://sso.microservices.local'),
        'internal_url' => env('SSO_INTERNAL_URL', 'http://sso-nginx'),
        'client_id' => env('SSO_CLIENT_ID', 'admin-client'),
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'redirect_uri' => env('SSO_REDIRECT_URI', 'https://admin.microservices.local/auth/sso/callback'),
    ],

];
