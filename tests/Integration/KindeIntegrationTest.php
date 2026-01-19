<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Sdk\Enums\GrantType;

/**
 * Integration tests for Kinde SDK
 * 
 * These tests verify that the SDK components work together correctly.
 * Full integration tests with real API calls require proper credentials
 * and should be run separately with the @group integration annotation.
 * 
 * @group integration
 */
class KindeIntegrationTest extends TestCase
{
    /**
     * Test that both client classes are available and can be instantiated.
     */
    public function testClientClassesAvailable(): void
    {
        $this->assertTrue(
            class_exists(KindeClientSDK::class),
            'KindeClientSDK class should exist'
        );
        $this->assertTrue(
            class_exists(KindeManagementClient::class),
            'KindeManagementClient class should exist'
        );
    }

    /**
     * Test that grant type enum values are available.
     */
    public function testGrantTypeEnumAvailable(): void
    {
        $this->assertEquals('authorization_code', GrantType::authorizationCode);
        $this->assertEquals('client_credentials', GrantType::clientCredentials);
        $this->assertEquals('authorization_code_flow_pkce', GrantType::PKCE);
    }

    /**
     * Test SDK instantiation with test configuration.
     * This verifies the constructor works without making API calls.
     */
    public function testSdkInstantiationWithTestConfig(): void
    {
        $client = new KindeClientSDK(
            'https://test.kinde.com',
            'http://localhost:8000/callback',
            'test_client_id',
            'test_client_secret',
            GrantType::authorizationCode,
            'http://localhost:8000'
        );

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals('https://test.kinde.com', $client->domain);
        $this->assertEquals('test_client_id', $client->clientId);
    }

    /**
     * Test management client instantiation with test configuration.
     * This verifies the constructor works when an access token is provided.
     */
    public function testManagementClientInstantiationWithTestConfig(): void
    {
        $management = new KindeManagementClient(
            'https://test.kinde.com',
            'test_client_id',
            'test_client_secret',
            'test_access_token'
        );

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals('https://test.kinde.com', $management->getDomain());
        $this->assertEquals('test_client_id', $management->getClientId());
    }
}
