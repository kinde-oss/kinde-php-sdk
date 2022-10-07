<?php

namespace Kinde\KindeSDK;

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

use Exception;
use GuzzleHttp\Client;
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
    public $domain;

    /**
     * @var string This is the redirect URI that you provided when you registered your application.
     */
    public $redirectUri;

    /**
     * @var string A variable that is used to store the client ID of the application.
     */
    public $clientId;

    /**
     * @var string This is the client secret of your application.
     */
    public $clientSecret;

    /**
     * @var string This is the authorization endpoint of the API.
     */
    public $authorizationEndpoint;

    /**
     * @var string This is the token endpoint of the API.
     */
    public $tokenEndpoint;

    /** 
     * @var string Used to store the logout endpoint of the API. 
     */
    public $logoutEndpoint;

    /** 
     * @var string Used to store the login method.
     */
    public $grantType;

    /**
     * @var string This is a variable that is used to store the scopes that you want to request.
     */
    public $scopes;

    function __construct(string $domain, string $redirectUri, string $clientId, string $clientSecret, string $grantType = '')
    {
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

        $this->grantType = $grantType;
        $this->authorizationEndpoint = $this->domain . '/oauth2/auth';
        $this->tokenEndpoint = $this->domain . '/oauth2/token';
        $this->logoutEndpoint = $this->domain . '/logout';
    }

    /**
      * A function that is used to login to the API.
      * 
      * @param string grantType The type of grant you want to use.
      * @param string state This is an optional parameter that you can use to pass a value to the
      * authorization server. The authorization server will return this value back to you in the
      * response.
      * @param string scopes The scopes you want to request.
      * 
      * @return array The login method returns an array with the following keys:
      */
    public function login(string $grantType = '', string $state = '', string $scopes = 'openid offline')
    {
        $this->cleanSession();
        try {
            $this->scopes = $scopes;
            $this->$state = $state;
            if (empty($grantType) && empty($this->grantType)) {
                throw new Exception("Please provide grant_type");
            }

            if (!empty($grantType)) {
                $this->grantType = $grantType;
            }
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
                    throw new Exception("Please provide correct grant_type");
                    break;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * It redirects the user to the authorization endpoint with the client id, redirect uri, a random
     * state, and the start page set to registration
     */
    public function register()
    {
        $this->grantType = 'authorization_code';
        $auth = new PKCE();
        return $auth->login($this, 'registration');
    }

   /**
    * It takes the grant type as parameter, and returns the token
    * 
    * @param string grantType The type of grant you want to use.
    * 
    * @return object The response is a JSON object containing the access token, the refresh token, the token
    * type, and the expiration time.
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
        return $tokenDecode;
    }

    /**
     * It unset's the token from the session and redirects the user to the logout endpoint
     */
    public function logout()
    {

        unset($_SESSION['token']);
        
        // Just left empty, we will implement later
        $searchParams = [];
        header('Location: '. $this->logoutEndpoint . '?' . http_build_query($searchParams));
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
        switch($grantType) {
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

    private function cleanSession()
    {
        unset($_SESSION['oauthState']);
        unset($_SESSION['oauthCodeVerifier']);
    }
}
