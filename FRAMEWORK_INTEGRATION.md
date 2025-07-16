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

### 2. Slim Framework Package

**Package Name**: `kinde-oss/kinde-auth-slim`

**Features**:
- ✅ Route handlers for authentication flows
- ✅ Middleware for authentication checks
- ✅ Configuration helpers
- ✅ PSR-7 compatible

### 3. Symfony Bundle

**Package Name**: `kinde-oss/kinde-auth-symfony`

**Features**:
- ✅ Controllers for authentication endpoints
- ✅ Security voters for permission checks
- ✅ Configuration integration
- ✅ Twig templates

### 4. Generic PHP Framework Adapters

Create adapters for other popular PHP frameworks:
- **CodeIgniter** adapter
- **CakePHP** plugin  
- **Yii2** extension

## Implementation Strategy

### Phase 1: Laravel Package (MVP)
1. Create `kinde-oss/kinde-auth-laravel` package
2. Implement service provider, controller, middleware
3. Add Artisan commands for installation
4. Create Blade components
5. Write comprehensive documentation

### Phase 2: Slim Package
1. Create `kinde-oss/kinde-auth-slim` package
2. Implement route handlers and middleware
3. Add configuration helpers

### Phase 3: Symfony Bundle
1. Create `kinde-oss/kinde-auth-symfony` bundle
2. Implement controllers and security voters
3. Add Twig templates

### Phase 4: Other Frameworks
1. Create adapters for CodeIgniter, CakePHP, Yii2
2. Maintain consistency across all packages

## Benefits

1. **Developer Experience**: One-command installation and setup
2. **Consistency**: Same patterns across all PHP frameworks
3. **Maintainability**: Framework-specific code is isolated
4. **Documentation**: Framework-specific examples and guides
5. **Community**: Easier adoption and contribution

## Migration Path

1. **Keep existing SDK**: The core `KindeClientSDK` remains unchanged
2. **Add framework packages**: New packages that depend on the core SDK
3. **Gradual adoption**: Developers can choose to use framework packages or manual integration
4. **Documentation**: Update docs to highlight framework packages as the recommended approach

## Example Usage Comparison

### Before (Manual Integration)
```php
// routes/web.php
Route::get('/login', function() {
    $kindeClient = new KindeClientSDK(/* config */);
    $kindeClient->login();
});

Route::get('/callback', function(Request $request) {
    $kindeClient = new KindeClientSDK(/* config */);
    $token = $kindeClient->getToken();
    // Manual session management
    session(['token' => $token]);
    return redirect('/dashboard');
});

// Middleware
Route::middleware(function($request, $next) {
    $kindeClient = new KindeClientSDK(/* config */);
    if (!$kindeClient->isAuthenticated) {
        return redirect('/login');
    }
    return $next($request);
})->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### After (Framework Package)
```php
// Automatic routes: /auth/login, /auth/callback, /auth/logout
// Automatic middleware registration

Route::middleware('kinde.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// In Blade template
<x-kinde::login-button />
```

## Next Steps

1. **Create Laravel package structure** (as shown in the examples above)
2. **Implement core functionality** (service provider, controller, middleware)
3. **Add Artisan commands** for installation and setup
4. **Create Blade components** for common UI elements
5. **Write comprehensive tests** for all functionality
6. **Document the package** with examples and guides
7. **Publish to Packagist** as `kinde-oss/kinde-auth-laravel`
8. **Repeat for other frameworks**

This approach will bring the PHP SDK in line with other Kinde SDKs and provide a much better developer experience. 