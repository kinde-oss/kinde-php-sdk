# Kinde\KindeSDK\OAuthApi

All URIs are relative to https://your_kinde_domain.kinde.com

Method | HTTP request | Description
------------- | ------------- | -------------
[**getUser()**](OAuthApi.md#getUser) | **GET** /oauth2/user_profile | Returns the details of the currently logged in user
[**getUserProfileV2()**](OAuthApi.md#getUserProfileV2) | **GET** /oauth2/v2/user_profile | Returns the details of the currently logged in user


## `getUser()`

```php
getUser(): \Kinde\KindeSDK\Model\UserProfile
```

Returns the details of the currently logged in user

Contains the id, names and email of the currently logged in user

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

Contains the id, names and email of the currently logged in user

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
