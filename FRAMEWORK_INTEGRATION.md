# Kinde PHP SDK Framework Integration

This document outlines the approach for bringing the Kinde PHP SDK up to date with other SDKs by providing framework-specific implementations.

## Current State

The current PHP SDK provides a low-level `KindeClientSDK` class that developers must manually integrate into their applications. This requires:

1. Manual route creation for login, callback, register, and logout
2. Manual session management
3. Manual middleware creation for authentication checks
4. Manual configuration setup

## Proposed Solution

Create framework-specific packages that provide ready-to-use authentication handlers, similar to other Kinde SDKs.

## Framework Integrations

### 1. Laravel Package (Priority)

**Package Name**: `kinde-oss/kinde-auth-laravel`

**Features**:
- ✅ Automatic route registration (`/auth/login`, `/auth/callback`, etc.)
- ✅ Service provider for dependency injection
- ✅ Middleware for route protection
- ✅ Artisan commands for quick setup
- ✅ Blade components for login/logout buttons
- ✅ Configuration publishing
- ✅ Session management

**Usage**:
```bash
composer require kinde-oss/kinde-auth-laravel
php artisan kinde:install
```

**Configuration**:
```php
// config/kinde.php
return [
    'domain' => env('KINDE_DOMAIN'),
    'client_id' => env('KINDE_CLIENT_ID'),
    'client_secret' => env('KINDE_CLIENT_SECRET'),
    'redirect_uri' => env('KINDE_REDIRECT_URI'),
    'logout_redirect_uri' => env('KINDE_LOGOUT_REDIRECT_URI'),
];
```

**Route Protection**:
```php
Route::middleware('kinde.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// With permission check
Route::middleware('kinde.auth:read:users')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

**Blade Components**:
```blade
<x-kinde::login-button text="Sign in with Kinde" />
<x-kinde::logout-button />
```

### 3. Symfony Bundle

**Package Name**: `