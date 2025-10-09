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

    /**
     * Comprehensive Management API Testing Dashboard
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

        $data = [
            'testResults' => $testResults,
            'summary' => [
                'total' => $totalTests,
                'successful' => $successfulTests,
                'failed' => $failedTests,
                'success_rate' => $totalTests > 0 ? round(($successfulTests / $totalTests) * 100, 2) : 0
            ],
            'errors' => $errors,
            'headerFixStatus' => $this->checkHeaderFixStatus()
        ];

        return view('kinde/test-management-api', $data);
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
            $this->management->users->getUsers();
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
            $featureFlags = $this->management->environments->getEnvironementFeatureFlags();
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
     * Test specific API endpoints with detailed error reporting
     */
    public function testSpecificEndpoint()
    {
        $endpoint = $this->request->getGet('endpoint') ?? 'users';
        $action = $this->request->getGet('action') ?? 'list';
        
        try {
            switch ($endpoint) {
                case 'users':
                    if ($action === 'list') {
                        $result = $this->management->users->getUsers();
                    } else {
                        return $this->response->setJSON(['error' => 'Invalid action for users endpoint'])->setStatusCode(400);
                    }
                    break;
                    
                case 'organizations':
                    if ($action === 'list') {
                        $result = $this->management->organizations->getOrganizations();
                    } else {
                        return $this->response->setJSON(['error' => 'Invalid action for organizations endpoint'])->setStatusCode(400);
                    }
                    break;
                    
                case 'applications':
                    if ($action === 'list') {
                        $result = $this->management->applications->getApplications();
                    } else {
                        return $this->response->setJSON(['error' => 'Invalid action for applications endpoint'])->setStatusCode(400);
                    }
                    break;
                    
                case 'roles':
                    if ($action === 'list') {
                        $result = $this->management->roles->getRoles();
                    } else {
                        return $this->response->setJSON(['error' => 'Invalid action for roles endpoint'])->setStatusCode(400);
                    }
                    break;
                    
                case 'permissions':
                    if ($action === 'list') {
                        $result = $this->management->permissions->getPermissions();
                    } else {
                        return $this->response->setJSON(['error' => 'Invalid action for permissions endpoint'])->setStatusCode(400);
                    }
                    break;
                    
                case 'feature-flags':
                    if ($action === 'list') {
                        $result = $this->management->environments->getEnvironementFeatureFlags();
                    } else {
                        return $this->response->setJSON(['error' => 'Invalid action for feature-flags endpoint'])->setStatusCode(400);
                    }
                    break;
                    
                default:
                    return $this->response->setJSON(['error' => 'Invalid endpoint'])->setStatusCode(400);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'endpoint' => $endpoint,
                'action' => $action,
                'data' => $result
            ]);
            
        } catch (ApiException $e) {
            return $this->response->setJSON([
                'success' => false,
                'endpoint' => $endpoint,
                'action' => $action,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'response_body' => $e->getResponseBody(),
                'response_headers' => $e->getResponseHeaders()
            ])->setStatusCode(400);
        }
    }

    /**
     * Get environment information (Management API)
     */
    public function getEnvironment()
    {
        try {
            $environment = $this->management->environments->getEnvironment();
            return $this->response->setJSON($environment);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get business information (Management API)
     */
    public function getBusiness()
    {
        try {
            $business = $this->management->business->getBusiness();
            return $this->response->setJSON($business);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get timezones (Management API)
     */
    public function getTimezones()
    {
        try {
            $timezones = $this->management->timezones->getTimezones();
            return $this->response->setJSON($timezones);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get industries (Management API)
     */
    public function getIndustries()
    {
        try {
            $industries = $this->management->industries->getIndustries();
            return $this->response->setJSON($industries);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get property categories (Management API)
     */
    public function getPropertyCategories()
    {
        try {
            $propertyCategories = $this->management->propertyCategories->getPropertyCategories();
            return $this->response->setJSON($propertyCategories);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get properties (Management API)
     */
    public function getProperties()
    {
        try {
            $properties = $this->management->properties->getProperties();
            return $this->response->setJSON($properties);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get APIs (Management API)
     */
    public function getAPIs()
    {
        try {
            $apis = $this->management->apis->getAPIs();
            return $this->response->setJSON($apis);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get webhooks (Management API)
     */
    public function getWebhooks()
    {
        try {
            $webhooks = $this->management->webhooks->getWebhooks();
            return $this->response->setJSON($webhooks);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
        }
    }

    /**
     * Get subscribers (Management API)
     */
    public function getSubscribers()
    {
        try {
            $subscribers = $this->management->subscribers->getSubscribers();
            return $this->response->setJSON($subscribers);
        } catch (ApiException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode($e->getCode());
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

    /**
     * Comprehensive Has Functionality Testing Dashboard
     * Tests all aspects of the new has functionality
     */
    public function testHasFunctionality()
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->to('/auth/login');
        }

        $testResults = [];
        $errors = [];
        $startTime = microtime(true);

        // Test 1: Basic hasRoles functionality
        $testResults['hasRoles'] = $this->testHasRoles();
        
        // Test 2: Basic hasPermissions functionality
        $testResults['hasPermissions'] = $this->testHasPermissions();
        
        // Test 3: Basic hasFeatureFlags functionality
        $testResults['hasFeatureFlags'] = $this->testHasFeatureFlags();
        
        // Test 4: Basic hasBillingEntitlements functionality
        $testResults['hasBillingEntitlements'] = $this->testHasBillingEntitlements();
        
        // Test 5: Unified has method
        $testResults['unifiedHas'] = $this->testUnifiedHas();
        
        // Test 6: Custom conditions
        $testResults['customConditions'] = $this->testCustomConditions();
        
        // Test 7: Force API parameter variations
        $testResults['forceApiTests'] = $this->testForceApiOptions();
        
        // Test 8: Edge cases and error handling
        $testResults['edgeCases'] = $this->testEdgeCases();
        
        // Test 9: Performance tests
        $testResults['performance'] = $this->testPerformance();
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // Calculate summary statistics
        $totalCategories = count($testResults);
        $successfulCategories = 0;
        $totalIndividualTests = 0;
        $passedIndividualTests = 0;

        foreach ($testResults as $category => $result) {
            if ($result['success']) {
                $successfulCategories++;
            }
            $totalIndividualTests += $result['testCount'] ?? 0;
            $passedIndividualTests += $result['passedCount'] ?? 0;
        }

        $data = [
            'testResults' => $testResults,
            'summary' => [
                'totalCategories' => $totalCategories,
                'successfulCategories' => $successfulCategories,
                'totalIndividualTests' => $totalIndividualTests,
                'passedIndividualTests' => $passedIndividualTests,
                'executionTime' => $executionTime,
                'successRate' => $totalIndividualTests > 0 ? round(($passedIndividualTests / $totalIndividualTests) * 100, 2) : 0
            ],
            'user' => session()->get('kinde_user'),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Load helper functions
        helper('has_test');

        return view('kinde/has-functionality-test', $data);
    }

    /**
     * Test hasRoles functionality
     */
    private function testHasRoles(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Empty roles array (should return true)
            $result = $this->kindeClient->hasRoles([]);
            $tests[] = [
                'name' => 'Empty roles array',
                'expected' => true,
                'actual' => $result,
                'passed' => $result === true
            ];
            if ($result === true) $passed++;

            // Test 2: Single role check
            $result = $this->kindeClient->hasRoles(['admin']);
            $tests[] = [
                'name' => 'Single role check (admin)',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Multiple roles check
            $result = $this->kindeClient->hasRoles(['admin', 'user']);
            $tests[] = [
                'name' => 'Multiple roles check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 4: Force API parameter
            $result = $this->kindeClient->hasRoles(['admin'], true);
            $tests[] = [
                'name' => 'With forceApi = true',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 5: Get actual roles for comparison
            $userRoles = $this->kindeClient->getRoles();
            $tests[] = [
                'name' => 'Get user roles',
                'expected' => 'array',
                'actual' => count($userRoles) . ' roles',
                'passed' => is_array($userRoles)
            ];
            if (is_array($userRoles)) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests,
                'userRoles' => $userRoles
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test hasPermissions functionality
     */
    private function testHasPermissions(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Empty permissions array
            $result = $this->kindeClient->hasPermissions([]);
            $tests[] = [
                'name' => 'Empty permissions array',
                'expected' => true,
                'actual' => $result,
                'passed' => $result === true
            ];
            if ($result === true) $passed++;

            // Test 2: Single permission check
            $result = $this->kindeClient->hasPermissions(['read:posts']);
            $tests[] = [
                'name' => 'Single permission check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Multiple permissions
            $result = $this->kindeClient->hasPermissions(['read:posts', 'write:posts']);
            $tests[] = [
                'name' => 'Multiple permissions check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 4: Force API parameter
            $result = $this->kindeClient->hasPermissions(['read:posts'], false);
            $tests[] = [
                'name' => 'With forceApi = false',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 5: Get actual permissions
            $userPermissions = $this->kindeClient->getPermissions();
            $tests[] = [
                'name' => 'Get user permissions',
                'expected' => 'array',
                'actual' => count($userPermissions['permissions'] ?? []) . ' permissions',
                'passed' => isset($userPermissions['permissions']) && is_array($userPermissions['permissions'])
            ];
            if (isset($userPermissions['permissions']) && is_array($userPermissions['permissions'])) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests,
                'userPermissions' => $userPermissions
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test hasFeatureFlags functionality
     */
    private function testHasFeatureFlags(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Empty feature flags array
            $result = $this->kindeClient->hasFeatureFlags([]);
            $tests[] = [
                'name' => 'Empty feature flags array',
                'expected' => true,
                'actual' => $result,
                'passed' => $result === true
            ];
            if ($result === true) $passed++;

            // Test 2: Single feature flag existence
            $result = $this->kindeClient->hasFeatureFlags(['dark_mode']);
            $tests[] = [
                'name' => 'Single feature flag check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Feature flag with specific value
            $result = $this->kindeClient->hasFeatureFlags([
                ['flag' => 'theme', 'value' => 'dark']
            ]);
            $tests[] = [
                'name' => 'Feature flag with value check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 4: Mixed simple and value checks
            $result = $this->kindeClient->hasFeatureFlags([
                'dark_mode',
                ['flag' => 'max_users', 'value' => 100]
            ]);
            $tests[] = [
                'name' => 'Mixed simple and value checks',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test hasBillingEntitlements functionality
     */
    private function testHasBillingEntitlements(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Empty entitlements array
            $result = $this->kindeClient->hasBillingEntitlements([]);
            $tests[] = [
                'name' => 'Empty entitlements array',
                'expected' => true,
                'actual' => $result,
                'passed' => $result === true
            ];
            if ($result === true) $passed++;

            // Test 2: Single entitlement check
            $result = $this->kindeClient->hasBillingEntitlements(['premium']);
            $tests[] = [
                'name' => 'Single entitlement check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Multiple entitlements
            $result = $this->kindeClient->hasBillingEntitlements(['premium', 'api-access']);
            $tests[] = [
                'name' => 'Multiple entitlements check',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 4: Get actual entitlements
            $userEntitlements = $this->kindeClient->getAllEntitlements();
            $tests[] = [
                'name' => 'Get user entitlements',
                'expected' => 'array',
                'actual' => count($userEntitlements) . ' entitlements',
                'passed' => is_array($userEntitlements)
            ];
            if (is_array($userEntitlements)) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests,
                'userEntitlements' => $userEntitlements
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test unified has method
     */
    private function testUnifiedHas(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Empty conditions
            $result = $this->kindeClient->has([]);
            $tests[] = [
                'name' => 'Empty conditions',
                'expected' => true,
                'actual' => $result,
                'passed' => $result === true
            ];
            if ($result === true) $passed++;

            // Test 2: Single condition type
            $result = $this->kindeClient->has(['roles' => ['admin']]);
            $tests[] = [
                'name' => 'Single condition (roles)',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Multiple condition types
            $result = $this->kindeClient->has([
                'roles' => ['admin'],
                'permissions' => ['read:posts']
            ]);
            $tests[] = [
                'name' => 'Multiple conditions',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 4: All condition types
            $result = $this->kindeClient->has([
                'roles' => ['admin'],
                'permissions' => ['read:posts'],
                'featureFlags' => ['dark_mode'],
                'billingEntitlements' => ['premium']
            ]);
            $tests[] = [
                'name' => 'All condition types',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test custom conditions
     */
    private function testCustomConditions(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Custom role condition
            $result = $this->kindeClient->hasRoles([
                [
                    'role' => 'admin',
                    'condition' => function($role) {
                        return isset($role['key']) && $role['key'] === 'admin';
                    }
                ]
            ]);
            $tests[] = [
                'name' => 'Custom role condition',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 2: Custom permission condition
            $result = $this->kindeClient->hasPermissions([
                [
                    'permission' => 'read:posts',
                    'condition' => function($context) {
                        return isset($context['orgCode']);
                    }
                ]
            ]);
            $tests[] = [
                'name' => 'Custom permission condition',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Custom entitlement condition
            $result = $this->kindeClient->hasBillingEntitlements([
                [
                    'entitlement' => 'premium',
                    'condition' => function($entitlement) {
                        return method_exists($entitlement, 'getFeatureKey');
                    }
                ]
            ]);
            $tests[] = [
                'name' => 'Custom entitlement condition',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test force API parameter options
     */
    private function testForceApiOptions(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Boolean true forceApi
            $result = $this->kindeClient->has([
                'roles' => ['admin'],
                'permissions' => ['read:posts']
            ], true);
            $tests[] = [
                'name' => 'ForceApi = true (boolean)',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 2: Boolean false forceApi
            $result = $this->kindeClient->has([
                'roles' => ['admin'],
                'permissions' => ['read:posts']
            ], false);
            $tests[] = [
                'name' => 'ForceApi = false (boolean)',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 3: Array forceApi (selective)
            $result = $this->kindeClient->has([
                'roles' => ['admin'],
                'permissions' => ['read:posts']
            ], [
                'roles' => true,
                'permissions' => false
            ]);
            $tests[] = [
                'name' => 'ForceApi = array (selective)',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            // Test 4: Null forceApi (default)
            $result = $this->kindeClient->has([
                'roles' => ['admin']
            ], null);
            $tests[] = [
                'name' => 'ForceApi = null (default)',
                'expected' => 'boolean',
                'actual' => $result,
                'passed' => is_bool($result)
            ];
            if (is_bool($result)) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test edge cases and error handling
     */
    private function testEdgeCases(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Non-existent role
            $result = $this->kindeClient->hasRoles(['non_existent_role_xyz']);
            $tests[] = [
                'name' => 'Non-existent role',
                'expected' => false,
                'actual' => $result,
                'passed' => $result === false
            ];
            if ($result === false) $passed++;

            // Test 2: Non-existent permission
            $result = $this->kindeClient->hasPermissions(['non:existent:permission']);
            $tests[] = [
                'name' => 'Non-existent permission',
                'expected' => false,
                'actual' => $result,
                'passed' => $result === false
            ];
            if ($result === false) $passed++;

            // Test 3: Non-existent feature flag
            $result = $this->kindeClient->hasFeatureFlags(['non_existent_flag_xyz']);
            $tests[] = [
                'name' => 'Non-existent feature flag',
                'expected' => false,
                'actual' => $result,
                'passed' => $result === false
            ];
            if ($result === false) $passed++;

            // Test 4: Non-existent entitlement
            $result = $this->kindeClient->hasBillingEntitlements(['non_existent_entitlement']);
            $tests[] = [
                'name' => 'Non-existent entitlement',
                'expected' => false,
                'actual' => $result,
                'passed' => $result === false
            ];
            if ($result === false) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }

    /**
     * Test performance characteristics
     */
    private function testPerformance(): array
    {
        $tests = [];
        $passed = 0;
        
        try {
            // Test 1: Simple role check timing
            $start = microtime(true);
            $result = $this->kindeClient->hasRoles(['admin']);
            $time1 = round((microtime(true) - $start) * 1000, 2);
            $tests[] = [
                'name' => 'Simple role check timing',
                'expected' => '< 100ms',
                'actual' => $time1 . 'ms',
                'passed' => $time1 < 100
            ];
            if ($time1 < 100) $passed++;

            // Test 2: Complex unified check timing
            $start = microtime(true);
            $result = $this->kindeClient->has([
                'roles' => ['admin', 'user'],
                'permissions' => ['read:posts', 'write:posts']
            ]);
            $time2 = round((microtime(true) - $start) * 1000, 2);
            $tests[] = [
                'name' => 'Complex unified check timing',
                'expected' => '< 200ms',
                'actual' => $time2 . 'ms',
                'passed' => $time2 < 200
            ];
            if ($time2 < 200) $passed++;

            // Test 3: Multiple sequential calls
            $start = microtime(true);
            for ($i = 0; $i < 5; $i++) {
                $this->kindeClient->hasRoles(['admin']);
            }
            $time3 = round((microtime(true) - $start) * 1000, 2);
            $tests[] = [
                'name' => '5 sequential role checks',
                'expected' => '< 250ms',
                'actual' => $time3 . 'ms',
                'passed' => $time3 < 250
            ];
            if ($time3 < 250) $passed++;

            return [
                'success' => true,
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'testCount' => count($tests),
                'passedCount' => $passed,
                'tests' => $tests
            ];
        }
    }
} 