# Namespace Duplication Fix

## Issue Description

The OpenAPI generator was creating duplicated namespaces for the Frontend API models and classes:

**Before (Incorrect):**
```php
namespace Kinde\KindeSDK\Kinde\KindeSDK\Model\Frontend;
namespace Kinde\KindeSDK\Kinde\KindeSDK\Api\Frontend;
```

**After (Correct):**
```php
namespace Kinde\KindeSDK\Model\Frontend;
namespace Kinde\KindeSDK\Api\Frontend;
```

## Root Cause

The issue was in the `package.json` file in the `generate-frontend` script. The configuration was using absolute paths for `apiPackage` and `modelPackage` instead of relative paths to the `invokerPackage`.

**Problematic Configuration:**
```json
"generate-frontend": "openapi-generator-cli generate -i ./kinde-frontend-api.yaml -g php -o ./tmp-frontend --additional-properties=invokerPackage='Kinde\\\\KindeSDK',apiPackage='Kinde\\\\KindeSDK\\\\Api\\\\Frontend',modelPackage='Kinde\\\\KindeSDK\\\\Model\\\\Frontend'"
```

**Fixed Configuration:**
```json
"generate-frontend": "openapi-generator-cli generate -i ./kinde-frontend-api.yaml -g php -o ./tmp-frontend --additional-properties=invokerPackage='Kinde\\\\KindeSDK',apiPackage='Api\\\\Frontend',modelPackage='Model\\\\Frontend'"
```

## How OpenAPI Generator Works

The OpenAPI generator for PHP uses these properties:

- `invokerPackage`: The base namespace for the entire generated code
- `apiPackage`: The sub-namespace for API classes (relative to invokerPackage)
- `modelPackage`: The sub-namespace for model classes (relative to invokerPackage)

When `apiPackage` and `modelPackage` are specified as absolute paths (starting with the full namespace), the generator treats them as complete namespaces, resulting in duplication.

## Files Updated

1. **`package.json`**: Fixed the `generate-frontend` script configuration
2. **`lib/KindeClientSDK.php`**: Updated import statements to use correct namespaces

## Testing

After the fix:
- ✅ Generated code has correct namespaces
- ✅ All unit tests pass
- ✅ No more "Class not found" errors related to namespace duplication
- ✅ Laravel example works correctly with entitlements functionality

## Prevention

To prevent this issue in the future:
- Always use relative paths for `apiPackage` and `modelPackage` in OpenAPI generator configuration
- The paths should be relative to the `invokerPackage`
- Test the generated code to ensure namespaces are correct
- Regenerate Frontend API code after any package.json changes

## Laravel Example Fix

The Laravel example was also affected by this namespace issue. After fixing the package.json configuration:

1. **Regenerated Frontend API code** with correct namespaces
2. **Updated Laravel autoloader** to pick up the corrected files
3. **Verified functionality** - the user-info page now works correctly with entitlements

The Laravel example now successfully demonstrates all entitlements functionality without namespace errors.

## Test Suite Recovery

After regenerating the Frontend API code, the test suite was temporarily broken due to a missing `OAuthApi.php` file. This was resolved by:

1. **Identified the missing file**: `lib/Api/OAuthApi.php` was accidentally removed during the regeneration process
2. **Restored from backup**: Copied the file from `kinde-php-sdk-master/lib/Api/OAuthApi.php`
3. **Verified all tests pass**: All 41 unit tests and 127 assertions now pass successfully

**Lesson learned**: When regenerating API code, ensure that existing management API files are preserved and not accidentally overwritten.

## OAuthApi Cleanup

After investigating the missing `OAuthApi.php` file, it was discovered that this file contained **frontend API endpoints** that were incorrectly placed in the management API directory. The endpoints (`getUserProfileV2`, `tokenIntrospection`, `tokenRevocation`) are not defined in the management API specification and should only exist in the Frontend API.

### **Changes Made**

1. **Removed incorrect OAuthApi**: The `lib/Api/OAuthApi.php` file was removed as it contained frontend endpoints
2. **Updated KindeManagementClient**: Removed references to `OAuthApi` from the management client
3. **Updated Examples**: Fixed all framework examples to use `KindeClientSDK->getUserDetails()` instead of `$management->oauth->getUserProfileV2()`
4. **Updated Tests**: Removed OAuthApi tests from `KindeManagementClientTest`

### **Correct API Usage**

- **Management API**: For server-to-server operations (users, organizations, applications, etc.)
- **Frontend API**: For user authentication and user-specific operations (user profile, entitlements, etc.)

### **Examples Updated**

- **Laravel**: `examples/laravel/app/Http/Controllers/ExampleController.php`
- **CodeIgniter**: `examples/codeigniter/app/Controllers/ExampleController.php`  
- **Symfony**: `examples/symfony/src/Controller/KindeController.php`

All examples now correctly use `$this->kindeClient->getUserDetails()` for user profile information instead of the incorrect management API call. 