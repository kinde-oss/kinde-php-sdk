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
- **AI agents and MCP** - Accept tokens Kinde issues to AI agents, and sign agents in with CIMD or DCR

### Step-up authentication

Ask Kinde for a stronger authentication context before a sensitive operation. Kinde collects only the missing factor; the user keeps their session.

```php
use Kinde\KindeSDK\Sdk\Enums\AcrValues;
use Kinde\KindeSDK\Sdk\Exceptions\UnmetAuthenticationRequirementsException;

// Before a sensitive operation: require MFA within the last 5 minutes
if (!$kindeClient->hasAuthenticationContext(AcrValues::MFA, 300)) {
    if (!$kindeClient->isStepUpSupported()) {
        // Kinde would ignore the request, so the check could never pass
        throw new RuntimeException('Step-up authentication is not available');
    }
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
- `getToken()` now throws when the URL has an `error` parameter, even if the user is signed in, so call it only on your callback route.
- The Laravel, Symfony and CodeIgniter callback controllers show an unmet step-up as a flash error and keep the user signed in.
- `acr: "mfa"` does not assert that the two factors are independent of one another. See the [Kinde step-up documentation](https://docs.kinde.com/developer-tools/about/using-kinde-without-an-sdk/#step-up-authentication).

Left to your app:

- **Unsupported environments.** If `isStepUpSupported()` is false, Kinde ignores `acr_values` and `max_age`, so no token will meet an `mfa` or `phr` requirement: deny the action rather than treating a sign-in as MFA. Sending `prompt=login` forces a fresh sign-in, which only proves the user signed in recently. `stepUp()` does not fall back for you. Each client instance fetches the discovery document once and throws if it can't, so cache the result per Kinde domain in your app (for example with Laravel's cache).
- **`auth_time` on unsupported environments.** `getAuthenticationContext()` returns the token's `auth_time` as is, without a discovery request. On those environments it may be the token's issue time, so don't use it for recency.
- **Returning the user.** The SDK doesn't remember where the user was. In Laravel, call `session()->put('url.intended', url()->full())` and return `redirect()->away($kindeClient->getStepUpUrl(...))`; the built-in callback redirects to the intended URL. Don't use `stepUp()` there: it exits before Laravel saves the session. If a form POST needs step-up, return the user to the form page (not the POST endpoint) and have them submit again.

**Protecting an API.** Check the claims of the verified access token on every request with `StepUp::satisfies($claims, AcrValues::MFA)`, and return the RFC 9470 `insufficient_user_authentication` challenge when it is not met. Don't rely on the client to have requested the context.

### AI agents and MCP

Let AI agents (Claude, ChatGPT, your own) call your API or MCP server on a user's behalf. Kinde signs the user in and issues the agent an access token for your resource. Agents identify themselves with a **Client ID Metadata Document** (CIMD, preferred: the URL of a JSON file on the agent's domain is its client ID) or **Dynamic Client Registration** (DCR, the fallback for agents that can't host a document).

**In Kinde:** create an application of type **AI agents and MCP** and set its **Resource URL** to the exact URL agents call, for example `https://api.example.com/mcp`. To accept CIMD agents, add their bare domains (up to 10, such as `claude.ai`) to **Trusted agent hosts**. This application has no callback URLs, and it is separate from the application your users sign in to. DCR requires the `dcr_application` feature for your business.

```bash
KINDE_DOMAIN=https://example.kinde.com
KINDE_AGENTS_CLIENT_ID=<AI agents and MCP application client ID>
KINDE_AGENTS_CLIENT_SECRET=<its client secret>
KINDE_AGENTS_RESOURCE_URL=https://api.example.com/mcp
KINDE_AGENTS_VERIFICATION=introspection   # or jwt, or jwt+introspection
KINDE_AGENTS_SCOPES=                      # optional scopes to advertise (offline, profile, email)
KINDE_AGENTS_RESOURCE_NAME=               # optional display name
```

#### Protecting an API or MCP server

`KindeAgentAuth` verifies the token's issuer, audience (your Resource URL), expiry and signature or introspection result, and answers with the challenge agents need to find Kinde. Don't use `Utils::parseJWT()` for agent tokens: it doesn't check the issuer or audience.

```php
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuthOptions;

$auth = new KindeAgentAuth(KindeAgentAuthOptions::fromEnvironment());

$context = KindeAgentAuth::requirePermissions(
    $auth->verifyAuthorizationHeader($_SERVER['HTTP_AUTHORIZATION'] ?? null),
    'read:orders'
);
if (!$context->isAuthenticated()) {
    http_response_code(KindeAgentAuth::statusCode($context)); // 401, 403 or 503
    foreach ($auth->challengeHeaders($context) as $name => $value) {
        header("$name: $value");
    }
    echo json_encode(KindeAgentAuth::errorBody($context));
    exit;
}
$context->getSubject();      // the user the agent acts for
$context->getClientId();     // the agent
$context->hasPermission('write:orders');
```

Serve the discovery documents agents read first, with `KindeAgentAuth::metadataHeaders()` (CORS for browser-based agents):

- `$auth->metadata()` at every path in `$auth->getOptions()->getProtectedResourceMetadataPaths()`: the RFC 9728 path-suffixed form (`/.well-known/oauth-protected-resource/mcp`) and the root form.
- `$auth->authorizationServerMetadata()` at `/.well-known/oauth-authorization-server`, for agents that skip `authorization_servers`.

Answer `OPTIONS` on those paths with 204 and `KindeAgentAuth::preflightHeaders()`.

- **Verification modes.** `introspection` (default) asks Kinde about every token, so revocations take effect at once; it needs the client secret. `jwt` verifies the signature locally against Kinde's JWKS and is faster, but a revoked token stays valid until it expires. `jwt+introspection` verifies locally, then introspects. In jwt modes, pass a PSR-16 cache (`psr/simple-cache`) as the fourth constructor argument to share the JWKS between requests. The cookie-based JWKS cache used for user sign-in doesn't suit a stateless API.
- **Failures.** A missing or invalid token is 401 with a `WWW-Authenticate` challenge pointing at your metadata. A missing permission is a plain 403 with no challenge. If Kinde can't be reached, or rejects the application's credentials, the result is 503, never 401, so agents don't discard valid tokens.
- **Revoking.** `$auth->revokeToken($token)` cuts an agent off. Kinde revokes both tokens of the pair.

#### Laravel

Register `KindeServiceProvider` and publish the config. The `agents` section of `config/kinde.php` reads the variables above. When `resource_url` is set, the provider serves the discovery documents and their preflights. Set `KINDE_AGENTS_SERVE_AS_METADATA=false` if your app already serves `/.well-known/oauth-authorization-server`.

```php
Route::get('/api/orders', [OrderController::class, 'index'])->middleware('kinde.agent');
Route::post('/api/orders', [OrderController::class, 'store'])->middleware('kinde.agent:write:orders'); // required permissions

// In the controller
$context = $request->attributes->get('kinde_agent'); // KindeAuthContext
```

#### Symfony

Register `KindeAgentAuth` as a service (for example with the `KindeAgentAuthOptions::fromEnvironment()` factory), autoconfigure `KindeAgentAuthSubscriber` and `KindeAgentMetadataController`, and import the controller's routes (Symfony 6.2+):

```yaml
# config/routes/kinde_agents.yaml
kinde_agents:
    resource: Kinde\KindeSDK\Frameworks\Symfony\KindeAgentMetadataController
    type: attribute
```

```php
use Kinde\KindeSDK\Frameworks\Symfony\RequireAgentAuth;

#[RequireAgentAuth(permissions: ['write:orders'])]
public function store(Request $request): Response
{
    $context = $request->attributes->get('kinde_agent'); // KindeAuthContext
}
```

#### CodeIgniter

Register the filter in `app/Config/Filters.php` and apply it to routes. `KindeAgentAuthFilter` reads the environment variables above. The routes for `KindeAgentMetadataController` are in its docblock.

```php
public array $aliases = ['kinde-agent' => \Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAgentAuthFilter::class];

$routes->post('api/orders', 'Orders::store', ['filter' => 'kinde-agent:write:orders']);

// In the controller
$context = \Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAgentAuthFilter::context();
```

#### MCP servers (mcp/sdk)

With the official MCP PHP SDK (`mcp/sdk`, PHP 8.2+), plug `KindeMcpTokenValidator` into the HTTP transport's middleware:

```php
use Kinde\KindeSDK\Sdk\Agents\Mcp\KindeMcpTokenValidator;
use Mcp\Server\Transport\Http\Middleware\AuthorizationMiddleware;
use Mcp\Server\Transport\Http\Middleware\ProtectedResourceMetadataMiddleware;

$metadata = KindeMcpTokenValidator::metadata($auth);
$middleware = [
    new ProtectedResourceMetadataMiddleware($metadata),
    new AuthorizationMiddleware(new KindeMcpTokenValidator($auth), $metadata),
];
```

Check permissions inside each tool, and answer with a tool error rather than an authorization failure:

```php
$kinde = KindeAuthContext::fromClaims($context->getAccessToken()->getClaims());
if (!$kinde->hasPermission('read:docs')) {
    return new CallToolResult([new TextContent("You don't have permission to read docs.")], true);
}
```

#### Building an agent

`KindeAgentClient` signs a user in to a Kinde AI agents and MCP application with the authorization code flow and PKCE, and keeps the tokens fresh in an `AgentStore` (`FileAgentStore`, or your own backed by a database or cache).

```php
use Kinde\KindeSDK\Sdk\Agents\Client\FileAgentStore;
use Kinde\KindeSDK\Sdk\Agents\Client\KindeAgentClient;
use Kinde\KindeSDK\Sdk\Agents\Client\KindeAgentClientOptions;

$client = new KindeAgentClient(new KindeAgentClientOptions([
    'domain' => 'https://example.kinde.com',
    'agent_application_id' => '<AI agents and MCP application client ID>',
    'redirect_uri' => 'https://agent.example.com/callback',
    'cimd_client_id' => 'https://agent.example.com/oauth/client.json', // omit to use DCR
    'resource_url' => 'https://api.example.com/mcp',
    'scopes' => ['offline'],
    'session_key' => $userId, // keeps each user's tokens apart
]), new FileAgentStore('/var/lib/my-agent'));

header('Location: ' . $client->createAuthorizationRequest()->url); // start sign-in
$client->handleCallback($_GET);                                     // on redirect_uri
$token = $client->getAccessToken(); // refreshed when needed; null means sign in again
$client->disconnect();              // revoke and forget
```

- **CIMD.** Serve the document at `cimd_client_id`, on a host in the application's Trusted agent hosts, with `ClientMetadataDocument::build($clientId, $clientName, $redirectUris)` and `ClientMetadataDocument::RESPONSE_HEADERS`. Laravel serves it from `kinde.agents.client_metadata`, Symfony has `KindeClientMetadataController`, and CodeIgniter has `KindeAgentMetadataController::clientMetadata` (from `KINDE_AGENTS_CLIENT_METADATA_URL`, `KINDE_AGENTS_CLIENT_NAME` and `KINDE_AGENTS_REDIRECT_URIS`). Redirect URIs must be https on the document's host, or http loopback.
- **DCR.** Without `cimd_client_id` or `client_id`, the client registers itself and stores the registration. Kinde registrations expire after 30 days; the client registers again before then, and when Kinde rejects the client.
- **Scopes.** Kinde accepts `offline`, `profile` and `email`. `openid` is refused: agent tokens are access tokens only.
- **Starting from the resource.** `AgentOAuth::discoverFromResource()` follows a 401 `WWW-Authenticate` challenge or the protected resource metadata to the authorization server. `AgentOAuth` also exposes each step (`register`, `createAuthorizationRequest`, `exchangeCode`, `refresh`, `revoke`) for stateless use.

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
