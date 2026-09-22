<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Tests\TestCase;

/**
 * Integration tests for instock dregs (Ampas) warehouse tracking:
 *   - partner-warehouse-data?do_id= scopes the list to warehouses touched by an SJ
 *   - dregs submit requires DO_Id + Warehouse_ID; warehouse must belong to the SJ
 *   - dregs data exposes Surat Jalan / Warehouse columns and whid scoping
 *   - warehouse operation log merges DREGS rows without touching the running balance
 */
class InstockDregsTest extends TestCase
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
            'Created_By'         => '__test__',
            'lat'                => -6.2,
            'lng'                => 106.8,
        ]);
    }

    private function seedPooToWarehouse(int $whId): int
    {
        return DB::table('master_poo')->insertGetId([
            'Name'                => 'Dregs POO '.uniqid(),
            'Province'            => 'DKI Jakarta',
            'City'                => 'Jakarta Pusat',
            'Subdistrict'         => 'Menteng',
            'Village'             => 'Menteng',
            'Full_Address'        => 'Jl Test',
            'Nearest_Warehouse'   => (string) $whId,
            'Price'               => '5000',
            'Is_Active'           => 1,
            'Location_Coordinate' => '-6.2,106.8',
        ]);
    }

    private function seedPo(int $pooId, ?int $doId = null): int
    {
        $woId = DB::table('work_order')->insertGetId([
            'POO_Id'          => $pooId,
            'Code'            => 'WO-DREGS-'.uniqid(),
            'Status'          => 'OPEN',
            'Work_Order_Date' => now(),
        ]);

        return DB::table('pickup_order')->insertGetId([
            'Code'       => 'PO-DREGS-'.uniqid(),
            'Qty'        => '100',
            'WO_Id'      => $woId,
            'DO_Id'      => $doId,
            'Created_At' => now(),
        ]);
    }

    private function seedSJ(): array
    {
        $whA = $this->seedWarehouse('W-DREGS-A');
        $whB = $this->seedWarehouse('W-DREGS-B');
        $whC = $this->seedWarehouse('W-DREGS-C');

        $sj1 = DB::table('delivery_order')->insertGetId([
            'Code'       => 'DO-DREGS-1-'.uniqid(),
            'DO_Status'  => 'DONE',
            'DO_Date'    => now()->toDateString(),
            'Created_At' => now(),
        ]);
        // SJ1 touches two warehouses (multi-warehouse SJ)
        $this->seedPo($this->seedPooToWarehouse($whA), $sj1);
        $this->seedPo($this->seedPooToWarehouse($whB), $sj1);

        $sj2 = DB::table('delivery_order')->insertGetId([
            'Code'       => 'DO-DREGS-2-'.uniqid(),
            'DO_Status'  => 'DONE',
            'DO_Date'    => now()->toDateString(),
            'Created_At' => now(),
        ]);
        $this->seedPo($this->seedPooToWarehouse($whA), $sj2);

        return compact('whA', 'whB', 'whC', 'sj1', 'sj2');
    }

    private function seedDregs(?int $doId, ?int $whId, float $qty): int
    {
        return DB::table('instock_dregs')->insertGetId([
            'Instock_Date' => now()->toDateString(),
            'DO_Id'        => $doId,
            'Warehouse_ID' => $whId,
            'Quantity'     => $qty,
            'Notes'        => 'seed',
            'Created_At'   => now(),
        ]);
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
    // 1. partner-warehouse-data?do_id scopes to SJ warehouses
    // ------------------------------------------------------------------

    /** @test */
    public function warehouse_data_scoped_to_surat_jalan_touches()
    {
        extract($this->seedSJ());

        $scoped = $this->actingAs($this->admin())
            ->get('/partner-warehouse-data?do_id='.$sj1)
            ->assertOk()
            ->json();

        $ids = array_column($scoped['data'], 'id');
        $this->assertContains($whA, $ids);
        $this->assertContains($whB, $ids);
        $this->assertNotContains($whC, $ids, 'warehouse untouched by the SJ must not appear');

        $single = $this->actingAs($this->admin())
            ->get('/partner-warehouse-data?do_id='.$sj2)
            ->assertOk()
            ->json();
        $ids2 = array_column($single['data'], 'id');
        $this->assertContains($whA, $ids2);
        $this->assertNotContains($whB, $ids2);
        $this->assertNotContains($whC, $ids2);

        $all = $this->actingAs($this->admin())
            ->get('/partner-warehouse-data')
            ->assertOk()
            ->json();
        $allIds = array_column($all['data'], 'id');
        $this->assertContains($whA, $allIds);
        $this->assertContains($whB, $allIds);
        $this->assertContains($whC, $allIds);
    }

    // ------------------------------------------------------------------
    // 2. Submit validation
    // ------------------------------------------------------------------

    /** @test */
    public function dregs_submit_requires_surat_jalan()
    {
        $before = DB::table('instock_dregs')->count();

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-dregs-submit', [
                'Instock_Date' => now()->toDateString(),
                'Quantity'     => '12.5',
            ])
            ->assertStatus(400)
            ->assertJson(['success' => false]);

        $this->assertSame($before, DB::table('instock_dregs')->count());
    }

    /** @test */
    public function dregs_submit_derives_warehouse_from_sj()
    {
        extract($this->seedSJ());

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-dregs-submit', [
                'Instock_Date' => now()->toDateString(),
                'Quantity'     => '12.5',
                'DO_Id'        => $sj1,
                'Notes'        => 'ampas dari SJ1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $row = DB::table('instock_dregs')->orderBy('ID', 'desc')->first();
        $this->assertEquals($sj1, $row->DO_Id);
        // warehouse is derived from the SJ's first pickup order (POO A -> whA)
        $this->assertEquals($whA, $row->Warehouse_ID);
    }

    // ------------------------------------------------------------------
    // 3. Dregs data columns + whid scoping
    // ------------------------------------------------------------------

    /** @test */
    public function dregs_data_exposes_sj_and_warehouse_and_whid_filter()
    {
        extract($this->seedSJ());
        $doCode = DB::table('delivery_order')->where('ID', $sj1)->value('Code');
        $legacyId = $this->seedDregs(null, null, 1.0);
        $rowAId = $this->seedDregs($sj1, $whA, 5.0);
        $rowBId = $this->seedDregs($sj1, $whB, 7.0);

        $rows = $this->xmlRows(
            $this->actingAs($this->admin())
                ->get('/partner-uco-instock-dregs-data')
                ->assertOk()
                ->getContent()
        );

        $this->assertCount(7, $rows[$rowAId], 'expected No,Date,Qty,SJ,Warehouse,Notes,Created cells');
        $this->assertEquals($doCode, $rows[$rowAId][3]);
        $this->assertEquals('Loc W-DREGS-A', $rows[$rowAId][4]);
        $this->assertEquals('', $rows[$legacyId][3], 'legacy row has no SJ');
        $this->assertEquals('', $rows[$legacyId][4], 'legacy row has no warehouse');

        $scoped = $this->xmlRows(
            $this->actingAs($this->admin())
                ->get('/partner-uco-instock-dregs-data?whid='.$whA)
                ->assertOk()
                ->getContent()
        );

        $this->assertArrayHasKey($rowAId, $scoped);
        $this->assertArrayNotHasKey($rowBId, $scoped);
        $this->assertArrayNotHasKey($legacyId, $scoped);
    }

    // ------------------------------------------------------------------
    // 4. Warehouse operation log merges DREGS, saldo untouched
    // ------------------------------------------------------------------

    /** @test */
    public function warehouse_ledger_shows_dregs_without_touching_balance()
    {
        extract($this->seedSJ());
        $taskId = DB::table('warehouse_partner')->where('id', $whA)->value('warehouse_task_id');

        DB::table('warehouse_partner_supplies')->insertGetId([
            'warehouse_task_id' => $taskId,
            'received_date'     => now()->toDateString(),
            'pickup_type'       => 'PICKUP',
            'name_poo'          => 'POO A',
            'source'            => 'MITRA',
            'coverage'          => 'Test',
            'received_qty'      => 100,
            'price'             => 0,
            'total'             => 0,
            'rejected_qty'      => 0,
            'created_date'      => now(),
            'created_by'        => '__test__',
        ]);

        DB::table('warehouse_partner_stock_request')->insertGetId([
            'warehouse_task_id' => $taskId,
            'request_qty'       => 30,
            'request_date'      => now()->toDateString(),
            'price'             => 0,
            'total'             => 0,
            'reference'         => 'REQ-1',
            'remark'            => '',
            'created_date'      => now(),
            'created_by'        => '__test__',
        ]);

        $dregsId = $this->seedDregs($sj1, $whA, 5);
        $legacyId = $this->seedDregs(null, null, 99);

        $rows = $this->xmlRows(
            $this->actingAs($this->admin())
                ->get('/xml-partner-warehouse-list-detail?Task='.$whA)
                ->assertOk()
                ->getContent()
        );

        $dregsRows = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'DREGS'));
        $this->assertCount(1, $dregsRows, 'only dregs assigned to this warehouse appear');
        $this->assertEquals('5.00', $dregsRows[0][6], 'dregs qty in its own column');
        $this->assertEquals('70', $dregsRows[0][7], 'dregs must not change the saldo');

        $instock = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'INSTOCK'));
        $this->assertEquals('100', $instock[0][4]);
        $outstock = array_values(array_filter($rows, fn ($c) => ($c[2] ?? '') === 'Outstock'));
        $this->assertEquals('30', $outstock[0][5]);
        $this->assertEquals('70', $outstock[0][7]);
    }
}
