# Kinde PHP SDK - CodeIgniter Playground

A comprehensive playground for testing and exploring the Kinde PHP SDK with CodeIgniter 4, featuring full Management API testing capabilities.

## Features

### 🔐 Authentication
- **Login/Logout**: Standard OAuth2 authentication flow
- **Registration**: User registration with Kinde
- **Organization Creation**: Create new organizations during signup
- **User Portal**: Access to Kinde's user management portal
- **Protected Routes**: Authentication-based route protection
- **Permission-based Access**: Role and permission-based access control

### ⚙️ Management API Testing
- **Comprehensive API Dashboard**: Test all Management API endpoints with detailed reporting
- **Header Fix Status**: Monitor and verify API header fix functionality
- **Individual Endpoint Testing**: Test specific API endpoints with detailed error reporting
- **Bulk Operations**: Test bulk user creation and other batch operations

### 📊 Available API Endpoints

#### Core Management APIs
- **Users**: List, create, and manage users
- **Organizations**: List and create organizations
- **Applications**: List and manage applications
- **Roles**: List and manage roles
- **Permissions**: List and manage permissions

#### Advanced APIs
- **Feature Flags**: Environment-specific feature flag management
- **Environment**: Get environment information
- **Business**: Get business information
- **Timezones**: List available timezones
- **Industries**: List available industries
- **Property Categories**: List property categories
- **Properties**: List and manage properties
- **APIs**: List and manage APIs
- **Webhooks**: List and manage webhooks
- **Subscribers**: List and manage subscribers

### 🎯 User Interface
- **Modern Dashboard**: Clean, responsive interface with Tailwind CSS
- **Real-time Status**: Live authentication and permission status
- **Navigation**: Easy access to all features and endpoints
- **Error Handling**: Comprehensive error reporting and debugging

## Setup

### Prerequisites
- PHP 8.0 or higher
- Composer
- CodeIgniter 4
- Kinde account and application

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd kinde-php-sdk/playground/codeigniter
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp env.example .env
   ```

4. **Set up Kinde credentials**
   Add your Kinde application credentials to the `.env` file:
   ```env
   # Frontend Application
   KINDE_CLIENT_ID=your_client_id
   KINDE_CLIENT_SECRET=your_client_secret
   KINDE_DOMAIN=your_domain.kinde.com
   KINDE_REDIRECT_URI=http://localhost:8080/auth/callback
   KINDE_LOGOUT_REDIRECT_URI=http://localhost:8080

   # Management API (M2M Application)
   KINDE_MANAGEMENT_CLIENT_ID=your_m2m_client_id
   KINDE_MANAGEMENT_CLIENT_SECRET=your_m2m_client_secret
   KINDE_MANAGEMENT_DOMAIN=your_domain.kinde.com
   ```

5. **Start the development server**
   ```bash
   php spark serve
   ```

## Usage

### Getting Started

1. **Visit the home page**: Navigate to `http://localhost:8080`
2. **Login or Register**: Use the authentication buttons to get started
3. **Explore the Dashboard**: Access user information and available features
4. **Test Management APIs**: Use the comprehensive testing dashboard

### Management API Testing

#### Comprehensive Testing Dashboard
- **URL**: `/test-management-api`
- **Features**:
  - Tests all 15+ Management API endpoints
  - Provides success/failure statistics
  - Shows header fix status
  - Detailed error reporting
  - Real-time API response data

#### Individual Endpoint Testing
- **Users**: `/api/users` (GET/POST)
- **Organizations**: `/api/organizations` (GET/POST)
- **Applications**: `/api/applications` (GET)
- **Roles**: `/api/roles` (GET)
- **Permissions**: `/api/permissions` (GET)
- **Feature Flags**: `/api/feature-flags` (GET)

#### Advanced Testing
- **Specific Endpoint Testing**: `/api/test-endpoint?endpoint=users&action=list`
- **Bulk Operations**: `/api/bulk-create-users` (POST)
- **User Profile**: `/api/user-profile` (GET)

### Authentication Flows

#### Standard Login
1. Click "Login" on the home page
2. Redirected to Kinde authentication
3. Complete authentication
4. Redirected back to dashboard

#### Organization Creation
1. Click "Create Organization" 
2. Fill in organization details
3. Complete authentication flow
4. Organization created and user added

#### User Portal
1. Click "Go to Portal" from dashboard
2. Redirected to Kinde user portal
3. Manage account settings
4. Return to application

### Protected Routes

#### Authentication Required
- `/dashboard` - User dashboard
- `/protected` - Example protected route
- `/auth/user-info` - User information
- `/auth/portal` - User portal access

#### Permission Required
- `/admin` - Admin area (requires `admin:read` permission)

## API Response Examples

### Successful API Call
```json
{
  "success": true,
  "data": {
    "users": [...],
    "count": 5
  }
}
```

### Failed API Call
```json
{
  "success": false,
  "error": "Authentication failed",
  "code": 401
}
```

### Management API Test Results
```json
{
  "testResults": {
    "users": {
      "success": true,
      "count": 5
    },
    "organizations": {
      "success": true,
      "count": 2
    }
  },
  "summary": {
    "total": 15,
    "successful": 14,
    "failed": 1,
    "success_rate": 93.33
  }
}
```

## Troubleshooting

### Common Issues

#### Authentication Errors
- **Problem**: "Invalid client credentials"
- **Solution**: Verify your Kinde application credentials in `.env`

#### API Permission Errors
- **Problem**: "Insufficient permissions"
- **Solution**: Ensure your M2M application has the required scopes

#### Header Fix Issues
- **Problem**: Content-type errors in API calls
- **Solution**: Check the header fix status in the testing dashboard

#### Session Issues
- **Problem**: Authentication state not persisting
- **Solution**: Verify session configuration in CodeIgniter

### Debugging

1. **Check the testing dashboard**: `/test-management-api`
2. **Review error logs**: Check CodeIgniter logs in `writable/logs/`
3. **Test individual endpoints**: Use specific endpoint URLs
4. **Verify credentials**: Ensure all environment variables are set correctly

## Development

### Project Structure
```
app/
├── Controllers/
│   └── ExampleController.php    # Main controller with all features
├── Views/
│   └── kinde/
│       ├── home.php             # Home page with navigation
│       ├── dashboard.php        # User dashboard
│       ├── test-management-api.php  # API testing dashboard
│       ├── protected.php        # Protected route example
│       └── admin.php            # Admin area example
└── Config/
    └── Routes.php               # Application routes
```

### Adding New Features

1. **Add controller method** in `ExampleController.php`
2. **Create view file** in `app/Views/kinde/`
3. **Add route** in `app/Config/Routes.php`
4. **Update navigation** in relevant view files

### Testing New APIs

1. **Add API call** to `testManagementApi()` method
2. **Add individual endpoint** method for direct testing
3. **Update routes** for new endpoints
4. **Test via dashboard** and individual URLs

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This playground is part of the Kinde PHP SDK and follows the same license terms.

## Support

For issues and questions:
- Check the [Kinde Documentation](https://kinde.com/docs/)
- Review the [PHP SDK Documentation](https://github.com/kinde-oss/kinde-php-sdk)
- Open an issue in the repository
