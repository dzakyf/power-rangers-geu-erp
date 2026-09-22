<?php

namespace Tests\Feature\UcoLogistics;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A Surat Jalan (delivery order) with DO_Status = DONE must not be editable.
 */
class DeliveryOrderLockTest extends TestCase
{
    use DatabaseTransactions;

    private function admin()
    {
        return \App\User::where('username', 'admin')->firstOrFail();
    }

    private function seedDeliveryOrder(string $status): int
    {
        return DB::table('delivery_order')->insertGetId([
            'Code'       => 'DO-LOCK-'.uniqid(),
            'DO_Status'  => $status,
            'DO_Date'    => now()->toDateString(),
            'Created_At' => now(),
        ]);
    }

    private function seedPickupOrder(int $doId, float $actualQty = 5): int
    {
        return DB::table('pickup_order')->insertGetId([
            'Code'            => 'PO-LOCK-'.uniqid(),
            'DO_Id'           => $doId,
            'Qty'             => 10,
            'Actual_Qty'      => $actualQty,
            'Allocation_Type' => 'MANUAL',
            'Created_At'      => now(),
        ]);
    }

    /** @test */
    public function done_delivery_order_cannot_be_updated_via_submit()
    {
        $do = $this->seedDeliveryOrder('DONE');
        $po = $this->seedPickupOrder($do, 5);

        $this->actingAs($this->admin())
            ->postJson('/partner-delivery-order-update-submit', [
                'PO_Id'      => $po,
                'Actual_Qty' => 999,
                'DO_Status'  => 'PROGRESS',
            ])
            ->assertStatus(403)
            ->assertJson(['status' => 'error']);

        $this->assertEquals(5, (float) DB::table('pickup_order')->where('ID', $po)->value('Actual_Qty'));
        $this->assertEquals('DONE', DB::table('delivery_order')->where('ID', $do)->value('DO_Status'));
    }

    /** @test */
    public function progress_delivery_order_can_be_updated_via_submit()
    {
        $do = $this->seedDeliveryOrder('PROGRESS');
        $po = $this->seedPickupOrder($do, 5);

        $this->actingAs($this->admin())
            ->postJson('/partner-delivery-order-update-submit', [
                'PO_Id'      => $po,
                'Actual_Qty' => 123,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals(123, (float) DB::table('pickup_order')->where('ID', $po)->value('Actual_Qty'));
    }

    /** @test */
    public function done_delivery_order_receipt_is_blocked()
    {
        $do = $this->seedDeliveryOrder('DONE');

        $this->actingAs($this->admin())
            ->postJson('/partner-delivery-order-update-receipt', ['DO_Id' => $do, 'Qty' => 999])
            ->assertStatus(403)
            ->assertJson(['status' => 'error']);

        $this->assertEquals('DONE', DB::table('delivery_order')->where('ID', $do)->value('DO_Status'));
        $this->assertNull(DB::table('delivery_order')->where('ID', $do)->value('Actual_Qty'));
    }

    /** @test */
    public function progress_delivery_order_receipt_completes_it()
    {
        $do = $this->seedDeliveryOrder('PROGRESS');

        $this->actingAs($this->admin())
            ->postJson('/partner-delivery-order-update-receipt', ['DO_Id' => $do, 'Qty' => 50])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals('DONE', DB::table('delivery_order')->where('ID', $do)->value('DO_Status'));
        $this->assertEquals(50, (float) DB::table('delivery_order')->where('ID', $do)->value('Actual_Qty'));
    }

    /** @test */
    public function done_delivery_order_note_is_blocked()
    {
        $do = $this->seedDeliveryOrder('DONE');

        $this->actingAs($this->admin())
            ->postJson('/partner-delivery-order-update-note', ['id' => $do, 'Actual_Pickup_Cost' => 999])
            ->assertStatus(403)
            ->assertJson(['status' => 'error']);

        $this->assertNull(DB::table('delivery_order')->where('ID', $do)->value('Actual_Pickup_Cost'));
    }

    /** @test */
    public function update_page_is_read_only_for_done_delivery_order()
    {
        $do = $this->seedDeliveryOrder('DONE');
        $po = $this->seedPickupOrder($do, 5);

        $html = $this->actingAs($this->admin())
            ->get('/partner-delivery-order-update?po_id='.$po)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('read-only', $html);
    }
}
