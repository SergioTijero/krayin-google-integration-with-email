<?php

namespace Webkul\Google\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'google');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'google');

        $this->publishes([
            __DIR__.'/../../publishable/assets'                                            => public_path('google'),
            __DIR__.'/../Resources/views/components/activities/actions/activity.blade.php' => resource_path('views/vendor/admin/components/activities/actions/activity.blade.php'),
            __DIR__.'/../Resources/views/activities/edit.blade.php'                        => resource_path('views/vendor/admin/activities/edit.blade.php'),
        ], 'public');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'google');

        Event::listen('admin.layout.head.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::components.layouts.style');
        });

        Event::listen('admin.components.activities.actions.activity.form_controls.modal.content.controls.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::leads.view.activities.create');
        });

        Event::listen('admin.activities.edit.form_controls.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::activities.google');
        });

        $this->app->register(EventServiceProvider::class);

        $this->app->register(ModuleServiceProvider::class);

        $this->registerGmailTransport();
        
        // Registrar configuraciones para menú y ACL
        $this->registerConfig();
        
        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\Google\Console\Commands\GoogleInstallCommand::class,
            ]);
        }

        $this->overridesModels();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Overrides models
     *
     * @return void
     */
    public function overridesModels()
    {
        $this->app->concord->registerModel(
            \Webkul\User\Contracts\User::class, \Webkul\Google\Models\User::class
        );

        $this->app->concord->registerModel(
            \Webkul\Google\Contracts\GmailMessage::class, \Webkul\Google\Models\GmailMessage::class
        );

        $this->app->concord->registerModel(
            \Webkul\Google\Contracts\GmailAttachment::class, \Webkul\Google\Models\GmailAttachment::class
        );
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/menu.php', 'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/acl.php', 'acl'
        );
    }

    /**
     * Register Gmail transport with Laravel's mail system.
     */
    protected function registerGmailTransport(): void
    {
        $this->app->afterResolving('mail.manager', function ($mailManager) {
            $mailManager->extend('gmail', function (array $config) {
                $google = $this->app->make(\Webkul\Google\Services\Google::class);
                
                // Get the Google account for the user or use default
                $account = $this->getGoogleAccount($config);
                
                if (!$account) {
                    throw new \Exception('No Google account configured for Gmail transport');
                }

                return new \Webkul\Google\Mail\Transport\GmailTransport($google, $account);
            });
        });
    }

    /**
     * Get the Google account to use for sending emails.
     */
    protected function getGoogleAccount(array $config)
    {
        // Try to get account by email if specified in config
        if (!empty($config['username'])) {
            $account = \Webkul\Google\Models\Account::where('email', $config['username'])->first();
            if ($account && $account->gmail_enabled) {
                return $account;
            }
        }

        // Fallback to the first available account with Gmail permissions
        return \Webkul\Google\Models\Account::whereNotNull('token')
            ->where('gmail_enabled', true)
            ->get()
            ->first(function ($account) {
                return $account->hasGmailPermissions();
            });
    }
}
