# Laravel Example with Entitlements

This Laravel example demonstrates how to use the Kinde PHP SDK's entitlements functionality.

## Features Demonstrated

### 1. User Authentication
- Login/logout functionality
- User profile information
- Permissions and roles
- Organization details

### 2. Entitlements Integration
The example now includes comprehensive entitlements functionality:

#### Available Methods
- `getAllEntitlements()` - Get all user entitlements with automatic pagination
- `getEntitlement(string $key)` - Get a specific entitlement by feature key
- `hasEntitlement(string $key)` - Check if user has a specific entitlement
- `getEntitlementLimit(string $key)` - Get the maximum limit for an entitlement

#### Displayed Information
- **Entitlement Cards**: Each entitlement is displayed in a clean card format showing:
  - Feature name and key
  - Maximum and minimum limits
  - Unit amounts
  - Fixed charges (formatted as currency)
  - Price plan names

- **Method Demonstrations**: Shows how the various entitlements methods work:
  - `hasEntitlement()` results for existing and non-existent features
  - `getEntitlementLimit()` results
  - `getEntitlement()` detailed object

- **Error Handling**: Graceful error display if entitlements cannot be retrieved

## How to Access

1. **Start the Laravel application**:
   ```bash
   cd examples/laravel
   composer install
   php artisan serve
   ```

2. **Navigate to the user info page**:
   ```
   http://localhost:8000/auth/user-info
   ```

3. **Authentication required**: You must be logged in to view entitlements

## File Structure

### Updated Files
- `app/Http/Controllers/ExampleController.php` - Added entitlements to the example controller
- `resources/views/kinde/user-info.blade.php` - Enhanced view with entitlements display
- `lib/Frameworks/Laravel/Controllers/KindeAuthController.php` - Updated to include entitlements

### Key Features
- **Responsive Design**: Uses Tailwind CSS for mobile-friendly layout
- **Error Handling**: Displays errors gracefully if entitlements cannot be retrieved
- **Raw Data Display**: Shows both formatted cards and raw data for debugging
- **Method Demonstrations**: Shows how to use all entitlements methods

## Entitlements Data Structure

Each entitlement object contains:
- `featureKey` - Unique identifier for the feature
- `featureName` - Human-readable feature name
- `entitlementLimitMax` - Maximum allowed limit
- `entitlementLimitMin` - Minimum required limit
- `unitAmount` - Cost per unit
- `fixedCharge` - Fixed cost (in cents)
- `priceName` - Name of the pricing plan

## Usage Examples

```php
// Get all entitlements
$entitlements = $kindeClient->getAllEntitlements();

// Check if user has specific entitlement
if ($kindeClient->hasEntitlement('premium_features')) {
    $limit = $kindeClient->getEntitlementLimit('premium_features');
    echo "User has premium features with limit: " . $limit;
}

// Get specific entitlement details
$entitlement = $kindeClient->getEntitlement('api_calls');
if ($entitlement) {
    echo "API calls limit: " . $entitlement->getEntitlementLimitMax();
}
```

## Notes

- Entitlements require user authentication
- The SDK automatically handles pagination for large entitlement lists
- All entitlements methods are available through the `KindeClientSDK` instance
- Error handling is built into the SDK methods 