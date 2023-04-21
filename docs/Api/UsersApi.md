# Kinde\KindeSDK\UsersApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganizationUsers()**](UsersApi.md#addOrganizationUsers) | **POST** /api/v1/organization/users | Assign Users to an Organization
[**createUser()**](UsersApi.md#createUser) | **POST** /api/v1/user | Create User
[**deleteuser()**](UsersApi.md#deleteuser) | **DELETE** /api/v1/user | Delete User
[**getOrganizationUsers()**](UsersApi.md#getOrganizationUsers) | **GET** /api/v1/organization/users | List Organization Users
[**getUserData()**](UsersApi.md#getUserData) | **GET** /api/v1/user | Get User
[**getUsers()**](UsersApi.md#getUsers) | **GET** /api/v1/users | List Users
[**removeOrganizationUsers()**](UsersApi.md#removeOrganizationUsers) | **PATCH** /api/v1/organization/users | Remove Users from an Organization
[**updateUser()**](UsersApi.md#updateUser) | **PATCH** /api/v1/user | Update User


## `addOrganizationUsers()`

```php
addOrganizationUsers($code, $add_organization_users_request): \Kinde\KindeSDK\Model\AddOrganizationUsersResponse
```

Assign Users to an Organization

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$code = 'code_example'; // string | The organization's code.
$add_organization_users_request = new \Kinde\KindeSDK\Model\AddOrganizationUsersRequest(); // \Kinde\KindeSDK\Model\AddOrganizationUsersRequest

try {
    $result = $apiInstance->addOrganizationUsers($code, $add_organization_users_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->addOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| The organization&#39;s code. | [optional]
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

## `createUser()`

```php
createUser($create_user_request): \Kinde\KindeSDK\Model\CreateUserResponse
```

Create User

Creates a user record and optionally zero or more identities for the user. An example identity could be the email address of the user.

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$create_user_request = new \Kinde\KindeSDK\Model\CreateUserRequest(); // \Kinde\KindeSDK\Model\CreateUserRequest | The details of the user to create.

try {
    $result = $apiInstance->createUser($create_user_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->createUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_user_request** | [**\Kinde\KindeSDK\Model\CreateUserRequest**](../Model/CreateUserRequest.md)| The details of the user to create. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\CreateUserResponse**](../Model/CreateUserResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteuser()`

```php
deleteuser($id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete User

Delete a user record.

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$id = 'id_example'; // string | The user's id.

try {
    $result = $apiInstance->deleteuser($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->deleteuser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The user&#39;s id. | [optional]

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

## `getOrganizationUsers()`

```php
getOrganizationUsers($sort, $page_size, $next_token, $code, $permissions): \Kinde\KindeSDK\Model\GetOrganizationsUsersResponse
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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.
$code = 'code_example'; // string | The organization's code.
$permissions = 'permissions_example'; // string | Filter by user permissions

try {
    $result = $apiInstance->getOrganizationUsers($sort, $page_size, $next_token, $code, $permissions);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]
 **code** | **string**| The organization&#39;s code. | [optional]
 **permissions** | **string**| Filter by user permissions | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationsUsersResponse**](../Model/GetOrganizationsUsersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getUserData()`

```php
getUserData($id): \Kinde\KindeSDK\Model\User
```

Get User

Retrieve a user record.

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$id = 'id_example'; // string | The user's id.

try {
    $result = $apiInstance->getUserData($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getUserData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The user&#39;s id. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\User**](../Model/User.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getUsers()`

```php
getUsers($sort, $page_size, $user_id, $next_token): \Kinde\KindeSDK\Model\UsersResponse
```

List Users

The returned list can be sorted by full name or email address in ascending or descending order. The number of records to return at a time can also be controlled using the `page_size` query string parameter.

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$user_id = 'user_id_example'; // string | ID of the user to filter by.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getUsers($sort, $page_size, $user_id, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **user_id** | **string**| ID of the user to filter by. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\UsersResponse**](../Model/UsersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `removeOrganizationUsers()`

```php
removeOrganizationUsers($code, $remove_organization_users_request): \Kinde\KindeSDK\Model\RemoveOrganizationUsersResponse
```

Remove Users from an Organization

Remove existing users from an organization.

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$code = 'code_example'; // string | The organization's code.
$remove_organization_users_request = new \Kinde\KindeSDK\Model\RemoveOrganizationUsersRequest(); // \Kinde\KindeSDK\Model\RemoveOrganizationUsersRequest

try {
    $result = $apiInstance->removeOrganizationUsers($code, $remove_organization_users_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->removeOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| The organization&#39;s code. | [optional]
 **remove_organization_users_request** | [**\Kinde\KindeSDK\Model\RemoveOrganizationUsersRequest**](../Model/RemoveOrganizationUsersRequest.md)|  | [optional]

### Return type

[**\Kinde\KindeSDK\Model\RemoveOrganizationUsersResponse**](../Model/RemoveOrganizationUsersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateUser()`

```php
updateUser($update_user_request, $id): \Kinde\KindeSDK\Model\User
```

Update User

Update a user record.

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

$apiInstance = new Kinde\KindeSDK\Api\UsersApi($config);
$update_user_request = new \Kinde\KindeSDK\Model\UpdateUserRequest(); // \Kinde\KindeSDK\Model\UpdateUserRequest | The user to update.
$id = 'id_example'; // string | The user's id.

try {
    $result = $apiInstance->updateUser($update_user_request, $id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->updateUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **update_user_request** | [**\Kinde\KindeSDK\Model\UpdateUserRequest**](../Model/UpdateUserRequest.md)| The user to update. |
 **id** | **string**| The user&#39;s id. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\User**](../Model/User.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
