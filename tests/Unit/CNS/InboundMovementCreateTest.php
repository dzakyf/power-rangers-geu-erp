<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\Partner\UCOController;
use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use App\UCOStoragePelindo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $this->assertTrue(Route::has('cns-inventory-tracking.approve'));
        $this->assertTrue(Route::has('cns-inventory-tracking.reject'));
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
                        'fleet_name'  => 'Fuso Tronton',
                        'fleet_plat'  => 'L 1234 AB',
                        'driver_name' => 'Budi Santoso',
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
        $this->assertSame('Fuso Tronton', $d['fleet_name']);
        $this->assertSame('L 1234 AB', $d['fleet_plat']);
        $this->assertSame('Budi Santoso', $d['driver_name']);
        $this->assertSame(148410, $d['Quantity']);
        $this->assertSame(3.27, $d['FFA']);
        $this->assertSame(0.43, $d['MC']);
        $this->assertSame(0.49, $d['IMP']);
        $this->assertArrayNotHasKey('MIU', $d);
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
                'fleet_name'          => 'Fuso Tronton',
                'fleet_plat'          => 'L 1234 AB',
                'driver_name'         => 'Budi Santoso',
                'Notes'               => 'Test inbound movement',
                'MC'                  => 0.43,
                'IMP'                 => 0.49,
                'Movement_FFA'        => 2.50,
                'Movement_MC'         => 0.35,
                'Movement_IMP'        => 0.40,
                'top_FFA_As_Palmitic' => 3.27,
                'top_Moisture'        => 0.43,
                'top_Impurities'      => 0.49,
            ], [], [], [], [
                'BA_Attachment' => UploadedFile::fake()->create('ba.pdf', 100, 'application/pdf'),
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
            $this->assertSame('Fuso Tronton', $saved->fleet_name);
            $this->assertSame('L 1234 AB', $saved->fleet_plat);
            $this->assertSame('Budi Santoso', $saved->driver_name);
            $this->assertSame('2026-11-11', $saved->Transaction_Date->toDateString());
            $this->assertSame(3.27, (float) $saved->FFA);
            $this->assertSame(0.43, (float) $saved->MC);
            $this->assertSame(0.49, (float) $saved->IMP);
            $this->assertSame(2.5, (float) $saved->Movement_FFA);
            $this->assertSame(0.35, (float) $saved->Movement_MC);
            $this->assertSame(0.4, (float) $saved->Movement_IMP);

            // Duplicate submit rejected
            $dupResp = $controller->submitStoragePelindo(new Request([
                'movement_kode'    => $kode,
                'Type'             => 'inbound',
                'Quantity'         => 19500,
                'Transaction_Date' => '2026-11-11',
                'Reference_Number' => $kode,
            ], [], [], [], [
                'BA_Attachment' => UploadedFile::fake()->create('ba.pdf', 100, 'application/pdf'),
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
        $this->assertStringContainsString('id="fleet_name"', $createHtml);
        $this->assertStringContainsString('id="fleet_plat"', $createHtml);
        $this->assertStringContainsString('id="driver_name"', $createHtml);
        $this->assertStringContainsString('id="edit_fleet_name"', $editHtml);
        $this->assertStringContainsString('id="edit_fleet_plat"', $editHtml);
        $this->assertStringContainsString('id="edit_driver_name"', $editHtml);
        $this->assertStringContainsString('id="Attachment"', $createHtml);
        $this->assertStringContainsString('id="BA_Attachment"', $createHtml);
        $this->assertStringContainsString('id="QC_Attachment"', $createHtml);
        $this->assertStringContainsString('id="edit_BA_Attachment"', $editHtml);
        $this->assertStringContainsString('id="edit_approval_banner"', $editHtml);
        $this->assertStringContainsString('id="btnUpdateEditStock"', $editHtml);
        $this->assertStringContainsString('id="create_mov_ffa"', $createHtml);
        $this->assertStringContainsString('id="create_Movement_FFA"', $createHtml);
        $this->assertStringContainsString('id="edit_mov_ffa"', $editHtml);
        $this->assertStringNotContainsString('id="edit_Movement_FFA"', $editHtml);
        $this->assertStringContainsString('QC Movement', $createHtml);
        $this->assertStringContainsString('QC Inbound', $createHtml);
        $this->assertStringContainsString('QC Movement', $editHtml);
        $this->assertStringContainsString('QC Inbound', $editHtml);
        $this->assertStringContainsString('id="Notes"', $createHtml);
        $this->assertStringNotContainsString('id="Quantity_Sounding"', $createHtml);
        $this->assertStringContainsString('window.populateCnsWarehouseSelect', $editHtml);
    }

    /** @test */
    public function inbound_submit_and_edit_require_ba_attachment()
    {
        $controller = app(UCOController::class);

        $createResp = $controller->submitStoragePelindo(new Request([
            'movement_kode'    => 'GEU-MOV-26-NOBA',
            'Type'             => 'inbound',
            'Quantity'         => 1000,
            'Transaction_Date' => '2026-11-12',
        ]));
        $this->assertSame(422, $createResp->getStatusCode());
        $this->assertFalse(json_decode($createResp->getContent(), true)['success']);

        $editResp = $controller->updateExportedStock(new Request([
            'id'               => 1,
            'Type'             => 'inbound',
            'Transaction_Date' => '2026-11-12',
        ]));
        $this->assertSame(422, $editResp->getStatusCode());
        $this->assertFalse(json_decode($editResp->getContent(), true)['success']);
    }

    /** @test */
    public function reject_requires_reason_and_index_contains_approval_actions()
    {
        $controller = app(UCOController::class);

        $resp = $controller->rejectInboundMovement(new Request([
            'id' => 1,
        ]));
        $this->assertSame(422, $resp->getStatusCode());
        $this->assertFalse(json_decode($resp->getContent(), true)['success']);

        $indexHtml = file_get_contents(resource_path('views/cns/inventory/index.blade.php'));
        $this->assertStringContainsString('approveSelectedInbound()', $indexHtml);
        $this->assertStringContainsString('rejectSelectedInbound()', $indexHtml);
        $this->assertStringContainsString('Status Approval', $indexHtml);
    }
}
