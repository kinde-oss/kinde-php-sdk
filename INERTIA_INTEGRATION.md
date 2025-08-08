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

## Routes

### Web Routes
```php
// routes/web.php
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware('kinde.auth');

Route::get('/user-info', function () {
    return Inertia::render('UserInfo');
})->middleware('kinde.auth');
```

### Auth Routes (Auto-registered)
The following routes are automatically registered by the Kinde package:
- `GET /auth/login` - Redirect to Kinde login
- `GET /auth/callback` - Handle OAuth callback
- `GET /auth/register` - Redirect to Kinde registration
- `GET /auth/create-org` - Create organization
- `GET /auth/logout` - Logout user

## Vue Components

### User Info Component
```vue
<template>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">User Info</h1>
            
            <div v-if="kinde.isAuthenticated" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">User Information</h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="font-medium">Name:</span>
                        <span>{{ kinde.user?.given_name || 'N/A' }} {{ kinde.user?.family_name || 'N/A' }}</span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Email:</span>
                        <span>{{ kinde.user?.email || 'N/A' }}</span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Organization:</span>
                        <span>{{ kinde.organization?.orgCode || 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Permissions</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="permission in kinde.permissions?.permissions" :key="permission">
                            {{ permission }}
                        </li>
                    </ul>
                </div>
                
                <div class="mt-6">
                    <a href="/auth/logout" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Logout
                    </a>
                </div>
            </div>
            
            <div v-else class="text-center">
                <p class="text-gray-600 mb-4">You are not authenticated.</p>
                <a href="/auth/login" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Login with Kinde
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { kinde } = usePage().props
</script>
```

### Dashboard Component
```vue
<template>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
            
            <div v-if="kinde.isAuthenticated" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Welcome, {{ kinde.user?.given_name || 'User' }}!</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">User Info</h3>
                        <p><strong>Email:</strong> {{ kinde.user?.email }}</p>
                        <p><strong>Organization:</strong> {{ kinde.organization?.orgCode }}</p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Permissions</h3>
                        <ul class="text-sm space-y-1">
                            <li v-for="permission in kinde.permissions?.permissions" :key="permission">
                                • {{ permission }}
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-4">
                    
                    <a href="/auth/logout" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Logout
                    </a>
                </div>
            </div>
            
            <div v-else class="text-center">
                <p class="text-gray-600 mb-4">Please log in to access the dashboard.</p>
                <a href="/auth/login" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Login with Kinde
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { kinde } = usePage().props
</script>
```

### Navigation Component
```vue
<template>
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold">My App</h1>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div v-if="kinde.isAuthenticated" class="flex items-center space-x-4">
                        <span class="text-gray-700">{{ kinde.user?.given_name }}</span>
                        <a href="/auth/logout" class="text-red-600 hover:text-red-500">Logout</a>
                    </div>
                    
                    <div v-else>
                        <a href="/auth/login" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { kinde } = usePage().props
</script>
```

## React Components

If you're using React instead of Vue, here's how the components would look:

### User Info Component (React)
```jsx
import { usePage } from '@inertiajs/react'

export default function UserInfo() {
    const { kinde } = usePage().props

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="max-w-2xl mx-auto">
                <h1 className="text-3xl font-bold mb-6">Profile</h1>
                
                {kinde.isAuthenticated ? (
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold mb-4">User Information</h2>
                        
                        <div className="space-y-3">
                            <div>
                                <span className="font-medium">Name:</span>
                                <span>{kinde.user?.given_name || 'N/A'} {kinde.user?.family_name || 'N/A'}</span>
                            </div>
                            
                            <div>
                                <span className="font-medium">Email:</span>
                                <span>{kinde.user?.email || 'N/A'}</span>
                            </div>
                            
                            <div>
                                <span className="font-medium">Organization:</span>
                                <span>{kinde.organization?.orgCode || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <div className="mt-6">
                            <h3 className="text-lg font-semibold mb-2">Permissions</h3>
                            <ul className="list-disc list-inside space-y-1">
                                {kinde.permissions?.permissions?.map(permission => (
                                    <li key={permission}>{permission}</li>
                                ))}
                            </ul>
                        </div>
                        
                        <div className="mt-6">
                            <a href="/auth/logout" 
                               className="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Logout
                            </a>
                        </div>
                    </div>
                ) : (
                    <div className="text-center">
                        <p className="text-gray-600 mb-4">You are not authenticated.</p>
                        <a href="/auth/login" 
                           className="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Login with Kinde
                        </a>
                    </div>
                )}
            </div>
        </div>
    )
}
```

## Permission Checking

### In Vue Components
```vue
<template>
    <div>
        <div v-if="hasPermission('read:users')">
            <h2>Users</h2>
            <!-- User management interface -->
        </div>
        
        <div v-if="hasPermission('write:users')">
            <button>Add User</button>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const { kinde } = usePage().props

const hasPermission = (permission) => {
    return kinde.permissions?.permissions?.includes(permission) || false
}
</script>
```

### In React Components
```jsx
import { usePage } from '@inertiajs/react'

export default function Users() {
    const { kinde } = usePage().props

    const hasPermission = (permission) => {
        return kinde.permissions?.permissions?.includes(permission) || false
    }

    return (
        <div>
            {hasPermission('read:users') && (
                <div>
                    <h2>Users</h2>
                    {/* User management interface */}
                </div>
            )}
            
            {hasPermission('write:users') && (
                <div>
                    <button>Add User</button>
                </div>
            )}
        </div>
    )
}
```

## Middleware with Permissions

### Route Protection
```php
// routes/web.php
Route::middleware('kinde.auth:read:users')->group(function () {
    Route::get('/users', function () {
        return Inertia::render('Users');
    });
});

Route::middleware('kinde.auth:write:users')->group(function () {
    Route::post('/users', function () {
        // Create user logic
    });
});
```

## Shared Data Structure

The Kinde data is automatically shared with all Inertia pages:

```javascript
{
    kinde: {
        isAuthenticated: true,
        user: {
            given_name: "John",
            family_name: "Doe",
            email: "john@example.com",
            // ... other user properties
        },
        permissions: {
            orgCode: "org_123",
            permissions: ["read:users", "write:users", "read:profile"]
        },
        organization: {
            orgCode: "org_123"
        }
    }
}
```

## Benefits of Inertia.js Integration

1. **SPA Experience**: Single-page application feel without API complexity
2. **Shared State**: Kinde authentication data available on all pages
3. **Reactive UI**: Real-time updates when authentication state changes
4. **Framework Agnostic**: Works with Vue, React, or Svelte
5. **Laravel Backend**: Keep all business logic in Laravel
6. **Type Safety**: Full TypeScript support with proper typing

## Migration from Blade

If you're migrating from Blade templates to Inertia.js:

1. **Install Inertia**: `composer require inertiajs/inertia-laravel`
2. **Update Routes**: Replace `view()` with `Inertia::render()`
3. **Create Components**: Convert Blade templates to Vue/React components
4. **Update Navigation**: Use Inertia's `Link` component for navigation
5. **Handle Forms**: Use Inertia's form handling for better UX

This approach gives you the best of both worlds: the simplicity of Laravel with the interactivity of modern JavaScript frameworks. 