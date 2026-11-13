<?php

namespace Tests\Feature\UcoInventory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstockPriceAndDuplicateGuardTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedWarehouse(): int
    {
        $taskId = 'W-INSTOCK-' . uniqid();

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

    private function seedContainer(int $warehouseId, float $volume = 100000): int
    {
        return DB::table('master_container')->insertGetId([
            'Name'         => 'INS-C-' . uniqid(),
            'Volume'       => $volume,
            'Warehouse_ID' => $warehouseId,
            'Status'       => 'active',
        ]);
    }

    private function seedPoo(int $warehouseId, float $pricePerLiter = 8000): int
    {
        return DB::table('master_poo')->insertGetId([
            'Name'                => 'INS POO ' . uniqid(),
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
            'Code'            => 'WO-INS-' . uniqid(),
            'Status'          => 'OPEN',
            'Work_Order_Date' => now(),
        ]);
    }

    private function seedDeliveryOrder(string $status = 'DONE'): int
    {
        return DB::table('delivery_order')->insertGetId([
            'Code'       => 'DO-INS-' . uniqid(),
            'DO_Status'  => $status,
            'DO_Date'    => now()->toDateString(),
            'Created_At' => now(),
        ]);
    }

    private function seedPickup(int $woId, int $doId, array $overrides = []): int
    {
        return DB::table('pickup_order')->insertGetId(array_merge([
            'Code'             => 'PO-INS-' . uniqid(),
            'Type_Pickup'      => 'pickup',
            'Coverage'         => 'DKI Jakarta - Jakarta Pusat',
            'Site'             => 'W-INSTOCK',
            'Package'          => 'Jerigen 18L Test',
            'Qty'              => 5,
            'Qty_Unit'         => 'PCS',
            'Price'            => 160000,
            'Price_Unit'       => 'PCS',
            'Package_Capacity' => 18,
            'Actual_Qty'       => 90,
            'WO_Id'            => $woId,
            'DO_Id'            => $doId,
            'Created_At'       => now(),
        ], $overrides));
    }

    private function group(int $warehouseId, int $containerId, int $poId, int $doId, array $overrides = []): array
    {
        return array_merge([
            'warehouse'         => $warehouseId,
            'warehouse_display' => 'Test Warehouse',
            'received_qty'      => 90,
            'avg_price'         => 1,
            'total'             => 90,
            'po_ids'            => [$poId],
            'do_ids'            => [$doId],
            'containers'        => [[
                'container_id' => $containerId,
                'qty'          => 90,
                'price'        => 1,
            ]],
        ], $overrides);
    }

    /** @test */
    public function submit_batch_recomputes_price_from_the_po_and_ignores_tampered_client_price(): void
    {
        $wh = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do);

        $response = $this->actingAs($this->admin())->postJson('/partner-uco-instock-submit-batch', [
            'received_date' => now()->toDateString(),
            'source'        => 'MITRA',
            'notes'         => '',
            'groups'        => json_encode([$this->group($wh, $c, $po, $do)]),
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $supply = DB::table('warehouse_partner_supplies')->orderBy('id', 'desc')->first();
        $this->assertEqualsWithDelta(8888.89, (float) $supply->price, 0.01);
        $this->assertEqualsWithDelta(800000, (float) $supply->total, 0.5);
        $this->assertNotEquals(1.0, (float) $supply->price, 'client-supplied price must be ignored');
    }

    /** @test */
    public function a_delivery_order_can_only_be_instocked_once(): void
    {
        $wh = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do);

        $payload = [
            'received_date' => now()->toDateString(),
            'source'        => 'MITRA',
            'notes'         => '',
            'groups'        => json_encode([$this->group($wh, $c, $po, $do)]),
        ];

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-submit-batch', $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull(DB::table('pickup_order')->where('ID', $po)->value('Instock_At'));

        $supplyCount = DB::table('warehouse_partner_supplies')->count();

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-submit-batch', $payload)
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame($supplyCount, DB::table('warehouse_partner_supplies')->count());
    }

    /** @test */
    public function preview_excludes_a_delivery_order_that_was_already_instocked(): void
    {
        $wh = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do);

        $this->actingAs($this->admin())->postJson('/partner-uco-instock-submit-batch', [
            'received_date' => now()->toDateString(),
            'source'        => 'MITRA',
            'groups'        => json_encode([$this->group($wh, $c, $po, $do)]),
        ])->assertOk();

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-preview', ['do_ids' => [$do]])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function deleting_the_instock_supply_restores_the_source_po(): void
    {
        $wh = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do);

        $this->actingAs($this->admin())->postJson('/partner-uco-instock-submit-batch', [
            'received_date' => now()->toDateString(),
            'source'        => 'MITRA',
            'groups'        => json_encode([$this->group($wh, $c, $po, $do)]),
        ])->assertOk();

        $supply = DB::table('warehouse_partner_supplies')->orderBy('id', 'desc')->first();

        $this->actingAs($this->admin())->postJson('/partner-uco-instock-supplies-delete', [
            'id' => $supply->id,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertNull(DB::table('pickup_order')->where('ID', $po)->value('Instock_At'));
    }

    /** @test */
    public function delivery_order_creation_rejects_a_po_that_is_already_used(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $existingDo = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $existingDo);

        $this->actingAs($this->admin())->postJson('/partner-delivery-order-create-submit', [
            'PO_Id'     => [$po],
            'DO_Status' => 'PROGRESS',
            'DO_Date'   => now()->toDateString(),
        ])->assertStatus(422)->assertJson(['status' => 'error']);
    }

    /** @test */
    public function consumed_work_orders_are_not_offered_when_creating_a_new_po(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do, ['Instock_At' => now()]);

        $response = $this->actingAs($this->admin())
            ->get('/partner-work-order-data?format=json&available_for_po=1')
            ->assertOk()
            ->json();

        $ids = array_column($response['data'], 'ID');
        $this->assertNotContains($wo, array_map('intval', $ids));
    }

    /** @test */
    public function consumed_delivery_orders_are_not_offered_for_instock(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do, ['Instock_At' => now()]);

        $response = $this->actingAs($this->admin())
            ->get('/partner-delivery-order-data?format=json&status=DONE&for_instock=1')
            ->assertOk()
            ->json();

        $ids = array_map('intval', array_column($response['data'], 'ID'));
        $this->assertNotContains($do, $ids);
    }

    /** @test */
    public function pos_that_already_have_a_delivery_order_are_not_offered_when_creating_one(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $po = $this->seedPickup($wo, $do);

        $response = $this->actingAs($this->admin())
            ->get('/partner-pickup-data?format=json&available_for_do=1')
            ->assertOk()
            ->json();

        $ids = array_map('intval', array_column($response['data'], 'ID'));
        $this->assertNotContains($po, $ids);
    }

    /** @test */
    public function creating_a_new_po_under_a_consumed_work_order_is_rejected(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do, ['Instock_At' => now()]);

        $this->actingAs($this->admin())->postJson('/partner-pickup-create-submit', [
            'WO_Id'       => $wo,
            'Type_Pickup' => 'pickup',
            'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
            'Site'        => 'W-INSTOCK',
            'Package'     => 'UCO (Liter)',
            'Qty'         => 10,
            'Price'       => 8000,
        ])->assertStatus(422)->assertJson(['status' => 'error']);
    }

    /** @test */
    public function work_order_with_any_open_po_is_not_offered_for_a_new_po(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do);

        $response = $this->actingAs($this->admin())
            ->get('/partner-work-order-data?format=json&available_for_po=1')
            ->assertOk()
            ->json();

        $ids = array_map('intval', array_column($response['data'], 'ID'));
        $this->assertNotContains($wo, $ids, 'a WO that already has a PO must not be reusable');
    }

    /** @test */
    public function creating_a_second_po_under_the_same_work_order_is_rejected(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do);

        $this->actingAs($this->admin())->postJson('/partner-pickup-create-submit', [
            'WO_Id'       => $wo,
            'Type_Pickup' => 'pickup',
            'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
            'Site'        => 'W-INSTOCK',
            'Package'     => 'UCO (Liter)',
            'Qty'         => 10,
            'Price'       => 8000,
        ])->assertStatus(422)->assertJson(['status' => 'error']);
    }

    /** @test */
    public function preview_rejects_a_delivery_order_when_any_of_its_pos_was_already_instocked(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do, ['Instock_At' => now()]);
        $openPo = $this->seedPickup($wo, $do);

        $this->actingAs($this->admin())
            ->postJson('/partner-uco-instock-preview', ['do_ids' => [$do]])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertNotNull(DB::table('pickup_order')->where('ID', $openPo)->value('Actual_Qty'));
    }

    /** @test */
    public function submit_rejects_a_delivery_order_when_any_of_its_pos_was_already_instocked(): void
    {
        $wh = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do, ['Instock_At' => now()]);
        $openPo = $this->seedPickup($wo, $do);

        $this->actingAs($this->admin())->postJson('/partner-uco-instock-submit-batch', [
            'received_date' => now()->toDateString(),
            'source'        => 'MITRA',
            'groups'        => json_encode([$this->group($wh, $c, $openPo, $do)]),
        ])->assertStatus(422)->assertJson(['success' => false]);
    }

    /** @test */
    public function submit_requires_every_po_in_a_delivery_order_to_be_consumed_together(): void
    {
        $wh = $this->seedWarehouse();
        $c = $this->seedContainer($wh);
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $poA = $this->seedPickup($wo, $do);
        $this->seedPickup($wo, $do);

        $this->actingAs($this->admin())->postJson('/partner-uco-instock-submit-batch', [
            'received_date' => now()->toDateString(),
            'source'        => 'MITRA',
            'groups'        => json_encode([$this->group($wh, $c, $poA, $do)]),
        ])->assertStatus(422)->assertJson(['success' => false]);
    }

    /** @test */
    public function partially_instocked_delivery_orders_are_not_offered_for_instock(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder();
        $this->seedPickup($wo, $do, ['Instock_At' => now()]);
        $this->seedPickup($wo, $do);

        $response = $this->actingAs($this->admin())
            ->get('/partner-delivery-order-data?format=json&status=DONE&for_instock=1')
            ->assertOk()
            ->json();

        $ids = array_map('intval', array_column($response['data'], 'ID'));
        $this->assertNotContains($do, $ids, 'a partially instocked DO must be treated as consumed');
    }

    /** @test */
    public function reconcile_command_backfills_legacy_supply_price_and_po_marker(): void
    {
        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));
        $do = $this->seedDeliveryOrder('DONE');
        $po = $this->seedPickup($wo, $do);

        $warehouseTaskId = DB::table('warehouse_partner')->where('id', $wh)->value('warehouse_task_id');

        $supplyId = DB::table('warehouse_partner_supplies')->insertGetId([
            'warehouse_task_id' => $warehouseTaskId,
            'received_date'     => now()->toDateString(),
            'pickup_type'       => 'PICKUP',
            'name_poo'          => 'Legacy POO',
            'source'            => 'MITRA',
            'coverage'          => 'Legacy',
            'received_qty'      => 90,
            'price'             => 8000,
            'total'             => 720000,
            'rejected_qty'      => 0,
            'created_date'      => now()->addMinute(),
            'created_by'        => 'legacy',
        ]);

        $this->artisan('uco:reconcile-instock', ['--apply' => true])->assertExitCode(0);

        $supply = DB::table('warehouse_partner_supplies')->where('id', $supplyId)->first();
        $this->assertEqualsWithDelta(8888.89, (float) $supply->price, 0.01);
        $this->assertEqualsWithDelta(800000, (float) $supply->total, 0.5);
        $this->assertNotNull($supply->Instock_Batch_Ref);

        $poRow = DB::table('pickup_order')->where('ID', $po)->first();
        $this->assertNotNull($poRow->Instock_At);
        $this->assertSame($supply->Instock_Batch_Ref, $poRow->Instock_Batch_Ref);
    }

    /** @test */
    public function pickup_creation_accepts_an_explicit_price_unit_of_liter_for_a_package(): void
    {
        DB::table('master_packaging')->insert([
            'Name'       => 'Jerigen Explicit Test',
            'Capacity'   => 18,
            'Unit'       => 'L',
            'Status'     => 'active',
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        $wh = $this->seedWarehouse();
        $wo = $this->seedWorkOrder($this->seedPoo($wh, 8000));

        $this->actingAs($this->admin())->post('/partner-pickup-create-submit', [
            'WO_Id'       => $wo,
            'Type_Pickup' => 'pickup',
            'Coverage'    => 'DKI Jakarta - Jakarta Pusat',
            'Site'        => 'W-INSTOCK',
            'Package'     => 'Jerigen Explicit Test',
            'Qty'         => 5,
            'Qty_Unit'    => 'PCS',
            'Price'       => 7000,
            'Price_Unit'  => 'L',
        ])->assertOk()->assertJson(['status' => 'success']);

        $po = DB::table('pickup_order')->latest('ID')->first();
        $this->assertSame('PCS', $po->Qty_Unit);
        $this->assertSame('L', $po->Price_Unit, 'the explicit price unit must be persisted');
        $this->assertEquals(18.0, (float) $po->Package_Capacity);
    }
}
