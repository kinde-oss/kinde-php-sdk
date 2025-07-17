<?php

namespace Kinde\KindeSDK\Frameworks\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\OAuthException;
use Exception;

class KindeAuthController
{
    protected KindeClientSDK $kindeClient;

    public function __construct(KindeClientSDK $kindeClient)
    {
        $this->kindeClient = $kindeClient;
    }

    /**
     * Handle login request
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $additionalParams = array_intersect_key($queryParams, array_flip(['org_code', 'org_name', 'is_create_org']));
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            
            // The login method should handle the redirect internally
            // This is just a fallback
            return $response
                ->withStatus(302)
                ->withHeader('Location', $result->getAuthUrl());
        } catch (Exception $e) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $token = $this->kindeClient->getToken();
            
            if ($token) {
                // Store user session (you can customize this based on your session handling)
                $userDetails = $this->kindeClient->getUserDetails();
                
                // In a real implementation, you'd store this in your session
                // For now, we'll just redirect to dashboard
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', '/dashboard');
            }
        } catch (OAuthException $e) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/?error=' . urlencode('Authentication failed: ' . $e->getMessage()));
        } catch (Exception $e) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/?error=' . urlencode('An error occurred during authentication'));
        }

        return $response
            ->withStatus(302)
            ->withHeader('Location', '/?error=' . urlencode('Authentication failed'));
    }

    /**
     * Handle registration request
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $additionalParams = array_intersect_key($queryParams, array_flip(['org_code', 'org_name', 'is_create_org']));
        
        try {
            $result = $this->kindeClient->register($additionalParams);
            
            return $response
                ->withStatus(302)
                ->withHeader('Location', $result->getAuthUrl());
        } catch (Exception $e) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle organization creation
     */
    public function createOrg(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $additionalParams = array_intersect_key($queryParams, array_flip(['org_name']));
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            
            return $response
                ->withStatus(302)
                ->withHeader('Location', $result->getAuthUrl());
        } catch (Exception $e) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle logout
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Clear session (customize based on your session handling)
        
        // Redirect to Kinde logout
        $this->kindeClient->logout();
        
        // This should not be reached as logout() should exit
        return $response
            ->withStatus(302)
            ->withHeader('Location', '/');
    }

    /**
     * Get user info
     */
    public function userInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/auth/login');
        }

        $userDetails = $this->kindeClient->getUserDetails();
        $permissions = $this->kindeClient->getPermissions();
        $organization = $this->kindeClient->getOrganization();

        $data = [
            'user' => $userDetails,
            'permissions' => $permissions,
            'organization' => $organization
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Generate portal URL and redirect to Kinde portal
     */
    public function portal(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (!$this->kindeClient->isAuthenticated) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/auth/login');
        }

        $queryParams = $request->getQueryParams();
        $returnUrl = $queryParams['return_url'] ?? '/dashboard';
        $subNav = $queryParams['sub_nav'] ?? 'profile';

        try {
            $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
            return $response
                ->withStatus(302)
                ->withHeader('Location', $portalData['url']);
        } catch (Exception $e) {
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/?error=' . urlencode('Failed to generate portal URL: ' . $e->getMessage()));
        }
    }
} 