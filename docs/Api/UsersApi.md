# Kinde\KindeSDK\UsersApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createUser()**](UsersApi.md#createUser) | **POST** /api/v1/user | Create User
[**deleteUser()**](UsersApi.md#deleteUser) | **DELETE** /api/v1/user | Delete User
[**getUserData()**](UsersApi.md#getUserData) | **GET** /api/v1/user | Get User
[**getUsers()**](UsersApi.md#getUsers) | **GET** /api/v1/users | List Users
[**refreshUserClaims()**](UsersApi.md#refreshUserClaims) | **POST** /api/v1/users/{user_id}/refresh_claims | Refresh User Claims and Invalidate Cache
[**updateUser()**](UsersApi.md#updateUser) | **PATCH** /api/v1/user | Update User
[**updateUserFeatureFlagOverride()**](UsersApi.md#updateUserFeatureFlagOverride) | **PATCH** /api/v1/users/{user_id}/feature_flags/{feature_flag_key} | Update User Feature Flag Override


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

## `deleteUser()`

```php
deleteUser($id, $is_delete_profile): \Kinde\KindeSDK\Model\SuccessResponse
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
$is_delete_profile = True; // bool | Delete all data and remove the user's profile from all of Kinde, including the subscriber list

try {
    $result = $apiInstance->deleteUser($id, $is_delete_profile);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->deleteUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The user&#39;s id. |
 **is_delete_profile** | **bool**| Delete all data and remove the user&#39;s profile from all of Kinde, including the subscriber list | [optional]

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

## `getUserData()`

```php
getUserData($id, $expand): \Kinde\KindeSDK\Model\User
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
$expand = 'expand_example'; // string | Specify additional data to retrieve. Use \"organizations\" and/or \"identities\".

try {
    $result = $apiInstance->getUserData($id, $expand);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->getUserData: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The user&#39;s id. |
 **expand** | **string**| Specify additional data to retrieve. Use \&quot;organizations\&quot; and/or \&quot;identities\&quot;. | [optional]

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
getUsers($sort, $page_size, $user_id, $next_token, $email, $expand): \Kinde\KindeSDK\Model\UsersResponse
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
$email = 'email_example'; // string | Filter the results by email address. The query string should be comma separated and url encoded.
$expand = 'expand_example'; // string | Specify additional data to retrieve. Use \"organizations\" and/or \"identities\".

try {
    $result = $apiInstance->getUsers($sort, $page_size, $user_id, $next_token, $email, $expand);
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
 **email** | **string**| Filter the results by email address. The query string should be comma separated and url encoded. | [optional]
 **expand** | **string**| Specify additional data to retrieve. Use \&quot;organizations\&quot; and/or \&quot;identities\&quot;. | [optional]

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

## `refreshUserClaims()`

```php
refreshUserClaims($user_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Refresh User Claims and Invalidate Cache

Refreshes the user's claims and invalidates the current cache.

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
$user_id = 'user_id_example'; // string | The id of the user whose claims needs to be updated.

try {
    $result = $apiInstance->refreshUserClaims($user_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->refreshUserClaims: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_id** | **string**| The id of the user whose claims needs to be updated. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`, `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateUser()`

```php
updateUser($update_user_request, $id): \Kinde\KindeSDK\Model\UpdateUserResponse
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

[**\Kinde\KindeSDK\Model\UpdateUserResponse**](../Model/UpdateUserResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateUserFeatureFlagOverride()`

```php
updateUserFeatureFlagOverride($user_id, $feature_flag_key, $value): \Kinde\KindeSDK\Model\SuccessResponse
```

Update User Feature Flag Override

Update user feature flag override.

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
$user_id = 'user_id_example'; // string | The identifier for the user
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag
$value = 'value_example'; // string | Override value

try {
    $result = $apiInstance->updateUserFeatureFlagOverride($user_id, $feature_flag_key, $value);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UsersApi->updateUserFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_id** | **string**| The identifier for the user |
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
