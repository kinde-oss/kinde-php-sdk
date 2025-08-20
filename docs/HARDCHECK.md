# Hardcheck Functionality

The Kinde PHP SDK now supports a "hardcheck" functionality that allows you to force the SDK to use API calls instead of token parsing for retrieving user claims and data.

## Overview

By default, the KindeClientSDK retrieves user data (permissions, feature flags, organization info, etc.) by parsing JWT tokens. However, when you enable the `force_api` flag, each method will make direct API calls to the appropriate Kinde Frontend API endpoints to get real-time data.

This approach is much more efficient because:
- **Only fetches what's needed**: If you only check feature flags, only the feature flags API is called
- **No complex mapping**: Each method handles its own API call directly
- **Better performance**: No unnecessary API calls for unrelated data
- **Cleaner code**: Each method is responsible for its own data source

## Benefits

- **Real-time data**: Always gets the latest data from the server
- **No token dependency**: Doesn't rely on JWT token claims
- **Fresh permissions**: Ensures permissions are up-to-date
- **Dynamic feature flags**: Gets the latest feature flag values
- **Better security**: Reduces reliance on client-side token data

## How to Enable

### Method 1: Constructor Parameter

```php
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;

$client = new KindeClientSDK(
    'https://your-domain.kinde.com',
    'http://localhost:8000/auth/callback',
    'your_client_id',
    'your_client_secret',
    GrantType::authorizationCode,
    'http://localhost:8000',
    'openid profile email offline',
    [],
    '',
    true // Enable force_api (hardcheck)
);
```

### Method 2: Environment Variable

Set the `KINDE_FORCE_API` environment variable:

```bash
export KINDE_FORCE_API=true
```

Or in your `.env` file:

```
KINDE_FORCE_API=true
```

Then create the client:

```php
$client = KindeClientSDK::createFromEnv();
```

## API Endpoints Used

When `force_api` is enabled, the following API endpoints are used:

| Functionality | API Endpoint | API Class |
|---------------|--------------|-----------|
| Permissions | `/account_api/v1/permissions` | `PermissionsApi::getUserPermissions()` |
| Feature Flags | `/account_api/v1/feature_flags` | `FeatureFlagsApi::getFeatureFlags()` |
| User Profile | `/oauth2/v2/user_profile` | `OAuthApi::getUserProfileV2()` |
| Entitlements | `/account_api/v1/entitlements` | `BillingApi::getEntitlements()` |
| Roles | `/account_api/v1/roles` | `RolesApi::getUserRoles()` |
| Properties | `/account_api/v1/properties` | `PropertiesApi::getUserProperties()` |

## Usage Examples

### Getting Permissions

```php
// With force_api enabled, this makes an API call to /account_api/v1/permissions
$permissions = $client->getPermissions();
echo "Organization: " . $permissions['orgCode'] . "\n";
echo "Permissions: " . implode(', ', $permissions['permissions']) . "\n";
```

### Checking Specific Permission

```php
// With force_api enabled, this makes an API call to /account_api/v1/permissions
$hasPermission = $client->getPermission('read:users');
if ($hasPermission['isGranted']) {
    echo "User has read:users permission\n";
}
```

### Getting Feature Flags

```php
// With force_api enabled, this makes an API call to /account_api/v1/feature_flags
$booleanFlag = $client->getBooleanFlag('new_feature', false);
echo "New feature enabled: " . ($booleanFlag['value'] ? 'Yes' : 'No') . "\n";

$stringFlag = $client->getStringFlag('theme', 'default');
echo "Theme: " . $stringFlag['value'] . "\n";
```

### Getting Organization Information

```php
// With force_api enabled, this makes an API call to /oauth2/v2/user_profile
$organization = $client->getOrganization();
echo "Organization code: " . $organization['orgCode'] . "\n";

$userOrgs = $client->getUserOrganizations();
echo "User organizations: " . implode(', ', $userOrgs['orgCodes']) . "\n";
```

## Error Handling

When using `force_api`, API calls may fail due to network issues, authentication problems, or rate limiting. Always wrap your calls in try-catch blocks:

```php
try {
    $permissions = $client->getPermissions();
    // Process permissions
} catch (Exception $e) {
    // Handle API errors gracefully
    error_log("Failed to get permissions: " . $e->getMessage());
    // Fall back to default behavior or show error message
}
```

## Cache Management

The SDK uses intelligent caching to minimize API calls. You can manage the cache as needed:

```php
// Clear all cached data when you need fresh information
$client->clearApiCache();

// Now subsequent calls will make new API requests
$freshPermissions = $client->getPermissions();
$freshFeatureFlags = $client->getBooleanFlag('new_feature');
```

**When to clear cache:**
- After user permissions are updated
- When feature flags are changed
- When user profile information is modified
- When you need to ensure data freshness

## Performance Considerations

- **Targeted API calls**: Each method only calls the API it needs
- **Intelligent caching**: Caches API responses to avoid repeated calls
- **Lazy loading**: Only fetches data when specifically requested
- **Cache management**: Use `clearApiCache()` when you need fresh data
- **Performance**: Optimized to minimize API calls while maintaining real-time data
- **Rate limiting**: Subject to API rate limits but minimized through caching

## Migration Guide

### From Token-based to API-based

If you're migrating from token-based claims to API-based claims:

1. **Enable force_api**: Set the flag in your client configuration
2. **Update error handling**: Add try-catch blocks around claim requests
3. **Test thoroughly**: Ensure your application handles API failures gracefully
4. **Monitor performance**: Watch for any performance impacts
5. **Consider caching**: Implement caching for frequently accessed data

### Example Migration

**Before (Token-based):**
```php
$client = new KindeClientSDK(/* ... */);
$permissions = $client->getPermissions(); // Uses token parsing
$featureFlag = $client->getBooleanFlag('new_feature'); // Uses token parsing
```

**After (API-based with targeted calls):**
```php
$client = new KindeClientSDK(/* ... */, true); // Enable force_api
try {
    $permissions = $client->getPermissions(); // Only calls /account_api/v1/permissions
    $featureFlag = $client->getBooleanFlag('new_feature'); // Only calls /account_api/v1/feature_flags
    $organization = $client->getOrganization(); // Only calls /oauth2/v2/user_profile
} catch (Exception $e) {
    // Handle API errors
    $permissions = ['orgCode' => null, 'permissions' => []];
}

// Clear cache when you need fresh data
$client->clearApiCache();
$freshPermissions = $client->getPermissions(); // Makes new API call
```

## Testing

Run the hardcheck tests to verify the functionality:

```bash
vendor/bin/phpunit tests/Unit/HardcheckTest.php
```

## API Specification

The hardcheck functionality uses the Kinde Frontend API specification, which is maintained in this project and can be updated using:

```bash
npm run update-frontend-spec
```

This downloads the latest API specification from `https://api-spec.kinde.com/kinde-frontend-api-spec.yaml`.

## Support

For questions or issues with the hardcheck functionality, please refer to the main Kinde documentation or contact support.
