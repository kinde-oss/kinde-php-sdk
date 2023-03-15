# Kinde\KindeSDK\OrganizationsApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganizationUsers()**](OrganizationsApi.md#addOrganizationUsers) | **POST** /api/v1/organization/users | Assign Users to an Organization
[**createOrganization()**](OrganizationsApi.md#createOrganization) | **POST** /api/v1/organization | Create Organization
[**deleteOrganizationFeatureFlagOverride()**](OrganizationsApi.md#deleteOrganizationFeatureFlagOverride) | **DELETE** /api/v1/organizations/{org_code}/feature_flags/{feature_flag_key} | Delete organization feature flag override
[**deleteOrganizationFeatureFlagOverrides()**](OrganizationsApi.md#deleteOrganizationFeatureFlagOverrides) | **DELETE** /api/v1/organizations/{org_code}/feature_flags | Delete all organization feature flag overrides
[**getOrgainzations()**](OrganizationsApi.md#getOrgainzations) | **GET** /api/v1/organizations | List Organizations
[**getOrganization()**](OrganizationsApi.md#getOrganization) | **GET** /api/v1/organization | Get Organization
[**getOrganizationUsers()**](OrganizationsApi.md#getOrganizationUsers) | **GET** /api/v1/organization/users | List Organization Users
[**removeOrganizationUsers()**](OrganizationsApi.md#removeOrganizationUsers) | **PATCH** /api/v1/organization/users | Remove Users from an Organization
[**updateOrganizationFeatureFlagOverride()**](OrganizationsApi.md#updateOrganizationFeatureFlagOverride) | **PATCH** /api/v1/organizations/{org_code}/feature_flags/{feature_flag_key} | Update organization feature flag override


## `addOrganizationUsers()`

```php
addOrganizationUsers($code, $add_organization_users_request): \Kinde\KindeSDK\Model\AddOrganizationUsers200Response
```

Assign Users to an Organization

Add existing users to an organization.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$code = 'code_example'; // string | The organization's code.
$add_organization_users_request = new \Kinde\KindeSDK\Model\AddOrganizationUsersRequest(); // \Kinde\KindeSDK\Model\AddOrganizationUsersRequest

try {
    $result = $apiInstance->addOrganizationUsers($code, $add_organization_users_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->addOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| The organization&#39;s code. | [optional]
 **add_organization_users_request** | [**\Kinde\KindeSDK\Model\AddOrganizationUsersRequest**](../Model/AddOrganizationUsersRequest.md)|  | [optional]

### Return type

[**\Kinde\KindeSDK\Model\AddOrganizationUsers200Response**](../Model/AddOrganizationUsers200Response.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createOrganization()`

```php
createOrganization($create_organization_request)
```

Create Organization

Create an organization.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$create_organization_request = new \Kinde\KindeSDK\Model\CreateOrganizationRequest(); // \Kinde\KindeSDK\Model\CreateOrganizationRequest | Organization details.

try {
    $apiInstance->createOrganization($create_organization_request);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->createOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_organization_request** | [**\Kinde\KindeSDK\Model\CreateOrganizationRequest**](../Model/CreateOrganizationRequest.md)| Organization details. | [optional]

### Return type

void (empty response body)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganizationFeatureFlagOverride()`

```php
deleteOrganizationFeatureFlagOverride($org_code, $feature_flag_key): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete organization feature flag override

Delete organization feature flag override.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag.

try {
    $result = $apiInstance->deleteOrganizationFeatureFlagOverride($org_code, $feature_flag_key);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |
 **feature_flag_key** | **string**| The identifier for the feature flag. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteOrganizationFeatureFlagOverrides()`

```php
deleteOrganizationFeatureFlagOverrides($org_code): \Kinde\KindeSDK\Model\SuccessResponse
```

Delete all organization feature flag overrides

Delete all organization feature flag overrides.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization.

try {
    $result = $apiInstance->deleteOrganizationFeatureFlagOverrides($org_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationFeatureFlagOverrides: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization. |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrgainzations()`

```php
getOrgainzations($sort, $page_size, $next_token): \Kinde\KindeSDK\Model\GetOrgainzations200Response
```

List Organizations

Get a list of organizations.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getOrgainzations($sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrgainzations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetOrgainzations200Response**](../Model/GetOrgainzations200Response.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganization()`

```php
getOrganization($code): \Kinde\KindeSDK\Model\Organization
```

Get Organization

Gets an organization given the organization's code.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$code = 'code_example'; // string | The organization's code.

try {
    $result = $apiInstance->getOrganization($code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| The organization&#39;s code. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\Organization**](../Model/Organization.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOrganizationUsers()`

```php
getOrganizationUsers($sort, $page_size, $next_token, $code): \Kinde\KindeSDK\Model\GetOrganizationUsers200Response
```

List Organization Users

Get users in an organization.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.
$code = 'code_example'; // string | The organization's code.

try {
    $result = $apiInstance->getOrganizationUsers($sort, $page_size, $next_token, $code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]
 **code** | **string**| The organization&#39;s code. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetOrganizationUsers200Response**](../Model/GetOrganizationUsers200Response.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `removeOrganizationUsers()`

```php
removeOrganizationUsers($code, $remove_organization_users_request): \Kinde\KindeSDK\Model\RemoveOrganizationUsers200Response
```

Remove Users from an Organization

Remove existing users from an organization.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$code = 'code_example'; // string | The organization's code.
$remove_organization_users_request = new \Kinde\KindeSDK\Model\RemoveOrganizationUsersRequest(); // \Kinde\KindeSDK\Model\RemoveOrganizationUsersRequest

try {
    $result = $apiInstance->removeOrganizationUsers($code, $remove_organization_users_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->removeOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| The organization&#39;s code. | [optional]
 **remove_organization_users_request** | [**\Kinde\KindeSDK\Model\RemoveOrganizationUsersRequest**](../Model/RemoveOrganizationUsersRequest.md)|  | [optional]

### Return type

[**\Kinde\KindeSDK\Model\RemoveOrganizationUsers200Response**](../Model/RemoveOrganizationUsers200Response.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateOrganizationFeatureFlagOverride()`

```php
updateOrganizationFeatureFlagOverride($org_code, $feature_flag_key, $value): \Kinde\KindeSDK\Model\SuccessResponse
```

Update organization feature flag override

Update organization feature flag override.

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

$apiInstance = new Kinde\KindeSDK\Api\OrganizationsApi($config);
$org_code = 'org_code_example'; // string | The identifier for the organization
$feature_flag_key = 'feature_flag_key_example'; // string | The identifier for the feature flag
$value = 'value_example'; // string | Override value

try {
    $result = $apiInstance->updateOrganizationFeatureFlagOverride($org_code, $feature_flag_key, $value);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->updateOrganizationFeatureFlagOverride: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_code** | **string**| The identifier for the organization |
 **feature_flag_key** | **string**| The identifier for the feature flag |
 **value** | **string**| Override value |

### Return type

[**\Kinde\KindeSDK\Model\SuccessResponse**](../Model/SuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
