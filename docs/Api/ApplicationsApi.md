# Kinde\KindeSDK\ApplicationsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createApplication()**](ApplicationsApi.md#createApplication) | **POST** /api/v1/applications | Create Application
[**deleteApplication()**](ApplicationsApi.md#deleteApplication) | **DELETE** /api/v1/applications/{application_id} | Delete Application
[**getApplication()**](ApplicationsApi.md#getApplication) | **GET** /api/v1/applications/{application_id} | Get Application
[**getApplications()**](ApplicationsApi.md#getApplications) | **GET** /api/v1/applications | List Applications
[**updateApplication()**](ApplicationsApi.md#updateApplication) | **PATCH** /api/v1/applications/{application_id} | Update Application


## `createApplication()`

```php
createApplication($create_application_request): \Kinde\KindeSDK\Model\CreateApplicationResponse
```

Create Application

Create an application.

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

$apiInstance = new Kinde\KindeSDK\Api\ApplicationsApi($config);
$create_application_request = new \Kinde\KindeSDK\Model\CreateApplicationRequest(); // \Kinde\KindeSDK\Model\CreateApplicationRequest | Application details.

try {
    $result = $apiInstance->createApplication($create_application_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApplicationsApi->createApplication: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_application_request** | [**\Kinde\KindeSDK\Model\CreateApplicationRequest**](../Model/CreateApplicationRequest.md)| Application details. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\CreateApplicationResponse**](../Model/CreateApplicationResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteApplication()`

```php
deleteApplication($application_id): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Application

Delete application.

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

$apiInstance = new Kinde\KindeSDK\Api\ApplicationsApi($config);
$application_id = 'application_id_example'; // string | The identifier for the application.

try {
    $result = $apiInstance->deleteApplication($application_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApplicationsApi->deleteApplication: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **application_id** | **string**| The identifier for the application. |

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

## `getApplication()`

```php
getApplication($application_id): \Kinde\KindeSDK\Model\GetApplicationResponse
```

Get Application

Gets an application given the application's id.

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

$apiInstance = new Kinde\KindeSDK\Api\ApplicationsApi($config);
$application_id = 'application_id_example'; // string | The identifier for the application.

try {
    $result = $apiInstance->getApplication($application_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApplicationsApi->getApplication: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **application_id** | **string**| The identifier for the application. |

### Return type

[**\Kinde\KindeSDK\Model\GetApplicationResponse**](../Model/GetApplicationResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getApplications()`

```php
getApplications($sort, $page_size, $next_token): \Kinde\KindeSDK\Model\GetApplicationsResponse
```

List Applications

Get a list of applications.

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

$apiInstance = new Kinde\KindeSDK\Api\ApplicationsApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getApplications($sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApplicationsApi->getApplications: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetApplicationsResponse**](../Model/GetApplicationsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateApplication()`

```php
updateApplication($application_id, $update_application_request)
```

Update Application

Update an application.

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

$apiInstance = new Kinde\KindeSDK\Api\ApplicationsApi($config);
$application_id = 'application_id_example'; // string | The identifier for the application.
$update_application_request = new \Kinde\KindeSDK\Model\UpdateApplicationRequest(); // \Kinde\KindeSDK\Model\UpdateApplicationRequest | Application details.

try {
    $apiInstance->updateApplication($application_id, $update_application_request);
} catch (Exception $e) {
    echo 'Exception when calling ApplicationsApi->updateApplication: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **application_id** | **string**| The identifier for the application. |
 **update_application_request** | [**\Kinde\KindeSDK\Model\UpdateApplicationRequest**](../Model/UpdateApplicationRequest.md)| Application details. | [optional]

### Return type

void (empty response body)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
