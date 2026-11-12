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

        DB::beginTransaction();
        try {
            UCOStoragePelindo::create([
                'Type'             => 'inbound',
                'Quantity'         => 1000,
                'Transaction_Date' => '2026-11-10',
                'Reference_Number' => $existingKode,
                'Created_By'       => 'PHPUnit',
            ]);

            $fakeClient = new class($this->createMock(ApipiAuthService::class), $existingKode, $openKode, $doneKode, $nullKode) extends ApipiApiClient {
                private $rows;
                public function __construct($auth, $existingKode, $openKode, $doneKode, $nullKode)
                {
                    parent::__construct($auth);
                    $this->rows = [
                        ['id' => 1, 'kode' => $existingKode, 'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Surabaya', 'tgl' => '2026-11-10', 'total_qty' => 1000, 'logistics_status' => 'in_transit'],
                        ['id' => 2, 'kode' => $openKode,     'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Malang',   'tgl' => '2026-11-11', 'total_qty' => 19526, 'logistics_status' => 'in_transit'],
                        ['id' => 3, 'kode' => $doneKode,     'tujuan_gudang' => 'Final Storage Pelindo', 'dari_gudang' => 'Sidoarjo', 'tgl' => '2026-11-11', 'total_qty' => 5000,  'logistics_status' => 'DONE'],
                        ['id' => 4, 'kode' => $nullKode,     'tujuan_gudang' => 'Pelindo Gresik',        'dari_gudang' => 'Denpasar', 'tgl' => '2026-11-12', 'total_qty' => 8500,  'logistics_status' => null],
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
            $this->assertStringNotContainsString($existingKode, $output);
            $this->assertStringNotContainsString($doneKode, $output);
            $this->assertStringNotContainsString('INB-2026-001', $output);

            // Filter by date and search
            $filtered = $controller->xmlData(new Request([
                'start_date' => '2026-11-12',
                'end_date'   => '2026-11-12',
                'search'     => 'Denpasar',
            ]))->getContent();

            $this->assertStringContainsString($nullKode, $filtered);
            $this->assertStringNotContainsString($openKode, $filtered);
        } finally {
            DB::rollBack();
        }
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
        $this->assertStringContainsString('<rows total_count="12" page="1" per_page="10" total_pages="2">', $page1);
        $this->assertStringContainsString('<cell>1</cell><cell>MOV-PG-01</cell>', $page1);
        $this->assertStringContainsString('MOV-PG-10', $page1);
        $this->assertStringNotContainsString('MOV-PG-11', $page1);

        // Page 2 uses cached movements and continues row numbering at 11
        $page2 = $controller->xmlData(new Request(['page' => 2, 'per_page' => 10]))->getContent();
        $this->assertSame(1, $fakeClient->calls);
        $this->assertStringContainsString('<rows total_count="12" page="2" per_page="10" total_pages="2">', $page2);
        $this->assertStringContainsString('<cell>11</cell><cell>MOV-PG-11</cell>', $page2);
        $this->assertStringContainsString('<cell>12</cell><cell>MOV-PG-12</cell>', $page2);
        $this->assertStringNotContainsString('MOV-PG-10', $page2);

        // Invalid per_page falls back to 25 and clamps out-of-range page to 1
        $invalidPerPage = $controller->xmlData(new Request(['page' => 99, 'per_page' => 999]))->getContent();
        $this->assertSame(1, $fakeClient->calls);
        $this->assertStringContainsString('<rows total_count="12" page="1" per_page="25" total_pages="1">', $invalidPerPage);

        // Refresh=1 clears cache and fetches fresh data
        $refreshed = $controller->xmlData(new Request(['refresh' => '1']))->getContent();
        $this->assertSame(2, $fakeClient->calls);
        $this->assertStringContainsString('<rows total_count="1" page="1" per_page="25" total_pages="1">', $refreshed);
        $this->assertStringContainsString('MOV-PG-NEW', $refreshed);
        $this->assertStringNotContainsString('MOV-PG-01', $refreshed);
    }
}
