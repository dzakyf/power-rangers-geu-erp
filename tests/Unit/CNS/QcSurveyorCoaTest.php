<?php

namespace Tests\Unit\CNS;

use App\BuyerContract;
use App\BuyerContractBooking;
use App\Http\Controllers\CNS\ExportChecklistController;
use App\Http\Controllers\CNS\QualityController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class QcSurveyorCoaTest extends TestCase
{
    public function test_insert_surveyor_requires_booking_id(): void
    {
        $request = new Request([
            'Created_At'  => '2026-11-25',
            'QC_Type'     => 'SURVEYOR',
            'QC_Category' => 'INTERNAL',
            'Booking_ID'  => null,
        ]);

        $response = (new QualityController())->insertQualityControl($request);

        $this->assertSame(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('error', $data['status']);
        $this->assertStringContainsString('Booking Confirmation wajib dipilih', $data['message']);
    }

    public function test_insert_surveyor_requires_attachment_on_create(): void
    {
        $request = new Request([
            'Created_At' => '2026-11-25',
            'QC_Type'    => 'SURVEYOR',
            'Booking_ID' => 10,
        ]);

        $response = (new QualityController())->insertQualityControl($request);

        $this->assertSame(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('error', $data['status']);
        $this->assertStringContainsString('File COA Surveyor wajib diupload', $data['message']);
    }

    public function test_insert_surveyor_enforces_one_coa_per_booking(): void
    {
        $dupQuery = Mockery::mock();
        $dupQuery->shouldReceive('where')->with('QC_Type', 'SURVEYOR')->once()->andReturnSelf();
        $dupQuery->shouldReceive('where')->with('Booking_ID', 10)->once()->andReturnSelf();
        $dupQuery->shouldReceive('where')->with('Is_Deleted', 0)->once()->andReturnSelf();
        $dupQuery->shouldReceive('exists')->once()->andReturn(true);

        DB::shouldReceive('table')->with('cns_quality_controls')->once()->andReturn($dupQuery);

        $file = UploadedFile::fake()->create('coa.pdf', 10, 'application/pdf');
        $request = Request::create('/cns-quality-control-insert', 'POST', [
            'Created_At' => '2026-11-25',
            'QC_Type'    => 'SURVEYOR',
            'Booking_ID' => 10,
        ], [], ['Attachment' => $file]);

        $response = (new QualityController())->insertQualityControl($request);

        $this->assertSame(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('error', $data['status']);
        $this->assertStringContainsString('1 BC = 1 COA Surveyor', $data['message']);
    }

    public function test_insert_surveyor_forces_external_and_saves_average_directly(): void
    {
        $dupQuery = Mockery::mock();
        $dupQuery->shouldReceive('where')->with('QC_Type', 'SURVEYOR')->once()->andReturnSelf();
        $dupQuery->shouldReceive('where')->with('Booking_ID', 10)->once()->andReturnSelf();
        $dupQuery->shouldReceive('where')->with('Is_Deleted', 0)->once()->andReturnSelf();
        $dupQuery->shouldReceive('exists')->once()->andReturn(false);

        $bookingQuery = Mockery::mock();
        $bookingQuery->shouldReceive('where')->with('ID', 10)->once()->andReturnSelf();
        $bookingQuery->shouldReceive('first')->once()->andReturn((object) ['ID' => 10, 'Contract_ID' => 55]);

        $seqQuery = Mockery::mock();
        $seqQuery->shouldReceive('whereDate')->with('Inspection_Date', '2026-11-25')->once()->andReturnSelf();
        $seqQuery->shouldReceive('whereNotNull')->with('Kode_QC')->once()->andReturnSelf();
        $seqQuery->shouldReceive('count')->once()->andReturn(0);

        $lookupHeaderQuery = Mockery::mock();
        $lookupHeaderQuery->shouldReceive('where')->with('Kode_QC', 'QC/SRV/EXT/20261125/001')->once()->andReturnSelf();
        $lookupHeaderQuery->shouldReceive('first')->once()->andReturn(null);

        $capturedHeader = null;
        $insertHeaderQuery = Mockery::mock();
        $insertHeaderQuery->shouldReceive('insertGetId')
            ->once()
            ->with(Mockery::on(function ($payload) use (&$capturedHeader) {
                $capturedHeader = $payload;
                return true;
            }))
            ->andReturn(99);

        $capturedCondition = null;
        $capturedDetail = null;
        $detailQuery = Mockery::mock();
        $detailQuery->shouldReceive('updateOrInsert')
            ->once()
            ->with(
                Mockery::on(function ($cond) use (&$capturedCondition) {
                    $capturedCondition = $cond;
                    return true;
                }),
                Mockery::on(function ($vals) use (&$capturedDetail) {
                    $capturedDetail = $vals;
                    return true;
                })
            )
            ->andReturn(true);

        DB::shouldReceive('table')
            ->with('cns_quality_controls')
            ->times(4)
            ->andReturn($dupQuery, $seqQuery, $lookupHeaderQuery, $insertHeaderQuery);
        DB::shouldReceive('table')
            ->with('cns_buyer_contract_bookings')
            ->once()
            ->andReturn($bookingQuery);
        DB::shouldReceive('table')
            ->with('cns_quality_control_details')
            ->once()
            ->andReturn($detailQuery);
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $file = UploadedFile::fake()->create('coa-surveyor.pdf', 10, 'application/pdf');
        $request = Request::create('/cns-quality-control-insert', 'POST', [
            'Created_At'          => '2026-11-25',
            'QC_Type'             => 'SURVEYOR',
            'QC_Category'         => 'INTERNAL',
            'QC_Method'           => 'AOCS Ca 5a-40',
            'Booking_ID'          => 10,
            'avg_FFA_As_Palmitic' => '3.456',
            'avg_Moisture'        => '0.50',
            'avg_IV'              => '57.25',
            'avg_Sulphur'         => '-',
        ], [], ['Attachment' => $file]);

        try {
            $response = (new QualityController())->insertQualityControl($request);

            $this->assertSame(200, $response->getStatusCode());
            $this->assertNotNull($capturedHeader);
            $this->assertSame('SURVEYOR', $capturedHeader['QC_Type']);
            $this->assertSame('EXTERNAL', $capturedHeader['QC_Category']);
            $this->assertSame('AOCS Ca 5a-40', $capturedHeader['QC_Method']);
            $this->assertSame(55, $capturedHeader['Contract_ID']);
            $this->assertSame(10, $capturedHeader['Booking_ID']);
            $this->assertSame('QC/SRV/EXT/20261125/001', $capturedHeader['Kode_QC']);
            $this->assertRegExp('#^filevendor/\d{4}/\d{2}/[0-9a-f-]+\.pdf$#', $capturedHeader['Attachment_Path']);

            $this->assertSame([
                'Quality_Control_ID' => 99,
                'Sample_Level'       => 'AVERAGE',
            ], $capturedCondition);
            $this->assertSame(3.46, $capturedDetail['FFA_As_Palmitic']);
            $this->assertSame(0.50, $capturedDetail['Moisture']);
            $this->assertSame(57.25, $capturedDetail['IV']);
            $this->assertNull($capturedDetail['Sulphur']);
        } finally {
            if (!empty($capturedHeader['Attachment_Path'])) {
                $uploadedFile = public_path($capturedHeader['Attachment_Path']);
                if (file_exists($uploadedFile)) {
                    @unlink($uploadedFile);
                }
            }
        }
    }

    public function test_export_checklist_queries_cns_quality_controls_and_maps_coa_surveyor(): void
    {
        $mockSchema = Mockery::mock();
        $mockSchema->shouldReceive('hasTable')->with('cns_customs_clearances')->once()->andReturn(false);
        $mockSchema->shouldReceive('hasTable')->with('packing_lists')->once()->andReturn(false);
        $mockSchema->shouldReceive('hasTable')->with('delivery_notes')->once()->andReturn(false);
        $mockSchema->shouldReceive('hasTable')->with('cns_quality_controls')->once()->andReturn(true);

        $mockConn = Mockery::mock();
        $mockConn->shouldReceive('getSchemaBuilder')->times(4)->andReturn($mockSchema);

        $qcQuery = Mockery::mock();
        $qcQuery->shouldReceive('where')->with('QC_Type', 'SURVEYOR')->once()->andReturnSelf();
        $qcQuery->shouldReceive('where')->with('Is_Deleted', 0)->once()->andReturnSelf();
        $qcQuery->shouldReceive('whereIn')->with('Booking_ID', [101])->once()->andReturnSelf();
        $qcQuery->shouldReceive('whereNotNull')->with('Attachment_Path')->once()->andReturnSelf();
        $qcQuery->shouldReceive('where')->with('Attachment_Path', '!=', '')->once()->andReturnSelf();
        $qcQuery->shouldReceive('orderByDesc')->with('id')->once()->andReturnSelf();
        $qcQuery->shouldReceive('get')->once()->andReturn(collect([
            (object) [
                'id'              => 12,
                'Booking_ID'      => 101,
                'Kode_QC'         => 'QC/SRV/EXT/20261125/001',
                'Inspection_Date' => '2026-11-25',
                'Attachment_Path' => 'filevendor/2026/11/coa-101.pdf',
                'QC_Type'         => 'SURVEYOR',
                'Is_Deleted'      => 0,
            ],
        ]));

        DB::shouldReceive('connection')->times(4)->andReturn($mockConn);
        DB::shouldReceive('table')->with('cns_quality_controls')->once()->andReturn($qcQuery);

        $booking = (new BuyerContractBooking())->forceFill(['ID' => 101]);
        $booking->setRelation('contract', null);

        $stages = (new ExportChecklistController())->buildProgressData(collect([$booking]))[101]['stages'];

        $this->assertTrue($stages['coa_surveyor']['exists']);
        $this->assertSame('QC/SRV/EXT/20261125/001', $stages['coa_surveyor']['doc_no']);
        $this->assertSame('25 Nov 2026', $stages['coa_surveyor']['date']);
        $this->assertSame('COA Surveyor Tersedia', $stages['coa_surveyor']['note']);
        $this->assertSame(url('filevendor/2026/11/coa-101.pdf'), $stages['coa_surveyor']['preview_url']);
    }

    public function test_export_checklist_marks_coa_missing_when_absent_deleted_or_without_attachment(): void
    {
        $bookings = collect([
            $this->makeBooking(1, 'BC-01', 'CTR-01'),
            $this->makeBooking(2, 'BC-02', 'CTR-01'),
            $this->makeBooking(3, 'BC-03', 'CTR-01'),
            $this->makeBooking(4, 'BC-04', 'CTR-01'),
        ]);

        $coaSurveyors = collect([
            (object) [
                'id'              => 1,
                'Booking_ID'      => 1,
                'Kode_QC'         => 'QC/SRV/EXT/20261125/001',
                'Inspection_Date' => '2026-11-25',
                'Attachment_Path' => 'filevendor/2026/11/coa-bc1.pdf',
                'QC_Type'         => 'SURVEYOR',
                'Is_Deleted'      => 0,
            ],
            (object) [
                'id'              => 2,
                'Booking_ID'      => 2,
                'Kode_QC'         => 'QC/SRV/EXT/20261125/002',
                'Inspection_Date' => '2026-11-25',
                'Attachment_Path' => 'filevendor/2026/11/coa-deleted.pdf',
                'QC_Type'         => 'SURVEYOR',
                'Is_Deleted'      => 1,
            ],
            (object) [
                'id'              => 3,
                'Booking_ID'      => 3,
                'Kode_QC'         => 'QC/OUT/EXT/20261125/003',
                'Inspection_Date' => '2026-11-25',
                'Attachment_Path' => 'filevendor/2026/11/outbound.pdf',
                'QC_Type'         => 'OUTBOUND',
                'Is_Deleted'      => 0,
            ],
            (object) [
                'id'              => 4,
                'Booking_ID'      => 3,
                'Kode_QC'         => 'QC/SRV/EXT/20261125/004',
                'Inspection_Date' => '2026-11-25',
                'Attachment_Path' => '',
                'QC_Type'         => 'SURVEYOR',
                'Is_Deleted'      => 0,
            ],
        ]);

        $controller = new class($coaSurveyors) extends ExportChecklistController {
            private $coaSurveyors;

            public function __construct(Collection $coaSurveyors)
            {
                $this->coaSurveyors = $coaSurveyors;
            }

            protected function fetchProgressSources(Collection $bookings): array
            {
                return [
                    'coaSurveyors' => $this->coaSurveyors,
                ];
            }
        };

        $data = $controller->buildProgressData($bookings);

        // BC-01: valid SURVEYOR COA -> Lengkap + preview_url
        $this->assertTrue($data[1]['stages']['coa_surveyor']['exists']);
        $this->assertSame('QC/SRV/EXT/20261125/001', $data[1]['stages']['coa_surveyor']['doc_no']);
        $this->assertSame('COA Surveyor Tersedia', $data[1]['stages']['coa_surveyor']['note']);
        $this->assertSame(url('filevendor/2026/11/coa-bc1.pdf'), $data[1]['stages']['coa_surveyor']['preview_url']);

        // BC-02: Is_Deleted = 1 -> false
        $this->assertFalse($data[2]['stages']['coa_surveyor']['exists']);
        $this->assertSame('-', $data[2]['stages']['coa_surveyor']['doc_no']);
        $this->assertSame('Menunggu QC Surveyor', $data[2]['stages']['coa_surveyor']['note']);
        $this->assertNull($data[2]['stages']['coa_surveyor']['preview_url']);

        // BC-03: non-SURVEYOR or empty Attachment_Path -> false
        $this->assertFalse($data[3]['stages']['coa_surveyor']['exists']);
        $this->assertNull($data[3]['stages']['coa_surveyor']['preview_url']);

        // BC-04: no record at all -> false
        $this->assertFalse($data[4]['stages']['coa_surveyor']['exists']);
        $this->assertSame('-', $data[4]['stages']['coa_surveyor']['doc_no']);
        $this->assertSame('Menunggu QC Surveyor', $data[4]['stages']['coa_surveyor']['note']);
        $this->assertNull($data[4]['stages']['coa_surveyor']['preview_url']);
    }

    private function makeBooking(int $id, string $bookingNo, string $contractNo): BuyerContractBooking
    {
        $contract = (new BuyerContract())->forceFill([
            'ID'           => $id,
            'Contract_No'  => $contractNo,
            'Created_Date' => '2026-11-01 08:00:00',
            'Status'       => 'ACTIVE',
        ]);

        $booking = (new BuyerContractBooking())->forceFill([
            'ID'           => $id,
            'Contract_ID'  => $id,
            'Booking_No'   => $bookingNo,
            'Label'        => 'BC ' . $id,
            'Created_Date' => '2026-11-10 09:00:00',
        ]);
        $booking->setRelation('contract', $contract);

        return $booking;
    }
}
