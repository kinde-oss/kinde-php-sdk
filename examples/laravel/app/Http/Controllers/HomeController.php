<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kinde\KindeSDK\KindeClientSDK;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function __construct(
        private KindeClientSDK $kindeClient
    ) {}

    public function index()
    {
        $isAuthenticated = $this->kindeClient->isAuthenticated;
        $user = $isAuthenticated ? $this->kindeClient->getUserDetails() : [];
        $permissions = [];
        if ($isAuthenticated) {
            $permissionsData = $this->kindeClient->getPermissions();
            $permissions = $permissionsData['permissions'] ?? [];
        }
        $organization = $isAuthenticated ? $this->kindeClient->getOrganization() : [];

        Log::info('HomeController@index variables', [
            'isAuthenticated' => $isAuthenticated,
            'user' => $user,
            'permissions' => $permissions,
            'organization' => $organization,
        ]);

        return view('home', compact('isAuthenticated', 'user', 'permissions', 'organization'));
    }
} 