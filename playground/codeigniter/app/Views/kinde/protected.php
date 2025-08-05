<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Protected Route - Kinde PHP SDK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">Protected Route</div>
        <div class="space-x-4">
            <a href="/" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Home</a>
            <a href="/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Dashboard</a>
            <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </nav>
    
    <main class="flex-1 flex items-center justify-center p-8">
        <div class="bg-white shadow rounded-lg p-8 max-w-md w-full">
            <div class="text-center">
                <div class="text-6xl mb-4">🔒</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-4">Protected Route</h1>
                <p class="text-gray-600 mb-6"><?= htmlspecialchars($message) ?></p>
                
                <?php if (isset($user)): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-green-800 mb-2">Authenticated User</h3>
                        <p class="text-green-700"><strong>Name:</strong> <?= htmlspecialchars(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')) ?></p>
                        <p class="text-green-700"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-3">
                    <a href="/dashboard" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Go to Dashboard</a>
                    <a href="/" class="block w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to Home</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html> 