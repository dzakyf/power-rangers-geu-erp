<?php

namespace Tests\Feature\CNS;

use App\User;
use App\VesselSchedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class StuffingReportTest extends TestCase
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
    public function it_generates_the_final_stuffing_report_from_the_template(): void
    {
        $this->assertFileExists(public_path('template_final_stuffing.xlsx'));

        [$contract, $supplier] = $this->makeContractWithSupplier();
        $schedule = $this->makeSchedule($contract, $supplier);
        $this->makeContainer($schedule, 18000, 20000, 'REPORTU0000001');
        $this->makeContainer($schedule, 22000, 24000, 'REPORTU0000002');

        $response = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-report?task=' . $schedule->id);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $file = $response->getFile();
        $this->assertNotNull($file);
        $sheet = IOFactory::load($file->getPathname())->getActiveSheet();

        $this->assertSame('Buyer Report Company', $sheet->getCell('B4')->getValue());
        $this->assertSame('REPORT-CTR-001', $sheet->getCell('F4')->getValue());
        $this->assertSame('02 November 2026 - 07 November 2026', $sheet->getCell('B5')->getValue());
        $this->assertSame('DO-REPORT-001', $sheet->getCell('F5')->getValue());
        $this->assertSame('CIF', $sheet->getCell('B6')->getValue());
        $this->assertSame('BOOK-REPORT-001', $sheet->getCell('F6')->getValue());
        $this->assertSame('REPORT VESSEL', $sheet->getCell('B7')->getValue());
        $this->assertSame('05 November 2026', $sheet->getCell('F7')->getValue());
        $this->assertSame('REPORT POD', $sheet->getCell('B8')->getValue());

        $this->assertSame(36.0, (float) $sheet->getCell('B11')->getValue());
        $this->assertSame(40000.0, (float) $sheet->getCell('D11')->getValue());
        $this->assertSame(40.0, (float) $sheet->getCell('F11')->getValue());
        $this->assertSame(2, (int) $sheet->getCell('H11')->getValue());
        $this->assertSame(44000.0, (float) $sheet->getCell('B12')->getValue());
        $this->assertSame(44.0, (float) $sheet->getCell('D12')->getValue());
        $this->assertSame(4.0, (float) $sheet->getCell('F12')->getValue());
        $this->assertSame('SESUAI', $sheet->getCell('H12')->getValue());

        $this->assertSame('Sudah', $sheet->getCell('A16')->getValue());
        $this->assertSame('Sudah', $sheet->getCell('B16')->getValue());
        $this->assertSame('Belum', $sheet->getCell('C16')->getValue());
        $this->assertSame('Sudah', $sheet->getCell('D16')->getValue());
        $this->assertSame('Belum', $sheet->getCell('E16')->getValue());
        $this->assertSame('admin', $sheet->getCell('F16')->getValue());
        $this->assertSame('Schedule report note', $sheet->getCell('H16')->getValue());
        $this->assertSame('DO report note', $sheet->getCell('A19')->getValue());
    }

    /** @test */
    public function it_uses_tbd_when_do_data_is_missing(): void
    {
        [$contract, $supplier] = $this->makeContractWithSupplier();
        $schedule = $this->makeSchedule($contract, $supplier, [
            'DO_No' => null,
            'DO_Status' => null,
            'DO_Note' => null,
        ]);
        $this->makeContainer($schedule, 18000, 20000, 'REPORTU0000003');

        $response = $this->actingAs($this->testUser())
            ->get('/cns-stuffing-report?task=' . $schedule->id);

        $response->assertOk();
        $file = $response->getFile();
        $this->assertNotNull($file);
        $sheet = IOFactory::load($file->getPathname())->getActiveSheet();

        $this->assertSame('TBD', $sheet->getCell('F5')->getValue());
        $this->assertSame('TBD', $sheet->getCell('H12')->getValue());
    }

    private function makeContractWithSupplier(): array
    {
        $user = $this->testUser();
        $supplierCode = 'REPORT-BUYER-' . uniqid();

        $supplierId = DB::table('supplier')->insertGetId([
            'Location' => $user->Location,
            'Supplier_ID' => $supplierCode,
            'Company_Name' => 'Buyer Report Company',
            'Address' => 'Buyer Report Address',
            'City' => 'Jakarta',
            'Is_Consignee' => 1,
            'Is_Company' => 1,
            'Is_Supplier' => 0,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        $contractNo = 'REPORT-CTR-001';
        $contractId = DB::table('cns_buyer_contracts')->insertGetId([
            'Contract_No' => $contractNo,
            'Supplier_ID' => $supplierId,
            'Buyer_Name' => 'Buyer Report Company',
            'Buyer_Address' => 'Buyer Report Address',
            'Buyer_Phone' => '021000000',
            'Buyer_Emails' => json_encode(['buyer@example.test']),
            'Quantity_MT' => 36,
            'Container_Count' => 2,
            'Container_Size' => '20 ft',
            'Loading_Start' => '2026-11-02',
            'Loading_End' => '2026-11-07',
            'Incoterms' => 'CIF',
            'Price_Currency' => 'USD',
            'Price_Per_MT' => 1000,
            'Status' => 'ACTIVE_IN_PROGRESS',
            'Location' => $user->Location,
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ]);

        return [DB::table('cns_buyer_contracts')->where('ID', $contractId)->first(), $supplierCode];
    }

    private function makeSchedule($contract, string $supplierCode, array $overrides = []): VesselSchedule
    {
        $user = $this->testUser();

        $data = array_merge([
            'Task_ID' => 'VS/REPORT/' . uniqid(),
            'Order_No' => 'REPORT-BOOKING-' . uniqid(),
            'Contract_ID' => $contract->ID,
            'Contract_No' => $contract->Contract_No,
            'Incoterms' => $contract->Incoterms,
            'Shipping_Method' => 'CONTAINER',
            'Booking_No' => 'BOOK-REPORT-001',
            'Qty' => '2 x 20 FT',
            'Qty1' => '2',
            'Qty2' => '20 FT',
            'Vessel' => 'REPORT VESSEL',
            'POL' => 'REPORT POL',
            'POD' => 'REPORT POD',
            'Stuffing' => '2026-11-04',
            'Checking_BC' => '2026-11-04',
            'Closing_CY' => '2026-11-04',
            'ETD' => '2026-11-05',
            'ETA' => '2026-11-06',
            'Note' => 'Schedule report note',
            'DO_No' => 'DO-REPORT-001',
            'DO_Status' => 'SESUAI',
            'DO_Note' => 'DO report note',
            'Checklist_Pickup_Container' => 1,
            'Checklist_Stuffing_Process' => 1,
            'Checklist_Rilis_NPE' => 0,
            'Checklist_Get_In_CY' => 1,
            'Checklist_Done_Kapal_Berangkat' => 0,
            'Checklist_Updated_By' => $user->username,
            'Checklist_Updated_Date' => '2026-11-03 10:00:00',
            'Created_By' => $user->username,
            'Created_Date' => now(),
        ], $overrides);

        return VesselSchedule::create($data);
    }

    private function makeContainer(VesselSchedule $schedule, float $netto, float $gross, string $container): void
    {
        $user = $this->testUser();

        DB::table('vessel_schedule_dtls')->insert([
            'Task_ID' => $schedule->Task_ID,
            'Order_No' => $schedule->Order_No,
            'SI_No' => null,
            'Container' => $container,
            'Seal' => 'SEAL-' . uniqid(),
            'Time_From' => '08:00:00',
            'Time_To' => '17:00:00',
            'Date' => '2026-11-04',
            'Police_No' => 'B 1234 TEST',
            'Tare' => $gross - $netto,
            'Netto_KG' => $netto,
            'Netto_MT' => round($netto / 1000, 2),
            'Gross_KG' => $gross,
            'Gross_MT' => round($gross / 1000, 2),
            'Note' => '',
            'Status' => 'CLOSED',
            'Created_By' => $user->username,
            'Created_Date' => now(),
            'Updated_By' => $user->username,
            'Updated_Date' => now(),
        ]);
    }
}
