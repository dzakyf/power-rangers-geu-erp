<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DebugLedger2Test extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function dump()
    {
        $whId = DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id' => 'DBG2-WH', 'warehouse_type' => 'mobil', 'Location' => 'T',
            'warehouse_location' => 'D', 'warehouse_status' => 'ACTIVE', 'partner_task_id' => 'D2',
            'Created_Date' => now(), 'Created_By' => '__dbg__',
        ]);
        $cid = DB::table('master_container')->insertGetId([
            'Name' => 'D2-C', 'Volume' => 1000, 'Warehouse_ID' => $whId, 'Status' => 'active',
        ]);
        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => 'DBG2-WH', 'received_date' => now()->toDateString(), 'received_qty' => 100,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => now(), 'created_by' => '__dbg__', 'total' => 0, 'Container_ID' => $cid,
        ]);
        $doc = DB::table('outstock')->insertGetId([
            'Code' => 'DBG2-OST', 'Date' => now()->toDateString(), 'Warehouse_Origin' => $whId,
            'Warehouse_Destination' => 'x', 'Status' => 'DRAFT', 'Created_At' => now(),
        ]);
        DB::table('outstock_loading')->insert([
            'Outstock_ID' => $doc, 'Container_ID' => $cid, 'Date' => now()->toDateString(),
            'System_Qty' => 0, 'Actual_Qty' => 30, 'Notes' => '', 'Created_At' => now(),
        ]);
        DB::table('warehouse_partner_stock_request')->insert([
            'warehouse_task_id' => 'DBG2-WH', 'request_qty' => 30, 'request_date' => now()->toDateString(),
            'price' => 0, 'total' => 0, 'reference' => 'OUTSTOCK: DBG2-OST - X',
            'remark' => 'Auto-generated from outstock completion', 'created_date' => now(), 'created_by' => '__dbg__',
        ]);
        DB::table('warehouse_partner_stock_request')->insert([
            'warehouse_task_id' => 'DBG2-WH', 'request_qty' => 5, 'request_date' => now()->toDateString(),
            'price' => 0, 'total' => 0, 'reference' => 'MANUAL-REQ', 'remark' => '',
            'created_date' => now(), 'created_by' => '__dbg__',
        ]);

        $admin = \App\User::where('username', 'admin')->firstOrFail();
        $body = $this->actingAs($admin)->get('/xml-partner-warehouse-list-detail?Task=' . $whId)->getContent();
        fwrite(STDERR, "\n=== HTTP LEDGER ===\n");
        foreach (new \SimpleXMLElement($body) as $row) {
            $cells = [];
            foreach ($row->cell as $c) $cells[] = (string) $c;
            fwrite(STDERR, implode(' | ', $cells) . "\n");
        }

        $direct = DB::table('outstock_loading as ol')
            ->join('master_container as mc', 'ol.Container_ID', '=', 'mc.ID')
            ->join('outstock as o', 'ol.Outstock_ID', '=', 'o.ID')
            ->where('mc.Warehouse_ID', $whId)
            ->select('ol.Actual_Qty', 'ol.Date', 'o.Code', 'mc.Name as Container_Name')
            ->get();
        fwrite(STDERR, "direct loading rows: " . $direct->toJson() . "\n");

        $reqs = DB::table('warehouse_partner_stock_request')
            ->where('warehouse_task_id', 'DBG2-WH')
            ->where(function ($q) {
                $q->whereNull('reference')->orWhere('reference', 'not like', 'OUTSTOCK:%');
            })
            ->get(['request_qty', 'reference']);
        fwrite(STDERR, "filtered requests: " . $reqs->toJson() . "\n");
        $this->assertTrue(true);
    }
}
