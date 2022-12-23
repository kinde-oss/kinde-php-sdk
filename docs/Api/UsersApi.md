# Kinde\KindeSDK\UsersApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createUser()**](UsersApi.md#createUser) | **POST** /user | Creates a user record
[**getUsers()**](UsersApi.md#getUsers) | **GET** /users | Returns a paginated list of end-user records for a business


## `createUser()`

```php
createUser($create_user_request): \Kinde\KindeSDK\Model\CreateUser200Response
```

Creates a user record

Creates a user record and optionally zero or more identities for the user. An example identity could be the email address of the user

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new Kinde\KindeSDK\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_user_request = new \Kinde\KindeSDK\Model\CreateUserRequest(); // \Kinde\KindeSDK\Model\CreateUserRequest | The details of the user to create

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
 **create_user_request** | [**\Kinde\KindeSDK\Model\CreateUserRequest**](../Model/CreateUserRequest.md)| The details of the user to create | [optional]

### Return type

[**\Kinde\KindeSDK\Model\CreateUser200Response**](../Model/CreateUser200Response.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getUsers()`

```php
getUsers($sort, $page_size, $user_id, $next_token): \Kinde\KindeSDK\Model\GetUsers200Response
```

Returns a paginated list of end-user records for a business

The returned list can be sorted by full name or email address in ascending or descending order. The number of records to return at a time can also be controlled using the page_size query string parameter.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new Kinde\KindeSDK\Api\UsersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$sort = 'sort_example'; // string | Describes the field and order to sort the result by
$page_size = 56; // int | The number of items to return
$user_id = 56; // int | The id of the user to filter by
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results

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
 **sort** | **string**| Describes the field and order to sort the result by | [optional]
 **page_size** | **int**| The number of items to return | [optional]
 **user_id** | **int**| The id of the user to filter by | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetUsers200Response**](../Model/GetUsers200Response.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
