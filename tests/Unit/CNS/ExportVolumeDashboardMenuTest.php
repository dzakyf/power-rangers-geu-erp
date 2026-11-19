<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;
use App\Services\CNS\CnsPermissionService;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class ExportVolumeDashboardMenuTest extends TestCase
{
    /** @test */
    public function dashboard_routes_are_registered_with_rbac_middleware(): void
    {
        foreach ([
            'cns-dashboard-export-volume.index',
            'cns-dashboard-export-volume.data',
            'cns-dashboard-export-volume.export-excel',
        ] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route {$name} must be registered.");
            $this->assertContains('can:cns.export_volume.view', $route->gatherMiddleware());
        }
    }

    /** @test */
    public function dashboard_menu_and_slug_mappings_use_the_power_safe_ids(): void
    {
        $this->assertSame('folder_dashboard', CnsPermissionService::getSlug('SM00080'));
        $this->assertSame('folder_sub_dashboard', CnsPermissionService::getSlug('SS00150'));
        $this->assertSame('export_volume', CnsPermissionService::getSlug('SI01150'));
        $this->assertSame('goods_receipt', CnsPermissionService::getSlug('SI01075'));

        $menuFile = database_path('seeds/data/menu_lists_cns.php');
        $cnsMenus = require $menuFile;

        $this->assertSame('Dashboard', $cnsMenus['SM00080'][0]);
        $this->assertSame('Dashboard', $cnsMenus['SS00150'][0]);
        $this->assertSame('cns-dashboard-export-volume', $cnsMenus['SI01150'][1]);
        $this->assertSame('Goods Receipt', $cnsMenus['SI01075'][0]);
        $this->assertArrayNotHasKey('SI01055', $cnsMenus);
        $this->assertArrayNotHasKey('SI00306', $cnsMenus);
        $this->assertSame('1', $cnsMenus['SM00080'][5]);
        $this->assertSame('2', $cnsMenus['SM00065'][5]);
    }

    /** @test */
    public function admin_and_adm_users_are_authorized_for_export_volume(): void
    {
        $admin = new User();
        $admin->username = 'admin';
        $this->assertTrue(Gate::forUser($admin)->check('cns.export_volume.view'));

        $adm = new User();
        $adm->username = 'user_adm';
        $adm->Priv_ID = 'ADM';
        $this->assertTrue(Gate::forUser($adm)->check('cns.export_volume.view'));
    }
}
