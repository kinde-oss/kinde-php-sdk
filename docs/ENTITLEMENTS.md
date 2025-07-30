# Entitlements

The Kinde PHP SDK provides functionality to access user entitlements through the frontend API. Entitlements are billing-related features that users have access to based on their subscription plan.

## Overview

Entitlements functionality allows you to:
- Get all entitlements for the authenticated user
- Check if a user has a specific entitlement
- Get the limits for specific entitlements
- Retrieve detailed information about entitlements

## Authentication

All entitlements methods require the user to be authenticated. The SDK will automatically use the user's access token to make authenticated requests to the frontend API.

## Methods

### getAllEntitlements()

Get all entitlements for the authenticated user, handling pagination automatically.

```php
$entitlements = $kinde->getAllEntitlements();
```

**Parameters:** None

**Returns:** Array of `GetEntitlementsResponseDataEntitlementsInner` objects

**Throws:** `Exception` if user is not authenticated or API request fails

### getEntitlement(string $key)

Get a specific entitlement by its feature key.

```php
$entitlement = $kinde->getEntitlement('premium_features');
```

**Parameters:**
- `$key`: The entitlement feature key to retrieve

**Returns:** `GetEntitlementsResponseDataEntitlementsInner|null` - The entitlement or null if not found

**Throws:** `Exception` if user is not authenticated or API request fails

### hasEntitlement(string $key)

Check if the user has a specific entitlement.

```php
if ($kinde->hasEntitlement('premium_features')) {
    echo "User has premium features";
}
```

**Parameters:**
- `$key`: The entitlement feature key to check

**Returns:** `bool` - True if the user has the entitlement, false otherwise

**Throws:** `Exception` if user is not authenticated or API request fails

### getEntitlementLimit(string $key)

Get the maximum limit for a specific entitlement.

```php
$limit = $kinde->getEntitlementLimit('api_calls');
echo "User can make up to " . $limit . " API calls";
```

**Parameters:**
- `$key`: The entitlement feature key

**Returns:** `int|null` - The maximum limit or null if not found

**Throws:** `Exception` if user is not authenticated or API request fails

## Entitlement Object Properties

Each entitlement object contains the following properties:

- `getId()`: Unique identifier for the entitlement
- `getFeatureKey()`: The feature key (e.g., 'premium_features', 'api_calls')
- `getFeatureName()`: Human-readable name for the feature
- `getEntitlementLimitMax()`: Maximum limit for this entitlement
- `getEntitlementLimitMin()`: Minimum limit for this entitlement
- `getUnitAmount()`: Unit amount for billing
- `getPriceName()`: Name of the pricing tier
- `getFixedCharge()`: Fixed charge amount

## Example Usage

```php
<?php

use Kinde\KindeSDK\KindeClientSDK;

// Initialize the SDK
$kinde = new KindeClientSDK(
    domain: 'https://your-domain.kinde.com',
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    redirectUri: 'http://localhost:8000/callback'
);

// Ensure user is authenticated (this would typically happen through login flow)
// ...

try {
    // Get all entitlements
    $entitlements = $kinde->getAllEntitlements();
    
    // Check for specific entitlements
    if ($kinde->hasEntitlement('premium_features')) {
        $limit = $kinde->getEntitlementLimit('premium_features');
        echo "User has premium features with limit: " . $limit;
    }
    
    // Get specific entitlement details
    $entitlement = $kinde->getEntitlement('api_calls');
    if ($entitlement) {
        echo "API Calls Limit: " . $entitlement->getEntitlementLimitMax();
        echo "Feature Name: " . $entitlement->getFeatureName();
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Error Handling

The entitlements methods will throw exceptions in the following cases:

- User is not authenticated
- Access token is missing or invalid
- API request fails
- Network connectivity issues

Always wrap entitlements calls in try-catch blocks to handle potential errors gracefully.

## Pagination

The `getAllEntitlements()` method automatically handles pagination internally. It will fetch all entitlements across multiple pages and return them as a single array, so you don't need to worry about pagination details.

## Frontend API Integration

The entitlements functionality uses the Kinde frontend API (`/account_api/v1/entitlements`) rather than the management API. This means:

- It requires user authentication (not client credentials)
- It returns entitlements for the authenticated user
- It's designed for client-side applications

For server-to-server operations that need to access entitlements for any user, use the KindeManagementClient instead. 