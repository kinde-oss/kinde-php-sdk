# Kinde\KindeSDK\EnvironmentsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteEnvironementFeatureFlagOverride()**](EnvironmentsApi.md#deleteEnvironementFeatureFlagOverride) | **DELETE** /api/v1/environment/feature_flags/{feature_flag_key} | Delete Environment Feature Flag Override
[**deleteEnvironementFeatureFlagOverrides()**](EnvironmentsApi.md#deleteEnvironementFeatureFlagOverrides) | **DELETE** /api/v1/environment/feature_flags | Delete Environment Feature Flag Overrides
[**getEnvironementFeatureFlags()**](EnvironmentsApi.md#getEnvironementFeatureFlags) | **GET** /api/v1/environment/feature_flags | List Environment Feature Flags
[**updateEnvironementFeatureFlagOverride()**](EnvironmentsApi.md#updateEnvironementFeatureFlagOverride) | **PATCH** /api/v1/environment/feature_flags/{feature_flag_key} | Update Environment Feature Flag Override


## `deleteEnvironementFeatureFlagOverride()`

```php
deleteEnvironementFeatureFlagOverride($feature_flag_key): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Environment Feature Flag Override

Delete environment feature flag override.

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

$apiInstance = new Kinde\KindeSDK\Api\EnvironmentsApi($config);
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag.

try {
    $result = $apiInstance->deleteEnvironementFeatureFlagOverride($feature_flag_key);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnvironmentsApi->deleteEnvironementFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **feature_flag_key** | **string**| The identifier for the feature flag. |

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

## `deleteEnvironementFeatureFlagOverrides()`

```php
deleteEnvironementFeatureFlagOverrides(): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete Environment Feature Flag Overrides

Delete all environment feature flag overrides.

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

$apiInstance = new Kinde\KindeSDK\Api\EnvironmentsApi($config);

try {
    $result = $apiInstance->deleteEnvironementFeatureFlagOverrides();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnvironmentsApi->deleteEnvironementFeatureFlagOverrides: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

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

## `getEnvironementFeatureFlags()`

```php
getEnvironementFeatureFlags(): \Kinde\KindeSDK\Model\GetEnvironmentFeatureFlagsResponse
```

List Environment Feature Flags

Get environment feature flags.

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

$apiInstance = new Kinde\KindeSDK\Api\EnvironmentsApi($config);

try {
    $result = $apiInstance->getEnvironementFeatureFlags();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnvironmentsApi->getEnvironementFeatureFlags: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Kinde\KindeSDK\Model\GetEnvironmentFeatureFlagsResponse**](../Model/GetEnvironmentFeatureFlagsResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateEnvironementFeatureFlagOverride()`

```php
updateEnvironementFeatureFlagOverride($feature_flag_key, $update_environement_feature_flag_override_request): \Kinde\KindeSDK\Model\SuccessResponse
```

Update Environment Feature Flag Override

Update environment feature flag override.

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

$apiInstance = new Kinde\KindeSDK\Api\EnvironmentsApi($config);
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag.
$update_environement_feature_flag_override_request = new \Kinde\KindeSDK\Model\UpdateEnvironementFeatureFlagOverrideRequest(); // \Kinde\KindeSDK\Model\UpdateEnvironementFeatureFlagOverrideRequest | Flag details.

try {
    $result = $apiInstance->updateEnvironementFeatureFlagOverride($feature_flag_key, $update_environement_feature_flag_override_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnvironmentsApi->updateEnvironementFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **feature_flag_key** | **string**| The identifier for the feature flag. |
 **update_environement_feature_flag_override_request** | [**\Kinde\KindeSDK\Model\UpdateEnvironementFeatureFlagOverrideRequest**](../Model/UpdateEnvironementFeatureFlagOverrideRequest.md)| Flag details. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`, `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
