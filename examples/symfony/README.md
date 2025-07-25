# Kinde Authentication Example - Symfony

This is a Symfony application demonstrating how to integrate Kinde authentication into a Symfony project.

## Features

- **User Authentication**: Login and register functionality using Kinde
- **User Dashboard**: Display user information, permissions, and organization details
- **Portal Integration**: Access to Kinde's user portal
- **Session Management**: Proper session handling for authenticated users
- **Modern UI**: Clean, responsive interface using Tailwind CSS

## Prerequisites

- PHP 8.2 or higher
- Composer
- Symfony CLI (optional but recommended)
- A Kinde account and application configured

## Installation

1. **Clone or navigate to the project directory:**
   ```bash
   cd kinde-php-sdk/examples/symfony
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment variables:**
   
   Create a `.env` file in the project root with the following variables:
   ```env
   # Kinde Configuration
   KINDE_DOMAIN=https://your-domain.kinde.com
   KINDE_CLIENT_ID=your_client_id
   KINDE_CLIENT_SECRET=your_client_secret
   KINDE_REDIRECT_URI=http://localhost:8000/auth/callback
   KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000
   KINDE_GRANT_TYPE=authorization_code
   KINDE_SCOPES=openid profile email offline
   KINDE_MANAGEMENT_ACCESS_TOKEN=your_management_access_token

   # Symfony Configuration
   APP_ENV=dev
   APP_SECRET=your_app_secret_here
   ```

4. **Start the development server:**
   ```bash
   symfony server:start
   # or
   php -S localhost:8000 -t public
   ```

## Configuration

### Kinde Setup

1. Create a Kinde account at [kinde.com](https://kinde.com)
2. Create a new application in your Kinde dashboard
3. Configure the following settings:
   - **Redirect URI**: `http://localhost:8000/auth/callback`
   - **Logout Redirect URI**: `http://localhost:8000`
   - **Grant Type**: Authorization Code
   - **Scopes**: `openid profile email offline`

4. Copy your domain, client ID, and client secret to the `.env` file

### Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `KINDE_DOMAIN` | Your Kinde domain (e.g., https://your-domain.kinde.com) | Yes |
| `KINDE_CLIENT_ID` | Your Kinde application client ID | Yes |
| `KINDE_CLIENT_SECRET` | Your Kinde application client secret | Yes |
| `KINDE_REDIRECT_URI` | Callback URL after authentication | Yes |
| `KINDE_LOGOUT_REDIRECT_URI` | URL to redirect after logout | Yes |
| `KINDE_GRANT_TYPE` | OAuth grant type (usually authorization_code) | Yes |
| `KINDE_SCOPES` | OAuth scopes to request | Yes |
| `KINDE_MANAGEMENT_ACCESS_TOKEN` | Token for management API access | Optional |

## Usage

### Authentication Flow

1. **Home Page**: Visit `http://localhost:8000` to see the landing page
2. **Login**: Click "Sign In" to authenticate with Kinde
3. **Register**: Click "Create Account" to register a new account
4. **Dashboard**: After authentication, you'll be redirected to the dashboard
5. **User Info**: View detailed user information and permissions
6. **Portal**: Access Kinde's user portal for account management
7. **Logout**: Click "Logout" to sign out

### Available Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/` | GET | Home page (landing page for unauthenticated users, dashboard for authenticated users) |
| `/auth/login` | GET | Initiate login flow |
| `/auth/register` | GET | Initiate registration flow |
| `/auth/callback` | GET | OAuth callback handler |
| `/auth/logout` | GET | Logout user |
| `/dashboard` | GET | User dashboard (requires authentication) |
| `/auth/user-info` | GET | Detailed user information (requires authentication) |
| `/auth/portal` | GET | Redirect to Kinde portal (requires authentication) |

### API Routes

The application also includes management API endpoints for server-to-server operations:

| Route | Method | Description |
|-------|--------|-------------|
| `/api/users` | GET | List all users |
| `/api/users` | POST | Create a new user |
| `/api/organizations` | GET | List all organizations |
| `/api/organizations` | POST | Create a new organization |
| `/api/applications` | GET | List all applications |
| `/api/roles` | GET | List all roles |
| `/api/permissions` | GET | List all permissions |
| `/api/feature-flags` | GET | List feature flags |
| `/api/user-profile` | GET | Get current user profile |
| `/api/users/bulk` | POST | Bulk create users |

## Project Structure

```
symfony/
├── config/                 # Symfony configuration
│   ├── services.yaml      # Service definitions
│   └── packages/          # Package configurations
├── src/
│   └── Controller/
│       └── ExampleController.php  # Main controller with all routes
├── templates/             # Twig templates
│   ├── base.html.twig    # Base template with navigation
│   └── kinde/
│       ├── home.html.twig      # Home page template
│       ├── dashboard.html.twig # Dashboard template
│       └── user-info.html.twig # User info template
├── public/               # Web root
└── vendor/              # Composer dependencies
```

## Key Features

### Session Management

The application uses Symfony's session system to store user authentication state:

- User information is stored in the session
- Authentication status is tracked
- Permissions and organization data are cached

### User Interface

- **Responsive Design**: Works on desktop and mobile devices
- **Dark Mode Support**: Automatic dark mode detection
- **Modern Styling**: Clean, professional appearance using Tailwind CSS
- **Accessibility**: Proper semantic HTML and ARIA attributes

### Security

- **OAuth 2.0**: Secure authentication using industry-standard protocols
- **Session Security**: Proper session handling and cleanup
- **CSRF Protection**: Built-in Symfony security features
- **Input Validation**: Proper validation of all user inputs

## Troubleshooting

### Common Issues

1. **"Authentication failed" error**
   - Check your Kinde configuration in the `.env` file
   - Verify redirect URIs match exactly
   - Ensure your Kinde application is properly configured

2. **"User information not available"**
   - Check if the user session is properly set
   - Verify the Kinde SDK is correctly configured
   - Check browser console for JavaScript errors

3. **Template rendering errors**
   - Ensure Twig is properly installed: `composer require twig`
   - Clear Symfony cache: `php bin/console cache:clear`

4. **Session issues**
   - Check session configuration in `config/packages/framework.yaml`
   - Verify session storage is working
   - Clear browser cookies and try again

### Debug Mode

To enable debug mode for more detailed error messages:

1. Set `APP_ENV=dev` in your `.env` file
2. Clear the cache: `php bin/console cache:clear`
3. Check the Symfony profiler for detailed information

## Contributing

This is an example application demonstrating Kinde integration with Symfony. For issues or improvements:

1. Check the main Kinde PHP SDK repository
2. Ensure your issue is reproducible
3. Provide detailed error messages and configuration

## License

This example is provided as-is for educational purposes. Please refer to the main Kinde PHP SDK repository for licensing information. 