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
        
        return view('kinde.home', [
            'isAuthenticated' => $isAuthenticated,
            'user' => $user,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
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

        return view('kinde.dashboard', [
            'user' => $user,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
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
        
        // Get entitlements using the new SDK methods
        $entitlements = [];
        $entitlementsError = null;
        $entitlementChecks = [];
        
        try {
            $entitlements = $this->kindeClient->getAllEntitlements();
            
            // Demonstrate other entitlements methods
            if (!empty($entitlements)) {
                // Get the first entitlement to demonstrate specific methods
                $firstEntitlement = $entitlements[0];
                $featureKey = $firstEntitlement->getFeatureKey();
                
                $entitlementChecks = [
                    'has_entitlement' => $this->kindeClient->hasEntitlement($featureKey),
                    'specific_entitlement' => $this->kindeClient->getEntitlement($featureKey),
                    'entitlement_limit' => $this->kindeClient->getEntitlementLimit($featureKey),
                    'non_existent_entitlement' => $this->kindeClient->hasEntitlement('non_existent_feature'),
                    'non_existent_limit' => $this->kindeClient->getEntitlementLimit('non_existent_feature')
                ];
            }
        } catch (Exception $e) {
            $entitlementsError = $e->getMessage();
        }

        return view('kinde.user-info', [
            'userDetails' => session('kinde_user'),
            'permissions' => session('kinde_permissions', []),
            'organization' => session('kinde_organization'),
            'entitlements' => $entitlements,
            'entitlementsError' => $entitlementsError,
            'entitlementChecks' => $entitlementChecks
        ]);
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
            return redirect($result->getAuthUrl());
        } catch (Exception $e) {
            return redirect('/?error=' . urlencode($e->getMessage()));
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
                return response()->json(['error' => 'Missing required fields: given_name, family_name, email'], 400);
            }

            $user = $this->management->users->createUser($userData);
            return response()->json($user);
        } catch (ApiException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
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
     * Get user profile (Frontend API - requires user authentication)
     */
    public function getUserProfile()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return ['error' => 'User must be authenticated to get profile'];
        }

        try {
            // Use the KindeClientSDK for frontend API endpoints
            $profile = $this->kindeClient->getUserDetails();
            return $profile;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

} 