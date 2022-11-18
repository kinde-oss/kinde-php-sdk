# Kinde PHP SDK

The Kinde PHP SDK allows developers to integrate with Composable Commerce APIs using PHP native interfaces, models and helpers instead of manually using the HTTP and JSON API.

## Register for Kinde

If you haven’t already got a Kinde account, [register for free here](http://app.kinde.com/register) (no credit card required).

You need a Kinde domain to get started, e.g. `yourapp.kinde.com`.

## Install

Install [Composer](https://getcomposer.org/) and then execute the following command:

```bash
php composer.phar require kinde-oss/kinde-auth-php
```

Or add the following to your composer.json file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kinde-oss/kinde-auth-php"
        }
    ],
    "require": {
        "kinde-oss/kinde-auth-php": "*@dev",
        "php": "^7.4 || ^8.0",
        "ext-curl": "*",
        "ext-json": "*",
        "ext-mbstring": "*",
        "guzzlehttp/guzzle": "^7.3",
        "guzzlehttp/psr7": "^1.7 || ^2.0"
    },
    "autoload": {
        "psr-4": {"Kinde\\KindeSDK\\": "lib/"}
    }
}
```

## Set callback URLs

1. In Kinde, go to **Settings** > **App keys**.
2. Add your callback URLs in the relevant fields. For example:

    - Allowed callback URLs - for example, `https://localhost:8000/callback`
    - Allowed logout redirect URLs - for example, `https://localhost:8000`

3. Select **Save**.

## Add environments

Kinde comes with a production environment, but you can set up other environments if you want to. Note that each environment needs to be set up independently, so you need to use the Environment subdomain in the code block above for those new environments.

## Configure your app

**Environment variables**

The following variables need to be replaced in the code snippets below.

-   `KINDE_HOST` - your Kinde domain - e.g. `https://your_kinde_domain.kinde.com`
-   `KINDE_REDIRECT_URL` - your callback url, make sure this URL is under your allowed callback redirect URLs. - e.g. `http://localhost:8000/callback`
-   `KINDE_POST_LOGOUT_REDIRECT_URL` - where you want users to be redirected to after logging out, make sure this URL is under your allowed logout redirect URLs. - e.g. `http://localhost:8000`
-   `KINDE_CLIENT_ID` - you can find this on the **App keys** page - e.g. `your_kinde_client_id`
-   `KINDE_CLIENT_SECRET` - you can find this on the **App keys** page - e.g. `your_kinde_client_secret`

## Integrate with your app

Add the composer autoloader to your app.

```php
...

require_once(__DIR__ . '/vendor/autoload.php');

```

Create a new instance of the Kinde Auth client object before you initialize your app.

```php
...

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Configuration;
use Kinde\KindeSDK\Sdk\Enums\GrantType;

...

private $kindeClient;
private $kindeConfig;



public function __construct()
{
    $this->kindeClient = new KindeClientSDK('KINDE_HOST', 'KINDE_REDIRECT_URL', 'KINDE_CLIENT_ID', 'KINDE_CLIENT_SECRET', 'KINDE_GRANT_TYPE', 'KINDE_POST_LOGOUT_REDIRECT_URL');
    $this->kindeConfig = new Configuration();
    $this->kindeConfig->setHost(' KINDE_HOST');
}
```

## Login and registration

The Kinde client provides methods for easy to implement login / registration.

You can add buttons in your HTML as follows:

```html
<div class="navigation">
    <a href="/login" type="button">Login</a>
    <a href="/register" type="button">Register</a>
</div>
```

You will then need to route /login and /register to functions that call the SDK methods, for example:

```php
public function login()
{
    $this->kindeClient->login();
}

public function register()
{
    $this->kindeClient->register();
}
```

## Manage redirects

When the user is redirected back to your site from Kinde, this will call your callback URL defined in the `KINDE_REDIRECT_URL` variable. You will need to route `/callback` to call a function to handle this.

```php
public function callback()
{
    $token = $this->kindeClient->getToken();
    $this->kindeConfig->setAccessToken($token->access_token);
    print_r($token);
}
```

You can also get the current authentication status with `AuthStatus`:
```php
...
use Kinde\KindeSDK\Sdk\Enums\AuthStatus;
...

public function callback()
{
    if ($this->kindeClient->getAuthStatus() != AuthStatus::UNAUTHENTICATED) {
        $token = $this->kindeClient->getToken();
        $this->kindeConfig->setAccessToken($token->access_token);
        print_r($token);
    }
}
```
For more information, please check out `Kinde\KindeSDK\Sdk\Enums\AuthStatus`

## Logout

The Kinde SPA client comes with a logout method.

```php
$this->kindeClient->logout();
```

## Get user information

You need to have already authenticated before you call the API, otherwise an error will occur.

Use the `Kinde\KindeSDK\Api\UserApi` class, then call the getUserProfile method.

```php
...

use Kinde\KindeSDK\Api\UserApi;

...

public function getProfile()
{

    $apiInstance = new UserApi($this->kindeConfig); // You have already defined `$this->kindeConfig` in the construction function

    try {
        $result = $apiInstance->getUserProfile();
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling UserApi->getUserProfile: ', $e->getMessage(), PHP_EOL;
    }
}
```

### View users in Kinde

Go to the **Users** page in Kinde to see who has registered.

### User Permissions

After a user signs in and they are verified, the token return includes permissions for that user. [User permissions are set in Kinde](https://kinde.com/docs/user-management/user-permissions), but you must also configure your application to unlock these functions.

```php
"permissions" => [
    "create:todos",
    "update:todos",
    "read:todos",
    "delete:todos",
    "create:tasks",
    "update:tasks",
    "read:tasks",
    "delete:tasks",
];
```

If you need help connecting to Kinde, please contact us at [support@kinde.com](mailto:support@kinde.com).
