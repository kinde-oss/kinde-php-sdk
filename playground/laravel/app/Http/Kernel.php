<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'kinde.auth' => \Kinde\KindeSDK\Frameworks\Laravel\Middleware\KindeAuthMiddleware::class,
        // Add other middleware here as needed
    ];
} 