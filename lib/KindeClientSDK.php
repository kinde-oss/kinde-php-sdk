<?php

namespace Kinde\KindeSDK;

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

use Exception;
use InvalidArgumentException;
use GuzzleHttp\Client;
use Kinde\KindeSDK\Sdk\Enums\AuthStatus;
use Kinde\KindeSDK\Sdk\OAuth2\PKCE;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Sdk\OAuth2\ClientCredentials;
use Kinde\KindeSDK\Sdk\Utils\Utils;

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

    /* This is a variable that is used to store the status of the authorization. */
    public string $authStatus;

    /* This is a additionalParameters data. */
    public array $additionalParameters;

    /**
     * @var string This is a variable that is used to store the scopes that you want to request.
     */
    public string $scopes;

    function __construct(
        string $domain,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        string $grantType,
        string $logoutRedirectUri,
        string $scopes = 'openid offline',
        array $additionalParameters = []
    ) {
        if (empty($domain)) {
            throw new InvalidArgumentException("Please provide domain");
        }
        if (!Utils::validationURL($domain)) {
            throw new InvalidArgumentException("Please provide valid domain");
        }
        $this->domain = $domain;

        if (empty($redirectUri)) {
            throw new InvalidArgumentException("Please provide redirect_uri");
        }
        if (!Utils::validationURL($redirectUri)) {
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

        if (empty($logoutRedirectUri)) {
            throw new InvalidArgumentException("Please provide logout_redirect_uri");
        }
        if (!Utils::validationURL($logoutRedirectUri)) {
            throw new InvalidArgumentException("Please provide valid logout_redirect_uri");
        }

        $this->additionalParameters = Utils::checkAdditionalParameters($additionalParameters);

        $this->logoutRedirectUri = $logoutRedirectUri;
        $this->scopes = $scopes;
        // Other endpoints
        $this->authorizationEndpoint = $this->domain . '/oauth2/auth';
        $this->tokenEndpoint = $this->domain . '/oauth2/token';
        $this->logoutEndpoint = $this->domain . '/logout';
        $this->authStatus = AuthStatus::UNAUTHENTICATED;
    }

    public function __get($key)
    {
        if (property_exists($this, $key)) {
            if ($key === 'isAuthenticated') {
                return $this->isAuthenticated();
            }
            return $this->$key;
        }
    }

    /**
     * A function that is used to login to the API.
     *
     * @param array additional The array includes params to pass api.
     * @param string scopes The scopes you want to request.
     * 
     * @return array The login method returns an array with the following keys:
     */
    public function login(
        array $additionalParameters = []
    ) {
        $this->cleanSession();
        try {
            $this->updateAuthStatus(AuthStatus::AUTHENTICATING);
            switch ($this->grantType) {
                case GrantType::clientCredentials:
                    $auth = new ClientCredentials();
                    return $auth->login($this, $additionalParameters);
                case GrantType::authorizationCode:
                    $auth = new AuthorizationCode();
                    return $auth->login($this, $additionalParameters);
                case GrantType::PKCE:
                    $auth = new PKCE();
                    return $auth->login($this, 'login', $additionalParameters);
                default:
                    $this->updateAuthStatus(AuthStatus::UNAUTHENTICATED);
                    throw new InvalidArgumentException("Please provide correct grant_type");
                    break;
            }
        } catch (\Throwable $th) {
            $this->updateAuthStatus(AuthStatus::UNAUTHENTICATED);
            throw $th;
        }
    }

    /**
     * It redirects the user to the authorization endpoint with the client id, redirect uri, a random
     * state, and the start page set to registration
     *
     * @param array additional The array includes params to pass api.
     */
    public function register(array $additionalParameters = [])
    {
        $this->updateAuthStatus(AuthStatus::AUTHENTICATING);
        $this->grantType = 'authorization_code';
        $auth = new PKCE();
        return $auth->login($this, 'registration', $additionalParameters);
    }

    /**
     * It redirects the user to the authorization endpoint with the client id, redirect uri, a random
     * state, and the start page set to registration and allow an organization to be created
     *
     *  @param array additional The array includes params to pass api.
     */
    public function createOrg(array $additionalParameters = [])
    {
        $additionalParameters['is_create_org'] = 'true';
        return $this->register($additionalParameters);
    }

    /**
     * It takes the grant type as parameter, and returns the token
     * 
     * @param array authServerParams The call back params from auth server.
     */
    public function getToken()
    {
        $newGrantType = $this->getGrantType($this->grantType);
        $formParams = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => $newGrantType,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code'
        ];
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
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
        $codeVerifier = $_SESSION['oauthCodeVerifier'] ?? "";
        if (!empty($codeVerifier)) {
            $formParams['code_verifier'] = $codeVerifier;
        } else if ($this->grantType == GrantType::PKCE) {
            throw new InvalidArgumentException('Not found code_verifier');
        }
        $client = new Client();
        $response =
            $client->request('POST', $this->tokenEndpoint, [
                'form_params' => $formParams
            ]);
        $token = $response->getBody()->getContents();
        $_SESSION['token'] = $token;
        $tokenDecode = json_decode($token);
        $this->saveDataToSession($tokenDecode);
        $this->updateAuthStatus(AuthStatus::AUTHENTICATED);
        return $tokenDecode;
    }

    private function saveDataToSession($token)
    {
        $_SESSION['login_time_stamp'] = time();
        $_SESSION['accessToken'] = $token->access_token ?? '';
        $_SESSION['id_token'] = $token->id_token ?? '';
        $_SESSION['expires_in'] = $token->expires_in ?? 0;
        $payload = Utils::parseJWT($token->id_token ?? '');
        $user = [
            'id' => $payload->sub,
            'given_name' => $payload->given_name,
            'family_name' => $payload->family_name,
            'email' => $payload->email
        ];
        $_SESSION['user'] = json_encode($user);
    }

    /**
     * It returns user's information after successful authentication
     *
     * @return array The response is a array containing id, given_name, family_name and email.
     */
    public function getUserDetails()
    {
        return json_decode($_SESSION['user'] ?? [], true);
    }

    /**
     * It unset's the token from the session and redirects the user to the logout endpoint
     */
    public function logout()
    {
        $this->cleanSession();
        $this->updateAuthStatus(AuthStatus::UNAUTHENTICATED);
        $searchParams = [
            'redirect' => $this->logoutRedirectUri
        ];
        header('Location: ' . $this->logoutEndpoint . '?' . http_build_query($searchParams));
        exit();
    }

    /**
     * This function takes a grant type and returns the grant type in the format that the API expects
     * 
     * @param string grantType The type of grant you want to use.
     * 
     * @return The grant type is being returned.
     */
    public function getGrantType(string $grantType)
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
     * It checks user is logged.
     *
     * @return bool The response is a bool, which check user logged or not
     */
    public function isAuthenticated()
    {
        if (empty($_SESSION["login_time_stamp"]) || empty($_SESSION["expires_in"])) {
            return false;
        }
        return time() - $_SESSION["login_time_stamp"] < $_SESSION["expires_in"];
    }

    public function getClaims(string $tokenType = 'accessToken')
    {
        if (!in_array($tokenType, ['accessToken', 'id_token'])) {
            throw new InvalidArgumentException('tokenType');
        }
        $token = $_SESSION[$tokenType] ?? '';
        if (empty($token)) {
            throw new Exception('Request is missing required authentication credential');
        }
        return Utils::parseJWT($token);
    }

    public function getClaim(string $keyName, string $tokenType = 'accessToken')
    {
        $data = (array) self::getClaims($tokenType);
        return $data[$keyName] ?? '';
    }

    public function getPermissions()
    {
        $claims = self::getClaims();
        return [
            'orgCode' => $claims->org_code,
            'permissions' => $claims->permissions
        ];
    }

    public function getPermission(string $permission)
    {
        $allClaims = self::getClaims();
        $permissions = (array) $allClaims->permissions;
        return [
            'orgCode' => $allClaims->org_code,
            'isGranted' => empty($permissions) ? false : $permissions[$permission]
        ];
    }

    public function getOrganization()
    {
        return [
            'orgCode' => self::getClaim('org_code')
        ];
    }

    public function getUserOrganizations()
    {
        return [
            'orgCodes' => self::getClaim('org_codes', 'id_token')
        ];
    }

    public function getAuthStatus()
    {
        return $_SESSION['auth_status'];
    }

    private function updateAuthStatus(string $_authStatus)
    {
        $_SESSION['auth_status'] = $_authStatus;
        $this->authStatus = $_authStatus;
    }

    private function cleanSession()
    {
        unset($_SESSION['token']);
        unset($_SESSION['auth_status']);
        unset($_SESSION['oauthState']);
        unset($_SESSION['oauthCodeVerifier']);
        unset($_SESSION['expires_in']);
        unset($_SESSION['login_time_stamp']);
        unset($_SESSION['user']);
    }

    private function checkStateAuthentication(string $stateServer)
    {
        if (empty($_SESSION['oauthState']) || $stateServer != $_SESSION['oauthState']) {
            throw new OAuthException("Authentication failed because it tries to validate state");
        }
    }
}
