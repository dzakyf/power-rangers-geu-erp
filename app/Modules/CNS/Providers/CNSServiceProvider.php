<?php

namespace App\Modules\CNS\Providers;

use App\Modules\CNS\Rbac\CnsPermissionProvider;
use App\Services\Rbac\PermissionRegistry;
use Illuminate\Support\ServiceProvider;

class CNSServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $helpers = __DIR__.'/../helpers.php';

        if (is_file($helpers)) {
            require_once $helpers;
        }

        app(PermissionRegistry::class)->register(
            app(CnsPermissionProvider::class)
        );
    }
}
