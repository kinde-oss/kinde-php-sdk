<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kinde PHP SDK - CodeIgniter Playground</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">Kinde PHP SDK - CodeIgniter Playground</div>
        <div class="space-x-4">
            <?php if ($isAuthenticated): ?>
                <a href="/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Dashboard</a>
                <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
            <?php else: ?>
                <a href="/auth/login" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Login</a>
                <a href="/auth/register" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Kinde PHP SDK Playground</h1>
                <p class="text-xl text-gray-600 mb-8">Test and explore Kinde authentication and management APIs with CodeIgniter</p>
                
                <?php if (!$isAuthenticated): ?>
                    <div class="space-x-4">
                        <a href="/auth/login" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-lg font-semibold">Get Started - Login</a>
                        <a href="/auth/register" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-lg font-semibold">Create Account</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <!-- Authentication Features -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-blue-600 text-2xl mb-4">🔐</div>
                    <h3 class="text-xl font-semibold mb-3">Authentication</h3>
                    <p class="text-gray-600 mb-4">Test Kinde authentication flows including login, registration, and organization creation.</p>
                    <div class="space-y-2">
                        <a href="/auth/login" class="block text-blue-600 hover:text-blue-800">Login</a>
                        <a href="/auth/register" class="block text-blue-600 hover:text-blue-800">Register</a>
                        <a href="/auth/create-org" class="block text-blue-600 hover:text-blue-800">Create Organization</a>
                    </div>
                </div>

                <!-- User Management -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-green-600 text-2xl mb-4">👤</div>
                    <h3 class="text-xl font-semibold mb-3">User Management</h3>
                    <p class="text-gray-600 mb-4">Manage users, view profiles, and test user-related API endpoints.</p>
                    <div class="space-y-2">
                        <a href="/dashboard" class="block text-green-600 hover:text-green-800">User Dashboard</a>
                        <a href="/auth/user-info" class="block text-green-600 hover:text-green-800">User Info</a>
                        <a href="/auth/portal" class="block text-green-600 hover:text-green-800">User Portal</a>
                    </div>
                </div>

                <!-- Management API Testing -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-purple-600 text-2xl mb-4">⚙️</div>
                    <h3 class="text-xl font-semibold mb-3">Management API</h3>
                    <p class="text-gray-600 mb-4">Comprehensive testing of all Kinde Management API endpoints with detailed reporting.</p>
                    <div class="space-y-2">
                        <a href="/test-management-api" class="block text-purple-600 hover:text-purple-800 font-semibold">Test Management API</a>
                        <a href="/api/users" class="block text-purple-600 hover:text-purple-800">List Users</a>
                        <a href="/api/organizations" class="block text-purple-600 hover:text-purple-800">List Organizations</a>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-orange-600 text-2xl mb-4">🔗</div>
                    <h3 class="text-xl font-semibold mb-3">API Endpoints</h3>
                    <p class="text-gray-600 mb-4">Direct access to various API endpoints for testing and development.</p>
                    <div class="space-y-2">
                        <a href="/api/applications" class="block text-orange-600 hover:text-orange-800">Applications</a>
                        <a href="/api/roles" class="block text-orange-600 hover:text-orange-800">Roles</a>
                        <a href="/api/permissions" class="block text-orange-600 hover:text-orange-800">Permissions</a>
                    </div>
                </div>

                <!-- Feature Flags -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-indigo-600 text-2xl mb-4">🚩</div>
                    <h3 class="text-xl font-semibold mb-3">Feature Flags</h3>
                    <p class="text-gray-600 mb-4">Test feature flag functionality and environment-specific configurations.</p>
                    <div class="space-y-2">
                        <a href="/api/feature-flags" class="block text-indigo-600 hover:text-indigo-800">Feature Flags</a>
                        <a href="/api/environment" class="block text-indigo-600 hover:text-indigo-800">Environment</a>
                    </div>
                </div>

                <!-- Session Persistence Testing -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-cyan-600 text-2xl mb-4">🍪</div>
                    <h3 class="text-xl font-semibold mb-3">Session Persistence</h3>
                    <p class="text-gray-600 mb-4">Test the new KSP (Kinde Session Persistence) feature implementation and cookie behavior.</p>
                    <div class="space-y-2">
                        <a href="/test-session-persistence" class="block text-cyan-600 hover:text-cyan-800 font-semibold">Test KSP Feature</a>
                        <div class="text-xs text-gray-500">Works with or without authentication</div>
                    </div>
                </div>

                <!-- Advanced Features -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-red-600 text-2xl mb-4">🔧</div>
                    <h3 class="text-xl font-semibold mb-3">Advanced Features</h3>
                    <p class="text-gray-600 mb-4">Advanced testing features including bulk operations and specific endpoint testing.</p>
                    <div class="space-y-2">
                        <a href="/api/test-endpoint?endpoint=users&action=list" class="block text-red-600 hover:text-red-800">Test Specific Endpoint</a>
                        <a href="/admin" class="block text-red-600 hover:text-red-800">Admin Area</a>
                    </div>
                </div>
            </div>

            <!-- Status Section -->
            <?php if ($isAuthenticated): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold text-green-800 mb-3">✅ Authentication Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-green-700"><strong>Status:</strong> Authenticated</p>
                            <?php if (isset($user['email'])): ?>
                                <p class="text-green-700"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-green-700"><strong>Permissions:</strong> <?= count($permissions) ?> found</p>
                            <?php if (isset($organization['name'])): ?>
                                <p class="text-green-700"><strong>Organization:</strong> <?= htmlspecialchars($organization['name']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-3">⚠️ Authentication Required</h3>
                    <p class="text-yellow-700">Please log in to access the full range of features and test the Management API endpoints.</p>
                </div>
            <?php endif; ?>

            <!-- Quick Start Guide -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Start Guide</h3>
                <div class="space-y-3 text-gray-700">
                    <p><strong>1.</strong> Start by logging in or creating an account to test authentication flows</p>
                    <p><strong>2.</strong> Explore the user dashboard to see your profile and permissions</p>
                    <p><strong>3.</strong> Test the new Session Persistence (KSP) feature to verify cookie behavior</p>
                    <p><strong>4.</strong> Test the Management API dashboard to verify all endpoints are working</p>
                    <p><strong>5.</strong> Use individual API endpoints for specific testing scenarios</p>
                    <p><strong>6.</strong> Check the header fix status to ensure API calls are working correctly</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-6 text-center">
        <p>&copy; 2024 Kinde PHP SDK - CodeIgniter Playground</p>
    </footer>
</body>
</html> 