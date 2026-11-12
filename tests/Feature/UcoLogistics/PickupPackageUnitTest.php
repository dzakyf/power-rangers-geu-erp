<?php

namespace Tests\Feature\UcoLogistics;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Package-based pickup orders (e.g. Jerigen 18L) must keep their package
 * quantities and prices while stock/inventory remains in liters.
 */
class PickupPackageUnitTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedPackaging(string $name, float $capacity): void
    {
        DB::table('master_packaging')->insert([
            'Name'       => $name,
            'Capacity'   => $capacity,
            'Unit'       => 'L',
            'Status'     => 'active',
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);
    }

    private function seedWarehouse(string $taskId = null): int
    {
        $taskId = $taskId ?: 'W-PKG-' . uniqid();

        return DB::table('warehouse_partner')->insertGetId([
            'warehouse_task_id'  => $taskId,
            'warehouse_type'     => 'mobil',
            'Location'           => 'Test',
            'warehouse_location' => 'Loc ' . $taskId,
            'warehouse_status'   => 'ACTIVE',
            'partner_task_id'    => 'PT-' . uniqid(),
            'Created_Date'       => now(),
            'Created_By'         => '__test__',
            'lat'                => -6.2,
            'lng'                => 106.8,
        ]);
    }

    private function seedPoo(int $warehouseId, float $pricePerLiter): int
    {
        return DB::table('master_poo')->insertGetId([
            'Name'                => 'Package POO ' . uniqid(),
            'Province'            => 'DKI Jakarta',
            'City'                => 'Jakarta Pusat',
            'Subdistrict'         => 'Menteng',
            'Village'             => 'Menteng',
            'Full_Address'        => 'Jl Test',
            'Nearest_Warehouse'   => (string) $warehouseId,
            'Price'               => (string) $pricePerLiter,
            'Price_Unit'          => 'L',
            'Is_Active'           => 1,
            'Location_Coordinate' => '-6.2,106.8',
        ]);
    }

    private function seedWorkOrder(int $pooId): int
    {
        return DB::table('work_order')->insertGetId([
            'POO_Id'          => $pooId,
            'Code'            => 'WO-PKG-' . uniqid(),
            'Status'          => 'OPEN',
            'Work_Order_Date' => now(),
        ]);
    }

    private function seedDeliveryOrder(string $status = 'PROGRESS'): int
    {
        return DB::table('delivery_order')->insertGetId([
            'Code'       => 'DO-PKG-' . uniqid(),
            'DO_Status'  => $status,
            'DO_Date'    => now()->toDateString(),
            'Created_At' => now(),
        ]);
    }

    private function seedPickup(int $woId, ?int $doId, array $overrides = []): int
    {
        return DB::table('pickup_order')->insertGetId(array_merge([
            'Code'        => 'PO-PKG-' . uniqid(),
            'Type_Pickup' => 'pickup',
            'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
            'Site'        => 'W-PKG',
            'Package'     => 'Jerigen 18L Test',
            'Qty'         => 5,
            'Price'       => 160000,
            'WO_Id'       => $woId,
            'DO_Id'       => $doId,
            'Created_At'  => now(),
        ], $overrides));
    }

    /** @test */
    public function delivery_note_page_offers_package_and_liter_units(): void
    {
        $this->seedPackaging('Jerigen 18L Test', 18);
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do, [
            'Actual_Qty' => null,
            'Qty_Unit' => 'PCS',
            'Price_Unit' => 'PCS',
            'Package_Capacity' => 18,
        ]);

        $html = $this->actingAs($this->admin())
            ->get('/partner-delivery-order-update?po_id=' . $po)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Jerigen 18L Test', $html);
        $this->assertStringContainsString('value="PCS"', $html);
        $this->assertStringContainsString('18,00', $html);
        $this->assertStringContainsString('90,00', $html);
    }

    /** @test */
    public function delivery_note_submit_converts_packages_to_liters(): void
    {
        $this->seedPackaging('Jerigen 18L Test', 18);
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder('PROGRESS');
        $po = $this->seedPickup($wo, $do, [
            'Qty_Unit' => 'PCS',
            'Price_Unit' => 'PCS',
            'Package_Capacity' => 18,
        ]);

        $this->actingAs($this->admin())
            ->postJson('/partner-delivery-order-update-submit', [
                'PO_Id'      => $po,
                'Actual_Qty' => 5,
                'BeratUnit'  => 'PCS',
                'DO_Status'  => 'DONE',
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals(90.0, (float) DB::table('pickup_order')->where('ID', $po)->value('Actual_Qty'));
    }

    /** @test */
    public function pickup_creation_persists_package_units_and_capacity(): void
    {
        $this->seedPackaging('Jerigen 18L Test', 18);
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));

        $this->actingAs($this->admin())
            ->post('/partner-pickup-create-submit', [
                'WO_Id'       => $wo,
                'Type_Pickup' => 'pickup',
                'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
                'Site'        => 'W-PKG',
                'Package'     => 'Jerigen 18L Test',
                'Qty'         => 5,
                'Price'       => 160000,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $po = DB::table('pickup_order')->latest('ID')->first();
        $this->assertSame('PCS', $po->Qty_Unit);
        $this->assertSame('PCS', $po->Price_Unit);
        $this->assertEquals(18.0, (float) $po->Package_Capacity);
    }

    /** @test */
    public function bypass_work_order_creation_uses_the_package_price(): void
    {
        $this->seedPackaging('Jerigen 18L Test', 18);
        $wh = $this->seedWarehouse();
        $poo = $this->seedPoo($wh, 8000);
        $do = $this->seedDeliveryOrder('PROGRESS');

        $this->actingAs($this->admin())
            ->post('/partner-work-order-create-submit', [
                'POO_Id'      => $poo,
                'Status'      => 'OPEN',
                'bypass'      => '1',
                'DO_Id'       => $do,
                'Type_Pickup' => 'pickup',
                'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
                'Site'        => 'W-PKG',
                'Package'     => 'Jerigen 18L Test',
                'Qty'         => 5,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $po = DB::table('pickup_order')->latest('ID')->first();
        $this->assertSame('PCS', $po->Qty_Unit);
        $this->assertSame('PCS', $po->Price_Unit);
        $this->assertEquals(18.0, (float) $po->Package_Capacity);
        $this->assertEquals(144000.0, (float) $po->Price);
    }

    /** @test */
    public function instock_preview_values_package_pos_using_the_po_price_per_liter(): void
    {
        $this->seedPackaging('Jerigen 18L Test', 18);
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder('DONE');
        $po = $this->seedPickup($wo, $do, [
            'Qty'              => 5,
            'Price'            => 160000,
            'Actual_Qty'       => 90,
            'Qty_Unit'         => 'PCS',
            'Price_Unit'       => 'PCS',
            'Package_Capacity' => 18,
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-preview', ['do_ids' => [$do]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $group = $response->json('data.0');
        $this->assertNotNull($group);
        $this->assertEquals(90, (float) $group['qty_plan'], 'plan quantity must be normalized to liters');
        $this->assertEquals(90, (float) $group['qty_actual']);
        $this->assertEquals(800000, (float) $group['total']);
        $this->assertEquals(8888.89, (float) $group['avg_price']);
        $this->assertEquals($po, (int) $group['po_details'][0]['po_id']);
    }

    /** @test */
    public function legacy_package_rows_that_were_entered_in_liters_stay_in_liters(): void
    {
        $this->seedPackaging('Jerigen 18L Test', 18);
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 7000));
        $do = $this->seedDeliveryOrder('DONE');
        $this->seedPickup($wo, $do, [
            'Qty'        => 755,
            'Price'      => 7000,
            'Actual_Qty' => 755,
        ]);

        $response = $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-preview', ['do_ids' => [$do]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $group = $response->json('data.0');
        $this->assertEquals(755, (float) $group['qty_plan']);
        $this->assertEquals(755, (float) $group['qty_actual']);
        $this->assertEquals(5285000, (float) $group['total']);
        $this->assertEquals(7000, (float) $group['avg_price']);
    }
}
