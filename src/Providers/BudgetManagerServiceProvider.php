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

        $this->publishes([
            __DIR__ . '/../../config/budget-manager.php' => config_path('budget-manager.php'),
        ], 'budget-manager-config');
    }
}
