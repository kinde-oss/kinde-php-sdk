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

Use the `Kinde\KindeSDK\Api\OAuthApi` class, then call the `getUser` method.

```php
...

use Kinde\KindeSDK\Api\OAuthApi;

...

public function getProfile()
{

    $apiInstance = new OAuthApi($this->kindeConfig); // You have already defined `$this->kindeConfig` in the construction function

    try {
        $result = $apiInstance->getUser();
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling UserApi->getUser: ', $e->getMessage(), PHP_EOL;
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
We provide helper functions to more easily access permissions:

```php
$this->kindeClient->getPermission('create:todos');
// ['orgCode' => 'org_1234', 'isGranted' => true]

$this->kindeClient->getPermissions();
// ['orgCode' => 'org_1234', 'permissions' => ['create:todos', 'update:todos', 'read:todos']]
```

A practical example in code might look something like:

```php
if ($this->kindeClient->getPermission("create:todos")['isGranted']) {
    // create new a todo
}
```
### Audience

An `audience` is the intended recipient of an access token - for example the API for your application. The audience argument can be passed to the Kinde client to request an audience be added to the provided token.

The audience of a token is the intended recipient of the token.

```php
...
public function __construct()
{
   $this->kindeClient = new KindeClientSDK('KINDE_HOST', 'KINDE_REDIRECT_URL', 'KINDE_CLIENT_ID', 'KINDE_CLIENT_SECRET', 'KINDE_GRANT_TYPE', 'KINDE_POST_LOGOUT_REDIRECT_URL', 'YOUR_SCOPES', [
      'audience' => 'api.yourapp.com'
   ]);
   ...
}
```

For details on how to connect, see [Register an API](https://kinde.com/docs/developer-tools/register-an-api/)

### Overriding scope

By default the KindeSDK SDK requests the following scopes:

-   profile
-   email
-   offline
-   openid

You can override this by passing scope into the KindeSDK

```php
...
public function __construct()
{
   $this->kindeClient = new KindeClientSDK('KINDE_HOST', 'KINDE_REDIRECT_URL', 'KINDE_CLIENT_ID', 'KINDE_CLIENT_SECRET', 'KINDE_GRANT_TYPE', 'KINDE_POST_LOGOUT_REDIRECT_URL', 'profile email offline openid');
   ...
}
```

### Getting claims

We have provided a helper to grab any claim from your id or access tokens. The helper defaults to access tokens:

```php
$this->kindeClient->getClaim('aud');
// ['api.yourapp.com']

$this->kindeClient->getClaim('given_name', 'id_token');
// 'David'
```

### Organizations Control

#### Create an organization

To have a new organization created within your application, you will need to run a similar function to below:

```php
public function register()
{
    $this->kindeClient->createOrg();
}
```

You can also pass org_name as your organization
```php
    ...
    $this->kindeClient->createOrg(['org_name' => 'Your Organization']);
    ...
```

#### Sign up and sign in to organizations

Kinde has a unique code for every organization. You’ll have to pass this code through when you register a new user. Example function below:

```php
    $this->kindeClient->register(['org_code' => 'your_org_code']);
```

If you want a user to sign into a particular organization, pass this code along with the sign in method.

```php
    $this->kindeClient->login(['org_code' => 'your_org_code']);
```

Following authentication, Kinde provides a json web token (jwt) to your application. Along with the standard information we also include the org_code and the permissions for that organization (this is important as a user can belong to multiple organizations and have different permissions for each). Example of a returned token:

```json
{
    "aud": [],
    "exp": 1658475930,
    "iat": 1658472329,
    "iss": "https://your_subdomain.kinde.com",
    "jti": "123457890",
    "org_code": "org_1234",
    "permissions": ["read:todos", "create:todos"],
    "scp": ["openid", "profile", "email", "offline"],
    "sub": "kp:123457890"
}
```

The id_token will also contain an array of organization that a user belongs to - this is useful if you want to build out an organization switcher for example.

```json
{
...
"org_codes": ["org_1234", "org_4567"]
...
}
```

There are two helper functions you can use to extract information:

```php
$this->kindeClient->getOrganization();
// ['orgCode' => 'org_1234']

$this->kindeClient->getUserOrganizations();
// ['orgCodes' => ['org_1234', 'org_abcd']]
```

## SDK API Reference

| Property                        | Type   | Is required | Default                      | Description                                                                         |
| ------------------------------- | ------ | ----------- | ---------------------------- | ----------------------------------------------------------------------------------- |
| host                            | string | Yes         |                              | Either your Kinde instance url or your custom domain. e.g https://yourapp.kinde.com |
| redirectUri                     | string | Yes         |                              | The url that the user will be returned to after authentication                      |
| clientId                        | string | Yes         |                              | The id of your application - get this from the Kinde admin area                     |
| clientSecret                    | string | Yes         |                              | The id secret of your application - get this from the Kinde admin area              |
| logoutRedirectUri               | string | Yes         |                              | Where your user will be redirected upon logout                                      |
| scope                           | string | No          | openid profile email offline | The scopes to be requested from Kinde                                               |
| additionalParameters            | array  | No          | \[\]                         | Additional parameters that will be passed in the authorization request              |
| additionalParameters - audience | string | No          |                              | The audience claim for the JWT                                                      |

## KindeSDK methods

| Property             | Description                                                                                       | Arguments                        | Usage                                                                                  | Sample output                                                                                         |
| -------------------- | ------------------------------------------------------------------------------------------------- | -------------------------------- | -------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| login                | Constructs redirect url and sends user to Kinde to sign in                                        | org\_code?: string               | $kinde->login();                                                                       |                                                                                                       |
| register             | Constructs redirect url and sends user to Kinde to sign up                                        | org\_code?: string               | $kinde->register();                                                                    |                                                                                                       |
| logout               | Logs the user out of Kinde                                                                        |                                  | $kinde->logout();                                                                      |                                                                                                       |
| getToken             | Returns the raw access token from URL after logged from Kinde                                     |                                  | $kinde->getToken();                                                                    | eyJhbGciOiJIUzI1...                                                                                   |
| createOrg            | Constructs redirect url and sends user to Kinde to sign up and create a new org for your business | org\_name?: string               | $kinde->createOrg(); or $kinde->createOrg(\['org\_name' => 'your organization name'}); | redirect                                                                                              |
| getClaim             | Gets a claim from an access or id token                                                           | claim: string, tokenKey?: string | $kinde->getClaim('given\_name', 'id\_token');                                          | David'                                                                                                |
| getPermission        | Returns the state of a given permission                                                           | key: string                      | $kinde->getPermission('read:todos');                                                   | \['orgCode' => 'org\_1234', 'isGranted' => true\]                                                     |
| getPermissions       | Returns all permissions for the current user for the organization they are logged into            |                                  | $kinde->getPermissions();                                                              | \['orgCode' => 'org\_1234', permissions => \['create:todos', 'update:todos', 'read:todos'\]\]         |
| getOrganization      | Get details for the organization your user is logged into                                         |                                  | $kinde->getOrganization();                                                             | \['orgCode' => 'org\_1234'\]                                                                          |
| getUserDetails       | Returns the profile for the current user                                                          |                                  | $kinde->getUserDetails();                                                              | \['given\_name' => 'Dave', 'id' => 'abcdef', 'family\_name' => 'Smith', 'email' => 'dave@smith.com'\] |
| getUserOrganizations | Gets an array of all organizations the user has access to                                         |                                  |                                                                                        |                                                                                                       |

If you need help connecting to Kinde, please contact us at [support@kinde.com](mailto:support@kinde.com).
