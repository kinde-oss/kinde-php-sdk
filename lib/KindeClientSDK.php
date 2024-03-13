<?php

namespace Kinde\KindeSDK;

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

use Exception;
use InvalidArgumentException;
use GuzzleHttp\Client;
use Kinde\KindeSDK\Sdk\OAuth2\PKCE;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Enums\TokenType;
use Kinde\KindeSDK\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Sdk\OAuth2\ClientCredentials;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\Sdk\Storage\Storage;
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
    public string $redirectUri;

    /**
     * @var string This is the logout redirect URI that you provided when you registered your application.
     */
    public string $logoutRedirectUri;

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

    public $storage;

    function __construct(
        string $domain,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        string $grantType,
        string $logoutRedirectUri,
        string $scopes = 'openid profile email offline',
        array $additionalParameters = [],
        string $protocol = ""
    ) {
        $isNotCCGrantType = $grantType !== GrantType::clientCredentials;

        if (empty($domain)) {
            throw new InvalidArgumentException("Please provide domain");
        }
        if (!Utils::validationURL($domain)) {
            throw new InvalidArgumentException("Please provide valid domain");
        }
        $this->domain = $domain;

        if ($isNotCCGrantType && empty($redirectUri)) {
            throw new InvalidArgumentException("Please provide redirect_uri");
        }
        if ($isNotCCGrantType && !Utils::validationURL($redirectUri)) {
            throw new InvalidArgumentException("Please provide valid redirect_uri");
        }
        $this->redirectUri = $redirectUri;

        if (empty($clientSecret)) {
            throw new InvalidArgumentException("Please provide client_secret");
        }
        $this->clientSecret = $clientSecret;

        if (empty($clientId)) {
            throw new InvalidArgumentException("Please provide client_id");
        }
        $this->clientId = $clientId;

        if (empty($grantType)) {
            throw new InvalidArgumentException("Please provide grant_type");
        }
        $this->grantType = $grantType;

        if ($isNotCCGrantType && empty($logoutRedirectUri)) {
            throw new InvalidArgumentException("Please provide logout_redirect_uri");
        }
        if ($isNotCCGrantType && !Utils::validationURL($logoutRedirectUri)) {
            throw new InvalidArgumentException("Please provide valid logout_redirect_uri");
        }

        $this->additionalParameters = Utils::checkAdditionalParameters($additionalParameters);

        $this->logoutRedirectUri = $logoutRedirectUri;
        $this->scopes = $scopes;
        $this->protocol = $protocol;

        // Other endpoints
        $this->authorizationEndpoint = $this->domain . '/oauth2/auth';
        $this->tokenEndpoint = $this->domain . '/oauth2/token';
        $this->logoutEndpoint = $this->domain . '/logout';

        $this->storage = Storage::getInstance();
        $this->storage->setJwksUrl($this->domain . '/.well-known/jwks.json');
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
        $this->cleanStorage();
        try {
            switch ($this->grantType) {
                case GrantType::clientCredentials:
                    $auth = new ClientCredentials();
                    return $auth->authenticate($this, $additionalParameters);
                case GrantType::authorizationCode:
                    $auth = new AuthorizationCode();
                    return $auth->authenticate($this, $additionalParameters);
                case GrantType::PKCE:
                    $auth = new PKCE();
                    return $auth->authenticate($this, 'login', $additionalParameters);
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
        $this->grantType = 'authorization_code';

        $auth = new PKCE();
        return $auth->authenticate($this, 'registration', $additionalParameters);
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
     * Retrieves a specific claim from the token.
     *
     * @param string $keyName   The name of the claim to retrieve.
     * @param string $tokenType The type of token to retrieve the claim from (optional, defaults to TokenType::ACCESS_TOKEN).
     *
     * @return array An associative array containing the name and value of the claim, or null if the claim doesn't exist.
     */
    public function getClaim(string $keyName, string $tokenType = TokenType::ACCESS_TOKEN)
    {
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
     * Retrieves the organization code and permissions from the claims.
     *
     * @return array An associative array containing the organization code and permissions.
     */
    public function getPermissions()
    {
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
    public function getFlag(string $flagName, array $options = [], string $flagType = null)
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

        $requestType = Utils::$listType[$flagType];
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
     * @return mixed|array|null The feature flags or a specific feature flag value.
     */
    private function getFeatureFlags(string $name = null)
    {
        $flags = self::getClaim('feature_flags')['value'];

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
}
