<?php

namespace Kinde\KindeSDK\Frameworks\Laravel\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\OAuthException;
use Inertia\Inertia;
use Inertia\Response;
use Exception;

class KindeAuthController extends Controller
{
    protected KindeClientSDK $kindeClient;

    public function __construct(KindeClientSDK $kindeClient)
    {
        $this->kindeClient = $kindeClient;
    }

    /**
     * Redirect to Kinde login page
     */
    public function login(Request $request): RedirectResponse
    {
        $additionalParams = $request->only(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->login($additionalParams);
            
            // The login method should handle the redirect internally
            // This is just a fallback
            return redirect()->away($result->getAuthUrl());
        } catch (Exception $e) {
            return redirect()->route('home')->withErrors(['auth' => $e->getMessage()]);
        }
    }

    /**
     * Handle the OAuth callback from Kinde
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $token = $this->kindeClient->getToken();
            
            if ($token) {
                // Store user session
                $userDetails = $this->kindeClient->getUserDetails();
                
                // You can customize this based on your user model
                session([
                    'kinde_user' => $userDetails,
                    'kinde_token' => $token,
                    'kinde_authenticated' => true
                ]);

                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Successfully logged in!');
            }
        } catch (OAuthException $e) {
            return redirect()->route('home')
                ->withErrors(['auth' => 'Authentication failed: ' . $e->getMessage()]);
        } catch (Exception $e) {
            return redirect()->route('home')
                ->withErrors(['auth' => 'An error occurred during authentication']);
        }

        return redirect()->route('home')
            ->withErrors(['auth' => 'Authentication failed']);
    }

    /**
     * Register a new user
     */
    public function register(Request $request): RedirectResponse
    {
        $additionalParams = $request->only(['org_code', 'org_name', 'is_create_org']);
        
        try {
            $result = $this->kindeClient->register($additionalParams);
            
            return redirect()->away($result->getAuthUrl());
        } catch (Exception $e) {
            return redirect()->route('home')->withErrors(['auth' => $e->getMessage()]);
        }
    }

    /**
     * Create an organization
     */
    public function createOrg(Request $request): RedirectResponse
    {
        $additionalParams = $request->only(['org_name']);
        $additionalParams['is_create_org'] = 'true';
        
        try {
            $result = $this->kindeClient->createOrg($additionalParams);
            
            return redirect()->away($result->getAuthUrl());
        } catch (Exception $e) {
            return redirect()->route('home')->withErrors(['auth' => $e->getMessage()]);
        }
    }

    /**
     * Logout the user
     */
    public function logout(): RedirectResponse
    {
        // Clear session
        session()->forget(['kinde_user', 'kinde_token', 'kinde_authenticated']);
        
        // Redirect to Kinde logout
        $this->kindeClient->logout();
        
        // This should not be reached as logout() should exit
        return redirect()->route('home');
    }

    /**
     * Get user info - supports both Blade and Inertia
     */
    public function userInfo(Request $request)
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->route('login');
        }

        $userDetails = $this->kindeClient->getUserDetails();
        $permissions = $this->kindeClient->getPermissions();
        $organization = $this->kindeClient->getOrganization();

        // Check if this is an Inertia request
        if ($request->header('X-Inertia')) {
            return Inertia::render('UserInfo', [
                'user' => $userDetails,
                'permissions' => $permissions,
                'organization' => $organization,
                'isAuthenticated' => true
            ]);
        }

        // Fallback to Blade view
        return view('kinde::user-info', compact('userDetails', 'permissions', 'organization'));
    }

    /**
     * Generate portal URL and redirect to Kinde portal
     */
    public function portal(Request $request): RedirectResponse
    {
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->route('login');
        }

        $returnUrl = $request->get('return_url', route('dashboard'));
        $subNav = $request->get('sub_nav', 'profile');

        try {
            $portalData = $this->kindeClient->generatePortalUrl($returnUrl, $subNav);
            return redirect()->away($portalData['url']);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['portal' => 'Failed to generate portal URL: ' . $e->getMessage()]);
        }
    }

    /**
     * Get user data for Inertia shared data
     */
    public function getUserData(): array
    {
        if (!$this->kindeClient->isAuthenticated) {
            return [
                'isAuthenticated' => false,
                'user' => null,
                'permissions' => [],
                'organization' => null
            ];
        }

        return [
            'isAuthenticated' => true,
            'user' => $this->kindeClient->getUserDetails(),
            'permissions' => $this->kindeClient->getPermissions(),
            'organization' => $this->kindeClient->getOrganization()
        ];
    }
} 