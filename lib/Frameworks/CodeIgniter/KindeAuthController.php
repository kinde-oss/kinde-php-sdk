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
        parent::__construct();
        
        // Initialize Kinde client (you'd configure this in your service)
        $this->kindeClient = new KindeClientSDK(
            getenv('KINDE_DOMAIN'),
            getenv('KINDE_REDIRECT_URI'),
            getenv('KINDE_CLIENT_ID'),
            getenv('KINDE_CLIENT_SECRET'),
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
        $additionalParams = $this->request->getGet(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            
            // The login method should handle the redirect internally
            // This is just a fallback
            return redirect()->to($result->getAuthUrl());
        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/');
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback()
    {
        try {
            $token = $this->kindeClient->getToken();
            
            if ($token) {
                // Store user session
                $userDetails = $this->kindeClient->getUserDetails();
                
                // Store in session
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
        $additionalParams = $this->request->getGet(['org_code', 'org_name', 'is_create_org']);
        
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
        $additionalParams = $this->request->getGet(['org_name']);
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
            return redirect()->away($portalData['url']);
        } catch (Exception $e) {
            session()->setFlashdata('error', 'Failed to generate portal URL: ' . $e->getMessage());
            return redirect()->back();
        }
    }
} 