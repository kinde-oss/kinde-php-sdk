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
    | Feature Flags Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default values for feature flags.
    |
    */
    
    'feature_flags' => [
        'defaults' => [
            // 'my_feature' => false,
        ],
    ],
]; 