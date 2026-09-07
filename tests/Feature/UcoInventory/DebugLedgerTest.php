<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Tests\TestCase;

class DebugLedgerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function dump_rows()
    {
        $whId = DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id' => 'DBG-WH', 'warehouse_type' => 'mobil', 'Location' => 'Test',
            'warehouse_location' => 'Debug', 'warehouse_status' => 'ACTIVE', 'partner_task_id' => 'DBG-PT',
            'Created_Date' => now(), 'Created_By' => '__dbg__',
        ]);
        $cid = DB::table('master_container')->insertGetId([
            'Name' => 'DBG-C', 'Volume' => 1000, 'Warehouse_ID' => $whId, 'Status' => 'active',
        ]);
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'DBG-WH', 'received_date' => now()->toDateString(), 'received_qty' => 100,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => now(), 'created_by' => '__dbg__', 'total' => 0, 'Container_ID' => $cid,
        ]);
        $oid = DB::table('outstock')->insertGetId([
            'Code' => 'DBG-OST', 'Date' => now()->toDateString(), 'Warehouse_Origin' => $whId,
            'Warehouse_Destination' => 'x', 'Status' => 'DRAFT', 'Created_At' => now(),
        ]);
        DB::table('outstock_loading')->insert([
            'Outstock_ID' => $oid, 'Container_ID' => $cid, 'Date' => now()->toDateString(),
            'System_Qty' => 0, 'Actual_Qty' => 30, 'Notes' => '', 'Created_At' => now(),
        ]);

        $admin = \App\User::where('username', 'admin')->firstOrFail();
        $res = $this->actingAs($admin)
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $whId, 'container_id' => $cid, 'opname_date' => now()->toDateString(),
            ])->assertOk()->json();
        $this->actingAs($admin)->postJson('/partner-warehouse-stock-opname-update', [
            'id' => $res['id'], 'physical_qty' => 71,
        ])->assertOk();
        $this->actingAs($admin)->postJson('/partner-warehouse-stock-opname-finalize', ['id' => $res['id']])->assertOk();

        $body = $this->actingAs($admin)->get('/xml-partner-warehouse-list-detail?Task=' . $whId)->getContent();
        $xml = new SimpleXMLElement($body);
        fwrite(STDERR, "\n=== LEDGER ROWS ===\n");
        foreach ($xml->row as $row) {
            $cells = [];
            foreach ($row->cell as $c) $cells[] = (string) $c;
            fwrite(STDERR, implode(' | ', $cells) . "\n");
        }
        $adj = DB::table('warehouse_stock_adjustments')->get();
        fwrite(STDERR, "adjustments: " . $adj->toJson() . "\n");
        $this->assertTrue(true);
    }
}
