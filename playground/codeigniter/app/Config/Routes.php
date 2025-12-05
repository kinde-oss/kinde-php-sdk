<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'ExampleController::index');
$routes->get('auth/login', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::login');
$routes->get('auth/callback', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::callback');
$routes->get('auth/logout', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::logout');
$routes->get('auth/register', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::register');
$routes->get('auth/create-org', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::createOrg');
$routes->get('auth/user-info', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::userInfo');
$routes->get('auth/portal', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::portal');

// Dashboard and user routes
$routes->get('dashboard', 'ExampleController::dashboard');
$routes->get('admin', 'ExampleController::adminOnly');
$routes->get('protected', 'ExampleController::protectedRoute');

// Management API Testing Dashboard
$routes->get('test-management-api', 'ExampleController::testManagementApi');

// Has Functionality Testing Dashboard
$routes->get('test-has-functionality', 'ExampleController::testHasFunctionality');

// Individual API endpoints
$routes->get('api/users', 'ExampleController::listUsers');
$routes->post('api/users', 'ExampleController::createUser');
$routes->get('api/organizations', 'ExampleController::listOrganizations');
$routes->post('api/organizations', 'ExampleController::createOrganization');
$routes->get('api/applications', 'ExampleController::listApplications');
$routes->get('api/roles', 'ExampleController::listRoles');
$routes->get('api/permissions', 'ExampleController::listPermissions');
$routes->get('api/feature-flags', 'ExampleController::listFeatureFlags');
$routes->get('api/user-profile', 'ExampleController::getUserProfile');

// Advanced testing endpoints
$routes->post('api/bulk-create-users', 'ExampleController::bulkCreateUsers');
$routes->get('api/test-endpoint', 'ExampleController::testSpecificEndpoint');

// Additional API endpoints for comprehensive testing
$routes->get('api/environment', 'ExampleController::getEnvironment');
$routes->get('api/business', 'ExampleController::getBusiness');
$routes->get('api/timezones', 'ExampleController::getTimezones');
$routes->get('api/industries', 'ExampleController::getIndustries');
$routes->get('api/property-categories', 'ExampleController::getPropertyCategories');
$routes->get('api/properties', 'ExampleController::getProperties');
$routes->get('api/apis', 'ExampleController::getAPIs');
$routes->get('api/webhooks', 'ExampleController::getWebhooks');
$routes->get('api/subscribers', 'ExampleController::getSubscribers');