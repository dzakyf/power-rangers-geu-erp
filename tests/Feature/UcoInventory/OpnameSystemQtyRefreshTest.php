<?php

namespace Tests\Feature\UcoInventory;

use App\Services\WarehouseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Tests\TestCase;

/**
 * Open Stock Opname rows must follow the live container ledger after instock
 * or outstock, while CLOSED opnames keep their historical snapshot.
 */
class OpnameSystemQtyRefreshTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouse(string $taskId = null): array
    {
        $taskId = $taskId ?: 'W-OPN-' . uniqid();
        $id = DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Loc ' . $taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-' . uniqid(),
            'Created_Date'       => now(),
            'Created_By'         => '__test__',
        ]);

        return [$id, $taskId];
    }

    private function seedContainer(int $warehouseId): int
    {
        return DB::table('master_container')->insertGetId([
            'Name'         => 'OPN-C-' . uniqid(),
            'Volume'       => 100000,
            'Warehouse_ID' => $warehouseId,
            'Status'       => 'active',
        ]);
    }

    private function addStockIn(string $taskId, int $containerId, float $qty, ?string $createdAt = null): void
    {
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => $taskId,
            'received_date'     => now()->toDateString(),
            'received_qty'      => $qty,
            'pickup_type'       => 'PICKUP',
            'name_poo'          => 'p',
            'source'            => 'MITRA',
            'coverage'          => 'c',
            'created_date'      => $createdAt ?: now(),
            'created_by'        => '__test__',
            'total'             => 0,
            'price'             => 0,
            'Container_ID'      => $containerId,
            'rejected_qty'      => 0,
        ]);
    }

    private function createOpname(int $warehouseId, int $containerId): int
    {
        return $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $warehouseId,
                'container_id' => $containerId,
                'opname_date'  => now()->toDateString(),
            ])->assertOk()->json()['id'];
    }

    private function instockViaBatch(int $warehouseId, int $containerId, float $qty): void
    {
        $pooId = DB::table('master_poo')->insertGetId([
            'Name'                => 'OPN POO ' . uniqid(),
            'Nearest_Warehouse'   => (string) $warehouseId,
            'Price'               => '1000',
            'Is_Active'           => 1,
            'Location_Coordinate' => '-6.2,106.8',
        ]);
        $woId = DB::table('work_order')->insertGetId([
            'POO_Id'          => $pooId,
            'Code'            => 'WO-OPN-' . uniqid(),
            'Status'          => 'OPEN',
            'Work_Order_Date' => now(),
        ]);
        $doId = DB::table('delivery_order')->insertGetId([
            'Code'       => 'DO-OPN-' . uniqid(),
            'DO_Status'  => 'DONE',
            'DO_Date'    => now()->toDateString(),
            'Created_At' => now(),
        ]);
        $poId = DB::table('pickup_order')->insertGetId([
            'Code'             => 'PO-OPN-' . uniqid(),
            'Qty'              => $qty,
            'Actual_Qty'       => $qty,
            'Qty_Unit'         => 'L',
            'Price'            => 1000,
            'Price_Unit'       => 'L',
            'Package_Capacity' => 1,
            'WO_Id'            => $woId,
            'DO_Id'            => $doId,
            'Created_At'       => now(),
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-submit-batch', [
                'received_date' => now()->toDateString(),
                'source'        => 'MITRA',
                'groups'        => json_encode([[
                    'warehouse'         => $warehouseId,
                    'warehouse_display' => 'TEST',
                    'received_qty'      => $qty,
                    'po_ids'            => [$poId],
                    'do_ids'            => [$doId],
                    'containers'        => [['container_id' => $containerId, 'qty' => $qty]],
                ]]),
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    private function listSystemQty(int $opnameId): string
    {
        $xml = simplexml_load_string(
            $this->actingAs($this->admin())
                ->get('/partner-warehouse-stock-opname-data?format=xml')
                ->assertOk()
                ->getContent()
        );

        foreach ($xml->row as $row) {
            if ((int) $row['ID'] === $opnameId) {
                return (string) $row->cell[5];
            }
        }

        $this->fail('Opname row not found in list');
    }

    /** @test */
    public function batch_instock_refreshes_open_opname_system_qty(): void
    {
        [$wh, $task] = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $this->addStockIn($task, $c, 100);

        $opnameId = $this->createOpname($wh, $c);
        $this->instockViaBatch($wh, $c, 50);

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals(150.0, (float) $op->System_Qty);
        $this->assertEquals(150.0, (float) $this->listSystemQty($opnameId));
    }

    /** @test */
    public function manual_instock_supply_refreshes_open_opname_system_qty(): void
    {
        [$wh, $task] = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $this->addStockIn($task, $c, 100);

        $opnameId = $this->createOpname($wh, $c);

        $this->actingAs($this->admin())
            ->post('/partner-warehouse-open-new-submit', [
                'warehouse_task_id' => $task,
                'Container_ID'      => $c,
                'received_date'     => now()->toDateString(),
                'pickup_type'       => 'PICKUP',
                'name_poo'          => 'p',
                'source'            => 'MITRA',
                'coverage'          => 'c',
                'received_qty'      => 25,
                'price'             => 0,
                'total'             => 0,
                'rejected_qty'      => 0,
            ])->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals(125.0, (float) $op->System_Qty);
    }

    /** @test */
    public function outstock_refresh_updates_open_opname_system_qty(): void
    {
        [$wh, $task] = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $this->addStockIn($task, $c, 100);

        $opnameId = $this->createOpname($wh, $c);

        DB::table('outstock_details')->insert([
            'Outstock_ID' => 999999,
            'Container_ID' => $c,
            'Warehouse_ID' => $wh,
            'Date' => now()->toDateString(),
            'System_Qty' => 0,
            'Actual_Qty' => 30,
            'Notes' => '',
            'Created_At' => now(),
        ]);

        app(WarehouseService::class)->recomputeWarehouseSystemQty($wh);

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals(70.0, (float) $op->System_Qty);
    }

    /** @test */
    public function saving_a_count_uses_the_live_system_quantity(): void
    {
        [$wh, $task] = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $this->addStockIn($task, $c, 100);

        $opnameId = $this->createOpname($wh, $c);
        $this->addStockIn($task, $c, 50); // no movement endpoint, so the opname is still stale

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', [
                'id' => $opnameId,
                'physical_qty' => 150,
            ])->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals(150.0, (float) $op->System_Qty);
        $this->assertEquals(0.0, (float) $op->Difference);
    }

    /** @test */
    public function list_shows_live_system_qty_for_stale_open_opname(): void
    {
        [$wh, $task] = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $this->addStockIn($task, $c, 100);

        $opnameId = $this->createOpname($wh, $c); // stored 100
        $this->addStockIn($task, $c, 50);         // live 150, stored still 100

        $this->assertEquals(150.0, (float) $this->listSystemQty($opnameId));

        $html = $this->actingAs($this->admin())
            ->get('/partner-warehouse-stock-opname-detail?ID=' . $opnameId)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('System Qty:</strong> 150', $html);
    }

    /** @test */
    public function closed_opname_keeps_its_historical_system_qty(): void
    {
        [$wh, $task] = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $this->addStockIn($task, $c, 100);

        $opnameId = $this->createOpname($wh, $c);
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 100])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $this->instockViaBatch($wh, $c, 50);

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED', $op->Status);
        $this->assertEquals(100.0, (float) $op->System_Qty);
    }
}
