<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselScheduleDtl;
use App\VesselScheduleLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShipmentInstructionDraftTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User|null */
    private $user;

    private function testUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::query()->orderBy('id')->first();

        if (!$user) {
            $this->markTestSkipped('No user exists in the test database.');
        }

        return $this->user = $user;
    }

    /** @test */
    public function it_creates_a_header_only_draft_si_from_an_active_contract(): void
    {
        $user = $this->testUser();
        $location = (string) ($user->Location ?? '');
        $supplierCode = 'TST-' . uniqid();
        $supplierId = DB::table('supplier')->insertGetId([
            'Location' => $location !== '' ? $location : 'TEST',
            'Supplier_ID' => $supplierCode,
            'Company_Name' => 'Test Supplier ' . uniqid(),
            'Created_Date' => now(),
        ]);

        $contractNo = 'TEST-SI-CONTRACT-' . uniqid();
        $contractId = DB::table('cns_buyer_contracts')->insertGetId([
            'Contract_No' => $contractNo,
            'Supplier_ID' => $supplierId,
            'Buyer_Name' => 'Test Buyer',
            'Buyer_Address' => 'Test Address',
            'Buyer_Phone' => '000',
            'Incoterms' => 'FOB',
            'Status' => 'ACTIVE_IN_PROGRESS',
            'Location' => $location !== '' ? $location : null,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $siNo = 'TEST-SI-' . uniqid();
        $response = $this->actingAs($user)->post('/cns-export-shipment-instruction-new-submit', [
            'Contract_ID' => $contractId,
            'SI_No' => $siNo,
            'SI_Date' => date('Y-m-d'),
            'Contract_No' => $contractNo,
            'Delivery_Terms' => 'FOB',
            'Consignee' => $supplierCode,
            'Shipper' => $supplierCode,
            'Notify' => $supplierCode,
            'Destination_Port' => $supplierCode,
            'PEB_No' => '',
            'PEB_Date' => '',
            'BL_No' => '',
            'Product_Name' => 'Test Product',
            'HS_Code' => '15180090',
        ]);

        $response->assertOk()->assertJson(['status' => 'success']);

        $si = DB::table('shipment_instructions')->where('SI_No', $siNo)->first();

        $this->assertNotNull($si);
        $this->assertSame('DRAFT', $si->Status);
        $this->assertSame((string) $contractId, (string) $si->Contract_ID);
        $this->assertNull($si->Order_No);
    }

    /** @test */
    public function it_links_a_container_schedule_and_marks_the_si_created(): void
    {
        $user = $this->testUser();
        $location = (string) ($user->Location ?? '');
        $supplierCode = 'TST-' . uniqid();
        $supplierId = DB::table('supplier')->insertGetId([
            'Location' => $location !== '' ? $location : 'TEST',
            'Supplier_ID' => $supplierCode,
            'Company_Name' => 'Test Supplier ' . uniqid(),
            'Created_Date' => now(),
        ]);

        $contractNo = 'TEST-SI-LINK-' . uniqid();
        $contractId = DB::table('cns_buyer_contracts')->insertGetId([
            'Contract_No' => $contractNo,
            'Supplier_ID' => $supplierId,
            'Buyer_Name' => 'Test Buyer',
            'Buyer_Address' => 'Test Address',
            'Buyer_Phone' => '000',
            'Incoterms' => 'FOB',
            'Status' => 'ACTIVE_IN_PROGRESS',
            'Location' => $location !== '' ? $location : null,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $orderNo = 'TEST-SI-ORDER-' . uniqid();
        $taskId = 'TEST-SI-TASK-' . uniqid();
        $vsId = DB::table('vessel_schedules')->insertGetId([
            'Task_ID' => $taskId,
            'Order_No' => $orderNo,
            'Contract_ID' => $contractId,
            'Contract_No' => $contractNo,
            'Shipping_Method' => 'CONTAINER',
            'Qty' => '2 x 20 FT',
            'Qty1' => '2',
            'Qty2' => '20 FT',
            'Vessel' => 'TEST VESSEL',
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'Note' => '',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $containerOne = $this->makeContainer($taskId, $orderNo, $user);
        $containerTwo = $this->makeContainer($taskId, $orderNo, $user);

        $siId = $this->makeDraftSi($contractId, $contractNo, $supplierCode, $user);

        $response = $this->actingAs($user)->post('/cns-export-shipment-instruction-detail-submit', [
            'id' => $siId,
            'SI_Date' => date('Y-m-d'),
            'Order_No' => $orderNo,
            'Qty1' => '2',
            'Qty2' => '20 FT',
            'Voyage' => 'TEST VESSEL',
            'ETD' => date('Y-m-d'),
            'ETA' => date('Y-m-d'),
            'Stuffing' => date('Y-m-d'),
            'POL' => 'TEST POL',
            'POD' => 'TEST POD',
            'Contract_No' => $contractNo,
            'Delivery_Terms' => 'FOB',
            'Consignee' => $supplierCode,
            'Shipper' => $supplierCode,
            'Notify' => $supplierCode,
            'Destination_Port' => $supplierCode,
            'PEB_No' => '',
            'PEB_Date' => '',
            'BL_No' => '',
            'Product_Name' => 'Test Product',
            'HS_Code' => '15180090',
            'Container1' => $containerOne,
            'Container2' => $containerTwo,
        ]);

        $response->assertOk()->assertJson(['success' => true, 'status' => 'CREATED']);

        $si = DB::table('shipment_instructions')->where('id', $siId)->first();
        $this->assertSame('CREATED', $si->Status);
        $this->assertSame($orderNo, $si->Order_No);

        $assigned = VesselScheduleDtl::whereIn('ID', [$containerOne, $containerTwo])
            ->pluck('SI_No')
            ->all();
        $this->assertSame([$si->SI_No, $si->SI_No], $assigned);
        $this->assertSame(1, VesselScheduleLog::where('Last_ID', $vsId)->count());

        $secondSiId = $this->makeDraftSi($contractId, $contractNo, $supplierCode, $user, 'SECOND');

        $blocked = $this->actingAs($user)->post('/cns-export-shipment-instruction-detail-submit', [
            'id' => $secondSiId,
            'SI_Date' => date('Y-m-d'),
            'Order_No' => $orderNo,
            'Qty1' => '2',
            'Qty2' => '20 FT',
            'Container1' => $containerOne,
            'Container2' => $containerTwo,
        ]);

        $blocked->assertStatus(422);
    }

    private function makeContainer(string $taskId, string $orderNo, User $user): int
    {
        return DB::table('vessel_schedule_dtls')->insertGetId([
            'Task_ID' => $taskId,
            'Order_No' => $orderNo,
            'Container' => 'TESTU' . uniqid(),
            'Seal' => 'SEAL-' . uniqid(),
            'Time_From' => '08:00:00',
            'Time_To' => '17:00:00',
            'Date' => date('Y-m-d'),
            'Police_No' => 'TEST-POLICE',
            'Tare' => 1000,
            'Netto_KG' => 20000,
            'Netto_MT' => 20,
            'Gross_KG' => 21000,
            'Gross_MT' => 21,
            'Note' => '',
            'Status' => 'CREATED',
            'Created_By' => $user->username,
            'Created_Date' => now(),
            'Updated_By' => $user->username,
            'Updated_Date' => now(),
        ]);
    }

    private function makeDraftSi(int $contractId, string $contractNo, string $supplierCode, User $user, string $suffix = ''): int
    {
        return DB::table('shipment_instructions')->insertGetId([
            'Order_No' => null,
            'SI_No' => 'TEST-SI-DRAFT-' . $suffix . uniqid(),
            'SI_Date' => date('Y-m-d'),
            'Qty' => '',
            'Qty1' => '',
            'Qty2' => '',
            'Shipper' => $supplierCode,
            'Consignee' => $supplierCode,
            'Notify' => $supplierCode,
            'Destination_Port' => $supplierCode,
            'Delivery_Terms' => 'FOB',
            'Contract_No' => $contractNo,
            'Contract_ID' => $contractId,
            'Shipped_By' => '',
            'Voyage' => '',
            'PEB_No' => '',
            'BL_No' => '',
            'POL' => '',
            'POD' => '',
            'HS_Code' => '15180090',
            'Status' => 'DRAFT',
            'Product_Name' => 'Test Product',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);
    }
}
