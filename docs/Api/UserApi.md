# Kinde\KindeSDK\UserApi

All URIs are relative to https://test.kinde.com.

Method | HTTP request | Description
------------- | ------------- | -------------
[**getUserProfile()**](UserApi.md#getUserProfile) | **GET** /oauth2/user_profile | Returns current user profile


## `getUserProfile()`

```php
getUserProfile(): \Kinde\KindeSDK\Model\UserProfile
```

Returns current user profile

Test

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



##### The SDK provides 3 methods authentication:
 - Client Credentials
 - Authorization Code Flow
 - Authorization Code Flow with PKCE

First thing, you need import SDK and configuration in construct function:

```
...

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Configuration;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
...

private $kindeClient;

private $kindeConfig;

public function __construct()
{
    $this->kindeClient = new KindeClientSDK('YOUR_KINDE_HOST', 'YOUR_KINDE_REDIRECT_URI', 'YOUR_KINDE_CLIENT_ID', 'YOUR_KINDE_CLIENT_SECRET', 'THE_LOGIN_METHOD_YOU_PREFER');
    $this->kindeConfig = new Configuration();
    $this->kindeConfig->setHost('YOUR_KINDE_HOST');
}

```

### How to authentication & get access token

#### 1. Client Credentials

```

public function login(
    ServerRequestInterface $request,
    ResponseInterface $response
) {
    $response = $this->kindeClient->login();
    return $response; // Token in here
}

```

#### 2. Authorization Code Flow & PKCE

The SDK provides 2 functions: login and getToken. The login function will redirect to Kinde login, the getToken function will request to the token endpoint with the code received as params (and code_verifier if the login method is PKCE), then return the token instance.

```

public function login(
    ServerRequestInterface $request,
    ResponseInterface $response
) {
    $this->kindeClient->login();
    $response->getBody()->write('redirecting...');
    return $response;
}

// Once authorized, it will come to this function, then you will get a token instance from there.
public function callback(
    ServerRequestInterface $request,
    ResponseInterface $response
) {
    $response = $this->kindeClient->getToken();
    return $response; // Token in here
}

```

You can also get or set access token through Configuration class. See more at: Kinde\KindeSDK\Configuration;

$apiInstance = new Kinde\KindeSDK\Api\UserApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getUserProfile();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UserApi->getUserProfile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Kinde\KindeSDK\Model\UserProfile**](../Model/UserProfile.md)

### Authorization

[oauth](../../README.md#oauth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
