<?php

namespace Webkul\Google\Providers;

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

        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'google');

        $this->publishes([
            __DIR__.'/../../publishable/assets' => public_path('vendor/google/assets'),
        ], 'public');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'google');

        Event::listen('admin.layout.head', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::layouts.style');
        });

        Event::listen('admin.leads.view.informations.activity_actions.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::leads.view.activity-action.create');
        });

        Event::listen('admin.activities.edit.form_controls.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::activities.edit');
        });

        $this->app->register(EventServiceProvider::class);

        $this->app->register(ModuleServiceProvider::class);

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
}
