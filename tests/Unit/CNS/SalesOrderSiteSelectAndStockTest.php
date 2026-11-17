<?php

namespace Tests\Unit\CNS;

use App\CnsInventoryKemasan;
use App\CnsMasterKemasan;
use App\Http\Controllers\CNS\SalesOrderController;
use App\Http\Controllers\Partner\MasterDataController;
use App\SalesOrderCNS;
use App\SalesOrderCNSDtl;
use App\Services\CNS\KemasanInventoryService;
use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ReflectionMethod;
use Tests\TestCase;

class SalesOrderSiteSelectAndStockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function invokeProtected(object $target, string $method, array $args)
    {
        $ref = new ReflectionMethod($target, $method);
        $ref->setAccessible(true);

        return $ref->invokeArgs($target, $args);
    }

    /** @test */
    public function master_data_warehouses_endpoint_returns_alamat()
    {
        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function warehouses(int $page = 1, int $limit = 200, ?string $type = null, ?string $search = null): array
            {
                return [
                    'data' => [
                        [
                            'id' => 18,
                            'name' => 'Malang',
                            'type' => 'site',
                            'active' => 1,
                            'alamat' => 'Jl. Karanglo Indah No. 10, Malang',
                            'kota_name' => 'Kota Malang',
                            'provinsi_name' => 'Jawa Timur',
                        ],
                    ],
                    'pagination' => ['has_next' => false],
                ];
            }
        };

        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $ctrl = new MasterDataController();
        $resp = $ctrl->getCnsMasterWarehouses(Request::create('/cns-master-warehouses', 'GET', ['type' => 'site']));
        $json = json_decode($resp->getContent(), true);

        $this->assertSame('success', $json['status']);
        $this->assertCount(1, $json['data']);
        $this->assertSame('Malang', $json['data'][0]['name']);
        $this->assertSame('Jl. Karanglo Indah No. 10, Malang', $json['data'][0]['alamat']);
    }

    /** @test */
    public function views_render_site_select2_and_approval_controls()
    {
        $user = new User();
        $user->username = 'tester';
        $this->be($user);

        $kemasans = collect([
            new CnsMasterKemasan(['Kemasan_ID' => 1, 'Name' => 'Ampas Basah', 'Volume' => 0, 'Is_Ampas' => true, 'Unit' => 'Kg', 'Price' => 2000]),
            new CnsMasterKemasan(['Kemasan_ID' => 2, 'Name' => 'Drum', 'Volume' => 200, 'Is_Ampas' => false, 'Unit' => 'Pcs', 'Price' => 75000]),
        ]);

        $newHtml = view('cns.sales.input-order-new', [
            'socode'   => '001/SO/XI/2026',
            'kemasans' => $kemasans,
        ])->render();

        $this->assertStringContainsString('<select class="form-control" id="site"', $newHtml);
        $this->assertStringContainsString('value=""', $newHtml);
        $this->assertStringContainsString('<option value="" selected>-- Pilih Site --</option>', $newHtml);
        $this->assertStringContainsString('<th class="stock-col text-center">Stok</th>', $newHtml);
        $this->assertStringContainsString('id="site_id"', $newHtml);
        $this->assertStringContainsString('initSiteSelect', $newHtml);
        $this->assertStringContainsString('cns-master-warehouses?type=site', $newHtml);
        $this->assertStringContainsString('cns-sales-order-check-stock', $newHtml);
        $this->assertStringContainsString('addrEl.value = alamat;', $newHtml);

        $salesOrder = (new SalesOrderCNS())->forceFill([
            'id'             => 42,
            'so_no'          => '001/SO/XI/2026',
            'so_date'        => '2026-11-17',
            'buyer_pic'      => 'Budi',
            'buyer_city'     => 'Malang',
            'buyer_province' => 'Jawa Timur',
            'buyer_phone'    => '08123',
            'site'           => 'Malang',
            'site_id'        => 18,
            'status'         => 'PENDING',
            'site_address'   => 'Jl. Karanglo',
            'payment_term'   => 'Transfer',
            'subtotal'       => 100000,
            'tax'            => 0,
            'shipping'       => 0,
            'other'          => 0,
            'total'          => 100000,
            'remarks'        => '',
            'created_by'     => 'tester',
        ]);

        $details = collect([
            (new SalesOrderCNSDtl())->forceFill([
                'id'          => 1,
                'so_no'       => '001/SO/XI/2026',
                'description' => 'Drum 200L',
                'qty'         => 2,
                'unit'        => 'Pcs',
                'unit_price'  => 75000,
                'total'       => 150000,
            ]),
        ]);

        $detailHtml = view('cns.sales.input-order-detail', [
            'salesOrder' => $salesOrder,
            'details'    => $details,
            'kemasans'   => $kemasans,
        ])->render();

        $this->assertStringContainsString('<select class="form-control" id="site"', $detailHtml);
        $this->assertStringContainsString('value="18"', $detailHtml);
        $this->assertStringContainsString('PENDING', $detailHtml);

        $indexHtml = file_get_contents(resource_path('views/cns/sales/input-order.blade.php'));
        $this->assertStringContainsString('ApproveSalesOrder', $indexHtml);
        $this->assertStringContainsString('RejectSalesOrder', $indexHtml);
        $this->assertStringContainsString('SalesOrderApprove()', $indexHtml);
        $this->assertStringContainsString('SalesOrderReject()', $indexHtml);
        $this->assertStringContainsString('cns-sales-order-approve/', $indexHtml);
        $this->assertStringContainsString('cns-sales-order-reject/', $indexHtml);
    }

    /** @test */
    public function build_header_payload_includes_site_id_and_status()
    {
        $controller = new SalesOrderController();

        $request = new Request([
            'soDate'         => '2026-11-17',
            'buyerPic'       => 'Siti',
            'site'           => 'Malang',
            'site_id'        => 18,
            'siteAddress'    => 'Jl. Raya No 1',
            'paymentTerm'    => 'Transfer',
            'subtotal'       => 50000,
        ]);

        $header = $this->invokeProtected($controller, 'buildHeaderPayload', [$request, '002/SO/XI/2026', 50000.0]);

        $this->assertSame('002/SO/XI/2026', $header['so_no']);
        $this->assertSame('Malang', $header['site']);
        $this->assertSame(18, $header['site_id']);
        $this->assertSame('PENDING', $header['status']);
    }

    /** @test */
    public function check_stock_returns_available_sale_balance()
    {
        $mockService = $this->createMock(KemasanInventoryService::class);
        $mockService->expects($this->once())
            ->method('getAvailableSaleBalance')
            ->with('Drum 200L', 2, 18)
            ->willReturn(15.0);

        $this->app->instance(KemasanInventoryService::class, $mockService);

        $controller = new SalesOrderController();
        $response = $controller->checkStock(new Request([
            'site_id'      => 18,
            'packaging_id' => 2,
            'description'  => 'Drum 200L',
        ]));

        $json = json_decode($response->getContent(), true);
        $this->assertSame('success', $json['status']);
        $this->assertEquals(15.0, $json['stock']);
    }

    /** @test */
    public function sales_order_submit_blocks_when_qty_exceeds_stock()
    {
        $mockService = $this->createMock(KemasanInventoryService::class);
        $mockService->method('getAvailableSaleBalance')
            ->willReturn(5.0);

        $this->app->instance(KemasanInventoryService::class, $mockService);

        $controller = new SalesOrderController();
        $response = $controller->newSalesOrderSubmit(new Request([
            'soDate'       => '2026-11-17',
            'site'         => 'Malang',
            'site_id'      => 18,
            'description1' => 'Drum 200L',
            'qty1'         => 10, // exceeds available stock 5
            'unit1'        => 50000,
        ]));

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('tidak mencukupi', $response->getContent());
        $this->assertStringContainsString('Tersedia: 5', $response->getContent());
        $this->assertStringContainsString('Diminta: 10', $response->getContent());
    }

    /** @test */
    public function kemasan_inventory_service_resolves_available_sale_balance_correctly()
    {
        $mockRecord = new CnsInventoryKemasan([
            'qty_balance'     => 100,
            'excess_balance'  => 30,
            'current_balance' => 130,
        ]);

        $builder = $this->getMockBuilder(\Illuminate\Database\Eloquent\Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['orderBy'])
            ->getMock();
        $builder->method('orderBy')->willReturnSelf();
        $builder->method('first')->willReturn($mockRecord);

        $service = new class($builder) extends KemasanInventoryService {
            protected $builder;
            public function __construct($builder) {
                $this->builder = $builder;
            }
            protected function buildScopedQuery(?int $siteId, ?int $packagingId, ?string $itemName)
            {
                return $this->builder;
            }
        };

        // For regular kemasan with max stock, sale balance is excess balance (30)
        $this->assertEquals(30.0, $service->getAvailableSaleBalance('Drum 200L', 2, 18));

        // For ampas, sale balance is qty balance (100)
        $this->assertEquals(100.0, $service->getAvailableSaleBalance('Ampas Basah', 1, 18));
    }
}
