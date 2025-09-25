<?php
/**
 * Session Persistence Example for Kinde PHP SDK
 * 
 * This example demonstrates how the Kinde PHP SDK handles session persistence
 * based on the KSP (Kinde Session Persistence) claim in access tokens.
 */

require_once '../lib/KindeClientSDK.php';
require_once '../lib/Sdk/Storage/Storage.php';

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Storage\Storage;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Kinde PHP SDK - Session Persistence Example</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 4px; }
        .highlight { background: #fff3cd; padding: 2px 4px; border-radius: 2px; }
    </style>
</head>
<body>
    <h1>Kinde PHP SDK - Session Persistence</h1>
    
    <h2>Overview</h2>
    <p>The Kinde PHP SDK now supports session persistence based on the <code>ksp</code> claim in access tokens. This feature allows Kinde administrators to control whether user sessions persist across browser restarts.</p>
    
    <h2>How it Works</h2>
    <ol>
        <li>When a user authenticates, Kinde may include a <code>ksp</code> claim in the access token</li>
        <li>The <code>ksp</code> claim contains a <code>persistent</code> boolean property</li>
        <li>The PHP SDK automatically detects this claim and sets cookie expiration accordingly:</li>
        <ul>
            <li><strong>persistent: true</strong> or <strong>no ksp claim</strong> → Cookies expire in 29 days</li>
            <li><strong>persistent: false</strong> → Cookies expire when browser closes (session cookies)</li>
        </ul>
    </ol>
    
    <h2>Implementation Details</h2>
    
    <h3>Automatic Detection</h3>
    <div class="code">
<?php
// Example JWT payload structure
$examplePayload = [
    'sub' => '1234567890',
    'name' => 'John Doe',
    'ksp' => [
        'persistent' => false  // This controls session persistence
    ],
    'exp' => time() + 3600
];

echo "// Example access token payload:\n";
echo json_encode($examplePayload, JSON_PRETTY_PRINT);
?>
    </div>
    
    <h3>Cookie Behavior</h3>
    <div class="code">
// When persistent = false:
setcookie('kinde_token', $tokenValue, [
    'expires' => 0,  // Session cookie - expires when browser closes
    'path' => '/',
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Lax'
]);

// When persistent = true (or no ksp claim):
setcookie('kinde_token', $tokenValue, [
    'expires' => time() + 2505600,  // 29 days (matches Next.js SDK)
    'path' => '/',
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Lax'
]);
    </div>
    
    <h3>New Methods Available</h3>
    <div class="code">
// Check if current session should be persistent
$isPersistent = Storage::isSessionPersistent();

// Get appropriate cookie expiration time
$expiration = Storage::getCookieExpiration();

// Get/set persistent cookie duration (default: 29 days)
$duration = Storage::getPersistentCookieDuration();
Storage::setPersistentCookieDuration(3600 * 24 * 30); // 30 days
    </div>
    
    <h2>Usage in Your Application</h2>
    <p><span class="highlight">No changes required!</span> The session persistence is handled automatically when:</p>
    <ul>
        <li>User completes OAuth authentication</li>
        <li>Access tokens are refreshed</li>
        <li>Any token storage operation occurs</li>
    </ul>
    
    <div class="code">
// Your existing code works unchanged:
$client = new KindeClientSDK($domain, $redirectUri, $clientId, $clientSecret, $logoutRedirectUri);

// Authentication flow automatically handles session persistence
if ($client->isAuthenticated()) {
    echo "User is authenticated!";
    // Session cookies will respect KSP setting
} else {
    // Redirect to login
    header('Location: ' . $client->login());
    exit;
}
    </div>
    
    <h2>Consistency Across SDKs</h2>
    <p>This implementation follows the same patterns as other Kinde SDKs:</p>
    <ul>
        <li><strong>TypeScript SDK:</strong> <code>sessionManager.persistent = payload.ksp?.persistent ?? true</code></li>
        <li><strong>Next.js SDK:</strong> <code>maxAge: sessionState.persistent ? TWENTY_NINE_DAYS : undefined</code></li>
        <li><strong>PHP SDK:</strong> <code>expires: $isPersistent ? time() + 29days : 0</code></li>
    </ul>
    
    <h2>Configuration</h2>
    <p>Session persistence is configured in your Kinde dashboard or via workflows. When enabled:</p>
    <ul>
        <li>Users will see their sessions persist across browser restarts</li>
        <li>When disabled, users must re-authenticate after closing their browser</li>
    </ul>
    
    <div class="code">
<?php
// Current session status (example)
if (class_exists('Kinde\KindeSDK\Sdk\Storage\Storage')) {
    echo "// Current persistent cookie duration: " . Storage::getPersistentCookieDuration() . " seconds\n";
    echo "// This equals: " . (Storage::getPersistentCookieDuration() / (24 * 3600)) . " days\n";
}
?>
    </div>

</body>
</html>
