# Kinde\KindeSDK\RolesApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createRole()**](RolesApi.md#createRole) | **POST** /api/v1/roles | Create Role
[**deleteRole()**](RolesApi.md#deleteRole) | **DELETE** /api/v1/roles/{role_id} | Delete Role
[**getRolePermission()**](RolesApi.md#getRolePermission) | **GET** /api/v1/roles/{role_id}/permissions | Get Role Permissions
[**getRoles()**](RolesApi.md#getRoles) | **GET** /api/v1/roles | List Roles
[**removeRolePermission()**](RolesApi.md#removeRolePermission) | **DELETE** /api/v1/roles/{role_id}/permissions/{permission_id} | Remove Role Permission
[**updateRolePermissions()**](RolesApi.md#updateRolePermissions) | **PATCH** /api/v1/roles/{role_id}/permissions | Update Role Permissions
[**updateRoles()**](RolesApi.md#updateRoles) | **PATCH** /api/v1/roles/{role_id} | Update Role


## `createRole()`

```php
createRole($create_role_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Create Role

Create role.

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$create_role_request = new \Kinde\KindeSDK\Model\CreateRoleRequest(); // \Kinde\KindeSDK\Model\CreateRoleRequest | Role details.

try {
    $result = $apiInstance->createRole($create_role_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->createRole: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_role_request** | [**\Kinde\KindeSDK\Model\CreateRoleRequest**](../Model/CreateRoleRequest.md)| Role details. | [optional]

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

## `deleteRole()`

```php
deleteRole($role_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Role

Delete role

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$role_id = 'role_id_example'; // string | The identifier for the role.

try {
    $result = $apiInstance->deleteRole($role_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->deleteRole: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **role_id** | **string**| The identifier for the role. |

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

## `getRolePermission()`

```php
getRolePermission($role_id, $sort, $page_size, $next_token): \Kinde\KindeSDK\Model\RolesPermissionResponseInner[]
```

Get Role Permissions

Get permissions for a role.

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$role_id = 'role_id_example'; // string | The role's public id.
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getRolePermission($role_id, $sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->getRolePermission: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **role_id** | **string**| The role&#39;s public id. |
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\RolesPermissionResponseInner[]**](../Model/RolesPermissionResponseInner.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getRoles()`

```php
getRoles($sort, $page_size, $next_token): \Kinde\KindeSDK\Model\GetRolesResponse
```

List Roles

The returned list can be sorted by role name or role ID in ascending or descending order. The number of records to return at a time can also be controlled using the `page_size` query string parameter.

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getRoles($sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->getRoles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetRolesResponse**](../Model/GetRolesResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `removeRolePermission()`

```php
removeRolePermission($role_id, $permission_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Remove Role Permission

Remove a permission from a role.

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$role_id = 'role_id_example'; // string | The role's public id.
$permission_id = 'permission_id_example'; // string | The permission's public id.

try {
    $result = $apiInstance->removeRolePermission($role_id, $permission_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->removeRolePermission: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **role_id** | **string**| The role&#39;s public id. |
 **permission_id** | **string**| The permission&#39;s public id. |

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

## `updateRolePermissions()`

```php
updateRolePermissions($role_id, $update_role_permissions_request): \Kinde\KindeSDK\Model\UpdateRolePermissionsResponse
```

Update Role Permissions

Update role permissions.

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$role_id = 'role_id_example'; // string | The identifier for the role.
$update_role_permissions_request = new \Kinde\KindeSDK\Model\UpdateRolePermissionsRequest(); // \Kinde\KindeSDK\Model\UpdateRolePermissionsRequest

try {
    $result = $apiInstance->updateRolePermissions($role_id, $update_role_permissions_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->updateRolePermissions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **role_id** | **string**| The identifier for the role. |
 **update_role_permissions_request** | [**\Kinde\KindeSDK\Model\UpdateRolePermissionsRequest**](../Model/UpdateRolePermissionsRequest.md)|  |

### Return type

[**\Kinde\KindeSDK\Model\UpdateRolePermissionsResponse**](../Model/UpdateRolePermissionsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateRoles()`

```php
updateRoles($role_id, $update_roles_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Update Role

Update a role

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

$apiInstance = new Kinde\KindeSDK\Api\RolesApi($config);
$role_id = 'role_id_example'; // string | The identifier for the role.
$update_roles_request = new \Kinde\KindeSDK\Model\UpdateRolesRequest(); // \Kinde\KindeSDK\Model\UpdateRolesRequest | Role details.

try {
    $result = $apiInstance->updateRoles($role_id, $update_roles_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RolesApi->updateRoles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **role_id** | **string**| The identifier for the role. |
 **update_roles_request** | [**\Kinde\KindeSDK\Model\UpdateRolesRequest**](../Model/UpdateRolesRequest.md)| Role details. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
