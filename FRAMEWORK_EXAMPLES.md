# Framework Integration Examples

This document shows how the Kinde PHP SDK would work with different PHP frameworks.

## Laravel Integration

### Installation
```bash
composer require kinde-oss/kinde-auth-laravel
php artisan kinde:install
```

### Configuration
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

### Routes (Auto-registered)
```php
// These routes are automatically registered:
// GET /auth/login
// GET /auth/callback  
// GET /auth/register
// GET /auth/create-org
// GET /auth/logout
// GET /auth/user-info
// GET /auth/portal
```

### Route Protection
```php
// routes/web.php
Route::middleware('kinde.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// With permission check
Route::middleware('kinde.auth:read:users')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

### Blade Components
```blade
{{-- Login button --}}
<x-kinde::login-button text="Sign in with Kinde" />

{{-- Logout button --}}
<x-kinde::logout-button />

{{-- User info view --}}
@if(session('kinde_authenticated'))
    <div>Welcome, {{ session('kinde_user')->given_name }}</div>
    <a href="{{ route('kinde.portal') }}">Manage Account</a>
@endif
```

## Slim Framework Integration

### Installation
```bash
composer require kinde-oss/kinde-auth-slim
```

### Setup
```php
// bootstrap/app.php
use Kinde\KindeSDK\Frameworks\Slim\KindeAuthController;
use Kinde\KindeSDK\Frameworks\Slim\KindeAuthMiddleware;

// Create Kinde client
$kindeClient = new KindeClientSDK(
    $_ENV['KINDE_DOMAIN'],
    $_ENV['KINDE_REDIRECT_URI'],
    $_ENV['KINDE_CLIENT_ID'],
    $_ENV['KINDE_CLIENT_SECRET'],
    $_ENV['KINDE_GRANT_TYPE'],
    $_ENV['KINDE_LOGOUT_REDIRECT_URI']
);

// Add to container
$container->set(KindeClientSDK::class, $kindeClient);

// Register routes
$app->get('/auth/login', [KindeAuthController::class, 'login']);
$app->get('/auth/callback', [KindeAuthController::class, 'callback']);
$app->get('/auth/register', [KindeAuthController::class, 'register']);
$app->get('/auth/create-org', [KindeAuthController::class, 'createOrg']);
$app->get('/auth/logout', [KindeAuthController::class, 'logout']);
$app->get('/auth/user-info', [KindeAuthController::class, 'userInfo']);
$app->get('/auth/portal', [KindeAuthController::class, 'portal']);

// Protected routes
$app->get('/dashboard', function (Request $request, Response $response) {
    $response->getBody()->write('Dashboard');
    return $response;
})->add(new KindeAuthMiddleware($kindeClient));

// With permission check
$app->get('/users', function (Request $request, Response $response) {
    $response->getBody()->write('Users');
    return $response;
})->add(KindeAuthMiddleware::withPermission('read:users'));
```

## Symfony Integration

### Installation
```bash
composer require kinde-oss/kinde-auth-symfony
```

### Configuration
```yaml
# config/packages/kinde.yaml
kinde:
    domain: '%env(KINDE_DOMAIN)%'
    client_id: '%env(KINDE_CLIENT_ID)%'
    client_secret: '%env(KINDE_CLIENT_SECRET)%'
    redirect_uri: '%env(KINDE_REDIRECT_URI)%'
    logout_redirect_uri: '%env(KINDE_LOGOUT_REDIRECT_URI)%'
```

### Routes
```yaml
# config/routes.yaml
kinde_auth:
    resource: '@KindeAuthBundle/Controller/'
    type: annotation
    prefix: /auth
```

### Security Configuration
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

### Twig Templates
```twig
{# templates/kinde/login.html.twig #}
<a href="{{ path('kinde_login') }}" class="btn btn-primary">
    Login with Kinde
</a>

{# templates/kinde/user-info.html.twig #}
{% if app.session.get('kinde_authenticated') %}
    <div>Welcome, {{ app.session.get('kinde_user').given_name }}</div>
    <a href="{{ path('kinde_portal') }}" class="btn btn-primary">Manage Account</a>
    <a href="{{ path('kinde_logout') }}" class="btn btn-danger">Logout</a>
{% endif %}
```

## CodeIgniter Integration

### Installation
```bash
composer require kinde-oss/kinde-auth-codeigniter
```

### Routes
```php
// app/Config/Routes.php
$routes->get('auth/login', 'KindeAuthController::login');
$routes->get('auth/callback', 'KindeAuthController::callback');
$routes->get('auth/register', 'KindeAuthController::register');
$routes->get('auth/create-org', 'KindeAuthController::createOrg');
$routes->get('auth/logout', 'KindeAuthController::logout');
$routes->get('auth/user-info', 'KindeAuthController::userInfo');
$routes->get('auth/portal', 'KindeAuthController::portal');
```

### Middleware
```php
// app/Filters/KindeAuthFilter.php
class KindeAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $kindeClient = new KindeClientSDK(/* config */);
        
        if (!$kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }
        
        // Check permissions if provided
        if (!empty($arguments)) {
            $permission = $arguments[0];
            $permissionCheck = $kindeClient->getPermission($permission);
            
            if (!$permissionCheck['isGranted']) {
                return redirect()->to('/')->with('error', 'Insufficient permissions');
            }
        }
    }
}
```

### Views
```php
// app/Views/kinde/login.php
<a href="<?= base_url('auth/login') ?>" class="btn btn-primary">
    Login with Kinde
</a>

// app/Views/kinde/user-info.php
<?php if (session()->get('kinde_authenticated')): ?>
    <div>Welcome, <?= session()->get('kinde_user')->given_name ?></div>
    <a href="<?= base_url('auth/portal') ?>" class="btn btn-primary">Manage Account</a>
    <a href="<?= base_url('auth/logout') ?>" class="btn btn-danger">Logout</a>
<?php endif; ?>
```

## Usage Comparison

### Before (Manual Integration)
```php
// Every developer had to write this manually
Route::get('/login', function() {
    $kindeClient = new KindeClientSDK(/* config */);
    $kindeClient->login();
});

Route::get('/callback', function(Request $request) {
    $kindeClient = new KindeClientSDK(/* config */);
    $token = $kindeClient->getToken();
    session(['token' => $token]);
    return redirect('/dashboard');
});

// Manual middleware
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
// Laravel
Route::middleware('kinde.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Slim
$app->get('/dashboard', function (Request $request, Response $response) {
    $response->getBody()->write('Dashboard');
    return $response;
})->add(new KindeAuthMiddleware($kindeClient));

// Symfony
#[Route('/dashboard')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function dashboard(): Response
{
    return $this->render('dashboard/index.html.twig');
}

// CodeIgniter
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'kinde_auth']);
```

## Benefits

1. **Consistent API**: Same authentication patterns across all frameworks
2. **Reduced Boilerplate**: No need to write authentication routes manually
3. **Framework Integration**: Uses framework-specific features (Blade, Twig, etc.)
4. **Security**: Built-in permission checking and session management
5. **Documentation**: Framework-specific examples and guides
6. **Maintenance**: Isolated framework-specific code

This approach brings the PHP SDK in line with other Kinde SDKs and provides the same level of developer experience that users expect from modern authentication libraries. 