# Kinde PHP SDK - Laravel Playground

This is a Laravel playground application demonstrating the integration of the Kinde PHP SDK for authentication and user management.

## Prerequisites

- PHP 8.0 or higher
- Composer
- A Kinde account and application

## Installation

1. **Install dependencies via Composer:**
   ```bash
   composer install
   ```

2. **Set up environment variables:**
   - Copy the `.env.example` file to `.env`
   - Configure your Kinde application credentials:
     ```
     KINDE_CLIENT_ID=your_client_id
     KINDE_CLIENT_SECRET=your_client_secret
     KINDE_DOMAIN=your_domain.kinde.com
     KINDE_REDIRECT_URI=http://localhost:8000/auth/callback
     KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000
     ```

3. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

## Running the Application

Start the development server using PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

The application will be available at `http://localhost:8000`.

## Features

This playground demonstrates:

- User authentication with Kinde
- User profile management
- Organization management
- Feature flags integration
- Entitlements handling

## Project Structure

- `app/Http/Controllers/` - Controllers handling authentication and user management
- `routes/web.php` - Web routes for the application
- `resources/views/` - Blade templates for the UI
- `config/kinde.php` - Kinde SDK configuration

## Documentation

For more information about the Kinde PHP SDK, visit:
- [Kinde Documentation](https://kinde.com/docs)
- [Kinde PHP SDK Documentation](../docs/)

## Support

If you encounter any issues or have questions, please refer to the main Kinde PHP SDK documentation or contact Kinde support.
