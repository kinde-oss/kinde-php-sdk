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

class KindeAuthController extends AbstractController
{
    protected KindeClientSDK $kindeClient;

    public function __construct(KindeClientSDK $kindeClient)
    {
        $this->kindeClient = $kindeClient;
    }

    /**
     * @Route("/auth/login", name="kinde_login")
     */
    public function login(Request $request): RedirectResponse
    {
        $additionalParams = $request->query->only(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            
            // The login method should handle the redirect internally
            // This is just a fallback
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
            $token = $this->kindeClient->getToken();
            
            if ($token) {
                // Store user session
                $userDetails = $this->kindeClient->getUserDetails();
                
                // Store in session
                $request->getSession()->set('kinde_user', $userDetails);
                $request->getSession()->set('kinde_token', $token);
                $request->getSession()->set('kinde_authenticated', true);

                $this->addFlash('success', 'Successfully logged in!');
                return $this->redirectToRoute('dashboard');
            }
        } catch (OAuthException $e) {
            $this->addFlash('error', 'Authentication failed: ' . $e->getMessage());
        } catch (Exception $e) {
            $this->addFlash('error', 'An error occurred during authentication');
        }

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
        $additionalParams = $request->query->only(['org_name']);
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            
            return $this->redirect($result->getAuthUrl());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/auth/logout", name="kinde_logout")
     */
    public function logout(Request $request): RedirectResponse
    {
        // Clear session
        $request->getSession()->remove('kinde_user');
        $request->getSession()->remove('kinde_token');
        $request->getSession()->remove('kinde_authenticated');
        
        // Redirect to Kinde logout
        $this->kindeClient->logout();
        
        // This should not be reached as logout() should exit
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/auth/profile", name="kinde_profile")
     */
    public function profile(Request $request): Response
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $this->redirectToRoute('kinde_login');
        }

        $userDetails = $this->kindeClient->getUserDetails();
        $permissions = $this->kindeClient->getPermissions();
        $organization = $this->kindeClient->getOrganization();

        return $this->render('kinde/profile.html.twig', [
            'userDetails' => $userDetails,
            'permissions' => $permissions,
            'organization' => $organization
        ]);
    }
} 