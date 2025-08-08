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

**Package Name**: `kinde-oss/kinde-auth-php`

**Features**:
- ✅ Service provider for dependency injection
- ✅ Middleware for route protection
- ✅ Configuration publishing
- ✅ Session management
- ✅ Built-in Laravel framework integration

**Usage**:
```bash
composer require kinde-oss/kinde-auth-php
```

**Register the service provider in `config/app.php`:**
```php
'providers' => [
    // ... other providers
    Kinde\KindeSDK\Frameworks\Laravel\KindeServiceProvider::class,
],
```

**Publish the configuration:**
```bash
php artisan vendor:publish --tag=kinde-config
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

### 2. CodeIgniter Integration

**Package Name**: `kinde-oss/kinde-auth-php`

**Features**:
- ✅ Built-in CodeIgniter controller for authentication
- ✅ Automatic route handling for login, callback, logout
- ✅ Session management
- ✅ User profile access
- ✅ Organization management
- ✅ Portal integration

**Usage**:
```bash
composer require kinde-oss/kinde-auth-php
```

**Add autoload mapping to `composer.json`:**
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Kinde\\KindeSDK\\": "vendor/kinde-oss/kinde-auth-php/lib/"
        }
    }
}
```

**Register routes in `app/Config/Routes.php`:**
```php
$routes->get('auth/login', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::login');
$routes->get('auth/callback', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::callback');
$routes->get('auth/logout', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::logout');
$routes->get('auth/register', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::register');
$routes->get('auth/create-org', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::createOrg');
$routes->get('auth/user-info', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::userInfo');
$routes->get('auth/portal', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::portal');
```

**Configuration**:
Set environment variables in your `.env` file:
```env
KINDE_DOMAIN=your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret
KINDE_REDIRECT_URI=http://localhost:8080/auth/callback
KINDE_LOGOUT_REDIRECT_URI=http://localhost:8080/
KINDE_GRANT_TYPE=authorization_code
KINDE_SCOPES=openid profile email offline
```

**Usage in Views**:
```php
<!-- Login button -->
<a href="<?= base_url('auth/login') ?>" class="btn btn-primary">
    Login with Kinde
</a>

<!-- User info display -->
<?php if (session()->get('kinde_authenticated')): ?>
    <div>Welcome, <?= session()->get('kinde_user')->given_name ?></div>
    <a href="<?= base_url('auth/portal') ?>" class="btn btn-primary">Manage Account</a>
    <a href="<?= base_url('auth/logout') ?>" class="btn btn-danger">Logout</a>
<?php endif; ?>
```

### 3. Symfony Integration

**Package Name**: `kinde-oss/kinde-auth-php`

**Features**:
- ✅ Built-in Symfony controller for authentication
- ✅ Attribute-based routing
- ✅ Direct file-based routing
- ✅ Session management
- ✅ User profile access
- ✅ Organization management
- ✅ Portal integration
- ✅ Security voter for permission checks

**Usage**:
```bash
composer require kinde-oss/kinde-auth-php
```

**Add autoload mapping to `composer.json`:**
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "Kinde\\KindeSDK\\": "vendor/kinde-oss/kinde-auth-php/lib/"
        }
    }
}
```

**Register routes in `config/routes.yaml`:**
```yaml
controllers:
    resource:
        path: ../src/Controller/
        namespace: App\Controller
    type: attribute
```

**Create `config/routes/kinde_sdk.yaml`:**
```yaml
kinde_sdk:
    resource: 'Kinde\KindeSDK\Frameworks\Symfony\KindeAuthController'
    type: attribute
```

**Configuration**:
Set environment variables in your `.env` file:
```env
KINDE_DOMAIN=your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret
KINDE_REDIRECT_URI=http://localhost:8000/auth/callback
KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000/
KINDE_GRANT_TYPE=authorization_code
KINDE_SCOPES=openid profile email offline
```

**Usage in Twig Templates**:
```twig
{# Login button #}
<a href="{{ path('kinde_login') }}" class="btn btn-primary">
    Login with Kinde
</a>

{# User info display #}
{% if app.session.get('kinde_authenticated') %}
    <div>Welcome, {{ app.session.get('kinde_user').given_name }}</div>
    <a href="{{ path('kinde_portal') }}" class="btn btn-primary">Manage Account</a>
    <a href="{{ path('kinde_logout') }}" class="btn btn-danger">Logout</a>
{% endif %}
```

**Security Configuration**:
```yaml
# config/packages/security.yaml
security:
    providers:
        kinde:
            id: kinde.user_provider
    
    access_control:
        - { path: ^/dashboard, roles: IS_AUTHENTICATED_FULLY }
        - { path: ^/users, roles: [IS_AUTHENTICATED_FULLY, read:users] }
```