<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Has Functionality Test Results - Kinde PHP SDK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">🧪 Has Functionality Test Suite</div>
        <div class="space-x-4">
            <a href="/dashboard" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Dashboard</a>
            <button onclick="window.location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">🔄 Refresh Tests</button>
            <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </nav>

    <main class="p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Has Functionality Test Results</h1>
                        <p class="text-gray-600">Comprehensive testing of the new Kinde PHP SDK has functionality</p>
                        <p class="text-sm text-gray-500 mt-2">Tested on: <?= $timestamp ?> | User: <?= htmlspecialchars($user['email'] ?? 'Unknown') ?></p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold <?= $summary['successRate'] >= 90 ? 'text-green-600' : ($summary['successRate'] >= 70 ? 'text-yellow-600' : 'text-red-600') ?>">
                            <?= $summary['successRate'] ?>%
                        </div>
                        <div class="text-sm text-gray-600">Success Rate</div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?= $summary['totalCategories'] ?></div>
                        <div class="text-sm text-gray-600">Test Categories</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600"><?= $summary['successfulCategories'] ?></div>
                        <div class="text-sm text-gray-600">Categories Passed</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600"><?= $summary['totalIndividualTests'] ?></div>
                        <div class="text-sm text-gray-600">Individual Tests</div>
                    </div>
                    <div class="text-center p-4 bg-indigo-50 rounded-lg">
                        <div class="text-2xl font-bold text-indigo-600"><?= $summary['passedIndividualTests'] ?></div>
                        <div class="text-sm text-gray-600">Tests Passed</div>
                    </div>
                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600"><?= $summary['executionTime'] ?>ms</div>
                        <div class="text-sm text-gray-600">Execution Time</div>
                    </div>
                </div>
            </div>

            <!-- Test Categories -->
            <div class="space-y-6">
                <?php foreach ($testResults as $categoryName => $categoryResult): ?>
                    <div class="bg-white shadow rounded-lg overflow-hidden" x-data="{ expanded: <?= $categoryResult['success'] ? 'false' : 'true' ?> }">
                        <!-- Category Header -->
                        <div class="p-6 border-b cursor-pointer" @click="expanded = !expanded">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <?php if ($categoryResult['success']): ?>
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <span class="text-green-600 font-bold">✓</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <span class="text-red-600 font-bold">✗</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            <?= getCategoryTitle($categoryName) ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            <?= $categoryResult['passedCount'] ?? 0 ?>/<?= $categoryResult['testCount'] ?? 0 ?> tests passed
                                            <?php if (isset($categoryResult['error'])): ?>
                                                - Error: <?= htmlspecialchars($categoryResult['error']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php 
                                    $successRate = ($categoryResult['testCount'] ?? 0) > 0 
                                        ? round((($categoryResult['passedCount'] ?? 0) / $categoryResult['testCount']) * 100, 1) 
                                        : 0;
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $successRate >= 90 ? 'bg-green-100 text-green-800' : ($successRate >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= $successRate ?>%
                                    </span>
                                    <span class="transform transition-transform" :class="expanded ? 'rotate-180' : ''">▼</span>
                                </div>
                            </div>
                        </div>

                        <!-- Category Details -->
                        <div x-show="expanded" x-transition class="p-6">
                            <?php if (isset($categoryResult['tests']) && !empty($categoryResult['tests'])): ?>
                                <!-- Individual Test Results -->
                                <div class="space-y-3 mb-6">
                                    <?php foreach ($categoryResult['tests'] as $test): ?>
                                        <div class="flex items-center justify-between p-3 rounded <?= $test['passed'] ? 'bg-green-50' : 'bg-red-50' ?>">
                                            <div class="flex items-center space-x-3">
                                                <span class="<?= $test['passed'] ? 'text-green-600' : 'text-red-600' ?> font-bold">
                                                    <?= $test['passed'] ? '✓' : '✗' ?>
                                                </span>
                                                <span class="font-medium text-gray-800"><?= htmlspecialchars($test['name']) ?></span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600">Expected: <code><?= formatValue($test['expected']) ?></code></div>
                                                <div class="text-sm text-gray-600">Actual: <code><?= formatValue($test['actual']) ?></code></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Additional Data -->
                            <?php if (isset($categoryResult['userRoles']) && !empty($categoryResult['userRoles'])): ?>
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                    <h4 class="font-semibold text-blue-800 mb-2">User Roles (<?= count($categoryResult['userRoles']) ?>)</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($categoryResult['userRoles'] as $role): ?>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                                <?= htmlspecialchars($role['key'] ?? $role) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($categoryResult['userPermissions']) && !empty($categoryResult['userPermissions']['permissions'])): ?>
                                <div class="mt-4 p-4 bg-purple-50 rounded-lg">
                                    <h4 class="font-semibold text-purple-800 mb-2">User Permissions (<?= count($categoryResult['userPermissions']['permissions']) ?>)</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($categoryResult['userPermissions']['permissions'] as $permission): ?>
                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm">
                                                <?= htmlspecialchars($permission) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (isset($categoryResult['userPermissions']['orgCode'])): ?>
                                        <div class="mt-2 text-sm text-gray-600">
                                            Organization: <code><?= htmlspecialchars($categoryResult['userPermissions']['orgCode']) ?></code>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($categoryResult['userEntitlements']) && !empty($categoryResult['userEntitlements'])): ?>
                                <div class="mt-4 p-4 bg-green-50 rounded-lg">
                                    <h4 class="font-semibold text-green-800 mb-2">User Entitlements (<?= count($categoryResult['userEntitlements']) ?>)</h4>
                                    <div class="space-y-2">
                                        <?php foreach ($categoryResult['userEntitlements'] as $entitlement): ?>
                                            <div class="bg-green-100 text-green-800 px-3 py-2 rounded text-sm">
                                                <div class="font-medium"><?= htmlspecialchars($entitlement->getFeatureKey()) ?></div>
                                                <?php if ($entitlement->getEntitlementLimitMax()): ?>
                                                    <div class="text-xs">
                                                        Limit: <?= $entitlement->getEntitlementLimitMax() ?>
                                                        <?php if ($entitlement->getEntitlementLimitUsed()): ?>
                                                            (Used: <?= $entitlement->getEntitlementLimitUsed() ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer Info -->
            <div class="mt-8 bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">🔍 Test Coverage</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-800">Individual Methods:</h4>
                        <ul class="text-gray-600 space-y-1">
                            <li>✓ hasRoles()</li>
                            <li>✓ hasPermissions()</li>
                            <li>✓ hasFeatureFlags()</li>
                            <li>✓ hasBillingEntitlements()</li>
                        </ul>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-800">Advanced Features:</h4>
                        <ul class="text-gray-600 space-y-1">
                            <li>✓ Unified has() method</li>
                            <li>✓ Custom conditions</li>
                            <li>✓ Force API parameters</li>
                            <li>✓ Error handling</li>
                        </ul>
                    </div>
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-800">Quality Assurance:</h4>
                        <ul class="text-gray-600 space-y-1">
                            <li>✓ Edge cases</li>
                            <li>✓ Performance testing</li>
                            <li>✓ Type validation</li>
                            <li>✓ Professional standards</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Code Examples -->
            <div class="mt-8 bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">💻 Code Examples</h3>
                
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Simple Role Check:</h4>
                        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto"><code>$hasAdmin = $kindeClient->hasRoles(['admin']);</code></pre>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Unified Authorization Check:</h4>
                        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto"><code>$authorized = $kindeClient->has([
    'roles' => ['admin', 'manager'],
    'permissions' => ['read:posts', 'write:posts'],
    'featureFlags' => ['advanced_ui'],
    'billingEntitlements' => ['premium']
]);</code></pre>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-800 mb-2">Custom Condition:</h4>
                        <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto"><code>$hasSpecificRole = $kindeClient->hasRoles([
    [
        'role' => 'manager',
        'condition' => function($role) {
            return $role['id'] === 'senior-manager-123';
        }
    ]
]);</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center mt-8">
        <p>&copy; 2024 Kinde PHP SDK - Has Functionality Test Suite</p>
    </footer>
</body>
</html>
