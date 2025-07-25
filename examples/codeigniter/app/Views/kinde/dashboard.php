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
            <a href="/auth/logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</a>
        </div>
    </nav>
    <main class="flex-1 flex flex-col items-center justify-center p-8">
        <div class="bg-white shadow rounded p-6 w-full max-w-md">
            <h2 class="text-2xl font-semibold mb-4">Welcome to your Dashboard</h2>
            <?php if (isset($user)): ?>
                <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')) ?></div>
                <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></div>
                <?php if (!empty($user['picture'])): ?>
                    <div class="mb-2"><img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile Picture" class="w-16 h-16 rounded-full"></div>
                <?php endif; ?>
                <div class="mt-4">
                    <a href="/auth/portal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Go to Portal</a>
                </div>
            <?php else: ?>
                <div class="mb-2 text-red-600">User information not available.</div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 