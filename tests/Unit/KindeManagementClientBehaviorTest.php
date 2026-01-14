<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use Kinde\KindeSDK\KindeManagementClient;
use PHPUnit\Framework\TestCase;

class KindeManagementClientBehaviorTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        putenv('KINDE_DOMAIN');
        putenv('KINDE_CLIENT_ID');
        putenv('KINDE_CLIENT_SECRET');
        putenv('KINDE_MANAGEMENT_CLIENT_ID');
        putenv('KINDE_MANAGEMENT_CLIENT_SECRET');
    }

    public function testIsConfiguredForM2MReturnsTrueWhenTokenAvailable(): void
    {
        $client = new TestableManagementClient(
            'https://test-domain.kinde.com',
            'client_id',
            'client_secret'
        );
        $client->tokenResult = 'access_token';

        $this->assertTrue($client->isConfiguredForM2M());
    }

    public function testIsConfiguredForM2MReturnsFalseWhenTokenFails(): void
    {
        $client = new TestableManagementClient(
            'https://test-domain.kinde.com',
            'client_id',
            'client_secret'
        );
        $client->throwOnAccessToken = true;

        $this->assertFalse($client->isConfiguredForM2M());
    }

    public function testGetConfigurationStatusAddsM2MRecommendationsOnFailure(): void
    {
        $client = new TestableManagementClient(
            'https://test-domain.kinde.com',
            'client_id',
            'client_secret'
        );
        $client->throwOnAccessToken = true;

        $status = $client->getConfigurationStatus();

        $this->assertFalse($status['m2m_configured']);
        $this->assertContains(
            'Application not configured for M2M flow. In your Kinde dashboard:',
            $status['recommendations']
        );
    }

    public function testGetCredentialSourceUsesManagementCredentials(): void
    {
        putenv('KINDE_DOMAIN=https://test-domain.kinde.com');
        putenv('KINDE_MANAGEMENT_CLIENT_ID=mgmt_client_id');
        putenv('KINDE_MANAGEMENT_CLIENT_SECRET=mgmt_client_secret');

        $client = new KindeManagementClient(null, null, null, 'access_token');
        $source = $client->getCredentialSource();

        $this->assertSame('management_api', $source['type']);
    }

    public function testGetCredentialSourceUsesRegularCredentials(): void
    {
        putenv('KINDE_DOMAIN=https://test-domain.kinde.com');
        putenv('KINDE_CLIENT_ID=regular_client_id');
        putenv('KINDE_CLIENT_SECRET=regular_client_secret');
        putenv('KINDE_MANAGEMENT_CLIENT_ID=mgmt_client_id');
        putenv('KINDE_MANAGEMENT_CLIENT_SECRET=mgmt_client_secret');

        $client = new KindeManagementClient(null, null, null, 'access_token');
        $source = $client->getCredentialSource();

        $this->assertSame('regular_client', $source['type']);
    }

    public function testRefreshAccessTokenUsesGetAccessToken(): void
    {
        $client = new TestableManagementClient(
            'https://test-domain.kinde.com',
            'client_id',
            'client_secret'
        );
        $client->tokenResult = 'new_access_token';

        $this->assertSame('new_access_token', $client->refreshAccessToken());
    }

    public function testGetCurrentAccessTokenReturnsSetValue(): void
    {
        $client = new KindeManagementClient(
            'https://test-domain.kinde.com',
            'client_id',
            'client_secret',
            'initial_token'
        );

        $client->setAccessToken('updated_token');
        $this->assertSame('updated_token', $client->getCurrentAccessToken());
    }
}

class TestableManagementClient extends KindeManagementClient
{
    public bool $throwOnAccessToken = false;
    public string $tokenResult = 'access_token';

    public function getAccessToken(): string
    {
        if ($this->throwOnAccessToken) {
            throw new Exception('access token failure');
        }

        $this->accessToken = $this->tokenResult;
        $this->config->setAccessToken($this->tokenResult);

        return $this->tokenResult;
    }
}

