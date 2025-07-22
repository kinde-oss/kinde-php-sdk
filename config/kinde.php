<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kinde Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Kinde authentication.
    | You can publish this config file using:
    | php artisan vendor:publish --tag=kinde-config
    |
    */

    'domain' => env('KINDE_DOMAIN', 'https://your-domain.kinde.com'),
    
    'client_id' => env('KINDE_CLIENT_ID'),
    
    'client_secret' => env('KINDE_CLIENT_SECRET'),
    
    'redirect_uri' => env('KINDE_REDIRECT_URI', 'http://localhost:8000/auth/callback'),
    
    'logout_redirect_uri' => env('KINDE_LOGOUT_REDIRECT_URI', 'http://localhost:8000'),
    
    'grant_type' => env('KINDE_GRANT_TYPE', 'authorization_code'),
    
    'scopes' => env('KINDE_SCOPES', 'openid profile email offline'),
    
    'additional_parameters' => [
        // Add any additional parameters you want to pass to Kinde
        // 'audience' => env('KINDE_AUDIENCE'),
    ],
    
    'protocol' => env('KINDE_PROTOCOL', ''), // Leave empty for auto-detection
    
    /*
    |--------------------------------------------------------------------------
    | Management API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for server-to-server management operations.
    | This uses the same client credentials but for management API access.
    |
    */
    
    'management_access_token' => env('KINDE_MANAGEMENT_ACCESS_TOKEN', null),
    
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how user sessions are stored and managed.
    |
    */
    
    'session' => [
        'user_key' => 'kinde_user',
        'token_key' => 'kinde_token',
        'authenticated_key' => 'kinde_authenticated',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routes that will be registered by the package.
    |
    */
    
    'routes' => [
        'prefix' => 'auth',
        'middleware' => ['web'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the middleware behavior.
    |
    */
    
    'middleware' => [
        'redirect_to_login' => true,
        'store_user_in_session' => true,
        'store_permissions_in_session' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Portal Configuration
    |--------------------------------------------------------------------------
    |
    | Configure portal redirect behavior.
    |
    */
    
    'portal' => [
        'default_sub_nav' => 'profile',
        'default_return_url' => '/dashboard',
    ],
]; 