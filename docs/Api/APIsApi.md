# Kinde\KindeSDK\APIsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**addAPIs()**](APIsApi.md#addAPIs) | **POST** /api/v1/apis | Add APIs
[**deleteAPI()**](APIsApi.md#deleteAPI) | **DELETE** /api/v1/apis/{api_id} | Delete API
[**getAPI()**](APIsApi.md#getAPI) | **GET** /api/v1/apis/{api_id} | List API details
[**getAPIs()**](APIsApi.md#getAPIs) | **GET** /api/v1/apis | List APIs
[**updateAPIApplications()**](APIsApi.md#updateAPIApplications) | **PATCH** /api/v1/apis/{api_id}/applications | Update API Applications


## `addAPIs()`

```php
addAPIs($add_apis_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Add APIs

Add APIs.

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

$apiInstance = new Kinde\KindeSDK\Api\APIsApi($config);
$add_apis_request = new \Kinde\KindeSDK\Model\AddAPIsRequest(); // \Kinde\KindeSDK\Model\AddAPIsRequest | API details.

try {
    $result = $apiInstance->addAPIs($add_apis_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling APIsApi->addAPIs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **add_apis_request** | [**\Kinde\KindeSDK\Model\AddAPIsRequest**](../Model/AddAPIsRequest.md)| API details. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json; charset=utf-8`, `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteAPI()`

```php
deleteAPI($api_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete API

Deletes API.

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

$apiInstance = new Kinde\KindeSDK\Api\APIsApi($config);
$api_id = 'api_id_example'; // string | The API's id.

try {
    $result = $apiInstance->deleteAPI($api_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling APIsApi->deleteAPI: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **api_id** | **string**| The API&#39;s id. |

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

## `getAPI()`

```php
getAPI($api_id): \Kinde\KindeSDK\Model\Api
```

List API details

Returns the details of the API.

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

$apiInstance = new Kinde\KindeSDK\Api\APIsApi($config);
$api_id = 'api_id_example'; // string | The API's id.

try {
    $result = $apiInstance->getAPI($api_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling APIsApi->getAPI: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **api_id** | **string**| The API&#39;s id. |

### Return type

[**\Kinde\KindeSDK\Model\Api**](../Model/Api.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`, `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getAPIs()`

```php
getAPIs(): \Kinde\KindeSDK\Model\Apis
```

List APIs

Returns a list of APIs.

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

$apiInstance = new Kinde\KindeSDK\Api\APIsApi($config);

try {
    $result = $apiInstance->getAPIs();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling APIsApi->getAPIs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Kinde\KindeSDK\Model\Apis**](../Model/Apis.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`, `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateAPIApplications()`

```php
updateAPIApplications($api_id, $update_api_applications_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Update API Applications

Update the applications under that API.

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

$apiInstance = new Kinde\KindeSDK\Api\APIsApi($config);
$api_id = 'api_id_example'; // string | The identifier for the API.
$update_api_applications_request = new \Kinde\KindeSDK\Model\UpdateAPIApplicationsRequest(); // \Kinde\KindeSDK\Model\UpdateAPIApplicationsRequest | The applications you want to connect or disconnect.

try {
    $result = $apiInstance->updateAPIApplications($api_id, $update_api_applications_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling APIsApi->updateAPIApplications: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **api_id** | **string**| The identifier for the API. |
 **update_api_applications_request** | [**\Kinde\KindeSDK\Model\UpdateAPIApplicationsRequest**](../Model/UpdateAPIApplicationsRequest.md)| The applications you want to connect or disconnect. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json; charset=utf-8`, `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
