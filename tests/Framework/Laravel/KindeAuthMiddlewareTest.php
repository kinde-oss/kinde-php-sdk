<?php

namespace Kinde\KindeSDK\Tests\Framework\Laravel;

use Kinde\KindeSDK\KindeClientSDK;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Kinde Laravel Middleware Integration
 * 
 * Note: Full middleware tests require a Laravel application test harness.
 * These tests verify that the SDK classes required for Laravel integration exist.
 * 
 * @group framework
 * @group laravel
 */
class KindeAuthMiddlewareTest extends TestCase
{
    /**
     * Test that the middleware file exists.
     * We can't fully test class instantiation without Laravel's Request class.
     */
    public function testMiddlewareFileExists(): void
    {
        $middlewarePath = __DIR__ . '/../../../lib/Frameworks/Laravel/Middleware/KindeAuthMiddleware.php';
        
        $this->assertFileExists(
            $middlewarePath,
            'KindeAuthMiddleware.php file should exist'
        );
    }

    /**
     * Test that SDK classes required by the middleware are available.
     */
    public function testRequiredSdkClassesAvailable(): void
    {
        $this->assertTrue(class_exists(KindeClientSDK::class));
    }
}
