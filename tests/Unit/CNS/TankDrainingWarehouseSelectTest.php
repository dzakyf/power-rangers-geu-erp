<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\TankDrainingController;
use App\MasterTank;
use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use App\TankDraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class TankDrainingWarehouseSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** @test */
    public function apipi_cached_warehouses_excludes_records_with_deleted_at_not_null()
    {
        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function warehouses(int $page = 1, int $limit = 200, ?string $type = null, ?string $search = null): array
            {
                return [
                    'data' => [
                        ['id' => 1, 'name' => 'Gudang Aktif A', 'type' => 'site', 'active' => 1, 'deleted_at' => null],
                        ['id' => 2, 'name' => 'Gudang Terhapus B', 'type' => 'site', 'active' => 1, 'deleted_at' => '2026-05-01 10:00:00'],
                        ['id' => 3, 'name' => 'Gudang Aktif C', 'type' => 'non-site', 'active' => 1],
                        ['id' => 4, 'name' => 'Gudang Inaktif D', 'type' => 'site', 'active' => 0, 'deleted_at' => null],
                    ],
                    'pagination' => ['has_next' => false],
                ];
            }
        };

        $result = $fakeClient->cachedWarehouses();
        $names = array_column($result, 'name');

        $this->assertContains('Gudang Aktif A', $names);
        $this->assertContains('Gudang Aktif C', $names);
        $this->assertNotContains('Gudang Terhapus B', $names);
        $this->assertNotContains('Gudang Inaktif D', $names);
    }

    /** @test */
    public function new_tank_draining_view_has_select2_element_for_moving_target_external()
    {
        $drainingCode = 'DRN-202611-0001';
        $tanks = collect([
            (object) ['id' => 1, 'name' => 'Tangki 1', 'capacity' => 50000],
        ]);
        $salesOrders = collect([]);

        $viewHtml = view('cns.inventory.tank-draining-new', compact('drainingCode', 'tanks', 'salesOrders'))->render();

        $this->assertStringContainsString('<select class="text-black form-control" id="new_moving_target_external" name="moving_target_external"', $viewHtml);
        $this->assertStringNotContainsString('<input type="text" class="text-black form-control" id="new_moving_target_external"', $viewHtml);
        $this->assertStringContainsString('loadMovingExternalWarehouses', $viewHtml);
        $this->assertStringContainsString('cns-master-warehouses', $viewHtml);
    }

    /** @test */
    public function detail_tank_draining_view_has_select2_element_and_prepopulates_target_external()
    {
        $draining = new TankDraining([
            'id' => 99,
            'draining_code' => 'DRN-202611-0099',
            'draining_date' => '2026-11-13',
            'tank_id' => 1,
            'tank_name' => 'Tangki 1',
            'qty' => 15000,
            'target_type' => 'MOVING',
            'moving_target_type' => 'EXTERNAL',
            'moving_target_external' => 'Gudang Bitung',
            'bast_document_path' => 'bast/sample.pdf',
            'bast_document_name' => 'sample.pdf',
        ]);
        $draining->id = 99;

        $tanks = collect([
            (object) ['id' => 1, 'name' => 'Tangki 1', 'capacity' => 50000],
        ]);
        $salesOrders = collect([]);
        $currentStock = 25000;

        $viewHtml = view('cns.inventory.tank-draining-detail', compact('draining', 'tanks', 'salesOrders', 'currentStock'))->render();

        $this->assertStringContainsString('<select class="text-black form-control" id="edit_moving_target_external" name="moving_target_external"', $viewHtml);
        $this->assertStringNotContainsString('<input type="text" class="text-black form-control" id="edit_moving_target_external"', $viewHtml);
        $this->assertStringContainsString('Gudang Bitung', $viewHtml);
        $this->assertStringContainsString('loadEditMovingExternalWarehouses', $viewHtml);
        $this->assertStringContainsString('cns-master-warehouses', $viewHtml);
    }

    /** @test */
    public function validation_rules_require_moving_target_external_when_external()
    {
        $controller = new TankDrainingController();
        $method = new ReflectionMethod(TankDrainingController::class, 'validateDrainingRequest');
        $method->setAccessible(true);

        $rules = $method->invoke($controller, Request::create('/', 'POST'))->getRules();
        $this->assertArrayHasKey('moving_target_external', $rules);
        $this->assertContains('required_if:moving_target_type,EXTERNAL', $rules['moving_target_external']);
    }
}
