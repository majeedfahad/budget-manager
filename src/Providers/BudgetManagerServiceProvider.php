<?php

namespace Majeedfahad\BudgetManager\Providers;

use Illuminate\Support\ServiceProvider;

class BudgetManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/budget-manager.php', 'budget-manager');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'budget-manager');

        $this->publishes([
            __DIR__ . '/../../config/budget-manager.php' => config_path('budget-manager.php'),
        ], 'budget-manager-config');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/budget-manager'),
        ], 'budget-manager-translations');
    }
}
