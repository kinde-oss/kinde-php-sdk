<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kinde\KindeSDK\KindeClientSDK;

// Initialize the SDK
$kinde = new KindeClientSDK(
    domain: 'https://your-domain.kinde.com',
    clientId: 'your_client_id',
    clientSecret: 'your_client_secret',
    redirectUri: 'http://localhost:8000/callback'
);

try {
    // First, authenticate the user (this would typically happen through the login flow)
    // For this example, we assume the user is already authenticated
    
    // Get all entitlements for the authenticated user
    echo "Getting all entitlements...\n";
    $entitlements = $kinde->getAllEntitlements();
    
    echo "Found " . count($entitlements) . " entitlements:\n";
    foreach ($entitlements as $entitlement) {
        echo "- " . $entitlement->getFeatureName() . " (Key: " . $entitlement->getFeatureKey() . ")\n";
        echo "  Max Limit: " . $entitlement->getEntitlementLimitMax() . "\n";
        echo "  Min Limit: " . $entitlement->getEntitlementLimitMin() . "\n";
        echo "  Unit Amount: " . $entitlement->getUnitAmount() . "\n";
        echo "  Price Name: " . $entitlement->getPriceName() . "\n\n";
    }
    
    // Check if user has a specific entitlement
    $featureKey = 'premium_features';
    if ($kinde->hasEntitlement($featureKey)) {
        echo "User has access to: " . $featureKey . "\n";
        
        // Get the limit for this entitlement
        $limit = $kinde->getEntitlementLimit($featureKey);
        if ($limit !== null) {
            echo "Limit for " . $featureKey . ": " . $limit . "\n";
        }
    } else {
        echo "User does not have access to: " . $featureKey . "\n";
    }
    
    // Get a specific entitlement
    $entitlement = $kinde->getEntitlement('basic_features');
    if ($entitlement) {
        echo "Found entitlement: " . $entitlement->getFeatureName() . "\n";
        echo "Feature Key: " . $entitlement->getFeatureKey() . "\n";
        echo "Max Limit: " . $entitlement->getEntitlementLimitMax() . "\n";
    } else {
        echo "Entitlement 'basic_features' not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 