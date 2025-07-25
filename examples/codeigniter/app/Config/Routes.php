<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('auth/login', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::login');
$routes->get('auth/callback', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::callback');
$routes->get('auth/logout', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::logout');
$routes->get('auth/register', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::register');
$routes->get('auth/create-org', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::createOrg');
$routes->get('auth/user-info', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::userInfo');
$routes->get('auth/portal', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAuthController::portal');
$routes->get('dashboard', 'ExampleController::dashboard');
$routes->get('admin', 'ExampleController::adminOnly');
