# Kinde\KindeSDK\OrganizationsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganizationUsers()**](OrganizationsApi.md#addOrganizationUsers) | **POST** /api/v1/organizations/{org_code}/users | Add Organization Users
[**createOrganization()**](OrganizationsApi.md#createOrganization) | **POST** /api/v1/organization | Create Organization
[**createOrganizationUserPermission()**](OrganizationsApi.md#createOrganizationUserPermission) | **POST** /api/v1/organizations/{org_code}/users/{user_id}/permissions | Add Organization User Permission
[**createOrganizationUserRole()**](OrganizationsApi.md#createOrganizationUserRole) | **POST** /api/v1/organizations/{org_code}/users/{user_id}/roles | Add Organization User Role
[**deleteOrganization()**](OrganizationsApi.md#deleteOrganization) | **DELETE** /api/v1/organization/{org_code} | Delete Organization
[**deleteOrganizationFeatureFlagOverride()**](OrganizationsApi.md#deleteOrganizationFeatureFlagOverride) | **DELETE** /api/v1/organizations/{org_code}/feature_flags/{feature_flag_key} | Delete Organization Feature Flag Override
[**deleteOrganizationFeatureFlagOverrides()**](OrganizationsApi.md#deleteOrganizationFeatureFlagOverrides) | **DELETE** /api/v1/organizations/{org_code}/feature_flags | Delete Organization Feature Flag Overrides
[**deleteOrganizationUserPermission()**](OrganizationsApi.md#deleteOrganizationUserPermission) | **DELETE** /api/v1/organizations/{org_code}/users/{user_id}/permissions/{permission_id} | Delete Organization User Permission
[**deleteOrganizationUserRole()**](OrganizationsApi.md#deleteOrganizationUserRole) | **DELETE** /api/v1/organizations/{org_code}/users/{user_id}/roles/{role_id} | Delete Organization User Role
[**getOrganization()**](OrganizationsApi.md#getOrganization) | **GET** /api/v1/organization | Get Organization
[**getOrganizationFeatureFlags()**](OrganizationsApi.md#getOrganizationFeatureFlags) | **GET** /api/v1/organizations/{org_code}/feature_flags | List Organization Feature Flags
[**getOrganizationUserPermissions()**](OrganizationsApi.md#getOrganizationUserPermissions) | **GET** /api/v1/organizations/{org_code}/users/{user_id}/permissions | List Organization User Permissions
[**getOrganizationUserRoles()**](OrganizationsApi.md#getOrganizationUserRoles) | **GET** /api/v1/organizations/{org_code}/users/{user_id}/roles | List Organization User Roles
[**getOrganizationUsers()**](OrganizationsApi.md#getOrganizationUsers) | **GET** /api/v1/organizations/{org_code}/users | List Organization Users
[**getOrganizations()**](OrganizationsApi.md#getOrganizations) | **GET** /api/v1/organizations | List Organizations
[**removeOrganizationUser()**](OrganizationsApi.md#removeOrganizationUser) | **DELETE** /api/v1/organizations/{org_code}/users/{user_id} | Remove Organization User
[**updateOrganization()**](OrganizationsApi.md#updateOrganization) | **PATCH** /api/v1/organization/{org_code} | Update Organization
[**updateOrganizationFeatureFlagOverride()**](OrganizationsApi.md#updateOrganizationFeatureFlagOverride) | **PATCH** /api/v1/organizations/{org_code}/feature_flags/{feature_flag_key} | Update Organization Feature Flag Override
[**updateOrganizationUsers()**](OrganizationsApi.md#updateOrganizationUsers) | **PATCH** /api/v1/organizations/{org_code}/users | Update Organization Users


## `addOrganizationUsers()`

```php
addOrganizationUsers($org_code, $add_organization_users_request): \Kinde\KindeSDK\Model\AddOrganizationUsersResponse
```

Add Organization Users

Add existing users to an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$add_organization_users_request = new \Kinde\KindeSDK\Model\AddOrganizationUsersRequest(); // \Kinde\KindeSDK\Model\AddOrganizationUsersRequest

try {
    $result = $apiInstance->addOrganizationUsers($org_code, $add_organization_users_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->addOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **add_organization_users_request** | [**\Kinde\KindeSDK\Model\AddOrganizationUsersRequest**](../Model/AddOrganizationUsersRequest.md)|  | [optional]

### Return type

[**\Kinde\KindeSDK\Model\AddOrganizationUsersResponse**](../Model/AddOrganizationUsersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createOrganization()`

```php
createOrganization($create_organization_request): \Kinde\KindeSDK\Model\CreateOrganizationResponse
```

Create Organization

Create an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$create_organization_request = new \Kinde\KindeSDK\Model\CreateOrganizationRequest(); // \Kinde\KindeSDK\Model\CreateOrganizationRequest | Organization details.

try {
    $result = $apiInstance->createOrganization($create_organization_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->createOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_organization_request** | [**\Kinde\KindeSDK\Model\CreateOrganizationRequest**](../Model/CreateOrganizationRequest.md)| Organization details. |

### Return type

[**\Kinde\KindeSDK\Model\CreateOrganizationResponse**](../Model/CreateOrganizationResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createOrganizationUserPermission()`

```php
createOrganizationUserPermission($org_code, $user_id, $create_organization_user_permission_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Add Organization User Permission

Add permission to an organization user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.
$create_organization_user_permission_request = new \Kinde\KindeSDK\Model\CreateOrganizationUserPermissionRequest(); // \Kinde\KindeSDK\Model\CreateOrganizationUserPermissionRequest | Permission details.

try {
    $result = $apiInstance->createOrganizationUserPermission($org_code, $user_id, $create_organization_user_permission_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->createOrganizationUserPermission: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |
 **create_organization_user_permission_request** | [**\Kinde\KindeSDK\Model\CreateOrganizationUserPermissionRequest**](../Model/CreateOrganizationUserPermissionRequest.md)| Permission details. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createOrganizationUserRole()`

```php
createOrganizationUserRole($org_code, $user_id, $create_organization_user_role_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Add Organization User Role

Add role to an organization user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.
$create_organization_user_role_request = new \Kinde\KindeSDK\Model\CreateOrganizationUserRoleRequest(); // \Kinde\KindeSDK\Model\CreateOrganizationUserRoleRequest | Role details.

try {
    $result = $apiInstance->createOrganizationUserRole($org_code, $user_id, $create_organization_user_role_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->createOrganizationUserRole: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |
 **create_organization_user_role_request** | [**\Kinde\KindeSDK\Model\CreateOrganizationUserRoleRequest**](../Model/CreateOrganizationUserRoleRequest.md)| Role details. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganization()`

```php
deleteOrganization($org_code)
```

Delete Organization

Delete an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.

try {
    $apiInstance->deleteOrganization($org_code);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |

### Return type

void (empty response body)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganizationFeatureFlagOverride()`

```php
deleteOrganizationFeatureFlagOverride($org_code, $feature_flag_key): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Organization Feature Flag Override

Delete organization feature flag override.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag.

try {
    $result = $apiInstance->deleteOrganizationFeatureFlagOverride($org_code, $feature_flag_key);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |
 **feature_flag_key** | **string**| The identifier for the feature flag. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganizationFeatureFlagOverrides()`

```php
deleteOrganizationFeatureFlagOverrides($org_code): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Organization Feature Flag Overrides

Delete all organization feature flag overrides.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.

try {
    $result = $apiInstance->deleteOrganizationFeatureFlagOverrides($org_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationFeatureFlagOverrides: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganizationUserPermission()`

```php
deleteOrganizationUserPermission($org_code, $user_id, $permission_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Organization User Permission

Delete permission for an organization user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.
$permission_id = 'permission_id_example'; // string | The permission id.

try {
    $result = $apiInstance->deleteOrganizationUserPermission($org_code, $user_id, $permission_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationUserPermission: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |
 **permission_id** | **string**| The permission id. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganizationUserRole()`

```php
deleteOrganizationUserRole($org_code, $user_id, $role_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Organization User Role

Delete role for an organization user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.
$role_id = 'role_id_example'; // string | The role id.

try {
    $result = $apiInstance->deleteOrganizationUserRole($org_code, $user_id, $role_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationUserRole: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |
 **role_id** | **string**| The role id. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganization()`

```php
getOrganization($code): \Kinde\KindeSDK\Model\Organization
```

Get Organization

Gets an organization given the organization's code.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$code = 'code_example'; // string | The organization's code.

try {
    $result = $apiInstance->getOrganization($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| The organization&#39;s code. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\Organization**](../Model/Organization.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganizationFeatureFlags()`

```php
getOrganizationFeatureFlags($org_code): \Kinde\KindeSDK\Model\GetOrganizationFeatureFlagsResponse
```

List Organization Feature Flags

Get all organization feature flags.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.

try {
    $result = $apiInstance->getOrganizationFeatureFlags($org_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationFeatureFlags: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationFeatureFlagsResponse**](../Model/GetOrganizationFeatureFlagsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganizationUserPermissions()`

```php
getOrganizationUserPermissions($org_code, $user_id, $expand): \Kinde\KindeSDK\Model\GetOrganizationsUserPermissionsResponse
```

List Organization User Permissions

Get permissions for an organization user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.
$expand = 'expand_example'; // string | Specify additional data to retrieve. Use \"roles\".

try {
    $result = $apiInstance->getOrganizationUserPermissions($org_code, $user_id, $expand);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationUserPermissions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |
 **expand** | **string**| Specify additional data to retrieve. Use \&quot;roles\&quot;. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationsUserPermissionsResponse**](../Model/GetOrganizationsUserPermissionsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganizationUserRoles()`

```php
getOrganizationUserRoles($org_code, $user_id): \Kinde\KindeSDK\Model\GetOrganizationsUserRolesResponse
```

List Organization User Roles

Get roles for an organization user.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.

try {
    $result = $apiInstance->getOrganizationUserRoles($org_code, $user_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationUserRoles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationsUserRolesResponse**](../Model/GetOrganizationsUserRolesResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganizationUsers()`

```php
getOrganizationUsers($org_code, $sort, $page_size, $next_token, $permissions, $roles): \Kinde\KindeSDK\Model\GetOrganizationUsersResponse
```

List Organization Users

Get users in an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.
$permissions = 'permissions_example'; // string | Filter by user permissions comma separated (where all match)
$roles = 'roles_example'; // string | Filter by user roles comma separated (where all match)

try {
    $result = $apiInstance->getOrganizationUsers($org_code, $sort, $page_size, $next_token, $permissions, $roles);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]
 **permissions** | **string**| Filter by user permissions comma separated (where all match) | [optional]
 **roles** | **string**| Filter by user roles comma separated (where all match) | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationUsersResponse**](../Model/GetOrganizationUsersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganizations()`

```php
getOrganizations($sort, $page_size, $next_token): \Kinde\KindeSDK\Model\GetOrganizationsResponse
```

List Organizations

Get a list of organizations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getOrganizations($sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationsResponse**](../Model/GetOrganizationsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `removeOrganizationUser()`

```php
removeOrganizationUser($org_code, $user_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Remove Organization User

Remove user from an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$user_id = 'user_id_example'; // string | The user's id.

try {
    $result = $apiInstance->removeOrganizationUser($org_code, $user_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->removeOrganizationUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **user_id** | **string**| The user&#39;s id. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateOrganization()`

```php
updateOrganization($org_code, $update_organization_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Update Organization

Update an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.
$update_organization_request = new \Kinde\KindeSDK\Model\UpdateOrganizationRequest(); // \Kinde\KindeSDK\Model\UpdateOrganizationRequest | Organization details.

try {
    $result = $apiInstance->updateOrganization($org_code, $update_organization_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->updateOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |
 **update_organization_request** | [**\Kinde\KindeSDK\Model\UpdateOrganizationRequest**](../Model/UpdateOrganizationRequest.md)| Organization details. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateOrganizationFeatureFlagOverride()`

```php
updateOrganizationFeatureFlagOverride($org_code, $feature_flag_key, $value): \Kinde\KindeSDK\Model\SuccessResponse
```

Update Organization Feature Flag Override

Update organization feature flag override.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag
$value = 'value_example'; // string | Override value

try {
    $result = $apiInstance->updateOrganizationFeatureFlagOverride($org_code, $feature_flag_key, $value);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->updateOrganizationFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization |
 **feature_flag_key** | **string**| The identifier for the feature flag |
 **value** | **string**| Override value |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateOrganizationUsers()`

```php
updateOrganizationUsers($org_code, $update_organization_users_request): \Kinde\KindeSDK\Model\UpdateOrganizationUsersResponse
```

Update Organization Users

Update users that belong to an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');




...
use Kinde\KindeSDK\Configuration;
...

$config = new Configuration();
$config->setHost(YOUR_KINDE_HOST);

// You can also set `access_token` via $config;
$config->setAccessToken(`kinde_access_token`);

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The organization's code.
$update_organization_users_request = new \Kinde\KindeSDK\Model\UpdateOrganizationUsersRequest(); // \Kinde\KindeSDK\Model\UpdateOrganizationUsersRequest

try {
    $result = $apiInstance->updateOrganizationUsers($org_code, $update_organization_users_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->updateOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The organization&#39;s code. |
 **update_organization_users_request** | [**\Kinde\KindeSDK\Model\UpdateOrganizationUsersRequest**](../Model/UpdateOrganizationUsersRequest.md)|  | [optional]

### Return type

[**\Kinde\KindeSDK\Model\UpdateOrganizationUsersResponse**](../Model/UpdateOrganizationUsersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
