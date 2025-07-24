<?php

namespace Kinde\KindeSDK;

use Kinde\KindeSDK\Api\UsersApi;
use Kinde\KindeSDK\Api\OrganizationsApi;
use Kinde\KindeSDK\Api\OAuthApi;
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
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use GuzzleHttp\Client;
use Exception;

/**
 * Kinde Management Client
 * 
 * Handles all management API operations for Kinde. This client is separate from
 * the OAuth functionality and is designed for server-to-server operations.
 */
class KindeManagementClient
{
    /**
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * @var string
     */
    protected string $clientId;

    /**
     * @var string
     */
    protected string $clientSecret;

    /**
     * @var string|null
     */
    protected ?string $accessToken;

    /**
     * @var UsersApi
     */
    public UsersApi $users;

    /**
     * @var OrganizationsApi
     */
    public OrganizationsApi $organizations;

    /**
     * @var OAuthApi
     */
    public OAuthApi $oauth;

    /**
     * @var ApplicationsApi
     */
    public ApplicationsApi $applications;

    /**
     * @var RolesApi
     */
    public RolesApi $roles;

    /**
     * @var PermissionsApi
     */
    public PermissionsApi $permissions;

    /**
     * @var FeatureFlagsApi
     */
    public FeatureFlagsApi $featureFlags;

    /**
     * @var EnvironmentsApi
     */
    public EnvironmentsApi $environments;

    /**
     * @var EnvironmentVariablesApi
     */
    public EnvironmentVariablesApi $environmentVariables;

    /**
     * @var ConnectionsApi
     */
    public ConnectionsApi $connections;

    /**
     * @var ConnectedAppsApi
     */
    public ConnectedAppsApi $connectedApps;

    /**
     * @var BusinessApi
     */
    public BusinessApi $business;

    /**
     * @var BillingAgreementsApi
     */
    public BillingAgreementsApi $billingAgreements;

    /**
     * @var BillingEntitlementsApi
     */
    public BillingEntitlementsApi $billingEntitlements;

    /**
     * @var BillingMeterUsageApi
     */
    public BillingMeterUsageApi $billingMeterUsage;

    /**
     * @var WebhooksApi
     */
    public WebhooksApi $webhooks;

    /**
     * @var CallbacksApi
     */
    public CallbacksApi $callbacks;

    /**
     * @var APIsApi
     */
    public APIsApi $apis;

    /**
     * @var IndustriesApi
     */
    public IndustriesApi $industries;

    /**
     * @var TimezonesApi
     */
    public TimezonesApi $timezones;

    /**
     * @var SubscribersApi
     */
    public SubscribersApi $subscribers;

    /**
     * @var SearchApi
     */
    public SearchApi $search;

    /**
     * @var PropertyCategoriesApi
     */
    public PropertyCategoriesApi $propertyCategories;

    /**
     * @var PropertiesApi
     */
    public PropertiesApi $properties;

    /**
     * @var IdentitiesApi
     */
    public IdentitiesApi $identities;

    /**
     * @var MFAApi
     */
    public MFAApi $mfa;

    /**
     * Constructor
     * 
     * @param string|null $domain The Kinde domain (e.g., https://your-domain.kinde.com)
     * @param string|null $clientId The client ID for your M2M application
     * @param string|null $clientSecret The client secret for your M2M application
     * @param string|null $accessToken Optional access token (will be fetched if not provided)
     */
    public function __construct(
        ?string $domain = null,
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $accessToken = null
    ) {
        // Load from environment variables if parameters are not provided
        $domain = $domain ?? $_ENV['KINDE_DOMAIN'] ?? $_ENV['KINDE_HOST'] ?? null;
        $clientId = $clientId ?? $_ENV['KINDE_CLIENT_ID'] ?? null;
        $clientSecret = $clientSecret ?? $_ENV['KINDE_CLIENT_SECRET'] ?? null;
        $accessToken = $accessToken ?? $_ENV['KINDE_MANAGEMENT_ACCESS_TOKEN'] ?? null;

        // Validate required parameters
        if (!$domain) {
            throw new Exception('Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable');
        }

        if (!$clientId) {
            throw new Exception('Please provide client_id via parameter or KINDE_CLIENT_ID environment variable');
        }

        if (!$clientSecret) {
            throw new Exception('Please provide client_secret via parameter or KINDE_CLIENT_SECRET environment variable');
        }

        $this->domain = $domain;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $accessToken;

        $this->initializeConfiguration();
        $this->initializeApiClients();
    }

    /**
     * Create a management client from environment variables only
     * 
     * @return self
     * @throws Exception If required environment variables are missing
     */
    public static function createFromEnv(): self
    {
        return new self();
    }

    /**
     * Initialize the configuration
     */
    protected function initializeConfiguration(): void
    {
        $this->config = new Configuration();
        $this->config->setHost($this->domain);
        
        // Set access token if provided
        if ($this->accessToken) {
            $this->config->setAccessToken($this->accessToken);
        }
    }

    /**
     * Initialize all API clients
     */
    protected function initializeApiClients(): void
    {
        $this->users = new UsersApi(null, $this->config);
        $this->organizations = new OrganizationsApi(null, $this->config);
        $this->oauth = new OAuthApi(null, $this->config);
        $this->applications = new ApplicationsApi(null, $this->config);
        $this->roles = new RolesApi(null, $this->config);
        $this->permissions = new PermissionsApi(null, $this->config);
        $this->featureFlags = new FeatureFlagsApi(null, $this->config);
        $this->environments = new EnvironmentsApi(null, $this->config);
        $this->environmentVariables = new EnvironmentVariablesApi(null, $this->config);
        $this->connections = new ConnectionsApi(null, $this->config);
        $this->connectedApps = new ConnectedAppsApi(null, $this->config);
        $this->business = new BusinessApi(null, $this->config);
        $this->billingAgreements = new BillingAgreementsApi(null, $this->config);
        $this->billingEntitlements = new BillingEntitlementsApi(null, $this->config);
        $this->billingMeterUsage = new BillingMeterUsageApi(null, $this->config);
        $this->webhooks = new WebhooksApi(null, $this->config);
        $this->callbacks = new CallbacksApi(null, $this->config);
        $this->apis = new APIsApi(null, $this->config);
        $this->industries = new IndustriesApi(null, $this->config);
        $this->timezones = new TimezonesApi(null, $this->config);
        $this->subscribers = new SubscribersApi(null, $this->config);
        $this->search = new SearchApi(null, $this->config);
        $this->propertyCategories = new PropertyCategoriesApi(null, $this->config);
        $this->properties = new PropertiesApi(null, $this->config);
        $this->identities = new IdentitiesApi(null, $this->config);
        $this->mfa = new MFAApi(null, $this->config);
    }

    /**
     * Get an access token using client credentials
     * 
     * @return string The access token
     * @throws Exception If token acquisition fails
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $client = new Client();
        
        try {
            $response = $client->post($this->domain . '/oauth2/token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'openid profile email offline'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($data['access_token'])) {
                throw new Exception('No access token in response');
            }

            $this->accessToken = $data['access_token'];
            $this->config->setAccessToken($this->accessToken);
            
            return $this->accessToken;
        } catch (Exception $e) {
            throw new Exception('Failed to get access token: ' . $e->getMessage());
        }
    }

    /**
     * Set a custom access token
     * 
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
        $this->config->setAccessToken($accessToken);
    }

    /**
     * Get the configuration object
     * 
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Get the domain
     * 
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the client ID
     * 
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Get all billing entitlements for a customer, handling pagination.
     *
     * @param string $customerId
     * @param int|null $pageSize
     * @param string|null $maxValue
     * @param string|null $expand
     * @return array All entitlements
     */
    public function getAllBillingEntitlements(
        string $customerId,
        ?int $pageSize = null,
        ?string $maxValue = null,
        ?string $expand = null
    ): array {
        $allEntitlements = [];
        $startingAfter = null;
        do {
            $response = $this->billingEntitlements->getBillingEntitlements(
                $customerId,
                $pageSize,
                $startingAfter,
                null, // ending_before
                $maxValue,
                $expand
            );
            $entitlements = $response->getEntitlements() ?? [];
            $allEntitlements = array_merge($allEntitlements, $entitlements);
            $hasMore = $response->getHasMore();
            if ($hasMore && count($entitlements) > 0) {
                $lastEntitlement = end($entitlements);
                $startingAfter = $lastEntitlement->getId();
            } else {
                $startingAfter = null;
            }
        } while ($hasMore && $startingAfter);
        return $allEntitlements;
    }
} 