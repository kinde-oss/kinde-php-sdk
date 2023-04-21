# Kinde\KindeSDK\FeatureFlagsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createFeatureFlag()**](FeatureFlagsApi.md#createFeatureFlag) | **POST** /api/v1/feature_flags | Create a new feature flag
[**deleteFeatureFlag()**](FeatureFlagsApi.md#deleteFeatureFlag) | **DELETE** /api/v1/feature_flags/{feature_flag_key} | Delete a feature flag
[**updateFeatureFlag()**](FeatureFlagsApi.md#updateFeatureFlag) | **PUT** /api/v1/feature_flags/{feature_flag_key} | Update a feature flag


## `createFeatureFlag()`

```php
createFeatureFlag($name, $description, $key, $type, $allow_override_level, $default_value): \Kinde\KindeSDK\Model\SuccessResponse
```

Create a new feature flag

Create feature flag.

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

$apiInstance = new Kinde\KindeSDK\Api\FeatureFlagsApi($config);
$name = 'name_example'; // string | The name of the flag.
$description = 'description_example'; // string | Description of the flag purpose.
$key = 'key_example'; // string | The flag identifier to use in code.
$type = 'type_example'; // string | The variable type.
$allow_override_level = 'allow_override_level_example'; // string | Allow the flag to be overridden at a different level.
$default_value = 'default_value_example'; // string | Default value for the flag used by environments and organizations.

try {
    $result = $apiInstance->createFeatureFlag($name, $description, $key, $type, $allow_override_level, $default_value);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FeatureFlagsApi->createFeatureFlag: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **name** | **string**| The name of the flag. |
 **description** | **string**| Description of the flag purpose. |
 **key** | **string**| The flag identifier to use in code. |
 **type** | **string**| The variable type. |
 **allow_override_level** | **string**| Allow the flag to be overridden at a different level. |
 **default_value** | **string**| Default value for the flag used by environments and organizations. |

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

## `deleteFeatureFlag()`

```php
deleteFeatureFlag($feature_flag_key): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete a feature flag

Delete feature flag

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

$apiInstance = new Kinde\KindeSDK\Api\FeatureFlagsApi($config);
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag.

try {
    $result = $apiInstance->deleteFeatureFlag($feature_flag_key);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FeatureFlagsApi->deleteFeatureFlag: ', $e->getMessage(), PHP_EOL;
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

## `updateFeatureFlag()`

```php
updateFeatureFlag($feature_flag_key, $name, $description, $key, $type, $allow_override_level, $default_value): \Kinde\KindeSDK\Model\SuccessResponse
```

Update a feature flag

Update feature flag.

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

$apiInstance = new Kinde\KindeSDK\Api\FeatureFlagsApi($config);
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag.
$name = 'name_example'; // string | The name of the flag.
$description = 'description_example'; // string | Description of the flag purpose.
$key = 'key_example'; // string | The flag identifier to use in code.
$type = 'type_example'; // string | The variable type
$allow_override_level = 'allow_override_level_example'; // string | Allow the flag to be overridden at a different level.
$default_value = 'default_value_example'; // string | Default value for the flag used by environments and organizations.

try {
    $result = $apiInstance->updateFeatureFlag($feature_flag_key, $name, $description, $key, $type, $allow_override_level, $default_value);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FeatureFlagsApi->updateFeatureFlag: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **feature_flag_key** | **string**| The identifier for the feature flag. |
 **name** | **string**| The name of the flag. |
 **description** | **string**| Description of the flag purpose. |
 **key** | **string**| The flag identifier to use in code. |
 **type** | **string**| The variable type |
 **allow_override_level** | **string**| Allow the flag to be overridden at a different level. |
 **default_value** | **string**| Default value for the flag used by environments and organizations. |

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
