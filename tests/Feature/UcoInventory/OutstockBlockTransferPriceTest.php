<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Outstock hard block (no outstock at system qty <= 0, no overdraw) and the
 * transfer price captured on the outstock document (decoupled report).
 */
class OutstockBlockTransferPriceTest extends TestCase
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
            'Created_By'         => '__test_os__',
        ]);
    }

    private function seedContainer(int $whId): int
    {
        return DB::table('master_container')->insertGetId([
            'Name' => 'OS-C '.uniqid(), 'Volume' => 100000, 'Warehouse_ID' => $whId, 'Status' => 'active',
        ]);
    }

    private function addStockIn(int $whId, string $taskId, int $cid, float $qty): void
    {
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => $taskId, 'received_date' => now()->toDateString(), 'received_qty' => $qty,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => now(), 'created_by' => '__test_os__', 'price' => 7000, 'total' => $qty * 7000,
            'Container_ID' => $cid,
        ]);
    }

    private function makeOutstock(int $whId): int
    {
        $id = DB::table('outstock')->insertGetId([
            'Code' => 'OST-T-'.uniqid(), 'Date' => now()->toDateString(), 'Warehouse_Origin' => $whId,
            'Warehouse_Destination' => 'x', 'Status' => 'DRAFT', 'Created_At' => now(),
        ]);
        DB::table('outstock_warehouses')->insert(['Outstock_ID' => $id, 'Warehouse_ID' => $whId]);
        return $id;
    }

    private function saveLoading(int $doc, int $cid, float $qty)
    {
        return $this->actingAs($this->admin())->postJson('/partner-outstock-loading-submit', [
            'outstock_id' => $doc,
            'rows' => [[
                'container_id' => $cid,
                'date'         => now()->toDateString(),
                'actual_qty'   => $qty,
                'notes'        => '',
            ]],
        ]);
    }

    /** @test */
    public function loading_is_blocked_when_system_qty_is_zero()
    {
        $wh = $this->seedWarehouse('TEST-OS-A');
        $c = $this->seedContainer($wh);
        $doc = $this->makeOutstock($wh);

        $this->saveLoading($doc, $c, 5)
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertEquals(0, DB::table('outstock_details')->where('Outstock_ID', $doc)->count());
    }

    /** @test */
    public function loading_is_blocked_on_overdraw()
    {
        $wh = $this->seedWarehouse('TEST-OS-B');
        $c = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OS-B', $c, 100);
        $doc = $this->makeOutstock($wh);

        $this->saveLoading($doc, $c, 150)->assertStatus(400)->assertJson(['success' => false]);
        $this->assertEquals(0, DB::table('outstock_details')->where('Outstock_ID', $doc)->count());
    }

    /** @test */
    public function loading_saves_within_system_qty()
    {
        $wh = $this->seedWarehouse('TEST-OS-C');
        $c = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OS-C', $c, 100);
        $doc = $this->makeOutstock($wh);

        $this->saveLoading($doc, $c, 80)->assertOk()->assertJson(['success' => true]);

        $row = DB::table('outstock_details')->where('Outstock_ID', $doc)->first();
        $this->assertEquals(80, (float) $row->Actual_Qty);
        $this->assertEquals(100, (float) $row->System_Qty);
    }

    /** @test */
    public function handover_is_blocked_when_distribution_exceeds_system_qty()
    {
        $wh = $this->seedWarehouse('TEST-OS-D');
        $c = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OS-D', $c, 100);
        $doc = $this->makeOutstock($wh);
        $this->saveLoading($doc, $c, 80)->assertOk();

        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-handover-submit', [
                'outstock_id' => $doc, 'Date' => now()->toDateString(),
                'Actual_Qty' => 120, 'Destination' => 'x', 'Notes' => '',
            ])
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertNotEquals('COMPLETED', DB::table('outstock')->where('ID', $doc)->value('Status'));
    }

    /** @test */
    public function handover_saves_transfer_price_and_report_reflects_it()
    {
        $wh = $this->seedWarehouse('TEST-OS-E');
        $c = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OS-E', $c, 100);
        $doc = $this->makeOutstock($wh);
        $this->saveLoading($doc, $c, 80)->assertOk();

        $this->actingAs($this->admin())
            ->post('/partner-outstock-handover-submit', [
                'outstock_id' => $doc, 'Date' => now()->toDateString(),
                'Actual_Qty' => 80, 'Destination' => 'x', 'Notes' => '',
                'Transfer_Price' => 11000,
                'Transfer_Document' => UploadedFile::fake()->create('transfer.pdf', 10, 'application/pdf'),
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $os = DB::table('outstock')->where('ID', $doc)->first();
        $this->assertEquals(11000, (float) $os->Transfer_Price);
        $this->assertNotNull($os->Transfer_Document);

        $stmt = app(\App\Services\WarehouseService::class)->calculateFinancialStatement(
            'TEST-OS-E', now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()
        );
        $this->assertEquals(11000.0, (float) $stmt['transfer_price']);
        $this->assertEquals(80 * 11000.0, (float) $stmt['transfer_income']);
    }

    /** @test */
    public function report_transfer_is_zero_without_a_transfer_price()
    {
        $wh = $this->seedWarehouse('TEST-OS-F');
        $c = $this->seedContainer($wh);
        $this->addStockIn($wh, 'TEST-OS-F', $c, 100);
        $doc = $this->makeOutstock($wh);
        $this->saveLoading($doc, $c, 50)->assertOk();

        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-handover-submit', [
                'outstock_id' => $doc, 'Date' => now()->toDateString(),
                'Actual_Qty' => 50, 'Destination' => 'x', 'Notes' => '',
            ])->assertOk();

        $stmt = app(\App\Services\WarehouseService::class)->calculateFinancialStatement(
            'TEST-OS-F', now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()
        );
        $this->assertEquals(0.0, (float) $stmt['transfer_income']);
        $this->assertEquals(0.0, (float) $stmt['transfer_price']);
    }
}
