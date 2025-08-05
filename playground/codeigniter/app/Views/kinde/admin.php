<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Area - Kinde PHP SDK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">Admin Area</div>
        <div class="space-x-4">
            <a href="/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Home</a>
            <a href="/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Dashboard</a>
            <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </nav>
    
    <main class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Admin Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg p-8 mb-8">
                <div class="text-center">
                    <div class="text-6xl mb-4">👑</div>
                    <h1 class="text-3xl font-bold mb-2">Admin Area</h1>
                    <p class="text-xl opacity-90"><?= htmlspecialchars($message) ?></p>
                </div>
            </div>

            <!-- Admin User Info -->
            <?php if (isset($user)): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-purple-600">👤 Admin User Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')) ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                            <p class="mb-2"><strong>User ID:</strong> <?= htmlspecialchars($user['id'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <?php if (!empty($user['picture'])): ?>
                                <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile Picture" class="w-20 h-20 rounded-full mb-2">
                            <?php endif; ?>
                            <p class="mb-2"><strong>Email Verified:</strong> <?= isset($user['email_verified']) && $user['email_verified'] ? '✅ Yes' : '❌ No' ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Admin Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Management API Testing -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-purple-600">⚙️ Management API</h3>
                    <div class="space-y-2">
                        <a href="/test-management-api" class="block text-purple-600 hover:text-purple-800 font-semibold">Test All APIs</a>
                        <a href="/api/users" class="block text-purple-600 hover:text-purple-800">Manage Users</a>
                        <a href="/api/organizations" class="block text-purple-600 hover:text-purple-800">Manage Organizations</a>
                    </div>
                </div>

                <!-- Advanced Operations -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">🔧 Advanced Operations</h3>
                    <div class="space-y-2">
                        <a href="/api/bulk-create-users" class="block text-red-600 hover:text-red-800">Bulk Create Users</a>
                        <a href="/api/test-endpoint?endpoint=users&action=list" class="block text-red-600 hover:text-red-800">Test Endpoints</a>
                        <a href="/api/applications" class="block text-red-600 hover:text-red-800">Manage Applications</a>
                    </div>
                </div>

                <!-- System Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-blue-600">📊 System Info</h3>
                    <div class="space-y-2">
                        <a href="/api/environment" class="block text-blue-600 hover:text-blue-800">Environment</a>
                        <a href="/api/business" class="block text-blue-600 hover:text-blue-800">Business Info</a>
                        <a href="/api/feature-flags" class="block text-blue-600 hover:text-blue-800">Feature Flags</a>
                    </div>
                </div>
            </div>

            <!-- Admin Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-purple-600 mb-2">🔐</div>
                    <div class="text-2xl font-bold text-gray-800">Admin</div>
                    <div class="text-gray-600">Access Level</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2">✅</div>
                    <div class="text-2xl font-bold text-gray-800">Verified</div>
                    <div class="text-gray-600">Authentication</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-2">🔑</div>
                    <div class="text-2xl font-bold text-gray-800">Full</div>
                    <div class="text-gray-600">Permissions</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-3xl font-bold text-orange-600 mb-2">⚡</div>
                    <div class="text-2xl font-bold text-gray-800">Active</div>
                    <div class="text-gray-600">Session</div>
                </div>
            </div>

            <!-- Admin Features -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">🚀 Available Admin Features</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>User Management</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>Organization Management</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>Application Management</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>Role & Permission Management</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>Feature Flag Management</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>API Testing Dashboard</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>Bulk Operations</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-500 mr-2">✅</span>
                            <span>System Monitoring</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center">
        <p>&copy; 2024 Kinde PHP SDK - CodeIgniter Playground | Admin Area</p>
    </footer>
</body>
</html> 