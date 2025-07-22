<?php

/**
 * Kinde Management Client Example
 * 
 * This example demonstrates how to use the KindeManagementClient
 * for server-to-server operations.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\ApiException;

// Load environment variables (you can use .env file or set them directly)
// $_ENV['KINDE_DOMAIN'] = 'https://your-domain.kinde.com';
// $_ENV['KINDE_CLIENT_ID'] = 'your_client_id';
// $_ENV['KINDE_CLIENT_SECRET'] = 'your_client_secret';

try {
    // Create management client from environment variables (recommended)
    $management = KindeManagementClient::createFromEnv();
    
    // Alternative initialization methods:
    // $management = new KindeManagementClient(); // Same as createFromEnv()
    // $management = new KindeManagementClient('https://custom-domain.kinde.com'); // Override domain
    // $management = new KindeManagementClient(null, null, null, 'custom_token'); // Override token
    
    echo "✅ Management client created successfully\n";
    echo "Domain: " . $management->getDomain() . "\n";
    echo "Client ID: " . $management->getClientId() . "\n";
    
    // Example 1: Get users
    echo "\n📋 Getting users...\n";
    try {
        $users = $management->users->getUsers();
        echo "Found " . count($users->getUsers()) . " users\n";
        
        foreach ($users->getUsers() as $user) {
            echo "- {$user->getGivenName()} {$user->getFamilyName()} ({$user->getEmail()})\n";
        }
    } catch (ApiException $e) {
        echo "❌ Failed to get users: {$e->getMessage()}\n";
    }
    
    // Example 2: Get organizations
    echo "\n🏢 Getting organizations...\n";
    try {
        $organizations = $management->organizations->getOrganizations();
        echo "Found " . count($organizations->getOrganizations()) . " organizations\n";
        
        foreach ($organizations->getOrganizations() as $org) {
            echo "- {$org->getName()} (ID: {$org->getCode()})\n";
        }
    } catch (ApiException $e) {
        echo "❌ Failed to get organizations: {$e->getMessage()}\n";
    }
    
    // Example 3: Get applications
    echo "\n📱 Getting applications...\n";
    try {
        $applications = $management->applications->getApplications();
        echo "Found " . count($applications->getApplications()) . " applications\n";
        
        foreach ($applications->getApplications() as $app) {
            echo "- {$app->getName()} (Type: {$app->getType()})\n";
        }
    } catch (ApiException $e) {
        echo "❌ Failed to get applications: {$e->getMessage()}\n";
    }
    
    // Example 4: Get feature flags
    echo "\n🚩 Getting feature flags...\n";
    try {
        $featureFlags = $management->featureFlags->getEnvironmentFeatureFlags();
        echo "Found " . count($featureFlags->getFeatureFlags()) . " feature flags\n";
        
        foreach ($featureFlags->getFeatureFlags() as $flag) {
            echo "- {$flag->getName()} (Type: {$flag->getType()}, Value: " . ($flag->getValue() ? 'true' : 'false') . ")\n";
        }
    } catch (ApiException $e) {
        echo "❌ Failed to get feature flags: {$e->getMessage()}\n";
    }
    
    // Example 5: Get roles
    echo "\n👥 Getting roles...\n";
    try {
        $roles = $management->roles->getRoles();
        echo "Found " . count($roles->getRoles()) . " roles\n";
        
        foreach ($roles->getRoles() as $role) {
            echo "- {$role->getName()} ({$role->getDescription()})\n";
        }
    } catch (ApiException $e) {
        echo "❌ Failed to get roles: {$e->getMessage()}\n";
    }
    
    // Example 6: Get permissions
    echo "\n🔐 Getting permissions...\n";
    try {
        $permissions = $management->permissions->getPermissions();
        echo "Found " . count($permissions->getPermissions()) . " permissions\n";
        
        foreach ($permissions->getPermissions() as $permission) {
            echo "- {$permission->getName()} ({$permission->getDescription()})\n";
        }
    } catch (ApiException $e) {
        echo "❌ Failed to get permissions: {$e->getMessage()}\n";
    }
    
    echo "\n✅ Management client example completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Make sure you have set the required environment variables:\n";
    echo "- KINDE_DOMAIN\n";
    echo "- KINDE_CLIENT_ID\n";
    echo "- KINDE_CLIENT_SECRET\n";
} 