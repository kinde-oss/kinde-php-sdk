# Kinde\KindeSDK\OAuthApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**getUser()**](OAuthApi.md#getUser) | **GET** /oauth2/user_profile | Get User Profile
[**getUserProfileV2()**](OAuthApi.md#getUserProfileV2) | **GET** /oauth2/v2/user_profile | Returns the details of the currently logged in user
[**tokenIntrospection()**](OAuthApi.md#tokenIntrospection) | **POST** /oauth2/introspect | Get token details
[**tokenRevocation()**](OAuthApi.md#tokenRevocation) | **POST** /oauth2/revoke | Revoke token


## `getUser()`

```php
getUser(): \Kinde\KindeSDK\Model\UserProfile
```

Get User Profile

Contains the id, names and email of the currently logged in user.

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

$apiInstance = new Kinde\KindeSDK\Api\OAuthApi($config);

try {
    $result = $apiInstance->getUser();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OAuthApi->getUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Kinde\KindeSDK\Model\UserProfile**](../Model/UserProfile.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getUserProfileV2()`

```php
getUserProfileV2(): \Kinde\KindeSDK\Model\UserProfileV2
```

Returns the details of the currently logged in user

Contains the id, names, profile picture URL and email of the currently logged in user.

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

$apiInstance = new Kinde\KindeSDK\Api\OAuthApi($config);

try {
    $result = $apiInstance->getUserProfileV2();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OAuthApi->getUserProfileV2: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Kinde\KindeSDK\Model\UserProfileV2**](../Model/UserProfileV2.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `tokenIntrospection()`

```php
tokenIntrospection($token, $token_type): \Kinde\KindeSDK\Model\TokenIntrospect
```

Get token details

Retrieve information about the provided token.

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

$apiInstance = new Kinde\KindeSDK\Api\OAuthApi($config);
$token = 'token_example'; // string | The token to be introspected.
$token_type = 'token_type_example'; // string | The provided token's type.

try {
    $result = $apiInstance->tokenIntrospection($token, $token_type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OAuthApi->tokenIntrospection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **token** | **string**| The token to be introspected. | [optional]
 **token_type** | **string**| The provided token&#39;s type. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\TokenIntrospect**](../Model/TokenIntrospect.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `tokenRevocation()`

```php
tokenRevocation($token, $client_id, $client_secret)
```

Revoke token

Revoke a previously issued token.

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

$apiInstance = new Kinde\KindeSDK\Api\OAuthApi($config);
$token = 'token_example'; // string | The token to be revoked.
$client_id = 'client_id_example'; // string | The identifier for your client.
$client_secret = 'client_secret_example'; // string | The secret associated with your client.

try {
    $apiInstance->tokenRevocation($token, $client_id, $client_secret);
} catch (Exception $e) {
    echo 'Exception when calling OAuthApi->tokenRevocation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **token** | **string**| The token to be revoked. | [optional]
 **client_id** | **string**| The identifier for your client. | [optional]
 **client_secret** | **string**| The secret associated with your client. | [optional]

### Return type

void (empty response body)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/x-www-form-urlencoded`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
