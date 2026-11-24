<?php

namespace Tests\Feature\Finance;

use App\ExportInvoicingRequest;
use App\Http\Controllers\Finance\ExportInvoicingController;
use App\Services\NotificationService;
use App\VesselSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
}
