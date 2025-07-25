<?php

namespace Kinde\KindeSDK\Frameworks\CodeIgniter;

use CodeIgniter\Controller;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\OAuthException;
use Exception;

class KindeAuthController extends Controller
{
    protected KindeClientSDK $kindeClient;

    public function __construct()
    {
        $domain = getenv('KINDE_DOMAIN');
        $redirectUri = getenv('KINDE_REDIRECT_URI');
        $clientId = getenv('KINDE_CLIENT_ID');
        $clientSecret = getenv('KINDE_CLIENT_SECRET');
        
        if (!$domain || !$redirectUri || !$clientId || !$clientSecret) {
            throw new \RuntimeException('Missing required Kinde environment variables');
        }
        
        // Initialize Kinde client (you'd configure this in your service)
        $this->kindeClient = new KindeClientSDK(
            $domain,
            $redirectUri,
            $clientId,
            $clientSecret,
            getenv('KINDE_GRANT_TYPE', 'authorization_code'),
            getenv('KINDE_LOGOUT_REDIRECT_URI'),
            getenv('KINDE_SCOPES', 'openid profile email offline')
        );
    }

    /**
     * Handle login request
     */
    public function login()
    {
        $additionalParams = array_filter(
            $this->request->getGet(['org_code', 'org_name', 'is_create_org']),
            function($v) { return $v !== null && $v !== ''; }
        );
        try {
            $result = $this->kindeClient->login($additionalParams);
            $authUrl = $result->getAuthUrl();
            if (empty($authUrl)) {
                log_message('error', 'ERROR: No auth URL generated. Check your Kinde configuration.');
                log_message('error', print_r($result, true));
                session()->setFlashdata('error', 'Authentication service unavailable. Please try again later.');
                return redirect()->to('/');
            }
            return redirect()->to($authUrl);
        } catch (Exception $e) {
            log_message('error', 'EXCEPTION: ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/');
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback()
    {
        $errorParam = $this->request->getGet('error');
        if ($errorParam) {
            if (strtolower($errorParam) === 'login_link_expired') {
                $reauthState = $this->request->getGet('reauth_state');
                if ($reauthState) {
                    $decodedAuthState = base64_decode($reauthState);
                    try {
                        $reauthStateArr = json_decode($decodedAuthState, true);
                        if ($reauthStateArr && is_array($reauthStateArr)) {
                            $urlParams = http_build_query($reauthStateArr);
                            $loginRoute = base_url('/auth/login');
                            $redirectUrl = $loginRoute . ($urlParams ? ('?' . $urlParams) : '');
                            return redirect()->to($redirectUrl);
                        }
                    } catch (\Exception $ex) {
                        session()->setFlashdata('error', $ex->getMessage() ?: 'Unknown Error parsing reauth state');
                        return redirect()->to('/');
                    }
                }
                // If no reauth_state, just return to login
                return redirect()->to('/auth/login');
            }
            // For other errors, redirect to home with error
            session()->setFlashdata('error', $errorParam);
            return redirect()->to('/');
        }

        try {
            $token = $this->kindeClient->getToken();
            if ($token) {
                // Store user session
                $userDetails = $this->kindeClient->getUserDetails();
                session()->set([
                    'kinde_user' => $userDetails,
                    'kinde_token' => $token,
                    'kinde_authenticated' => true
                ]);
                session()->setFlashdata('success', 'Successfully logged in!');
                return redirect()->to('/dashboard');
            }
        } catch (OAuthException $e) {
            session()->setFlashdata('error', 'Authentication failed: ' . $e->getMessage());
        } catch (Exception $e) {
            session()->setFlashdata('error', 'An error occurred during authentication');
        }
        return redirect()->to('/');
    }

    /**
     * Handle registration request
     */
    public function register()
    {
        $additionalParams = array_filter(
            $this->request->getGet(['org_code', 'org_name', 'is_create_org']),
            function($v) { return $v !== null && $v !== ''; }
        );
        
        try {
            $result = $this->kindeClient->register($additionalParams);
            
            return redirect()->to($result->getAuthUrl());
        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/');
        }
    }

    /**
     * Handle organization creation
     */
    public function createOrg()
    {
        $additionalParams = array_filter(
            $this->request->getGet(['org_name']),
            function($v) { return $v !== null && $v !== ''; }
        );
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            
            return redirect()->to($result->getAuthUrl());
        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/');
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // Clear session
        session()->remove(['kinde_user', 'kinde_token', 'kinde_authenticated']);
        
        // Redirect to Kinde logout
        $this->kindeClient->logout();
        
        // This should not be reached as logout() should exit
        return redirect()->to('/');
    }

    /**
     * Get user info
     */
    public function userInfo()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $userDetails = $this->kindeClient->getUserDetails();
        $permissions = $this->kindeClient->getPermissions();
        $organization = $this->kindeClient->getOrganization();

        return view('kinde/user-info', [
            'userDetails' => $userDetails,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
    }

    /**
     * Generate portal URL and redirect to Kinde portal
     */
    public function portal()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $returnUrl = $this->request->getGet('return_url') ?? base_url('dashboard');
        $subNav = $this->request->getGet('sub_nav') ?? 'profile';

        try {
            $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
            return redirect()->to($portalData['url']);
        } catch (Exception $e) {
            session()->setFlashdata('error', 'Failed to generate portal URL: ' . $e->getMessage());
            return redirect()->back();
        }
    }
} 