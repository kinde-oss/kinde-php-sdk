<?php

namespace Kinde\KindeSDK\Examples\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\KindeManagementClient;
use Kinde\KindeSDK\Frameworks\Symfony\KindeAuthController;
use Kinde\KindeSDK\OAuthException;
use Kinde\KindeSDK\ApiException;
use Exception;

class ExampleController extends AbstractController
{
    public function __construct(
        private KindeClientSDK $kindeClient,
        private KindeManagementClient $management
    ) {}

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('kinde/home.html.twig', [
            'isAuthenticated' => $this->kindeClient->isAuthenticated,
            'user' => $this->getUser(),
            'permissions' => $this->getPermissions(),
            'organization' => $this->getOrganization()
        ]);
    }

    /**
     * @Route("/auth/login", name="kinde_login")
     */
    public function login(Request $request): RedirectResponse
    {
        $additionalParams = $request->query->only(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            return $this->redirect($result->getAuthUrl());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/auth/callback", name="kinde_callback")
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            if ($this->kindeClient->isAuthenticated) {
                $user = $this->kindeClient->getUserDetails();
                $this->setUser($user);
                $this->addFlash('success', 'Logged in successfully');
                return $this->redirectToRoute('dashboard');
            }
            
            $this->addFlash('error', 'Authentication failed');
            return $this->redirectToRoute('home');
        } catch (OAuthException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(): Response
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->redirectToRoute('kinde_login');
        }

        return $this->render('kinde/dashboard.html.twig', [
            'user' => $this->getUser(),
            'permissions' => $this->getPermissions(),
            'organization' => $this->getOrganization()
        ]);
    }

    /**
     * @Route("/auth/user-info", name="kinde_user_info")
     */
    public function userInfo(): Response
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->redirectToRoute('kinde_login');
        }

        return $this->render('kinde/user-info.html.twig', [
            'user' => $this->getUser(),
            'permissions' => $this->getPermissions(),
            'organization' => $this->getOrganization()
        ]);
    }

    /**
     * @Route("/auth/portal", name="kinde_portal")
     */
    public function portal(Request $request): RedirectResponse
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->redirectToRoute('kinde_login');
        }

        $returnUrl = $request->query->get('return_url', $this->generateUrl('dashboard', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
        $subNav = $request->query->get('sub_nav', 'profile');

        try {
            $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
            return $this->redirect($portalData['url']);
        } catch (Exception $e) {
            $this->addFlash('error', 'Failed to generate portal URL: ' . $e->getMessage());
            return $this->redirectToRoute('dashboard');
        }
    }

    /**
     * @Route("/auth/logout", name="kinde_logout")
     */
    public function logout(): RedirectResponse
    {
        $this->clearUser();
        
        try {
            $this->kindeClient->logout();
        } catch (Exception $e) {
            // Continue with logout even if Kinde logout fails
        }
        
        $this->addFlash('success', 'Logged out successfully');
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/auth/register", name="kinde_register")
     */
    public function register(Request $request): RedirectResponse
    {
        $additionalParams = $request->query->only(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->register($additionalParams);
            return $this->redirect($result->getAuthUrl());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/auth/create-org", name="kinde_create_org")
     */
    public function createOrg(Request $request): RedirectResponse
    {
        $additionalParams = $request->query->only(['org_code', 'org_name']);
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

    /**
     * @Route("/api/users", name="api_users", methods={"GET"})
     */
    public function listUsers(): JsonResponse
    {
        try {
            $users = $this->management->users->getUsers();
            return $this->json($users);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/users", name="api_create_user", methods={"POST"})
     */
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

    /**
     * @Route("/api/organizations", name="api_organizations", methods={"GET"})
     */
    public function listOrganizations(): JsonResponse
    {
        try {
            $organizations = $this->management->organizations->getOrganizations();
            return $this->json($organizations);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/organizations", name="api_create_organization", methods={"POST"})
     */
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

    /**
     * @Route("/api/applications", name="api_applications", methods={"GET"})
     */
    public function listApplications(): JsonResponse
    {
        try {
            $applications = $this->management->applications->getApplications();
            return $this->json($applications);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/roles", name="api_roles", methods={"GET"})
     */
    public function listRoles(): JsonResponse
    {
        try {
            $roles = $this->management->roles->getRoles();
            return $this->json($roles);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/permissions", name="api_permissions", methods={"GET"})
     */
    public function listPermissions(): JsonResponse
    {
        try {
            $permissions = $this->management->permissions->getPermissions();
            return $this->json($permissions);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/feature-flags", name="api_feature_flags", methods={"GET"})
     */
    public function listFeatureFlags(): JsonResponse
    {
        try {
            $featureFlags = $this->management->featureFlags->getEnvironmentFeatureFlags();
            return $this->json($featureFlags);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/user-profile", name="api_user_profile", methods={"GET"})
     */
    public function getUserProfile(): JsonResponse
    {
        try {
            $profile = $this->management->oauth->getUserProfileV2();
            return $this->json($profile);
        } catch (ApiException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @Route("/api/users/bulk", name="api_bulk_create_users", methods={"POST"})
     */
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

    private function getUser()
    {
        return $this->get('session')->get('kinde_user');
    }

    private function setUser($user)
    {
        $this->get('session')->set('kinde_user', $user);
        $this->get('session')->set('kinde_authenticated', true);
    }

    private function clearUser()
    {
        $this->get('session')->remove('kinde_user');
        $this->get('session')->remove('kinde_authenticated');
        $this->get('session')->remove('kinde_permissions');
        $this->get('session')->remove('kinde_organization');
    }

    private function getPermissions()
    {
        return $this->get('session')->get('kinde_permissions', []);
    }

    private function getOrganization()
    {
        return $this->get('session')->get('kinde_organization');
    }
} 