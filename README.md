# Kinde PHP SDK

The official PHP SDK for Kinde authentication and management APIs.

## Overview

The Kinde PHP SDK provides two main clients:

1. **KindeClientSDK** - For OAuth user authentication (frontend applications)
2. **KindeManagementClient** - For server-to-server management operations (backend services)

## Installation

```bash
composer require kinde-oss/kinde-php-sdk
```

## Quick Start

### Environment Variables

Set up your environment variables:

```env
# Required for both clients
KINDE_DOMAIN=https://your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret

# OAuth client specific
KINDE_REDIRECT_URI=http://localhost:8000/auth/callback
KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000
KINDE_GRANT_TYPE=authorization_code

# Management client specific (optional)
KINDE_MANAGEMENT_ACCESS_TOKEN=your_access_token
```

### OAuth Client (User Authentication)

```php
use Kinde\KindeSDK\KindeClientSDK;

// Create OAuth client from environment variables (recommended)
$kindeClient = KindeClientSDK::createFromEnv();

// Or use constructor (same result)
$kindeClient = new KindeClientSDK();

// Or override specific parameters
$kindeClient = new KindeClientSDK(
    domain: 'https://custom-domain.kinde.com', // Override domain
    redirectUri: null, // Use from environment
    clientId: null, // Use from environment
    clientSecret: null, // Use from environment
    grantType: 'authorization_code' // Override grant type
);

// Redirect user to login
$kindeClient->login();

// Handle callback and get user info
if ($kindeClient->isAuthenticated) {
    $user = $kindeClient->getUserDetails();
    echo "Welcome, {$user['given_name']}!";
    
    // Check user entitlements
    if ($kindeClient->hasEntitlement('premium_features')) {
        $limit = $kindeClient->getEntitlementLimit('premium_features');
        echo "You have premium features with limit: " . $limit;
    }
}
```

### Management Client (Server-to-Server)

```php
use Kinde\KindeSDK\KindeManagementClient;

// Create management client from environment variables (recommended)
$management = KindeManagementClient::createFromEnv();

// Or use constructor (same result)
$management = new KindeManagementClient();

// Or override specific parameters
$management = new KindeManagementClient(
    domain: 'https://custom-domain.kinde.com', // Override domain
    clientId: null, // Use from environment
    clientSecret: null, // Use from environment
    accessToken: 'custom_token' // Override access token
);

// Create a user
$user = $management->users->createUser([
    'given_name' => 'John',
    'family_name' => 'Doe',
    'email' => 'john@example.com'
]);

// Get all users
$users = $management->users->getUsers();

// Create an organization
$org = $management->organizations->createOrganization([
    'name' => 'My Organization'
]);
```

## Framework Integration

### Laravel

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

```php
// In your controller
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;

class AuthController extends Controller
{
    public function __construct(
        private KindeClientSDK $kindeClient,
        private KindeManagementClient $management
    ) {}
    
    public function login()
    {
        return $this->kindeClient->login();
    }
    
    public function createUser(Request $request)
    {
        $user = $this->management->users->createUser([
            'given_name' => $request->input('given_name'),
            'family_name' => $request->input('family_name'),
            'email' => $request->input('email')
        ]);
        
        return response()->json($user);
    }
}
```

## Available APIs

### Management Client APIs

The `KindeManagementClient` provides access to all management APIs:

- **Users API** - `$management->users`
- **Organizations API** - `$management->organizations`
- **Applications API** - `$management->applications`
- **Roles API** - `$management->roles`
- **Permissions API** - `$management->permissions`
- **Feature Flags API** - `$management->featureFlags`
- **Environments API** - `$management->environments`
- **OAuth API** - `$management->oauth`
- **And many more...**

### OAuth Client Features

The `KindeClientSDK` provides OAuth authentication features:

- User login/logout
- Authorization code flow
- PKCE flow
- User profile access
- Token management
- Portal redirects
- **Entitlements** - Access user billing entitlements and feature limits

## Documentation

- [Management Client Documentation](MANAGEMENT_CLIENT.md)
- [Entitlements Documentation](docs/ENTITLEMENTS.md)
- [Framework Integration](FRAMEWORK_INTEGRATION.md)
- [Framework Examples](FRAMEWORK_EXAMPLES.md)
- [Portal Integration](PORTAL_INTEGRATION.md)
- [Inertia.js Integration](INERTIA_INTEGRATION.md)

## Examples

See the [playground](playground/) directory for complete working examples.

## Migration Guide

If you're currently using the API classes directly, you can migrate to the management client:

### Before
```php
use Kinde\KindeSDK\Api\UsersApi;
use Kinde\KindeSDK\Configuration;

$config = new Configuration();
$config->setHost('https://your-domain.kinde.com');
$config->setAccessToken('your_token');

$usersApi = new UsersApi(null, $config);
$users = $usersApi->getUsers();
```

### After
```php
use Kinde\KindeSDK\KindeManagementClient;

$management = KindeManagementClient::createFromEnv();
$users = $management->users->getUsers();
```

## Support

- [Documentation](https://docs.kinde.com)
- [API Reference](https://docs.kinde.com/kinde-apis/)

## License

This project is licensed under the MIT License.
