# Kinde\KindeSDK\BusinessApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**getBusiness()**](BusinessApi.md#getBusiness) | **GET** /api/v1/business | List business details
[**updateBusiness()**](BusinessApi.md#updateBusiness) | **PATCH** /api/v1/business | Update business details


## `getBusiness()`

```php
getBusiness($code, $name, $email, $phone, $industry, $timezone, $privacy_url, $terms_url): \Kinde\KindeSDK\Model\SuccessResponse
```

List business details

Get your business details.

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

$apiInstance = new Kinde\KindeSDK\Api\BusinessApi($config);
$code = 'code_example'; // string | Business code.
$name = 'name_example'; // string | Business name.
$email = 'email_example'; // string | Email associated with business.
$phone = 'phone_example'; // string | Phone number associated with business.
$industry = 'industry_example'; // string | The industry your business is in.
$timezone = 'timezone_example'; // string | The timezone your business is in.
$privacy_url = 'privacy_url_example'; // string | Your Privacy policy URL.
$terms_url = 'terms_url_example'; // string | Your Terms and Conditions URL.

try {
    $result = $apiInstance->getBusiness($code, $name, $email, $phone, $industry, $timezone, $privacy_url, $terms_url);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BusinessApi->getBusiness: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **code** | **string**| Business code. |
 **name** | **string**| Business name. |
 **email** | **string**| Email associated with business. |
 **phone** | **string**| Phone number associated with business. | [optional]
 **industry** | **string**| The industry your business is in. | [optional]
 **timezone** | **string**| The timezone your business is in. | [optional]
 **privacy_url** | **string**| Your Privacy policy URL. | [optional]
 **terms_url** | **string**| Your Terms and Conditions URL. | [optional]

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

## `updateBusiness()`

```php
updateBusiness($business_name, $primary_email, $primary_phone, $industry_key, $timezone_id, $privacy_url, $terms_url, $is_show_kinde_branding, $is_click_wrap, $partner_code): \Kinde\KindeSDK\Model\SuccessResponse
```

Update business details

Update business details.

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

$apiInstance = new Kinde\KindeSDK\Api\BusinessApi($config);
$business_name = 'business_name_example'; // string | Business name.
$primary_email = 'primary_email_example'; // string | Email associated with business.
$primary_phone = 'primary_phone_example'; // string | Phone number associated with business.
$industry_key = 'industry_key_example'; // string | The key of the industry your business is in.
$timezone_id = 'timezone_id_example'; // string | The ID of the timezone your business is in.
$privacy_url = 'privacy_url_example'; // string | Your Privacy policy URL.
$terms_url = 'terms_url_example'; // string | Your Terms and Conditions URL.
$is_show_kinde_branding = 'is_show_kinde_branding_example'; // string | Display \"Powered by Kinde\" on your sign up, sign in, and subscription pages.
$is_click_wrap = True; // bool | Show a policy acceptance checkbox on sign up.
$partner_code = 'partner_code_example'; // string | Your Kinde Perk code.

try {
    $result = $apiInstance->updateBusiness($business_name, $primary_email, $primary_phone, $industry_key, $timezone_id, $privacy_url, $terms_url, $is_show_kinde_branding, $is_click_wrap, $partner_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BusinessApi->updateBusiness: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **business_name** | **string**| Business name. |
 **primary_email** | **string**| Email associated with business. |
 **primary_phone** | **string**| Phone number associated with business. | [optional]
 **industry_key** | **string**| The key of the industry your business is in. | [optional]
 **timezone_id** | **string**| The ID of the timezone your business is in. | [optional]
 **privacy_url** | **string**| Your Privacy policy URL. | [optional]
 **terms_url** | **string**| Your Terms and Conditions URL. | [optional]
 **is_show_kinde_branding** | **string**| Display \&quot;Powered by Kinde\&quot; on your sign up, sign in, and subscription pages. | [optional]
 **is_click_wrap** | **bool**| Show a policy acceptance checkbox on sign up. | [optional]
 **partner_code** | **string**| Your Kinde Perk code. | [optional]

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
