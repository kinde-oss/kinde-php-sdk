<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController;
use Kinde\KindeSDK\OAuthException;
use Kinde\KindeSDK\ApiException;
use Exception;

class ExampleController extends Controller
{
    protected $kindeClient;
    protected $management;
    protected $kindeAuthController;

    public function __construct()
    {
        // Initialize Kinde clients
        $this->kindeClient = KindeClientSDK::createFromEnv();
        $this->management = KindeManagementClient::createFromEnv();
        $this->kindeAuthController = new KindeAuthController($this->kindeClient);
    }

    /**
     * Show the home page with login/logout buttons
     */
    public function index()
    {
        $data = [
            'isAuthenticated' => $this->kindeClient->isAuthenticated,
            'user' => session()->get('kinde_user'),
            'permissions' => session()->get('kinde_permissions') ?? [],
            'organization' => session()->get('kinde_organization')
        ];
        
        return view('kinde/home', $data);
    }


    /**
     * Show user dashboard
     */
    public function dashboard()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'user' => session()->get('kinde_user'),
            'permissions' => session()->get('kinde_permissions') ?? [],
            'organization' => session()->get('kinde_organization')
        ];
        
        return view('kinde/dashboard', $data);
    }

    /**
     * Show user info
     */
    public function userInfo()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'user' => session()->get('kinde_user'),
            'permissions' => session()->get('kinde_permissions') ?? [],
            'organization' => session()->get('kinde_organization')
        ];
        
        return view('kinde/user-info', $data);
    }

    /**
     * Redirect to Kinde portal
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

    /**
     * Create organization
     */
    public function createOrg()
    {
        $additionalParams = $this->request->getGet(['org_code', 'org_name']);
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            return redirect()->to($result->getAuthUrl());
        } catch (Exception $e) {
            session()->setFlashdata('error', $e->getMessage());
            return redirect()->to('/');
        }
    }

    // Management API Examples

    /**
     * List all users (Management API)
     */
    public function listUsers()
    {
        try {
            $users = $this->management->users->getUsers();
            return $this->response->setJSON($users);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Create a new user (Management API)
     */
    public function createUser()
    {
        try {
            $data = $this->request->getJSON(true);
            
            // Validate required fields
            if (!isset($data['given_name']) || !isset($data['family_name']) || !isset($data['email'])) {
                return $this->response->setJSON(['error' => 'Missing required fields'])->setStatusCode(400);
            }
            
            $user = $this->management->users->createUser($data);
            return $this->response->setJSON($user)->setStatusCode(201);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * List all organizations (Management API)
     */
    public function listOrganizations()
    {
        try {
            $organizations = $this->management->organizations->getOrganizations();
            return $this->response->setJSON($organizations);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Create a new organization (Management API)
     */
    public function createOrganization()
    {
        try {
            $data = $this->request->getJSON(true);
            
            if (!isset($data['name'])) {
                return $this->response->setJSON(['error' => 'Organization name is required'])->setStatusCode(400);
            }
            
            $organization = $this->management->organizations->createOrganization($data);
            return $this->response->setJSON($organization)->setStatusCode(201);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * List all applications (Management API)
     */
    public function listApplications()
    {
        try {
            $applications = $this->management->applications->getApplications();
            return $this->response->setJSON($applications);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * List all roles (Management API)
     */
    public function listRoles()
    {
        try {
            $roles = $this->management->roles->getRoles();
            return $this->response->setJSON($roles);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * List all permissions (Management API)
     */
    public function listPermissions()
    {
        try {
            $permissions = $this->management->permissions->getPermissions();
            return $this->response->setJSON($permissions);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * List all feature flags (Management API)
     */
    public function listFeatureFlags()
    {
        try {
            $featureFlags = $this->management->featureFlags->getEnvironmentFeatureFlags();
            return $this->response->setJSON($featureFlags);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get user profile (Frontend API - requires user authentication)
     */
    public function getUserProfile()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->response->setJSON(['error' => 'User must be authenticated to get profile'])->setStatusCode(401);
        }

        try {
            // Use the KindeClientSDK for frontend API endpoints
            $profile = $this->kindeClient->getUserDetails();
            return $this->response->setJSON($profile);
        } catch (Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Bulk user creation example (Management API)
     */
    public function bulkCreateUsers()
    {
        try {
            $data = $this->request->getJSON(true);
            
            if (!isset($data['users']) || !is_array($data['users'])) {
                return $this->response->setJSON(['error' => 'Users array is required'])->setStatusCode(400);
            }
            
            $createdUsers = [];
            $errors = [];
            
            foreach ($data['users'] as $userData) {
                try {
                    $user = $this->management->users->createUser($userData);
                    $createdUsers[] = $user;
                } catch (ApiException $e) {
                    $errors[] = [
                        'email' => $userData['email'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $result = [
                'created_users' => $createdUsers,
                'errors' => $errors
            ];
            
            return $this->response->setJSON($result);
        } catch (Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Protected route example
     */
    public function protectedRoute()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'user' => session()->get('kinde_user'),
            'message' => 'This is a protected route'
        ];
        
        return view('kinde/protected', $data);
    }

    /**
     * Permission-based route example
     */
    public function adminOnly()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $permissions = session()->get('kinde_permissions') ?? [];
        
        if (!in_array('admin:read', $permissions)) {
            session()->setFlashdata('error', 'Access denied. Admin permission required.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'user' => session()->get('kinde_user'),
            'message' => 'Welcome to the admin area!'
        ];
        
        return view('kinde/admin', $data);
    }
} 