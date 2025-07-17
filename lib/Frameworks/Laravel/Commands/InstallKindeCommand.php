<?php

namespace Kinde\KindeSDK\Frameworks\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallKindeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kinde:install {--force : Overwrite existing files} {--inertia : Install Inertia.js examples}';

    /**
     * The console command description.
     */
    protected $description = 'Install Kinde authentication scaffolding';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Kinde authentication...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'kinde-config',
            '--force' => $this->option('force')
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'kinde-migrations',
            '--force' => $this->option('force')
        ]);

        // Add environment variables to .env
        $this->addEnvironmentVariables();

        // Create example views
        $this->createExampleViews();

        // Create Inertia examples if requested
        if ($this->option('inertia')) {
            $this->createInertiaExamples();
        }

        $this->info('Kinde authentication installed successfully!');
        $this->info('Please configure your Kinde application settings in your .env file.');
        $this->info('Visit https://kinde.com/docs/developer-tools/php-sdk for setup instructions.');

        return Command::SUCCESS;
    }

    /**
     * Add environment variables to .env file
     */
    protected function addEnvironmentVariables(): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->error('.env file not found. Please create one first.');
            return;
        }

        $envContent = File::get($envPath);
        
        $kindeVars = [
            '',
            '# Kinde Authentication',
            'KINDE_DOMAIN=https://your-domain.kinde.com',
            'KINDE_CLIENT_ID=',
            'KINDE_CLIENT_SECRET=',
            'KINDE_REDIRECT_URI=http://localhost:8000/auth/callback',
            'KINDE_LOGOUT_REDIRECT_URI=http://localhost:8000',
            'KINDE_GRANT_TYPE=authorization_code',
            'KINDE_SCOPES=openid profile email offline',
        ];

        if (!str_contains($envContent, 'KINDE_DOMAIN')) {
            File::append($envPath, implode("\n", $kindeVars));
            $this->info('Added Kinde environment variables to .env file.');
        } else {
            $this->info('Kinde environment variables already exist in .env file.');
        }
    }

    /**
     * Create example views
     */
    protected function createExampleViews(): void
    {
        $viewsPath = resource_path('views/kinde');
        
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        // Create login button component
        $loginButton = <<<'BLADE'
@props(['text' => 'Login with Kinde'])

<a href="{{ route('kinde.login') }}" 
   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
    {{ $text }}
</a>
BLADE;

        File::put($viewsPath . '/login-button.blade.php', $loginButton);

        // Create profile view
        $profileView = <<<'BLADE'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Profile</h1>
        
        @if(session('kinde_authenticated'))
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">User Information</h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="font-medium">Name:</span>
                        <span>{{ session('kinde_user')->given_name ?? 'N/A' }} {{ session('kinde_user')->family_name ?? 'N/A' }}</span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Email:</span>
                        <span>{{ session('kinde_user')->email ?? 'N/A' }}</span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Organization:</span>
                        <span>{{ $organization['orgCode'] ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Permissions</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($permissions['permissions'] as $permission)
                            <li>{{ $permission }}</li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('kinde.logout') }}" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Logout
                    </a>
                </div>
            </div>
        @else
            <div class="text-center">
                <p class="text-gray-600 mb-4">You are not authenticated.</p>
                <x-kinde::login-button />
            </div>
        @endif
    </div>
</div>
@endsection
BLADE;

        File::put($viewsPath . '/profile.blade.php', $profileView);

        $this->info('Created example views in resources/views/kinde/');
    }

    /**
     * Create Inertia.js examples
     */
    protected function createInertiaExamples(): void
    {
        $jsPath = resource_path('js/Pages');
        
        if (!File::exists($jsPath)) {
            File::makeDirectory($jsPath, 0755, true);
        }

        // Create Profile page component
        $profileComponent = <<<'VUE'
<template>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Profile</h1>
            
            <div v-if="kinde.isAuthenticated" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">User Information</h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="font-medium">Name:</span>
                        <span>{{ kinde.user?.given_name || 'N/A' }} {{ kinde.user?.family_name || 'N/A' }}</span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Email:</span>
                        <span>{{ kinde.user?.email || 'N/A' }}</span>
                    </div>
                    
                    <div>
                        <span class="font-medium">Organization:</span>
                        <span>{{ kinde.organization?.orgCode || 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Permissions</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="permission in kinde.permissions?.permissions" :key="permission">
                            {{ permission }}
                        </li>
                    </ul>
                </div>
                
                <div class="mt-6">
                    <a href="/auth/logout" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Logout
                    </a>
                </div>
            </div>
            
            <div v-else class="text-center">
                <p class="text-gray-600 mb-4">You are not authenticated.</p>
                <a href="/auth/login" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Login with Kinde
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { kinde } = usePage().props
</script>
VUE;

        File::put($jsPath . '/Profile.vue', $profileComponent);

        // Create Dashboard component
        $dashboardComponent = <<<'VUE'
<template>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
            
            <div v-if="kinde.isAuthenticated" class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Welcome, {{ kinde.user?.given_name || 'User' }}!</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">User Info</h3>
                        <p><strong>Email:</strong> {{ kinde.user?.email }}</p>
                        <p><strong>Organization:</strong> {{ kinde.organization?.orgCode }}</p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Permissions</h3>
                        <ul class="text-sm space-y-1">
                            <li v-for="permission in kinde.permissions?.permissions" :key="permission">
                                • {{ permission }}
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-4">
                    <a href="/auth/profile" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Profile
                    </a>
                    
                    <a href="/auth/logout" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Logout
                    </a>
                </div>
            </div>
            
            <div v-else class="text-center">
                <p class="text-gray-600 mb-4">Please log in to access the dashboard.</p>
                <a href="/auth/login" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Login with Kinde
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const { kinde } = usePage().props
</script>
VUE;

        File::put($jsPath . '/Dashboard.vue', $dashboardComponent);

        // Create Login component
        $loginComponent = <<<'VUE'
<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign in to your account
                </h2>
            </div>
            
            <div class="mt-8 space-y-6">
                <div>
                    <a href="/auth/login" 
                       class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <!-- Heroicon name: lock-closed -->
                            <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Login with Kinde
                    </a>
                </div>
                
                <div class="text-center">
                    <a href="/auth/register" class="text-blue-600 hover:text-blue-500">
                        Don't have an account? Register
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// No props needed for login page
</script>
VUE;

        File::put($jsPath . '/Login.vue', $loginComponent);

        $this->info('Created Inertia.js examples in resources/js/Pages/');
        $this->info('Make sure to set up your Inertia.js routes in your app.js file.');
    }
} 