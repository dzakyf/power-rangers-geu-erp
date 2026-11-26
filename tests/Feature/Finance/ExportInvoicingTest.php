<?php

namespace Tests\Feature\Finance;

use App\ExportInvoicingRequest;
use App\Http\Controllers\Finance\ExportInvoicingController;
use App\PackingList;
use App\Services\NotificationService;
use App\VesselSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExportInvoicingTest extends TestCase
{
    public function test_menu_and_routes_are_registered()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayHasKey('SM00069', $menus);
        $this->assertArrayHasKey('SS00099', $menus);
        $this->assertArrayHasKey('SI00353', $menus);
        $this->assertSame(['Finance & Accounting', '#', 'CNS', 2, 'MS00011', 14], $menus['SM00069']);
        $this->assertSame(['Finance & Accounting', '#', 'CNS', 3, 'SM00069', 1], $menus['SS00099']);
        $this->assertSame(['Export Invoicing', 'finance-export-invoicing', 'CNS', 4, 'SS00099', 1], $menus['SI00353']);
        $this->assertSame('export_invoicing', \App\Services\CNS\CnsPermissionService::getSlug('SI00353'));

        $routes = collect(Route::getRoutes()->getRoutes())->map->uri()->all();
        foreach ([
            'finance-export-invoicing',
            'finance-export-invoicing-xml',
            'finance-export-invoicing-detail/{id}',
            'finance-export-invoicing-submit',
            'finance-export-invoicing-approve/{id}',
            'finance-export-invoicing-reject/{id}',
            'finance-export-invoicing-file/{id}/{type}',
            'api/finance/export-invoicing',
            'api/finance/export-invoicing/{id}',
            'api/finance/export-invoicing/{id}/file/{type}',
            'api/finance/export-invoicing/{id}/approve',
            'api/finance/export-invoicing/{id}/reject',
        ] as $uri) {
            $this->assertContains($uri, $routes, "Route {$uri} not found");
        }
    }

    public function test_model_status_constants_and_relationship()
    {
        $this->assertSame('Ready to Submit', ExportInvoicingRequest::STATUS_READY);
        $this->assertSame('pending FAT', ExportInvoicingRequest::STATUS_PENDING_FAT);
        $this->assertSame('Approved', ExportInvoicingRequest::STATUS_APPROVED);
        $this->assertSame('Rejected', ExportInvoicingRequest::STATUS_REJECTED);

        $model = new ExportInvoicingRequest();
        $this->assertSame('export_invoicing_requests', $model->getTable());
        $this->assertInstanceOf(VesselSchedule::class, $model->vesselSchedule()->getRelated());

        $xmlRes = \App\Support\DhtmlxGridPagination::response(1, 0, '<row id="1"><cell>1</cell></row>');
        $this->assertSame(200, $xmlRes->getStatusCode());
        $this->assertStringContainsString('<row id="1">', $xmlRes->getContent());
    }

    public function test_cns_submit_requires_contract_and_packing_list_files()
    {
        $controller = new ExportInvoicingController();
        $notifications = $this->createMock(NotificationService::class);

        $this->expectException(ValidationException::class);
        $controller->submit(Request::create('/finance-export-invoicing-submit', 'POST', []), $notifications);
    }

    public function test_finance_reject_requires_rejection_reason()
    {
        $controller = new ExportInvoicingController();
        $notifications = $this->createMock(NotificationService::class);

        $this->expectException(ValidationException::class);
        $controller->reject(Request::create('/finance-export-invoicing-reject/1', 'POST', []), 1, $notifications);
    }

    public function test_finance_approve_requires_transfer_proof()
    {
        $controller = new ExportInvoicingController();
        $notifications = $this->createMock(NotificationService::class);

        $this->expectException(ValidationException::class);
        $controller->approve(Request::create('/finance-export-invoicing-approve/1', 'POST', []), 1, $notifications);
    }

    public function test_external_fat_api_requires_valid_webhook_token()
    {
        $controller = new ExportInvoicingController();
        $notifications = $this->createMock(NotificationService::class);

        config(['services.finance.webhook_token' => '']);
        $res503 = $controller->apiIndex(Request::create('/api/finance/export-invoicing', 'GET'));
        $this->assertSame(503, $res503->getStatusCode());

        config(['services.finance.webhook_token' => 'secret-fat-token']);
        $res401 = $controller->apiApprove(Request::create('/api/finance/export-invoicing/1/approve', 'POST'), 1, $notifications);
        $this->assertSame(401, $res401->getStatusCode());

        $badKeyReq = Request::create('/api/finance/export-invoicing/1/reject', 'POST');
        $badKeyReq->headers->set('X-API-Key', 'wrong-token');
        $resBad = $controller->apiReject($badKeyReq, 1, $notifications);
        $this->assertSame(401, $resBad->getStatusCode());
    }

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

    public function test_submit_rejects_when_packing_list_not_approved_even_if_get_in_cy_checked()
    {
        $this->beginDatabaseTransactionOrSkip();

        $vs = VesselSchedule::create([
            'Order_No' => 'ORD-EI-UNAPPROVED-' . uniqid(),
            'Stuffing_No' => 'STF-001',
            'Checklist_Get_In_CY' => 1,
        ]);

        PackingList::create([
            'Packing_List_No' => 'PL-EI-CREATED-' . uniqid(),
            'PL_Date' => date('Y-m-d'),
            'Order_No' => $vs->Order_No,
            'SI_No' => 'SI-001',
            'SI_Date' => date('Y-m-d'),
            'Qty' => '1',
            'Qty1' => '1',
            'Qty2' => '1',
            'Shipper' => 'S',
            'Consignee' => 'C',
            'Notify' => 'N',
            'Destination_Port' => 'DP',
            'Delivery_Terms' => 'FOB',
            'Contract_No' => 'CTR-001',
            'Voyage' => 'V1',
            'ETD' => date('Y-m-d'),
            'ETA' => date('Y-m-d'),
            'Stuffing' => date('Y-m-d'),
            'PEB_No' => 'PEB-1',
            'PEB_Date' => date('Y-m-d'),
            'BL_No' => 'BL-1',
            'POL' => 'IDJKT',
            'POD' => 'SGSIN',
            'HS_Code' => '1511',
            'Status' => 'CREATED',
            'Product_Name' => 'UCO',
            'Created_By' => 'tester',
        ]);

        Storage::fake('public');
        $controller = new ExportInvoicingController();
        $notifications = $this->createMock(NotificationService::class);

        $request = Request::create('/finance-export-invoicing-submit', 'POST', [
            'vessel_schedule_id' => $vs->id,
        ], [], [
            'contract_file' => UploadedFile::fake()->create('contract.pdf', 10),
            'packing_list_file' => UploadedFile::fake()->create('pl.pdf', 10),
        ]);

        $res = $controller->submit($request, $notifications);
        $this->assertSame(422, $res->getStatusCode());
        $this->assertStringContainsString('APPROVED', $res->getContent());
    }

    public function test_index_xml_and_submit_allow_approved_packing_list_without_get_in_cy()
    {
        $this->beginDatabaseTransactionOrSkip();

        $orderNo = 'ORD-EI-APPROVED-' . uniqid();
        $vs = VesselSchedule::create([
            'Order_No' => $orderNo,
            'Stuffing_No' => 'STF-002',
            'Checklist_Get_In_CY' => 0,
        ]);

        $plBase = [
            'PL_Date' => date('Y-m-d'),
            'Order_No' => $orderNo,
            'SI_No' => 'SI-002',
            'SI_Date' => date('Y-m-d'),
            'Qty' => '1',
            'Qty1' => '1',
            'Qty2' => '1',
            'Shipper' => 'S',
            'Consignee' => 'C',
            'Notify' => 'N',
            'Destination_Port' => 'DP',
            'Delivery_Terms' => 'FOB',
            'Contract_No' => 'CTR-002',
            'Voyage' => 'V2',
            'ETD' => date('Y-m-d'),
            'ETA' => date('Y-m-d'),
            'Stuffing' => date('Y-m-d'),
            'PEB_No' => 'PEB-2',
            'PEB_Date' => date('Y-m-d'),
            'BL_No' => 'BL-2',
            'POL' => 'IDJKT',
            'POD' => 'SGSIN',
            'HS_Code' => '1511',
            'Status' => 'APPROVED',
            'Product_Name' => 'UCO',
            'Created_By' => 'tester',
        ];

        $pl1 = PackingList::create(array_merge($plBase, ['Packing_List_No' => 'PL-EI-A-' . uniqid()]));
        $pl2 = PackingList::create(array_merge($plBase, ['Packing_List_No' => 'PL-EI-B-' . uniqid()]));

        $controller = new ExportInvoicingController();

        $xmlRes = $controller->indexXml(Request::create('/finance-export-invoicing-xml', 'GET', ['count' => 100]));
        $this->assertSame(200, $xmlRes->getStatusCode());
        $this->assertStringContainsString($orderNo, $xmlRes->getContent());
        $this->assertStringContainsString($pl1->Packing_List_No . ', ' . $pl2->Packing_List_No, $xmlRes->getContent());

        Storage::fake('public');
        $notifications = $this->createMock(NotificationService::class);
        $notifications->expects($this->once())->method('makeNotification');

        $submitReq = Request::create('/finance-export-invoicing-submit', 'POST', [
            'vessel_schedule_id' => $vs->id,
        ], [], [
            'contract_file' => UploadedFile::fake()->create('contract.pdf', 10),
            'packing_list_file' => UploadedFile::fake()->create('pl.pdf', 10),
        ]);

        $submitRes = $controller->submit($submitReq, $notifications);
        $this->assertSame(200, $submitRes->getStatusCode());

        $saved = ExportInvoicingRequest::where('vessel_schedule_id', $vs->id)->first();
        $this->assertNotNull($saved);
        $this->assertSame(ExportInvoicingRequest::STATUS_PENDING_FAT, $saved->status);
        $this->assertSame($pl1->Packing_List_No . ', ' . $pl2->Packing_List_No, $saved->packing_list_no);
    }

    public function test_notification_service_encodes_array_metadata()
    {
        $builder = \Mockery::mock();
        $builder->shouldReceive('insertGetId')
            ->once()
            ->with(\Mockery::on(function ($payload) {
                return isset($payload['Metadata'])
                    && is_string($payload['Metadata'])
                    && $payload['Metadata'] === json_encode(['export_invoicing_request_id' => 99, 'order_no' => 'ORD-1']);
            }))
            ->andReturn(123);

        DB::shouldReceive('table')->once()->with('notification')->andReturn($builder);

        $service = new NotificationService();
        $id = $service->makeNotification(
            'Export Invoicing: Approved',
            'Order ORD-1 approved',
            'EXPORT_INVOICING',
            'finance',
            'cns',
            'SENT',
            ['export_invoicing_request_id' => 99, 'order_no' => 'ORD-1']
        );

        $this->assertSame(123, $id);
    }

    public function test_approve_marks_booking_finance_payment_verified()
    {
        $this->beginDatabaseTransactionOrSkip();

        $bookingNo = 'BC-EI-' . uniqid();
        $booking = \App\BuyerContractBooking::create([
            'Contract_ID' => 1,
            'Booking_No' => $bookingNo,
            'Finance_Payment_Verified' => 0,
            'Payment_Approved' => 0,
        ]);

        $vs = VesselSchedule::create([
            'Order_No' => $bookingNo,
            'Booking_No' => $bookingNo,
            'Stuffing_No' => 'STF-APPR',
        ]);

        $req = ExportInvoicingRequest::create([
            'vessel_schedule_id' => $vs->id,
            'order_no' => $bookingNo,
            'status' => ExportInvoicingRequest::STATUS_PENDING_FAT,
            'submitted_by' => 'cns_user',
            'submitted_at' => now(),
        ]);

        Storage::fake('public');
        $controller = new ExportInvoicingController();
        $res = $controller->approve(
            Request::create("/finance-export-invoicing-approve/{$req->id}", 'POST', ['finance_notes' => 'OK'], [], [
                'transfer_proof' => UploadedFile::fake()->create('transfer.pdf', 10),
            ]),
            $req->id,
            new NotificationService()
        );

        $this->assertSame(200, $res->getStatusCode());
        $req->refresh();
        $this->assertNotNull($req->transfer_proof_path);
        $this->assertSame('transfer.pdf', $req->transfer_proof_name);
        $booking->refresh();
        $this->assertSame(1, (int) $booking->Finance_Payment_Verified);
        $this->assertSame(0, (int) $booking->Payment_Approved);
    }
}
