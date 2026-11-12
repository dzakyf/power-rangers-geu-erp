<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\Partner\UCOController;
use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use App\UCOStoragePelindo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class InboundMovementCreateTest extends TestCase
{
    /** @test */
    public function movement_routes_are_registered()
    {
        $this->assertTrue(Route::has('cns-inventory-tracking.movements'));
        $this->assertTrue(Route::has('cns-inventory-tracking.movement-detail'));
    }

    /** @test */
    public function apipi_client_filters_pelindo_movements_across_pages()
    {
        $pagesCalled = [];
        $fakeClient = new class($this->createMock(ApipiAuthService::class), $pagesCalled) extends ApipiApiClient {
            public $pagesCalled;
            public function __construct($auth, &$pagesCalled)
            {
                parent::__construct($auth);
                $this->pagesCalled = &$pagesCalled;
            }
            public function movements(int $page = 1, int $limit = 100): array
            {
                $this->pagesCalled[] = $page;
                if ($page === 1) {
                    return [
                        'data' => [
                            ['id' => 1, 'kode' => 'GEU-MOV-26-TEST01', 'tujuan_gudang' => 'Final Storage Pelindo', 'tgl' => '2026-11-10T00:00:00+07:00', 'dari_gudang' => 'Surabaya', 'total_qty' => 12000, 'deleted_at' => null],
                            ['id' => 2, 'kode' => 'GEU-MOV-26-TEST02', 'tujuan_gudang' => 'Transit Sidoarjo', 'tgl' => '2026-11-10T00:00:00+07:00', 'dari_gudang' => 'Denpasar', 'total_qty' => 5000, 'deleted_at' => null],
                            ['id' => 4, 'kode' => 'GEU-MOV-26-DEL01', 'tujuan_gudang' => 'Final Storage Pelindo', 'tgl' => '2026-11-10T00:00:00+07:00', 'dari_gudang' => 'Surabaya', 'total_qty' => 9000, 'deleted_at' => '2026-11-10T12:00:00+07:00'],
                        ],
                        'pagination' => ['has_next' => true],
                    ];
                }
                return [
                    'data' => [
                        ['id' => 3, 'kode' => 'GEU-MOV-26-TEST03', 'tujuan_gudang' => 'Final Storage Pelindo', 'tgl' => '2026-11-09', 'dari_gudang' => 'Malang', 'total_qty' => 0],
                    ],
                    'pagination' => ['has_next' => false],
                ];
            }
        };

        $rows = $fakeClient->pelindoInboundMovements();

        $this->assertSame([1, 2], $pagesCalled);
        $this->assertSame(['GEU-MOV-26-TEST01', 'GEU-MOV-26-TEST03'], array_column($rows, 'kode'));
    }

    /** @test */
    public function options_endpoint_excludes_existing_and_reports_api_failure()
    {
        $existingKode = 'GEU-MOV-26-EXT' . substr((string) uniqid(), -5);
        $newKode = 'GEU-MOV-26-NEW' . substr((string) uniqid(), -5);

        DB::beginTransaction();
        try {
            UCOStoragePelindo::create([
                'Type'             => 'inbound',
                'Quantity'         => 1000,
                'Transaction_Date' => '2026-11-10',
                'Reference_Number' => $existingKode,
                'Created_By'       => 'PHPUnit',
            ]);

            $fakeClient = new class($this->createMock(ApipiAuthService::class), $existingKode, $newKode) extends ApipiApiClient {
                private $existingKode;
                private $newKode;
                public function __construct($auth, $existingKode, $newKode)
                {
                    parent::__construct($auth);
                    $this->existingKode = $existingKode;
                    $this->newKode = $newKode;
                }
                public function pelindoInboundMovements(): array
                {
                    return [
                        ['id' => 10, 'kode' => $this->existingKode, 'tgl' => '2026-11-10T00:00:00+07:00', 'dari_gudang' => 'Surabaya', 'total_qty' => 1000, 'logistics_status' => 'done'],
                        ['id' => 11, 'kode' => $this->newKode, 'tgl' => '2026-11-11T00:00:00+07:00', 'dari_gudang' => 'Malang', 'total_qty' => 19526, 'logistics_status' => 'DONE'],
                        ['id' => 12, 'kode' => 'GEU-MOV-26-PENDING', 'tgl' => '2026-11-11T00:00:00+07:00', 'dari_gudang' => 'Sidoarjo', 'total_qty' => 8000, 'logistics_status' => 'in_transit'],
                        ['id' => 13, 'kode' => 'GEU-MOV-26-NULLST', 'tgl' => '2026-11-11T00:00:00+07:00', 'dari_gudang' => 'Gresik', 'total_qty' => 5000, 'logistics_status' => null],
                    ];
                }
            };
            $this->app->instance(ApipiApiClient::class, $fakeClient);

            $controller = app(UCOController::class);
            $resp = $controller->getInboundMovementOptions();
            $json = json_decode($resp->getContent(), true);

            $this->assertSame(200, $resp->getStatusCode(), $resp->getContent());
            $this->assertTrue($json['success']);
            $kodes = array_column($json['data'], 'kode');
            $this->assertNotContains($existingKode, $kodes);
            $this->assertContains($newKode, $kodes);
            $this->assertNotContains('GEU-MOV-26-PENDING', $kodes);
            $this->assertNotContains('GEU-MOV-26-NULLST', $kodes);

            // Simulate upstream failure
            $failingClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
                public function pelindoInboundMovements(): array
                {
                    throw new RuntimeException('timeout');
                }
            };
            $this->app->instance(ApipiApiClient::class, $failingClient);

            $failResp = $controller->getInboundMovementOptions();
            $failJson = json_decode($failResp->getContent(), true);
            $this->assertSame(502, $failResp->getStatusCode());
            $this->assertFalse($failJson['success']);
            $this->assertStringContainsString('timeout', $failJson['message']);
        } finally {
            DB::rollBack();
        }
    }

    /** @test */
    public function movement_detail_endpoint_maps_api_fields_to_database_columns()
    {
        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function movementDetail($id): array
            {
                return [
                    'movement' => [
                        'id'          => 206,
                        'kode'        => 'GEU-MOV-26-JZG0002',
                        'tgl'         => '2026-11-01',
                        'dari_gudang' => 'Surabaya, Malang',
                        'total_qty'   => 148410,
                    ],
                    'details' => [
                        [
                            'to_storage_name' => 'TK-01',
                            'tgl_unloading'   => '2026-11-11T00:00:00+07:00',
                            'ffa'             => 2.88,
                            'dest_ffa'        => 3.27,
                            'moisture'        => 0.38,
                            'impurities'      => 0.41,
                        ],
                        [
                            'to_storage_name' => 'TK-01',
                            'tgl_unloading'   => '2026-11-11T00:00:00+07:00',
                            'ffa'             => 3.80,
                            'dest_ffa'        => 3.27,
                            'moisture'        => 0.48,
                            'impurities'      => 0.57,
                        ],
                    ],
                ];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = app(UCOController::class);
        $resp = $controller->getInboundMovementDetail(new Request(['id' => 206]));
        $json = json_decode($resp->getContent(), true);

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertTrue($json['success']);
        $d = $json['data'];
        $this->assertSame('inbound', $d['Type']);
        $this->assertSame('GEU-MOV-26-JZG0002', $d['Reference_Number']);
        $this->assertSame('2026-11-11', $d['Transaction_Date']);
        $this->assertSame('Surabaya, Malang', $d['Asal_Warehouse']);
        $this->assertSame('TK-01', $d['Nama_Tangki']);
        $this->assertSame(148410, $d['Quantity']);
        $this->assertSame(3.27, $d['FFA']);
        $this->assertSame(0.43, $d['MC']);
        $this->assertSame(0.49, $d['IMP']);
        $this->assertSame(0.92, $d['MIU']);
    }

    /** @test */
    public function submit_creates_inbound_with_full_form_fields_and_prevents_duplicates()
    {
        $kode = 'GEU-MOV-26-TST' . substr((string) uniqid(), -5);

        $controller = app(UCOController::class);

        DB::beginTransaction();
        try {
            $resp = $controller->submitStoragePelindo(new Request([
                'movement_kode'       => $kode,
                'Type'                => 'inbound',
                'Quantity'            => 19500,
                'Transaction_Date'    => '2026-11-11',
                'Asal_Warehouse'      => 'Surabaya, Transit Sidoarjo',
                'Nama_Tangki'         => 'TK-01',
                'Quantity_PTU'        => 19526,
                'Reference_Number'    => $kode,
                'Notes'               => 'Test inbound movement',
                'MC'                  => 0.43,
                'IMP'                 => 0.49,
                'top_FFA_As_Palmitic' => 3.27,
                'top_MIU'             => 0.92,
            ]));
            $json = json_decode($resp->getContent(), true);

            $this->assertSame(200, $resp->getStatusCode(), $resp->getContent());
            $this->assertTrue($json['success']);

            $saved = UCOStoragePelindo::where('Reference_Number', $kode)->first();
            $this->assertNotNull($saved);
            $this->assertSame('inbound', $saved->Type);
            $this->assertSame(19500.0, (float) $saved->Quantity);
            $this->assertSame(19526.0, (float) $saved->Quantity_PTU);
            $this->assertSame('Surabaya, Transit Sidoarjo', $saved->Asal_Warehouse);
            $this->assertSame('TK-01', $saved->Nama_Tangki);
            $this->assertSame('2026-11-11', $saved->Transaction_Date->toDateString());
            $this->assertSame(3.27, (float) $saved->FFA);
            $this->assertSame(0.43, (float) $saved->MC);
            $this->assertSame(0.49, (float) $saved->IMP);

            // Duplicate submit rejected
            $dupResp = $controller->submitStoragePelindo(new Request([
                'movement_kode'    => $kode,
                'Type'             => 'inbound',
                'Quantity'         => 19500,
                'Transaction_Date' => '2026-11-11',
                'Reference_Number' => $kode,
            ]));
            $dupJson = json_decode($dupResp->getContent(), true);
            $this->assertSame(422, $dupResp->getStatusCode());
            $this->assertFalse($dupJson['success']);
        } finally {
            DB::rollBack();
        }
    }

    /** @test */
    public function create_view_contains_all_expected_fields_hidden_until_movement_selected()
    {
        $createHtml = view('cns.inventory.create')->render();
        $editHtml = view('cns.inventory.edit')->render();

        $this->assertStringContainsString('id="movement_kode"', $createHtml);
        $this->assertStringContainsString('id="movementFieldsWrapper" style="display:none;"', $createHtml);
        $this->assertStringContainsString('id="Type"', $createHtml);
        $this->assertStringContainsString('id="Quantity"', $createHtml);
        $this->assertStringContainsString('id="Transaction_Date"', $createHtml);
        $this->assertStringContainsString('id="Asal_Warehouse"', $createHtml);
        $this->assertStringContainsString('id="Nama_Tangki"', $createHtml);
        $this->assertStringContainsString('id="Quantity_PTU"', $createHtml);
        $this->assertStringContainsString('id="Reference_Number"', $createHtml);
        $this->assertStringContainsString('id="Attachment"', $createHtml);
        $this->assertStringContainsString('id="QC_Attachment"', $createHtml);
        $this->assertStringContainsString('id="Notes"', $createHtml);
        $this->assertStringNotContainsString('id="Quantity_Sounding"', $createHtml);
        $this->assertStringContainsString('window.populateCnsWarehouseSelect', $editHtml);
    }
}
