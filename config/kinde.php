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

    /*
    |--------------------------------------------------------------------------
    | AI Agent Authentication (resource server)
    |--------------------------------------------------------------------------
    |
    | Accept access tokens issued to AI agents by a Kinde "AI agents and MCP"
    | application. client_id and client_secret belong to that application, not
    | the one users sign in with. resource_url must equal the application's
    | Resource URL exactly. verification: introspection, jwt or
    | jwt+introspection.
    |
    */

    'agents' => [
        'domain' => env('KINDE_DOMAIN'),
        'client_id' => env('KINDE_AGENTS_CLIENT_ID'),
        'client_secret' => env('KINDE_AGENTS_CLIENT_SECRET'),
        'resource_url' => env('KINDE_AGENTS_RESOURCE_URL'),
        'verification' => env('KINDE_AGENTS_VERIFICATION', 'introspection'),
        'scopes_supported' => env('KINDE_AGENTS_SCOPES', ''),
        'resource_name' => env('KINDE_AGENTS_RESOURCE_NAME'),
        // Mirror /.well-known/oauth-authorization-server on this origin for agents that skip
        // authorization_servers. Disable if your app already serves that path.
        'serve_authorization_server_metadata' => env('KINDE_AGENTS_SERVE_AS_METADATA', true),

        // When this app is itself an agent using CIMD: the document is served at the path of
        // client_id, whose host must be in the Kinde application's Trusted agent hosts.
        'client_metadata' => [
            'client_id' => env('KINDE_AGENTS_CLIENT_METADATA_URL'),
            'client_name' => env('KINDE_AGENTS_CLIENT_NAME'),
            'redirect_uris' => env('KINDE_AGENTS_REDIRECT_URIS', ''),
        ],
    ],
]; 