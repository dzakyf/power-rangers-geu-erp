<?php

namespace Tests\Feature\UcoInventory;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Tests\TestCase;

/**
 * Integration tests: "System Quantity" in the UCO inventory section is an
 * event-sourced container balance: stock-in (+), outstock loading (-),
 * rebaselined to the physical count at each CLOSED stock opname. Opname
 * adjustments are approval artifacts, never additive terms; pending or
 * rejected counts never rebaseline.
 *
 * Single implementation: WarehouseService::containerSystemQty().
 *
 * Covers: opname creation (server-side calc), outstock loading persistence
 * (snapshot before delete), outstock edit display (pre-document view),
 * the systemqty autofill endpoint, and the warehouse operation log.
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
        DB::table('outstock_details')->insert([
            'Outstock_ID'  => $outstockId,
            'Container_ID' => $this->containerId,
            'Warehouse_ID' => $this->warehouseId,
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
    public function ledger_math_allows_negative_before_opname()
    {
        $svc = app(\App\Services\WarehouseService::class);

        $this->addStockIn(100);
        $this->assertEquals(100.0, $svc->containerSystemQty($this->containerId));

        $doc = $this->makeOutstock();
        $this->addOutRow($doc, 30);
        $this->assertEquals(70.0, $svc->containerSystemQty($this->containerId));

        // Overdraw goes negative - an honest signal of unattributed stock
        // that a physical opname later rebaselines.
        $this->addOutRow($this->makeOutstock(), 500);
        $this->assertEquals(-430.0, $svc->containerSystemQty($this->containerId));
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
        $row = DB::table('outstock_details')->where('Outstock_ID', $doc)->first();
        $this->assertEquals(60, (float) $row->System_Qty);

        // Resubmitting the same document must keep the snapshot stable:
        // deleting its own 25 L first would otherwise inflate the baseline to 85.
        $payload['rows'][0]['actual_qty'] = 50;
        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-loading-submit', $payload)
            ->assertOk()->assertJson(['success' => true]);

        $row = DB::table('outstock_details')->where('Outstock_ID', $doc)->first();
        $this->assertEquals(60, (float) $row->System_Qty);
    }

    private function countOpname(float $physical): int
    {
        $res = $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $this->warehouseId,
                'container_id' => $this->containerId,
                'opname_date'  => now()->toDateString(),
            ])->assertOk()->json();

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-update', [
                'id'           => $res['id'],
                'physical_qty' => $physical,
            ])->assertOk();

        return $res['id'];
    }

    private function finalizeOpname(int $id): void
    {
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $id])
            ->assertOk();
    }

    private function xmlRows(string $body): array
    {
        $xml = new SimpleXMLElement($body);
        $rows = [];
        foreach ($xml->row as $row) {
            $cells = [];
            foreach ($row->cell as $cell) {
                $cells[] = (string) $cell;
            }
            $rows[(int) $row['ID']] = $cells;
        }
        return $rows;
    }

    // ------------------------------------------------------------------
    // Event replay: CLOSED opnames rebaseline the running balance
    // ------------------------------------------------------------------

    /** @test */
    public function event_replay_reaches_physical_count_after_interleaved_events()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $c = $this->containerId;

        // loading events with no containerized supply yet (legacy gap)
        DB::table('outstock_details')->insert([
            ['Outstock_ID' => $this->makeOutstock(), 'Container_ID' => $c, 'Warehouse_ID' => $this->warehouseId, 'Date' => '2026-07-28',
             'System_Qty' => 0, 'Actual_Qty' => 15, 'Notes' => '', 'Created_At' => '2026-07-28 17:16:36'],
            ['Outstock_ID' => $this->makeOutstock(), 'Container_ID' => $c, 'Warehouse_ID' => $this->warehouseId, 'Date' => '2026-09-07',
             'System_Qty' => 0, 'Actual_Qty' => 10, 'Notes' => '', 'Created_At' => '2026-09-07 06:05:00'],
        ]);

        // opname created when the system believes 0; closed with physical 120
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-REPLAY', 'Warehouse_ID' => $this->warehouseId, 'Container_ID' => $c,
            'Opname_Date' => '2026-09-07', 'System_Qty' => 0, 'Physical_Qty' => 120, 'Difference' => 120,
            'Status' => 'CLOSED', 'Created_At' => '2026-09-07 06:06:00', 'Updated_At' => '2026-09-07 09:30:00',
        ]);

        // movements interleaved around the count
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'received_date' => '2026-09-07', 'received_qty' => 90,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => '2026-09-07 06:12:00', 'created_by' => '__test_sq__', 'total' => 0, 'Container_ID' => $c,
        ]);
        DB::table('outstock_details')->insert([
            'Outstock_ID' => $this->makeOutstock(), 'Container_ID' => $c, 'Warehouse_ID' => $this->warehouseId, 'Date' => '2026-09-07',
            'System_Qty' => 0, 'Actual_Qty' => 90, 'Notes' => '', 'Created_At' => '2026-09-07 06:15:00',
        ]);

        // replay: -15, -10, rebaseline 120, +90, -90 = 120 (physical reality)
        $this->assertEquals(120.0, $svc->containerSystemQty($c));
    }

    /** @test */
    public function chained_opnames_replay_exactly()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $c = $this->containerId;

        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'received_date' => '2026-09-01', 'received_qty' => 100,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => '2026-09-01 10:00:00', 'created_by' => '__test_sq__', 'total' => 0, 'Container_ID' => $c,
        ]);
        DB::table('outstock_details')->insert([
            'Outstock_ID' => $this->makeOutstock(), 'Container_ID' => $c, 'Warehouse_ID' => $this->warehouseId, 'Date' => '2026-09-02',
            'System_Qty' => 0, 'Actual_Qty' => 75, 'Notes' => '', 'Created_At' => '2026-09-02 11:45:00',
        ]);
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-C1', 'Warehouse_ID' => $this->warehouseId, 'Container_ID' => $c,
            'Opname_Date' => '2026-09-02', 'System_Qty' => 25, 'Physical_Qty' => 20, 'Difference' => -5,
            'Status' => 'CLOSED', 'Created_At' => '2026-09-02 11:50:00', 'Updated_At' => '2026-09-02 12:00:00',
        ]);
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'received_date' => '2026-09-03', 'received_qty' => 855,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => '2026-09-03 11:59:00', 'created_by' => '__test_sq__', 'total' => 0, 'Container_ID' => $c,
        ]);
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-C2', 'Warehouse_ID' => $this->warehouseId, 'Container_ID' => $c,
            'Opname_Date' => '2026-09-03', 'System_Qty' => 880, 'Physical_Qty' => 880, 'Difference' => 0,
            'Status' => 'CLOSED', 'Created_At' => '2026-09-03 12:10:00', 'Updated_At' => '2026-09-03 12:20:00',
        ]);
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'received_date' => '2026-09-03', 'received_qty' => 4000,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => '2026-09-03 13:16:00', 'created_by' => '__test_sq__', 'total' => 0, 'Container_ID' => $c,
        ]);

        // replay: +100, -75, rebaseline 20, +855, rebaseline 880, +4000 = 4880
        $this->assertEquals(4880.0, $svc->containerSystemQty($c));
    }

    /** @test */
    public function pending_or_rejected_opname_does_not_rebaseline()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $this->addStockIn(100); // system = 100

        // disputed count -> CLOSED_PENDING -> no rebaseline
        $rejected = $this->countOpname(150); // +50 = 50% -> PENDING
        $this->finalizeOpname($rejected);
        $this->assertEquals(100.0, $svc->containerSystemQty($this->containerId), 'CLOSED_PENDING must not rebaseline');

        $adjId = DB::table('warehouse_stock_adjustments')->where('Opname_ID', $rejected)->value('ID');
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId, 'action' => 'reject'])
            ->assertOk();
        $this->assertEquals(100.0, $svc->containerSystemQty($this->containerId), 'REJECTED must not rebaseline');

        // approval -> CLOSED -> rebaseline to physical
        $approvable = $this->countOpname(130); // +30 -> PENDING
        $this->finalizeOpname($approvable);
        $adjId2 = DB::table('warehouse_stock_adjustments')->where('Opname_ID', $approvable)->value('ID');
        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-action', ['id' => $adjId2, 'action' => 'approve'])
            ->assertOk();
        $this->assertEquals(130.0, $svc->containerSystemQty($this->containerId), 'CLOSED rebaselines to physical');

        // a fresh DRAFT never rebaselines
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-DRAFT', 'Warehouse_ID' => $this->warehouseId, 'Container_ID' => $this->containerId,
            'Opname_Date' => now()->toDateString(), 'System_Qty' => 130, 'Physical_Qty' => 999, 'Difference' => 869,
            'Status' => 'DRAFT', 'Created_At' => now(), 'Updated_At' => now(),
        ]);
        $this->assertEquals(130.0, $svc->containerSystemQty($this->containerId), 'DRAFT must not rebaseline');
    }

    /** @test */
    public function operation_log_shows_loading_outstock_without_mirrors_and_rebases_on_opname()
    {
        $this->addStockIn(100); // containerized supply

        $doc = $this->makeOutstock();
        $this->addOutRow($doc, 30);

        // mirror row of the completed outstock — 99 L so a leak is detectable
        // (references are not part of the ledger grid, quantities must discriminate)
        DB::table('warehouse_partner_stock_request')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'request_qty' => 99,
            'request_date' => now()->toDateString(), 'price' => 0, 'total' => 0,
            'reference' => 'OUTSTOCK: OST-SQ-MIRROR - Somewhere',
            'remark' => 'Auto-generated from outstock completion',
            'created_date' => now(), 'created_by' => '__test_sq__',
        ]);

        // manual request
        DB::table('warehouse_partner_stock_request')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'request_qty' => 5,
            'request_date' => now()->toDateString(), 'price' => 0, 'total' => 0,
            'reference' => 'MANUAL-REQ', 'remark' => '',
            'created_date' => now(), 'created_by' => '__test_sq__',
        ]);

        // closed opname rebases the log saldo to physical
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-LOG', 'Warehouse_ID' => $this->warehouseId, 'Container_ID' => $this->containerId,
            'Opname_Date' => now()->toDateString(), 'System_Qty' => 65, 'Physical_Qty' => 71, 'Difference' => 6,
            'Status' => 'CLOSED', 'Created_At' => now()->toDateString().' 10:00:00', 'Updated_At' => now(),
        ]);

        $rows = $this->xmlRows(
            $this->actingAs($this->admin())
                ->get('/xml-partner-warehouse-list-detail?Task='.$this->warehouseId)
                ->assertOk()
                ->getContent()
        );

        $outstock = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'Outstock'));
        $this->assertCount(2, $outstock, 'loading + manual request shown; mirror excluded');
        $vals = array_map('floatval', array_map(fn ($c) => $c[4], $outstock));
        sort($vals);
        $this->assertEquals([5.0, 30.0], $vals, 'mirror (99) must not appear; loading (30) must');


        $opname = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'OPNAME'));
        $this->assertNotEmpty($opname);
        $this->assertEquals('71', $opname[0][6], 'closed opname rebases the log saldo to physical');
    }

    /** @test */
    public function ledger_ignores_loading_of_other_warehouses_containers()
    {
        $this->addStockIn(100);

        $otherContainer = DB::table('master_container')->insertGetId([
            'Name' => 'SQ-Other '.uniqid(), 'Volume' => 1000,
            'Warehouse_ID' => $this->warehouseId + 1000, 'Status' => 'active',
        ]);
        DB::table('outstock_details')->insert([
            'Outstock_ID' => $this->makeOutstock(), 'Container_ID' => $otherContainer,
            'Warehouse_ID' => $this->warehouseId + 1000,
            'Date' => now()->toDateString(), 'System_Qty' => 0, 'Actual_Qty' => 50,
            'Notes' => '', 'Created_At' => now(),
        ]);

        $rows = $this->xmlRows(
            $this->actingAs($this->admin())
                ->get('/xml-partner-warehouse-list-detail?Task='.$this->warehouseId)
                ->assertOk()
                ->getContent()
        );

        $outstock = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'Outstock'));
        $this->assertCount(0, $outstock, 'another warehouse loading must not leak in');
        $instock = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'INSTOCK'));
        $this->assertEquals('100', $instock[0][6]);
    }

    /** @test */
    public function outstock_edit_containers_carry_system_qty()
    {
        $this->addStockIn(55.5);

        $doc = $this->makeOutstock();
        DB::table('outstock_warehouses')->insert([
            'Outstock_ID' => $doc,
            'Warehouse_ID' => $this->warehouseId,
        ]);

        $html = $this->actingAs($this->admin())
            ->get('/partner-outstock-detail?id='.$doc)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"system_qty":55.5', $html);
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

    // ------------------------------------------------------------------
    // As-of balance
    // ------------------------------------------------------------------

    /** @test */
    public function as_of_excludes_later_events()
    {
        $svc = app(\App\Services\WarehouseService::class);
        $c = $this->containerId;

        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'received_date' => '2026-09-07', 'received_qty' => 90,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => '2026-09-07 10:00:00', 'created_by' => '__test_sq__', 'total' => 0, 'Container_ID' => $c,
        ]);
        DB::table('outstock_details')->insert([
            'Outstock_ID' => $this->makeOutstock(), 'Container_ID' => $c, 'Warehouse_ID' => $this->warehouseId,
            'Date' => '2026-09-07', 'System_Qty' => 0, 'Actual_Qty' => 30, 'Notes' => '', 'Created_At' => '2026-09-07 11:00:00',
        ]);
        DB::table('warehouse_stock_opnames')->insert([
            'Opname_Number' => 'WSO-T-ASOF', 'Warehouse_ID' => $this->warehouseId, 'Container_ID' => $c,
            'Opname_Date' => '2026-09-07', 'System_Qty' => 60, 'Physical_Qty' => 120, 'Difference' => 60,
            'Status' => 'CLOSED', 'Created_At' => '2026-09-07 12:00:00', 'Updated_At' => '2026-09-07 12:30:00',
        ]);

        $this->assertEquals(60.0, $svc->containerSystemQtyAsOf($c, '2026-09-07 11:30:00'), 'before opname');
        $this->assertEquals(120.0, $svc->containerSystemQtyAsOf($c, '2026-09-07 13:00:00'), 'after opname');
        $this->assertEquals(120.0, $svc->containerSystemQty($c));
    }

    // ------------------------------------------------------------------
    // Handover distribution (one total -> container lines)
    // ------------------------------------------------------------------

    /** @test */
    public function handover_distributes_total_across_container_lines()
    {
        $c2 = DB::table('master_container')->insertGetId([
            'Name' => 'SQ-C2 '.uniqid(), 'Volume' => 1000,
            'Warehouse_ID' => $this->warehouseId, 'Status' => 'active',
        ]);
        $doc = $this->makeOutstock();
        DB::table('outstock_warehouses')->insert([
            'Outstock_ID' => $doc, 'Warehouse_ID' => $this->warehouseId,
        ]);
        DB::table('outstock_details')->insert([
            ['Outstock_ID' => $doc, 'Container_ID' => $this->containerId, 'Warehouse_ID' => $this->warehouseId,
             'Date' => now()->toDateString(), 'System_Qty' => 0, 'Actual_Qty' => 30, 'Notes' => '', 'Created_At' => now()],
            ['Outstock_ID' => $doc, 'Container_ID' => $c2, 'Warehouse_ID' => $this->warehouseId,
             'Date' => now()->toDateString(), 'System_Qty' => 0, 'Actual_Qty' => 70, 'Notes' => '', 'Created_At' => now()],
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-handover-submit', [
                'outstock_id' => $doc,
                'Date'        => now()->toDateString(),
                'Actual_Qty'  => 80,
                'Destination' => 'Test Dest',
                'Notes'       => '',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $lines = DB::table('outstock_details')->where('Outstock_ID', $doc)->orderBy('ID')->get();
        $this->assertEquals(80.0, (float) $lines->sum('Actual_Qty'), 'distributed lines sum to the handover total');
        $this->assertEquals(24.0, (float) $lines[0]->Actual_Qty, '30/100 * 80');
        $this->assertEquals(56.0, (float) $lines[1]->Actual_Qty, 'residual keeps the sum exact');
        $this->assertEquals(80.0, (float) DB::table('outstock')->where('ID', $doc)->value('Actual_Qty'));

        $mirror = DB::table('warehouse_partner_stock_request')
            ->where('source', 'OUTSTOCK')->where('source_id', $doc)->first();
        $this->assertNotNull($mirror, 'finance mirror generated from distributed lines');
        $this->assertEquals(80.0, (float) $mirror->request_qty);
    }

    // ------------------------------------------------------------------
    // Warehouse system qty cache
    // ------------------------------------------------------------------

    /** @test */
    public function loading_submit_recomputes_warehouse_system_qty_cache()
    {
        $this->addStockIn(100); // container balance = 100
        $doc = $this->makeOutstock();

        $this->actingAs($this->admin())
            ->postJson('/partner-outstock-loading-submit', [
                'outstock_id' => $doc,
                'rows' => [[
                    'container_id' => $this->containerId,
                    'date'         => now()->toDateString(),
                    'actual_qty'   => 30,
                    'notes'        => '',
                ]],
            ])
            ->assertOk()->assertJson(['success' => true]);

        $wh = DB::table('warehouse_partner')->where('id', $this->warehouseId)->first();
        $this->assertEquals(70.0, (float) $wh->System_Qty);
        $this->assertNotNull($wh->System_Qty_Updated_At);
    }

    // ------------------------------------------------------------------
    // Warehouse detail summary
    // ------------------------------------------------------------------

    /** @test */
    public function warehouse_detail_shows_total_breakdown_and_unattributed()
    {
        $this->addStockIn(100); // containerized
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'TEST-WH-SQ', 'received_date' => now()->toDateString(), 'received_qty' => 15,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => now(), 'created_by' => '__test_sq__', 'total' => 0, 'Container_ID' => null,
        ]);

        $html = $this->actingAs($this->admin())
            ->get('/partner-warehouse-list-detail?Task='.$this->warehouseId)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Total System Qty (containers)', $html);
        $this->assertStringContainsString('100,00', $html, 'container total');
        $this->assertStringContainsString('15,00', $html, 'unattributed stock');
    }
}
