<?php

namespace Tests\Feature\UcoLogistics;

use App\Services\OSRMService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests for the settlement flow:
 *   - Route Mapping auto-flows to Settlement (Status = PENDING_SETTLEMENT)
 *   - Settlement submit -> SETTLEMENT_PENDING + settlement rows
 *   - Approval: approve -> SETTLED / reject -> PENDING_SETTLEMENT
 *   - Menu separation: MITRA Settlement = submit page, FINANCE Settlement Cost = approval page
 *   - Acquisition cost receipt stored with the topic the detail view reads
 *   - Route-data JSON exposes Code for the settlement summary card
 */
class SettlementFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function seedPOO(): int
    {
        return DB::table('master_poo')->insertGetId([
            'Name'                => 'Settle POO '.uniqid(),
            'Province'            => 'DKI Jakarta',
            'City'                => 'Jakarta Pusat',
            'Subdistrict'         => 'Menteng',
            'Village'             => 'Menteng',
            'Full_Address'        => 'Jl Test',
            'Nearest_Warehouse'   => 'W-SETTLE',
            'Price'               => '5000',
            'Is_Active'           => 1,
            'Location_Coordinate' => '-6.2,106.8',
        ]);
    }

    private function seedWO(int $pooId): int
    {
        return DB::table('work_order')->insertGetId([
            'POO_Id'          => $pooId,
            'Code'            => 'WO-SETTLE-'.uniqid(),
            'Status'          => 'OPEN',
            'Work_Order_Date' => now(),
        ]);
    }

    private function seedPO(int $woId, ?int $doId = null): int
    {
        return DB::table('pickup_order')->insertGetId([
            'Code'        => 'PO-SETTLE-'.uniqid(),
            'Type_Pickup' => 'pickup',
            'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
            'Site'        => 'W-SETTLE',
            'Package'     => 'jerigen',
            'Qty'         => '100',
            'Price'       => '5000',
            'WO_Id'       => $woId,
            'DO_Id'       => $doId,
            'Created_At'  => now(),
        ]);
    }

    private function seedWarehouse(): int
    {
        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => 'W-SETTLE',
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Loc Settle',
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-SETTLE',
            'Created_Date'       => now(),
            'Created_By'         => 'test',
            'lat'                => -6.2,
            'lng'                => 106.8,
        ]);
    }

    private function mockOsrm(): void
    {
        $this->mock(OSRMService::class, function ($mock) {
            $mock->shouldReceive('getRoute')->once()->andReturn([
                'distance_km'      => 12.5,
                'duration_min'     => 25,
                'encoded_polyline' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@',
            ]);
        });
    }

    private function createRoute(int $warehouseId): array
    {
        $this->mockOsrm();
        $woId = $this->seedWO($this->seedPOO());
        $poA = $this->seedPO($woId);
        $poB = $this->seedPO($woId);

        $this->actingAs($this->admin())
            ->post('/partner-pickup-route-submit', [
                'pickup_order_ids' => [$poA, $poB],
                'warehouse_id'     => $warehouseId,
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
        return [(int) $route->ID, (string) $route->Code];
    }

    // ------------------------------------------------------------------
    // 1. Route Mapping -> transfers to settlement (F1)
    // ------------------------------------------------------------------

    /** @test */
    public function route_creation_auto_flows_to_settlement()
    {
        $whId = $this->seedWarehouse();
        [$routeId, $code] = $this->createRoute($whId);

        $this->assertEquals('PENDING_SETTLEMENT', DB::table('pickup_routes')->where('ID', $routeId)->value('Status'));

        // ...and it appears in the settlement list (Cost & Route Management)
        $json = $this->actingAs($this->admin())
            ->get('/partner-pickup-route-data?status=PENDING_SETTLEMENT&format=json')
            ->assertOk()
            ->json();

        $this->assertContains($code, array_column($json['data'], 'Code'));
    }

    // ------------------------------------------------------------------
    // 2. Settlement submit (F2 contract)
    // ------------------------------------------------------------------

    /** @test */
    public function settlement_submit_moves_route_to_settlement_pending()
    {
        $whId = $this->seedWarehouse();
        [$routeId] = $this->createRoute($whId);

        $this->actingAs($this->admin())
            ->post('/partner-pickup-finish-settlement', [
                'action'         => 'submit',
                'route_ids'      => (string) $routeId,
                'bbm_nominal'    => '250000',
                'bbm_keterangan' => 'BBM rute',
                'tol_nominal'    => '100000',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals('SETTLEMENT_PENDING', DB::table('pickup_routes')->where('ID', $routeId)->value('Status'));

        $bbm = DB::table('settlement')->where('Route_Id', (string) $routeId)->where('Description', 'BBM')->first();
        $this->assertNotNull($bbm);
        $this->assertEquals('250000', $bbm->Nominal);
    }

    // ------------------------------------------------------------------
    // 3. Approval flow
    // ------------------------------------------------------------------

    /** @test */
    public function settlement_approve_and_reject()
    {
        $whId = $this->seedWarehouse();
        [$routeId] = $this->createRoute($whId);

        // move to SETTLEMENT_PENDING
        $this->actingAs($this->admin())
            ->post('/partner-pickup-finish-settlement', ['action' => 'submit', 'route_ids' => (string) $routeId])
            ->assertOk();

        // approve
        $this->actingAs($this->admin())
            ->post('/partner-pickup-finish-settlement', ['action' => 'approve', 'route_id' => $routeId])
            ->assertOk();
        $this->assertEquals('SETTLED', DB::table('pickup_routes')->where('ID', $routeId)->value('Status'));

        // move back and reject
        $this->actingAs($this->admin())
            ->post('/partner-pickup-finish-settlement', ['action' => 'submit', 'route_ids' => (string) $routeId])
            ->assertOk();
        $this->actingAs($this->admin())
            ->post('/partner-pickup-finish-settlement', ['action' => 'reject', 'route_id' => $routeId, 'notes' => 'lengkapi berkas'])
            ->assertOk();
        $this->assertEquals('PENDING_SETTLEMENT', DB::table('pickup_routes')->where('ID', $routeId)->value('Status'));
    }

    // ------------------------------------------------------------------
    // 4. Menu separation (F4/F5)
    // ------------------------------------------------------------------

    /** @test */
    public function settlement_menus_are_separated_by_role()
    {
        $this->assertSame(
            'finance-settlement-cost',
            DB::table('menu_lists')->where('Name', 'Settlement')->where('Modules', 'MITRA')->value('Path_Loc')
        );
        $this->assertSame(
            'finance-settlement-approval',
            DB::table('menu_lists')->where('Name', 'Settlement Cost')->where('Modules', 'FINANCE')->value('Path_Loc')
        );
    }

    // ------------------------------------------------------------------
    // 5. Acquisition receipt topic (F3)
    // ------------------------------------------------------------------

    /** @test */
    public function acquisition_receipt_uses_detail_topic()
    {
        $woId = $this->seedWO($this->seedPOO());
        $doId = DB::table('delivery_order')->insertGetId([
            'Code'      => 'DO-SETTLE-'.uniqid(),
            'DO_Status' => 'DONE',
            'DO_Date'   => now()->toDateString(),
            'Created_At'=> now(),
        ]);
        $poId = $this->seedPO($woId, $doId);

        $file = UploadedFile::fake()->create('receipt.pdf', 20, 'application/pdf');

        $this->actingAs($this->admin())
            ->post('/finance-acquisition-cost-submit', [
                'po_id'                   => $poId,
                'actual_nominal_transfer' => '500000',
                'actual_qty'              => '80',
                'receipt_file'            => $file,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $row = DB::table('meta_files')->where('Topic', 'acquisition-cost-receipt')->where('Map_To', $poId)->first();
        $this->assertNotNull($row, 'receipt must be stored with the topic the detail view reads');
    }

    // ------------------------------------------------------------------
    // 6. Route-data JSON exposes Code (F2 summary contract)
    // ------------------------------------------------------------------

    /** @test */
    public function route_data_json_exposes_code()
    {
        $whId = $this->seedWarehouse();
        [$routeId, $code] = $this->createRoute($whId);

        $json = $this->actingAs($this->admin())
            ->get('/partner-pickup-route-data?format=json&id=' . $routeId)
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('Code', $json['data'][0]);
        $this->assertEquals($code, $json['data'][0]['Code']);
    }
}
