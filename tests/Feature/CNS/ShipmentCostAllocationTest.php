<?php

namespace Tests\Feature\CNS;

use App\CnsShipmentCostAllocation;
use App\CnsShipmentCostAllocationItem;
use App\Services\CNS\CnsPermissionService;
use App\ShippingInstruction;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShipmentCostAllocationTest extends TestCase
{
    public function test_menu_and_permission_mapping_are_registered()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayHasKey('SS00097', $menus);
        $this->assertSame(['Cost Control', '#', 'CNS', 3, 'SM00058', 2], $menus['SS00097']);
        $this->assertSame('folder_cost_control', CnsPermissionService::getSlug('SS00097'));

        $this->assertArrayHasKey('SI00351', $menus);
        $this->assertSame(['Cost Allocation per Shipment', 'cns-export-cost-allocation', 'CNS', 4, 'SS00097', 1], $menus['SI00351']);
        $this->assertSame('cost_allocation_shipment', CnsPermissionService::getSlug('SI00351'));
        $this->assertSame('SI00351', CnsPermissionService::getMenuId('cost_allocation_shipment'));
    }

    public function test_cost_allocation_routes_and_middleware_are_registered()
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $expected = [
            'cns-export-cost-allocation'               => 'can:cns.cost_allocation_shipment.view',
            'cns-export-cost-allocation-edit/{id}'     => 'can:cns.cost_allocation_shipment.view',
            'cns-export-cost-allocation-export-csv'    => 'can:cns.cost_allocation_shipment.view',
            'cns-export-cost-allocation-create'        => 'can:cns.cost_allocation_shipment.create',
            'cns-export-cost-allocation-store'         => 'can:cns.cost_allocation_shipment.create',
            'cns-export-cost-allocation-update/{id}'   => 'can:cns.cost_allocation_shipment.edit',
            'cns-export-cost-allocation-finalize/{id}' => 'can:cns.cost_allocation_shipment.edit',
            'cns-export-cost-allocation-reopen/{id}'   => 'can:cns.cost_allocation_shipment.edit',
            'cns-export-cost-allocation-delete/{id}'   => 'can:cns.cost_allocation_shipment.delete',
        ];

        foreach ($expected as $uri => $middleware) {
            $route = $routes->first(function ($r) use ($uri) {
                return $r->uri() === $uri;
            });
            $this->assertNotNull($route, "Route {$uri} not found");
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }

    public function test_currency_conversion_calculates_usd_and_idr_accurately()
    {
        $usdItem = CnsShipmentCostAllocationItem::convertAmounts('USD', 1500, 16000);
        $this->assertSame('USD', $usdItem['currency']);
        $this->assertEquals(1500.0, $usdItem['amount_usd']);
        $this->assertEquals(24000000.0, $usdItem['amount_idr']);

        $idrItem = CnsShipmentCostAllocationItem::convertAmounts('IDR', 3200000, 16000);
        $this->assertSame('IDR', $idrItem['currency']);
        $this->assertEquals(200.0, $idrItem['amount_usd']);
        $this->assertEquals(3200000.0, $idrItem['amount_idr']);
    }

    public function test_recalculate_totals_computes_category_and_unit_costs()
    {
        $alloc = (new CnsShipmentCostAllocation())->forceFill([
            'volume_mt'       => 100.0,
            'container_count' => 2,
        ]);

        $items = collect([
            (new CnsShipmentCostAllocationItem())->forceFill(['category' => 'freight', 'amount_usd' => 2000, 'amount_idr' => 32000000]),
            (new CnsShipmentCostAllocationItem())->forceFill(['category' => 'trucking', 'amount_usd' => 500, 'amount_idr' => 8000000]),
            (new CnsShipmentCostAllocationItem())->forceFill(['category' => 'surveyor', 'amount_usd' => 300, 'amount_idr' => 4800000]),
            (new CnsShipmentCostAllocationItem())->forceFill(['category' => 'port', 'amount_usd' => 200, 'amount_idr' => 3200000]),
        ]);
        $alloc->setRelation('items', $items);
        $alloc->recalculateTotals();

        $this->assertEquals(2000.0, $alloc->total_freight_usd);
        $this->assertEquals(500.0, $alloc->total_trucking_usd);
        $this->assertEquals(300.0, $alloc->total_surveyor_usd);
        $this->assertEquals(200.0, $alloc->total_port_usd);
        $this->assertEquals(3000.0, $alloc->total_cost_usd);
        $this->assertEquals(48000000.0, $alloc->total_cost_idr);
        $this->assertEquals(30.0, $alloc->cost_per_mt_usd);
        $this->assertEquals(1500.0, $alloc->cost_per_container_usd);
    }

    public function test_full_cost_allocation_lifecycle_with_sqlite()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension not available in this environment.');
        }

        $this->setupSqliteSchema();

        $admin = new User();
        $admin->id = 1;
        $admin->username = 'admin';
        $admin->name = 'Admin';
        $admin->Priv_ID = 'ADM';
        $this->be($admin);

        DB::table('shipping_instructions')->insert([
            'SI_No'       => 'SI/EXP/001/XI/2026',
            'Final_SI_No' => 'FINALSI/EXP/001/XI/2026',
            'Order_No'    => 'ORD-001',
            'Consignee'   => 'BUYER-1',
            'POD'         => 'Rotterdam',
            'Qty'         => '100',
            'Status'      => 'FINAL',
        ]);

        DB::table('vessel_schedules')->insert([
            'Task_ID'  => 'TSK-001',
            'Order_No' => 'ORD-001',
            'BC_No'    => 'BC-2026-001',
            'Vessel'   => 'MV MAERSK',
            'POL'      => 'Belawan',
            'POD'      => 'Rotterdam',
            'Qty'      => '120',
        ]);

        DB::table('vessel_schedule_dtls')->insert([
            ['Task_ID' => 'TSK-001', 'Order_No' => 'ORD-001', 'SI_No' => 'SI/EXP/001/XI/2026', 'Container' => 'CONT-01', 'Netto_MT' => 50.0, 'Status' => 'ACTIVE'],
            ['Task_ID' => 'TSK-001', 'Order_No' => 'ORD-001', 'SI_No' => 'SI/EXP/001/XI/2026', 'Container' => 'CONT-02', 'Netto_MT' => 50.0, 'Status' => 'ACTIVE'],
        ]);

        // Store DRAFT using bc_no
        $res = $this->post('/cns-export-cost-allocation-store', [
            'bc_no'                 => 'BC-2026-001',
            'default_exchange_rate' => 16000,
            'notes'                 => 'Shipment pertama',
            'items'                 => [
                [
                    'category'      => 'freight',
                    'description'   => 'Ocean Freight',
                    'vendor_name'   => 'Maersk',
                    'currency'      => 'USD',
                    'amount'        => 2000,
                    'exchange_rate' => 16000,
                ],
                [
                    'category'      => 'trucking',
                    'description'   => 'Trucking Pabrik - Pelabuhan',
                    'vendor_name'   => 'PT Angkut',
                    'currency'      => 'IDR',
                    'amount'        => 8000000,
                    'exchange_rate' => 16000,
                ],
                [
                    'category'      => 'surveyor',
                    'description'   => 'Surveyor Fee',
                    'currency'      => 'USD',
                    'amount'        => 300,
                    'exchange_rate' => 16000,
                ],
                [
                    'category'      => 'port',
                    'description'   => 'THC & Port Handling',
                    'currency'      => 'IDR',
                    'amount'        => 3200000,
                    'exchange_rate' => 16000,
                ],
            ],
        ]);
        $res->assertRedirect();

        $alloc = CnsShipmentCostAllocation::first();
        $this->assertNotNull($alloc);
        $this->assertSame('BC-2026-001', $alloc->bc_no);
        $this->assertSame('DRAFT', $alloc->status);
        $this->assertEquals(2000.0, $alloc->total_freight_usd);
        $this->assertEquals(500.0, $alloc->total_trucking_usd);
        $this->assertEquals(300.0, $alloc->total_surveyor_usd);
        $this->assertEquals(200.0, $alloc->total_port_usd);
        $this->assertEquals(3000.0, $alloc->total_cost_usd);
        $this->assertEquals(48000000.0, $alloc->total_cost_idr);
        $this->assertEquals(100.0, $alloc->volume_mt);
        $this->assertEquals(2, $alloc->container_count);
        $this->assertEquals(30.0, $alloc->cost_per_mt_usd);
        $this->assertEquals(1500.0, $alloc->cost_per_container_usd);

        // Finalize
        $this->post("/cns-export-cost-allocation-finalize/{$alloc->id}")->assertRedirect();
        $alloc->refresh();
        $this->assertSame('FINAL', $alloc->status);

        // Delete blocked while FINAL
        $this->delete("/cns-export-cost-allocation-delete/{$alloc->id}")->assertStatus(422);

        // Reopen to DRAFT
        $this->post("/cns-export-cost-allocation-reopen/{$alloc->id}")->assertRedirect();
        $alloc->refresh();
        $this->assertSame('DRAFT', $alloc->status);

        // CSV Export
        $csvRes = $this->get('/cns-export-cost-allocation-export-csv');
        $csvRes->assertStatus(200);
        $csvContent = $csvRes->streamedContent();
        $this->assertStringContainsString('BC-2026-001', $csvContent);
        $this->assertStringContainsString('SI/EXP/001/XI/2026', $csvContent);

        // Delete allowed when DRAFT
        $this->delete("/cns-export-cost-allocation-delete/{$alloc->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertNull(CnsShipmentCostAllocation::find($alloc->id));
    }

    public function test_bc_allocation_falls_back_to_vessel_schedule_qty_and_rejects_unknown_bc()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite extension not available in this environment.');
        }

        $this->setupSqliteSchema();

        $admin = new User();
        $admin->id = 1;
        $admin->username = 'admin';
        $admin->name = 'Admin';
        $admin->Priv_ID = 'ADM';
        $this->be($admin);

        DB::table('vessel_schedules')->insert([
            'Task_ID'  => 'TSK-002',
            'Order_No' => 'ORD-002',
            'BC_No'    => 'BC-2026-002',
            'Vessel'   => 'MV EVERGREEN',
            'POL'      => 'Dumai',
            'POD'      => 'Singapore',
            'Qty'      => '85.5',
        ]);

        // Unknown BC_No rejected
        $this->post('/cns-export-cost-allocation-store', [
            'bc_no'                 => 'BC-UNKNOWN',
            'default_exchange_rate' => 16000,
        ])->assertSessionHasErrors('bc_no');

        // Valid BC_No without stuffing falls back to Qty
        $this->post('/cns-export-cost-allocation-store', [
            'bc_no'                 => 'BC-2026-002',
            'default_exchange_rate' => 16000,
        ])->assertRedirect();

        $alloc = CnsShipmentCostAllocation::where('bc_no', 'BC-2026-002')->first();
        $this->assertNotNull($alloc);
        $this->assertEquals(85.5, $alloc->volume_mt);
        $this->assertEquals(0, $alloc->container_count);
    }

    private function setupSqliteSchema(): void
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        DB::purge('sqlite');

        Schema::create('shipping_instructions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('SI_No');
            $table->string('Final_SI_No')->nullable();
            $table->string('Order_No')->nullable();
            $table->string('Consignee')->nullable();
            $table->string('POL')->nullable();
            $table->string('POD')->nullable();
            $table->date('ETD')->nullable();
            $table->string('Qty')->nullable();
            $table->string('Status')->nullable();
        });

        Schema::create('vessel_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Task_ID')->nullable();
            $table->string('Order_No')->nullable();
            $table->string('BC_No')->nullable();
            $table->string('Vessel')->nullable();
            $table->string('POL')->nullable();
            $table->string('POD')->nullable();
            $table->date('ETD')->nullable();
            $table->date('ATD')->nullable();
            $table->string('Qty')->nullable();
        });

        Schema::create('vessel_schedule_dtls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Task_ID')->nullable();
            $table->string('Order_No')->nullable();
            $table->string('SI_No')->nullable();
            $table->string('Container')->nullable();
            $table->decimal('Netto_MT', 18, 4)->default(0);
            $table->string('Status')->default('ACTIVE');
        });

        Schema::create('cns_shipment_cost_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('allocation_no')->unique();
            $table->string('bc_no')->nullable()->unique();
            $table->unsignedInteger('si_id')->nullable();
            $table->string('si_no')->nullable();
            $table->string('final_si_no')->nullable();
            $table->string('order_no')->nullable();
            $table->decimal('default_exchange_rate', 15, 4)->default(16000);
            $table->decimal('total_freight_usd', 18, 4)->default(0);
            $table->decimal('total_trucking_usd', 18, 4)->default(0);
            $table->decimal('total_surveyor_usd', 18, 4)->default(0);
            $table->decimal('total_port_usd', 18, 4)->default(0);
            $table->decimal('total_cost_usd', 18, 4)->default(0);
            $table->decimal('total_cost_idr', 20, 2)->default(0);
            $table->decimal('volume_mt', 18, 4)->default(0);
            $table->integer('container_count')->default(0);
            $table->decimal('cost_per_mt_usd', 18, 4)->default(0);
            $table->decimal('cost_per_container_usd', 18, 4)->default(0);
            $table->string('status', 20)->default('DRAFT');
            $table->text('notes')->nullable();
            $table->string('finalized_by')->nullable();
            $table->dateTime('finalized_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('cns_shipment_cost_allocation_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('allocation_id');
            $table->string('category', 20);
            $table->string('description', 255);
            $table->string('vendor_name', 150)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('amount', 18, 4)->default(0);
            $table->decimal('exchange_rate', 15, 4)->default(16000);
            $table->decimal('amount_usd', 18, 4)->default(0);
            $table->decimal('amount_idr', 20, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }
}
