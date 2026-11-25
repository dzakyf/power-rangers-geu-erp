<?php

namespace Tests\Feature\CNS;

use App\PackingList;
use App\ShippingInstruction;
use App\User;
use App\VesselSchedule;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinalShippingInstructionCreatePlTest extends TestCase
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

    private function testUser(): User
    {
        $user = User::query()->orderBy('id')->first();
        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $user;
    }

    private function seedFsiAndSchedule(string $status): array
    {
        $user = $this->testUser();
        $uniq = uniqid();
        $orderNo = 'ORD/TEST/' . $uniq;
        $fsiNo = 'FSI/TEST/' . $uniq;

        $contractId = DB::table('contract')->insertGetId([
            'Contract_No' => 'CTR/TEST/' . $uniq,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        VesselSchedule::create([
            'Task_ID' => 'VS/TEST/' . $uniq,
            'Order_No' => $orderNo,
            'Contract_ID' => $contractId,
            'Contract_No' => 'CTR/TEST/' . $uniq,
            'Shipping_Method' => 'CONTAINER',
            'Qty' => '1 x 20 FT',
            'Qty1' => '1',
            'Qty2' => '20 FT',
            'Vessel' => 'TEST VESSEL',
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'Stuffing' => '2026-11-04',
            'Checking_BC' => '2026-11-04',
            'Closing_CY' => '2026-11-04',
            'ETD' => '2026-11-05',
            'ETA' => '2026-11-06',
            'Note' => 'Test schedule',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $si = ShippingInstruction::create([
            'SI_No' => 'SI/TEST/' . $uniq,
            'Final_SI_No' => $fsiNo,
            'SI_Date' => '2026-11-01',
            'Final_SI_Date' => '2026-11-02',
            'Order_No' => $orderNo,
            'Contract_ID' => $contractId,
            'Contract_No' => 'CTR/TEST/' . $uniq,
            'Qty' => '1 x 20 FT',
            'Qty1' => '1',
            'Qty2' => '20 FT',
            'Shipper' => 'PT TEST',
            'Consignee' => 'CONSIGNEE TEST',
            'Notify' => 'NOTIFY TEST',
            'Destination_Port' => 'TEST POD',
            'Delivery_Terms' => 'FOB',
            'Shipped_By' => 'VESSEL',
            'Voyage' => 'VOY-01',
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'HS_Code' => '123456',
            'Status' => $status,
            'Product_Name' => 'TEST PRODUCT',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        return [$user, $si];
    }

    /** @test */
    public function it_rejects_create_packing_list_when_fsi_status_is_not_approved(): void
    {
        $this->beginDatabaseTransactionOrSkip();
        [$user, $si] = $this->seedFsiAndSchedule('FINAL');

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->getJson('/cns-export-final-shipping-instruction-create-pl?Task=' . urlencode($si->Final_SI_No) . '&PL_Date=2026-11-19');

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);

        $this->assertNull(PackingList::where('Final_SI_No', $si->Final_SI_No)->first());
    }

    /** @test */
    public function it_creates_packing_list_when_fsi_status_is_approved_and_blocks_duplicate(): void
    {
        $this->beginDatabaseTransactionOrSkip();
        [$user, $si] = $this->seedFsiAndSchedule('APPROVED');

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->getJson('/cns-export-final-shipping-instruction-create-pl?Task=' . urlencode($si->Final_SI_No) . '&PL_Date=2026-11-19');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $pl = PackingList::where('Final_SI_No', $si->Final_SI_No)->first();
        $this->assertNotNull($pl);
        $this->assertSame('CREATED', $pl->Status);
        $this->assertSame('2026-11-19', $pl->PL_Date);

        $duplicate = $this->actingAs($user)
            ->withoutMiddleware()
            ->getJson('/cns-export-final-shipping-instruction-create-pl?Task=' . urlencode($si->Final_SI_No) . '&PL_Date=2026-11-19');

        $duplicate->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /** @test */
    public function it_allows_create_packing_list_when_previous_pl_is_not_active(): void
    {
        $this->beginDatabaseTransactionOrSkip();
        [$user, $si] = $this->seedFsiAndSchedule('APPROVED');

        PackingList::create([
            'Packing_List_No' => 'PL-CANCELLED-' . uniqid(),
            'Final_SI_No' => $si->Final_SI_No,
            'SI_No' => $si->SI_No,
            'Order_No' => $si->Order_No,
            'PL_Date' => '2026-11-18',
            'Status' => 'CANCEL',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->getJson('/cns-export-final-shipping-instruction-create-pl?Task=' . urlencode($si->Final_SI_No) . '&PL_Date=2026-11-19');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    /** @test */
    public function it_rejects_create_packing_list_when_task_is_empty(): void
    {
        $this->beginDatabaseTransactionOrSkip();
        $user = $this->testUser();

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->getJson('/cns-export-final-shipping-instruction-create-pl?Task=&PL_Date=2026-11-19');

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /** @test */
    public function fsi_view_validates_approved_status_and_uses_ajax_in_create_pl(): void
    {
        $content = file_get_contents(resource_path('views/cns/export/fsi.blade.php'));

        $this->assertStringContainsString("status.trim().toUpperCase() !== 'APPROVED'", $content);
        $this->assertStringContainsString('harus berstatus APPROVED sebelum membuat Packing List', $content);
        $this->assertStringContainsString('url: "cns-export-final-shipping-instruction-create-pl"', $content);
    }
}
