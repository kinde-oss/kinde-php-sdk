<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kinde Auth Example - CodeIgniter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold text-gray-800">Kinde Auth Example</div>
        <div class="space-x-4">
            <?php if (isset($isAuthenticated) && $isAuthenticated): ?>
                <span class="text-gray-700">Welcome, <?= htmlspecialchars($user['given_name'] ?? 'User') ?>!</span>
                <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
            <?php else: ?>
                <a href="/auth/login" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Login</a>
                <a href="/auth/register" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="flex-1 flex flex-col items-center justify-center p-8">
        <?php if (isset($isAuthenticated) && $isAuthenticated): ?>
            <div class="bg-white shadow rounded p-6 w-full max-w-md">
                <h2 class="text-2xl font-semibold mb-4">Dashboard</h2>
                <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')) ?></div>
                <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></div>
                <?php if (!empty($user['picture'])): ?>
                    <div class="mb-2"><img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile Picture" class="w-16 h-16 rounded-full"></div>
                <?php endif; ?>
                <div class="mt-4">
                    <a href="/auth/user-info" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">View User Info</a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white shadow rounded p-8 w-full max-w-lg text-center">
                <h1 class="text-3xl font-bold mb-4">Welcome to the Kinde Auth Example</h1>
                <p class="text-gray-600 mb-6">Sign in or create an account to access your dashboard and try Kinde authentication in CodeIgniter.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/auth/login" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded text-lg">Sign In</a>
                    <a href="/auth/register" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded text-lg">Create Account</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
