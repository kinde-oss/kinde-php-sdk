<?php

namespace Kinde\KindeSDK\Tests\Framework\Laravel;

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Kinde Laravel Service Provider Integration
 * 
 * Note: Full service provider tests require a Laravel application test harness.
 * These tests verify that the SDK classes required for Laravel integration exist.
 * 
 * @group framework
 * @group laravel
 */
class KindeServiceProviderTest extends TestCase
{
    /**
     * Test that the service provider file exists.
     * We can't test class_exists because it requires Laravel's ServiceProvider.
     */
    public function testServiceProviderFileExists(): void
    {
        $providerPath = __DIR__ . '/../../../lib/Frameworks/Laravel/KindeServiceProvider.php';
        
        $this->assertFileExists(
            $providerPath,
            'KindeServiceProvider.php file should exist'
        );
    }

    /**
     * Test that SDK classes that would be registered are available.
     */
    public function testRequiredSdkClassesAvailable(): void
    {
        $this->assertTrue(class_exists(KindeClientSDK::class));
        $this->assertTrue(class_exists(KindeManagementClient::class));
    }
}
