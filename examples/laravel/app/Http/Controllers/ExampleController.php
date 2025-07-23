<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\OAuthException;
use Kinde\KindeSDK\ApiException;
use Exception;

class ExampleController extends Controller
{
    public function __construct(
        private KindeClientSDK $kindeClient,
        private KindeManagementClient $management
    ) {}

    /**
     * Show the home page with login/logout buttons
     */
    public function home()
    {
        // Simple view rendering for the minimal setup
        $isAuthenticated = $this->kindeClient->isAuthenticated;
        $user = session('kinde_user');
        $permissions = session('kinde_permissions', []);
        $organization = session('kinde_organization');
        
        ob_start();
        include __DIR__ . '/views/kinde/home.blade.php';
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Show user dashboard
     */
    public function dashboard()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect('/');
        }

        $user = session('kinde_user');
        $permissions = session('kinde_permissions', []);
        $organization = session('kinde_organization');

        ob_start();
        include __DIR__ . '/views/kinde/dashboard.blade.php';
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Show user info
     */
    public function userInfo()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect('/');
        }

        $user = session('kinde_user');
        $permissions = session('kinde_permissions', []);
        $organization = session('kinde_organization');

        ob_start();
        include __DIR__ . '/views/kinde/user-info.blade.php';
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Create organization
     */
    public function createOrg(Request $request)
    {
        $additionalParams = $request->only(['org_code', 'org_name']);
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            header('Location: ' . $result->getAuthUrl());
            exit;
        } catch (Exception $e) {
            header('Location: /?error=' . urlencode($e->getMessage()));
            exit;
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
            return $users;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a new user (Management API)
     */
    public function createUser(Request $request)
    {
        try {
            $userData = $request->all();
            // Simple validation
            if (empty($userData['given_name']) || empty($userData['family_name']) || empty($userData['email'])) {
                return ['error' => 'Missing required fields: given_name, family_name, email'];
            }

            $user = $this->management->users->createUser($userData);
            return $user;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * List all organizations (Management API)
     */
    public function listOrganizations()
    {
        try {
            $organizations = $this->management->organizations->getOrganizations();
            return $organizations;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a new organization (Management API)
     */
    public function createOrganization(Request $request)
    {
        try {
            $orgData = $request->all();
            // Simple validation
            if (empty($orgData['name'])) {
                return ['error' => 'Missing required field: name'];
            }

            $organization = $this->management->organizations->createOrganization($orgData);
            return $organization;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * List all applications (Management API)
     */
    public function listApplications()
    {
        try {
            $applications = $this->management->applications->getApplications();
            return $applications;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * List all roles (Management API)
     */
    public function listRoles()
    {
        try {
            $roles = $this->management->roles->getRoles();
            return $roles;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * List all permissions (Management API)
     */
    public function listPermissions()
    {
        try {
            $permissions = $this->management->permissions->getPermissions();
            return $permissions;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * List all feature flags (Management API)
     */
    public function listFeatureFlags()
    {
        try {
            $featureFlags = $this->management->featureFlags->getEnvironmentFeatureFlags();
            return $featureFlags;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get user profile (Management API)
     */
    public function getUserProfile()
    {
        try {
            $profile = $this->management->oauth->getUserProfileV2();
            return $profile;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Bulk user creation example (Management API)
     */
    public function bulkCreateUsers(Request $request)
    {
        try {
            $usersData = $request->all();
            
            if (empty($usersData['users']) || !is_array($usersData['users'])) {
                return ['error' => 'Missing required field: users (array)'];
            }

            $createdUsers = [];
            $errors = [];

            foreach ($usersData['users'] as $userData) {
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

            return [
                'created_users' => $createdUsers,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
} 