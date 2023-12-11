# Kinde\KindeSDK\CallbacksApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**addLogoutRedirectURLs()**](CallbacksApi.md#addLogoutRedirectURLs) | **POST** /api/v1/applications/{app_id}/auth_logout_urls | Add Logout Redirect URLs
[**addRedirectCallbackURLs()**](CallbacksApi.md#addRedirectCallbackURLs) | **POST** /api/v1/applications/{app_id}/auth_redirect_urls | Add Redirect Callback URLs
[**deleteCallbackURLs()**](CallbacksApi.md#deleteCallbackURLs) | **DELETE** /api/v1/applications/{app_id}/auth_redirect_urls | Delete Callback URLs
[**deleteLogoutURLs()**](CallbacksApi.md#deleteLogoutURLs) | **DELETE** /api/v1/applications/{app_id}/auth_logout_urls | Delete Logout URLs
[**getCallbackURLs()**](CallbacksApi.md#getCallbackURLs) | **GET** /api/v1/applications/{app_id}/auth_redirect_urls | List Callback URLs
[**getLogoutURLs()**](CallbacksApi.md#getLogoutURLs) | **GET** /api/v1/applications/{app_id}/auth_logout_urls | List Logout URLs
[**replaceLogoutRedirectURLs()**](CallbacksApi.md#replaceLogoutRedirectURLs) | **PUT** /api/v1/applications/{app_id}/auth_logout_urls | Replace Logout Redirect URLs
[**replaceRedirectCallbackURLs()**](CallbacksApi.md#replaceRedirectCallbackURLs) | **PUT** /api/v1/applications/{app_id}/auth_redirect_urls | Replace Redirect Callback URLs


## `addLogoutRedirectURLs()`

```php
addLogoutRedirectURLs($app_id, $replace_logout_redirect_urls_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Add Logout Redirect URLs

Add additional logout redirect URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.
$replace_logout_redirect_urls_request = new \Kinde\KindeSDK\Model\ReplaceLogoutRedirectURLsRequest(); // \Kinde\KindeSDK\Model\ReplaceLogoutRedirectURLsRequest | Callback details.

try {
    $result = $apiInstance->addLogoutRedirectURLs($app_id, $replace_logout_redirect_urls_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->addLogoutRedirectURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |
 **replace_logout_redirect_urls_request** | [**\Kinde\KindeSDK\Model\ReplaceLogoutRedirectURLsRequest**](../Model/ReplaceLogoutRedirectURLsRequest.md)| Callback details. |

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

## `addRedirectCallbackURLs()`

```php
addRedirectCallbackURLs($app_id, $replace_redirect_callback_urls_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Add Redirect Callback URLs

Add additional redirect callback URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.
$replace_redirect_callback_urls_request = new \Kinde\KindeSDK\Model\ReplaceRedirectCallbackURLsRequest(); // \Kinde\KindeSDK\Model\ReplaceRedirectCallbackURLsRequest | Callback details.

try {
    $result = $apiInstance->addRedirectCallbackURLs($app_id, $replace_redirect_callback_urls_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->addRedirectCallbackURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |
 **replace_redirect_callback_urls_request** | [**\Kinde\KindeSDK\Model\ReplaceRedirectCallbackURLsRequest**](../Model/ReplaceRedirectCallbackURLsRequest.md)| Callback details. |

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

## `deleteCallbackURLs()`

```php
deleteCallbackURLs($app_id, $urls): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Callback URLs

Delete callback URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.
$urls = 'urls_example'; // string | Urls to delete, comma separated and url encoded.

try {
    $result = $apiInstance->deleteCallbackURLs($app_id, $urls);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->deleteCallbackURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |
 **urls** | **string**| Urls to delete, comma separated and url encoded. |

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

## `deleteLogoutURLs()`

```php
deleteLogoutURLs($app_id, $urls): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Logout URLs

Delete logout URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.
$urls = 'urls_example'; // string | Urls to delete, comma separated and url encoded.

try {
    $result = $apiInstance->deleteLogoutURLs($app_id, $urls);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->deleteLogoutURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |
 **urls** | **string**| Urls to delete, comma separated and url encoded. |

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

## `getCallbackURLs()`

```php
getCallbackURLs($app_id): \Kinde\KindeSDK\Model\RedirectCallbackUrls
```

List Callback URLs

Returns an application's redirect callback URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.

try {
    $result = $apiInstance->getCallbackURLs($app_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->getCallbackURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |

### Return type

[**\Kinde\KindeSDK\Model\RedirectCallbackUrls**](../Model/RedirectCallbackUrls.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getLogoutURLs()`

```php
getLogoutURLs($app_id): \Kinde\KindeSDK\Model\LogoutRedirectUrls
```

List Logout URLs

Returns an application's logout redirect URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.

try {
    $result = $apiInstance->getLogoutURLs($app_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->getLogoutURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |

### Return type

[**\Kinde\KindeSDK\Model\LogoutRedirectUrls**](../Model/LogoutRedirectUrls.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `replaceLogoutRedirectURLs()`

```php
replaceLogoutRedirectURLs($app_id, $replace_logout_redirect_urls_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Replace Logout Redirect URLs

Replace all logout redirect URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.
$replace_logout_redirect_urls_request = new \Kinde\KindeSDK\Model\ReplaceLogoutRedirectURLsRequest(); // \Kinde\KindeSDK\Model\ReplaceLogoutRedirectURLsRequest | Callback details.

try {
    $result = $apiInstance->replaceLogoutRedirectURLs($app_id, $replace_logout_redirect_urls_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->replaceLogoutRedirectURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |
 **replace_logout_redirect_urls_request** | [**\Kinde\KindeSDK\Model\ReplaceLogoutRedirectURLsRequest**](../Model/ReplaceLogoutRedirectURLsRequest.md)| Callback details. |

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

## `replaceRedirectCallbackURLs()`

```php
replaceRedirectCallbackURLs($app_id, $replace_redirect_callback_urls_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Replace Redirect Callback URLs

Replace all redirect callback URLs.

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

$apiInstance = new Kinde\KindeSDK\Api\CallbacksApi($config);
$app_id = 'app_id_example'; // string | The identifier for the application.
$replace_redirect_callback_urls_request = new \Kinde\KindeSDK\Model\ReplaceRedirectCallbackURLsRequest(); // \Kinde\KindeSDK\Model\ReplaceRedirectCallbackURLsRequest | Callback details.

try {
    $result = $apiInstance->replaceRedirectCallbackURLs($app_id, $replace_redirect_callback_urls_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallbacksApi->replaceRedirectCallbackURLs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The identifier for the application. |
 **replace_redirect_callback_urls_request** | [**\Kinde\KindeSDK\Model\ReplaceRedirectCallbackURLsRequest**](../Model/ReplaceRedirectCallbackURLsRequest.md)| Callback details. |

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
