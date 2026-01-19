<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Api\UsersApi;
use Kinde\KindeSDK\Api\OrganizationsApi;
use Kinde\KindeSDK\Api\ApplicationsApi;
use Kinde\KindeSDK\Api\RolesApi;
use Kinde\KindeSDK\Api\PermissionsApi;
use Kinde\KindeSDK\Api\FeatureFlagsApi;
use Kinde\KindeSDK\Api\EnvironmentsApi;
use Kinde\KindeSDK\Api\EnvironmentVariablesApi;
use Kinde\KindeSDK\Api\ConnectionsApi;
use Kinde\KindeSDK\Api\ConnectedAppsApi;
use Kinde\KindeSDK\Api\BusinessApi;
use Kinde\KindeSDK\Api\BillingAgreementsApi;
use Kinde\KindeSDK\Api\BillingEntitlementsApi;
use Kinde\KindeSDK\Api\BillingMeterUsageApi;
use Kinde\KindeSDK\Api\WebhooksApi;
use Kinde\KindeSDK\Api\CallbacksApi;
use Kinde\KindeSDK\Api\APIsApi;
use Kinde\KindeSDK\Api\IndustriesApi;
use Kinde\KindeSDK\Api\TimezonesApi;
use Kinde\KindeSDK\Api\SubscribersApi;
use Kinde\KindeSDK\Api\SearchApi;
use Kinde\KindeSDK\Api\PropertyCategoriesApi;
use Kinde\KindeSDK\Api\PropertiesApi;
use Kinde\KindeSDK\Api\IdentitiesApi;
use Kinde\KindeSDK\Api\MFAApi;
use Kinde\KindeSDK\Configuration;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Exception;

/**
 * Unit tests for KindeManagementClient.
 * Tests constructor, environment variable handling, API client initialization,
 * and access token management.
 *
 * @covers \Kinde\KindeSDK\KindeManagementClient
 */
class KindeManagementClientTest extends KindeTestCase
{
    private const TEST_ACCESS_TOKEN = 'test_access_token_for_management';

    // =========================================================================
    // Constructor Tests
    // =========================================================================

    public function testConstructorWithExplicitParameters(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
        $this->assertEquals(self::TEST_CLIENT_ID, $management->getClientId());
    }

    public function testConstructorInitializesApiClients(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN // Provide token to avoid HTTP call
        );

        // Test that all API clients are initialized
        $this->assertInstanceOf(UsersApi::class, $management->users);
        $this->assertInstanceOf(OrganizationsApi::class, $management->organizations);
        $this->assertInstanceOf(ApplicationsApi::class, $management->applications);
        $this->assertInstanceOf(RolesApi::class, $management->roles);
        $this->assertInstanceOf(PermissionsApi::class, $management->permissions);
        $this->assertInstanceOf(FeatureFlagsApi::class, $management->featureFlags);
        $this->assertInstanceOf(EnvironmentsApi::class, $management->environments);
        $this->assertInstanceOf(EnvironmentVariablesApi::class, $management->environmentVariables);
        $this->assertInstanceOf(ConnectionsApi::class, $management->connections);
        $this->assertInstanceOf(ConnectedAppsApi::class, $management->connectedApps);
        $this->assertInstanceOf(BusinessApi::class, $management->business);
        $this->assertInstanceOf(BillingAgreementsApi::class, $management->billingAgreements);
        $this->assertInstanceOf(BillingEntitlementsApi::class, $management->billingEntitlements);
        $this->assertInstanceOf(BillingMeterUsageApi::class, $management->billingMeterUsage);
        $this->assertInstanceOf(WebhooksApi::class, $management->webhooks);
        $this->assertInstanceOf(CallbacksApi::class, $management->callbacks);
        $this->assertInstanceOf(APIsApi::class, $management->apis);
        $this->assertInstanceOf(IndustriesApi::class, $management->industries);
        $this->assertInstanceOf(TimezonesApi::class, $management->timezones);
        $this->assertInstanceOf(SubscribersApi::class, $management->subscribers);
        $this->assertInstanceOf(SearchApi::class, $management->search);
        $this->assertInstanceOf(PropertyCategoriesApi::class, $management->propertyCategories);
        $this->assertInstanceOf(PropertiesApi::class, $management->properties);
        $this->assertInstanceOf(IdentitiesApi::class, $management->identities);
        $this->assertInstanceOf(MFAApi::class, $management->mfa);
    }

    public function testConstructorInitializesConfiguration(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEquals(self::TEST_DOMAIN, $config->getHost());
    }

    public function testConstructorWithAccessTokenSetsItInConfig(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEquals(self::TEST_DOMAIN, $config->getHost());
        $this->assertEquals(self::TEST_ACCESS_TOKEN, $config->getAccessToken());
    }

    // =========================================================================
    // Environment Variable Tests
    // =========================================================================

    public function testCreateFromEnvWithValidEnvironmentVariables(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] = self::TEST_ACCESS_TOKEN;
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);
        putenv('KINDE_CLIENT_SECRET=' . self::TEST_CLIENT_SECRET);
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=' . self::TEST_ACCESS_TOKEN);

        $management = KindeManagementClient::createFromEnv();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
        $this->assertEquals(self::TEST_CLIENT_ID, $management->getClientId());
    }

    public function testCreateFromEnvWithKindeHostEnvironmentVariable(): void
    {
        $_ENV['KINDE_HOST'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] = self::TEST_ACCESS_TOKEN;
        putenv('KINDE_HOST=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);
        putenv('KINDE_CLIENT_SECRET=' . self::TEST_CLIENT_SECRET);
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=' . self::TEST_ACCESS_TOKEN);

        $management = KindeManagementClient::createFromEnv();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
    }

    public function testConstructorWithAccessTokenFromEnvironment(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] = self::TEST_ACCESS_TOKEN;
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);
        putenv('KINDE_CLIENT_SECRET=' . self::TEST_CLIENT_SECRET);
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=' . self::TEST_ACCESS_TOKEN);

        $management = new KindeManagementClient();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
    }

    public function testConstructorWithMixedParametersAndEnvironmentVariables(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);

        $management = new KindeManagementClient(
            null, // Use from environment
            null, // Use from environment
            self::TEST_CLIENT_SECRET, // Override
            self::TEST_ACCESS_TOKEN // Override
        );

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
        $this->assertEquals(self::TEST_CLIENT_ID, $management->getClientId());
    }

    public function testEmptyConstructorUsesEnvironmentVariables(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] = self::TEST_ACCESS_TOKEN;
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);
        putenv('KINDE_CLIENT_SECRET=' . self::TEST_CLIENT_SECRET);
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=' . self::TEST_ACCESS_TOKEN);

        $management = new KindeManagementClient();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
        $this->assertEquals(self::TEST_CLIENT_ID, $management->getClientId());
    }

    // =========================================================================
    // Validation Tests
    // =========================================================================

    public function testCreateFromEnvThrowsWhenMissingDomain(): void
    {
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);
        putenv('KINDE_CLIENT_SECRET=' . self::TEST_CLIENT_SECRET);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable');

        KindeManagementClient::createFromEnv();
    }

    public function testCreateFromEnvThrowsWhenMissingClientId(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_SECRET=' . self::TEST_CLIENT_SECRET);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide client_id via parameter or KINDE_MANAGEMENT_CLIENT_ID/KINDE_CLIENT_ID environment variable');

        KindeManagementClient::createFromEnv();
    }

    public function testCreateFromEnvThrowsWhenMissingClientSecret(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=' . self::TEST_CLIENT_ID);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide client_secret via parameter or KINDE_MANAGEMENT_CLIENT_SECRET/KINDE_CLIENT_SECRET environment variable');

        KindeManagementClient::createFromEnv();
    }

    // =========================================================================
    // Access Token Management Tests
    // =========================================================================

    public function testSetAccessToken(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            'initial_token'
        );

        $management->setAccessToken(self::TEST_ACCESS_TOKEN);
        
        // The access token should be set in the configuration
        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testGetAccessTokenReturnsSetValue(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $token = $management->getAccessToken();
        $this->assertEquals(self::TEST_ACCESS_TOKEN, $token);
    }

    /**
     * @group integration
     * @skip This test requires HTTP mocking to properly test token acquisition failure
     */
    public function testGetAccessTokenWithoutTokenThrowsException(): void
    {
        // This test is skipped because without HTTP mocking, the management client
        // will attempt to acquire a token via M2M flow, which will fail with an HTTP error
        // rather than the expected application-level exception.
        $this->markTestSkipped('Requires HTTP mocking to test properly');
    }

    public function testGetCurrentAccessTokenReturnsSetValue(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            'initial_token'
        );

        $management->setAccessToken('updated_token');
        $this->assertSame('updated_token', $management->getCurrentAccessToken());
    }

    // =========================================================================
    // Getter Tests
    // =========================================================================

    public function testGetDomain(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $this->assertEquals(self::TEST_DOMAIN, $management->getDomain());
    }

    public function testGetClientId(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $this->assertEquals(self::TEST_CLIENT_ID, $management->getClientId());
    }

    public function testGetConfig(): void
    {
        $management = new KindeManagementClient(
            self::TEST_DOMAIN,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            self::TEST_ACCESS_TOKEN
        );

        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
    }

    // =========================================================================
    // Credential Source Tests
    // =========================================================================

    public function testGetCredentialSourceUsesManagementCredentials(): void
    {
        // Only set management credentials - should use management_api
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_MANAGEMENT_CLIENT_ID'] = 'mgmt_client_id';
        $_ENV['KINDE_MANAGEMENT_CLIENT_SECRET'] = 'mgmt_client_secret';
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_MANAGEMENT_CLIENT_ID=mgmt_client_id');
        putenv('KINDE_MANAGEMENT_CLIENT_SECRET=mgmt_client_secret');

        $management = new KindeManagementClient(null, null, null, self::TEST_ACCESS_TOKEN);
        $source = $management->getCredentialSource();

        $this->assertSame('management_api', $source['type']);
    }

    public function testGetCredentialSourceUsesRegularCredentials(): void
    {
        // Set only regular credentials - should use regular_client
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = 'regular_client_id';
        $_ENV['KINDE_CLIENT_SECRET'] = 'regular_client_secret';
        putenv('KINDE_DOMAIN=' . self::TEST_DOMAIN);
        putenv('KINDE_CLIENT_ID=regular_client_id');
        putenv('KINDE_CLIENT_SECRET=regular_client_secret');

        // Clear management credentials
        unset($_ENV['KINDE_MANAGEMENT_CLIENT_ID']);
        unset($_ENV['KINDE_MANAGEMENT_CLIENT_SECRET']);
        putenv('KINDE_MANAGEMENT_CLIENT_ID');
        putenv('KINDE_MANAGEMENT_CLIENT_SECRET');

        $management = new KindeManagementClient(null, null, null, self::TEST_ACCESS_TOKEN);
        $source = $management->getCredentialSource();

        $this->assertSame('regular_client', $source['type']);
    }
}
