<?php

/**
 * Kinde KSP (Key Storage Provider) Example
 * 
 * This example demonstrates how to add enterprise-grade encryption to your Kinde SDK
 * with just one line of code. Perfect for production applications requiring
 * enhanced security for token storage.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/Sdk/KindeKSP.php';

use Kinde\KindeSDK\Sdk\KindeKSP;

echo "<h1>🔐 Simple KSP Example</h1>\n";

// ============================================================================
// STEP 1: Enable KSP - That's it! One line!
// ============================================================================

echo "<h2>Step 1: Enable KSP</h2>\n";
$enabled = KindeKSP::enable();

if ($enabled) {
    echo "✅ KSP enabled successfully!\n";
    
    // Check the status
    $status = KindeKSP::getStatus();
    echo "<p>📊 Status: " . json_encode($status, JSON_PRETTY_PRINT) . "</p>\n";
} else {
    echo "❌ KSP failed to initialize\n";
    exit(1);
}

// ============================================================================
// STEP 2: Test encryption - Simple API
// ============================================================================

echo "<h2>Step 2: Test Encryption</h2>\n";

$sensitiveData = [
    "User email" => "user@example.com",
    "Session token" => "abc123def456",
    "JWT token" => '{"sub":"user123","exp":1234567890}',
    "Personal info" => "SSN: 123-45-6789, Phone: +1234567890"
];

foreach ($sensitiveData as $label => $data) {
    echo "<h3>{$label}</h3>\n";
    echo "<p><strong>Original:</strong> " . htmlspecialchars($data) . "</p>\n";
    
    // Encrypt
    $encrypted = KindeKSP::encrypt($data);
    echo "<p><strong>Encrypted:</strong> " . htmlspecialchars(substr($encrypted, 0, 60)) . "...</p>\n";
    
    // Decrypt
    $decrypted = KindeKSP::decrypt($encrypted);
    $success = $data === $decrypted;
    
    echo "<p><strong>Result:</strong> " . ($success ? "✅ SUCCESS" : "❌ FAILED") . "</p>\n";
    echo "<hr>\n";
}

// ============================================================================
// STEP 3: Integration with Kinde SDK (Example)
// ============================================================================

echo "<h2>Step 3: Integration Example</h2>\n";
echo "<p>Here's how you would integrate KSP with your existing Kinde SDK:</p>\n";

echo "<pre>\n";
echo "// 1. Enable KSP before initializing Kinde SDK\n";
echo "KindeKSP::enable();\n\n";
echo "// 2. Initialize Kinde SDK as normal\n";
echo "\$client = new KindeClientSDK(\n";
echo "    \$domain,\n";
echo "    \$redirectUri,\n";
echo "    \$clientId,\n";
echo "    \$clientSecret\n";
echo ");\n\n";
echo "// 3. Optionally wrap storage for automatic encryption\n";
echo "\$secureStorage = KindeKSP::wrapStorage(\$client->getStorage());\n";
echo "\$client->setStorage(\$secureStorage);\n\n";
echo "// 4. Use SDK normally - tokens are now encrypted! 🔐\n";
echo "\$client->login();\n";
echo "</pre>\n";

// ============================================================================
// STEP 4: Show the minimal footprint
// ============================================================================

echo "<h2>Step 4: Minimal SDK Impact</h2>\n";
echo "<div style='background:#e8f5e8;padding:15px;border:1px solid #4caf50;'>\n";
echo "<h3>✅ What We Added to Kinde SDK:</h3>\n";
echo "<ul>\n";
echo "<li>📁 <strong>Just 1 file:</strong> <code>lib/Sdk/KindeKSP.php</code></li>\n";
echo "<li>📊 <strong>File size:</strong> ~10KB (including all functionality)</li>\n";
echo "<li>🚀 <strong>Zero dependencies</strong> (uses built-in PHP OpenSSL)</li>\n";
echo "<li>🔒 <strong>Military-grade encryption:</strong> AES-256-GCM</li>\n";
echo "<li>⚡ <strong>High performance:</strong> Minimal overhead</li>\n";
echo "<li>🛡️ <strong>Bulletproof:</strong> Graceful fallbacks, never breaks your app</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>🎉 Done!</h2>\n";
echo "<p>Your Kinde SDK now has enterprise-grade encryption with zero complexity!</p>\n";
?>
