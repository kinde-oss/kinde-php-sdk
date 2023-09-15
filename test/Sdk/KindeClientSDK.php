<?php

namespace Kinde\KindeSDK\Test\Sdk;

use Exception;
use InvalidArgumentException;
use GuzzleHttp\Client;
use Kinde\KindeSDK\Test\Sdk\OAuth2\PKCE;
use Kinde\KindeSDK\Test\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Test\Sdk\Enums\TokenType;
use Kinde\KindeSDK\Test\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Test\Sdk\OAuth2\ClientCredentials;
use Kinde\KindeSDK\Test\Sdk\Utils\Utils;
use Kinde\KindeSDK\Test\Sdk\Storage\Storage;
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
    }

    /**
     * A function that is used to login to the API.
     *
     * @param array additionalParameters The array includes params to pass api.
     * @param string scopes The scopes you want to request.
     * 
     * @return The login method returns an array with the following keys:
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
     * It redirects the user to the authorization endpoint with the client id, redirect uri, a random
     * state, and the start page set to registration
     *
     * @param array additionalParameters The array includes params to pass api.
     */
    public function register(array $additionalParameters = [])
    {
        $this->grantType = 'authorization_code';

        $auth = new PKCE();
        return $auth->authenticate($this, 'registration', $additionalParameters);
    }

    /**
     * It redirects the user to the authorization endpoint with the client id, redirect uri, a random
     * state, and the start page set to registration and allow an organization to be created
     *
     *  @param array additionalParameters The array includes params to pass api.
     */
    public function createOrg(array $additionalParameters = [])
    {
        $additionalParameters['is_create_org'] = 'true';
        return $this->register($additionalParameters);
    }

    /**
     * It unset's the token from the storage and redirects the user to the logout endpoint
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
     * It takes the grant type as parameter, and returns the token
     * 
     * @param array authServerParams The call back params from auth server.
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
     * It returns user's information after successful authentication
     *
     * @return array The response is a array containing id, given_name, family_name and email.
     */
    public function getUserDetails()
    {
        return $this->storage->getUserProfile();
    }

    /**
     * Accept a key for a token and returns the claim value.
     * Optional argument to define which token to check - defaults to Access token  - e.g.
     *
     * @param string keyName Accept a key for a token.
     * @param string tokenType Optional argument to define which token to check.
     *
     * @return any The response is a data in token.
     */
    public function getClaim(string $keyName, string $tokenType = TokenType::ACCESS_TOKEN)
    {
        $data = self::getClaims($tokenType);

        return [
            'name' => $keyName,
            'value' => $data[$keyName] ?? null
        ];
    }

    /**
     * Get an array of permissions (from the permissions claim in access token)
     * And also the relevant org code (org_code claim in access token). e.g
     *
     * @return array The response includes orgCode and permissions.
     */
    public function getPermissions()
    {
        $claims = self::getClaims();

        return [
            'orgCode' => $claims['org_code'],
            'permissions' => $claims['permissions']
        ];
    }

    /**
     * Given a permission value, returns if it is granted or not (checks if permission key exists in the permissions claim array)
     * And relevant org code (checking against claim org_code) e.g
     *
     * @return array The response includes orgCode and isGranted.
     */
    public function getPermission(string $permission)
    {
        $allClaims = self::getClaims();
        $permissions = $allClaims['permissions'];

        return [
            'orgCode' => $allClaims['org_code'],
            'isGranted' => in_array($permission, $permissions)
        ];
    }

    /**
     * Gets the org code (and later other org info) (checking against claim org_code)
     *
     * @return array The response is a orgCode.
     */
    public function getOrganization()
    {
        return [
            'orgCode' => self::getClaim('org_code')['value']
        ];
    }
    /**
     * Gets all org code
     *
     * @return array The response is a orgCodes.
     */
    public function getUserOrganizations()
    {
        return [
            'orgCodes' => self::getClaim('org_codes', TokenType::ID_TOKEN)['value']
        ];
    }

    public function getBooleanFlag(string $flagName, $defaultValue = null) // Let's use original default value, do not add type to here
    {
        return self::getFlag($flagName, ['defaultValue' => $defaultValue], 'b');
    }

    public function getStringFlag(string $flagName, $defaultValue = null)
    {
        return self::getFlag($flagName, ['defaultValue' => $defaultValue], 's');
    }

    public function getIntegerFlag(string $flagName, $defaultValue = null)
    {
        return self::getFlag($flagName, ['defaultValue' => $defaultValue], 'i');
    }

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

    private function getFeatureFlags(string $name = null)
    {
        $flags = self::getClaim('feature_flags')['value'];

        if (isset($name) && !empty($flags)) {
            return $flags[$name] ?? null;
        }

        return $flags;
    }

    private function fetchToken($formParams)
    {
        $client = new Client();

        $response =
            $client->request('POST', $this->tokenEndpoint, [
                'form_params' => $formParams,
                'headers' => [
                    'Kinde-SDK' => 'PHP' . '1.2' // current SDK version
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
     * It checks user is logged.
     *
     * @return bool The response is a bool, which check user logged or not
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
                return true;
            }
        } catch (\Throwable $th) {
        }

        return false;
    }

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

    private function checkStateAuthentication(string $stateServer)
    {
        $storageOAuthState = $this->storage->getState();

        if (empty($storageOAuthState) || $stateServer != $storageOAuthState) {
            throw new OAuthException("Authentication failed because it tries to validate state");
        }
    }

    /**
     * This function takes a grant type and returns the grant type in the format that the API expects
     * 
     * @param string grantType The type of grant you want to use.
     * 
     * @return The grant type is being returned.
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
