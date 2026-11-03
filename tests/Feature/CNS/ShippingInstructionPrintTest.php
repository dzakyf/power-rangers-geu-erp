<?php

namespace Tests\Feature\CNS;

use App\ShipmentInstruction;
use App\Services\CNS\ShippingInstructionPrintService;
use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShippingInstructionPrintTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User|null */
    private $user;

    /** @var array<int, string> */
    private $uploadedPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->uploadedPaths as $path) {
            $fullPath = public_path($path);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        parent::tearDown();
    }

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
    public function it_resolves_contract_fallbacks_when_si_fields_are_empty(): void
    {
        [$contract, $supplier] = $this->makeContractWithSupplier();
        $si = $this->makeSi($contract->ID, $contract->Contract_No, [
            'Delivery_Terms' => '',
            'Contract_No' => '',
            'Consignee' => '',
            'Notify' => '',
            'Destination_Letter' => '',
            'Qty' => '',
        ]);

        $print = app(ShippingInstructionPrintService::class)->resolve($si);

        $this->assertSame($contract->Contract_No, $print['values']['contract_no']);
        $this->assertSame('CIF', $print['values']['delivery_terms']);
        $this->assertStringContainsString('36.000 MT', $print['values']['quantity']);
        $this->assertSame('Test Buyer Company', $print['consignee']['name']);
        $this->assertSame('Test Buyer Company', $print['notify']['name']);
        $this->assertSame('Test Buyer Company', $print['destination']['name']);
    }

    /** @test */
    public function it_resolves_schedule_fallbacks_and_tbd_when_no_schedule_exists(): void
    {
        [$contract] = $this->makeContractWithSupplier();
        $si = $this->makeSi($contract->ID, $contract->Contract_No);
        $schedule = $this->makeSchedule($contract, 'PRINT-ORDER-1');
        $this->makeContainer($schedule, $si->SI_No);

        $print = app(ShippingInstructionPrintService::class)->resolve($si);

        $this->assertSame('TEST VESSEL', $print['values']['voyage']);
        $this->assertSame('TEST POL', $print['values']['pol']);
        $this->assertSame('TEST POD', $print['values']['pod']);
        $this->assertSame('November, 05 2026', $print['values']['etd']);

        $siWithoutSchedule = $this->makeSi(null, '', [
            'SI_No' => 'TEST-PRINT-NO-SCHEDULE-' . uniqid(),
        ]);
        $empty = app(ShippingInstructionPrintService::class)->resolve($siWithoutSchedule);

        $this->assertSame('TBD', $empty['values']['voyage']);
        $this->assertSame('TBD', $empty['values']['etd']);
        $this->assertSame('TBD', $empty['values']['pol']);
    }

    /** @test */
    public function it_collects_all_containers_from_the_resolved_vessel_schedule(): void
    {
        [$contract] = $this->makeContractWithSupplier();
        $si = $this->makeSi($contract->ID, $contract->Contract_No);
        $schedule = $this->makeSchedule($contract, 'PRINT-ORDER-2');
        $this->makeContainer($schedule, null);
        $this->makeContainer($schedule, null);

        $containers = app(ShippingInstructionPrintService::class)->containersFor($si);

        $this->assertCount(2, $containers);
        $this->assertSame(36000.0, (float) $containers->sum('Netto_KG'));
        $this->assertSame(40000.0, (float) $containers->sum('Gross_KG'));
    }

    /** @test */
    public function it_uses_peb_no_and_formats_missing_dates_as_tbd(): void
    {
        [$contract] = $this->makeContractWithSupplier();
        $si = $this->makeSi($contract->ID, $contract->Contract_No, [
            'PEB_No' => 'PEB-123',
            'PEB_Date' => null,
            'ETD' => null,
            'ETA' => null,
            'Stuffing' => null,
        ]);

        $print = app(ShippingInstructionPrintService::class)->resolve($si);

        $this->assertSame('PEB-123', $print['values']['peb_no']);
        $this->assertSame('TBD', $print['values']['peb_date']);
        $this->assertSame('TBD', $print['values']['etd']);
        $this->assertSame('November, 02 2026', $print['values']['si_date']);
    }

    /** @test */
    public function it_generates_a_pdf_when_the_si_has_no_shipper(): void
    {
        [$contract] = $this->makeContractWithSupplier();
        $si = $this->makeSi($contract->ID, $contract->Contract_No, [
            'Shipper' => '',
        ]);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-shipment-instruction-print?Task=' . urlencode($si->SI_No));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_marks_the_status_label_and_uses_final_si_date_for_final_records(): void
    {
        [$contract] = $this->makeContractWithSupplier();

        $draft = $this->makeSi($contract->ID, $contract->Contract_No, [
            'SI_No' => 'TEST-PRINT-DRAFT-' . uniqid(),
            'Status' => 'DRAFT',
        ]);
        $created = $this->makeSi($contract->ID, $contract->Contract_No, [
            'SI_No' => 'TEST-PRINT-CREATED-' . uniqid(),
            'Status' => 'CREATED',
        ]);
        $final = $this->makeSi($contract->ID, $contract->Contract_No, [
            'SI_No' => 'TEST-PRINT-FINAL-' . uniqid(),
            'Final_SI_No' => 'TEST-PRINT-FINAL-NO-' . uniqid(),
            'Final_SI_Date' => '2026-11-10',
            'Status' => 'FINAL',
        ]);

        $service = app(ShippingInstructionPrintService::class);

        $this->assertSame('DRAFT', $service->resolve($draft)['values']['status_label']);
        $this->assertSame('DRAFT', $service->resolve($created)['values']['status_label']);
        $this->assertSame('FINAL', $service->resolve($final)['values']['status_label']);
        $this->assertSame($final->Final_SI_No, $service->resolve($final)['values']['si_no']);
        $this->assertSame('November, 10 2026', $service->resolve($final)['values']['si_date']);
    }

    /** @test */
    public function it_generates_a_final_si_pdf_with_the_shared_template(): void
    {
        [$contract] = $this->makeContractWithSupplier();
        $final = $this->makeSi($contract->ID, $contract->Contract_No, [
            'SI_No' => 'TEST-PRINT-FINAL-PDF-' . uniqid(),
            'Final_SI_No' => 'TEST-PRINT-FINAL-PDF-NO-' . uniqid(),
            'Final_SI_Date' => '2026-11-10',
            'Status' => 'FINAL',
        ]);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-final-shipment-instruction-print?Task=' . urlencode($final->Final_SI_No));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    /** @test */
    public function it_embeds_local_images_as_data_uris_and_omits_missing_images(): void
    {
        [$contract, $supplierId] = $this->makeContractWithSupplier();
        $supplier = DB::table('supplier')->where('ID', $supplierId)->first();

        $header = $this->makeInterlacedPng('print-header-' . uniqid() . '.png');
        $logo = $this->makeInterlacedPng('print-logo-' . uniqid() . '.png');

        DB::table('supplier')->where('ID', $supplierId)->update([
            'Letterhead' => $header,
            'Logo' => $logo,
            'Footer' => 'filesupplier/missing-footer-' . uniqid() . '.png',
        ]);

        $si = $this->makeSi($contract->ID, $contract->Contract_No, [
            'Shipper' => $supplier->Supplier_ID,
        ]);

        $print = app(ShippingInstructionPrintService::class)->resolve($si);

        $this->assertStringStartsWith('data:image/png;base64,', $print['shipper']['letterhead_data_uri']);
        $this->assertStringStartsWith('data:image/png;base64,', $print['shipper']['logo_data_uri']);
        $this->assertNull($print['shipper']['footer_data_uri']);

        $response = $this->actingAs($this->testUser())
            ->get('/cns-export-shipment-instruction-print?Task=' . urlencode($si->SI_No));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    private function makeInterlacedPng(string $filename): string
    {
        $relative = 'filesupplier/' . $filename;
        $full = public_path($relative);

        if (!is_dir(dirname($full))) {
            mkdir(dirname($full), 0775, true);
        }

        $image = imagecreatetruecolor(40, 40);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imageinterlace($image, true);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        file_put_contents($full, $binary);
        $this->uploadedPaths[] = $relative;

        return $relative;
    }

    private function makeContractWithSupplier(): array
    {
        $user = $this->testUser();
        $code = 'TEST-BUYER-' . uniqid();

        $supplierId = DB::table('supplier')->insertGetId([
            'Location' => $user->Location,
            'Supplier_ID' => $code,
            'Company_Name' => 'Test Buyer Company',
            'Address' => 'Buyer Address',
            'City' => 'Jakarta',
            'Email' => 'buyer@example.test',
            'Emails' => json_encode(['buyer@example.test']),
            'Is_Consignee' => 1,
            'Is_Company' => 1,
            'Is_Supplier' => 0,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $contractNo = 'TEST-PRINT-CONTRACT-' . uniqid();
        $contractId = DB::table('cns_buyer_contracts')->insertGetId([
            'Contract_No' => $contractNo,
            'Supplier_ID' => $supplierId,
            'Buyer_Name' => 'Test Buyer Company',
            'Buyer_Address' => 'Buyer Address',
            'Buyer_Phone' => '021000000',
            'Buyer_Emails' => json_encode(['buyer@example.test']),
            'Quantity_MT' => 36,
            'Container_Count' => 2,
            'Container_Size' => '20 ft',
            'Incoterms' => 'CIF',
            'Price_Currency' => 'USD',
            'Price_Per_MT' => 1000,
            'Status' => 'ACTIVE_IN_PROGRESS',
            'Location' => $user->Location,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        return [DB::table('cns_buyer_contracts')->where('ID', $contractId)->first(), $supplierId];
    }

    private function makeSi($contractId, string $contractNo, array $overrides = []): ShipmentInstruction
    {
        $user = $this->testUser();

        $data = array_merge([
            'Order_No' => null,
            'SI_No' => 'TEST-PRINT-SI-' . uniqid(),
            'SI_Date' => '2026-11-02',
            'Qty' => '',
            'Qty1' => '',
            'Qty2' => '',
            'Shipper' => '',
            'Consignee' => '',
            'Notify' => '',
            'Destination_Letter' => '',
            'Delivery_Terms' => '',
            'Contract_No' => '',
            'Contract_ID' => $contractId,
            'Shipped_By' => '',
            'Voyage' => '',
            'ETD' => null,
            'ETA' => null,
            'Stuffing' => null,
            'PEB_No' => '',
            'PEB_Date' => null,
            'BL_No' => '',
            'POL' => '',
            'POD' => '',
            'HS_Code' => '',
            'Status' => 'DRAFT',
            'Product_Name' => '',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ], $overrides);

        return ShipmentInstruction::create($data);
    }

    private function makeSchedule($contract, string $orderNo): VesselSchedule
    {
        $user = $this->testUser();

        return VesselSchedule::create([
            'Task_ID' => 'VS/PRINT/' . uniqid(),
            'Order_No' => $orderNo,
            'Contract_ID' => $contract->ID,
            'Contract_No' => $contract->Contract_No,
            'Shipping_Method' => 'CONTAINER',
            'Qty' => '2 x 20 FT',
            'Qty1' => '2',
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
    }

    private function makeContainer(VesselSchedule $schedule, ?string $siNo = null): void
    {
        $user = $this->testUser();

        DB::table('vessel_schedule_dtls')->insert([
            'Task_ID' => $schedule->Task_ID,
            'Order_No' => $schedule->Order_No,
            'SI_No' => $siNo,
            'Container' => 'TESTU' . uniqid(),
            'Seal' => 'SEAL-' . uniqid(),
            'Time_From' => '08:00:00',
            'Time_To' => '17:00:00',
            'Date' => '2026-11-04',
            'Police_No' => 'B 1234 TEST',
            'Tare' => 2000,
            'Netto_KG' => 18000,
            'Netto_MT' => 18,
            'Gross_KG' => 20000,
            'Gross_MT' => 20,
            'Note' => '',
            'Status' => 'CLOSED',
            'Created_By' => $user->username,
            'Created_Date' => now(),
            'Updated_By' => $user->username,
            'Updated_Date' => now(),
        ]);
    }
}
