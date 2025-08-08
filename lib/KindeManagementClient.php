<?php

namespace Kinde\KindeSDK;

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
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
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
        $domain = $domain ?? getenv('KINDE_DOMAIN') ?: getenv('KINDE_HOST') ?: null;
        
        // Try Management API credentials first, then fall back to regular credentials
        $clientId = $clientId  ?? getenv('KINDE_MANAGEMENT_CLIENT_ID') ?: getenv('KINDE_CLIENT_ID') ?: null;
        $clientSecret = $clientSecret ?? getenv('KINDE_MANAGEMENT_CLIENT_SECRET') ?: getenv('KINDE_CLIENT_SECRET') ?: null;
        $accessToken = $accessToken ?? getenv('KINDE_MANAGEMENT_ACCESS_TOKEN') ?: null;

        // Validate required parameters
        if (!$domain) {
            throw new Exception('Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable');
        }

        if (!$clientId) {
            throw new Exception('Please provide client_id via parameter or KINDE_MANAGEMENT_CLIENT_ID/KINDE_CLIENT_ID environment variable');
        }

        if (!$clientSecret) {
            throw new Exception('Please provide client_secret via parameter or KINDE_MANAGEMENT_CLIENT_SECRET/KINDE_CLIENT_SECRET environment variable');
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
     * Check if the application is configured for M2M flow
     * 
     * @return bool
     */
    public function isConfiguredForM2M(): bool
    {
        // Basic check - if we can get a token, the app is configured for M2M
        try {
            $this->getAccessToken();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get configuration status and recommendations
     * 
     * @return array
     */
    public function getConfigurationStatus(): array
    {
        $status = [
            'domain' => !empty($this->domain),
            'client_id' => !empty($this->clientId),
            'client_secret' => !empty($this->clientSecret),
            'm2m_configured' => false,
            'recommendations' => []
        ];

        if (!$status['domain']) {
            $status['recommendations'][] = 'Set KINDE_DOMAIN environment variable';
        }
        if (!$status['client_id']) {
            $status['recommendations'][] = 'Set KINDE_MANAGEMENT_CLIENT_ID or KINDE_CLIENT_ID environment variable';
        }
        if (!$status['client_secret']) {
            $status['recommendations'][] = 'Set KINDE_MANAGEMENT_CLIENT_SECRET or KINDE_CLIENT_SECRET environment variable';
        }

        if ($status['domain'] && $status['client_id'] && $status['client_secret']) {
            try {
                $this->getAccessToken();
                $status['m2m_configured'] = true;
            } catch (Exception $e) {
                $status['recommendations'][] = 'Application not configured for M2M flow. In your Kinde dashboard:';
                $status['recommendations'][] = '  1. Go to Applications';
                $status['recommendations'][] = '  2. Create or edit an application';
                $status['recommendations'][] = '  3. Set Application Type to "Machine to Machine"';
                $status['recommendations'][] = '  4. Enable "Client Credentials" grant type';
                $status['recommendations'][] = '  5. Add required scopes (openid, profile, email, offline)';
            }
        }

        return $status;
    }

    /**
     * Initialize the configuration
     */
    protected function initializeConfiguration(): void
    {
        $this->config = new Configuration();
        $this->config->setHost($this->domain);
        
        // Set access token if provided, otherwise get one automatically
        if ($this->accessToken) {
            $this->config->setAccessToken($this->accessToken);
        }
        // Note: We'll get the token automatically in initializeApiClients() 
        // to avoid potential issues during construction
    }

    /**
     * Initialize all API clients
     */
    protected function initializeApiClients(): void
    {
        // Ensure we have an access token before initializing API clients
        $this->ensureAccessToken();
        
        $this->users = new UsersApi(null, $this->config);
        $this->organizations = new OrganizationsApi(null, $this->config);
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
     * Ensure access token is available
     * 
     * @throws Exception If token acquisition fails
     */
    protected function ensureAccessToken(): void
    {
        if (!$this->accessToken) {
            $this->getAccessToken();
        }
        
        // Verify token is set in configuration
        $configToken = $this->config->getAccessToken();
        
        if (!$configToken) {
            $this->config->setAccessToken($this->accessToken);
        }
    }

    /**
     * Get an access token using client credentials for Management API
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
            // Basic client credentials flow without audience parameter
            $requestData = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
                'audience' => $this->domain . '/api'
            ];
            
            $tokenUrl = $this->domain . '/oauth2/token';
            
            $response = $client->post($tokenUrl, [
                'form_params' => $requestData
            ]);

            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);
            
            if (!isset($data['access_token'])) {
                throw new Exception('No access token in response: ' . json_encode($data));
            }

            $this->accessToken = $data['access_token'];
            $this->config->setAccessToken($this->accessToken);
            
            return $this->accessToken;
            
        } catch (ClientException $e) {
            // Handle 4xx client errors
            $response = $e->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';
            $statusCode = $response ? $response->getStatusCode() : 'Unknown';
            
            throw new Exception("Client error (HTTP $statusCode): " . $e->getMessage() . "\nResponse: " . $responseBody);
            
        } catch (ServerException $e) {
            // Handle 5xx server errors
            $response = $e->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';
            $statusCode = $response ? $response->getStatusCode() : 'Unknown';
            
            throw new Exception("Server error (HTTP $statusCode): " . $e->getMessage() . "\nResponse: " . $responseBody);
            
        } catch (RequestException $e) {
            // Handle other request errors
            throw new Exception("Request error: " . $e->getMessage());
            
        } catch (Exception $e) {
            // Handle any other exceptions
            throw new Exception('Failed to get access token for Management API: ' . $e->getMessage());
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
     * Refresh the access token
     * 
     * @return string The new access token
     * @throws Exception If token refresh fails
     */
    public function refreshAccessToken(): string
    {
        // Clear existing token to force refresh
        $this->accessToken = null;
        $this->config->setAccessToken(null);
        
        return $this->getAccessToken();
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
     * Get the client secret
     * 
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Get the current access token
     * 
     * @return string|null
     */
    public function getCurrentAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Check if the client is using Management API credentials
     * 
     * @return bool
     */
    public function isUsingManagementCredentials(): bool
    {
        $managementClientId = getenv('KINDE_MANAGEMENT_CLIENT_ID');
        return $managementClientId && $this->clientId === $managementClientId;
    }

    /**
     * Get credential source information
     * 
     * @return array
     */
    public function getCredentialSource(): array
    {
        $managementClientId = getenv('KINDE_MANAGEMENT_CLIENT_ID');
        $regularClientId = getenv('KINDE_CLIENT_ID');
        
        if ($managementClientId && $this->clientId === $managementClientId) {
            return [
                'type' => 'management_api',
                'description' => 'Using dedicated Management API credentials',
                'env_vars' => ['KINDE_MANAGEMENT_CLIENT_ID', 'KINDE_MANAGEMENT_CLIENT_SECRET']
            ];
        } elseif ($regularClientId && $this->clientId === $regularClientId) {
            return [
                'type' => 'regular_client',
                'description' => 'Using regular client credentials (fallback)',
                'env_vars' => ['KINDE_CLIENT_ID', 'KINDE_CLIENT_SECRET']
            ];
        } else {
            return [
                'type' => 'direct',
                'description' => 'Credentials passed directly to constructor',
                'env_vars' => []
            ];
        }
    }


} 