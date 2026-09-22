<?php

namespace Tests\Feature\BusinessPartner;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Dashboards must aggregate across ALL registered warehouses when no warehouse
 * is selected (scoped to its own warehouses for a MITRA user).
 */
class DashboardAllWarehouseTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouseWithData(string $taskId, string $partnerTaskId, float $inQty, float $outQty): int
    {
        $whId = DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => 'booth',
            'Location'           => 'Test',
            'warehouse_location' => 'Loc '.$taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => $partnerTaskId,
            'Created_Date'       => now(),
            'Created_By'         => '__test_dash__',
        ]);

        $cid = DB::table('master_container')->insertGetId([
            'Name' => 'DASH-C '.uniqid(), 'Volume' => 100000, 'Warehouse_ID' => $whId, 'Status' => 'active',
        ]);

        DB::table('warehouse_partner_supplies')->insert([
            'warehouse_task_id' => $taskId, 'received_date' => now()->toDateString(), 'received_qty' => $inQty,
            'pickup_type' => 'T', 'name_poo' => 'p', 'source' => 's', 'coverage' => 'c',
            'created_date' => now(), 'created_by' => '__test_dash__', 'price' => 7000, 'total' => $inQty * 7000,
            'Container_ID' => $cid,
        ]);

        if ($outQty > 0) {
            $doc = DB::table('outstock')->insertGetId([
                'Code' => 'OST-DASH-'.uniqid(), 'Date' => now()->toDateString(), 'Warehouse_Origin' => $whId,
                'Status' => 'COMPLETED', 'Transfer_Price' => 11000, 'Created_At' => now(),
            ]);
            DB::table('outstock_details')->insert([
                'Outstock_ID' => $doc, 'Container_ID' => $cid, 'Warehouse_ID' => $whId,
                'Date' => now()->toDateString(), 'System_Qty' => $outQty, 'Actual_Qty' => $outQty,
                'Created_At' => now(),
            ]);
        }

        return $whId;
    }

    private function monthRange(): array
    {
        return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
    }

    /** @test */
    public function financial_insight_without_task_aggregates_all_warehouses()
    {
        $this->seedWarehouseWithData('TEST-DASH-A', 'PT-A', 100, 60);
        $this->seedWarehouseWithData('TEST-DASH-B', 'PT-B', 40, 30);

        [$start, $end] = $this->monthRange();

        $all = $this->actingAs($this->admin())
            ->get("/partner-dashboard-financial-insight-data?date_start={$start}&date_end={$end}")
            ->assertOk()->json()['data'];

        $sumQty = 0;
        foreach (DB::table('warehouse_partner')->pluck('warehouse_task_id') as $taskId) {
            $one = $this->actingAs($this->admin())
                ->get("/partner-dashboard-financial-insight-data?Task={$taskId}&date_start={$start}&date_end={$end}")
                ->assertOk()->json()['data'];
            $sumQty += (float) $one['total_qty'];
        }

        $this->assertEqualsWithDelta($sumQty, (float) $all['total_qty'], 0.01, 'all-warehouse qty equals the sum of each warehouse');
    }

    /** @test */
    public function aftersales_without_warehouse_lists_each_warehouse_and_sums()
    {
        $this->seedWarehouseWithData('TEST-DASH-C', 'PT-C', 100, 60);
        $emptyWh = $this->seedWarehouseWithData('TEST-DASH-EMPTY', 'PT-E', 0, 0);

        [$start, $end] = $this->monthRange();

        $data = $this->actingAs($this->admin())
            ->get("/partner-dashboard-aftersales-data?date_start={$start}&date_end={$end}")
            ->assertOk()->json()['data'];

        $byWh = collect($data['net_profit'])->keyBy('warehouse');
        $this->assertTrue($byWh->has('TEST-DASH-C'));
        $this->assertTrue($byWh->has('TEST-DASH-EMPTY'), 'empty warehouse included as a 0 row');
        $this->assertEquals(0.0, (float) $byWh['TEST-DASH-EMPTY']['amount']);

        $sum = collect($data['net_profit'])->sum('amount');
        $this->assertEqualsWithDelta((float) $data['total_net_profit'], (float) $sum, 0.01);
    }

    /** @test */
    public function mitra_aggregate_is_scoped_to_its_own_warehouses()
    {
        $partnerTask = 'PARTNER-MITRA-'.uniqid();
        $this->seedWarehouseWithData('TEST-DASH-MITRA', $partnerTask, 100, 60);
        $this->seedWarehouseWithData('TEST-DASH-OTHER', 'PARTNER-OTHER-'.uniqid(), 999, 500);

        $user = DB::table('users')->insertGetId([
            'username' => 'mitra_dash_'.uniqid(), 'Name' => 'Mitra Dash', 'Position' => 'MITRA',
            'Priv_ID' => 'MITRA', 'Emp_ID' => $partnerTask, 'Status' => 'ACTIVE', 'Location' => 'HQO',
        ]);

        [$start, $end] = $this->monthRange();

        $scoped = $this->actingAs(\App\User::find($user))
            ->get("/partner-dashboard-financial-insight-data?date_start={$start}&date_end={$end}")
            ->assertOk()->json()['data'];

        $single = $this->actingAs($this->admin())
            ->get("/partner-dashboard-financial-insight-data?Task=TEST-DASH-MITRA&date_start={$start}&date_end={$end}")
            ->assertOk()->json()['data'];

        $this->assertEqualsWithDelta((float) $single['total_qty'], (float) $scoped['total_qty'], 0.01, 'MITRA sees only its own warehouse');
    }

    /** @test */
    public function selected_warehouse_is_unchanged()
    {
        $this->seedWarehouseWithData('TEST-DASH-SINGLE', 'PT-S', 100, 60);

        [$start, $end] = $this->monthRange();

        $data = $this->actingAs($this->admin())
            ->get("/partner-dashboard-financial-insight-data?Task=TEST-DASH-SINGLE&date_start={$start}&date_end={$end}")
            ->assertOk()->json()['data'];

        $this->assertEqualsWithDelta(100.0, (float) $data['total_qty'], 0.01);
    }
}
