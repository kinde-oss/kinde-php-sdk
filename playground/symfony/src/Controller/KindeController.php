<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Frameworks\Symfony\KindeAuthController;
use Kinde\KindeSDK\OAuthException;
use Kinde\KindeSDK\ApiException;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class KindeController extends AbstractController
{
    public function __construct(
        private KindeClientSDK $kindeClient,
        private KindeManagementClient $management,
        private RequestStack $requestStack
    ) {}

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $isAuthenticated = $this->getSession()?->get('kinde_authenticated', false);
        $permissionsRaw = $this->getPermissions();
        $permissions = $permissionsRaw['permissions'] ?? [];
        $organization = $this->getOrganization();
        if ($organization && isset($organization['orgCode'])) {
            $organization['name'] = $organization['orgCode'];
        }
        return $this->render('kinde/home.html.twig', [
            'isAuthenticated' => $isAuthenticated,
            'user' => $this->getKindeUser(),
            'permissions' => $permissions,
            'organization' => $organization
        ]);
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        error_log('[KindeController] Dashboard route accessed');
        $user = $this->getKindeUser();
        $isAuthenticated = $this->getSession()?->get('kinde_authenticated', false);
        error_log('[KindeController] User from session in dashboard: ' . print_r($user, true));
        $permissionsRaw = $this->getPermissions();
        $permissions = $permissionsRaw['permissions'] ?? [];
        $organization = $this->getOrganization();
        if ($organization && isset($organization['orgCode'])) {
            $organization['name'] = $organization['orgCode'];
        }
        return $this->render('kinde/dashboard.html.twig', [
            'isAuthenticated' => $isAuthenticated,
            'user' => $user,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
    }

    #[Route('/auth/user-info', name: 'kinde_user_info')]
    public function userInfo(): Response
    {
        $isAuthenticated = $this->getSession()?->get('kinde_authenticated', false);
        $user = $this->getKindeUser();
        if (!$isAuthenticated) {
            return $this->redirectToRoute('kinde_login');
        }
        $permissionsRaw = $this->getPermissions();
        $permissions = $permissionsRaw['permissions'] ?? [];
        $organization = $this->getOrganization();
        if ($organization && isset($organization['orgCode'])) {
            $organization['name'] = $organization['orgCode'];
        }
        return $this->render('kinde/user-info.html.twig', [
            'isAuthenticated' => $isAuthenticated,
            'user' => $user,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
    }
    
    #[Route('/auth/create-org', name: 'kinde_create_org')]
    public function createOrg(Request $request): RedirectResponse
    {
        $additionalParams = [];
        if ($request->query->has('org_code')) {
            $additionalParams['org_code'] = $request->query->get('org_code');
        }
        if ($request->query->has('org_name')) {
            $additionalParams['org_name'] = $request->query->get('org_name');
        }
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            return $this->redirect($result->getAuthUrl());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    // Management API Routes

    #[Route('/api/users', name: 'api_users', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        try {
            $users = $this->management->users->getUsers();
            return $this->json($users);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/users', name: 'api_create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Validate required fields
            if (!isset($data['given_name']) || !isset($data['family_name']) || !isset($data['email'])) {
                return $this->json(['error' => 'Missing required fields'], 400);
            }
            
            $user = $this->management->users->createUser($data);
            return $this->json($user, 201);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/organizations', name: 'api_organizations', methods: ['GET'])]
    public function listOrganizations(): JsonResponse
    {
        try {
            $organizations = $this->management->organizations->getOrganizations();
            return $this->json($organizations);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/organizations', name: 'api_create_organization', methods: ['POST'])]
    public function createOrganization(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['name'])) {
                return $this->json(['error' => 'Organization name is required'], 400);
            }
            
            $organization = $this->management->organizations->createOrganization($data);
            return $this->json($organization, 201);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/applications', name: 'api_applications', methods: ['GET'])]
    public function listApplications(): JsonResponse
    {
        try {
            $applications = $this->management->applications->getApplications();
            return $this->json($applications);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/roles', name: 'api_roles', methods: ['GET'])]
    public function listRoles(): JsonResponse
    {
        try {
            $roles = $this->management->roles->getRoles();
            return $this->json($roles);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/permissions', name: 'api_permissions', methods: ['GET'])]
    public function listPermissions(): JsonResponse
    {
        try {
            $permissions = $this->management->permissions->getPermissions();
            return $this->json($permissions);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/feature-flags', name: 'api_feature_flags', methods: ['GET'])]
    public function listFeatureFlags(): JsonResponse
    {
        try {
            $featureFlags = $this->management->featureFlags->getEnvironmentFeatureFlags();
            return $this->json($featureFlags);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('/api/user-profile', name: 'api_user_profile', methods: ['GET'])]
    public function getUserProfile(): JsonResponse
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->json(['error' => 'User must be authenticated to get profile'], 401);
        }

        try {
            // Use the KindeClientSDK for frontend API endpoints
            $profile = $this->kindeClient->getUserDetails();
            return $this->json($profile);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/users/bulk', name: 'api_bulk_create_users', methods: ['POST'])]
    public function bulkCreateUsers(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['users']) || !is_array($data['users'])) {
                return $this->json(['error' => 'Users array is required'], 400);
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
            
            return $this->json($result);
        } catch (Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper methods for session management

    private function getSession(): ?SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request?->getSession();
    }

    private function getKindeUser()
    {
        return $this->getSession()?->get('kinde_user');
    }

    private function setUser($user)
    {
        $session = $this->getSession();
        if ($session) {
            $session->set('kinde_user', $user);
            $session->set('kinde_authenticated', true);
        }
    }

    private function clearUser()
    {
        $session = $this->getSession();
        if ($session) {
            $session->remove('kinde_user');
            $session->remove('kinde_authenticated');
            $session->remove('kinde_permissions');
            $session->remove('kinde_organization');
        }
    }

    private function getPermissions()
    {
        return $this->getSession()?->get('kinde_permissions', []);
    }

    private function getOrganization()
    {
        return $this->getSession()?->get('kinde_organization');
    }
} 