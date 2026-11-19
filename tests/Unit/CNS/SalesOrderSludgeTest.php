<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\SalesOrderController;
use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
use App\TankDraining;
use App\User;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class SalesOrderSludgeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = new User();
        $user->username = 'tester';
        $user->Location = 'CNS';
        $this->be($user);
    }

    public function test_new_sales_order_view_renders_two_choices_and_sludge_draining_select2()
    {
        $socode = '001/SO/XI/2026';
        $kemasans = collect();
        $drainings = collect([
            (object) [
                'id'                   => 10,
                'draining_code'        => 'DRN-202611-0001',
                'draining_date'        => '2026-11-18',
                'tank_name'            => 'Tank A',
                'qty_sludge'           => 125.5,
                'available_qty_sludge' => 80.5,
            ],
        ]);

        $html = view('cns.sales.input-order-new', compact('socode', 'kemasans', 'drainings'))->render();

        $this->assertStringContainsString('1. Sludge', $html);
        $this->assertStringContainsString('2. Kemasan dan Ampas', $html);
        $this->assertStringContainsString('id="soFormContainer" style="display:none;"', $html);
        $this->assertStringContainsString('id="sludgeDrainingId"', $html);
        $this->assertStringContainsString('DRN-202611-0001', $html);
        $this->assertStringContainsString('data-qty="80.5"', $html);
    }

    public function test_parse_submitted_items_and_header_payload_for_sludge_order()
    {
        $controller = new SalesOrderController();
        $parseMethod = new ReflectionMethod(SalesOrderController::class, 'parseSubmittedItems');
        $parseMethod->setAccessible(true);
        $headerMethod = new ReflectionMethod(SalesOrderController::class, 'buildHeaderPayload');
        $headerMethod->setAccessible(true);

        $request = Request::create('/cns-sales-order-new-submit', 'POST', [
            'soDate'             => '2026-11-18',
            'soCode'             => '001/SO/XI/2026',
            'soType'             => 'SLUDGE',
            'sludgeDrainingId'   => '10',
            'sludgeDrainingCode' => 'DRN-202611-0001',
            'sludgeQty'          => 50,
            'sludgePrice'        => 2500,
            'taxAmount'          => 1000,
            'shippingAmount'     => 0,
            'otherAmount'        => 0,
        ]);

        [$items, $subtotal] = $parseMethod->invoke($controller, $request, true);
        $this->assertCount(1, $items);
        $this->assertSame('Sludge (DRN-202611-0001)', $items[0]['description']);
        $this->assertSame(50.0, $items[0]['qty']);
        $this->assertSame('KG', $items[0]['unit']);
        $this->assertSame('10', $items[0]['product_code']);
        $this->assertSame(125000.0, $subtotal);

        $header = $headerMethod->invoke($controller, $request, '001/SO/XI/2026', $subtotal);
        $this->assertSame(126000.0, $header['total']);
        if (array_key_exists('so_type', $header)) {
            $this->assertSame('SLUDGE', $header['so_type']);
            $this->assertSame(10, $header['draining_id']);
            $this->assertSame('DRN-202611-0001', $header['draining_code']);
        }
    }

    public function test_validate_sludge_order_checks_available_qty()
    {
        $controller = new class extends SalesOrderController {
            public $mockAvailable = 40.0;
            public $mockMovementAvailable = 25.0;

            protected function getAvailableSludgeQty(int $drainingId, ?int $excludeSoId = null): ?float
            {
                return $drainingId === 10 ? $this->mockAvailable : null;
            }

            protected function getAvailableMovementSludgeQty(int $movementId, ?int $excludeSoId = null): ?float
            {
                return $movementId === 5 ? $this->mockMovementAvailable : null;
            }
        };

        $method = new ReflectionMethod(SalesOrderController::class, 'validateSludgeOrder');
        $method->setAccessible(true);

        $this->assertNotNull($method->invoke($controller, null, 10.0));
        $this->assertNotNull($method->invoke($controller, 10, 0.0));
        $this->assertNotNull($method->invoke($controller, 999, 10.0));
        $this->assertNotNull($method->invoke($controller, 10, 45.0));
        $this->assertNull($method->invoke($controller, 10, 40.0));
        $this->assertNotNull($method->invoke($controller, null, 30.0, null, 5));
        $this->assertNull($method->invoke($controller, null, 25.0, null, 5));
    }

    public function test_detail_view_renders_sludge_section_for_sludge_so()
    {
        $salesOrder = (new SalesOrderCNS())->forceFill([
            'id'            => 1,
            'so_no'         => '001/SO/XI/2026',
            'so_date'       => '2026-11-18',
            'so_type'       => 'SLUDGE',
            'draining_id'   => 10,
            'draining_code' => 'DRN-202611-0001',
            'subtotal'      => 100000,
            'tax'           => 0,
            'shipping'      => 0,
            'other'         => 0,
            'total'         => 100000,
            'status'        => 'PENDING',
        ]);

        $details = collect([
            (new SalesOrderCNSDtl())->forceFill([
                'description'  => 'Sludge (DRN-202611-0001)',
                'qty'          => 50,
                'unit'         => 'KG',
                'unit_price'   => 2000,
                'total'        => 100000,
                'product_code' => '10',
            ]),
        ]);

        $kemasans = collect();
        $drainings = collect([
            (new TankDraining())->forceFill([
                'id'                   => 10,
                'draining_code'        => 'DRN-202611-0001',
                'tank_name'            => 'Tank A',
                'qty_sludge'           => 100,
                'available_qty_sludge' => 80,
            ]),
        ]);

        $html = view('cns.sales.input-order-detail', compact('salesOrder', 'details', 'kemasans', 'drainings'))->render();

        $this->assertStringContainsString('id="soType" name="soType" value="SLUDGE"', $html);
        $this->assertStringContainsString('id="sludgeItemsSection" class="mb-3" style=""', $html);
        $this->assertStringContainsString('id="kemasanItemsSection" style="display:none;"', $html);
        $this->assertStringContainsString('id="salesDetailCol" style="display:none;"', $html);
        $this->assertStringContainsString('id="buyerPaymentTermSlot"', $html);
    }

    public function test_tank_draining_view_renders_monitoring_stock_sludge_tab_and_route()
    {
        $this->assertTrue(app('router')->has('cns-tank-draining.sludge-stock'));
        $this->assertTrue(app('router')->has('cns-tank-draining.sludge-xml'));
        $this->assertTrue(app('router')->has('cns-tank-draining.sludge-moving'));

        $viewContent = file_get_contents(resource_path('views/cns/inventory/tank-draining.blade.php'));
        $this->assertStringContainsString('Monitoring Stock Sludge', $viewContent);
        $this->assertStringContainsString('id="sludgeTankCardsContainer"', $viewContent);
        $this->assertStringContainsString('id="SludgeGrid"', $viewContent);
        $this->assertStringContainsString('id="sludgeMovingFormCard"', $viewContent);
        $this->assertStringContainsString('cns-tank-draining.sludge-stock', $viewContent);
        $this->assertStringContainsString('cns-tank-draining.sludge-xml', $viewContent);
        $this->assertStringContainsString('cns-tank-draining.sludge-moving', $viewContent);
        $this->assertStringContainsString('id="sludgeFilterTank"', $viewContent);
        $this->assertStringContainsString('id="sludgeFilterStartDate"', $viewContent);
        $this->assertStringContainsString('id="sludgeFilterEndDate"', $viewContent);

        $controller = new \App\Http\Controllers\CNS\TankDrainingController();
        $method = new ReflectionMethod(\App\Http\Controllers\CNS\TankDrainingController::class, 'getSludgeStockSummary');
        $method->setAccessible(true);

        $tanks = collect([
            (object) ['id' => 1, 'name' => 'Tank A'],
            (object) ['id' => 2, 'name' => 'Tank B'],
        ]);
        [$rows, $totals, $sources, $warehouses] = $method->invoke($controller, $tanks, []);

        $this->assertCount(2, $rows);
        $this->assertSame('Tank A', $rows[0]['tank_name']);
        $this->assertArrayHasKey('total_sludge_awal', $rows[0]);
        $this->assertArrayHasKey('total_sludge_used', $rows[0]);
        $this->assertArrayHasKey('total_sludge_moving', $rows[0]);
        $this->assertArrayHasKey('sisa_stock_sludge', $rows[0]);
        $this->assertArrayHasKey('sources', $rows[0]);
        $this->assertSame([], $sources);
        $this->assertSame([], $warehouses);
        $this->assertSame([
            'total_awal'        => 0.0,
            'total_used'        => 0.0,
            'total_moving'      => 0.0,
            'total_sisa'        => 0.0,
            'total_sisa_gudang' => 0.0,
        ], $totals);

        [$filteredRows] = $method->invoke($controller, $tanks, ['tank_id' => 2]);
        $this->assertCount(1, $filteredRows);
        $this->assertSame('Tank B', $filteredRows[0]['tank_name']);
    }
}
