<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Opname realignment: the system figure is refreshed from the latest ledger
 * at the CLOSED transition, and the balance rebaselines to the physical count
 * anchored at Closed_At (approval), not at draft creation.
 */
class OpnameRealignmentTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouse(string $taskId): int
    {
        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Loc '.$taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-'.uniqid(),
            'Created_Date'       => now(),
            'Created_By'         => '__test_op__',
        ]);
    }

    private function seedContainer(int $whId): int
    {
        return DB::table('master_container')->insertGetId([
            'Name' => 'OP-C '.uniqid(), 'Volume' => 100000,
            'Warehouse_ID' => $whId, 'Status' => 'active',
        ]);
    }

    private function addStockIn(int $whId, string $taskId, int $cid, float $qty, ?string $createdAt = null): void
    {
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => $taskId, 'received_date' => now()->toDateString(), 'received_qty' => $qty,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => $createdAt ?: now(), 'created_by' => '__test_op__', 'total' => 0, 'Container_ID' => $cid,
        ]);
    }

    private function createOpname(int $whId, int $cid): int
    {
        return $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $whId,
                'container_id' => $cid,
                'opname_date'  => now()->toDateString(),
            ])->assertOk()->json()['id'];
    }

    /** @test */
    public function auto_finalize_refreshes_system_qty_from_latest_ledger()
    {
        $wh = $this->seedWarehouse('TEST-OP-A');
        $c  = $this->seedContainer($wh);

        $this->addStockIn($wh, 'TEST-OP-A', $c, 100);

        $opnameId = $this->createOpname($wh, $c); // System snapshot = 100

        $this->addStockIn($wh, 'TEST-OP-A', $c, 50); // ledger now 150, after draft creation

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 150])
            ->assertOk();

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals(150.0, (float) $op->System_Qty, 'system qty refreshed from the latest ledger');
        $this->assertEquals(0.0, (float) $op->Difference);
        $this->assertEquals('CLOSED', $op->Status);
        $this->assertNotNull($op->Closed_At, 'approved/closed timestamp recorded');

        // rebaselined to physical (150), not double-counted to 200
        $this->assertEquals(
            150.0,
            app(\App\Services\WarehouseService::class)->containerSystemQty($c),
            'balance anchored at close: 100 + 50, then reset to 150'
        );
    }

    /** @test */
    public function manual_approval_refreshes_system_qty_at_approval_time()
    {
        $wh = $this->seedWarehouse('TEST-OP-B');
        $c  = $this->seedContainer($wh);

        $this->addStockIn($wh, 'TEST-OP-B', $c, 100);
        $opnameId = $this->createOpname($wh, $c);

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 200])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED_PENDING', $op->Status);
        $this->assertNull($op->Closed_At);

        $this->addStockIn($wh, 'TEST-OP-B', $c, 25); // ledger 125 before approval

        $adjId = DB::table('warehouse_stock_adjustments')->where('Opname_ID', $opnameId)->value('ID');
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId, 'action' => 'approve'])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED', $op->Status);
        $this->assertEquals(125.0, (float) $op->System_Qty, 'refreshed to latest ledger at approval');
        $this->assertEquals(75.0, (float) $op->Difference);
        $this->assertNotNull($op->Closed_At);

        $adj = DB::table('warehouse_stock_adjustments')->where('ID', $adjId)->first();
        $this->assertEquals(125.0, (float) $adj->Qty_Before);
        $this->assertEquals(75.0, (float) $adj->Difference);
    }

    /** @test */
    public function reject_returns_disputed_count_to_counted_without_rebaseline()
    {
        $wh = $this->seedWarehouse('TEST-OP-C');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-C', $c, 100);

        $opnameId = $this->createOpname($wh, $c);
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 50])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $adjId = DB::table('warehouse_stock_adjustments')->where('Opname_ID', $opnameId)->value('ID');
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId, 'action' => 'reject'])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('COUNTED', $op->Status, 'disputed count returns to COUNTED for a re-count');
        $this->assertNull($op->Closed_At);
        $this->assertEquals(100.0, app(\App\Services\WarehouseService::class)->containerSystemQty($c));
    }

    /** @test */
    public function balance_anchors_at_closed_at_and_falls_back_for_legacy_rows()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-D');
        $cLegacy = $this->seedContainer($wh);
        $cClosed = $this->seedContainer($wh);

        foreach ([$cLegacy, $cClosed] as $c) {
            $this->addStockIn($wh, 'TEST-OP-D', $c, 100, '2026-01-01 10:00:00');
            $this->addStockIn($wh, 'TEST-OP-D', $c, 50, '2026-01-01 12:00:00');
        }

        // legacy CLOSED row without Closed_At -> falls back to Created_At (11:00)
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-LEGACY', 'Warehouse_ID' => $wh, 'Container_ID' => $cLegacy,
            'Opname_Date' => '2026-01-01', 'System_Qty' => 100, 'Physical_Qty' => 150, 'Difference' => 50,
            'Status' => 'CLOSED', 'Created_At' => '2026-01-01 11:00:00', 'Updated_At' => '2026-01-01 11:00:00',
        ]);
        $this->assertEquals(200.0, $svc->containerSystemQty($cLegacy), 'legacy: reset at 11:00 then +50');

        // CLOSED row with Closed_At = 13:00 -> reset after all movements
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-CLOSED', 'Warehouse_ID' => $wh, 'Container_ID' => $cClosed,
            'Opname_Date' => '2026-01-01', 'System_Qty' => 100, 'Physical_Qty' => 150, 'Difference' => 50,
            'Status' => 'CLOSED', 'Created_At' => '2026-01-01 11:00:00', 'Updated_At' => '2026-01-01 13:00:00',
            'Closed_At' => '2026-01-01 13:00:00',
        ]);
        $this->assertEquals(150.0, $svc->containerSystemQty($cClosed), 'anchored at Closed_At');
    }

    /** @test */
    public function draft_detail_shows_live_system_qty()
    {
        $wh = $this->seedWarehouse('TEST-OP-E');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-E', $c, 100);

        $opnameId = $this->createOpname($wh, $c); // stored snapshot 100
        $this->addStockIn($wh, 'TEST-OP-E', $c, 50); // live 150

        $html = $this->actingAs($this->admin())
            ->get('/partner-warehouse-stock-opname-detail?ID='.$opnameId)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Current System Qty (live)', $html);
        $this->assertStringContainsString('150', $html);
    }

    /** @test */
    public function instock_batch_recomputes_warehouse_system_qty()
    {
        $wh = $this->seedWarehouse('TEST-OP-F');
        $c  = $this->seedContainer($wh);

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-submit-batch', [
                'received_date' => now()->toDateString(),
                'source'        => 'MITRA',
                'notes'         => '',
                'groups'        => json_encode([[
                    'warehouse'         => $wh,
                    'warehouse_display' => 'TEST-OP-F',
                    'received_qty'      => 40,
                    'avg_price'         => 1000,
                    'total'             => 40000,
                    'containers'        => [['container_id' => $c, 'qty' => 40, 'price' => 1000]],
                ]]),
            ])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(40.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }

    /** @test */
    public function warehouse_open_create_recomputes_warehouse_system_qty()
    {
        $wh = $this->seedWarehouse('TEST-OP-G');
        $c  = $this->seedContainer($wh);

        $this->actingAs($this->admin())
            ->post('/partner-warehouse-open-new-submit', [
                'warehouse_task_id' => 'TEST-OP-G',
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
            ])
            ->assertOk();

        $this->assertEquals(25.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }

    // ------------------------------------------------------------------
    // Post-close corrections (addAdjustment)
    // ------------------------------------------------------------------

    private function approveOpnameAdjustment(int $opnameId): int
    {
        $adjId = DB::table('warehouse_stock_adjustments')
            ->where('Opname_ID', $opnameId)
            ->where('Approval_Status', 'PENDING')
            ->value('ID');

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId, 'action' => 'approve'])
            ->assertOk();

        return $adjId;
    }

    private function closedOpname(int $whId, int $cid, float $physical): int
    {
        $opnameId = $this->createOpname($whId, $cid);
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => $physical])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        if ($op->Status === 'CLOSED_PENDING') {
            $this->approveOpnameAdjustment($opnameId);
        }

        return $opnameId;
    }

    /** @test */
    public function correction_pending_keeps_the_approved_baseline()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-H');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-H', $c, 100);

        $opnameId = $this->closedOpname($wh, $c, 80); // rebaselined to 80
        $this->assertEquals(80.0, $svc->containerSystemQty($c));
        $this->assertEquals(80.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-add-adjustment', [
                'opname_id' => $opnameId, 'physical_qty' => 130, 'reason' => 'correction',
            ])->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED_PENDING', $op->Status);
        $this->assertEquals(80.0, $svc->containerSystemQty($c), 'baseline preserved while correction pending');
        $this->assertEquals(80.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }

    /** @test */
    public function rejected_correction_restores_closed_and_keeps_balance()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-I');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-I', $c, 100);
        $opnameId = $this->closedOpname($wh, $c, 80);

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-add-adjustment', [
                'opname_id' => $opnameId, 'physical_qty' => 130, 'reason' => 'correction',
            ])->assertOk();

        $adjId = DB::table('warehouse_stock_adjustments')
            ->where('Opname_ID', $opnameId)->where('Approval_Status', 'PENDING')->value('ID');
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId, 'action' => 'reject'])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED', $op->Status, 'rejected correction exits the pending state');
        $this->assertEquals(80.0, $svc->containerSystemQty($c));
        $this->assertEquals(80.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }

    /** @test */
    public function approved_correction_rebaselines_to_corrected_physical()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-J');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-J', $c, 100);
        $opnameId = $this->closedOpname($wh, $c, 80);

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-add-adjustment', [
                'opname_id' => $opnameId, 'physical_qty' => 130, 'reason' => 'correction',
            ])->assertOk();

        $this->approveOpnameAdjustment($opnameId);

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED', $op->Status);
        $this->assertEquals(130.0, (float) $op->Physical_Qty);
        $this->assertEquals(80.0, (float) $op->System_Qty, 'refreshed from ledger at approval');
        $this->assertEquals(50.0, (float) $op->Difference);
        $this->assertNotNull($op->Closed_At);

        $this->assertEquals(130.0, $svc->containerSystemQty($c));
        $this->assertEquals(130.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }

    /** @test */
    public function sequential_corrections_apply_cleanly()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-K');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-K', $c, 100);
        $opnameId = $this->closedOpname($wh, $c, 80);

        // first correction -> 130
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-add-adjustment', ['opname_id' => $opnameId, 'physical_qty' => 130])
            ->assertOk();
        $this->approveOpnameAdjustment($opnameId);
        $this->assertEquals(130.0, $svc->containerSystemQty($c));

        // second correction -> 90 (opname is CLOSED at this point)
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-add-adjustment', ['opname_id' => $opnameId, 'physical_qty' => 90])
            ->assertOk();
        $this->approveOpnameAdjustment($opnameId);

        $this->assertEquals(90.0, $svc->containerSystemQty($c));
        $this->assertEquals(90.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }

    /** @test */
    public function pending_opname_without_closed_at_is_not_a_baseline()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-L');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-L', $c, 100);

        $opnameId = $this->createOpname($wh, $c);
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 50])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED_PENDING', $op->Status);
        $this->assertNull($op->Closed_At);
        $this->assertEquals(100.0, $svc->containerSystemQty($c), 'disputed count must not rebaseline');
    }

    /** @test */
    public function rejected_normal_count_returns_to_counted_and_can_be_recountered()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $wh = $this->seedWarehouse('TEST-OP-M');
        $c  = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OP-M', $c, 100);

        $opnameId = $this->createOpname($wh, $c);
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 50])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        // CLOSED_PENDING always carries a PENDING adjustment
        $this->assertNotNull(
            DB::table('warehouse_stock_adjustments')->where('Opname_ID', $opnameId)->where('Approval_Status', 'PENDING')->value('ID')
        );

        $adjId = DB::table('warehouse_stock_adjustments')->where('Opname_ID', $opnameId)->value('ID');
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId, 'action' => 'reject'])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('COUNTED', $op->Status, 'disputed initial count returns to COUNTED for a re-count');
        $this->assertNull($op->Closed_At);
        $this->assertEquals(100.0, $svc->containerSystemQty($c));

        // re-count within tolerance -> AUTO close on the corrected figure
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', ['id' => $opnameId, 'physical_qty' => 99])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $opnameId])
            ->assertOk();

        $op = DB::table('warehouse_stock_opnames')->where('ID', $opnameId)->first();
        $this->assertEquals('CLOSED', $op->Status);
        $this->assertNotNull($op->Closed_At);
        $this->assertEquals(99.0, $svc->containerSystemQty($c));
        $this->assertEquals(99.0, (float) DB::table('warehouse_partner')->where('id', $wh)->value('System_Qty'));
    }
}
