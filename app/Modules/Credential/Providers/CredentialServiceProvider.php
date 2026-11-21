<?php

namespace App\Modules\Credential\Providers;

use App\Modules\Credential\Rbac\CredentialPermissionProvider;
use App\Services\Rbac\PermissionRegistry;
use Illuminate\Support\ServiceProvider;

class CredentialServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        app(PermissionRegistry::class)->register(
            app(CredentialPermissionProvider::class)
        );
    }
}
