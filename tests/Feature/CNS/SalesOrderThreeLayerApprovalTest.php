<?php

namespace Tests\Feature\CNS;

use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalesOrderThreeLayerApprovalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = new User();
        $user->username = 'tester';
        $user->Name = 'Tester User';
        $user->Position = 'Staff';
        $user->Location = 'CNS';
        $this->be($user);
    }

    /** @test */
    public function routes_and_permissions_for_three_layer_so_approval_are_registered(): void
    {
        $routes = app('router')->getRoutes();

        $gudangRoute = $routes->match(Request::create('cns-sales-order-approve-gudang/1', 'POST'));
        $buyerRoute = $routes->match(Request::create('cns-sales-order-approve-buyer/1', 'POST'));
        $managerRoute = $routes->match(Request::create('cns-sales-order-approve/1', 'POST'));
        $sjPrintRoute = $routes->match(Request::create('cns-sales-order-surat-jalan-print', 'GET'));

        $this->assertContains('can:cns.sales_order.approve_gudang', $gudangRoute->gatherMiddleware());
        $this->assertContains('can:cns.sales_order.approve_buyer', $buyerRoute->gatherMiddleware());
        $this->assertContains('can:cns.sales_order.approve_manager', $managerRoute->gatherMiddleware());
        $this->assertContains('can:cns.sales_order.view', $sjPrintRoute->gatherMiddleware());
    }

    /** @test */
    public function input_order_view_contains_three_layer_buttons_modal_and_surat_jalan_print(): void
    {
        $indexContent = file_get_contents(resource_path('views/cns/sales/input-order.blade.php'));
        $detailContent = file_get_contents(resource_path('views/cns/sales/input-order-detail.blade.php'));

        $this->assertStringContainsString('id="PrintDropdownBtn"', $indexContent);
        $this->assertStringContainsString('id="PrintSalesOrder"', $indexContent);
        $this->assertStringContainsString('id="PrintSuratJalan"', $indexContent);
        $this->assertStringContainsString('cns-sales-order-surat-jalan-print', $indexContent);
        $this->assertStringNotContainsString('id="ApproveGudang"', $indexContent);
        $this->assertStringNotContainsString('id="ApproveBuyer"', $indexContent);
        $this->assertStringNotContainsString('id="ApproveSalesOrder"', $indexContent);

        $this->assertStringContainsString("cns.sales_order.approve_gudang", $detailContent);
        $this->assertStringContainsString("cns.sales_order.approve_buyer", $detailContent);
        $this->assertStringContainsString("cns.sales_order.approve_manager", $detailContent);
        $this->assertStringContainsString('id="ApproveGudang"', $detailContent);
        $this->assertStringContainsString('id="ApproveBuyer"', $detailContent);
        $this->assertStringContainsString('id="ApproveSalesOrder"', $detailContent);
        $this->assertStringContainsString('id="RejectSalesOrder"', $detailContent);
        $this->assertStringContainsString('id="buyerSignatureModal"', $detailContent);
        $this->assertStringContainsString('id="buyerSignatureCanvas"', $detailContent);
    }

    /** @test */
    public function print_view_shows_prices_for_so_and_hides_all_prices_for_surat_jalan(): void
    {
        DB::shouldReceive('table')->with('users')->andReturnSelf();
        DB::shouldReceive('where')->andReturnSelf();
        DB::shouldReceive('select')->andReturnSelf();
        DB::shouldReceive('first')->andReturn((object) ['Name' => 'Tester User', 'Position' => 'Warehouse Leader']);

        $salesOrder = (object) [
            'so_no'                => '001/SO/XI/2026',
            'so_date'              => '2026-11-19',
            'site'                 => 'MALANG',
            'site_address'         => 'Jl. Industri',
            'Payment_Term'         => 'CASH',
            'buyer_pic'            => 'Budi Buyer',
            'buyer_city'           => 'Malang',
            'buyer_province'       => 'Jawa Timur',
            'buyer_phone'          => '08123456789',
            'subtotal'             => 500000,
            'tax'                  => 55000,
            'shipping'             => 10000,
            'other'                => 5000,
            'total'                => 570000,
            'remarks'              => 'Kirim pagi',
            'created_by'           => 'tester',
            'status'               => 'APPROVED',
            'buyer_signed_by'      => 'Pak Joko',
            'buyer_signature_path' => 'uploads/so-signatures/so_1_test.png',
            'surat_jalan_no'       => 'SJ-202611-0001',
            'surat_jalan_date'     => '2026-11-19',
        ];

        $details = collect([
            (object) [
                'description' => 'Sludge Tank A',
                'qty'         => 50,
                'unit'        => 'Kg',
                'unit_price'  => 10000,
                'total'       => 500000,
            ],
        ]);

        $soHtml = view('cns.sales.print.input-order', [
            'salesOrder'   => $salesOrder,
            'details'      => $details,
            'isSuratJalan' => false,
        ])->render();

        $this->assertStringContainsString('SALES ORDER', $soHtml);
        $this->assertStringContainsString('UNIT PRICE (Rp)', $soHtml);
        $this->assertStringContainsString('TOTAL (Rp)', $soHtml);
        $this->assertStringContainsString('570,000.00', $soHtml);
        $this->assertStringContainsString('Terbilang', $soHtml);
        $this->assertStringContainsString('uploads/so-signatures/so_1_test.png', $soHtml);
        $this->assertStringContainsString('Pak Joko', $soHtml);

        $sjHtml = view('cns.sales.print.input-order', [
            'salesOrder'   => $salesOrder,
            'details'      => $details,
            'isSuratJalan' => true,
        ])->render();

        $this->assertStringContainsString('SURAT JALAN', $sjHtml);
        $this->assertStringContainsString('SJ-202611-0001', $sjHtml);
        $this->assertStringContainsString('001/SO/XI/2026', $sjHtml);
        $this->assertStringContainsString('Sludge Tank A', $sjHtml);
        $this->assertStringContainsString('50.00 Kg', $sjHtml);
        $this->assertStringContainsString('uploads/so-signatures/so_1_test.png', $sjHtml);
        $this->assertStringNotContainsString('UNIT PRICE (Rp)', $sjHtml);
        $this->assertStringNotContainsString('TOTAL (Rp)', $sjHtml);
        $this->assertStringNotContainsString('Subtotal', $sjHtml);
        $this->assertStringNotContainsString('Shipping', $sjHtml);
        $this->assertStringNotContainsString('Terbilang', $sjHtml);
        $this->assertStringNotContainsString('500,000.00', $sjHtml);
        $this->assertStringNotContainsString('570,000.00', $sjHtml);
    }

    /** @test */
    public function status_stepper_renders_in_grid_xml_and_detail_view(): void
    {
        $controller = new \App\Http\Controllers\CNS\SalesOrderController();
        $method = new \ReflectionMethod(\App\Http\Controllers\CNS\SalesOrderController::class, 'formatStatusStepperHtml');
        $method->setAccessible(true);

        $order = new SalesOrderCNS();
        $order->status = 'APPROVED_GUDANG';
        $order->gudang_approved_date = '2026-11-19 10:00:00';
        $html = $method->invoke($controller, $order);

        $this->assertStringContainsString('so-mini-stepper', $html);
        $this->assertStringContainsString('data-status="APPROVED_GUDANG"', $html);
        $this->assertStringContainsString('>GD<', $html);
        $this->assertStringContainsString('>BY<', $html);
        $this->assertStringContainsString('>MG<', $html);
        $this->assertStringContainsString('>OK<', $html);

        $labelMethod = new \ReflectionMethod(\App\Http\Controllers\CNS\SalesOrderController::class, 'formatStatusLabel');
        $labelMethod->setAccessible(true);
        $this->assertSame('Menunggu approve gudang', $labelMethod->invoke($controller, 'PENDING'));
        $this->assertSame('Menunggu ttd buyer', $labelMethod->invoke($controller, 'APPROVED_GUDANG'));
        $this->assertSame('Menunggu final approve manager', $labelMethod->invoke($controller, 'SIGNED_BUYER'));
        $this->assertSame('APPROVED', $labelMethod->invoke($controller, 'APPROVED'));
        $this->assertSame('REJECTED', $labelMethod->invoke($controller, 'REJECTED'));

        $detailContent = file_get_contents(resource_path('views/cns/sales/input-order-detail.blade.php'));
        $this->assertStringContainsString('id="soApprovalStepper"', $detailContent);
        $this->assertStringContainsString('Kepala Gudang', $detailContent);
        $this->assertStringContainsString('TTD Buyer', $detailContent);
        $this->assertStringContainsString('Manager CNS & SJ', $detailContent);
    }
}
