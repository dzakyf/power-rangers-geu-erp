<?php

namespace Tests\Feature\CNS;

use App\BuyerContract;
use App\BuyerContractBooking;
use App\CnsShipmentCostAllocation;
use App\Http\Controllers\CNS\ExportVolumeDashboardController;
use App\Http\Middleware\LogUserAccess;
use App\Services\CNS\CnsPermissionService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExportVolumeDashboardTest extends TestCase
{
    public function test_menu_renamed_to_export_performance_and_si00355_removed()
    {
        $menus = require base_path('database/seeds/data/menu_lists_cns.php');

        $this->assertArrayNotHasKey('SI00355', $menus);
        $this->assertArrayHasKey('SI01150', $menus);
        $this->assertSame(
            ['Export Performance', 'cns-dashboard-export-volume', 'CNS', 4, 'SS00150', 1],
            $menus['SI01150']
        );

        $this->assertNull(CnsPermissionService::getSlug('SI00355'));
        $this->assertSame('export_volume', CnsPermissionService::getSlug('SI01150'));
        $this->assertSame('SI01150', CnsPermissionService::getMenuId('export_volume'));
        $this->assertSame('cns.export_volume.view', CnsPermissionService::permissionName('SI01150', 'view'));

        $routes = collect(Route::getRoutes()->getRoutes());
        $uris = $routes->map(function ($r) {
            return $r->uri();
        })->all();

        $this->assertNotContains('cns-dashboard-export-performance', $uris);
        $this->assertNotContains('cns-dashboard-export-performance-data', $uris);
        $this->assertNotContains('cns-dashboard-export-performance-excel', $uris);

        foreach ([
            'cns-dashboard-export-volume',
            'cns-dashboard-export-volume-data',
            'cns-dashboard-export-volume-excel',
        ] as $uri) {
            $route = $routes->first(function ($r) use ($uri) {
                return $r->uri() === $uri && in_array('GET', $r->methods(), true);
            });
            $this->assertNotNull($route, "Route GET {$uri} not found");
            $this->assertContains('can:cns.export_volume.view', $route->gatherMiddleware());
        }
    }

    public function test_export_volume_blade_view_compiles_with_performance_kpis_and_columns()
    {
        $this->assertTrue(view()->exists('cns.dashboard.export-volume'));

        $raw = file_get_contents(resource_path('views/cns/dashboard/export-volume.blade.php'));
        $this->assertStringNotContainsString('JudulContent', $raw);
        $this->assertStringNotContainsString('hdr_back.gif', $raw);
        $this->assertStringContainsString('cns.partials.page-header', $raw);
        $this->assertStringContainsString('applyFilters', $raw);
        $this->assertStringContainsString('resetFilters', $raw);
        $this->assertStringContainsString('renderTable', $raw);

        $compiled = app('blade.compiler')->compileString($raw);
        $this->assertNotEmpty($compiled);
        $this->assertStringContainsString('Dashboard Export Performance', $compiled);
        $this->assertStringContainsString('TOTAL SI (PROGRESS)', $compiled);
        $this->assertStringContainsString('TOTAL VOLUME (MT)', $compiled);
        $this->assertStringContainsString('TOTAL VALUE (USD)', $compiled);
        $this->assertStringContainsString('TOTAL MARGIN (USD', $compiled);
        $this->assertStringContainsString('Total Kontainer (Box)', $compiled);
        $this->assertStringContainsString('Total Draft / Pending', $compiled);
        $this->assertStringContainsString('Status Checklist', $compiled);
        $this->assertStringContainsString('Value (USD)', $compiled);
        $this->assertStringContainsString('Cost (USD)', $compiled);
        $this->assertStringContainsString('Margin (USD', $compiled);
        $this->assertStringContainsString('Checklist', $compiled);
    }

    public function test_volume_value_cost_margin_and_checklist_filters_calculate_accurately()
    {
        $source = $this->makeSampleSourceData();
        $controller = new ExportVolumeDashboardController();

        // 1. All shipments
        $all = $controller->buildDashboardPayload($source, '2026', 'all', 'all', 'all', 'all', 'all');

        $this->assertSame(3, $all['kpis']['total_shipments']);
        $this->assertSame(5, $all['kpis']['total_containers']);
        $this->assertSame(1, $all['kpis']['total_pending']);
        $this->assertEquals(130.5, $all['kpis']['total_volume_mt']);     // 80.5 + 40.0 + 10.0
        $this->assertEquals(150625.0, $all['kpis']['total_value_usd']);  // 100625 + 50000 + 0
        $this->assertEquals(21000.0, $all['kpis']['total_cost_usd']);    // 12000 + 8000 + 1000
        $this->assertEquals(129625.0, $all['kpis']['total_margin_usd']); // 150625 - 21000
        $this->assertEquals(86.1, $all['kpis']['avg_margin_pct']);       // round(129625/150625*100, 1)

        // Monthly trend includes value_usd & margin_usd (Oct = index 9, Nov = index 10)
        $this->assertCount(12, $all['monthlyTrend']['value_usd']);
        $this->assertCount(12, $all['monthlyTrend']['margin_usd']);
        $this->assertEquals(100625.0, $all['monthlyTrend']['value_usd'][9]);
        $this->assertEquals(88625.0, $all['monthlyTrend']['margin_usd'][9]);
        $this->assertEquals(50000.0, $all['monthlyTrend']['value_usd'][10]);
        $this->assertEquals(41000.0, $all['monthlyTrend']['margin_usd'][10]); // 42000 + (-1000)

        $bySi = collect($all['shipments'])->keyBy('si_no');

        // SI/001: completed 14/14, matched cost by bc_no (not double-counted by si_id)
        $this->assertSame('completed', $bySi['SI/001']['checklist_status']);
        $this->assertSame(14, $bySi['SI/001']['completed_stages']);
        $this->assertSame(14, $bySi['SI/001']['total_stages']);
        $this->assertEquals(80.5, $bySi['SI/001']['volume_mt']);
        $this->assertEquals(100625.0, $bySi['SI/001']['value_usd']);
        $this->assertEquals(12000.0, $bySi['SI/001']['cost_usd']);
        $this->assertEquals(88625.0, $bySi['SI/001']['margin_usd']);
        $this->assertEquals(88.1, $bySi['SI/001']['margin_pct']);

        // SI/002: in_progress 8/14, cost matched via fallback si_id
        $this->assertSame('in_progress', $bySi['SI/002']['checklist_status']);
        $this->assertSame(8, $bySi['SI/002']['completed_stages']);
        $this->assertSame(14, $bySi['SI/002']['total_stages']);
        $this->assertEquals(40.0, $bySi['SI/002']['volume_mt']);
        $this->assertEquals(50000.0, $bySi['SI/002']['value_usd']);
        $this->assertEquals(8000.0, $bySi['SI/002']['cost_usd']);
        $this->assertEquals(42000.0, $bySi['SI/002']['margin_usd']);
        $this->assertEquals(84.0, $bySi['SI/002']['margin_pct']);

        // SI/003: no BC -> checklist_status '-' & value 0
        $this->assertSame('-', $bySi['SI/003']['checklist_status']);
        $this->assertSame(0, $bySi['SI/003']['completed_stages']);
        $this->assertSame(0, $bySi['SI/003']['total_stages']);
        $this->assertEquals(10.0, $bySi['SI/003']['volume_mt']);
        $this->assertEquals(0.0, $bySi['SI/003']['value_usd']);
        $this->assertEquals(1000.0, $bySi['SI/003']['cost_usd']);
        $this->assertEquals(-1000.0, $bySi['SI/003']['margin_usd']);
        $this->assertEquals(0.0, $bySi['SI/003']['margin_pct']);

        // 2. Filter status = completed
        $completedOnly = $controller->buildDashboardPayload($source, '2026', 'all', 'all', 'all', 'all', 'completed');
        $this->assertCount(1, $completedOnly['shipments']);
        $this->assertSame('SI/001', $completedOnly['shipments'][0]['si_no']);
        $this->assertEquals(80.5, $completedOnly['kpis']['total_volume_mt']);
        $this->assertEquals(100625.0, $completedOnly['kpis']['total_value_usd']);
        $this->assertEquals(12000.0, $completedOnly['kpis']['total_cost_usd']);
        $this->assertEquals(88625.0, $completedOnly['kpis']['total_margin_usd']);
        $this->assertEquals(88.1, $completedOnly['kpis']['avg_margin_pct']);
        $this->assertEquals(0.0, $completedOnly['monthlyTrend']['value_usd'][10]);

        // 3. Filter status = in_progress
        $inProgressOnly = $controller->buildDashboardPayload($source, '2026', 'all', 'all', 'all', 'all', 'in_progress');
        $this->assertCount(1, $inProgressOnly['shipments']);
        $this->assertSame('SI/002', $inProgressOnly['shipments'][0]['si_no']);
        $this->assertEquals(40.0, $inProgressOnly['kpis']['total_volume_mt']);
        $this->assertEquals(50000.0, $inProgressOnly['kpis']['total_value_usd']);
        $this->assertEquals(8000.0, $inProgressOnly['kpis']['total_cost_usd']);
        $this->assertEquals(42000.0, $inProgressOnly['kpis']['total_margin_usd']);
        $this->assertEquals(84.0, $inProgressOnly['kpis']['avg_margin_pct']);
    }

    public function test_http_endpoints_render_view_json_and_csv_with_status_filter()
    {
        $this->withoutMiddleware(LogUserAccess::class);
        $source = $this->makeSampleSourceData();

        $testController = new class($source) extends ExportVolumeDashboardController {
            private $source;

            public function __construct(array $source)
            {
                $this->source = $source;
            }

            protected function loadSourceData(): array
            {
                return $this->source;
            }
        };

        $this->app->instance(ExportVolumeDashboardController::class, $testController);

        Gate::before(function () {
            return true;
        });

        $user = new User();
        $user->id = 1;
        $user->username = 'admin';
        $user->Name = 'Admin';
        $user->Priv_ID = 'ADM';
        $user->Sub_Menu = 'SM00080,SS00150,SI01150';
        $this->be($user);

        DB::shouldReceive('table')->with('menu_lists')->andReturnSelf();
        DB::shouldReceive('where')->andReturnSelf();
        DB::shouldReceive('orderBy')->andReturnSelf();
        DB::shouldReceive('get')->andReturn(collect());

        // 1. HTML view
        $htmlRes = $this->get('/cns-dashboard-export-volume?year=all&month=all');
        $htmlRes->assertStatus(200);
        $htmlRes->assertSee('Dashboard Export Performance');
        $htmlRes->assertSee('150,625.00');
        $htmlRes->assertSee('129,625.00');
        $htmlRes->assertSee('86.1%');
        $htmlRes->assertSee('Rotterdam');
        $htmlRes->assertSee('SI\\/001', false);

        // 2. JSON AJAX endpoint filtered by status=completed
        $jsonCompleted = $this->getJson('/cns-dashboard-export-volume-data?year=all&month=all&status=completed');
        $jsonCompleted->assertStatus(200)
            ->assertJsonPath('kpis.total_shipments', 1)
            ->assertJsonPath('kpis.total_volume_mt', 80.5)
            ->assertJsonPath('kpis.total_value_usd', 100625)
            ->assertJsonPath('kpis.total_cost_usd', 12000)
            ->assertJsonPath('kpis.total_margin_usd', 88625)
            ->assertJsonPath('kpis.avg_margin_pct', 88.1)
            ->assertJsonPath('shipments.0.si_no', 'SI/001')
            ->assertJsonPath('shipments.0.checklist_status', 'completed');

        // 3. JSON AJAX endpoint filtered by status=in_progress
        $jsonProgress = $this->getJson('/cns-dashboard-export-volume-data?year=all&month=all&status=in_progress');
        $jsonProgress->assertStatus(200)
            ->assertJsonPath('kpis.total_shipments', 1)
            ->assertJsonPath('kpis.total_volume_mt', 40)
            ->assertJsonPath('kpis.total_value_usd', 50000)
            ->assertJsonPath('shipments.0.si_no', 'SI/002')
            ->assertJsonPath('shipments.0.checklist_status', 'in_progress');

        // 4. CSV Export endpoint
        $csvRes = $this->get('/cns-dashboard-export-volume-excel?year=all&month=all&status=completed');
        $csvRes->assertStatus(200);
        $csv = $csvRes->streamedContent();
        $this->assertStringContainsString('Checklist', $csv);
        $this->assertStringContainsString('Value (USD)', $csv);
        $this->assertStringContainsString('Cost (USD)', $csv);
        $this->assertStringContainsString('Margin (USD)', $csv);
        $this->assertStringContainsString('Margin (%)', $csv);
        $this->assertStringContainsString('SI/001', $csv);
        $this->assertStringContainsString('Completed (14/14)', $csv);
        $this->assertStringContainsString('100625.00', $csv);
        $this->assertStringContainsString('12000.00', $csv);
        $this->assertStringContainsString('88625.00', $csv);
        $this->assertStringContainsString('88.1', $csv);
        $this->assertStringNotContainsString('SI/002', $csv);
    }

    private function makeSampleSourceData(): array
    {
        $contract1 = (new BuyerContract())->forceFill([
            'ID'           => 1,
            'Contract_No'  => 'CTR-001',
            'Buyer_Name'   => 'PT Alpha Oils',
            'Price_Per_MT' => 1250.00,
        ]);

        $booking1 = (new BuyerContractBooking())->forceFill([
            'ID'          => 101,
            'Contract_ID' => 1,
            'Booking_No'  => 'BC-001',
        ]);
        $booking1->setRelation('contract', $contract1);

        $booking2 = (new BuyerContractBooking())->forceFill([
            'ID'          => 102,
            'Contract_ID' => 1,
            'Booking_No'  => 'BC-002',
        ]);
        $booking2->setRelation('contract', $contract1);

        $rows = collect([
            (object) [
                'id'               => 10,
                'SI_No'            => 'SI/001',
                'Final_SI_No'      => 'FSI/001',
                'Final_SI_Date'    => '2026-10-12',
                'SI_Date'          => '2026-10-10',
                'si_etd'           => '2026-10-14',
                'BL_No'            => 'BL-001',
                'Consignee'        => 'SUP-01',
                'Destination_Port' => 'Rotterdam',
                'si_pod'           => 'Rotterdam',
                'Delivery_Terms'   => 'CIF',
                'si_qty'           => null,
                'Status'           => 'COMPLETED',
                'si_order_no'      => 'ORD-001',
                'vs_etd'           => '2026-10-14',
                'vs_atd'           => '2026-10-15',
                'vs_pod'           => 'Rotterdam',
                'vs_incoterms'     => 'CIF',
                'vs_booking_no'    => 'BC-001',
                'vs_bc_no'         => null,
                'vs_booking_id'    => null,
            ],
            (object) [
                'id'               => 11,
                'SI_No'            => 'SI/002',
                'Final_SI_No'      => null,
                'Final_SI_Date'    => null,
                'SI_Date'          => '2026-11-10',
                'si_etd'           => '2026-11-12',
                'BL_No'            => null,
                'Consignee'        => 'SUP-01',
                'Destination_Port' => 'Singapore',
                'si_pod'           => 'Singapore',
                'Delivery_Terms'   => 'FOB',
                'si_qty'           => null,
                'Status'           => 'APPROVED',
                'si_order_no'      => 'BC-002',
                'vs_etd'           => null,
                'vs_atd'           => null,
                'vs_pod'           => null,
                'vs_incoterms'     => null,
                'vs_booking_no'    => null,
                'vs_bc_no'         => null,
                'vs_booking_id'    => null,
            ],
            (object) [
                'id'               => 12,
                'SI_No'            => 'SI/003',
                'Final_SI_No'      => null,
                'Final_SI_Date'    => null,
                'SI_Date'          => '2026-11-18',
                'si_etd'           => null,
                'BL_No'            => null,
                'Consignee'        => 'SUP-02',
                'Destination_Port' => 'Hamburg',
                'si_pod'           => null,
                'Delivery_Terms'   => 'FOB',
                'si_qty'           => '10 MT',
                'Status'           => 'DRAFT',
                'si_order_no'      => 'ORD-NO-BC',
                'vs_etd'           => null,
                'vs_atd'           => null,
                'vs_pod'           => null,
                'vs_incoterms'     => null,
                'vs_booking_no'    => null,
                'vs_bc_no'         => null,
                'vs_booking_id'    => null,
            ],
        ]);

        $vsdData = [
            'SI/001' => ['netto_mt' => 80.5, 'containers' => 3],
            'SI/002' => ['netto_mt' => 40.0, 'containers' => 2],
        ];

        $consigneeMap = [
            'SUP-01' => 'PT Alpha Oils',
            'SUP-02' => 'Beta Trading',
        ];

        $progressByBookingId = [
            101 => ['stages' => $this->makeStages(14)],
            102 => ['stages' => $this->makeStages(8)],
        ];

        // Allocation 1 has BOTH bc_no and si_id (must not double-count)
        $alloc1 = (new CnsShipmentCostAllocation())->forceFill([
            'id'             => 1,
            'bc_no'          => 'BC-001',
            'si_id'          => 10,
            'total_cost_usd' => 12000.00,
        ]);

        // Allocation 2 has only si_id (legacy fallback)
        $alloc2 = (new CnsShipmentCostAllocation())->forceFill([
            'id'             => 2,
            'bc_no'          => null,
            'si_id'          => 11,
            'total_cost_usd' => 8000.00,
        ]);

        // Allocation 3 for SI without BC
        $alloc3 = (new CnsShipmentCostAllocation())->forceFill([
            'id'             => 3,
            'bc_no'          => null,
            'si_id'          => 12,
            'total_cost_usd' => 1000.00,
        ]);

        return [
            'rows'                => $rows,
            'vsdData'             => $vsdData,
            'consigneeMap'        => $consigneeMap,
            'bookings'            => collect([$booking1, $booking2]),
            'progressByBookingId' => $progressByBookingId,
            'costAllocations'     => collect([$alloc1, $alloc2, $alloc3]),
        ];
    }

    private function makeStages(int $completedCount): array
    {
        $keys = [
            'contract', 'booking', 'vs', 'stuffing', 'si', 'fsi', 'pl',
            'ci', 'wbt', 'coo', 'sd', 'dn', 'annex', 'payment',
        ];
        $stages = [];
        foreach ($keys as $idx => $key) {
            $stages[$key] = ['exists' => $idx < $completedCount];
        }

        return $stages;
    }
}
