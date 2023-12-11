# Kinde\KindeSDK\IndustriesApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**getIndustries()**](IndustriesApi.md#getIndustries) | **GET** /api/v1/industries | List industries and industry keys.


## `getIndustries()`

```php
getIndustries($industry_key, $name): \Kinde\KindeSDK\Model\SuccessResponse
```

List industries and industry keys.

Get a list of industries and associated industry keys.

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

$apiInstance = new Kinde\KindeSDK\Api\IndustriesApi($config);
$industry_key = 'industry_key_example'; // string | Industry Key.
$name = 'name_example'; // string | Industry name.

try {
    $result = $apiInstance->getIndustries($industry_key, $name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IndustriesApi->getIndustries: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **industry_key** | **string**| Industry Key. | [optional]
 **name** | **string**| Industry name. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
