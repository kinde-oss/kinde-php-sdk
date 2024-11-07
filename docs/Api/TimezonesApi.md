# Kinde\KindeSDK\TimezonesApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**getTimezones()**](TimezonesApi.md#getTimezones) | **GET** /api/v1/timezones | List timezones and timezone IDs.


## `getTimezones()`

```php
getTimezones($timezone_key, $name): \Kinde\KindeSDK\Model\SuccessResponse
```

List timezones and timezone IDs.

Get a list of timezones and associated timezone keys.

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

$apiInstance = new Kinde\KindeSDK\Api\TimezonesApi($config);
$timezone_key = 'timezone_key_example'; // string | Timezone Key.
$name = 'name_example'; // string | Timezone.

try {
    $result = $apiInstance->getTimezones($timezone_key, $name);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TimezonesApi->getTimezones: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **timezone_key** | **string**| Timezone Key. | [optional]
 **name** | **string**| Timezone. | [optional]

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
