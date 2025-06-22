<?php

namespace Webkul\Google\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

class GoogleInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:install {--force : Force installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure Google Integration package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Installing Google Integration Package...');

        // 1. Check if routes are registered
        $this->checkRoutes();

        // 2. Publish configuration
        $this->publishConfig();

        // 3. Clear caches
        $this->clearCaches();

        // 4. Check menu registration
        $this->checkMenuRegistration();

        $this->info('✅ Google Integration installation completed!');
        
        $this->info('📧 To enable Gmail integration:');
        $this->info('   1. Configure your Google Cloud Console');
        $this->info('   2. Set MAIL_MAILER=gmail in your .env file');
        $this->info('   3. Go to Admin → Google Integration');

        return 0;
    }

    /**
     * Check if Google routes are registered
     */
    protected function checkRoutes()
    {
        $routes = collect(Route::getRoutes())->filter(function($route) {
            return str_contains($route->uri(), 'google');
        });

        if ($routes->count() > 0) {
            $this->info("✅ Google routes registered ({$routes->count()} routes found)");
        } else {
            $this->error('❌ Google routes not found');
            $this->info('   Try running: php artisan route:cache');
        }
    }

    /**
     * Publish configuration files
     */
    protected function publishConfig()
    {
        $this->info('📦 Publishing configuration files...');
        
        Artisan::call('vendor:publish', [
            '--provider' => 'Webkul\Google\Providers\GoogleServiceProvider',
            '--force' => $this->option('force')
        ]);

        $this->info('✅ Configuration files published');
    }

    /**
     * Clear all caches
     */
    protected function clearCaches()
    {
        $this->info('🧹 Clearing caches...');
        
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:clear');

        $this->info('✅ Caches cleared');
    }

    /**
     * Check menu registration
     */
    protected function checkMenuRegistration()
    {
        if (config('menu.admin.google')) {
            $this->info('✅ Google menu registered in config');
        } else {
            $this->warn('⚠️  Google menu not found in config');
            $this->info('   Add this to your AppServiceProvider boot() method:');
            $this->info('   ');
            $this->info('   if (class_exists(\'\Webkul\Google\Providers\GoogleServiceProvider\')) {');
            $this->info('       $this->mergeConfigFrom(');
            $this->info('           base_path(\'vendor/sergiotijero/krayin-google-integration-with-email/src/Config/menu.php\'),');
            $this->info('           \'menu.admin\'');
            $this->info('       );');
            $this->info('   }');
        }
    }
}
