# Kinde\KindeSDK\SubscribersApi

All URIs are relative to https://your_kinde_domain.kinde.com/api/v1

Method | HTTP request | Description
------------- | ------------- | -------------
[**createSubscriber()**](SubscribersApi.md#createSubscriber) | **POST** /api/v1/subscribers | Create Subscriber
[**getSubscriber()**](SubscribersApi.md#getSubscriber) | **GET** /api/v1/subscribers/{subscriber_id} | Get Subscriber
[**getSubscribers()**](SubscribersApi.md#getSubscribers) | **GET** /api/v1/subscribers | List Subscribers


## `createSubscriber()`

```php
createSubscriber($first_name, $last_name, $email): \Kinde\KindeSDK\Model\CreateSubscriberSuccessResponse
```

Create Subscriber

Create subscriber.

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

$apiInstance = new Kinde\KindeSDK\Api\SubscribersApi($config);
$first_name = 'first_name_example'; // string | Subscriber's first name.
$last_name = 'last_name_example'; // string | Subscriber's last name.
$email = 'email_example'; // string | The email address of the subscriber.

try {
    $result = $apiInstance->createSubscriber($first_name, $last_name, $email);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SubscribersApi->createSubscriber: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **first_name** | **string**| Subscriber&#39;s first name. |
 **last_name** | **string**| Subscriber&#39;s last name. |
 **email** | **string**| The email address of the subscriber. |

### Return type

[**\Kinde\KindeSDK\Model\CreateSubscriberSuccessResponse**](../Model/CreateSubscriberSuccessResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getSubscriber()`

```php
getSubscriber($subscriber_id): \Kinde\KindeSDK\Model\GetSubscriberResponse
```

Get Subscriber

Retrieve a subscriber record.

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

$apiInstance = new Kinde\KindeSDK\Api\SubscribersApi($config);
$subscriber_id = 'subscriber_id_example'; // string | The subscriber's id.

try {
    $result = $apiInstance->getSubscriber($subscriber_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SubscribersApi->getSubscriber: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **subscriber_id** | **string**| The subscriber&#39;s id. |

### Return type

[**\Kinde\KindeSDK\Model\GetSubscriberResponse**](../Model/GetSubscriberResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getSubscribers()`

```php
getSubscribers($sort, $page_size, $next_token): \Kinde\KindeSDK\Model\GetSubscribersResponse
```

List Subscribers

The returned list can be sorted by full name or email address in ascending or descending order. The number of records to return at a time can also be controlled using the `page_size` query string parameter.

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

$apiInstance = new Kinde\KindeSDK\Api\SubscribersApi($config);
$sort = 'sort_example'; // string | Field and order to sort the result by.
$page_size = 56; // int | Number of results per page. Defaults to 10 if parameter not sent.
$next_token = 'next_token_example'; // string | A string to get the next page of results if there are more results.

try {
    $result = $apiInstance->getSubscribers($sort, $page_size, $next_token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SubscribersApi->getSubscribers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort** | **string**| Field and order to sort the result by. | [optional]
 **page_size** | **int**| Number of results per page. Defaults to 10 if parameter not sent. | [optional]
 **next_token** | **string**| A string to get the next page of results if there are more results. | [optional]

### Return type

[**\Kinde\KindeSDK\Model\GetSubscribersResponse**](../Model/GetSubscribersResponse.md)

### Authorization

[kindeBearerAuth](../../README.md#kindeBearerAuth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json; charset=utf-8`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
