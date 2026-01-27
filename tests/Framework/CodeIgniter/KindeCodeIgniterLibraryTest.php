<?php

namespace Kinde\KindeSDK\Tests\Framework\CodeIgniter;

use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Sdk\OAuth2\PKCE;
use Kinde\KindeSDK\Sdk\OAuth2\ClientCredentials;

/**
 * Test class for Kinde CodeIgniter Framework Integration
 * 
 * Note: Full library tests require a CodeIgniter application test harness.
 * These tests verify that the SDK classes required for CodeIgniter integration exist.
 * 
 * @group framework
 * @group codeigniter
 */
class KindeCodeIgniterLibraryTest extends TestCase
{
    /**
     * Test that OAuth2 classes required for CodeIgniter integration are available.
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
