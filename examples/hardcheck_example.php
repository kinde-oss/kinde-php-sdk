<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;

/**
 * Example demonstrating the hardcheck functionality with force_api flag
 * 
 * This example shows how to use the KindeClientSDK with the force_api flag enabled,
 * which forces the SDK to use API calls instead of token parsing for retrieving claims.
 */

// Example 1: Enable hardcheck via constructor parameter
echo "=== Example 1: Enable hardcheck via constructor parameter ===\n";

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

echo "Force API enabled: " . ($client->forceApi ? 'Yes' : 'No') . "\n\n";

// Example 2: Enable hardcheck via environment variable
echo "=== Example 2: Enable hardcheck via environment variable ===\n";

// Set environment variable
putenv('KINDE_FORCE_API=true');

$clientFromEnv = KindeClientSDK::createFromEnv();
echo "Force API enabled from environment: " . ($clientFromEnv->forceApi ? 'Yes' : 'No') . "\n\n";

// Example 3: Usage with hardcheck enabled
echo "=== Example 3: Usage with hardcheck enabled ===\n";

// Note: In a real application, you would first authenticate the user
// For this example, we'll show the structure but note that API calls will fail without proper authentication

try {
    // When force_api is enabled, these methods will use API calls instead of token parsing:
    
    // Get permissions from API
    $permissions = $client->getPermissions();
    echo "Permissions: " . json_encode($permissions) . "\n";
    
    // Check specific permission from API
    $hasPermission = $client->getPermission('read:users');
    echo "Has read:users permission: " . ($hasPermission['isGranted'] ? 'Yes' : 'No') . "\n";
    
    // Get organization from API
    $organization = $client->getOrganization();
    echo "Organization: " . json_encode($organization) . "\n";
    
    // Get user organizations from API
    $userOrgs = $client->getUserOrganizations();
    echo "User Organizations: " . json_encode($userOrgs) . "\n";
    
    // Get feature flags from API
    $booleanFlag = $client->getBooleanFlag('new_feature', false);
    echo "Boolean flag 'new_feature': " . json_encode($booleanFlag) . "\n";
    
    $stringFlag = $client->getStringFlag('theme', 'default');
    echo "String flag 'theme': " . json_encode($stringFlag) . "\n";
    
    $integerFlag = $client->getIntegerFlag('max_users', 10);
    echo "Integer flag 'max_users': " . json_encode($integerFlag) . "\n";
    
} catch (Exception $e) {
    echo "Error (expected without proper authentication): " . $e->getMessage() . "\n";
}

echo "\n=== API Endpoints Used When force_api is enabled ===\n";
echo "• Permissions: /account_api/v1/permissions (PermissionsApi::getUserPermissions)\n";
echo "• Feature Flags: /account_api/v1/feature_flags (FeatureFlagsApi::getFeatureFlags)\n";
echo "• User Profile: /oauth2/v2/user_profile (OAuthApi::getUserProfileV2)\n";
echo "• Entitlements: /account_api/v1/entitlements (BillingApi::getEntitlements)\n";
echo "• Roles: /account_api/v1/roles (RolesApi::getUserRoles)\n";
echo "• Properties: /account_api/v1/properties (PropertiesApi::getUserProperties)\n";

echo "\n=== Optimized API Usage ===\n";
echo "• Targeted API calls: Each method only calls the API it needs\n";
echo "• Stateless: No caching - always gets fresh data from the server\n";
echo "• Lazy loading: Only fetches data when specifically requested\n";
echo "• Real-time: Always reflects the latest server state\n";

echo "\n=== Benefits of Using force_api ===\n";
echo "• Real-time data: Always gets the latest data from the server\n";
echo "• No token dependency: Doesn't rely on JWT token claims\n";
echo "• Fresh permissions: Ensures permissions are up-to-date\n";
echo "• Dynamic feature flags: Gets the latest feature flag values\n";
echo "• Better security: Reduces reliance on client-side token data\n";
echo "• Efficient: Only makes API calls for the specific data you need\n";

echo "\n=== Considerations ===\n";
echo "• Network calls: Only makes API calls when data is requested\n";
echo "• Performance: Optimized with lazy loading - only fetches when needed\n";
echo "• Rate limiting: Subject to API rate limits\n";
echo "• Authentication required: User must be properly authenticated\n";
echo "• Error handling: API failures need to be handled gracefully\n";
echo "• Real-time updates: Always gets the latest data from the server\n";

echo "\n=== Real-time Data Example ===\n";
echo "// Each call gets fresh data from the server\n";
echo "\$permissions = \$client->getPermissions(); // Fresh API call\n";
echo "\$featureFlags = \$client->getFeatureFlags(); // Fresh API call\n";
echo "// No caching means always up-to-date information\n";
