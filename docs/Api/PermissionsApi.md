# Kinde\KindeSDK\PermissionsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createPermission()**](PermissionsApi.md#createPermission) | **POST** /api/v1/permissions | Create Permission
[**deletePermission()**](PermissionsApi.md#deletePermission) | **DELETE** /api/v1/permissions/{permission_id} | Delete Permission
[**getPermissions()**](PermissionsApi.md#getPermissions) | **GET** /api/v1/permissions | List Permissions
[**updatePermissions()**](PermissionsApi.md#updatePermissions) | **PATCH** /api/v1/permissions/{permission_id} | Update Permission


## `createPermission()`

```php
createPermission($create_permission_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Create Permission

Create a new permission.

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

$apiInstance = new Kinde\KindeSDK\Api\PermissionsApi($config);
$create_permission_request = new \Kinde\KindeSDK\Model\CreatePermissionRequest(); // \Kinde\KindeSDK\Model\CreatePermissionRequest | Permission details.

try {
    $result = $apiInstance->createPermission($create_permission_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PermissionsApi->createPermission: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_permission_request** | [**\Kinde\KindeSDK\Model\CreatePermissionRequest**](../Model/CreatePermissionRequest.md)| Permission details. | [optional]

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

## `deletePermission()`

```php
deletePermission($permission_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Permission

Delete permission

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

$apiInstance = new Kinde\KindeSDK\Api\PermissionsApi($config);
$permission_id = 'permission_id_example'; // string | The identifier for the permission.

try {
    $result = $apiInstance->deletePermission($permission_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PermissionsApi->deletePermission: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **permission_id** | **string**| The identifier for the permission. |

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

## `getPermissions()`

```php
getPermissions($sort, $page_size, $next_token): \Kinde\KindeSDK\Model\GetPermissionsResponse
```

List Permissions

The returned list can be sorted by permission name or permission ID in ascending or descending order. The number of records to return at a time can also be controlled using the `page_size` query string parameter.

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

$apiInstance = new Kinde\KindeSDK\Api\PermissionsApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getPermissions($sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PermissionsApi->getPermissions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetPermissionsResponse**](../Model/GetPermissionsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updatePermissions()`

```php
updatePermissions($permission_id, $create_permission_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Update Permission

Update permission

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

$apiInstance = new Kinde\KindeSDK\Api\PermissionsApi($config);
$permission_id = 56; // int | The identifier for the permission.
$create_permission_request = new \Kinde\KindeSDK\Model\CreatePermissionRequest(); // \Kinde\KindeSDK\Model\CreatePermissionRequest | Permission details.

try {
    $result = $apiInstance->updatePermissions($permission_id, $create_permission_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PermissionsApi->updatePermissions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **permission_id** | **int**| The identifier for the permission. |
 **create_permission_request** | [**\Kinde\KindeSDK\Model\CreatePermissionRequest**](../Model/CreatePermissionRequest.md)| Permission details. | [optional]

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
