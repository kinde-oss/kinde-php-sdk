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

