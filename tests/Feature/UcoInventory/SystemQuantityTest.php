<?php

namespace Tests\Feature\UcoInventory;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests: "System Quantity" in the UCO inventory section must
 * always be computed from the stock ledger (warehouse_partner_supplies IN
 * minus outstock_loading OUT) - never flat zero, never trusted from client
 * input, never distorted by opnames.
 *
 * Formula authority: pure ledger (user decision), single implementation in
 * WarehouseService::containerSystemQty().
 *
 * Covers: opname creation (server-side calc), outstock loading persistence
 * (snapshot before delete), outstock edit display (pre-document view),
 * and the systemqty autofill endpoint.
 */
class SystemQuantityTest extends TestCase
{
    use DatabaseTransactions;

    private $containerId;
    private $warehouseId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseId = DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => 'TEST-WH-SQ',
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Test Location',
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'TEST-PT-SQ',
            'Created_Date'       => now(),
            'Created_By'         => '__test_sq__',
        ]);

        $this->containerId = DB::table('master_container')->insertGetId([
            'Name'         => 'SQ-Container '.uniqid(),
            'Volume'       => 1000,
            'Warehouse_ID' => $this->warehouseId,
            'Status'       => 'active',
        ]);
    }

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function addStockIn(float $qty): void
    {
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ',
            'received_date'     => now()->toDateString(),
            'received_qty'      => $qty,
            'pickup_type'       => 'TEST',
            'name_poo'          => 'Test POO',
            'source'            => 'TEST',
            'coverage'          => 'TEST',
            'created_date'      => now(),
            'created_by'        => '__test_sq__',
            'total'             => 0,
            'Container_ID'      => $this->containerId,
        ]);
    }

    private function addOutRow(int $outstockId, float $actualQty): void
    {
        DB::table('outstock_loading')->insert([
            'Outstock_ID'  => $outstockId,
            'Container_ID' => $this->containerId,
            'Date'         => now()->toDateString(),
            'System_Qty'   => 0,
            'Actual_Qty'   => $actualQty,
            'Notes'        => '',
            'Created_At'   => now(),
        ]);
    }

    private function makeOutstock(): int
    {
        return DB::table('outstock')->insertGetId([
            'Code'                  => 'OST-SQ-'.uniqid(),
            'Date'                  => now()->toDateString(),
            'Warehouse_Origin'      => $this->warehouseId,
            'Warehouse_Destination' => 'Test Dest',
            'Status'                => 'DRAFT',
            'Created_At'            => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // Ledger math
    // ------------------------------------------------------------------

    /** @test */
    public function ledger_math_basic_and_floor()
    {
        $svc = app(\App\Services\WarehouseService::class);

        $this->addStockIn(100);
        $this->assertEquals(100.0, $svc->containerSystemQty($this->containerId));

        $doc = $this->makeOutstock();
        $this->addOutRow($doc, 30);
        $this->assertEquals(70.0, $svc->containerSystemQty($this->containerId));

        // Overdraw floors at zero instead of going negative
        $this->addOutRow($this->makeOutstock(), 500);
        $this->assertEquals(0.0, $svc->containerSystemQty($this->containerId));
    }

    // ------------------------------------------------------------------
    // Opname creation
    // ------------------------------------------------------------------

    /** @test */
    public function opname_submit_ignores_client_value_and_stores_ledger_qty()
    {
        $this->addStockIn(120);
        $other = $this->makeOutstock();
        $this->addOutRow($other, 20); // ledger = 100

        $res = $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $this->warehouseId,
                'container_id' => $this->containerId,
                'opname_date'  => now()->toDateString(),
                'system_qty'   => 999, // tampered client value must be ignored
                'notes'        => 'sq test',
            ])->assertOk()->assertJson(['success' => true])->json();

        $opname = DB::table('warehouse_stock_opnames')->where('ID', $res['id'])->first();
        $this->assertEquals(100, (float) $opname->System_Qty);
    }

    /** @test */
    public function systemqty_endpoint_returns_computed_value()
    {
        $this->addStockIn(55);

        $this->actingAs($this->admin())
            ->get('/partner-warehouse-stock-opname-systemqty?container_id=' . $this->containerId)
            ->assertOk()
            ->assertJson(['success' => true, 'system_qty' => 55.0]);
    }

    // ------------------------------------------------------------------
    // Outstock loading persistence
    // ------------------------------------------------------------------

    /** @test */
    public function loading_submit_persists_pre_delete_snapshot()
    {
        // Baseline ledger: 100 in - 40 other-doc out = 60
        $this->addStockIn(100);
        $other = $this->makeOutstock();
        $this->addOutRow($other, 40);

        $doc = $this->makeOutstock();

        $payload = [
            'outstock_id' => $doc,
            'rows' => [[
                'container_id' => $this->containerId,
                'date'         => now()->toDateString(),
                'actual_qty'   => 25,
                'notes'        => '',
            ]],
        ];

        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-loading-submit', $payload)
            ->assertOk()->assertJson(['success' => true]);

        // Snapshot taken BEFORE this doc's own rows entered the ledger.
        $row = DB::table('outstock_loading')->where('Outstock_ID', $doc)->first();
        $this->assertEquals(60, (float) $row->System_Qty);

        // Resubmitting the same document must keep the snapshot stable:
        // deleting its own 25 L first would otherwise inflate the baseline to 85.
        $payload['rows'][0]['actual_qty'] = 50;
        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-loading-submit', $payload)
            ->assertOk()->assertJson(['success' => true]);

        $row = DB::table('outstock_loading')->where('Outstock_ID', $doc)->first();
        $this->assertEquals(60, (float) $row->System_Qty);
    }

    /** @test */
    public function edit_view_shows_pre_document_qty_even_without_opnames()
    {
        $this->addStockIn(200); // no opname records exist at all

        $doc = $this->makeOutstock();
        $this->addOutRow($doc, 70); // this doc's own rows

        $html = $this->actingAs($this->admin())
            ->get('/partner-outstock-detail?id='.$doc)
            ->assertOk()
            ->getContent();

        // Pre-document system qty = 200; the readonly field must NOT show the
        // post-subtraction value (130) nor a flat 0.
        $this->assertStringContainsString('value="200"', html_entity_decode($html));
    }
}
