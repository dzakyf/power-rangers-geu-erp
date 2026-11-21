<?php

namespace App\Modules\HCGA\Providers;

use App\Modules\HCGA\Rbac\HcgaPermissionProvider;
use App\Services\Rbac\PermissionRegistry;
use Illuminate\Support\ServiceProvider;

class HCGAServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        app(PermissionRegistry::class)->register(
            app(HcgaPermissionProvider::class)
        );
    }
}
