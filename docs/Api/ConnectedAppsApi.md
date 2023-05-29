# Kinde\KindeSDK\ConnectedAppsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**getConnectedAppAuthUrl()**](ConnectedAppsApi.md#getConnectedAppAuthUrl) | **GET** /api/v1/connected_apps/auth_url | Get Connected App URL
[**getConnectedAppToken()**](ConnectedAppsApi.md#getConnectedAppToken) | **GET** /api/v1/connected_apps/token | Get Connected App Token
[**revokeConnectedAppToken()**](ConnectedAppsApi.md#revokeConnectedAppToken) | **POST** /api/v1/connected_apps/revoke | Revoke Connected App Token


## `getConnectedAppAuthUrl()`

```php
getConnectedAppAuthUrl($key_code_ref, $user_id): \Kinde\KindeSDK\Model\ConnectedAppsAuthUrl
```

Get Connected App URL

Get a URL that authenticates and authorizes a user to a third-party connected app.

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

$apiInstance = new Kinde\KindeSDK\Api\ConnectedAppsApi($config);
$key_code_ref = 'key_code_ref_example'; // string | The unique key code reference of the connected app to authenticate against.
$user_id = 'user_id_example'; // string | The id of the user that needs to authenticate to the third-party connected app.

try {
    $result = $apiInstance->getConnectedAppAuthUrl($key_code_ref, $user_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectedAppsApi->getConnectedAppAuthUrl: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **key_code_ref** | **string**| The unique key code reference of the connected app to authenticate against. |
 **user_id** | **string**| The id of the user that needs to authenticate to the third-party connected app. |

### Return type

[**\Kinde\KindeSDK\Model\ConnectedAppsAuthUrl**](../Model/ConnectedAppsAuthUrl.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getConnectedAppToken()`

```php
getConnectedAppToken($session_id): \Kinde\KindeSDK\Model\ConnectedAppsAccessToken
```

Get Connected App Token

Get an access token that can be used to call the third-party provider linked to the connected app.

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

$apiInstance = new Kinde\KindeSDK\Api\ConnectedAppsApi($config);
$session_id = 'session_id_example'; // string | The unique sesssion id reprensenting the login session of a user.

try {
    $result = $apiInstance->getConnectedAppToken($session_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectedAppsApi->getConnectedAppToken: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **session_id** | **string**| The unique sesssion id reprensenting the login session of a user. |

### Return type

[**\Kinde\KindeSDK\Model\ConnectedAppsAccessToken**](../Model/ConnectedAppsAccessToken.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `revokeConnectedAppToken()`

```php
revokeConnectedAppToken($session_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Revoke Connected App Token

Revoke the tokens linked to the connected app session.

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

$apiInstance = new Kinde\KindeSDK\Api\ConnectedAppsApi($config);
$session_id = 'session_id_example'; // string | The unique sesssion id reprensenting the login session of a user.

try {
    $result = $apiInstance->revokeConnectedAppToken($session_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectedAppsApi->revokeConnectedAppToken: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **session_id** | **string**| The unique sesssion id reprensenting the login session of a user. |

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
