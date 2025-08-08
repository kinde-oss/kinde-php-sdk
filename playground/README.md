# Kinde PHP SDK Examples

This directory contains examples demonstrating how to use the Kinde PHP SDK with different frameworks and scenarios.

## Quick Start

### Prerequisites

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Set Environment Variables**
   Create a `.env` file in the root directory or set environment variables:
   ```bash
   export KINDE_DOMAIN="https://your-domain.kinde.com"
   export KINDE_CLIENT_ID="your_client_id"
   export KINDE_CLIENT_SECRET="your_client_secret"
   export KINDE_REDIRECT_URI="http://localhost:8000/auth/callback"
   export KINDE_GRANT_TYPE="authorization_code"
   export KINDE_LOGOUT_REDIRECT_URI="http://localhost:8000"
   export KINDE_SCOPES="openid profile email offline"
   export KINDE_PROTOCOL="https"
   ```

## Available Examples

### 1. Management Client Example (Standalone)

**File**: `management_client_example.php`

**Description**: Demonstrates server-to-server operations using the KindeManagementClient.

**Run it**:
```bash
cd kinde-php-sdk
php playground/management_client_example.php
```

**What it does**:
- Creates a management client
- Lists users, organizations, applications, feature flags, roles, and permissions
- Shows error handling for API calls

### 2. Framework Examples

Each framework example includes a complete controller/application that demonstrates:
- OAuth client initialization
- Login/logout flows
- User authentication
- Portal redirection
- Error handling

#### Laravel Example

**File**: `laravel/ExampleController.php`

**Description**: Complete Laravel controller with all authentication endpoints.

**To use in Laravel**:
1. Copy the controller to your Laravel app's `app/Http/Controllers/` directory
2. Add routes to `routes/web.php`:
   ```php
   Route::get('/auth/login', [KindeAuthController::class, 'login']);
   Route::get('/auth/callback', [KindeAuthController::class, 'callback']);
   Route::get('/auth/logout', [KindeAuthController::class, 'logout']);
   Route::get('/auth/register', [KindeAuthController::class, 'register']);
   Route::get('/auth/create-org', [KindeAuthController::class, 'createOrg']);
   Route::get('/user/info', [KindeAuthController::class, 'userInfo']);
   Route::get('/portal', [KindeAuthController::class, 'portal']);
   ```

#### Symfony Example

**File**: `symfony/ExampleController.php`

**Description**: Complete Symfony controller with authentication endpoints.

**To use in Symfony**:
1. Copy the controller to your Symfony app's `src/Controller/` directory
2. Add routes to `config/routes.yaml` or use annotations
3. Install the Kinde SDK: `composer require kinde/kinde-php-sdk`

#### CodeIgniter Example

**File**: `codeigniter/ExampleController.php`

**Description**: Complete CodeIgniter controller with authentication endpoints.

**To use in CodeIgniter**:
1. Copy the controller to your CodeIgniter app's `app/Controllers/` directory
2. Add routes to `app/Config/Routes.php`
3. Install the Kinde SDK: `composer require kinde/kinde-php-sdk`

## Running Examples

### Option 1: Standalone Management Client

```bash
# Set environment variables
export KINDE_DOMAIN="https://your-domain.kinde.com"
export KINDE_CLIENT_ID="your_client_id"
export KINDE_CLIENT_SECRET="your_client_secret"

# Run the example
php examples/management_client_example.php
```

### Option 2: Framework Examples

Each framework example can be integrated into its respective framework application. The examples show:

- **OAuth Client Setup**: How to initialize the KindeClientSDK
- **Authentication Flows**: Login, logout, registration, and organization creation
- **User Management**: Getting user information and handling authentication states
- **Portal Integration**: Redirecting users to Kinde's user portal
- **Error Handling**: Proper exception handling for OAuth and API errors

## Environment Variables

All examples use these environment variables:

| Variable | Description | Required |
|----------|-------------|----------|
| `KINDE_DOMAIN` | Your Kinde domain | Yes |
| `KINDE_CLIENT_ID` | Your application client ID | Yes |
| `KINDE_CLIENT_SECRET` | Your application client secret | Yes |
| `KINDE_REDIRECT_URI` | OAuth redirect URI | Yes |
| `KINDE_GRANT_TYPE` | OAuth grant type (default: authorization_code) | No |
| `KINDE_LOGOUT_REDIRECT_URI` | Logout redirect URI | No |
| `KINDE_SCOPES` | OAuth scopes (default: openid profile email offline) | No |
| `KINDE_PROTOCOL` | Protocol for API calls (default: https) | No |

## Testing Examples

You can test the examples by:

1. **Management Client**: Run directly with proper environment variables
2. **Framework Examples**: Integrate into your framework application and test the endpoints

## Troubleshooting

### Common Issues

1. **"Class not found" errors**: Make sure you've run `composer install`
2. **Authentication errors**: Verify your environment variables are set correctly
3. **Redirect URI mismatch**: Ensure your redirect URI matches what's configured in Kinde
4. **CORS issues**: Make sure your domain is allowed in Kinde settings

### Debug Mode

To enable debug output, set:
```bash
export KINDE_DEBUG=true
```

## Next Steps

After running the examples:

1. **Customize**: Modify the examples to fit your application's needs
2. **Security**: Implement proper session management and CSRF protection
3. **Error Handling**: Add more robust error handling for production use
4. **Testing**: Write tests for your authentication flows

## Support

For issues with the examples or SDK, please check:
- [SDK Documentation](../README.md)
- [API Documentation](https://kinde.com/docs/api/)
- [Community Support](https://kinde.com/support/) 