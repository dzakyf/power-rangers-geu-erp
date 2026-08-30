<?php

namespace Tests\Feature\UcoLogistics;

use App\Services\OSRMService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests for the UCO logistics fixes:
 *   1. WO Date locked to today (server-enforced)
 *   2. WO bypass PO keeps editable Site/Coverage (no POO override)
 *   3. Delivery order create with PROGRESS status + PO linkage
 *   4. Delivery note update persists DO_Status
 *   5. Pickup grid XML carries the PO Code (displayed as PO Number)
 *   6. Route submit persists pickup_count / total_weight
 */
class PickupLogisticsTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function seedPOO(): int
    {
        return DB::table('master_poo')->insertGetId([
            'Name'               => 'POO Test '.uniqid(),
            'Province'           => 'DKI Jakarta',
            'City'               => 'Jakarta Pusat',
            'Subdistrict'        => 'Menteng',
            'Village'            => 'Menteng',
            'Full_Address'       => 'Jl Test 1',
            'Nearest_Warehouse'  => 'W-TEST-1',
            'Price'              => '5000',
            'Is_Active'          => 1,
            'Location_Coordinate'=> '-6.2,106.8',
        ]);
    }

    private function seedWO(int $pooId): int
    {
        return DB::table('work_order')->insertGetId([
            'POO_Id'          => $pooId,
            'Code'            => 'WO-TEST-'.uniqid(),
            'Status'          => 'OPEN',
            'Work_Order_Date' => now(),
        ]);
    }

    private function seedDO(string $status = 'PROGRESS'): int
    {
        return DB::table('delivery_order')->insertGetId([
            'Code'      => 'DO-TEST-'.uniqid(),
            'DO_Status' => $status,
            'DO_Date'   => now()->toDateString(),
            'Created_At'=> now(),
        ]);
    }

    private function seedPO(int $woId, ?int $doId = null): int
    {
        return DB::table('pickup_order')->insertGetId([
            'Code'        => 'PO-TEST-'.uniqid(),
            'Type_Pickup' => 'mandiri',
            'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
            'Site'        => 'W-TEST-1',
            'Package'     => 'jerigen',
            'Qty'         => '100',
            'Price'       => '5000',
            'WO_Id'       => $woId,
            'DO_Id'       => $doId,
            'Created_At'  => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // 1. WO Date locked to today
    // ------------------------------------------------------------------

    /** @test */
    public function wo_date_is_forced_to_today()
    {
        $pooId = $this->seedPOO();

        $this->actingAs($this->admin())
            ->post('/partner-work-order-create-submit', [
                'POO_Id'          => $pooId,
                'Status'          => 'OPEN',
                'Work_Order_Date' => '2020-01-01', // client tries a backdate
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $wo = DB::table('work_order')->latest('id')->first();
        $this->assertEquals(now()->toDateString(), substr($wo->Work_Order_Date, 0, 10));
    }

    // ------------------------------------------------------------------
    // 2. WO bypass PO keeps editable Site/Coverage
    // ------------------------------------------------------------------

    /** @test */
    public function wo_bypass_persists_editable_site_and_coverage()
    {
        $pooId = $this->seedPOO();
        $doId = $this->seedDO();

        $this->actingAs($this->admin())
            ->post('/partner-work-order-create-submit', [
                'POO_Id'    => $pooId,
                'Status'    => 'OPEN',
                'bypass'    => '1',
                'DO_Id'     => $doId,
                'Type_Pickup' => 'pickup',
                'Coverage'  => 'Custom Coverage XYZ',
                'Site'      => 'Custom Site ABC',
                'Package'   => 'drum',
                'Qty'       => '50',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $po = DB::table('pickup_order')->latest('id')->first();
        $this->assertEquals('Custom Coverage XYZ', $po->Coverage);
        $this->assertEquals('Custom Site ABC', $po->Site); // NOT overridden by POO City
        $this->assertEquals($doId, (int) $po->DO_Id);
    }

    // ------------------------------------------------------------------
    // 3. Delivery order create: PROGRESS status + PO linkage
    // ------------------------------------------------------------------

    /** @test */
    public function delivery_order_create_sets_status_and_links_pos()
    {
        $woId = $this->seedWO($this->seedPOO());
        $poA = $this->seedPO($woId);
        $poB = $this->seedPO($woId);

        $this->actingAs($this->admin())
            ->post('/partner-delivery-order-create-submit', [
                'PO_Id'     => [$poA, $poB],
                'DO_Status' => 'PROGRESS',
                'DO_Date'   => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $do = DB::table('delivery_order')->latest('id')->first();
        $this->assertEquals('PROGRESS', $do->DO_Status);

        $this->assertEquals($do->ID, (int) DB::table('pickup_order')->where('ID', $poA)->value('DO_Id'));
        $this->assertEquals($do->ID, (int) DB::table('pickup_order')->where('ID', $poB)->value('DO_Id'));
    }

    // ------------------------------------------------------------------
    // 4. Delivery note update persists DO_Status
    // ------------------------------------------------------------------

    /** @test */
    public function delivery_note_update_persists_status()
    {
        $woId = $this->seedWO($this->seedPOO());
        $doId = $this->seedDO('PROGRESS');
        $poId = $this->seedPO($woId, $doId);

        $this->actingAs($this->admin())
            ->post('/partner-delivery-order-update-submit', [
                'PO_Id'     => $poId,
                'Actual_Qty'=> '90',
                'DO_Status' => 'DONE',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals('DONE', DB::table('delivery_order')->where('ID', $doId)->value('DO_Status'));
        $this->assertEquals('90', DB::table('pickup_order')->where('ID', $poId)->value('Actual_Qty'));
    }

    // ------------------------------------------------------------------
    // 5. Pickup grid XML carries PO Code (PO Number column)
    // ------------------------------------------------------------------

    /** @test */
    public function pickup_xml_emits_po_code_in_cell_2()
    {
        $woId = $this->seedWO($this->seedPOO());
        $poId = $this->seedPO($woId);

        $xml = simplexml_load_string(
            $this->actingAs($this->admin())
                ->get('/partner-pickup-data?format=xml')
                ->assertOk()
                ->getContent()
        );

        $cells = [];
        foreach ($xml->row[0]->cell as $cell) {
            $cells[] = (string) $cell;
        }
        $code = DB::table('pickup_order')->where('ID', $poId)->value('Code');
        $this->assertContains($code, $cells);
        // cell[1] is the PO Code (2nd column = the PO Number column in the grids)
        $this->assertEquals($code, $cells[1]);
    }

    // ------------------------------------------------------------------
    // 6. Route submit persists pickup_count / total_weight
    // ------------------------------------------------------------------

    /** @test */
    public function route_submit_persists_pickup_count_and_total_weight()
    {
        $this->mock(OSRMService::class, function ($mock) {
            $mock->shouldReceive('getRoute')->once()->andReturn([
                'distance_km'      => 12.5,
                'duration_min'     => 25,
                'encoded_polyline' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@',
            ]);
        });

        $woId = $this->seedWO($this->seedPOO());
        $poA = $this->seedPO($woId);
        $poB = $this->seedPO($woId);
        $whId = DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => 'W-TEST-1',
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Test Loc',
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-1',
            'Created_Date'       => now(),
            'Created_By'         => 'test',
            'lat'                => -6.2,
            'lng'                => 106.8,
        ]);

        $this->actingAs($this->admin())
            ->post('/partner-pickup-route-submit', [
                'pickup_order_ids' => [$poA, $poB],
                'warehouse_id'     => $whId,
                'bbm_ratio'        => 'ev',
                'bbm_consumption'  => '10',
                'bbm_price'        => '13000',
                'total_distance'   => '12.5',
                'pickup_count'     => '2',
                'total_weight'     => '200',
                'fleet_id'         => '1',
                'toll_estimation'  => '0',
                'parking_cost'     => '0',
                'alkon_bbm_cost'   => '0',
                'driver_operational'=> '0',
                'field_operational'=> '7000',
                'cost_method'      => 'variable',
                'apc'              => '0',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $route = DB::table('pickup_routes')->latest('id')->first();
        $this->assertNotNull($route);
        $this->assertEquals('2', $route->Pickup_Waypoints); // pickup_count
        $this->assertEquals('200', $route->Total_Weight);    // total_weight
    }
}
