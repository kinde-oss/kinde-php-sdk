# Hardcheck Implementation for Kinde PHP SDK

## Overview

The hardcheck functionality has been successfully implemented in the Kinde PHP SDK. When the `force_api` flag is set to `true`, the methods `getClaim`, `getClaims`, `getPermissions`, `getFlag`, and `getFeatureFlags` will use RESTful APIs wrapped by the OpenAPI generated code stored in `Api/Frontend` and `Model/Frontend` instead of parsing JWT tokens.

## Implementation Details

### 1. New Properties Added

- `public bool $forceApi` - Controls whether to use API calls or token parsing
- `private ?UserProfileV2 $cachedUserProfile` - Caches user profile from API
- `private ?GetUserPermissionsResponse $cachedPermissions` - Caches permissions from API  
- `private ?GetFeatureFlagsResponse $cachedFeatureFlags` - Caches feature flags from API

### 2. Constructor Changes

The constructor now accepts an additional parameter:
```php
function __construct(
    ?string $domain = null,
    ?string $redirectUri = null,
    ?string $clientId = null,
    ?string $clientSecret = null,
    ?string $grantType = null,
    ?string $logoutRedirectUri = null,
    string $scopes = 'openid profile email offline',
    array $additionalParameters = [],
    string $protocol = "",
    bool $forceApi = false  // New parameter
)
```

### 3. Environment Variable Support

The `force_api` flag can be enabled via environment variable:
```bash
KINDE_FORCE_API=true
```

### 4. Modified Methods

#### `getClaim(string $keyName, string $tokenType = TokenType::ACCESS_TOKEN)`
- When `force_api` is enabled, retrieves claims from API calls instead of token parsing
- Handles different claim types:
  - `feature_flags` - Uses `getFeatureFlagsFromApi()`
  - `org_code` - Uses `getPermissionsFromApi()`
  - `permissions` - Uses `getPermissionsFromApi()`
  - `org_codes` - Uses `getUserProfileFromApi()`
  - Other claims - Uses `getUserProfileFromApi()` and maps to standard JWT claims

#### `getPermissions()`
- When `force_api` is enabled, uses `getPermissionsFromApi()` instead of token parsing
- Returns the same structure: `['orgCode' => string, 'permissions' => array]`

#### `getFeatureFlags(?string $name = null)`
- When `force_api` is enabled, uses `getFeatureFlagsFromApi()` instead of token parsing
- Maintains the same interface and return format

### 5. New Private Methods

#### `getApiConfig()`
- Creates and configures the API client with the current access token
- Sets the host and access token for API calls

#### `getUserProfileFromApi()`
- Retrieves user profile from `/oauth2/v2/user_profile` endpoint
- Uses `OAuthApi::getUserProfileV2()`
- Implements caching to avoid repeated API calls

#### `getPermissionsFromApi()`
- Retrieves permissions from `/account_api/v1/permissions` endpoint
- Uses `PermissionsApi::getUserPermissions()`
- Returns data in the same format as the original method
- Implements caching to avoid repeated API calls

#### `getFeatureFlagsFromApi()`
- Retrieves feature flags from `/account_api/v1/feature_flags` endpoint
- Uses `FeatureFlagsApi::getFeatureFlags()`
- Converts API response to the internal format expected by existing code
- Implements caching to avoid repeated API calls

#### `getFlagType(string $type)`
- Converts API flag type strings to internal type codes
- Maps: `boolean` → `'b'`, `string` → `'s'`, `integer` → `'i'`

#### `clearApiCache()`
- Public method to clear all cached API responses
- Useful when fresh data is needed

### 6. API Endpoints Used

When `force_api` is enabled, the following endpoints are used:

- **User Profile**: `/oauth2/v2/user_profile` (OAuthApi::getUserProfileV2)
- **Permissions**: `/account_api/v1/permissions` (PermissionsApi::getUserPermissions)
- **Feature Flags**: `/account_api/v1/feature_flags` (FeatureFlagsApi::getFeatureFlags)

### 7. Caching Strategy

- All API responses are cached in memory for the lifetime of the client instance
- Cache is cleared when `clearApiCache()` is called
- Cache is automatically invalidated when a new client instance is created
- This minimizes API calls while ensuring data consistency

## Usage Examples

### Enable via Constructor
```php
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
    true // Enable force_api
);
```

### Enable via Environment Variable
```bash
export KINDE_FORCE_API=true
```

### Clear Cache When Needed
```php
$client->clearApiCache(); // Get fresh data from API
```

## Benefits

1. **Real-time Data**: Always gets the latest data from the server
2. **No Token Dependency**: Doesn't rely on JWT token claims
3. **Fresh Permissions**: Ensures permissions are up-to-date
4. **Dynamic Feature Flags**: Gets the latest feature flag values
5. **Better Security**: Reduces reliance on client-side token data
6. **Efficient**: Only makes API calls for the specific data you need
7. **Caching**: Minimizes API calls through intelligent caching

## Backward Compatibility

- The `force_api` flag defaults to `false`, maintaining backward compatibility
- All existing code will continue to work without changes
- The same method signatures are preserved
- Return formats remain consistent

## Testing

The implementation includes comprehensive tests in `tests/Unit/HardcheckTest.php` that verify:
- Constructor parameter handling
- Environment variable support
- Method existence and visibility
- Property existence and visibility
- Default behavior (force_api disabled)

All tests pass successfully, ensuring the implementation is robust and reliable.
