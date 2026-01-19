<?php

namespace Kinde\KindeSDK;

use Exception;
use InvalidArgumentException;
use GuzzleHttp\Client;
use Kinde\KindeSDK\Sdk\OAuth2\PKCE;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Enums\TokenType;
use Kinde\KindeSDK\Sdk\Enums\AdditionalParameters;
use Kinde\KindeSDK\Sdk\Enums\PortalPage;
use Kinde\KindeSDK\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Sdk\OAuth2\ClientCredentials;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Api\Frontend\BillingApi;
use Kinde\KindeSDK\Api\Frontend\FeatureFlagsApi;
use Kinde\KindeSDK\Api\Frontend\PermissionsApi;
use Kinde\KindeSDK\Api\Frontend\OAuthApi;
use Kinde\KindeSDK\Model\Frontend\GetEntitlementsResponseDataEntitlementsInner;
use Kinde\KindeSDK\Model\Frontend\GetUserPermissionsResponse;
use Kinde\KindeSDK\Model\Frontend\GetFeatureFlagsResponse;
use Kinde\KindeSDK\Model\Frontend\UserProfileV2;
use UnexpectedValueException;

class KindeClientSDK
{
    /**
     * @var string A variable that is used to store the domain of the API.
     */
    public string $domain;

    /**
     * @var string This is the redirect URI that you provided when you registered your application.
     */
    public ?string $redirectUri;

    /**
     * @var string This is the logout redirect URI that you provided when you registered your application.
     */
    public ?string $logoutRedirectUri;

    /**
     * @var string A variable that is used to store the client ID of the application.
     */
    public string $clientId;

    /**
     * @var string This is the client secret of your application.
     */
    public string $clientSecret;

    /**
     * @var string This is the authorization endpoint of the API.
     */
    public string $authorizationEndpoint;

    /**
     * @var string This is the token endpoint of the API.
     */
    public string $tokenEndpoint;

    /** 
     * @var string Used to store the logout endpoint of the API. 
     */
    public string $logoutEndpoint;

    /* A variable that is used to store the grant type that you want to use. */
    public string $grantType;

    /* This is a additionalParameters data. */
    public array $additionalParameters;

    /**
     * @var string This is a variable that is used to store the scopes that you want to request.
     */
    public string $scopes;

    /* A variable that is used to store the protocol that you want to use when the SDK requests to get a token */
    public string $protocol;

    /**
     * @var bool When set to true, forces the SDK to use API calls instead of token parsing for retrieving claims.
     */
    public bool $forceApi;

    public $storage;



    function __construct(
        ?string $domain = null,
        ?string $redirectUri = null,
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $grantType = null,
        ?string $logoutRedirectUri = null,
        string $scopes = 'openid profile email offline',
        array $additionalParameters = [],
        string $protocol = "",
        bool $forceApi = false
    ) {
        // Load from environment variables if parameters are not provided
        $domain = $domain ?? $_ENV['KINDE_DOMAIN'] ?? $_ENV['KINDE_HOST'] ?? null;
        $redirectUri = $redirectUri ?? $_ENV['KINDE_REDIRECT_URI'] ?? null;
        $clientId = $clientId ?? $_ENV['KINDE_CLIENT_ID'] ?? null;
        $clientSecret = $clientSecret ?? $_ENV['KINDE_CLIENT_SECRET'] ?? null;
        $grantType = $grantType ?? $_ENV['KINDE_GRANT_TYPE'] ?? GrantType::authorizationCode;
        $logoutRedirectUri = $logoutRedirectUri ?? $_ENV['KINDE_LOGOUT_REDIRECT_URI'] ?? null;
        $scopes = $_ENV['KINDE_SCOPES'] ?? $scopes;
        $protocol = $_ENV['KINDE_PROTOCOL'] ?? $protocol;
        $forceApi = $forceApi || ($_ENV['KINDE_FORCE_API'] ?? 'false') === 'true';

        // Validate required parameters
        if (empty($domain)) {
            throw new InvalidArgumentException("Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable");
        }
        if (!Utils::validationURL($domain)) {
            throw new InvalidArgumentException("Please provide valid domain");
        }
        $this->domain = $domain;

        $isNotCCGrantType = $grantType !== GrantType::clientCredentials;

        if ($isNotCCGrantType && empty($redirectUri)) {
            throw new InvalidArgumentException("Please provide redirect_uri via parameter or KINDE_REDIRECT_URI environment variable");
        }
        if ($isNotCCGrantType && !Utils::validationURL($redirectUri)) {
            throw new InvalidArgumentException("Please provide valid redirect_uri");
        }
        $this->redirectUri = $redirectUri;

        if (empty($clientSecret)) {
            throw new InvalidArgumentException("Please provide client_secret via parameter or KINDE_CLIENT_SECRET environment variable");
        }
        $this->clientSecret = $clientSecret;

        if (empty($clientId)) {
            throw new InvalidArgumentException("Please provide client_id via parameter or KINDE_CLIENT_ID environment variable");
        }
        $this->clientId = $clientId;

        if (empty($grantType)) {
            throw new InvalidArgumentException("Please provide grant_type via parameter or KINDE_GRANT_TYPE environment variable");
        }
        $this->grantType = $grantType;

        if ($isNotCCGrantType && empty($logoutRedirectUri)) {
            throw new InvalidArgumentException("Please provide logout_redirect_uri via parameter or KINDE_LOGOUT_REDIRECT_URI environment variable");
        }
        if ($isNotCCGrantType && !Utils::validationURL($logoutRedirectUri)) {
            throw new InvalidArgumentException("Please provide valid logout_redirect_uri");
        }

        $this->additionalParameters = Utils::checkAdditionalParameters($additionalParameters);

        $this->logoutRedirectUri = $logoutRedirectUri;
        $this->scopes = $scopes;
        $this->protocol = $protocol;
        $this->forceApi = $forceApi;

        // Other endpoints
        $this->authorizationEndpoint = $this->domain . '/oauth2/auth';
        $this->tokenEndpoint = $this->domain . '/oauth2/token';
        $this->logoutEndpoint = $this->domain . '/logout';

        $this->storage = Storage::getInstance();
        $this->storage->setJwksUrl($this->domain . '/.well-known/jwks.json');
    }

    /**
     * Create a Kinde client from environment variables only
     * 
     * @return self
     * @throws InvalidArgumentException If required environment variables are missing
     */
    public static function createFromEnv(): self
    {
        return new self();
    }

    /**
     * Performs user login and returns the authentication result.
     *
     * @param array $additionalParameters Additional parameters for authentication (optional).
     *
     * @return AuthenticationResult The authentication result.
     *
     * @throws InvalidArgumentException When an incorrect grant_type is provided.
     * @throws Throwable                  When an error occurs during authentication.
     */
    public function login(
        array $additionalParameters = []
    ) {
        try {
            switch ($this->grantType) {
                case GrantType::clientCredentials:
                    $auth = new ClientCredentials();
                    return $auth->authenticate($this, $additionalParameters);
                case GrantType::authorizationCode:
                    $this->cleanStorage();
                    $auth = new AuthorizationCode();
                    return $auth->authenticate($this, $additionalParameters);
                case GrantType::PKCE:
                    $this->cleanStorage();
                    $auth = new PKCE();
                    return $auth->authenticate($this, $additionalParameters);
                default:
                    throw new InvalidArgumentException("Please provide correct grant_type");
                    break;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Registers the user and returns the authentication result.
     *
     * @param array $additionalParameters Additional parameters for registration (optional).
     *
     * @return AuthenticationResult The authentication result.
     */
    public function register(array $additionalParameters = [])
    {
        $this->cleanStorage();
        
        if (!isset($additionalParameters['prompt'])) {
            $additionalParameters['prompt'] = AdditionalParameters::PROMPT_CREATE;
        }

        $auth = new PKCE();
        return $auth->authenticate($this, $additionalParameters);
    }

    /**
     * Creates an organization and returns the authentication result.
     *
     * @param array $additionalParameters Additional parameters for organization creation (optional).
     *
     * @return AuthenticationResult The authentication result.
     */
    public function createOrg(array $additionalParameters = [])
    {
        $additionalParameters['is_create_org'] = 'true';
        return $this->register($additionalParameters);
    }

    /**
     * Logs out the user and redirects to the logout endpoint.
     *
     * @return void
     */
    public function logout()
    {
        $this->cleanStorage();

        $searchParams = [
            'redirect' => $this->logoutRedirectUri
        ];
        header('Location: ' . $this->logoutEndpoint . '?' . http_build_query($searchParams));
        exit();
    }

    /**
     * Retrieves the access token for authentication.
     *
     * @return AccessToken|null The access token or null if not available.
     *
     * @throws OAuthException        When an OAuth-related error occurs.
     * @throws InvalidArgumentException When required parameters are missing or invalid.
     */
    public function getToken()
    {
        if ($this->grantType == GrantType::clientCredentials) {
            return $this->login();
        }
        // Check authenticated
        if ($this->isAuthenticated) {
            $token = $this->storage->getToken(false);
            if (!empty($token)) {
                return $token;
            }
        }

        $newGrantType = $this->getGrantType($this->grantType);
        $formParams = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => $newGrantType,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code'
        ];

        $url = $this->getProtocol() . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $urlComponents = parse_url($url);
        parse_str($urlComponents['query'] ?? "", $params);
        $stateServer = $params['state'] ?? null;

        $this->checkStateAuthentication($stateServer);

        $error = $params['error'] ?? '';
        if (!empty($error)) {
            $errorDescription = $params['error_description'] ?? '';
            $msg = !empty($errorDescription) ? $errorDescription : $error;
            throw new OAuthException($msg);
        }

        $authorizationCode = $params['code'] ?? '';
        if (empty($authorizationCode)) {
            throw new InvalidArgumentException('Not found code param');
        }

        $formParams['code'] = $authorizationCode;
        $codeVerifier = $this->storage->getCodeVerifier();

        if (!empty($codeVerifier)) {
            $formParams['code_verifier'] = $codeVerifier;
        } else if ($this->grantType == GrantType::PKCE) {
            throw new InvalidArgumentException('Not found code_verifier');
        }

        return $this->fetchToken($formParams);
    }

    /**
     * Retrieves the user details from the storage.
     *
     * @return UserProfile The user profile or null if not available.
     */
    public function getUserDetails()
    {
        return $this->storage->getUserProfile();
    }

    /**
     * Retrieves a specific claim from the token or API.
     *
     * @param string $keyName   The name of the claim to retrieve.
     * @param string $tokenType The type of token to retrieve the claim from (optional, defaults to TokenType::ACCESS_TOKEN).
     *
     * @return array An associative array containing the name and value of the claim, or null if the claim doesn't exist.
     */
    public function getClaim(string $keyName, string $tokenType = TokenType::ACCESS_TOKEN)
    {
        if ($this->forceApi) {
            return $this->getClaimFromApi($keyName);
        }

        $claims = self::getClaims($tokenType);

        if (!array_key_exists($keyName, $claims)) {
            error_log("The value of '{$keyName}' claimed does not exist in your token");
        }
        return [
            'name' => $keyName,
            'value' => $claims[$keyName] ?? null
        ];
    }

    /**
     * Gets a claim from API when force_api is enabled.
     *
     * @param string $keyName The name of the claim to retrieve
     * @return array The claim data
     */
    private function getClaimFromApi(string $keyName): array
    {
        return match ($keyName) {
            'feature_flags' => [
                'name' => $keyName,
                'value' => $this->getFeatureFlagsFromApi()
            ],
            'org_code', 'permissions' => [
                'name' => $keyName,
                'value' => $this->getPermissionsData()[$keyName === 'org_code' ? 'orgCode' : 'permissions'] ?? ($keyName === 'org_code' ? null : [])
            ],
            'org_codes' => [
                'name' => $keyName,
                'value' => $this->getUserProfileFromApi()->getOrgCodes() ?? []
            ],
            default => [
                'name' => $keyName,
                'value' => $this->getProfileClaimValue($keyName)
            ]
        };
    }

    /**
     * Gets permissions data (org_code and permissions) with optimized caching.
     *
     * @return array The permissions data
     */
    private function getPermissionsData(): array
    {
        return $this->getPermissionsFromApi();
    }

    /**
     * Gets a profile claim value by key name.
     *
     * @param string $keyName The claim key name
     * @return mixed The claim value
     */
    private function getProfileClaimValue(string $keyName): mixed
    {
        $userProfile = $this->getUserProfileFromApi();
        $profileData = [
            'id' => $userProfile->getId(),
            'given_name' => $userProfile->getGivenName(),
            'family_name' => $userProfile->getFamilyName(),
            'email' => $userProfile->getEmail(),
            'picture' => $userProfile->getPicture(),
            'preferred_username' => $userProfile->getPreferredUsername(),
            'iss' => $this->domain,
            'aud' => $this->clientId,
            'sub' => $userProfile->getId(),
        ];
        
        return $profileData[$keyName] ?? null;
    }

    /**
     * Retrieves the organization code and permissions from the claims or API.
     *
     * @return array An associative array containing the organization code and permissions.
     */
    public function getPermissions()
    {
        if ($this->forceApi) {
            return $this->getPermissionsFromApi();
        }

        $claims = self::getClaims();

        return [
            'orgCode' => $claims['org_code'] ?? null,
            'permissions' => $claims['permissions'] ?? []
        ];
    }

    /**
     * Checks if a specific permission is granted.
     *
     * @param string $permission The permission to check.
     *
     * @return array An associative array containing the organization code and a boolean indicating if the permission is granted.
     */
    public function getPermission(string $permission)
    {
        $claims = self::getClaims();
        $permissions = $claims['permissions'] ?? [];

        return [
            'orgCode' => $claims['org_code'] ?? null,
            'isGranted' => in_array($permission, $permissions)
        ];
    }

    /**
     * Retrieves the organization code from the token.
     *
     * @return array An associative array containing the organization code.
     */
    public function getOrganization()
    {
        return [
            'orgCode' => self::getClaim('org_code')['value']
        ];
    }
    
    /**
     * Retrieves the organization codes associated with the user from the token.
     *
     * @return array An associative array containing the organization codes.
     */
    public function getUserOrganizations()
    {
        return [
            'orgCodes' => self::getClaim('org_codes', TokenType::ID_TOKEN)['value']
        ];
    }

    /**
     * Retrieves a boolean feature flag value based on the flag name.
     *
     * @param string $flagName      The name of the feature flag to retrieve.
     * @param mixed  $defaultValue  The default value for the flag (optional).
     *
     * @throws UnexpectedValueException If the flag is not found and no default value is provided, or if the requested type doesn't match the flag's type.
     *
     * @return array An associative array containing the flag code, type, value, and a boolean indicating if the default value was used.
     */
    public function getBooleanFlag(string $flagName, $defaultValue = null) // Let's use original default value, do not add type to here
    {
        return self::getFlag($flagName, ['defaultValue' => $defaultValue], 'b');
    }

    /**
     * Retrieves a string feature flag value based on the flag name.
     *
     * @param string $flagName      The name of the feature flag to retrieve.
     * @param mixed  $defaultValue  The default value for the flag (optional).
     *
     * @throws UnexpectedValueException If the flag is not found and no default value is provided, or if the requested type doesn't match the flag's type.
     *
     * @return array An associative array containing the flag code, type, value, and a boolean indicating if the default value was used.
     */
    public function getStringFlag(string $flagName, $defaultValue = null)
    {
        return self::getFlag($flagName, ['defaultValue' => $defaultValue], 's');
    }

    /**
     * Retrieves an integer feature flag value based on the flag name.
     *
     * @param string $flagName      The name of the feature flag to retrieve.
     * @param mixed  $defaultValue  The default value for the flag (optional).
     *
     * @throws UnexpectedValueException If the flag is not found and no default value is provided, or if the requested type doesn't match the flag's type.
     *
     * @return array An associative array containing the flag code, type, value, and a boolean indicating if the default value was used.
     */
    public function getIntegerFlag(string $flagName, $defaultValue = null)
    {
        return self::getFlag($flagName, ['defaultValue' => $defaultValue], 'i');
    }

    /**
     * Retrieves a feature flag value based on the flag name.
     *
     * @param string $flagName   The name of the feature flag to retrieve.
     * @param array  $options    Additional options for handling the flag (optional).
     * @param string $flagType   The expected type of the flag (optional).
     *
     * @throws UnexpectedValueException If the flag is not found and no default value is provided, or if the requested type doesn't match the flag's type.
     *
     * @return array An associative array containing the flag code, type, value, and a boolean indicating if the default value was used.
     */
    public function getFlag(string $flagName, array $options = [], ?string $flagType = null)
    {
        $isUsedDefault = false;
        $flag = self::getFeatureFlags($flagName);
        
        if (!isset($flag)) {
            $isUsedDefault = true;
            $flag = [
                'v' => $options['defaultValue'],
                't' => $flagType
            ];
        }

        if (!isset($flag['v'])) {
            throw new UnexpectedValueException("This flag '{$flagName}' was not found, and no default value has been provided");
        }
        $flagTypeParsed = Utils::$listType[$flag['t']];

        $requestType = $flagType ? Utils::$listType[$flagType] : null;
        if (isset($requestType) && $flagTypeParsed != $requestType) {
            throw new UnexpectedValueException("Flag '{$flagName}' is type {$flagTypeParsed} - requested type {$requestType}");
        }

        return [
            "code" => $flagName,
            "type" => $flagTypeParsed,
            "value" => $flag['v'],
            "is_default" => $isUsedDefault
        ];
    }

    public function __get($key)
    {
        if (!property_exists($this, $key) && $key === 'isAuthenticated') {
            return $this->isAuthenticated();
        }

        return $this->$key;
    }

    /**
     * Retrieves the feature flags or a specific feature flag value based on the flag name.
     *
     * @param string|null $name The name of the feature flag to retrieve (optional).
     *
     * @throws InvalidArgumentException If the feature flag is not found.
     * @return mixed|array|null The feature flags or a specific feature flag value.
     */
    private function getFeatureFlags(?string $name = null)
    {
        if ($this->forceApi) {
            $flags = $this->getFeatureFlagsFromApi();
        } else {
            $flags = self::getClaim('feature_flags')['value'];
        }

        if (isset($name) && ! array_key_exists($name, $flags)) {
            throw new InvalidArgumentException("The feature flag '{$name}' was not found");
        }

        if (isset($name) && !empty($flags)) {
            return $flags[$name];
        }

        return $flags;
    }

    /**
     * Fetches a token from the token endpoint using the provided form parameters.
     *
     * @param array $formParams The form parameters to be sent in the request.
     *
     * @throws GuzzleException If an error occurs during the HTTP request.
     *
     * @return mixed The decoded token response.
     */
    private function fetchToken($formParams)
    {
        $client = new Client();

        $response =
            $client->request('POST', $this->tokenEndpoint, [
                'form_params' => $formParams,
                'headers' => [
                    'Kinde-SDK' => 'PHP/1.2' // current SDK version
                ]
            ]);

        $token = $response->getBody()->getContents();
        $this->storage->setToken($token);
        $tokenDecode = json_decode($token, false);

        // Cleaning
        $this->storage->removeItem(StorageEnums::CODE_VERIFIER);
        $this->storage->removeItem(StorageEnums::STATE);
        return $tokenDecode;
    }

    /**
     * Checks if the user is authenticated by verifying the expiration time of the token.
     *
     * @return bool Returns true if the user is authenticated, false otherwise.
     */
    private function isAuthenticated()
    {
        $timeExpired = $this->storage->getExpiredAt();
        $authenticated = $timeExpired > time();

        if ($authenticated) {
            return true;
        }

        // Using refresh token to get new access token
        try {
            $refreshToken = $this->storage->getRefreshToken();
            if (!empty($refreshToken)) {
                $formParams = [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken
                ];

                $token = $this->fetchToken($formParams);
                if (!empty($token) && $token->expires_in > 0) {
                    return true;
                }
            }
        } catch (\Throwable $th) {
        }

        return false;
    }

    
    /**
     * Gets the API configuration with the current access token.
     *
     * @return \Kinde\KindeSDK\Configuration
     * @throws Exception If the access token is not found
     */
    private function getApiConfig()
    {
        $token = $this->storage->getAccessToken();
        if (empty($token)) {
            throw new Exception('Access token not found');
        }

        $config = new \Kinde\KindeSDK\Configuration();
        $config->setHost($this->domain);
        $config->setAccessToken($token);
        
        return $config;
    }

    /**
     * Gets user profile from API.
     *
     * @return UserProfileV2
     * @throws Exception If the API request fails
     */
    private function getUserProfileFromApi()
    {
        $config = $this->getApiConfig();
        $oauthApi = new OAuthApi(null, $config);
        
        try {
            return $oauthApi->getUserProfileV2();
        } catch (\Kinde\KindeSDK\ApiException $e) {
            throw new Exception('Failed to get user profile: ' . $e->getMessage());
        }
    }

    /**
     * Gets permissions from API.
     *
     * @return array An associative array containing the organization code and permissions
     * @throws Exception If the API request fails
     */
    protected function getPermissionsFromApi()
    {
        $config = $this->getApiConfig();
        $permissionsApi = new PermissionsApi(null, $config);
        
        try {
            $permissions = $permissionsApi->getUserPermissions();
            $data = $permissions->getData();
            return [
                'orgCode' => $data->getOrgCode(),
                'permissions' => array_map(fn($permission) => $permission->getKey(), $data->getPermissions() ?? [])
            ];
        } catch (\Kinde\KindeSDK\ApiException $e) {
            throw new Exception('Failed to get permissions: ' . $e->getMessage());
        }
    }

    /**
     * Gets feature flags from API.
     *
     * @return array An associative array of feature flags
     * @throws Exception If the API request fails
     */
    protected function getFeatureFlagsFromApi()
    {
        $config = $this->getApiConfig();
        $featureFlagsApi = new FeatureFlagsApi(null, $config);
        
        try {
            $featureFlags = $featureFlagsApi->getFeatureFlags();
            return $this->processFeatureFlagsData($featureFlags->getData());
        } catch (\Kinde\KindeSDK\ApiException $e) {
            throw new Exception('Failed to get feature flags: ' . $e->getMessage());
        }
    }

    /**
     * Processes feature flags data into the expected format.
     *
     * @param mixed $data The feature flags data
     * @return array Processed feature flags
     */
    protected function processFeatureFlagsData($data): array
    {
        $flags = [];
        foreach ($data->getFeatureFlags() ?? [] as $flag) {
            $flags[$flag->getKey()] = [
                'v' => $flag->getValue(),
                't' => $this->getFlagType($flag->getType())
            ];
        }
        return $flags;
    }

    /**
     * Converts flag type string to internal type code.
     *
     * @param string $type The flag type string
     * @return string The internal type code
     */
    protected function getFlagType(string $type): string
    {
        return match ($type) {
            'boolean' => 'b',
            'string' => 's',
            'integer' => 'i',
            default => 's'
        };
    }

    /**
     * Retrieves the claims from the specified token type.
     *
     * @param string $tokenType The type of token to retrieve claims from (access_token or id_token).
     *
     * @throws InvalidArgumentException If an invalid token type is provided.
     * @throws Exception If the token is empty or missing authentication credentials.
     *
     * @return mixed The parsed claims from the token.
     */
    private function getClaims(string $tokenType = TokenType::ACCESS_TOKEN)
    {
        if (!in_array($tokenType, [TokenType::ACCESS_TOKEN, TokenType::ID_TOKEN])) {
            throw new InvalidArgumentException('Please provide valid token (access_token or id_token) to get claim');
        }

        $token = $tokenType === TokenType::ACCESS_TOKEN ? $this->storage->getAccessToken() : $this->storage->getIdToken();
        if (empty($token)) {
            throw new Exception('Request is missing required authentication credential');
        }

        return Utils::parseJWT($token);
    }

    private function cleanStorage()
    {
        $this->storage->clear();
    }

    private function getProtocol()
    {
        if (!empty($this->protocol)) {
            return $this->protocol;
        }

        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    }
    
    /**
     * Checks the authentication state against the provided state from the server.
     *
     * @param string $stateServer The state received from the server.
     *
     * @throws OAuthException If the authentication state is empty or does not match the provided state.
     */
    private function checkStateAuthentication(string $stateServer)
    {
        $storageOAuthState = $this->storage->getState();

        if (empty($storageOAuthState) || $stateServer != $storageOAuthState) {
            throw new OAuthException("Authentication failed because it tries to validate state");
        }
    }

    /**
     * Retrieves the corresponding grant type value based on the provided grant type.
     *
     * @param string $grantType The grant type.
     *
     * @throws InvalidArgumentException If an invalid grant type is provided.
     *
     * @return string The corresponding grant type value.
     */
    private function getGrantType(string $grantType)
    {
        switch ($grantType) {
            case GrantType::authorizationCode:
            case GrantType::PKCE:
                return 'authorization_code';
            case GrantType::clientCredentials:
                return 'client_credentials';
            default:
                throw new InvalidArgumentException("Please provide correct grant_type");
                break;
        }
    }

    /**
     * Clears the cached JWKS data.
     * This can be useful when JWKS keys are rotated or for testing purposes.
     *
     * @return void
     */
    public function clearJwksCache()
    {
        $this->storage->clearCachedJwks();
    }



    /**
     * Retrieves user roles from token or API.
     *
     * @param bool|null $forceApi Force API call instead of using token data
     * @return array An associative array containing roles
     * @throws Exception If the API request fails
     */
    public function getRoles(?bool $forceApi = null): array
    {
        $useApi = $forceApi ?? $this->forceApi;
        
        if ($useApi) {
            return $this->getRolesFromApi();
        }

        $claims = $this->getClaims();
        $roles = $claims['roles'] ?? [];
        
        // Convert roles to consistent format if they're just strings
        return array_map(function($role) {
            if (is_string($role)) {
                return ['key' => $role, 'id' => null, 'name' => $role];
            }
            return $role;
        }, $roles);
    }

    /**
     * Gets user roles from API.
     *
     * @return array An array of role objects
     * @throws Exception If the API request fails
     */
    private function getRolesFromApi(): array
    {
        $config = $this->getApiConfig();
        $rolesApi = new \Kinde\KindeSDK\Api\Frontend\RolesApi(null, $config);
        
        try {
            $response = $rolesApi->getUserRoles();
            $data = $response->getData();
            $roles = $data->getRoles() ?? [];
            
            return array_map(function($role) {
                return [
                    'id' => $role->getId(),
                    'key' => $role->getKey(),
                    'name' => $role->getName()
                ];
            }, $roles);
        } catch (\Kinde\KindeSDK\ApiException $e) {
            throw new Exception('Failed to get roles: ' . $e->getMessage());
        }
    }

    /**
     * Check if the user has specific roles.
     *
     * @param array $roles Array of role keys or role condition objects
     * @param bool|null $forceApi Force API call instead of using token data
     * @return bool True if user has all specified roles
     */
    public function hasRoles(array $roles = [], ?bool $forceApi = null): bool
    {
        // Match js-utils parameter validation
        if (empty($roles)) {
            return true;
        }

        try {
            $userRoles = $this->getRoles($forceApi);
            $userRoleKeys = array_column($userRoles, 'key');

            // Check all roles - optimize for early exit
            foreach ($roles as $role) {
                if (is_string($role)) {
                    if (!in_array($role, $userRoleKeys)) {
                        return false;
                    }
                } elseif ($this->isCustomRoleCondition($role)) {
                    $matchingRole = $this->findMatchingRole($userRoles, $role['role']);
                    if (!$matchingRole || !call_user_func($role['condition'], $matchingRole)) {
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            // Follow js-utils pattern: log error and return false (graceful failure)
            error_log("[hasRoles] Error getting roles: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the user has specific permissions.
     *
     * @param array $permissions Array of permission keys or permission condition objects
     * @param bool|null $forceApi Force API call instead of using token data
     * @return bool True if user has all specified permissions
     */
    public function hasPermissions(array $permissions = [], ?bool $forceApi = null): bool
    {
        if (empty($permissions)) {
            return true;
        }

        try {
            $useApi = $forceApi ?? $this->forceApi;
            $permissionData = $useApi ? $this->getPermissionsFromApi() : $this->getPermissions();
            $userPermissions = $permissionData['permissions'] ?? [];
            $orgCode = $permissionData['orgCode'] ?? null;

            foreach ($permissions as $permission) {
                if (is_string($permission)) {
                    if (!in_array($permission, $userPermissions)) {
                        return false;
                    }
                } elseif ($this->isCustomPermissionCondition($permission)) {
                    if (!in_array($permission['permission'], $userPermissions)) {
                        return false;
                    }
                    if (!call_user_func($permission['condition'], [
                        'permissionKey' => $permission['permission'],
                        'orgCode' => $orgCode
                    ])) {
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("[hasPermissions] Error getting permissions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the user has specific feature flags.
     *
     * @param array $featureFlags Array of feature flag keys or flag condition objects
     * @param bool|null $forceApi Force API call instead of using token data
     * @return bool True if user has all specified feature flags
     */
    public function hasFeatureFlags(array $featureFlags = [], ?bool $forceApi = null): bool
    {
        if (empty($featureFlags)) {
            return true;
        }

        try {
            $useApi = $forceApi ?? $this->forceApi;
            // Use consistent method pattern instead of direct getClaim call
            $flags = $useApi
                ? $this->getFeatureFlagsFromApi()
                : ($this->getClaim('feature_flags')['value'] ?? []);
            if (!is_array($flags)) {
                $flags = [];
            }

            foreach ($featureFlags as $featureFlag) {
                if (is_string($featureFlag)) {
                    if (!array_key_exists($featureFlag, $flags)) {
                        return false;
                    }
                } elseif ($this->isCustomFeatureFlagCondition($featureFlag)) {
                    $flagKey = $featureFlag['flag'];
                    if (!array_key_exists($flagKey, $flags)) {
                        return false;
                    }
                    
                    // Value-specific check
                    if (array_key_exists('value', $featureFlag)) {
                        $flagData = $flags[$flagKey];
                        $flagValue = is_array($flagData) ? $flagData['v'] : $flagData;
                        if ($flagValue !== $featureFlag['value']) {
                            return false;
                        }
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("[hasFeatureFlags] Error getting feature flags: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the user has specific billing entitlements.
     *
     * @param array $billingEntitlements Array of entitlement keys or entitlement condition objects
     * @return bool True if user has all specified entitlements
     */
    public function hasBillingEntitlements(array $billingEntitlements = []): bool
    {
        if (empty($billingEntitlements)) {
            return true;
        }

        try {
            $userEntitlements = $this->getAllEntitlements();
            $entitlementKeys = array_map(fn($entitlement) => $entitlement->getFeatureKey(), $userEntitlements);

            foreach ($billingEntitlements as $entitlement) {
                if (is_string($entitlement)) {
                    if (!in_array($entitlement, $entitlementKeys)) {
                        return false;
                    }
                } elseif ($this->isCustomEntitlementCondition($entitlement)) {
                    $matchingEntitlement = $this->findMatchingEntitlement($userEntitlements, $entitlement['entitlement']);
                    if (!$matchingEntitlement || !call_user_func($entitlement['condition'], $matchingEntitlement)) {
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("[hasBillingEntitlements] Error getting entitlements: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unified method to check multiple authorization conditions.
     *
     * @param array $conditions Array containing roles, permissions, featureFlags, and/or billingEntitlements
     * @param bool|array|null $forceApi Boolean to force all API calls, or array to specify per-type
     * @return bool True if user has all specified conditions
     * @throws Exception If any API request fails
     */
    public function has(array $conditions = [], $forceApi = null): bool
    {
        if (empty($conditions)) {
            return true;
        }

        // Parse forceApi parameter
        $forceApiSettings = $this->parseForceApiParameter($forceApi);

        // Early exit pattern for better performance - avoid unnecessary API calls
        if (isset($conditions['roles']) &&
            !$this->hasRoles($conditions['roles'], $forceApiSettings['roles'] ?? null)) {
            return false;
        }
        
        if (isset($conditions['permissions']) &&
            !$this->hasPermissions($conditions['permissions'], $forceApiSettings['permissions'] ?? null)) {
            return false;
        }
        
        if (isset($conditions['featureFlags']) &&
            !$this->hasFeatureFlags($conditions['featureFlags'], $forceApiSettings['featureFlags'] ?? null)) {
            return false;
        }
        
        if (isset($conditions['billingEntitlements']) &&
            !$this->hasBillingEntitlements($conditions['billingEntitlements'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Parse forceApi parameter for the has method.
     *
     * @param bool|array|null $forceApi Force API parameter
     * @return array Parsed forceApi settings
     */
    private function parseForceApiParameter($forceApi): array
    {
        if (is_bool($forceApi)) {
            return [
                'roles' => $forceApi,
                'permissions' => $forceApi,
                'featureFlags' => $forceApi,
                'billingEntitlements' => true // Always use API for billing entitlements
            ];
        }

        if (is_array($forceApi)) {
            return array_merge([
                'roles' => null,
                'permissions' => null,
                'featureFlags' => null,
                'billingEntitlements' => true // Always use API for billing entitlements
            ], $forceApi);
        }

        return [
            'roles' => null,
            'permissions' => null,
            'featureFlags' => null,
            'billingEntitlements' => true
        ];
    }

    /**
     * Helper method to check if a role condition is a custom condition.
     * Matches js-utils isCustomRolesCondition pattern.
     *
     * @param mixed $role The role to check
     * @return bool True if it's a custom condition
     */
    private function isCustomRoleCondition($role): bool
    {
        return is_array($role) && 
               isset($role['role']) && 
               isset($role['condition']) && 
               is_callable($role['condition']);
    }

    /**
     * Helper method to check if a permission condition is a custom condition.
     * Matches js-utils isCustomPermissionsCondition pattern.
     *
     * @param mixed $permission The permission to check
     * @return bool True if it's a custom condition
     */
    private function isCustomPermissionCondition($permission): bool
    {
        return is_array($permission) && 
               isset($permission['permission']) && 
               isset($permission['condition']) && 
               is_callable($permission['condition']);
    }

    /**
     * Helper method to check if a feature flag condition is a custom condition.
     *
     * @param mixed $featureFlag The feature flag to check
     * @return bool True if it's a custom condition
     */
    private function isCustomFeatureFlagCondition($featureFlag): bool
    {
        return is_array($featureFlag) &&
               array_key_exists('flag', $featureFlag);
    }

    /**
     * Helper method to check if an entitlement condition is a custom condition.
     *
     * @param mixed $entitlement The entitlement to check
     * @return bool True if it's a custom condition
     */
    private function isCustomEntitlementCondition($entitlement): bool
    {
        return is_array($entitlement) && 
               isset($entitlement['entitlement']) && 
               isset($entitlement['condition']) && 
               is_callable($entitlement['condition']);
    }

    /**
     * Find matching role by key.
     *
     * @param array $userRoles Array of user roles
     * @param string $roleKey Role key to find
     * @return array|null The matching role or null
     */
    private function findMatchingRole(array $userRoles, string $roleKey): ?array
    {
        foreach ($userRoles as $userRole) {
            if ($userRole['key'] === $roleKey) {
                return $userRole;
            }
        }
        return null;
    }

    /**
     * Find matching entitlement by key.
     *
     * @param array $userEntitlements Array of user entitlements
     * @param string $entitlementKey Entitlement key to find
     * @return object|null The matching entitlement or null
     */
    private function findMatchingEntitlement(array $userEntitlements, string $entitlementKey)
    {
        foreach ($userEntitlements as $userEntitlement) {
            if ($userEntitlement->getFeatureKey() === $entitlementKey) {
                return $userEntitlement;
            }
        }
        return null;
    }

    /**
     * Get all entitlements for the authenticated user, handling pagination automatically.
     *
     * @return array All entitlements
     * @throws Exception If the user is not authenticated or API request fails
     */
    public function getAllEntitlements(): array
    {
        if (!$this->isAuthenticated()) {
            throw new Exception('User must be authenticated to get entitlements');
        }

        $token = $this->storage->getAccessToken();
        if (empty($token)) {
            throw new Exception('Access token not found');
        }

        $allEntitlements = [];
        $startingAfter = null;
        
        do {
            $response = $this->getEntitlementsFromApi(null, $startingAfter, $token);
            $entitlements = $response->getData()->getEntitlements() ?? [];
            $allEntitlements = array_merge($allEntitlements, $entitlements);
            
            $metadata = $response->getMetadata();
            $hasMore = $metadata->getHasMore();
            
            if ($hasMore && count($entitlements) > 0) {
                $startingAfter = $metadata->getNextPageStartingAfter();
            } else {
                $startingAfter = null;
            }
        } while ($hasMore && $startingAfter);
        
        return $allEntitlements;
    }

    /**
     * Get a specific entitlement by key.
     *
     * @param string $key The entitlement key to retrieve
     * @return GetEntitlementsResponseDataEntitlementsInner|null The entitlement or null if not found
     * @throws Exception If the user is not authenticated or API request fails
     */
    public function getEntitlement(string $key): ?GetEntitlementsResponseDataEntitlementsInner
    {
        $entitlements = $this->getAllEntitlements();
        
        foreach ($entitlements as $entitlement) {
            if ($entitlement->getFeatureKey() === $key) {
                return $entitlement;
            }
        }
        
        return null;
    }

    /**
     * Check if the user has a specific entitlement.
     *
     * @param string $key The entitlement key to check
     * @return bool True if the user has the entitlement, false otherwise
     * @throws Exception If the user is not authenticated or API request fails
     */
    public function hasEntitlement(string $key): bool
    {
        return $this->getEntitlement($key) !== null;
    }

    /**
     * Get the maximum limit for a specific entitlement.
     *
     * @param string $key The entitlement key
     * @return int|null The maximum limit or null if not found
     * @throws Exception If the user is not authenticated or API request fails
     */
    public function getEntitlementLimit(string $key): ?int
    {
        $entitlement = $this->getEntitlement($key);
        return $entitlement ? $entitlement->getEntitlementLimitMax() : null;
    }

    /**
     * Get entitlements from the frontend API.
     *
     * @param int|null $pageSize Number of results per page (uses API default if null)
     * @param string|null $startingAfter The ID to start after for pagination
     * @param string $token The access token
              * @return \Kinde\KindeSDK\Model\Frontend\GetEntitlementsResponse
     * @throws Exception If the API request fails
     */
    private function getEntitlementsFromApi(?int $pageSize, ?string $startingAfter, string $token)
    {
        $config = new \Kinde\KindeSDK\Configuration();
        $config->setHost($this->domain);
        $config->setAccessToken($token);
        
        $billingApi = new BillingApi(null, $config);
        
        try {
            return $billingApi->getEntitlements($pageSize, $startingAfter);
        } catch (\Kinde\KindeSDK\ApiException $e) {
            throw new Exception('Failed to get entitlements: ' . $e->getMessage());
        }
    }

    /**
     * Generates a URL to the user profile portal
     *
     * @param string $returnUrl URL to redirect to after completing the profile flow
     * @param string $subNav Sub-navigation section to display (defaults to 'profile')
     *
     * @throws Exception If the access token is not found or if the API request fails
     * @throws InvalidArgumentException If the returnUrl is not an absolute URL
     *
     * @return array An array containing the generated URL
     */
    public function generatePortalUrl(string $returnUrl, string $subNav = PortalPage::PROFILE)
    {
        $token = $this->storage->getAccessToken();
        if (empty($token)) {
            throw new Exception('generatePortalUrl: Access Token not found');
        }

        if (!filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('generatePortalUrl: returnUrl must be an absolute URL');
        }

        $params = [
            'sub_nav' => $subNav,
            'return_url' => $returnUrl
        ];

        $client = new Client();
        try {
            $response = $client->request('GET', $this->domain . '/account_api/v1/portal_link', [
                'query' => $params,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Kinde-SDK' => 'PHP/1.2'
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($result['url']) || !is_string($result['url'])) {
                throw new Exception('Invalid URL received from API');
            }

            if (!filter_var($result['url'], FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid URL format received from API: ' . $result['url']);
            }

            return [
                'url' => $result['url']
            ];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new Exception('Failed to fetch profile URL: ' . $e->getMessage());
        }
    }
}