<?php

namespace Tests\Feature\CNS;

use App\BuyerContract;
use App\BuyerContractBooking;
use App\CnsCustomsClearance;
use App\CommercialInvoice;
use App\Http\Controllers\CNS\ExportChecklistController;
use App\Http\Controllers\CNS\SalesForecastController;
use App\Http\Middleware\LogUserAccess;
use App\PackingList;
use App\ShippingInstruction;
use App\User;
use App\VesselSchedule;
use App\WeightBridgeTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PipelineTrackingTest extends TestCase
{
    public function test_contract_without_bc_has_8_percent_and_3_bc_contract_uses_weighted_export_checklist_progress()
    {
        $this->bindAuthenticatedUser();
        $contracts = $this->makeSampleContracts();
        $this->bindChecklistAndForecastControllers($contracts);

        $response = $this->getJson('/cns-sales-forecast-data?year=2026');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'year',
                'monthly',
                'pipeline',
                'contract_pipeline',
                'kpis',
            ]);

        $payload = $response->json();
        $pipeline = collect($payload['contract_pipeline'])->keyBy('contract_no');

        // Contract from 2025 is filtered out; 2026 + empty/unformatted date contracts remain
        $this->assertFalse($pipeline->has('CTR-2025-OLD'));
        $this->assertTrue($pipeline->has('CTR-NEW-01'));
        $this->assertTrue($pipeline->has('CTR-NO-DATE'));
        $this->assertTrue($pipeline->has('CTR-BAD-DATE'));
        $this->assertTrue($pipeline->has('CTR-PIPE-02'));

        // Contract without BC => 1/12 = 8%
        $noBc = $pipeline->get('CTR-NEW-01');
        $this->assertSame(0, $noBc['bc_count']);
        $this->assertSame(8, $noBc['progress_pct']);
        $this->assertSame([], $noBc['bcs']);

        // Fallback contracts with empty/unformatted dates also get 8% when bc_count === 0
        $this->assertSame(8, $pipeline->get('CTR-NO-DATE')['progress_pct']);
        $this->assertSame(0, $pipeline->get('CTR-NO-DATE')['bc_count']);
        $this->assertSame(8, $pipeline->get('CTR-BAD-DATE')['progress_pct']);
        $this->assertSame(0, $pipeline->get('CTR-BAD-DATE')['bc_count']);

        // Contract with 3 BCs => equal weight 33.3% each, contract progress = avg of 3 BCs from ExportChecklistController
        // BC 1: 2/12 = 17%, BC 2: 6/12 = 50%, BC 3: 12/12 = 100% => round((17 + 50 + 100) / 3) = 56
        $threeBc = $pipeline->get('CTR-PIPE-02');
        $this->assertSame(3, $threeBc['bc_count']);
        $this->assertSame(56, $threeBc['progress_pct']);
        $this->assertCount(3, $threeBc['bcs']);

        $expectedByBc = [
            'BC-201' => 17,
            'BC-202' => 50,
            'BC-203' => 100,
        ];

        foreach ($threeBc['bcs'] as $bc) {
            $this->assertSame(
                ['booking_id', 'booking_no', 'label', 'weight_pct', 'progress_pct'],
                array_keys($bc)
            );
            $this->assertEquals(33.3, $bc['weight_pct']);
            $this->assertSame($expectedByBc[$bc['booking_no']], $bc['progress_pct']);
        }

        // Ensure no checklist stages or document names leak into contract_pipeline JSON
        $pipelineJson = json_encode($payload['contract_pipeline']);
        $this->assertNotFalse($pipelineJson);
        foreach ([
            '"stages"',
            'doc_done',
            'doc_total',
            'sub_docs',
            'Shipping Instructions',
            'Weight Bridge Ticket',
            'Final Packing List',
            'Commercial Invoice',
            'COA Surveyor',
            'Other Export Document',
            'Dokumen 3D',
            'Payment (Finance)',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $pipelineJson);
        }
    }

    public function test_sales_forecast_view_separates_tabs_and_hides_checklist_documents()
    {
        $this->bindAuthenticatedUser();
        $contracts = $this->makeSampleContracts();
        $this->bindChecklistAndForecastControllers($contracts);

        DB::shouldReceive('table')->with('menu_lists')->andReturnSelf();
        DB::shouldReceive('where')->andReturnSelf();
        DB::shouldReceive('orderBy')->andReturnSelf();
        DB::shouldReceive('get')->andReturn(collect());

        $response = $this->get('/cns-sales-forecast?year=2026');
        $response->assertStatus(200);

        // Two Bootstrap nav-tabs
        $response->assertSee('id="tab-sales-forecast-link"', false);
        $response->assertSee('id="tab-pipeline-tracking-link"', false);
        $response->assertSee('Sales Forecast');
        $response->assertSee('Pipeline Tracking');

        // Tab 1 (Sales Forecast) controls & table
        $response->assertSee('Set Target Bulanan');
        $response->assertSee('id="pipelineProgressChart"', false);
        $response->assertSee('Rincian Target &amp; Realisasi Bulanan', false);

        // Tab 2 (Pipeline Tracking) contract table & BC subtable columns (without checklist details)
        $response->assertSee('id="pipelineDetailTable"', false);
        $response->assertSee('No. Kontrak');
        $response->assertSee('Qty Kontrak (MT)');
        $response->assertSee('Jumlah BC');
        $response->assertSee('Total Progress Ekspor (%)');
        $response->assertSee('Label / No. BC');
        $response->assertSee('Bobot (%)');
        $response->assertSee('Progress Shipment (%)');

        // Contract numbers passed in initialData
        $response->assertSee('CTR-NEW-01');
        $response->assertSee('CTR-PIPE-02');

        // Old single-tab checklist document columns must not appear
        $response->assertDontSee('Kelengkapan Dokumen');
        $response->assertDontSee('Pembayaran Buyer');
        $response->assertDontSee('doc_done');
    }

    private function bindAuthenticatedUser(): void
    {
        $this->withoutMiddleware(LogUserAccess::class);

        Gate::before(function () {
            return true;
        });

        $user = new User();
        $user->id = 1;
        $user->username = 'cns_pipeline_tester';
        $user->Name = 'CNS Pipeline Tester';
        $user->Priv_ID = 'ADM';
        $user->Sub_Menu = 'SM00058,SS00084,SI00354';
        $this->be($user);
    }

    private function makeSampleContracts(): Collection
    {
        $c1 = (new BuyerContract())->forceFill([
            'ID'           => 1,
            'Contract_No'  => 'CTR-NEW-01',
            'Buyer_Name'   => 'Buyer Alpha',
            'Quantity_MT'  => 100.0,
            'Status'       => 'UNDER_REVIEW',
            'Created_Date' => '2026-11-01 08:00:00',
        ]);
        $c1->setRelation('bookings', collect());

        $c2 = (new BuyerContract())->forceFill([
            'ID'           => 2,
            'Contract_No'  => 'CTR-PIPE-02',
            'Buyer_Name'   => 'Buyer Beta',
            'Quantity_MT'  => 300.0,
            'Status'       => 'ACTIVE_IN_PROGRESS',
            'Created_Date' => '2026-11-10 08:00:00',
        ]);

        $b201 = (new BuyerContractBooking())->forceFill([
            'ID'               => 201,
            'Contract_ID'      => 2,
            'Label'            => 'BC 1',
            'Booking_No'       => 'BC-201',
            'Created_Date'     => '2026-11-11 09:00:00',
            'Payment_Approved' => 0,
        ]);
        $b202 = (new BuyerContractBooking())->forceFill([
            'ID'               => 202,
            'Contract_ID'      => 2,
            'Label'            => 'BC 2',
            'Booking_No'       => 'BC-202',
            'Created_Date'     => '2026-11-12 09:00:00',
            'Payment_Approved' => 0,
        ]);
        $b203 = (new BuyerContractBooking())->forceFill([
            'ID'               => 203,
            'Contract_ID'      => 2,
            'Label'            => 'BC 3',
            'Booking_No'       => 'BC-203',
            'Created_Date'     => '2026-11-13 09:00:00',
            'Payment_Approved' => 1,
        ]);
        $c2->setRelation('bookings', collect([$b201, $b202, $b203]));

        $c3 = (new BuyerContract())->forceFill([
            'ID'           => 3,
            'Contract_No'  => 'CTR-2025-OLD',
            'Buyer_Name'   => 'Buyer Old',
            'Quantity_MT'  => 50.0,
            'Status'       => 'COMPLETED',
            'Created_Date' => '2025-10-15 08:00:00',
        ]);
        $c3->setRelation('bookings', collect());

        $c4 = (new BuyerContract())->forceFill([
            'ID'           => 4,
            'Contract_No'  => 'CTR-NO-DATE',
            'Buyer_Name'   => 'Buyer Fallback Empty',
            'Quantity_MT'  => 75.0,
            'Status'       => 'PENDING_SIGNATURE',
            'Created_Date' => null,
        ]);
        $c4->setRelation('bookings', collect());

        $c5 = (new BuyerContract())->forceFill([
            'ID'           => 5,
            'Contract_No'  => 'CTR-BAD-DATE',
            'Buyer_Name'   => 'Buyer Fallback Unformatted',
            'Quantity_MT'  => 80.0,
            'Status'       => 'UNDER_REVIEW',
            'Created_Date' => 'invalid-date',
        ]);
        $c5->setRelation('bookings', collect());

        return collect([$c1, $c2, $c3, $c4, $c5]);
    }

    private function bindChecklistAndForecastControllers(Collection $contracts): void
    {
        $fakeChecklist = new class extends ExportChecklistController {
            protected function fetchProgressSources(Collection $bookings): array
            {
                return [
                    'vesselSchedules' => collect([
                        (new VesselSchedule())->forceFill([
                            'id'                             => 502,
                            'Booking_ID'                     => 202,
                            'Booking_No'                     => 'BC-202',
                            'Order_No'                       => 'ORD-202',
                            'Checklist_Done_Kapal_Berangkat' => 1,
                        ]),
                        (new VesselSchedule())->forceFill([
                            'id'                             => 503,
                            'Booking_ID'                     => 203,
                            'Booking_No'                     => 'BC-203',
                            'Order_No'                       => 'ORD-203',
                            'Checklist_Done_Kapal_Berangkat' => 1,
                        ]),
                    ]),
                    'containerCounts' => collect([
                        'ORD-202' => 2,
                        'ORD-203' => 4,
                    ]),
                    'shippingInstructions' => collect([
                        (new ShippingInstruction())->forceFill([
                            'SI_No'       => 'SI-202',
                            'Final_SI_No' => 'FSI-202',
                            'Order_No'    => 'ORD-202',
                        ]),
                        (new ShippingInstruction())->forceFill([
                            'SI_No'       => 'SI-203',
                            'Final_SI_No' => 'FSI-203',
                            'Order_No'    => 'ORD-203',
                        ]),
                    ]),
                    'clearances' => collect([
                        (new CnsCustomsClearance())->forceFill([
                            'id'         => 12,
                            'booking_id' => 202,
                            'doc_3d_path'=> 'uploads/3d-202.pdf',
                            'doc_3d_name'=> '3d-202.pdf',
                            'peb_path'   => 'uploads/peb-202.pdf',
                            'peb_name'   => 'peb-202.pdf',
                            'npe_path'   => null,
                        ]),
                        (new CnsCustomsClearance())->forceFill([
                            'id'         => 13,
                            'booking_id' => 203,
                            'doc_3d_path'=> 'uploads/3d-203.pdf',
                            'doc_3d_name'=> '3d-203.pdf',
                            'peb_path'   => 'uploads/peb-203.pdf',
                            'peb_name'   => 'peb-203.pdf',
                            'npe_path'   => 'uploads/npe-203.pdf',
                            'npe_name'   => 'npe-203.pdf',
                        ]),
                    ]),
                    'pls' => collect([
                        (new PackingList())->forceFill([
                            'Packing_List_No' => 'PL-203',
                            'SI_No'           => 'SI-203',
                            'Final_SI_No'     => 'FSI-203',
                            'Status'          => 'FINAL',
                        ]),
                    ]),
                    'cis' => collect([
                        (new CommercialInvoice())->forceFill([
                            'Inv_No' => 'INV-203',
                            'SI_No'  => 'SI-203',
                        ]),
                    ]),
                    'wbts' => collect([
                        (new WeightBridgeTicket())->forceFill([
                            'WBT_No' => 'WBT-203',
                            'SI_No'  => 'SI-203',
                        ]),
                    ]),
                    'sds'               => collect(),
                    'dns'               => collect(),
                    'annexes'           => collect(),
                    'invoicingRequests' => collect(),
                    'coaSurveyors'      => collect([
                        (object) [
                            'id'              => 903,
                            'Booking_ID'      => 203,
                            'Kode_QC'         => 'COA-203',
                            'Inspection_Date' => '2026-11-20',
                            'Attachment_Path' => 'uploads/coa-203.pdf',
                            'QC_Type'         => 'SURVEYOR',
                            'Is_Deleted'      => 0,
                        ],
                    ]),
                ];
            }
        };

        $this->app->instance(ExportChecklistController::class, $fakeChecklist);

        $testForecastController = new class($contracts) extends SalesForecastController {
            private $contracts;

            public function __construct(Collection $contracts)
            {
                $this->contracts = $contracts;
            }

            protected function loadContractsForPipeline(): Collection
            {
                return $this->contracts;
            }

            protected function fetchMonthlyForecasts(string $yearPrefix): array
            {
                return [];
            }

            protected function fetchMonthlyStuffing(string $yearPrefix): array
            {
                return ['by_month' => [], 'by_booking' => []];
            }

            protected function fetchMonthlyBcPipeline(string $yearPrefix, array $stuffingByBooking): array
            {
                return [
                    'categories'     => [],
                    'monthly_totals' => [],
                    'monthly_values' => [],
                    'breakdown_rows' => [],
                ];
            }
        };

        $this->app->instance(SalesForecastController::class, $testForecastController);
    }
}
