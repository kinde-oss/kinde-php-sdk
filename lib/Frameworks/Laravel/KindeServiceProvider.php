<?php

namespace Kinde\KindeSDK\Frameworks\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;

class KindeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/kinde.php', 'kinde'
        );

        $this->app->singleton(KindeClientSDK::class, function ($app) {
            $config = config('kinde');
            
            return new KindeClientSDK(
                $config['domain'],
                $config['redirect_uri'],
                $config['client_id'],
                $config['client_secret'],
                $config['grant_type'] ?? GrantType::authorizationCode,
                $config['logout_redirect_uri'],
                $config['scopes'] ?? 'openid profile email offline',
                $config['additional_parameters'] ?? [],
                $config['protocol'] ?? ''
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/kinde.php' => config_path('kinde.php'),
        ], 'kinde-config');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'kinde-migrations');

        $this->loadRoutesFrom(__DIR__.'/../../routes/auth.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'kinde');
    }
} 