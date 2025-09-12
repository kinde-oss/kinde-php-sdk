<?php

/**
 * Kinde PHP SDK - Has Functionality Examples
 * 
 * This file demonstrates the comprehensive authorization checking
 * capabilities of the Kinde PHP SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Kinde\KindeSDK\KindeClientSDK;

// Initialize the Kinde client
$kindeClient = new KindeClientSDK(
    domain: 'https://yourapp.kinde.com',
    redirectUri: 'http://localhost:3000/callback',
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    grantType: 'authorization_code'
);

echo "=== Kinde PHP SDK - Has Functionality Examples ===\n\n";

// Example 1: Simple Role Check
echo "1. Simple Role Check\n";
try {
    $hasAdminRole = $kindeClient->hasRoles(['admin']);
    echo "User has admin role: " . ($hasAdminRole ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking roles: " . $e->getMessage() . "\n";
}

// Example 2: Multiple Role Check
echo "\n2. Multiple Role Check\n";
try {
    $hasMultipleRoles = $kindeClient->hasRoles(['admin', 'manager']);
    echo "User has admin AND manager roles: " . ($hasMultipleRoles ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking multiple roles: " . $e->getMessage() . "\n";
}

// Example 3: Permission Check
echo "\n3. Permission Check\n";
try {
    $canEdit = $kindeClient->hasPermissions(['canEdit', 'canDelete']);
    echo "User can edit and delete: " . ($canEdit ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking permissions: " . $e->getMessage() . "\n";
}

// Example 4: Feature Flag Check
echo "\n4. Feature Flag Check\n";
try {
    $hasFeatureFlags = $kindeClient->hasFeatureFlags([
        'darkMode',
        ['flag' => 'theme', 'value' => 'dark']
    ]);
    echo "User has dark mode and theme=dark: " . ($hasFeatureFlags ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking feature flags: " . $e->getMessage() . "\n";
}

// Example 5: Billing Entitlements Check
echo "\n5. Billing Entitlements Check\n";
try {
    $hasPremium = $kindeClient->hasBillingEntitlements(['premium']);
    echo "User has premium entitlement: " . ($hasPremium ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking billing entitlements: " . $e->getMessage() . "\n";
}

// Example 6: Unified Has Check
echo "\n6. Unified Has Check - All Conditions\n";
try {
    $hasAllAccess = $kindeClient->has([
        'roles' => ['admin'],
        'permissions' => ['canEdit'],
        'featureFlags' => ['adminUI'],
        'billingEntitlements' => ['premium']
    ]);
    echo "User has complete admin access: " . ($hasAllAccess ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking unified conditions: " . $e->getMessage() . "\n";
}

// Example 7: Custom Conditions
echo "\n7. Custom Role Condition\n";
try {
    $hasCustomRole = $kindeClient->hasRoles([
        [
            'role' => 'manager',
            'condition' => function($role) {
                // Check if this is a senior manager
                return strpos($role['name'] ?? '', 'Senior') !== false;
            }
        ]
    ]);
    echo "User is a senior manager: " . ($hasCustomRole ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking custom role condition: " . $e->getMessage() . "\n";
}

// Example 8: Custom Permission Condition
echo "\n8. Custom Permission Condition\n";
try {
    $hasOrgPermission = $kindeClient->hasPermissions([
        [
            'permission' => 'canEdit',
            'condition' => function($context) {
                // Check if user can edit in the main organization
                return $context['orgCode'] === 'main-org';
            }
        ]
    ]);
    echo "User can edit in main org: " . ($hasOrgPermission ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking custom permission condition: " . $e->getMessage() . "\n";
}

// Example 9: Custom Entitlement Condition
echo "\n9. Custom Entitlement Condition\n";
try {
    $hasApiAccess = $kindeClient->hasBillingEntitlements([
        [
            'entitlement' => 'api-access',
            'condition' => function($entitlement) {
                // Check if user has more than 1000 API calls available
                $limit = $entitlement->getEntitlementLimitMax();
                return $limit > 1000;
            }
        ]
    ]);
    echo "User has high API access: " . ($hasApiAccess ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking custom entitlement condition: " . $e->getMessage() . "\n";
}

// Example 10: Force API Usage
echo "\n10. Force API Usage\n";
try {
    $hasRoleFromApi = $kindeClient->hasRoles(['admin'], true); // Force API call
    echo "User has admin role (from API): " . ($hasRoleFromApi ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking role with forced API: " . $e->getMessage() . "\n";
}

// Example 11: Selective Force API
echo "\n11. Selective Force API\n";
try {
    $hasAccessSelective = $kindeClient->has([
        'roles' => ['admin'],
        'permissions' => ['canEdit']
    ], [
        'roles' => true,        // Force API for roles
        'permissions' => false  // Use token for permissions
    ]);
    echo "User has selective access: " . ($hasAccessSelective ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "Error checking with selective force API: " . $e->getMessage() . "\n";
}

// Example 12: Real-world Authorization Function
echo "\n12. Real-world Authorization Example\n";

function canAccessAdminPanel($kindeClient): array {
    try {
        $canAccess = $kindeClient->has([
            'roles' => ['admin', 'super-admin'],
            'permissions' => ['admin:view', 'users:manage'],
            'featureFlags' => ['adminPanel'],
            'billingEntitlements' => ['admin-features']
        ]);
        
        return [
            'authorized' => $canAccess,
            'message' => $canAccess ? 'Access granted to admin panel' : 'Insufficient permissions for admin panel'
        ];
    } catch (Exception $e) {
        return [
            'authorized' => false,
            'message' => 'Authorization check failed: ' . $e->getMessage()
        ];
    }
}

$adminAccess = canAccessAdminPanel($kindeClient);
echo "Admin panel access: " . $adminAccess['message'] . "\n";

// Example 13: Content Management Authorization
echo "\n13. Content Management Authorization\n";

function canManageContent($kindeClient, $contentOrgCode = null): array {
    try {
        $conditions = [
            'roles' => ['content-manager', 'admin'],
            'permissions' => ['content:create', 'content:edit', 'content:delete'],
            'featureFlags' => ['contentManagement']
        ];

        // Add organization-specific permission check if org code provided
        if ($contentOrgCode) {
            $conditions['permissions'][] = [
                'permission' => 'content:edit',
                'condition' => function($context) use ($contentOrgCode) {
                    return $context['orgCode'] === $contentOrgCode;
                }
            ];
        }

        $canManage = $kindeClient->has($conditions);
        
        return [
            'authorized' => $canManage,
            'message' => $canManage 
                ? 'Can manage content' . ($contentOrgCode ? " in org {$contentOrgCode}" : '')
                : 'Cannot manage content - insufficient permissions'
        ];
    } catch (Exception $e) {
        return [
            'authorized' => false,
            'message' => 'Content management check failed: ' . $e->getMessage()
        ];
    }
}

$contentAccess = canManageContent($kindeClient, 'main-org');
echo "Content management: " . $contentAccess['message'] . "\n";

echo "\n=== Examples completed ===\n";
