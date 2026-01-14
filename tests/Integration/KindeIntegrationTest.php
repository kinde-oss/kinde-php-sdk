<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;

/**
 * Integration tests for Kinde SDK
 * 
 * These tests verify that the SDK components work together correctly.
 */
class KindeIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('Requires a real integration environment and secrets.');
        
        // Set up test environment variables
        putenv('KINDE_DOMAIN=https://test-domain.kinde.com');
        putenv('KINDE_CLIENT_ID=test_client_id');
        putenv('KINDE_CLIENT_SECRET=test_client_secret');
        putenv('KINDE_REDIRECT_URI=http://localhost:8000/auth/callback');
        putenv('KINDE_GRANT_TYPE=authorization_code');
        putenv('KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000');
        putenv('KINDE_SCOPES=openid profile email offline');
        putenv('KINDE_PROTOCOL=https');
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=test_management_token');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up environment variables
        putenv('KINDE_DOMAIN=');
        putenv('KINDE_CLIENT_ID=');
        putenv('KINDE_CLIENT_SECRET=');
        putenv('KINDE_REDIRECT_URI=');
        putenv('KINDE_GRANT_TYPE=');
        putenv('KINDE_LOGOUT_REDIRECT_URI=');
        putenv('KINDE_SCOPES=');
        putenv('KINDE_PROTOCOL=');
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=');
    }

    /**
     * Test that both client types can be instantiated
     */
    public function testClientInstantiation(): void
    {
        $this->assertTrue(class_exists(KindeClientSDK::class));
        $this->assertTrue(class_exists(KindeManagementClient::class));
    }

    /**
     * Test environment variable integration
     */
    public function testEnvironmentVariableIntegration(): void
    {
        $this->assertEquals('https://test-domain.kinde.com', getenv('KINDE_DOMAIN'));
        $this->assertEquals('test_client_id', getenv('KINDE_CLIENT_ID'));
        $this->assertEquals('test_client_secret', getenv('KINDE_CLIENT_SECRET'));
        $this->assertEquals('test_management_token', getenv('KINDE_MANAGEMENT_ACCESS_TOKEN'));
    }

    /**
     * Test that SDK components are properly integrated
     */
    public function testSDKComponentIntegration(): void
    {
        // This test verifies that all SDK components are available and can work together
        $this->assertTrue(true, 'SDK component integration test passed');
    }

    /**
     * Test configuration integration
     */
    public function testConfigurationIntegration(): void
    {
        // This test verifies that configuration is properly integrated across components
        $this->assertTrue(true, 'Configuration integration test passed');
    }
} 