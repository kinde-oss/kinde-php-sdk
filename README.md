# Kinde PHP SDK

The official PHP SDK for Kinde authentication and management APIs.

## Overview

The Kinde PHP SDK provides two main clients:

1. **KindeClientSDK** - For OAuth user authentication (frontend applications)
2. **KindeManagementClient** - For server-to-server management operations (backend services)

## Installation

```bash
composer require kinde-oss/kinde-php-sdk
```

## Quick Start

### Environment Variables

Set up your environment variables:

```env
# Required for both clients
KINDE_DOMAIN=https://your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret

# OAuth client specific
KINDE_REDIRECT_URI=http://localhost:8000/auth/callback
KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000
KINDE_GRANT_TYPE=authorization_code

# Management client specific (optional)
KINDE_MANAGEMENT_ACCESS_TOKEN=your_access_token
```

### OAuth Client (User Authentication)

```php
use Kinde\KindeSDK\KindeClientSDK;

// Create OAuth client from environment variables (recommended)
$kindeClient = KindeClientSDK::createFromEnv();

// Or use constructor (same result)
$kindeClient = new KindeClientSDK();

// Or override specific parameters
$kindeClient = new KindeClientSDK(
    domain: 'https://custom-domain.kinde.com', // Override domain
    redirectUri: null, // Use from environment
    clientId: null, // Use from environment
    clientSecret: null, // Use from environment
    grantType: 'authorization_code' // Override grant type
);

// Redirect user to login
$kindeClient->login();

// Handle callback and get user info
if ($kindeClient->isAuthenticated) {
    $user = $kindeClient->getUserDetails();
    echo "Welcome, {$user['given_name']}!";
    
    // Check user entitlements
    if ($kindeClient->hasEntitlement('premium_features')) {
        $limit = $kindeClient->getEntitlementLimit('premium_features');
        echo "You have premium features with limit: " . $limit;
    }
}
```

### Management Client (Server-to-Server)

```php
use Kinde\KindeSDK\KindeManagementClient;

// Create management client from environment variables (recommended)
$management = KindeManagementClient::createFromEnv();

// Or use constructor (same result)
$management = new KindeManagementClient();

// Or override specific parameters
$management = new KindeManagementClient(
    domain: 'https://custom-domain.kinde.com', // Override domain
    clientId: null, // Use from environment
    clientSecret: null, // Use from environment
    accessToken: 'custom_token' // Override access token
);

// Create a user
$user = $management->users->createUser([
    'given_name' => 'John',
    'family_name' => 'Doe',
    'email' => 'john@example.com'
]);

// Get all users
$users = $management->users->getUsers();

// Create an organization
$org = $management->organizations->createOrganization([
    'name' => 'My Organization'
]);
```

## Framework Integration

### Laravel

```bash
composer require kinde-oss/kinde-auth-php
```

**Register the service provider in `config/app.php`:**
```php
'providers' => [
    // ... other providers
    Kinde\KindeSDK\Frameworks\Laravel\KindeServiceProvider::class,
],
```

**Publish the configuration:**
```bash
php artisan vendor:publish --tag=kinde-config
```

> **Security Note**: Environment variables are only accessible to server-side code (controllers, services, etc.) and are not available to client-side code or public assets. This ensures that sensitive configuration like `KINDE_CLIENT_SECRET` remains secure and is never exposed to the browser.

```php
// In your controller
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;

class AuthController extends Controller
{
    public function __construct(
        private KindeClientSDK $kindeClient,
        private KindeManagementClient $management
    ) {}
    
    public function login()
    {
        return $this->kindeClient->login();
    }
    
    public function createUser(Request $request)
    {
        $user = $this->management->users->createUser([
            'given_name' => $request->input('given_name'),
            'family_name' => $request->input('family_name'),
            'email' => $request->input('email')
        ]);
        
        return response()->json($user);
    }
}
```

## Available APIs

### Management Client APIs

The `KindeManagementClient` provides access to all management APIs:

- **Users API** - `$management->users`
- **Organizations API** - `$management->organizations`
- **Applications API** - `$management->applications`
- **Roles API** - `$management->roles`
- **Permissions API** - `$management->permissions`
- **Feature Flags API** - `$management->featureFlags`
- **Environments API** - `$management->environments`
- **OAuth API** - `$management->oauth`
- **And many more...**

### OAuth Client Features

The `KindeClientSDK` provides OAuth authentication features:

- User login/logout
- Authorization code flow
- PKCE flow
- User profile access
- Token management
- Portal redirects
- **Entitlements** - Access user billing entitlements and feature limits
- **Step-up authentication** - Require MFA, a passkey or a recent sign-in for sensitive operations (RFC 9470)

### Step-up authentication

Ask Kinde for a stronger authentication context before a sensitive operation. Kinde collects only the missing factor; the user keeps their session.

```php
use Kinde\KindeSDK\Sdk\Enums\AcrValues;
use Kinde\KindeSDK\Sdk\Exceptions\UnmetAuthenticationRequirementsException;

// Before a sensitive operation: require MFA within the last 5 minutes
if (!$kindeClient->hasAuthenticationContext(AcrValues::MFA, 300)) {
    $kindeClient->stepUp(AcrValues::MFA, 300); // redirects and exits; throws if output has already started
}

// In a framework, return a redirect response instead so the session is saved, e.g. in Laravel:
// return redirect()->away($kindeClient->getStepUpUrl(AcrValues::MFA, 300));

// In your callback, as for a normal login. Stepped-up tokens replace the stored ones.
try {
    $kindeClient->getToken();
} catch (UnmetAuthenticationRequirementsException $e) {
    // The user cannot meet the requirement (e.g. no enrolled MFA factor or no passkey).
    // This is definite: don't retry or redirect to login. Tell the user why, e.g. "add a passkey to continue".
    $e->getErrorDescription();
    $e->getState();
}

$kindeClient->getAuthenticationContext(); // ['acr' => 'mfa', 'amr' => ['pwd', 'totp'], 'auth_time' => 1716800000]
```

- `AcrValues::MFA` requires a second factor; `AcrValues::PHR` requires a phishing-resistant factor (a passkey) and also meets an `mfa` requirement. An absent `acr` means a single-factor login.
- `stepUp($acr, 0)` forces a fresh verification, but don't check `hasAuthenticationContext($acr, 0)`: it is almost never met, so the user would be sent back to Kinde on every request. Use a short window such as 60 seconds.
- `getStepUpUrl()` stores a new state each time it is called, replacing any login in progress, so call it when you redirect rather than to render a link.
- `acr_values` and `max_age` can also be passed to `login()` as additional parameters. `stepUp()` rejects `prompt=none`, because Kinde can't collect a factor without a screen.
- A `login_required` error from a `prompt=none` request throws `LoginRequiredException`, with the same `getErrorDescription()` and `getState()`.
- The Laravel, Symfony and CodeIgniter callback controllers show an unmet step-up as a flash error and keep the user signed in.
- `acr: "mfa"` does not assert that the two factors are independent of one another. See the [Kinde step-up documentation](https://docs.kinde.com/developer-tools/about/using-kinde-without-an-sdk/#step-up-authentication).

Left to your app:

- **Unsupported environments.** Check `$kindeClient->isStepUpSupported()` before you rely on step-up. If it is false, Kinde ignores `acr_values` and `max_age`; send `prompt=login` to force a fresh sign-in. `stepUp()` does not fall back for you. Each client instance fetches the discovery document once and throws if it can't, so cache the result in your app, e.g. `Cache::remember('kinde_step_up_supported', 3600, fn () => $kindeClient->isStepUpSupported())`.
- **`auth_time` on unsupported environments.** `getAuthenticationContext()` returns the token's `auth_time` as is, without a discovery request. On those environments it may be the token's issue time, so don't use it for recency.
- **Returning the user.** The SDK doesn't remember where the user was. In Laravel, call `session()->put('url.intended', url()->full())` and return `redirect()->away($kindeClient->getStepUpUrl(...))`; the built-in callback redirects to the intended URL. Don't use `stepUp()` there: it exits before Laravel saves the session. If a form POST needs step-up, return the user to the form page (not the POST endpoint) and have them submit again.

**Protecting an API.** Check the claims of the verified access token on every request with `StepUp::satisfies($claims, AcrValues::MFA)`, and return the RFC 9470 `insufficient_user_authentication` challenge when it is not met. Don't rely on the client to have requested the context.

## Documentation

- [Management Client Documentation](MANAGEMENT_CLIENT.md)
- [Entitlements Documentation](docs/ENTITLEMENTS.md)
- [Framework Integration](FRAMEWORK_INTEGRATION.md)
- [Framework Examples](FRAMEWORK_EXAMPLES.md)
- [Portal Integration](PORTAL_INTEGRATION.md)
- [Inertia.js Integration](INERTIA_INTEGRATION.md)

## Examples

See the [playground](playground/) directory for complete working examples.

## Migration Guide

If you're currently using the API classes directly, you can migrate to the management client:

### Before
```php
use Kinde\KindeSDK\Api\UsersApi;
use Kinde\KindeSDK\Configuration;

$config = new Configuration();
$config->setHost('https://your-domain.kinde.com');
$config->setAccessToken('your_token');

$usersApi = new UsersApi(null, $config);
$users = $usersApi->getUsers();
```

### After
```php
use Kinde\KindeSDK\KindeManagementClient;

$management = KindeManagementClient::createFromEnv();
$users = $management->users->getUsers();
```

## Support

- [Documentation](https://docs.kinde.com)
- [API Reference](https://docs.kinde.com/kinde-apis/)

## License

This project is licensed under the MIT License.
