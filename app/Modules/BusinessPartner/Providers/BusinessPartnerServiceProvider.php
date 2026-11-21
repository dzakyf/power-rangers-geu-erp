<?php

namespace App\Modules\BusinessPartner\Providers;

use App\Modules\BusinessPartner\Rbac\BdpPermissionProvider;
use App\Services\Rbac\PermissionRegistry;
use Illuminate\Support\ServiceProvider;

class BusinessPartnerServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        app(PermissionRegistry::class)->register(
            app(BdpPermissionProvider::class)
        );
    }
}
