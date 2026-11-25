<?php

namespace Tests\Feature\CNS;

use App\Http\Controllers\CNS\PackingListController;
use App\PackingList;
use App\User;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinalPackingListFromStuffingTest extends TestCase
{
    private function beginDatabaseTransactionOrSkip(): void
    {
        try {
            DB::connection()->getPdo();
            DB::beginTransaction();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database connection unavailable: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        try {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
        }

        parent::tearDown();
    }

    /** @test */
    public function final_packing_list_routes_are_registered(): void
    {
        $routes = app('router')->getRoutes();

        $finalNewRoute = $routes->match(app('request')->create('cns-export-packing-list-final-new', 'GET'));
        $this->assertNotNull($finalNewRoute);

        $getBcRoute = $routes->match(app('request')->create('cns-export-packing-list-get-bc', 'POST'));
        $this->assertNotNull($getBcRoute);
    }

    /** @test */
    public function stuffing_view_has_create_final_packing_list_button(): void
    {
        $content = file_get_contents(resource_path('views/cns/vs/stuffing.blade.php'));

        $this->assertStringContainsString('id="CreateFinalPackingList"', $content);
        $this->assertStringContainsString('value="Create Final Packing List"', $content);
        $this->assertStringContainsString('onclick="createFinalPackingList()"', $content);
        $this->assertStringContainsString('cns-export-packing-list-final-new', $content);
    }

    /** @test */
    public function final_pl_new_view_has_bc_selector_weight_totals_and_description(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/pl-final-new.blade.php'));

        $this->assertStringContainsString('name="BC_No"', $content);
        $this->assertStringContainsString('name="Is_Final"', $content);
        $this->assertStringContainsString('name="Total_Netto_KG"', $content);
        $this->assertStringContainsString('name="Total_Gross_KG"', $content);
        $this->assertStringContainsString('name="Description"', $content);
        $this->assertStringContainsString('cns-export-packing-list-get-bc', $content);
    }

    /** @test */
    public function build_final_description_formats_product_and_accumulated_nw_gw(): void
    {
        $controller = new PackingListController();
        $method = new \ReflectionMethod($controller, 'buildFinalDescription');
        $method->setAccessible(true);

        $desc = $method->invoke($controller, 'Refined Used Cooking Oil', 42500.5, 45100.75);

        $this->assertStringContainsString('REFINED USED COOKING OIL', $desc);
        $this->assertStringContainsString('NW: 42,500.50 KGS', $desc);
        $this->assertStringContainsString('GW: 45,100.75 KGS', $desc);
    }

    /** @test */
    public function get_stuffing_by_bc_accumulates_all_stuffing_containers_and_creates_final_pl(): void
    {
        $this->beginDatabaseTransactionOrSkip();

        if (!Schema::hasColumn('packing_lists', 'BC_No')) {
            $this->markTestSkipped('packing_lists.BC_No column not migrated yet.');
        }

        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        $uniq = uniqid();
        $bcNo = 'BC-TEST-' . $uniq;

        $vs1 = VesselSchedule::create([
            'Task_ID' => 'VS-1-' . $uniq,
            'Order_No' => 'ORD-1-' . $uniq,
            'Booking_No' => 'BK-1-' . $uniq,
            'BC_No' => $bcNo,
            'Vessel' => 'MV TEST',
            'POL' => 'IDTPP',
            'POD' => 'MYPKG',
            'Stuffing' => date('Y-m-d'),
            'ETD' => date('Y-m-d'),
            'ETA' => date('Y-m-d'),
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $vs2 = VesselSchedule::create([
            'Task_ID' => 'VS-2-' . $uniq,
            'Order_No' => 'ORD-2-' . $uniq,
            'Booking_No' => 'BK-2-' . $uniq,
            'BC_No' => $bcNo,
            'Vessel' => 'MV TEST',
            'POL' => 'IDTPP',
            'POD' => 'MYPKG',
            'Stuffing' => date('Y-m-d'),
            'ETD' => date('Y-m-d'),
            'ETA' => date('Y-m-d'),
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        VesselScheduleDtl::create([
            'Task_ID' => $vs1->Task_ID,
            'Order_No' => $vs1->Order_No,
            'Container' => 'CONT1111',
            'Seal' => 'SEAL1',
            'Tare' => 2200,
            'Netto_KG' => 21000,
            'Netto_MT' => 21,
            'Gross_KG' => 23200,
            'Gross_MT' => 23.2,
            'Status' => 'ACTIVE',
        ]);

        VesselScheduleDtl::create([
            'Task_ID' => $vs2->Task_ID,
            'Order_No' => $vs2->Order_No,
            'Container' => 'CONT2222',
            'Seal' => 'SEAL2',
            'Tare' => 2300,
            'Netto_KG' => 21500.5,
            'Netto_MT' => 21.5,
            'Gross_KG' => 23800.5,
            'Gross_MT' => 23.8,
            'Status' => 'ACTIVE',
        ]);

        $res = $this->actingAs($user)
            ->postJson('/cns-export-packing-list-get-bc', ['bc_no' => $bcNo]);

        $res->assertOk()
            ->assertJsonPath('totals.container_count', 2)
            ->assertJsonPath('totals.netto_kg', 42500.5)
            ->assertJsonPath('totals.gross_kg', 47000.5);

        $this->assertStringContainsString('NW: 42,500.50 KGS', $res->json('description'));
        $this->assertStringContainsString('GW: 47,000.50 KGS', $res->json('description'));

        $plNo = 'PL-TEST-' . $uniq;
        $submitRes = $this->actingAs($user)->post('/cns-export-packing-list-new-submit', [
            'Is_Final' => 1,
            'BC_No' => $bcNo,
            'Packing_List_No' => $plNo,
            'PL_Date' => date('Y-m-d'),
            'Qty1' => 2,
            'Qty2' => '20 FT',
        ]);

        $submitRes->assertOk();
        $pl = PackingList::where('Packing_List_No', $plNo)->first();
        $this->assertNotNull($pl);
        $this->assertEquals(1, (int) $pl->Is_Final);
        $this->assertEquals($bcNo, $pl->BC_No);
        $this->assertEquals(42500.5, (float) $pl->Total_Netto_KG);
        $this->assertEquals(47000.5, (float) $pl->Total_Gross_KG);
        $this->assertStringContainsString('NW: 42,500.50 KGS', $pl->Description);
        $this->assertStringContainsString('GW: 47,000.50 KGS', $pl->Description);
    }
}
