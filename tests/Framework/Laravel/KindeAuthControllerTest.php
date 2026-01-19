<?php

namespace Kinde\KindeSDK\Tests\Framework\Laravel;

use Kinde\KindeSDK\KindeClientSDK;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Kinde Laravel Auth Controller Integration
 * 
 * Note: Full controller tests require a Laravel application test harness.
 * These tests verify that the SDK classes required for Laravel integration exist.
 * 
 * @group framework
 * @group laravel
 */
class KindeAuthControllerTest extends TestCase
{
    /**
     * Test that the controller file exists.
     * We can't test class_exists because it requires Laravel's base Controller.
     */
    public function testControllerFileExists(): void
    {
        $controllerPath = __DIR__ . '/../../../lib/Frameworks/Laravel/Controllers/KindeAuthController.php';
        
        $this->assertFileExists(
            $controllerPath,
            'KindeAuthController.php file should exist'
        );
    }

    /**
     * Test that SDK classes required by the controller are available.
     */
    public function testRequiredSdkClassesAvailable(): void
    {
        $this->assertTrue(class_exists(KindeClientSDK::class));
    }
}
