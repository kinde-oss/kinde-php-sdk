<?php

namespace Kinde\KindeSDK\Tests\Framework\Symfony;

use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Sdk\OAuth2\PKCE;
use Kinde\KindeSDK\Sdk\OAuth2\ClientCredentials;

/**
 * Test class for Kinde Symfony Framework Integration
 * 
 * Note: Full bundle tests require a Symfony application test harness.
 * These tests verify that the SDK classes required for Symfony integration exist.
 * 
 * @group framework
 * @group symfony
 */
class KindeSymfonyBundleTest extends TestCase
{
    /**
     * Test that OAuth2 classes required for Symfony integration are available.
     */
    public function testOAuth2ClassesAvailable(): void
    {
        $this->assertTrue(
            class_exists(AuthorizationCode::class),
            'AuthorizationCode class should exist'
        );
        $this->assertTrue(
            class_exists(PKCE::class),
            'PKCE class should exist'
        );
        $this->assertTrue(
            class_exists(ClientCredentials::class),
            'ClientCredentials class should exist'
        );
    }

    /**
     * Test that the main SDK client class is available.
     */
    public function testSdkClientClassAvailable(): void
    {
        $this->assertTrue(class_exists(KindeClientSDK::class));
    }
}
