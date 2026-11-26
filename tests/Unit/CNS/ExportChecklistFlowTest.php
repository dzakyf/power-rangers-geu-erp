<?php

namespace Tests\Unit\CNS;

use App\AnnexVII;
use App\BuyerContract;
use App\BuyerContractBooking;
use App\CnsCustomsClearance;
use App\CommercialInvoice;
use App\DeliveryNote;
use App\Http\Controllers\CNS\ExportChecklistController;
use App\PackingList;
use App\ShippingInstruction;
use App\SustainabilityDeclaration;
use App\VesselSchedule;
use App\WeightBridgeTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ExportChecklistFlowTest extends TestCase
{
    private const EXPECTED_STAGE_KEYS = [
        'contract',
        'si',
        'booking',
        'doc_3d',
        'stuffing',
        'wbt',
        'peb',
        'npe',
        'fpl',
        'invoice',
        'coa_surveyor',
        'other_export_doc',
        'payment',
    ];

    public function test_stage_order_is_exactly_13_stages_and_customs_clearance_links_resolve(): void
    {
        $this->assertSame(self::EXPECTED_STAGE_KEYS, array_keys(ExportChecklistController::STAGE_CODES));

        $booking = $this->makeBooking(1, 'BC-001', 'CTR-001');
        $sources = $this->emptySources();
        $sources['shippingInstructions'] = collect([
            (new ShippingInstruction())->forceFill([
                'SI_No'       => 'SI-001',
                'Final_SI_No' => null,
                'Order_No'    => 'BC-001',
                'SI_Date'     => '2026-11-20',
                'Status'      => 'DRAFT',
            ]),
        ]);
        $sources['clearances'] = collect([
            (new CnsCustomsClearance())->forceFill([
                'id'          => 77,
                'booking_id'  => 1,
                'doc_3d_path' => 'uploads/customs-clearance/doc_3d.pdf',
                'doc_3d_name' => 'doc-3d.pdf',
                'peb_path'    => 'uploads/customs-clearance/peb.pdf',
                'peb_name'    => 'peb.pdf',
                'npe_path'    => null,
                'npe_name'    => null,
                'updated_at'  => '2026-11-24 10:00:00',
            ]),
        ]);

        $controller = $this->makeController($sources);
        $progress = $controller->buildProgressData(collect([$booking]))[1];
        $stages = $progress['stages'];

        $this->assertSame(self::EXPECTED_STAGE_KEYS, array_keys($stages));

        // SI complete even when ShippingInstruction is DRAFT
        $this->assertTrue($stages['si']['exists']);
        $this->assertSame('SI-001', $stages['si']['doc_no']);

        // 3D & PEB linked to CnsCustomsClearance; NPE missing
        $this->assertTrue($stages['doc_3d']['exists']);
        $this->assertSame('/cns-export-customs-clearance-file/77/doc_3d', $stages['doc_3d']['preview_url']);
        $this->assertTrue($stages['peb']['exists']);
        $this->assertSame('/cns-export-customs-clearance-file/77/peb', $stages['peb']['preview_url']);
        $this->assertFalse($stages['npe']['exists']);
        $this->assertNull($stages['npe']['preview_url']);

        // COA Surveyor stub defaults to incomplete
        $this->assertFalse($stages['coa_surveyor']['exists']);
    }

    public function test_other_export_doc_filters_only_approved_documents_and_isolates_annex_by_si(): void
    {
        $booking1 = $this->makeBooking(1, 'BC-101', 'CTR-100');
        $booking2 = $this->makeBooking(2, 'BC-102', 'CTR-100');

        $sources = $this->emptySources();
        $sources['shippingInstructions'] = collect([
            (new ShippingInstruction())->forceFill([
                'SI_No'       => 'SI-101',
                'Final_SI_No' => 'FSI-101',
                'Order_No'    => 'BC-101',
                'SI_Date'     => '2026-11-20',
                'Status'      => 'APPROVED',
            ]),
            (new ShippingInstruction())->forceFill([
                'SI_No'       => 'SI-102',
                'Final_SI_No' => 'FSI-102',
                'Order_No'    => 'BC-102',
                'SI_Date'     => '2026-11-21',
                'Status'      => 'APPROVED',
            ]),
        ]);
        $sources['sds'] = collect([
            (new SustainabilityDeclaration())->forceFill([
                'SD_No'       => 'SD-APPROVED-01',
                'Contract_No' => 'CTR-100',
                'Issue_Date'  => '2026-11-22',
                'Status'      => 'APPROVED',
            ]),
            (new SustainabilityDeclaration())->forceFill([
                'SD_No'       => 'SD-DRAFT-02',
                'Contract_No' => 'CTR-100',
                'Issue_Date'  => '2026-11-23',
                'Status'      => 'CREATED',
            ]),
        ]);
        $sources['dns'] = collect([
            (new DeliveryNote())->forceFill([
                'DN_No'         => 'DN-DRAFT-01',
                'Contract_No'   => 'CTR-100',
                'Delivery_Date' => '2026-11-22',
                'Status'        => 'DRAFT',
            ]),
        ]);
        $sources['annexes'] = collect([
            (new AnnexVII())->forceFill([
                'Annex_No'    => 'AX-101',
                'SI_No'       => 'FSI-101',
                'Order_No'    => 'BC-101',
                'Contract_No' => 'CTR-100',
                'Status'      => 'APPROVED',
                'created_at'  => '2026-11-23 09:00:00',
            ]),
            (new AnnexVII())->forceFill([
                'Annex_No'    => 'AX-102',
                'SI_No'       => 'FSI-102',
                'Order_No'    => 'BC-102',
                'Contract_No' => 'CTR-100',
                'Status'      => 'DRAFT',
                'created_at'  => '2026-11-24 09:00:00',
            ]),
        ]);

        $controller = $this->makeController($sources);
        $data = $controller->buildProgressData(collect([$booking1, $booking2]));

        // BC-101 gets SD-APPROVED-01 + AX-101, excludes DN-DRAFT-01 and SD-DRAFT-02
        $other1 = $data[1]['stages']['other_export_doc'];
        $this->assertTrue($other1['exists']);
        $this->assertFalse($other1['optional']);
        $this->assertCount(2, $other1['sub_docs']);
        $this->assertSame(['Annex VII', 'SD'], array_column($other1['sub_docs'], 'code'));
        $this->assertSame('AX-101', $other1['sub_docs'][0]['doc_no']);
        $this->assertSame('SD-APPROVED-01', $other1['sub_docs'][1]['doc_no']);
        $this->assertStringContainsString('cns-export-annex-print?Task=AX-101', $other1['sub_docs'][0]['preview_url']);
        $this->assertStringContainsString('cns-export-sustainability-declaration-print?Task=SD-APPROVED-01', $other1['sub_docs'][1]['preview_url']);
        $this->assertSame('Annex VII, SD', $other1['doc_no']);
        $this->assertSame('2 dokumen approved', $other1['note']);

        // BC-102 only gets SD-APPROVED-01 (contract-level) and does NOT get BC-101's AX-101 or its own DRAFT AX-102
        $other2 = $data[2]['stages']['other_export_doc'];
        $this->assertCount(1, $other2['sub_docs']);
        $this->assertSame('SD', $other2['sub_docs'][0]['code']);
        $this->assertSame('SD-APPROVED-01', $other2['sub_docs'][0]['doc_no']);
    }

    public function test_other_export_doc_empty_is_optional_and_does_not_block_100_percent_save(): void
    {
        $booking = $this->makeBooking(5, 'BC-500', 'CTR-500');
        $booking->Payment_Approved = 1;
        $booking->Payment_Approved_At = '2026-11-25 11:00:00';
        $booking->Payment_Approved_By = 'cns_admin';

        $sources = $this->emptySources();
        $sources['vesselSchedules'] = collect([
            (new VesselSchedule())->forceFill([
                'id'                            => 50,
                'Booking_ID'                    => 5,
                'Booking_No'                    => 'BC-500',
                'Order_No'                      => 'BC-500',
                'Checklist_Done_Kapal_Berangkat'=> 1,
                'Checklist_Updated_Date'        => '2026-11-21',
            ]),
        ]);
        $sources['shippingInstructions'] = collect([
            (new ShippingInstruction())->forceFill([
                'SI_No'       => 'SI-500',
                'Final_SI_No' => 'FSI-500',
                'Order_No'    => 'BC-500',
                'SI_Date'     => '2026-11-20',
                'Status'      => 'APPROVED',
            ]),
        ]);
        $sources['clearances'] = collect([
            (new CnsCustomsClearance())->forceFill([
                'id'          => 9,
                'booking_id'  => 5,
                'doc_3d_path' => 'uploads/customs-clearance/3d.pdf',
                'doc_3d_name' => '3d.pdf',
                'peb_path'    => 'uploads/customs-clearance/peb.pdf',
                'peb_name'    => 'peb.pdf',
                'npe_path'    => 'uploads/customs-clearance/npe.pdf',
                'npe_name'    => 'npe.pdf',
            ]),
        ]);
        $sources['wbts'] = collect([
            (new WeightBridgeTicket())->forceFill([
                'WBT_No'         => 'WBT-500',
                'SI_No'          => 'SI-500',
                'Departure_Date' => '2026-11-21',
            ]),
        ]);
        $sources['pls'] = collect([
            (new PackingList())->forceFill([
                'Packing_List_No' => 'FPL-500',
                'SI_No'           => 'SI-500',
                'Final_SI_No'     => 'FSI-500',
                'Status'          => 'APPROVED',
                'PL_Date'         => '2026-11-22',
            ]),
        ]);
        $sources['cis'] = collect([
            (new CommercialInvoice())->forceFill([
                'Inv_No'   => 'INV-500',
                'SI_No'    => 'FSI-500',
                'Inv_Date' => '2026-11-23',
            ]),
        ]);
        $sources['dns'] = collect([
            (new DeliveryNote())->forceFill([
                'DN_No'       => 'DN-500',
                'Contract_No' => 'CTR-500',
                'Status'      => 'CREATED',
            ]),
        ]);

        // Controller with COA resolved via subclass seam (COW remains empty/null -> 0 approved Other Docs)
        $controller = $this->makeController($sources, $booking, true);
        $stages = $controller->buildProgressData(collect([$booking]))[5]['stages'];

        $this->assertFalse($stages['other_export_doc']['exists']);
        $this->assertTrue($stages['other_export_doc']['optional']);
        $this->assertSame([], $stages['other_export_doc']['sub_docs']);

        $summary = ExportChecklistController::summarizeStages($stages);
        $this->assertSame(12, $summary['completed']);
        $this->assertSame(12, $summary['total']);
        $this->assertSame(100, $summary['percent']);

        // saveChecklist succeeds at 12/12 despite 0 optional Other Export Documents
        $saveResp = $controller->saveChecklist(Request::create('/cns-export-checklist-detail-submit', 'POST', [
            'id'                 => 5,
            'Checklist_Verified' => 1,
            'Checklist_Notes'    => 'All required stages complete',
        ]));
        $this->assertSame(200, $saveResp->getStatusCode());
        $this->assertSame(1, (int) $booking->Checklist_Verified);

        // If COA is missing (11/12), saveChecklist blocks with 422
        $controllerIncomplete = $this->makeController($sources, $booking, false);
        $blockedResp = $controllerIncomplete->saveChecklist(Request::create('/cns-export-checklist-detail-submit', 'POST', [
            'id'                 => 5,
            'Checklist_Verified' => 1,
        ]));
        $this->assertSame(422, $blockedResp->getStatusCode());

        // checklist-detail renders 13 rows and empty optional note without error
        Gate::before(function () {
            return true;
        });
        $html = view('cns.export.checklist-detail', [
            'booking'          => $booking,
            'contract'         => $booking->contract,
            'vs'               => null,
            'si'               => $sources['shippingInstructions']->first(),
            'statuses'         => $stages,
            'contractProgress' => ['percent' => 100, 'bc_count' => 1],
        ])->render();
        $this->assertStringContainsString('Belum ada dokumen di-approve (Opsional)', $html);
        $this->assertStringContainsString('COA Surveyor', $html);
        $this->assertStringContainsString('Other Export Document', $html);
    }

    public function test_fpl_stage_requires_final_or_approved_packing_list_and_detail_renders_sub_docs(): void
    {
        $booking = $this->makeBooking(10, 'BC-700', 'CTR-700');

        $baseSources = $this->emptySources();
        $baseSources['shippingInstructions'] = collect([
            (new ShippingInstruction())->forceFill([
                'SI_No'       => 'SI-700',
                'Final_SI_No' => 'FSI-700',
                'Order_No'    => 'BC-700',
                'Status'      => 'APPROVED',
            ]),
        ]);

        // 1. Draft / CREATED PackingList -> fpl.exists must be false
        $draftSources = $baseSources;
        $draftSources['pls'] = collect([
            (new PackingList())->forceFill([
                'Packing_List_No' => 'PL-DRAFT-700',
                'SI_No'           => 'SI-700',
                'Final_SI_No'     => 'FSI-700',
                'Status'          => 'CREATED',
                'PL_Date'         => '2026-11-22',
            ]),
        ]);
        $draftStages = $this->makeController($draftSources)->buildProgressData(collect([$booking]))[10]['stages'];
        $this->assertFalse($draftStages['fpl']['exists']);
        $this->assertNull($draftStages['fpl']['preview_url']);

        // 2. FINAL PackingList -> fpl.exists must be true (even alongside an older CREATED PL)
        $finalSources = $draftSources;
        $finalSources['pls']->push((new PackingList())->forceFill([
            'Packing_List_No' => 'FPL-700',
            'SI_No'           => 'SI-700',
            'Final_SI_No'     => 'FSI-700',
            'Status'          => 'FINAL',
            'PL_Date'         => '2026-11-23',
        ]));
        $finalStages = $this->makeController($finalSources)->buildProgressData(collect([$booking]))[10]['stages'];
        $this->assertTrue($finalStages['fpl']['exists']);
        $this->assertSame('FPL-700', $finalStages['fpl']['doc_no']);
        $this->assertStringContainsString('cns-export-packing-list-print?Task=FPL-700', $finalStages['fpl']['preview_url']);

        // 3. APPROVED PackingList -> fpl.exists must be true
        $approvedSources = $baseSources;
        $approvedSources['pls'] = collect([
            (new PackingList())->forceFill([
                'Packing_List_No' => 'FPL-APR-700',
                'SI_No'           => 'SI-700',
                'Final_SI_No'     => 'FSI-700',
                'Status'          => 'APPROVED',
                'PL_Date'         => '2026-11-24',
            ]),
        ]);
        $approvedSources['dns'] = collect([
            (new DeliveryNote())->forceFill([
                'DN_No'         => 'DN-APR-700',
                'Contract_No'   => 'CTR-700',
                'Delivery_Date' => '2026-11-24',
                'Status'        => 'APPROVED',
            ]),
        ]);

        $approvedController = $this->makeController($approvedSources, $booking);
        $approvedStages = $approvedController->buildProgressData(collect([$booking]))[10]['stages'];
        $this->assertTrue($approvedStages['fpl']['exists']);
        $this->assertSame('FPL-APR-700', $approvedStages['fpl']['doc_no']);

        // Verify checklist-detail renders sub_docs badge & Preview button, and indexXml outputs 13 codes
        Gate::before(function () {
            return true;
        });
        $html = view('cns.export.checklist-detail', [
            'booking'          => $booking,
            'contract'         => $booking->contract,
            'vs'               => null,
            'si'               => $approvedSources['shippingInstructions']->first(),
            'statuses'         => $approvedStages,
            'contractProgress' => ['percent' => 42, 'bc_count' => 1],
        ])->render();
        $this->assertStringContainsString('DN-APR-700', $html);
        $this->assertStringContainsString('Preview DN', $html);

        $xmlResp = $approvedController->indexXml(Request::create('/xml-cns-export-checklist', 'GET'));
        $xml = $xmlResp->getContent();
        foreach (array_values(ExportChecklistController::STAGE_CODES) as $code) {
            $this->assertStringContainsString('>' . $code . '<', $xml);
        }
    }

    private function makeBooking(int $id, string $bookingNo, string $contractNo): BuyerContractBooking
    {
        $contract = (new BuyerContract())->forceFill([
            'ID'           => $id,
            'Contract_No'  => $contractNo,
            'Created_Date' => '2026-11-01 08:00:00',
            'Status'       => 'ACTIVE',
        ]);

        $booking = (new BuyerContractBooking())->forceFill([
            'ID'           => $id,
            'Contract_ID'  => $id,
            'Booking_No'   => $bookingNo,
            'Label'        => 'BC 1',
            'Created_Date' => '2026-11-10 09:00:00',
        ]);
        $booking->setRelation('contract', $contract);

        return $booking;
    }

    private function emptySources(): array
    {
        return [
            'vesselSchedules'      => collect(),
            'containerCounts'      => collect(),
            'shippingInstructions' => collect(),
            'clearances'           => collect(),
            'pls'                  => collect(),
            'cis'                  => collect(),
            'wbts'                 => collect(),
            'sds'                  => collect(),
            'dns'                  => collect(),
            'annexes'              => collect(),
            'invoicingRequests'    => collect(),
        ];
    }

    private function makeController(array $sources, ?BuyerContractBooking $booking = null, bool $resolveCoa = false): ExportChecklistController
    {
        return new class($sources, $booking, $resolveCoa) extends ExportChecklistController {
            private $sources;
            private $booking;
            private $resolveCoa;

            public function __construct(array $sources, ?BuyerContractBooking $booking, bool $resolveCoa)
            {
                $this->sources = $sources;
                $this->booking = $booking;
                $this->resolveCoa = $resolveCoa;
            }

            protected function fetchProgressSources(Collection $bookings): array
            {
                return $this->sources;
            }

            protected function resolveSurveyorDocument(string $type, $booking, $si = null): ?array
            {
                if ($type === 'coa' && $this->resolveCoa) {
                    return [
                        'doc_no' => 'COA-TEST-01',
                        'date'   => '2026-11-24',
                        'note'   => 'Verified',
                    ];
                }

                return parent::resolveSurveyorDocument($type, $booking, $si);
            }

            protected function paginateChecklistBookings(Request $request): array
            {
                $rows = $this->booking ? collect([$this->booking]) : collect();
                return [
                    'rows'     => $rows,
                    'total'    => $rows->count(),
                    'posStart' => 0,
                ];
            }

            protected function fetchContractBookings(array $contractIds): Collection
            {
                return $this->booking ? collect([$this->booking]) : collect();
            }

            protected function findBookingWithContractOrFail($id): BuyerContractBooking
            {
                return $this->booking;
            }

            protected function persistBooking(BuyerContractBooking $booking): void
            {
                // No-op in unit test
            }
        };
    }
}
