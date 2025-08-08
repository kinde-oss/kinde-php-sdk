# Framework Integration Examples

This document shows how the Kinde PHP SDK would work with different PHP frameworks.

## Laravel Integration

### Installation
```bash
composer require kinde-oss/kinde-auth-php
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

## Symfony Integration

### Installation
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

### Routes
```yaml
# config/routes.yaml
controllers:
    resource:
        path: ../src/Controller/
        namespace: App\Controller
    type: attribute
```

```yaml
# config/routes/kinde_sdk.yaml
kinde_sdk:
    resource: 'Kinde\KindeSDK\Frameworks\Symfony\KindeAuthController'
    type: attribute
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

### Routes
```php
// app/Config/Routes.php
$routes->get('auth/login', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::login');
$routes->get('auth/callback', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::callback');
$routes->get('auth/register', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::register');
$routes->get('auth/create-org', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::createOrg');
$routes->get('auth/logout', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::logout');
$routes->get('auth/user-info', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::userInfo');
$routes->get('auth/portal', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::portal');
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