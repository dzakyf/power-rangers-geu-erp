<?php

namespace Tests\Unit\CNS;

use Tests\TestCase;
use App\Http\Controllers\CNS\ExportVolumeDashboardController;
use App\Services\CNS\CnsPermissionService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class ExportVolumeDashboardTest extends TestCase
{
    /** @test */
    public function dashboard_routes_are_registered_with_correct_rbac_middleware(): void
    {
        $indexRoute = Route::getRoutes()->getByName('cns-dashboard-export-volume.index');
        $this->assertNotNull($indexRoute);
        $this->assertContains('can:cns.export_volume.view', $indexRoute->gatherMiddleware());

        $dataRoute = Route::getRoutes()->getByName('cns-dashboard-export-volume.data');
        $this->assertNotNull($dataRoute);
        $this->assertContains('can:cns.export_volume.view', $dataRoute->gatherMiddleware());

        $excelRoute = Route::getRoutes()->getByName('cns-dashboard-export-volume.export-excel');
        $this->assertNotNull($excelRoute);
        $this->assertContains('can:cns.export_volume.view', $excelRoute->gatherMiddleware());
    }

    /** @test */
    public function dashboard_menu_and_slug_mappings_are_valid(): void
    {
        $this->assertEquals('export_volume', CnsPermissionService::getSlug('SI01150'));
        $this->assertEquals('SI01150', CnsPermissionService::getMenuId('export_volume'));
        $this->assertEquals('cns.export_volume.view', CnsPermissionService::permissionName('SI01150', 'view'));

        $this->assertEquals('folder_dashboard', CnsPermissionService::getSlug('SM00080'));
        $this->assertEquals('folder_sub_dashboard', CnsPermissionService::getSlug('SS00150'));

        $menuFile = database_path('seeds/data/menu_lists_cns.php');
        $cnsMenus = require $menuFile;
        $this->assertEquals(1, $cnsMenus['SM00080'][5], 'Dashboard folder SM00080 must have Urutan = 1 (top position)');
        $this->assertEquals(3, $cnsMenus['SM00065'][5], 'Contract folder SM00065 must have Urutan = 3');
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

    /** @test */
    public function data_ajax_returns_valid_json_structure(): void
    {
        $controller = new ExportVolumeDashboardController();
        $request = Request::create('/cns-dashboard-export-volume-data', 'GET', [
            'year'      => '2026',
            'month'     => 'all',
            'consignee' => 'all',
            'country'   => 'all',
            'incoterm'  => 'all',
        ]);

        $response = $controller->dataAjax($request);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertArrayHasKey('kpis', $data);
        $this->assertArrayHasKey('todayStats', $data);
        $this->assertArrayHasKey('monthlyTrend', $data);
        $this->assertArrayHasKey('incotermChart', $data);
        $this->assertArrayHasKey('statusChart', $data);
        $this->assertArrayHasKey('topConsigneesChart', $data);
        $this->assertArrayHasKey('countryChart', $data);
        $this->assertArrayHasKey('shipments', $data);

        $this->assertArrayHasKey('total_volume_mt', $data['kpis']);
        $this->assertArrayHasKey('total_containers', $data['kpis']);
        $this->assertArrayHasKey('total_shipments', $data['kpis']);
        $this->assertArrayHasKey('total_consignees', $data['kpis']);
        $this->assertArrayHasKey('total_pending', $data['kpis']);

        $this->assertArrayHasKey('shipments', $data['todayStats']);
        $this->assertArrayHasKey('containers', $data['todayStats']);
        $this->assertArrayHasKey('volume_mt', $data['todayStats']);
        $this->assertArrayHasKey('last_update', $data['todayStats']);

        $this->assertCount(12, $data['monthlyTrend']['categories']);
        $this->assertCount(12, $data['monthlyTrend']['volume']);
        $this->assertCount(12, $data['monthlyTrend']['containers']);
    }

    /** @test */
    public function export_excel_returns_streamed_csv_response(): void
    {
        $controller = new ExportVolumeDashboardController();
        $request = Request::create('/cns-dashboard-export-volume-excel', 'GET', [
            'year' => '2026',
        ]);

        $response = $controller->exportExcel($request);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Export_Volume_2026_', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function default_month_is_current_month(): void
    {
        $controller = new ExportVolumeDashboardController();
        $request = Request::create('/cns-dashboard-export-volume', 'GET');

        $view = $controller->index($request);
        $this->assertEquals((string) (int) date('n'), $view->getData()['selectedMonth']);
    }
}
