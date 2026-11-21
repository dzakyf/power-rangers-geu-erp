<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Services\Rbac\PermissionRegistry;
use App\Support\Modules;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PermissionRegistry::class);

        // Register module service providers for every enabled module.
        foreach (Modules::enabledProviders() as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Register per-module migration paths for every enabled module. The
        // single shared `migrations` ledger means these are additive: a slim
        // deployment only loads its own module folders (see config/modules.php).
        foreach (Modules::enabled() as $module) {
            $path = database_path("migrations/{$module}");

            if (is_dir($path)) {
                $this->loadMigrationsFrom($path);
            }
        }
    }
}
