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

        $this->info('Kinde authentication installed successfully!');
        $this->info('Please configure your Kinde application settings in your .env file.');
        $this->info('Visit https://kinde.com/docs/developer-tools/php-sdk for setup instructions.');

        return Command::SUCCESS;
    }
} 