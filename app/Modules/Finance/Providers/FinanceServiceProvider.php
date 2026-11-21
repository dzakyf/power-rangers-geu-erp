<?php

namespace App\Modules\Finance\Providers;

use App\Modules\Finance\Rbac\FinancePermissionProvider;
use App\Services\Rbac\PermissionRegistry;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        app(PermissionRegistry::class)->register(
            app(FinancePermissionProvider::class)
        );
    }
}
