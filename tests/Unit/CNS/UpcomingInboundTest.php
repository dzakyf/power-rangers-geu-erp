<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\CNS\UpcomingInboundController;
use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use App\UCOStoragePelindo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class UpcomingInboundTest extends TestCase
{
    /** @test */
    public function controller_is_instantiable()
    {
        $controller = new UpcomingInboundController();
        $this->assertInstanceOf(UpcomingInboundController::class, $controller);
    }

    /** @test */
    public function routes_are_registered()
    {
        $this->assertTrue(Route::has('cns-upcoming-inbound.xml'));
        $this->assertFalse(Route::has('cns-upcoming-inbound.sync'));
    }

    /** @test */
    public function inbound_movement_view_has_tabs_and_upcoming_grid()
    {
        $viewPath = resource_path('views/cns/inventory/index.blade.php');
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);
        $this->assertStringContainsString('Monitoring Stock UCO', $content);
        $this->assertStringContainsString('Upcoming Inbound', $content);
        $this->assertStringContainsString('id="tab-monitoring"', $content);
        $this->assertStringContainsString('id="tab-upcoming"', $content);
        $this->assertStringContainsString('id="UpcomingGrid"', $content);
        $this->assertStringContainsString('id="ProjectionGrid"', $content);
        $this->assertStringContainsString('id="btnRefreshUpcomingApi"', $content);
        $this->assertStringContainsString('id="upcomingPaginationBar"', $content);
        $this->assertStringContainsString('id="upcomingPerPage"', $content);
        $this->assertStringContainsString('id="upcomingPaginationControls"', $content);
        $this->assertStringContainsString('id="upcomingEmptyNotice"', $content);
        $this->assertStringContainsString('id="upcomingErrorNotice"', $content);
        $this->assertStringContainsString('function renderUpcomingState', $content);
        $this->assertStringContainsString('id="filterUpcomingDateType"', $content);
        $this->assertStringContainsString('Tanggal,ETA,Asal Gudang', $content);
        $this->assertStringNotContainsString('id="filterUpcomingStatus"', $content);
        $this->assertStringNotContainsString('#text_filter', $content);
    }

    /** @test */
    public function xml_data_filters_non_done_and_excludes_existing_reference_numbers()
    {
        $existingKode = 'GEU-MOV-26-EXT' . substr((string) uniqid(), -5);
        $openKode     = 'GEU-MOV-26-OPN' . substr((string) uniqid(), -5);
        $doneKode     = 'GEU-MOV-26-DON' . substr((string) uniqid(), -5);
        $nullKode     = 'GEU-MOV-26-NUL' . substr((string) uniqid(), -5);

        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            DB::beginTransaction();
            UCOStoragePelindo::create([
                'Type'             => 'inbound',
                'Quantity'         => 1000,
                'Transaction_Date' => '2026-11-10',
                'Reference_Number' => $existingKode,
                'Created_By'       => 'PHPUnit',
            ]);
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        try {
            $fakeClient = new class($this->createMock(ApipiAuthService::class), $existingKode, $openKode, $doneKode, $nullKode) extends ApipiApiClient {
                private $rows;
                public function __construct($auth, $existingKode, $openKode, $doneKode, $nullKode)
                {
                    parent::__construct($auth);
                    $this->rows = [
                        ['id' => 1, 'kode' => $existingKode, 'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Surabaya', 'tgl' => '2026-11-10', 'tgl_loading' => '2026-11-10', 'total_qty' => 1000, 'logistics_status' => 'in_transit'],
                        ['id' => 2, 'kode' => $openKode,     'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Malang',   'tgl' => '2026-11-11', 'tgl_loading' => '2026-11-11', 'total_qty' => 19526, 'logistics_status' => 'in_transit'],
                        ['id' => 3, 'kode' => $doneKode,     'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Sidoarjo', 'tgl' => '2026-11-11', 'tgl_loading' => '2026-11-11', 'total_qty' => 5000,  'logistics_status' => 'DONE'],
                        ['id' => 4, 'kode' => $nullKode,     'tujuan_gudang' => 'Pelindo Gresik',        'dari_gudang' => 'Denpasar', 'tgl' => '2026-11-12', 'tgl_loading' => '2026-11-12', 'total_qty' => 8500,  'logistics_status' => null],
                    ];
                }
                public function pelindoInboundMovements(): array
                {
                    return $this->rows;
                }
            };
            $this->app->instance(ApipiApiClient::class, $fakeClient);

            $controller = new UpcomingInboundController();

            $response = $controller->xmlData(new Request());
            $output = $response->getContent();

            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString('text/xml', $response->headers->get('Content-Type'));
            $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $output);
            $this->assertStringContainsString($openKode, $output);
            $this->assertStringContainsString($nullKode, $output);
            if ($dbConnected) {
                $this->assertStringNotContainsString($existingKode, $output);
            }
            $this->assertStringNotContainsString($doneKode, $output);
            $this->assertStringNotContainsString('INB-2026-001', $output);

            // Filter by date and search (date_type=movement)
            $filtered = $controller->xmlData(new Request([
                'date_type'  => 'movement',
                'start_date' => '2026-11-12',
                'end_date'   => '2026-11-12',
                'search'     => 'Denpasar',
            ]))->getContent();

            $this->assertStringContainsString($nullKode, $filtered);
            $this->assertStringNotContainsString($openKode, $filtered);
        } finally {
            if ($dbConnected) {
                DB::rollBack();
            }
        }
    }

    /** @test */
    public function xml_data_exposes_filter_counts_for_empty_state()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                return [];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();
        $response = $controller->xmlData(new Request());
        $this->assertSame(200, $response->getStatusCode());

        $xml = simplexml_load_string($response->getContent());
        $this->assertSame('0', (string) $xml['total_count']);
        $this->assertSame('0', (string) $xml['source_count']);
        $this->assertSame('0', (string) $xml['used_excluded']);
        $this->assertSame('0', (string) $xml['done_excluded']);
        $this->assertSame('0', (string) $xml['filtered_count']);
    }

    /** @test */
    public function xml_data_counts_done_and_used_movements_for_empty_state()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                return [
                    ['id' => 901, 'kode' => 'MOV-EMPTY-OPEN', 'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Malang', 'tgl' => '2026-11-12', 'total_qty' => 100, 'logistics_status' => 'in_transit'],
                    ['id' => 902, 'kode' => 'MOV-EMPTY-DONE', 'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Solo', 'tgl' => '2026-11-12', 'total_qty' => 200, 'logistics_status' => 'done'],
                ];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();
        $response = $controller->xmlData(new Request());
        $this->assertSame(200, $response->getStatusCode());

        $xml = simplexml_load_string($response->getContent());
        $this->assertSame('1', (string) $xml['total_count']);
        $this->assertSame('2', (string) $xml['source_count']);
        $this->assertSame('0', (string) $xml['used_excluded']);
        $this->assertSame('1', (string) $xml['done_excluded']);
        $this->assertSame('1', (string) $xml['filtered_count']);
    }

    /** @test */
    public function xml_data_returns_502_json_when_apipi_fails()
    {
        $failingClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                throw new RuntimeException('timeout');
            }
        };
        $this->app->instance(ApipiApiClient::class, $failingClient);

        $controller = new UpcomingInboundController();
        $resp = $controller->xmlData(new Request());
        $json = json_decode($resp->getContent(), true);

        $this->assertSame(502, $resp->getStatusCode());
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('timeout', $json['message']);
    }

    /** @test */
    public function xml_data_paginates_results_and_bypasses_cache_on_refresh()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public $calls = 0;

            public function pelindoInboundMovements(): array
            {
                $this->calls++;
                if ($this->calls === 1) {
                    $rows = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $rows[] = [
                            'id'               => 100 + $i,
                            'kode'             => sprintf('MOV-PG-%02d', $i),
                            'tujuan_gudang'    => 'Pelindo',
                            'dari_gudang'      => 'Gudang ' . $i,
                            'tgl'              => '2026-11-10',
                            'total_qty'        => $i * 100,
                            'logistics_status' => 'in_transit',
                        ];
                    }
                    return $rows;
                }

                return [
                    ['id' => 201, 'kode' => 'MOV-PG-NEW', 'tujuan_gudang' => 'Pelindo', 'dari_gudang' => 'D', 'tgl' => '2026-11-12', 'total_qty' => 400, 'logistics_status' => 'in_transit'],
                ];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();

        $page1 = $controller->xmlData(new Request(['page' => 1, 'per_page' => 10]))->getContent();
        $this->assertSame(1, $fakeClient->calls);
        $this->assertStringContainsString('total_count="12"', $page1);
        $this->assertStringContainsString('page="1"', $page1);
        $this->assertStringContainsString('per_page="10"', $page1);
        $this->assertStringContainsString('total_pages="2"', $page1);
        $this->assertStringContainsString('<cell>1</cell><cell>MOV-PG-01</cell>', $page1);
        $this->assertStringContainsString('MOV-PG-10', $page1);
        $this->assertStringNotContainsString('MOV-PG-11', $page1);

        // Page 2 uses cached movements and continues row numbering at 11
        $page2 = $controller->xmlData(new Request(['page' => 2, 'per_page' => 10]))->getContent();
        $this->assertSame(1, $fakeClient->calls);
        $this->assertStringContainsString('total_count="12"', $page2);
        $this->assertStringContainsString('page="2"', $page2);
        $this->assertStringContainsString('per_page="10"', $page2);
        $this->assertStringContainsString('total_pages="2"', $page2);
        $this->assertStringContainsString('<cell>11</cell><cell>MOV-PG-11</cell>', $page2);
        $this->assertStringContainsString('<cell>12</cell><cell>MOV-PG-12</cell>', $page2);
        $this->assertStringNotContainsString('MOV-PG-10', $page2);

        // Invalid per_page falls back to 25 and clamps out-of-range page to 1
        $invalidPerPage = $controller->xmlData(new Request(['page' => 99, 'per_page' => 999]))->getContent();
        $this->assertSame(1, $fakeClient->calls);
        $this->assertStringContainsString('total_count="12"', $invalidPerPage);
        $this->assertStringContainsString('page="1"', $invalidPerPage);
        $this->assertStringContainsString('per_page="25"', $invalidPerPage);
        $this->assertStringContainsString('total_pages="1"', $invalidPerPage);

        // Refresh=1 clears cache and fetches fresh data
        $refreshed = $controller->xmlData(new Request(['refresh' => '1']))->getContent();
        $this->assertSame(2, $fakeClient->calls);
        $this->assertStringContainsString('total_count="1"', $refreshed);
        $this->assertStringContainsString('page="1"', $refreshed);
        $this->assertStringContainsString('per_page="25"', $refreshed);
        $this->assertStringContainsString('total_pages="1"', $refreshed);
        $this->assertStringContainsString('MOV-PG-NEW', $refreshed);
        $this->assertStringNotContainsString('MOV-PG-01', $refreshed);
    }

    /** @test */
    public function xml_data_calculates_eta_based_on_warehouse_from_id_rules()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                return [
                    // Group 1: (5, 18, 33, 48, 50) -> +1 day
                    [
                        'id' => 101,
                        'kode' => 'MOV-GRP1',
                        'tgl' => '2026-11-10',
                        'dari_gudang' => 'Gudang 5',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 1000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 5, 'tgl_loading' => '2026-11-10'],
                        ],
                    ],
                    // Group 2: (30, 31) -> +2 days
                    [
                        'id' => 102,
                        'kode' => 'MOV-GRP2',
                        'tgl' => '2026-11-10',
                        'dari_gudang' => 'Gudang 30',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 1000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 30, 'tgl_loading' => '2026-11-10'],
                        ],
                    ],
                    // Group 3: (24, 29, 37, 38, 40) -> +3 days
                    [
                        'id' => 103,
                        'kode' => 'MOV-GRP3',
                        'tgl' => '2026-11-10',
                        'dari_gudang' => 'Gudang 24',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 1000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 24, 'tgl_loading' => '2026-11-10'],
                        ],
                    ],
                    // Outside groups -> '-'
                    [
                        'id' => 104,
                        'kode' => 'MOV-OUTSIDE',
                        'tgl' => '2026-11-10',
                        'dari_gudang' => 'Gudang 99',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 1000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 99, 'tgl_loading' => '2026-11-10'],
                        ],
                    ],
                    // In group but tgl_loading empty -> 'TBD'
                    [
                        'id' => 105,
                        'kode' => 'MOV-TBD',
                        'tgl' => '2026-11-10',
                        'dari_gudang' => 'Gudang 18',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 1000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 18, 'tgl_loading' => null],
                        ],
                    ],
                    // Detail fetched via movementDetail endpoint
                    [
                        'id' => 106,
                        'kode' => 'MOV-DETAIL-FETCH',
                        'tgl' => '2026-11-10',
                        'dari_gudang' => 'Gudang 31',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 1000,
                        'logistics_status' => 'in_transit',
                    ],
                ];
            }

            public function movementDetail($id): array
            {
                if ($id == 106) {
                    return [
                        'details' => [
                            ['warehouse_from_id' => 31, 'tgl_loading' => '2026-11-10'],
                        ],
                    ];
                }
                return [];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();
        $xml = $controller->xmlData(new Request(['per_page' => 25]))->getContent();

        // Check rows and cell order: No, Kode, Tanggal, ETA, Asal, Tujuan, Qty, Status
        $this->assertStringContainsString('<cell>MOV-GRP1</cell><cell>10/11/2026</cell><cell>11/11/2026</cell>', $xml);
        $this->assertStringContainsString('<cell>MOV-GRP2</cell><cell>10/11/2026</cell><cell>12/11/2026</cell>', $xml);
        $this->assertStringContainsString('<cell>MOV-GRP3</cell><cell>10/11/2026</cell><cell>13/11/2026</cell>', $xml);
        $this->assertStringContainsString('<cell>MOV-OUTSIDE</cell><cell>10/11/2026</cell><cell>-</cell>', $xml);
        $this->assertStringContainsString('<cell>MOV-TBD</cell><cell>-</cell><cell>TBD</cell>', $xml);
        $this->assertStringContainsString('<cell>MOV-DETAIL-FETCH</cell><cell>10/11/2026</cell><cell>12/11/2026</cell>', $xml);

        // Test filtering by date_type=eta (default)
        $filteredEta = $controller->xmlData(new Request([
            'date_type'  => 'eta',
            'start_date' => '2026-11-12',
            'end_date'   => '2026-11-12',
        ]))->getContent();

        $this->assertStringContainsString('MOV-GRP2', $filteredEta);
        $this->assertStringContainsString('MOV-DETAIL-FETCH', $filteredEta);
        $this->assertStringNotContainsString('MOV-GRP1', $filteredEta);
        $this->assertStringNotContainsString('MOV-GRP3', $filteredEta);
        $this->assertStringNotContainsString('MOV-OUTSIDE', $filteredEta);
        $this->assertStringNotContainsString('MOV-TBD', $filteredEta);
    }

    /** @test */
    public function xml_data_uses_tgl_loading_from_movement_details_for_tanggal_column()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                return [
                    [
                        'id' => 301,
                        'kode' => 'MOV-TL-01',
                        'tgl' => '2026-11-01', // Movement date
                        'dari_gudang' => 'Surabaya',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 5000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 5, 'tgl_loading' => '2026-11-05'], // Loading date differs
                        ],
                    ],
                    [
                        'id' => 302,
                        'kode' => 'MOV-TL-FALLBACK',
                        'tgl' => '2026-11-02',
                        'dari_gudang' => 'Malang',
                        'tujuan_gudang' => 'Pelindo',
                        'total_qty' => 6000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 30, 'tgl_loading' => null], // Empty loading date
                        ],
                    ],
                ];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();
        $xml = $controller->xmlData(new Request())->getContent();

        // MOV-TL-01 uses tgl_loading 05/11/2026 instead of root tgl 01/11/2026; ETA +1 day = 06/11/2026
        $this->assertStringContainsString('<cell>MOV-TL-01</cell><cell>05/11/2026</cell><cell>06/11/2026</cell>', $xml);

        // MOV-TL-FALLBACK has empty tgl_loading, displays '-' for date and 'TBD' for ETA
        $this->assertStringContainsString('<cell>MOV-TL-FALLBACK</cell><cell>-</cell><cell>TBD</cell>', $xml);

        // Filter date_type=loading matches tgl_loading date
        $filtered = $controller->xmlData(new Request([
            'date_type'  => 'loading',
            'start_date' => '2026-11-05',
            'end_date'   => '2026-11-05',
        ]))->getContent();

        $this->assertStringContainsString('MOV-TL-01', $filtered);
        $this->assertStringNotContainsString('MOV-TL-FALLBACK', $filtered);
    }

    /** @test */
    public function xml_data_handles_multi_detail_movements_selecting_latest_loading_date()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                return [
                    // Multi-detail 1: Detail A (09/11 Gudang Surabaya) vs Detail B (12/11 Gudang Malang)
                    // Must calculate ETA from the LATEST tgl_loading (12/11 + 1 day = 13/11/2026)
                    // And sort Asal Gudang chronologically ascending: "Gudang Surabaya, Gudang Malang"
                    [
                        'id' => 401,
                        'kode' => 'MOV-MULTI-01',
                        'tujuan_gudang' => 'Pelindo',
                        'dari_gudang' => 'Malang & Surabaya',
                        'total_qty' => 10000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 5,  'gudang_from_name' => 'Gudang Malang',   'tgl_loading' => '2026-11-12'], // +1 day = 13/11 (latest loading!)
                            ['warehouse_from_id' => 24, 'gudang_from_name' => 'Gudang Surabaya', 'tgl_loading' => '2026-11-09'], // +3 days = 12/11 (earlier loading)
                        ],
                    ],
                    // Multi-detail 2: 1 Valid loading (10/11) + 1 null loading -> chooses latest valid loading (10/11 + 2 days = 12/11/2026)
                    [
                        'id' => 402,
                        'kode' => 'MOV-MULTI-TBD-MIX',
                        'tujuan_gudang' => 'Pelindo',
                        'dari_gudang' => 'Denpasar & Gresik',
                        'total_qty' => 8000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 31, 'tgl_loading' => '2026-11-10'], // +2 days = 12/11/2026
                            ['warehouse_from_id' => 50, 'tgl_loading' => null],         // TBD
                        ],
                    ],
                    // Multi-detail 3: All TBD -> returns '-' date and 'TBD' ETA
                    [
                        'id' => 403,
                        'kode' => 'MOV-MULTI-ALL-TBD',
                        'tujuan_gudang' => 'Pelindo',
                        'dari_gudang' => 'Sidoarjo',
                        'total_qty' => 4000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            ['warehouse_from_id' => 18, 'tgl_loading' => ''],
                            ['warehouse_from_id' => 48, 'tgl_loading' => null],
                        ],
                    ],
                ];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();
        $xml = $controller->xmlData(new Request())->getContent();

        // Check multi-detail 1 chose Detail B with latest tgl_loading (12/11/2026) -> ETA 13/11/2026
        // and sorted Asal Gudang chronologically ascending (Gudang Surabaya, Gudang Malang)
        $this->assertStringContainsString('<cell>MOV-MULTI-01</cell><cell>12/11/2026</cell><cell>13/11/2026</cell><cell>Gudang Surabaya, Gudang Malang</cell>', $xml);

        // Check multi-detail 2 chose Valid Detail over null
        $this->assertStringContainsString('<cell>MOV-MULTI-TBD-MIX</cell><cell>10/11/2026</cell><cell>12/11/2026</cell>', $xml);

        // Check multi-detail 3 all TBD
        $this->assertStringContainsString('<cell>MOV-MULTI-ALL-TBD</cell><cell>-</cell><cell>TBD</cell>', $xml);
    }

    /** @test */
    public function xml_data_uses_sj_generated_at_as_tie_breaker_when_loading_date_is_equal()
    {
        Cache::flush();

        $fakeClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function pelindoInboundMovements(): array
            {
                return [
                    [
                        'id' => 501,
                        'kode' => 'MOV-TIE-BREAK',
                        'tujuan_gudang' => 'Pelindo',
                        'dari_gudang' => 'Default Wh',
                        'total_qty' => 7000,
                        'logistics_status' => 'in_transit',
                        'details' => [
                            // Detail Beta is listed first but has later sj_generated_at (16:45)
                            [
                                'warehouse_from_id' => 30, // +2 days = 12/11/2026
                                'gudang_from_name' => 'Gudang Beta',
                                'tgl_loading' => '2026-11-10',
                                'sj_generated_at' => '2026-11-10 16:45:00',
                            ],
                            // Detail Alpha is listed second but has earlier sj_generated_at (08:30)
                            [
                                'warehouse_from_id' => 5,  // +1 day = 11/11/2026
                                'gudang_from_name' => 'Gudang Alpha',
                                'tgl_loading' => '2026-11-10',
                                'sj_generated_at' => '2026-11-10 08:30:00',
                            ],
                        ],
                    ],
                ];
            }
        };
        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $controller = new UpcomingInboundController();
        $xml = $controller->xmlData(new Request())->getContent();

        // 1. Asal Gudang sorted ascending by sj_generated_at: "Gudang Alpha, Gudang Beta"
        // 2. ETA selected from later sj_generated_at (Gudang Beta +2 days): ETA = 12/11/2026
        $this->assertStringContainsString(
            '<cell>MOV-TIE-BREAK</cell><cell>10/11/2026</cell><cell>12/11/2026</cell><cell>Gudang Alpha, Gudang Beta</cell>',
            $xml
        );
    }
}
