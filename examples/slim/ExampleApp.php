<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Frameworks\Slim\KindeAuthController;
use Kinde\KindeSDK\Frameworks\Slim\KindeAuthMiddleware;
use Kinde\KindeSDK\OAuthException;
use Kinde\KindeSDK\ApiException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

// Create Slim app
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Create Kinde clients
$kindeClient = KindeClientSDK::createFromEnv();
$management = KindeManagementClient::createFromEnv();

// Create Kinde auth controller
$kindeAuthController = new KindeAuthController($kindeClient);

// Create renderer
$renderer = new PhpRenderer(__DIR__ . '/templates');

// Home page
$app->get('/', function (Request $request, Response $response) use ($renderer, $kindeClient) {
    $data = [
        'isAuthenticated' => $kindeClient->isAuthenticated,
        'user' => $_SESSION['kinde_user'] ?? null,
        'permissions' => $_SESSION['kinde_permissions'] ?? [],
        'organization' => $_SESSION['kinde_organization'] ?? null
    ];
    
    return $renderer->render($response, 'home.php', $data);
});

// Login route
$app->get('/auth/login', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->login($request, $response);
});

// Callback route
$app->get('/auth/callback', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->callback($request, $response);
});

// Register route
$app->get('/auth/register', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->register($request, $response);
});

// Create organization route
$app->get('/auth/create-org', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->createOrg($request, $response);
});

// Logout route
$app->get('/auth/logout', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->logout($request, $response);
});

// User info route
$app->get('/auth/user-info', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->userInfo($request, $response);
});

// Portal route
$app->get('/auth/portal', function (Request $request, Response $response) use ($kindeAuthController) {
    return $kindeAuthController->portal($request, $response);
});

// Dashboard (protected)
$app->get('/dashboard', function (Request $request, Response $response) use ($renderer, $kindeClient) {
    if (!$kindeClient->isAuthenticated) {
        return $response->withStatus(302)->withHeader('Location', '/');
    }
    
    $data = [
        'user' => $_SESSION['kinde_user'] ?? null,
        'permissions' => $_SESSION['kinde_permissions'] ?? [],
        'organization' => $_SESSION['kinde_organization'] ?? null
    ];
    
    return $renderer->render($response, 'dashboard.php', $data);
})->add(new KindeAuthMiddleware($kindeClient));

// Management API Routes

// List users
$app->get('/api/users', function (Request $request, Response $response) use ($management) {
    try {
        $users = $management->users->getUsers();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// Create user
$app->post('/api/users', function (Request $request, Response $response) use ($management) {
    try {
        $data = $request->getParsedBody();
        
        // Validate required fields
        if (!isset($data['given_name']) || !isset($data['family_name']) || !isset($data['email'])) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        $user = $management->users->createUser($data);
        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// List organizations
$app->get('/api/organizations', function (Request $request, Response $response) use ($management) {
    try {
        $organizations = $management->organizations->getOrganizations();
        $response->getBody()->write(json_encode($organizations));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// Create organization
$app->post('/api/organizations', function (Request $request, Response $response) use ($management) {
    try {
        $data = $request->getParsedBody();
        
        if (!isset($data['name'])) {
            $response->getBody()->write(json_encode(['error' => 'Organization name is required']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        $organization = $management->organizations->createOrganization($data);
        $response->getBody()->write(json_encode($organization));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// List applications
$app->get('/api/applications', function (Request $request, Response $response) use ($management) {
    try {
        $applications = $management->applications->getApplications();
        $response->getBody()->write(json_encode($applications));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// List roles
$app->get('/api/roles', function (Request $request, Response $response) use ($management) {
    try {
        $roles = $management->roles->getRoles();
        $response->getBody()->write(json_encode($roles));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// List permissions
$app->get('/api/permissions', function (Request $request, Response $response) use ($management) {
    try {
        $permissions = $management->permissions->getPermissions();
        $response->getBody()->write(json_encode($permissions));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// List feature flags
$app->get('/api/feature-flags', function (Request $request, Response $response) use ($management) {
    try {
        $featureFlags = $management->featureFlags->getEnvironmentFeatureFlags();
        $response->getBody()->write(json_encode($featureFlags));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// Get user profile
$app->get('/api/user-profile', function (Request $request, Response $response) use ($management) {
    try {
        $profile = $management->oauth->getUserProfileV2();
        $response->getBody()->write(json_encode($profile));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (ApiException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($e->getCode());
    }
});

// Bulk user creation
$app->post('/api/users/bulk', function (Request $request, Response $response) use ($management) {
    try {
        $data = $request->getParsedBody();
        
        if (!isset($data['users']) || !is_array($data['users'])) {
            $response->getBody()->write(json_encode(['error' => 'Users array is required']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        $createdUsers = [];
        $errors = [];
        
        foreach ($data['users'] as $userData) {
            try {
                $user = $management->users->createUser($userData);
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
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});

// Run the app
$app->run(); 