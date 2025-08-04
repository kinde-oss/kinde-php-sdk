<?php

namespace Kinde\KindeSDK\Frameworks\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Inertia\Inertia;

class KindeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../../config/kinde.php', 'kinde'
        );

        // Register the OAuth client (for user authentication)
        $this->app->singleton(KindeClientSDK::class, function ($app) {
            $config = config('kinde');
            
            // Validate required configuration
            $required = ['domain', 'redirect_uri', 'client_id', 'client_secret'];
            foreach ($required as $key) {
                if (empty($config[$key])) {
                    throw new \InvalidArgumentException("Missing required Kinde configuration: {$key}");
                }
            }

            // Use environment variables by default, with config overrides
            return new KindeClientSDK(
                domain: $config['domain'] ?? null,
                redirectUri: $config['redirect_uri'] ?? null,
                clientId: $config['client_id'] ?? null,
                clientSecret: $config['client_secret'] ?? null,
                grantType: $config['grant_type'] ?? null,
                logoutRedirectUri: $config['logout_redirect_uri'] ?? null,
                scopes: $config['scopes'] ?? 'openid profile email offline',
                additionalParameters: $config['additional_parameters'] ?? [],
                protocol: $config['protocol'] ?? ''
            );
        });

        // Register the management client (for server-to-server operations)
        $this->app->singleton(KindeManagementClient::class, function ($app) {
            $config = config('kinde');
            
            // Validate required configuration (management_access_token is optional)
            $required = ['domain', 'client_id', 'client_secret'];
            foreach ($required as $key) {
                if (empty($config[$key])) {
                    throw new \InvalidArgumentException("Missing required Kinde configuration: {$key}");
                }
            }

            return new KindeManagementClient(
                domain: $config['domain'] ?? null,
                clientId: $config['client_id'] ?? null,
                clientSecret: $config['client_secret'] ?? null,
                accessToken: $config['management_access_token'] ?? null
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config/kinde.php' => config_path('kinde.php'),
        ], 'kinde-config');

        $this->loadRoutesFrom(__DIR__.'/../../../routes/auth.php');
        $this->loadViewsFrom(__DIR__.'/../../../resources/views', 'kinde');

        // Share Kinde data with Inertia if Inertia is available
        if (class_exists(Inertia::class)) {
            $this->shareKindeDataWithInertia();
        }
    }

    /**
     * Share Kinde authentication data with Inertia
     */
    protected function shareKindeDataWithInertia(): void
    {
        Inertia::share('kinde', function () {
            $kindeClient = app(KindeClientSDK::class);
            
            return [
                'isAuthenticated' => $kindeClient->isAuthenticated,
                'user' => session('kinde_user'),
                'permissions' => session('kinde_permissions', []),
                'organization' => session('kinde_organization'),
            ];
        });
    }
} 