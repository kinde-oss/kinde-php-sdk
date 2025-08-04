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

    /**
     * Test Management API Dashboard - Comprehensive testing of all endpoints
     */
    public function testManagementApi()
    {
        $testResults = [];
        $errors = [];

        // Test 1: Get Users
        $usersResult = $this->safeApiCall(
            fn() => $this->management->users->getUsers()
        );
        $testResults['users'] = $usersResult;
        if (!$usersResult['success']) {
            $errors[] = "Users API failed: " . $usersResult['error'];
        } else {
            $testResults['users']['count'] = count($usersResult['data']->getUsers() ?? []);
        }

        // Test 2: Get Organizations
        $organizationsResult = $this->safeApiCall(
            fn() => $this->management->organizations->getOrganizations()
        );
        $testResults['organizations'] = $organizationsResult;
        if (!$organizationsResult['success']) {
            $errors[] = "Organizations API failed: " . $organizationsResult['error'];
        } else {
            $testResults['organizations']['count'] = count($organizationsResult['data']->getOrganizations() ?? []);
        }

        // Test 3: Get Applications
        $applicationsResult = $this->safeApiCall(
            fn() => $this->management->applications->getApplications()
        );
        $testResults['applications'] = $applicationsResult;
        if (!$applicationsResult['success']) {
            $errors[] = "Applications API failed: " . $applicationsResult['error'];
        } else {
            $testResults['applications']['count'] = count($applicationsResult['data']->getApplications() ?? []);
        }

        // Test 4: Get Roles
        $rolesResult = $this->safeApiCall(
            fn() => $this->management->roles->getRoles()
        );
        $testResults['roles'] = $rolesResult;
        if (!$rolesResult['success']) {
            $errors[] = "Roles API failed: " . $rolesResult['error'];
        } else {
            $testResults['roles']['count'] = count($rolesResult['data']->getRoles() ?? []);
        }

        // Test 5: Get Permissions
        $permissionsResult = $this->safeApiCall(
            fn() => $this->management->permissions->getPermissions()
        );
        $testResults['permissions'] = $permissionsResult;
        if (!$permissionsResult['success']) {
            $errors[] = "Permissions API failed: " . $permissionsResult['error'];
        } else {
            $testResults['permissions']['count'] = count($permissionsResult['data']->getPermissions() ?? []);
        }

        // Test 6: Get Feature Flags (from Environments API)
        $featureFlagsResult = $this->safeApiCall(
            fn() => $this->management->environments->getEnvironementFeatureFlags()
        );
        $testResults['feature_flags'] = $featureFlagsResult;
        if (!$featureFlagsResult['success']) {
            $errors[] = "Feature Flags API failed: " . $featureFlagsResult['error'];
        } else {
            $testResults['feature_flags']['count'] = count($featureFlagsResult['data']->getFeatureFlags() ?? []);
        }

        // Test 7: Get Environment (singular - this exists)
        $environmentResult = $this->safeApiCall(
            fn() => $this->management->environments->getEnvironment()
        );
        $testResults['environment'] = $environmentResult;
        if (!$environmentResult['success']) {
            $errors[] = "Environment API failed: " . $environmentResult['error'];
        }

        // Test 8: Get Business
        $businessResult = $this->safeApiCall(
            fn() => $this->management->business->getBusiness()
        );
        $testResults['business'] = $businessResult;
        if (!$businessResult['success']) {
            $errors[] = "Business API failed: " . $businessResult['error'];
        }

        // Test 9: Get Timezones
        $timezonesResult = $this->safeApiCall(
            fn() => $this->management->timezones->getTimezones()
        );
        $testResults['timezones'] = $timezonesResult;
        if (!$timezonesResult['success']) {
            $errors[] = "Timezones API failed: " . $timezonesResult['error'];
        } else {
            $testResults['timezones']['count'] = count($timezonesResult['data']->getTimezones() ?? []);
        }

        // Test 10: Get Industries
        $industriesResult = $this->safeApiCall(
            fn() => $this->management->industries->getIndustries()
        );
        $testResults['industries'] = $industriesResult;
        if (!$industriesResult['success']) {
            $errors[] = "Industries API failed: " . $industriesResult['error'];
        } else {
            $testResults['industries']['count'] = count($industriesResult['data']->getIndustries() ?? []);
        }

        // Test 11: Get Property Categories
        $propertyCategoriesResult = $this->safeApiCall(
            fn() => $this->management->propertyCategories->getPropertyCategories()
        );
        $testResults['property_categories'] = $propertyCategoriesResult;
        if (!$propertyCategoriesResult['success']) {
            $errors[] = "Property Categories API failed: " . $propertyCategoriesResult['error'];
        } else {
            $testResults['property_categories']['count'] = count($propertyCategoriesResult['data']->getPropertyCategories() ?? []);
        }

        // Test 12: Get Properties
        $propertiesResult = $this->safeApiCall(
            fn() => $this->management->properties->getProperties()
        );
        $testResults['properties'] = $propertiesResult;
        if (!$propertiesResult['success']) {
            $errors[] = "Properties API failed: " . $propertiesResult['error'];
        } else {
            $testResults['properties']['count'] = count($propertiesResult['data']->getProperties() ?? []);
        }

        // Test 13: Get APIs
        $apisResult = $this->safeApiCall(
            fn() => $this->management->apis->getAPIs()
        );
        $testResults['apis'] = $apisResult;
        if (!$apisResult['success']) {
            $errors[] = "APIs API failed: " . $apisResult['error'];
        } else {
            $testResults['apis']['count'] = count($apisResult['data']->getApis() ?? []);
        }

        // Test 14: Get Webhooks
        $webhooksResult = $this->safeApiCall(
            fn() => $this->management->webhooks->getWebhooks()
        );
        $testResults['webhooks'] = $webhooksResult;
        if (!$webhooksResult['success']) {
            $errors[] = "Webhooks API failed: " . $webhooksResult['error'];
        } else {
            $testResults['webhooks']['count'] = count($webhooksResult['data']->getWebhooks() ?? []);
        }

        // Test 15: Get Subscribers
        $subscribersResult = $this->safeApiCall(
            fn() => $this->management->subscribers->getSubscribers()
        );
        $testResults['subscribers'] = $subscribersResult;
        if (!$subscribersResult['success']) {
            $errors[] = "Subscribers API failed: " . $subscribersResult['error'];
        } else {
            $testResults['subscribers']['count'] = count($subscribersResult['data']->getSubscribers() ?? []);
        }

        // Calculate summary statistics
        $totalTests = count($testResults);
        $successfulTests = count(array_filter($testResults, fn($result) => $result['success']));
        $failedTests = $totalTests - $successfulTests;

        return view('kinde.test-management-api', [
            'testResults' => $testResults,
            'summary' => [
                'total' => $totalTests,
                'successful' => $successfulTests,
                'failed' => $failedTests,
                'success_rate' => $totalTests > 0 ? round(($successfulTests / $totalTests) * 100, 2) : 0
            ],
            'errors' => $errors,
            'headerFixStatus' => $this->checkHeaderFixStatus()
        ]);
    }

    /**
     * Helper function to safely test API methods with robust error handling
     */
    private function safeApiCall(callable $apiCall)
    {
        try {
            $result = $apiCall();
            return [
                'success' => true,
                'data' => $result
            ];
        } catch (ApiException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        } catch (\Error $e) {
            return [
                'success' => false,
                'error' => 'Method not found: ' . $e->getMessage(),
                'code' => 'METHOD_NOT_FOUND'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Unexpected error: ' . $e->getMessage(),
                'code' => 'UNEXPECTED_ERROR'
            ];
        }
    }

    /**
     * Check if the header fix is working by examining the request headers
     */
    private function checkHeaderFixStatus()
    {
        try {
            // Make a simple API call and check if it succeeds
            $users = $this->management->users->getUsers();
            return [
                'working' => true,
                'message' => 'Header fix is working - API calls are successful',
                'test_call' => 'getUsers() succeeded'
            ];
        } catch (ApiException $e) {
            // Check if the error is related to content type
            if (strpos($e->getMessage(), 'content type') !== false || 
                strpos($e->getMessage(), 'charset') !== false ||
                strpos($e->getMessage(), '415') !== false) {
                return [
                    'working' => false,
                    'message' => 'Header fix may not be working - content type error detected',
                    'error' => $e->getMessage()
                ];
            }
            return [
                'working' => 'unknown',
                'message' => 'Cannot determine header fix status - different error occurred',
                'error' => $e->getMessage()
            ];
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
            $featureFlags = $this->management->environments->getEnvironementFeatureFlags();
            return $featureFlags;
        } catch (ApiException $e) {
            return ['error' => $e->getMessage()];
        } catch (\Error $e) {
            return ['error' => 'Method not found: ' . $e->getMessage()];
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

    /**
     * Test specific API endpoints with detailed error reporting
     */
    public function testSpecificEndpoint(Request $request)
    {
        $endpoint = $request->get('endpoint', 'users');
        $action = $request->get('action', 'list');
        
        try {
            switch ($endpoint) {
                case 'users':
                    if ($action === 'list') {
                        $result = $this->management->users->getUsers();
                    } else {
                        return response()->json(['error' => 'Invalid action for users endpoint'], 400);
                    }
                    break;
                    
                case 'organizations':
                    if ($action === 'list') {
                        $result = $this->management->organizations->getOrganizations();
                    } else {
                        return response()->json(['error' => 'Invalid action for organizations endpoint'], 400);
                    }
                    break;
                    
                case 'applications':
                    if ($action === 'list') {
                        $result = $this->management->applications->getApplications();
                    } else {
                        return response()->json(['error' => 'Invalid action for applications endpoint'], 400);
                    }
                    break;
                    
                case 'roles':
                    if ($action === 'list') {
                        $result = $this->management->roles->getRoles();
                    } else {
                        return response()->json(['error' => 'Invalid action for roles endpoint'], 400);
                    }
                    break;
                    
                case 'permissions':
                    if ($action === 'list') {
                        $result = $this->management->permissions->getPermissions();
                    } else {
                        return response()->json(['error' => 'Invalid action for permissions endpoint'], 400);
                    }
                    break;
                    
                case 'feature-flags':
                    if ($action === 'list') {
                        $result = $this->management->environments->getEnvironementFeatureFlags();
                    } else {
                        return response()->json(['error' => 'Invalid action for feature-flags endpoint'], 400);
                    }
                    break;
                    
                default:
                    return response()->json(['error' => 'Invalid endpoint'], 400);
            }
            
            return response()->json([
                'success' => true,
                'endpoint' => $endpoint,
                'action' => $action,
                'data' => $result
            ]);
            
        } catch (ApiException $e) {
            return response()->json([
                'success' => false,
                'endpoint' => $endpoint,
                'action' => $action,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'response_body' => $e->getResponseBody(),
                'response_headers' => $e->getResponseHeaders()
            ], 400);
        }
    }

} 