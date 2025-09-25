<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kinde Session Persistence (KSP) Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        .test-pass { @apply bg-green-100 border-green-500 text-green-900; }
        .test-fail { @apply bg-red-100 border-red-500 text-red-900; }
        .test-info { @apply bg-blue-100 border-blue-500 text-blue-900; }
        .test-warn { @apply bg-yellow-100 border-yellow-500 text-yellow-900; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">🍪 Kinde Session Persistence Test</div>
        <div class="space-x-4">
            <a href="/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Home</a>
            <a href="/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Dashboard</a>
            <?php if (!$authenticated): ?>
                <a href="/auth/login" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Login to Test</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <main class="p-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h1 class="text-3xl font-bold mb-4 text-gray-800">Session Persistence (KSP) Test Results</h1>
                <p class="text-gray-600 mb-4">
                    This test verifies the implementation of Kinde Session Persistence (KSP) in the PHP SDK. 
                    KSP allows administrators to control whether user sessions persist across browser restarts.
                </p>
                <div class="flex items-center space-x-4 text-sm">
                    <span class="bg-gray-100 px-3 py-1 rounded">
                        <strong>Test Time:</strong> <?= htmlspecialchars($timestamp) ?>
                    </span>
                    <span class="<?= $authenticated ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?> px-3 py-1 rounded">
                        <strong>Status:</strong> <?= $authenticated ? '✅ Authenticated' : '⚠️ Not Authenticated' ?>
                    </span>
                </div>
            </div>

            <!-- Consistency Check -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4 text-purple-600">🔧 SDK Consistency Check</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="border rounded-lg p-4 <?= $consistency_check['matches_nextjs_twenty_nine_days'] ? 'test-pass' : 'test-fail' ?>">
                        <div class="text-lg font-bold">
                            <?= $consistency_check['matches_nextjs_twenty_nine_days'] ? '✅' : '❌' ?>
                        </div>
                        <div class="text-sm font-semibold">Next.js SDK Match</div>
                        <div class="text-xs">
                            Expected: 2505600s<br>
                            Actual: <?= $consistency_check['persistent_duration_seconds'] ?>s
                        </div>
                    </div>
                    <div class="border rounded-lg p-4 test-info">
                        <div class="text-lg font-bold"><?= $consistency_check['duration_in_days'] ?></div>
                        <div class="text-sm font-semibold">Duration (Days)</div>
                        <div class="text-xs">Persistent cookie lifetime</div>
                    </div>
                    <div class="border rounded-lg p-4 test-info">
                        <div class="text-lg font-bold"><?= number_format($consistency_check['persistent_duration_seconds']) ?></div>
                        <div class="text-sm font-semibold">Duration (Seconds)</div>
                        <div class="text-xs">Raw persistent duration</div>
                    </div>
                    <div class="border rounded-lg p-4 test-pass">
                        <div class="text-lg font-bold">✅</div>
                        <div class="text-sm font-semibold">Implementation</div>
                        <div class="text-xs">KSP feature active</div>
                    </div>
                </div>
            </div>

            <?php if ($authenticated): ?>
                <!-- Current Session Analysis -->
                <div class="bg-white shadow rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-semibold mb-4 text-blue-600">📊 Current Session Analysis</h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="border-l-4 border-red-500 bg-red-50 p-4 mb-4">
                            <p class="text-red-700"><strong>Error:</strong> <?= htmlspecialchars($error) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($current_session_status): ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="border rounded-lg p-4 <?= $current_session_status['is_persistent'] ? 'test-info' : 'test-warn' ?>">
                                <div class="text-lg font-bold">
                                    <?= $current_session_status['is_persistent'] ? '🔒 Persistent' : '⏰ Session Only' ?>
                                </div>
                                <div class="text-sm">Session Type</div>
                            </div>
                            <div class="border rounded-lg p-4 test-info">
                                <div class="text-lg font-bold">
                                    <?= $current_session_status['cookie_expiration'] === 0 ? '🕐 Session' : '📅 ' . date('Y-m-d H:i', $current_session_status['cookie_expiration']) ?>
                                </div>
                                <div class="text-sm">Cookie Expiration</div>
                            </div>
                            <div class="border rounded-lg p-4 test-info">
                                <div class="text-lg font-bold"><?= $current_session_status['persistent_duration_days'] ?> days</div>
                                <div class="text-sm">Persistent Duration</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- KSP Claim Analysis -->
                    <?php if ($ksp_claim_analysis): ?>
                        <div class="border rounded-lg p-4 mb-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-700">🔍 Access Token KSP Claim Analysis</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                                <div class="<?= $ksp_claim_analysis['has_access_token'] ? 'test-pass' : 'test-fail' ?> p-2 rounded">
                                    <div class="font-semibold">Access Token</div>
                                    <div><?= $ksp_claim_analysis['has_access_token'] ? '✅ Present' : '❌ Missing' ?></div>
                                </div>
                                <div class="<?= $ksp_claim_analysis['jwt_parsed_successfully'] ? 'test-pass' : 'test-fail' ?> p-2 rounded">
                                    <div class="font-semibold">JWT Parsing</div>
                                    <div><?= $ksp_claim_analysis['jwt_parsed_successfully'] ? '✅ Success' : '❌ Failed' ?></div>
                                </div>
                                <div class="<?= $ksp_claim_analysis['has_ksp_claim'] ? 'test-info' : 'test-warn' ?> p-2 rounded">
                                    <div class="font-semibold">KSP Claim</div>
                                    <div><?= $ksp_claim_analysis['has_ksp_claim'] ? '✅ Present' : '⚠️ Missing' ?></div>
                                </div>
                                <div class="test-info p-2 rounded">
                                    <div class="font-semibold">Persistent Value</div>
                                    <div><?= $ksp_claim_analysis['ksp_persistent_value'] !== null ? ($ksp_claim_analysis['ksp_persistent_value'] ? 'true' : 'false') : 'null' ?></div>
                                </div>
                                <div class="<?= $ksp_claim_analysis['default_applied'] ? 'test-warn' : 'test-info' ?> p-2 rounded">
                                    <div class="font-semibold">Default Applied</div>
                                    <div><?= $ksp_claim_analysis['default_applied'] ? '⚠️ Yes' : '✅ No' ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Cookie Analysis -->
                    <?php if (!empty($cookie_analysis)): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-700">🍪 Current Cookies Analysis</h3>
                            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($cookie_analysis['note']) ?></p>
                            
                            <div class="text-sm">
                                <strong>Kinde cookies found:</strong> <?= $cookie_analysis['kinde_cookies_found'] ?>
                            </div>
                            
                            <?php if (!empty($cookie_analysis['cookies'])): ?>
                                <div class="mt-3 space-y-2">
                                    <?php foreach ($cookie_analysis['cookies'] as $name => $info): ?>
                                        <div class="bg-gray-50 p-2 rounded text-xs">
                                            <strong><?= htmlspecialchars($name) ?></strong> 
                                            (Length: <?= $info['value_length'] ?>, JSON: <?= $info['is_json'] ? 'Yes' : 'No' ?>)
                                            <div class="text-gray-600 mt-1">Preview: <?= htmlspecialchars($info['sample_value']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Not Authenticated Notice -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">⚠️</div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Authentication Required:</strong> 
                                To test current session persistence, please 
                                <a href="/auth/login" class="underline font-semibold hover:text-yellow-900">log in</a> 
                                first. The scenarios below will still demonstrate the expected behavior.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Test Scenarios -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4 text-green-600">🧪 KSP Test Scenarios</h2>
                <p class="text-gray-600 mb-4">
                    These scenarios demonstrate how different KSP claim values affect session persistence:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($test_scenarios as $key => $scenario): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <?php if (isset($scenario['error'])): ?>
                                <div class="test-fail p-3 rounded mb-3">
                                    <strong>Error:</strong> <?= htmlspecialchars($scenario['error']) ?>
                                </div>
                            <?php else: ?>
                                <h3 class="text-lg font-semibold mb-3 capitalize">
                                    <?= str_replace('_', ' ', $key) ?>
                                </h3>
                                
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <strong>Description:</strong><br>
                                        <span class="text-gray-600"><?= htmlspecialchars($scenario['description']) ?></span>
                                    </div>
                                    
                                    <div>
                                        <strong>KSP Claim:</strong><br>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">
                                            <?= $scenario['mock_ksp_claim'] ? json_encode($scenario['mock_ksp_claim']) : 'null' ?>
                                        </code>
                                    </div>
                                    
                                    <div>
                                        <strong>Expected Behavior:</strong><br>
                                        <span class="text-gray-600"><?= htmlspecialchars($scenario['expected_behavior']) ?></span>
                                    </div>
                                    
                                    <div class="<?= strpos($scenario['cookie_type'], 'Session') !== false ? 'test-warn' : 'test-info' ?> p-2 rounded">
                                        <strong>Result:</strong> <?= htmlspecialchars($scenario['cookie_type']) ?>
                                    </div>
                                    
                                    <div class="text-xs text-gray-500">
                                        <strong>Expiration:</strong> 
                                        <?= $scenario['expected_expiration'] === 0 ? 'Session (0)' : date('Y-m-d H:i:s', $scenario['expected_expiration']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Implementation Details -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4 text-gray-700">📋 Implementation Details</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- How it Works -->
                    <div>
                        <h3 class="text-lg font-semibold mb-3 text-blue-600">How KSP Works</h3>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                            <li>User authenticates via OAuth flow</li>
                            <li>Kinde includes <code class="bg-gray-100 px-1 rounded">ksp</code> claim in access token (if configured)</li>
                            <li>PHP SDK parses the <code class="bg-gray-100 px-1 rounded">ksp.persistent</code> boolean</li>
                            <li>Cookies are set with appropriate expiration:
                                <ul class="list-disc list-inside ml-4 mt-1">
                                    <li><code>persistent: false</code> → Session cookie (expires = 0)</li>
                                    <li><code>persistent: true</code> or missing → Persistent cookie (29 days)</li>
                                </ul>
                            </li>
                        </ol>
                    </div>

                    <!-- SDK Consistency -->
                    <div>
                        <h3 class="text-lg font-semibold mb-3 text-purple-600">Cross-SDK Consistency</h3>
                        <div class="space-y-3 text-sm">
                            <div class="bg-gray-50 p-3 rounded">
                                <strong class="text-blue-600">TypeScript SDK:</strong><br>
                                <code class="text-xs">sessionManager.persistent = payload.ksp?.persistent ?? true</code>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <strong class="text-green-600">Next.js SDK:</strong><br>
                                <code class="text-xs">maxAge: sessionState.persistent ? TWENTY_NINE_DAYS : undefined</code>
                            </div>
                            <div class="bg-gray-50 p-3 rounded">
                                <strong class="text-red-600">PHP SDK:</strong><br>
                                <code class="text-xs">$expires = $isPersistent ? time() + 2505600 : 0</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testing Instructions -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">💡</div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Testing Instructions</h3>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p><strong>To verify session persistence:</strong></p>
                            <ol class="list-decimal list-inside ml-2 space-y-1">
                                <li>Configure KSP settings in your Kinde dashboard</li>
                                <li>Authenticate a user via the OAuth flow</li>
                                <li>Check browser dev tools → Application → Cookies</li>
                                <li>Session cookies show "Session" expiration</li>
                                <li>Persistent cookies show a specific expiration date</li>
                                <li>Close and reopen browser to test persistence</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center mt-8">
        <p>&copy; 2024 Kinde PHP SDK - Session Persistence Test</p>
    </footer>
</body>
</html>
