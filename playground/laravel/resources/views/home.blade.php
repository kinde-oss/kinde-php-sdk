<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Kinde Integration</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Kinde Todo App
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if($isAuthenticated)
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Welcome, {{ $user['given_name'] ?? 'User' }}!
                            </span>
                            <a href="/auth/logout" 
                               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Logout
                            </a>
                        @else
                            <a href="/auth/login" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Login
                            </a>
                            <a href="/auth/register" 
                               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Register
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            @if($isAuthenticated)
                <!-- Authenticated User Dashboard -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Welcome to Your Dashboard
                        </h2>
                        
                        <!-- User Information -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-white mb-3">User Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Name</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $user['given_name'] ?? 'N/A' }} {{ $user['family_name'] ?? '' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $user['email'] ?? 'N/A' }}
                                    </p>
                                </div>
                                @if(isset($user['picture']))
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Profile Picture</p>
                                    <img src="{{ $user['picture'] }}" alt="Profile" class="w-10 h-10 rounded-full">
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Permissions -->
                        @if(!empty($permissions))
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-white mb-3">Your Permissions</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $permission }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Organization -->
                        @if($organization)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-white mb-3">Organization</h3>
                            <p class="text-sm text-gray-900 dark:text-white">
                                {{ $organization['name'] ?? 'N/A' }}
                            </p>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex space-x-4">
                            <a href="/auth/portal" 
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Go to Portal
                            </a>
                            <a href="/auth/user-info" 
                               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                View User Info
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Landing Page for Unauthenticated Users -->
                <div class="text-center">
                    <div class="max-w-3xl mx-auto">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">
                            Welcome to Kinde Todo App
                        </h1>
                        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
                            A simple Laravel application demonstrating Kinde authentication integration.
                        </p>
                        
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                                Get Started
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-8">
                                Sign in or create an account to access your personalized dashboard and manage your todos.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="/auth/login" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md text-lg font-medium transition duration-200">
                                    Sign In
                                </a>
                                <a href="/auth/register" 
                                   class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-md text-lg font-medium transition duration-200">
                                    Create Account
                                </a>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="text-center">
                                <div class="bg-blue-100 dark:bg-blue-900 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Secure Authentication</h3>
                                <p class="text-gray-600 dark:text-gray-400">Powered by Kinde's secure authentication system</p>
                            </div>
                            
                            <div class="text-center">
                                <div class="bg-green-100 dark:bg-green-900 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">User Management</h3>
                                <p class="text-gray-600 dark:text-gray-400">Manage your profile and permissions easily</p>
                            </div>
                            
                            <div class="text-center">
                                <div class="bg-purple-100 dark:bg-purple-900 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Fast & Modern</h3>
                                <p class="text-gray-600 dark:text-gray-400">Built with Laravel and modern web technologies</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </main>
    </div>
</body>
</html> 