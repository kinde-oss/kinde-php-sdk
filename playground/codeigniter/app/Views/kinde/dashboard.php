<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kinde Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">Kinde Dashboard</div>
        <div class="space-x-4">
            <a href="/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Home</a>
            <a href="/auth/portal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Portal</a>
            <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </nav>
    
    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Welcome to your Dashboard</h2>
                <?php if (isset($user)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')) ?></div>
                            <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></div>
                            <?php if (!empty($user['picture'])): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile Picture" class="w-16 h-16 rounded-full">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="mb-2"><strong>Permissions:</strong> <?= count($permissions) ?> found</div>
                            <?php if (isset($organization['name'])): ?>
                                <div class="mb-2"><strong>Organization:</strong> <?= htmlspecialchars($organization['name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mb-2 text-red-600">User information not available.</div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- User Management -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-blue-600">👤 User Management</h3>
                    <div class="space-y-2">
                        <a href="/auth/user-info" class="block text-blue-600 hover:text-blue-800">View User Info</a>
                        <a href="/auth/portal" class="block text-blue-600 hover:text-blue-800">Go to Portal</a>
                        <a href="/api/user-profile" class="block text-blue-600 hover:text-blue-800">Get User Profile (API)</a>
                    </div>
                </div>

                <!-- Management API Testing -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-purple-600">⚙️ Management API</h3>
                    <div class="space-y-2">
                        <a href="/test-management-api" class="block text-purple-600 hover:text-purple-800 font-semibold">Test All APIs</a>
                        <a href="/api/users" class="block text-purple-600 hover:text-purple-800">List Users</a>
                        <a href="/api/organizations" class="block text-purple-600 hover:text-purple-800">List Organizations</a>
                    </div>
                </div>

                <!-- Session Persistence Testing -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-indigo-600">🍪 Session Persistence</h3>
                    <div class="space-y-2">
                        <a href="/test-session-persistence" class="block text-indigo-600 hover:text-indigo-800 font-semibold">Test KSP Feature</a>
                        <div class="text-xs text-gray-600">
                            Verify session persistence implementation
                        </div>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-green-600">🔗 API Endpoints</h3>
                    <div class="space-y-2">
                        <a href="/api/applications" class="block text-green-600 hover:text-green-800">Applications</a>
                        <a href="/api/roles" class="block text-green-600 hover:text-green-800">Roles</a>
                        <a href="/api/permissions" class="block text-green-600 hover:text-green-800">Permissions</a>
                    </div>
                </div>

                <!-- Feature Flags -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-orange-600">🚩 Feature Flags</h3>
                    <div class="space-y-2">
                        <a href="/api/feature-flags" class="block text-orange-600 hover:text-orange-800">Feature Flags</a>
                        <a href="/api/environment" class="block text-orange-600 hover:text-orange-800">Environment</a>
                    </div>
                </div>

                <!-- Advanced Testing -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">🔧 Advanced Testing</h3>
                    <div class="space-y-2">
                        <a href="/api/test-endpoint?endpoint=users&action=list" class="block text-red-600 hover:text-red-800">Test Specific Endpoint</a>
                        <a href="/api/bulk-create-users" class="block text-red-600 hover:text-red-800">Bulk Create Users</a>
                        <a href="/admin" class="block text-red-600 hover:text-red-800">Admin Area</a>
                    </div>
                </div>

                <!-- Organization Management -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-indigo-600">🏢 Organizations</h3>
                    <div class="space-y-2">
                        <a href="/auth/create-org" class="block text-indigo-600 hover:text-indigo-800">Create Organization</a>
                        <a href="/api/organizations" class="block text-indigo-600 hover:text-indigo-800">List Organizations</a>
                        <a href="/api/create-organization" class="block text-indigo-600 hover:text-indigo-800">Create Org (API)</a>
                    </div>
                </div>
            </div>

            <!-- Permissions Display -->
            <?php if (!empty($permissions)): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4">🔑 Your Permissions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        <?php foreach ($permissions as $permission): ?>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm"><?= htmlspecialchars($permission) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Organization Info -->
            <?php if (isset($organization)): ?>
                <div class="bg-white shadow rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold mb-4">🏢 Organization Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Name:</strong> <?= htmlspecialchars($organization['name'] ?? 'N/A') ?></p>
                            <p><strong>ID:</strong> <?= htmlspecialchars($organization['id'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <p><strong>Code:</strong> <?= htmlspecialchars($organization['code'] ?? 'N/A') ?></p>
                            <p><strong>Type:</strong> <?= htmlspecialchars($organization['type'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= count($permissions) ?></div>
                    <div class="text-gray-600">Permissions</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-2xl font-bold text-green-600"><?= isset($organization) ? '1' : '0' ?></div>
                    <div class="text-gray-600">Organizations</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-2xl font-bold text-purple-600">15+</div>
                    <div class="text-gray-600">API Endpoints</div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="text-2xl font-bold text-orange-600">100%</div>
                    <div class="text-gray-600">Test Coverage</div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center">
        <p>&copy; 2024 Kinde PHP SDK - CodeIgniter Playground</p>
    </footer>
</body>
</html> 