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
use Exception;
use PHPUnit\Framework\TestCase;

class KindeManagementClientTest extends TestCase
{
    private string $testDomain = 'https://test-domain.kinde.com';
    private string $testClientId = 'test_client_id';
    private string $testClientSecret = 'test_client_secret';
    private string $testAccessToken = 'test_access_token';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any existing environment variables
        putenv('KINDE_DOMAIN');
        putenv('KINDE_HOST');
        putenv('KINDE_CLIENT_ID');
        putenv('KINDE_CLIENT_SECRET');
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN');
        unset($_ENV['KINDE_DOMAIN']);
        unset($_ENV['KINDE_HOST']);
        unset($_ENV['KINDE_CLIENT_ID']);
        unset($_ENV['KINDE_CLIENT_SECRET']);
        unset($_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN']);
    }

    public function testCreateFromEnvWithValidEnvironmentVariables()
    {
        // Set up environment variables
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $management = KindeManagementClient::createFromEnv();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
        $this->assertEquals($this->testClientId, $management->getClientId());
    }

    public function testCreateFromEnvWithKindeHostEnvironmentVariable()
    {
        // Set up environment variables using KINDE_HOST
        putenv('KINDE_HOST=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        $_ENV['KINDE_HOST'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $management = KindeManagementClient::createFromEnv();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
    }

    public function testCreateFromEnvWithMissingDomain()
    {
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        unset($_ENV['KINDE_DOMAIN']);
        unset($_ENV['KINDE_HOST']);
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable');

        KindeManagementClient::createFromEnv();
    }

    public function testCreateFromEnvWithMissingClientId()
    {
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        unset($_ENV['KINDE_CLIENT_ID']);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide client_id via parameter or KINDE_CLIENT_ID environment variable');

        KindeManagementClient::createFromEnv();
    }

    public function testCreateFromEnvWithMissingClientSecret()
    {
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        unset($_ENV['KINDE_CLIENT_SECRET']);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide client_secret via parameter or KINDE_CLIENT_SECRET environment variable');

        KindeManagementClient::createFromEnv();
    }

    public function testConstructorWithExplicitParameters()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret,
            $this->testAccessToken
        );

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
        $this->assertEquals($this->testClientId, $management->getClientId());
    }

    public function testConstructorWithMixedParameters()
    {
        // Set some environment variables
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;

        // Override some parameters
        $management = new KindeManagementClient(
            domain: null, // Use from environment
            clientId: null, // Use from environment
            clientSecret: $this->testClientSecret, // Override
            accessToken: $this->testAccessToken // Override
        );

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
        $this->assertEquals($this->testClientId, $management->getClientId());
    }

    public function testConstructorWithAccessTokenFromEnvironment()
    {
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=' . $this->testAccessToken);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] = $this->testAccessToken;

        $management = new KindeManagementClient();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
    }

    public function testApiClientsInitialization()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret
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

    public function testConfigurationInitialization()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret
        );

        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEquals($this->testDomain, $config->getHost());
    }

    public function testConfigurationWithAccessToken()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret,
            $this->testAccessToken
        );

        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertEquals($this->testDomain, $config->getHost());
    }

    public function testSetAccessToken()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret
        );

        $management->setAccessToken($this->testAccessToken);
        
        // The access token should be set in the configuration
        $config = $management->getConfig();
        $this->assertInstanceOf(Configuration::class, $config);
    }

    public function testGetAccessToken()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret,
            $this->testAccessToken
        );

        $token = $management->getAccessToken();
        $this->assertEquals($this->testAccessToken, $token);
    }

    public function testGetAccessTokenWithoutToken()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret
        );

        // This should throw an exception since we're not mocking the HTTP client
        $this->expectException(Exception::class);
        $management->getAccessToken();
    }

    public function testGetDomain()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret
        );

        $this->assertEquals($this->testDomain, $management->getDomain());
    }

    public function testGetClientId()
    {
        $management = new KindeManagementClient(
            $this->testDomain,
            $this->testClientId,
            $this->testClientSecret
        );

        $this->assertEquals($this->testClientId, $management->getClientId());
    }

    public function testEmptyConstructor()
    {
        // Set environment variables
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $management = new KindeManagementClient();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
        $this->assertEquals($this->testClientId, $management->getClientId());
    }

    public function testEmptyConstructorWithAccessToken()
    {
        // Set environment variables
        putenv('KINDE_DOMAIN=' . $this->testDomain);
        putenv('KINDE_CLIENT_ID=' . $this->testClientId);
        putenv('KINDE_CLIENT_SECRET=' . $this->testClientSecret);
        putenv('KINDE_MANAGEMENT_ACCESS_TOKEN=' . $this->testAccessToken);
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] = $this->testAccessToken;

        $management = new KindeManagementClient();

        $this->assertInstanceOf(KindeManagementClient::class, $management);
        $this->assertEquals($this->testDomain, $management->getDomain());
        $this->assertEquals($this->testClientId, $management->getClientId());
    }


} 