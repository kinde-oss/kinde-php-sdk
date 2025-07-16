<?php

namespace Kinde\KindeSDK\Frameworks\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallKindeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kinde:install {--force : Overwrite existing files}';

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
} 