<?php

namespace Kinde\KindeSDK;

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

use Exception;
use GuzzleHttp\Client;
use Kinde\KindeSDK\Sdk\Enums\Additional;
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

    /**
     * @var string This is a variable that is used to store the scopes that you want to request.
     */
    public string $scopes;

    /* This is a variable that is used to store the status of the authorization. */
    public string $authStatus;

    /* This is a additional data. */
    public array $additional;

    /*This is an optional parameter that you can use to pass a value to the authorization server. */
    public string $state;

    function __construct(
        string $domain,
        string $redirectUri,
        string $clientId,
        string $clientSecret,
        string $grantType,
        string $logoutRedirectUri,
        array $additional = []
    ) {
        if (empty($domain)) {
            throw new Exception("Please provide domain");
        }
        if (!Utils::validationURL($domain)) {
            throw new Exception("Please provide valid domain");
        }
        $this->domain = $domain;

        if (empty($redirectUri)) {
            throw new Exception("Please provide redirect_uri");
        }
        if (!Utils::validationURL($redirectUri)) {
            throw new Exception("Please provide valid redirect_uri");
        }
        $this->redirectUri = $redirectUri;

        if (empty($clientSecret)) {
            throw new Exception("Please provide client_secret");
        }
        $this->clientSecret = $clientSecret;

        if (empty($clientId)) {
            throw new Exception("Please provide client_id");
        }
        $this->clientId = $clientId;

        if (empty($grantType)) {
            throw new Exception("Please provide grant_type");
        }
        $this->grantType = $grantType;

        if (empty($logoutRedirectUri)) {
            throw new Exception("Please provide logout_redirect_uri");
        }
        if (!Utils::validationURL($logoutRedirectUri)) {
            throw new Exception("Please provide valid logout_redirect_uri");
        }

        if (!empty($additional)) {
            $this->validateWithConfig($additional, 'audience');
        }
        $this->additional = $additional;

        $this->logoutRedirectUri = $logoutRedirectUri;
        $this->scopes = 'openid offline';
        // Other endpoints
        $this->authorizationEndpoint = $this->domain . '/oauth2/auth';
        $this->tokenEndpoint = $this->domain . '/oauth2/token';
        $this->logoutEndpoint = $this->domain . '/logout';
        $this->authStatus = AuthStatus::UNAUTHENTICATED;
    }

    /**
     * A function that is used to login to the API.
     *
     * @param array additional The array includes params to pass api.
     * @param string grantType The type of grant you want to use.
     * @param string state This is an optional parameter that you can use to pass a value to the
     * authorization server. The authorization server will return this value back to you in the
     * response.
     * @param string scopes The scopes you want to request.
     * 
     * @return array The login method returns an array with the following keys:
     */
    public function login(
        array $additional = [],
        string $grantType = '',
        string $state = '',
        string $scopes = 'openid profile email offline'
    ) {
        $this->cleanSession();
        try {
            $this->mergeAdditional($additional);
            $this->scopes = $scopes;
            $this->state = $state;
            if (empty($grantType) && empty($this->grantType)) {
                throw new Exception("Please provide grant_type");
            }

            if (!empty($grantType)) {
                $this->grantType = $grantType;
            }
            $this->updateAuthStatus(AuthStatus::AUTHENTICATING);
            switch ($this->grantType) {
                case GrantType::clientCredentials:
                    $auth = new ClientCredentials();
                    return $auth->login($this);
                case GrantType::authorizationCode:
                    $auth = new AuthorizationCode();
                    return $auth->login($this);
                case GrantType::PKCE:
                    $auth = new PKCE();
                    return $auth->login($this);
                default:
                    $this->updateAuthStatus(AuthStatus::UNAUTHENTICATED);
                    throw new Exception("Please provide correct grant_type");
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
    public function register(array $additional = [])
    {
        $this->mergeAdditional($additional);
        $this->updateAuthStatus(AuthStatus::AUTHENTICATING);
        $this->grantType = 'authorization_code';
        $auth = new PKCE();
        return $auth->login($this, 'registration');
    }

    /**
     * It redirects the user to the authorization endpoint with the client id, redirect uri, a random
     * state, and the start page set to registration and allow an organization to be created
     */
    public function createOrg()
    {
        $additional['is_create_org'] = 'true';
        return $this->register($additional);
    }

    /**
     * It takes the grant type as parameter, and returns the token
     * 
     * @param array authServerParams The call back params from auth server.
     * 
     * @return object The response is a JSON object containing the access token, the refresh token, the token
     * type, and the expiration time.
     */
    public function getToken(array $authServerParams)
    {
        $stateServer = $authServerParams['state'] ?? null;
        $this->checkStateAuthentication($stateServer);
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
        $error = $params['error'] ?? '';
        if (!empty($error)) {
            $errorDescription = $params['error_description'] ?? '';
            $msg = !empty($errorDescription) ? $errorDescription : $error;
            throw new Exception($msg);
        }
        $authorizationCode = $params['code'] ?? '';
        if (empty($authorizationCode)) {
            throw new Exception('Not found code param');
        }
        $formParams['code'] = $authorizationCode;
        $codeVerifier = $_SESSION['oauthCodeVerifier'] ?? "";
        if (!empty($codeVerifier)) {
            $formParams['code_verifier'] = $codeVerifier;
        } else if ($this->grantType == GrantType::PKCE) {
            throw new Exception('Not found code_verifier');
        }
        $client = new Client();
        $response =
            $client->request('POST', $this->tokenEndpoint, [
                'form_params' => $formParams
            ]);
        $token = $response->getBody()->getContents();
        $_SESSION['token'] = $token;
        $tokenDecode = json_decode($token);
        $this->setDataToSession($tokenDecode);
        $this->updateAuthStatus(AuthStatus::AUTHENTICATED);
        return $tokenDecode;
    }

    private function setDataToSession($data)
    {
        $_SESSION['login_time_stamp'] = time();
        $_SESSION['expires_in'] = $data->expires_in ?? 0;
        $payload = Utils::parseJWT($data->id_token??'');
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
    public function getUserDetail()
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
                throw new Exception("Please provide correct grant_type");
                break;
        }
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

    /**
     * It checks if the data pass the config
     *
     * @param array data need validate.
     * @param string keyValue It will check only value with the keyValue.
     *
     */
    private function validateWithConfig(array $data, string $keyValue = '')
    {
        $config = Additional::ADDITIONAL;
        $keysAvailable = array_keys($config);
        if ($keyValue) {
            $data = [$keyValue => $data[$keyValue]];
        }
        foreach ($data as $key => $val) {
            if (!in_array($key, $keysAvailable)) {
                throw new Exception("Please provide correct additional, $key");
            }
            if (gettype($val) != $config[$key]) {
                throw new Exception("Please supply a valid $key");
            }
        }
    }

    private function checkStateAuthentication(string $stateServer)
    {
        if (empty($_SESSION['oauthState']) || $stateServer != $_SESSION['oauthState']) {
            throw new Exception("Authentication failed.");
        }
    }

    private function mergeAdditional(array $additional)
    {
        if (empty($additional)) {
            return;
        }
        $this->validateWithConfig($additional);
        $this->additional = array_merge($this->additional, $additional);
    }
}
