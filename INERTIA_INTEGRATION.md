# Kinde Laravel Integration with Inertia.js

This document shows how to use the Kinde Laravel package with Inertia.js for building SPAs with Vue/React.

## Installation

### 1. Install Kinde Laravel Package
```bash
composer require kinde-oss/kinde-auth-php
```

### 2. Install Inertia.js (if not already installed)
```bash
composer require inertiajs/inertia-laravel
npm install @inertiajs/vue3 @inertiajs/inertia-vue3
```

## Configuration

### Environment Variables
```env
KINDE_DOMAIN=https://your-domain.kinde.com
KINDE_CLIENT_ID=your_client_id
KINDE_CLIENT_SECRET=your_client_secret
KINDE_REDIRECT_URI=http://localhost:8000/auth/callback
KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000
```

### Inertia Setup
```php
// app/Http/Middleware/HandleInertiaRequests.php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Kinde data is automatically shared via the service provider
        ]);
    }
}
```
