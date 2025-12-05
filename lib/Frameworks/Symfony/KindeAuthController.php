<?php

namespace Kinde\KindeSDK\Frameworks\Symfony;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\OAuthException;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class KindeAuthController extends AbstractController
{
    protected KindeClientSDK $kindeClient;

    public function __construct(
        KindeClientSDK $kindeClient,
        private RequestStack $requestStack)
    {
        $this->kindeClient = $kindeClient;
    }

    #[Route('/auth/login', name: 'kinde_login')]
    public function login(Request $request): RedirectResponse
    {
        $additionalParams = [];
        if ($request->query->has('org_code')) {
            $additionalParams['org_code'] = $request->query->get('org_code');
        }
        if ($request->query->has('org_name')) {
            $additionalParams['org_name'] = $request->query->get('org_name');
        }
        if ($request->query->has('is_create_org')) {
            $additionalParams['is_create_org'] = $request->query->get('is_create_org');
        }
        if ($request->query->has('invitation_code')) {
            $invitationCode = $request->query->get('invitation_code');
            if (!empty($invitationCode)) {
                $additionalParams['invitation_code'] = $invitationCode;
                // When invitation_code is present, use registration flow
                if (!isset($additionalParams['prompt'])) {
                    $additionalParams['prompt'] = 'create';
                }
            }
        }
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            return $this->redirect($result->getAuthUrl());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/auth/callback', name: 'kinde_callback')]
    public function callback(Request $request): RedirectResponse
    {
        $errorParam = $request->query->get('error');
        if ($errorParam) {
            if (strtolower($errorParam) === 'login_link_expired') {
                $reauthState = $request->query->get('reauth_state');
                if ($reauthState) {
                    $decodedAuthState = base64_decode($reauthState);
                    try {
                        $reauthStateArr = json_decode($decodedAuthState, true);
                        if ($reauthStateArr && is_array($reauthStateArr)) {
                            $urlParams = http_build_query($reauthStateArr);
                            $loginRoute = $this->generateUrl('kinde_login');
                            $redirectUrl = $loginRoute . ($urlParams ? ('?' . $urlParams) : '');
                            return $this->redirect($redirectUrl);
                        }
                    } catch (\Exception $ex) {
                        throw new \Exception($ex->getMessage() ?: 'Unknown Error parsing reauth state');
                    }
                }
                // If no reauth_state, just return to login
                return $this->redirectToRoute('kinde_login');
            }
            // For other errors, redirect to home
            return $this->redirectToRoute('home');
        }

        try {
            $result = $this->kindeClient->getToken();
            if ($result) {
                $user = $this->kindeClient->getUserDetails();
                $this->setUser($user);
                // Store permissions and organization info
                $permissions = $this->kindeClient->getPermissions();
                $organization = $this->kindeClient->getOrganization();
                $session = $this->getSession();
                if ($session) {
                    $session->set('kinde_permissions', $permissions);
                    $session->set('kinde_organization', $organization);
                }
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

    #[Route('/auth/register', name: 'kinde_register')]
    public function register(Request $request): RedirectResponse
    {
        $additionalParams = [];
        if ($request->query->has('org_code')) {
            $additionalParams['org_code'] = $request->query->get('org_code');
        }
        if ($request->query->has('org_name')) {
            $additionalParams['org_name'] = $request->query->get('org_name');
        }
        if ($request->query->has('is_create_org')) {
            $additionalParams['is_create_org'] = $request->query->get('is_create_org');
        }
        if ($request->query->has('invitation_code')) {
            $invitationCode = $request->query->get('invitation_code');
            if (!empty($invitationCode)) {
                $additionalParams['invitation_code'] = $invitationCode;
            }
        }
        
        try {
            $result = $this->kindeClient->register($additionalParams);
            return $this->redirect($result->getAuthUrl());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    
    #[Route('/auth/logout', name: 'kinde_logout')]
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
     * @Route("/auth/user-info", name="kinde_user_info")
     */
    public function userInfo(Request $request): Response
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->redirectToRoute('kinde_login');
        }

        $userDetails = $this->kindeClient->getUserDetails();
        $permissions = $this->kindeClient->getPermissions();
        $organization = $this->kindeClient->getOrganization();

        return $this->render('kinde/user-info.html.twig', [
            'userDetails' => $userDetails,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
    }

    #[Route('/auth/portal', name: 'kinde_portal')]
    public function portal(Request $request): RedirectResponse
    {
        $isAuthenticated = $this->getSession()?->get('kinde_authenticated', false);
        
        if (!$isAuthenticated) {
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

    private function getPermissions()
    {
        return $this->getSession()?->get('kinde_permissions', []);
    }

    private function getOrganization()
    {
        return $this->getSession()?->get('kinde_organization');
    }
} 