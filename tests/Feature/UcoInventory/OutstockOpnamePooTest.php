<?php

namespace Tests\Feature\UcoInventory;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Integration tests for:
 *   - Outstock menu entry repointed to the functional list (partner-outstock)
 *   - partner-warehouse-data now serves warehouse_partner (real warehouses)
 *   - master-container-get supports warehouse_id scoping (opname IBC dropdown)
 *   - stock opname date cannot be backdated
 *   - POO create accepts curated Type / Price_Unit dropdown values
 */
class OutstockOpnamePooTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
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

    private function seedContainer(int $warehouseId): int
    {
        return DB::table('master_container')->insertGetId([
            'Name'         => 'Container '.uniqid(),
            'Volume'       => 1000,
            'Warehouse_ID' => $warehouseId,
            'Status'       => 'active',
        ]);
    }

    // ------------------------------------------------------------------
    // 1. Outstock menu repoint
    // ------------------------------------------------------------------

    /** @test */
    public function outstock_menu_points_to_functional_list()
    {
        $this->assertSame(
            'partner-outstock',
            DB::table('menu_lists')->where('Name', 'Outstock')->value('Path_Loc')
        );
    }

    // ------------------------------------------------------------------
    // 2. Warehouse source
    // ------------------------------------------------------------------

    /** @test */
    public function warehouse_data_serves_warehouse_partner_fields()
    {
        $whId = $this->seedWarehouse('W-SRC-1');

        $json = $this->actingAs($this->admin())
            ->get('/partner-warehouse-data')
            ->assertOk()
            ->json();

        $this->assertNotEmpty($json['data']);
        $row = collect($json['data'])->firstWhere('id', $whId);
        $this->assertNotNull($row, 'seeded warehouse should appear');
        $this->assertArrayHasKey('warehouse_task_id', $row);
        $this->assertArrayHasKey('warehouse_location', $row);
        $this->assertArrayHasKey('lat', $row);
        $this->assertArrayHasKey('lng', $row);
        $this->assertEquals('W-SRC-1', $row['warehouse_task_id']);
    }

    // ------------------------------------------------------------------
    // 3. Container scoping
    // ------------------------------------------------------------------

    /** @test */
    public function container_get_filters_by_warehouse()
    {
        $whA = $this->seedWarehouse('W-A');
        $whB = $this->seedWarehouse('W-B');
        $cA = $this->seedContainer($whA);
        $cB = $this->seedContainer($whB);

        $filtered = $this->actingAs($this->admin())
            ->get('/master-container-get?warehouse_id=' . $whA)
            ->assertOk()
            ->json();

        $ids = array_column($filtered['data'], 'id');
        $this->assertContains($cA, $ids);
        $this->assertNotContains($cB, $ids);

        $all = $this->actingAs($this->admin())
            ->get('/master-container-get')
            ->assertOk()
            ->json();
        $this->assertContains($cB, array_column($all['data'], 'id'));
    }

    // ------------------------------------------------------------------
    // 4. Opname date
    // ------------------------------------------------------------------

    /** @test */
    public function opname_rejects_backdated_date()
    {
        $whId = $this->seedWarehouse('W-OP');
        $cId = $this->seedContainer($whId);

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $whId,
                'container_id' => $cId,
                'opname_date'  => '2020-01-01',
            ])
            ->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function opname_accepts_today_date()
    {
        $whId = $this->seedWarehouse('W-OP2');
        $cId = $this->seedContainer($whId);

        $this->actingAs($this->admin())
            ->postJson('/partner-warehouse-stock-opname-submit', [
                'warehouse_id' => $whId,
                'container_id' => $cId,
                'opname_date'  => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    // ------------------------------------------------------------------
    // 5. POO create with curated dropdowns
    // ------------------------------------------------------------------

    /** @test */
    public function poo_create_accepts_curated_type_and_price_unit()
    {
        $whId = $this->seedWarehouse('W-POO');

        $this->actingAs($this->admin())
            ->postJson('/master-poo-create-submit', [
                'Type'                 => 'SUPPLIER',
                'Name'                 => 'POO Dropdown '.uniqid(),
                'PIC'                  => 'PIC Test',
                'Employee'             => 'Emp Test',
                'Phone'                => '0812',
                'Position'             => 'Owner',
                'Category'             => 'UCO',
                'UCO_Type'             => 'Cair',
                'Price'                => '5000',
                'Price_Unit'           => 'KG',
                'Province'             => 'DKI Jakarta',
                'City'                 => 'Jakarta Pusat',
                'Subdistrict'          => 'Menteng',
                'Village'              => 'Menteng',
                'Full_Address'         => 'Jl Test',
                'Location_Coordinate'  => '-6.2,106.8',
                'Nearest_Warehouse'    => $whId,
                'Bank_Account_Number'  => '123',
                'Bank_Account_Name'    => 'Test',
                'Bank_Name'            => 'BCA',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $poo = DB::table('master_poo')->where('Name', 'LIKE', 'POO Dropdown%')->latest('ID')->first();
        $this->assertNotNull($poo);
        $this->assertEquals('SUPPLIER', $poo->Type);
        $this->assertEquals('KG', $poo->Price_Unit);
    }
}
