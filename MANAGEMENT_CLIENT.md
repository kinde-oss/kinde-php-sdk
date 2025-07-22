# Kinde Management Client

The `KindeManagementClient` provides a clean separation between OAuth user authentication and server-to-server management operations. This client handles all management API operations and is designed for backend services that need to manage Kinde resources.

## Overview

The management client is separate from the OAuth client (`KindeClientSDK`) and is specifically designed for:
- Server-to-server operations
- Management API access
- Automated user/organization management
- Bulk operations

## Installation

The management client is included with the main SDK. No additional installation is required.

## Basic Usage

### 1. Create from Environment Variables (Recommended)

```php
use Kinde\KindeSDK\KindeManagementClient;

// Create from environment variables only
$management = KindeManagementClient::createFromEnv();

// Or use the constructor (same result)
$management = new KindeManagementClient();
```

### 2. Create with Explicit Parameters

```php
use Kinde\KindeSDK\KindeManagementClient;

$management = new KindeManagementClient(
    'https://your-domain.kinde.com',
    'your_client_id',
    'your_client_secret',
    'optional_access_token' // Will be fetched automatically if not provided
);
```

### 3. Create with Mixed Parameters (Override Environment Variables)

```php
use Kinde\KindeSDK\KindeManagementClient;

// Use environment variables but override specific parameters
$management = new KindeManagementClient(
    domain: 'https://custom-domain.kinde.com', // Override domain
    clientId: null, // Use from environment
    clientSecret: null, // Use from environment
    accessToken: 'custom_token' // Override access token
);
```

### 4. Laravel Integration

```php
// In your controller or service
use Kinde\KindeSDK\KindeManagementClient;

class UserManagementController extends Controller
{
    public function __construct(
        private KindeManagementClient $management
    ) {}
    
    public function createUser(Request $request)
    {
        $user = $this->management->users->createUser([
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email' => 'john@example.com'
        ]);
        
        return response()->json($user);
    }
}
```

## Environment Variables

```env
# Required for management client
KINDE_DOMAIN=https://your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret

# Optional - if you have a pre-existing access token
KINDE_MANAGEMENT_ACCESS_TOKEN=your_access_token
```

## Available API Clients

The management client provides access to all management APIs:

### Users API
```php
// Create a user
$user = $management->users->createUser([
    'given_name' => 'John',
    'family_name' => 'Doe',
    'email' => 'john@example.com'
]);

// Get users
$users = $management->users->getUsers();

// Update a user
$updatedUser = $management->users->updateUser($userId, [
    'given_name' => 'Jane'
]);

// Delete a user
$management->users->deleteUser($userId);
```

### Organizations API
```php
// Create an organization
$org = $management->organizations->createOrganization([
    'name' => 'My Organization'
]);

// Get organizations
$orgs = $management->organizations->getOrganizations();

// Add users to organization
$management->organizations->addOrganizationUsers($orgId, [
    'users' => ['user_id_1', 'user_id_2']
]);
```

### OAuth API
```php
// Get user profile
$profile = $management->oauth->getUserProfileV2();

// Token introspection
$introspection = $management->oauth->tokenIntrospection($token);

// Token revocation
$management->oauth->tokenRevocation($clientId, $token);
```

### Applications API
```php
// Get applications
$apps = $management->applications->getApplications();

// Create application
$app = $management->applications->createApplication([
    'name' => 'My App',
    'type' => 'spa'
]);
```

### Roles API
```php
// Get roles
$roles = $management->roles->getRoles();

// Create role
$role = $management->roles->createRole([
    'name' => 'admin',
    'description' => 'Administrator role'
]);
```

### Permissions API
```php
// Get permissions
$permissions = $management->permissions->getPermissions();

// Create permission
$permission = $management->permissions->createPermission([
    'name' => 'read:users',
    'description' => 'Read user data'
]);
```

### Feature Flags API
```php
// Get feature flags
$flags = $management->featureFlags->getEnvironmentFeatureFlags();

// Create feature flag
$flag = $management->featureFlags->createFeatureFlag([
    'name' => 'new_feature',
    'type' => 'boolean',
    'value' => true
]);
```

### Environments API
```php
// Get environments
$environments = $management->environments->getEnvironments();

// Create environment
$env = $management->environments->createEnvironment([
    'name' => 'staging',
    'display_name' => 'Staging Environment'
]);
```

## Access Token Management

### Automatic Token Acquisition

The management client automatically fetches an access token using client credentials if none is provided:

```php
$management = new KindeManagementClient();
// No access token provided - will be fetched automatically

// The token is automatically fetched when needed
$users = $management->users->getUsers();
```

### Manual Token Management

```php
// Set a custom access token
$management->setAccessToken('your_access_token');

// Get the current access token
$token = $management->getAccessToken();
```

## Error Handling

```php
use Kinde\KindeSDK\ApiException;

try {
    $users = $management->users->getUsers();
} catch (ApiException $e) {
    // Handle API errors
    $statusCode = $e->getCode();
    $message = $e->getMessage();
    
    if ($statusCode === 401) {
        // Token expired or invalid
        $management->getAccessToken(); // Refresh token
        $users = $management->users->getUsers();
    }
} catch (Exception $e) {
    // Handle other errors
    logger()->error('Management API error: ' . $e->getMessage());
}
```

## Laravel Integration

### Service Provider Registration

The management client is automatically registered in Laravel:

```php
// In your controller
use Kinde\KindeSDK\KindeManagementClient;

class UserController extends Controller
{
    public function __construct(
        private KindeManagementClient $management
    ) {}
    
    public function index()
    {
        $users = $this->management->users->getUsers();
        return response()->json($users);
    }
}
```

### Configuration

Add management-specific configuration to your `config/kinde.php`:

```php
return [
    // ... existing config
    
    'management_access_token' => env('KINDE_MANAGEMENT_ACCESS_TOKEN', null),
];
```

## Best Practices

### 1. Use for Backend Operations Only

The management client should only be used for server-to-server operations, not for user-facing authentication.

### 2. Handle Token Expiration

```php
try {
    $result = $management->users->getUsers();
} catch (ApiException $e) {
    if ($e->getCode() === 401) {
        // Token expired, refresh it
        $management->getAccessToken();
        $result = $management->users->getUsers();
    }
}
```

### 3. Cache Results When Appropriate

```php
// Cache expensive operations
$cacheKey = 'kinde_users_' . md5($management->getAccessToken());
$users = Cache::remember($cacheKey, 300, function () use ($management) {
    return $management->users->getUsers();
});
```

### 4. Use Environment Variables

Always use environment variables for sensitive configuration:

```env
KINDE_DOMAIN=https://your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret
```

## Migration from Direct API Usage

If you're currently using the API classes directly, you can migrate to the management client:

### Before (Direct API Usage)
```php
use Kinde\KindeSDK\Api\UsersApi;
use Kinde\KindeSDK\Configuration;

$config = new Configuration();
$config->setHost('https://your-domain.kinde.com');
$config->setAccessToken('your_token');

$usersApi = new UsersApi(null, $config);
$users = $usersApi->getUsers();
```

### After (Management Client)
```php
use Kinde\KindeSDK\KindeManagementClient;

$management = KindeManagementClient::createFromEnv();
$users = $management->users->getUsers();
```

## Comparison with OAuth Client

| Feature | KindeClientSDK (OAuth) | KindeManagementClient |
|---------|------------------------|----------------------|
| Purpose | User authentication | Server-to-server operations |
| Grant Type | Authorization Code, PKCE | Client Credentials |
| Use Case | Frontend applications | Backend services |
| Token Management | Automatic session handling | Manual or automatic |
| API Access | Limited user-scoped APIs | Full management APIs |
| User Context | User-specific operations | System-wide operations |
| Initialization | `new KindeClientSDK()` or `KindeClientSDK::createFromEnv()` | `new KindeManagementClient()` or `KindeManagementClient::createFromEnv()` |

## Examples

### Bulk User Creation
```php
$users = [
    ['given_name' => 'John', 'family_name' => 'Doe', 'email' => 'john@example.com'],
    ['given_name' => 'Jane', 'family_name' => 'Smith', 'email' => 'jane@example.com'],
];

foreach ($users as $userData) {
    try {
        $user = $management->users->createUser($userData);
        echo "Created user: {$user->getId()}\n";
    } catch (ApiException $e) {
        echo "Failed to create user: {$e->getMessage()}\n";
    }
}
```

### Organization Management
```php
// Create organization
$org = $management->organizations->createOrganization([
    'name' => 'Acme Corp'
]);

// Add users to organization
$management->organizations->addOrganizationUsers($org->getId(), [
    'users' => ['user_1', 'user_2']
]);

// Get organization users
$orgUsers = $management->organizations->getOrganizationUsers($org->getId());
```

### Feature Flag Management
```php
// Create feature flag
$flag = $management->featureFlags->createFeatureFlag([
    'name' => 'new_dashboard',
    'type' => 'boolean',
    'value' => true
]);

// Update feature flag
$management->featureFlags->updateFeatureFlag($flag->getId(), [
    'value' => false
]);
``` 