<?php

namespace Tests\Unit\CNS;

use App\Http\Controllers\Partner\MasterDataController;
use App\Services\CNS\KemasanInventoryService;
use App\Services\ExternalApi\ApipiApiClient;
use App\Services\ExternalApi\ApipiAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class ApipiWarehousesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** @test */
    public function cached_warehouses_filters_active_caches_and_supports_type_and_search()
    {
        $calls = 0;
        $fakeClient = new class($this->createMock(ApipiAuthService::class), $calls) extends ApipiApiClient {
            public $calls;
            public function __construct($auth, &$calls)
            {
                parent::__construct($auth);
                $this->calls = &$calls;
            }
            public function warehouses(int $page = 1, int $limit = 200, ?string $type = null, ?string $search = null): array
            {
                $this->calls++;
                return [
                    'data' => [
                        ['id' => 18, 'name' => 'Malang', 'type' => 'site', 'active' => 1],
                        ['id' => 43, 'name' => 'Final Storage Pelindo', 'type' => 'non-site', 'active' => 1],
                        ['id' => 5, 'name' => 'Surabaya', 'type' => 'hybrid', 'active' => 1],
                        ['id' => 48, 'name' => 'Transit Sidoarjo', 'type' => 'hybrid', 'active' => 1],
                        ['id' => 99, 'name' => 'Inactive Site', 'type' => 'site', 'active' => 0],
                        ['id' => 101, 'name' => 'Deleted Site', 'type' => 'site', 'active' => 1, 'deleted_at' => '2026-01-01 00:00:00'],
                    ],
                    'pagination' => ['has_next' => false],
                ];
            }
        };

        $this->app->instance(ApipiApiClient::class, $fakeClient);

        $all = $fakeClient->cachedWarehouses();
        $this->assertCount(4, $all);
        $this->assertSame(['Final Storage Pelindo', 'Malang', 'Surabaya', 'Transit Sidoarjo'], array_column($all, 'name'));

        // Report warehouse list includes type=site + Surabaya only (excludes non-site and other hybrids)
        $reportOnly = $fakeClient->reportWarehouses();
        $this->assertSame(['Malang', 'Surabaya'], array_column($reportOnly, 'name'));

        // Second call uses 15-minute cache without hitting upstream again
        $sitesOnly = $fakeClient->cachedWarehouses('site');
        $this->assertSame(1, $calls);
        $this->assertCount(1, $sitesOnly);
        $this->assertSame('Malang', $sitesOnly[0]['name']);

        $search = $fakeClient->cachedWarehouses('site,hybrid', 'sura');
        $this->assertCount(1, $search);
        $this->assertSame(5, $search[0]['id']);

        // Dropdown getSites() returns all active warehouses; getReportSites() returns site + Surabaya
        $this->assertSame([
            43 => 'Final Storage Pelindo',
            18 => 'Malang',
            5 => 'Surabaya',
            48 => 'Transit Sidoarjo',
        ], KemasanInventoryService::getSites());
        $this->assertSame([18 => 'Malang', 5 => 'Surabaya'], KemasanInventoryService::getReportSites());

        // Controller endpoint returns all active warehouses by default and still supports ?type=
        $ctrl = new MasterDataController();
        $defaultResp = $ctrl->getCnsMasterWarehouses(Request::create('/cns-master-warehouses', 'GET'));
        $defaultJson = json_decode($defaultResp->getContent(), true);
        $this->assertSame(['Final Storage Pelindo', 'Malang', 'Surabaya', 'Transit Sidoarjo'], array_column($defaultJson['data'], 'name'));

        $resp = $ctrl->getCnsMasterWarehouses(Request::create('/cns-master-warehouses', 'GET', ['type' => 'hybrid']));
        $json = json_decode($resp->getContent(), true);
        $this->assertSame('success', $json['status']);
        $this->assertCount(2, $json['data']);
        $this->assertSame(['Surabaya', 'Transit Sidoarjo'], array_column($json['data'], 'name'));
    }

    /** @test */
    public function cached_warehouses_falls_back_to_stale_cache_then_static_sites_on_error()
    {
        $failingClient = new class($this->createMock(ApipiAuthService::class)) extends ApipiApiClient {
            public function warehouses(int $page = 1, int $limit = 200, ?string $type = null, ?string $search = null): array
            {
                throw new RuntimeException('upstream down');
            }
        };
        $this->app->instance(ApipiApiClient::class, $failingClient);

        // With stale cache available, returns stale rows
        Cache::put('apipi.warehouses.stale', [
            ['id' => 24, 'name' => 'Cakung', 'type' => 'site', 'active' => 1],
        ], now()->addDay());

        $stale = $failingClient->cachedWarehouses();
        $this->assertCount(1, $stale);
        $this->assertSame([24 => 'Cakung'], KemasanInventoryService::getSites());

        // With both fresh and stale cache empty, getSites() falls back to static SITES
        Cache::flush();
        $this->assertSame(KemasanInventoryService::SITES, KemasanInventoryService::getSites());
    }
}
