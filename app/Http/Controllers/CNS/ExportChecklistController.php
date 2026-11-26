<?php

namespace App\Http\Controllers\CNS;

use App\AnnexVII;
use App\BuyerContractBooking;
use App\CnsCustomsClearance;
use App\CommercialInvoice;
use App\DeliveryNote;
use App\ExportInvoicingRequest;
use App\Http\Controllers\Controller;
use App\PackingList;
use App\Services\NotificationService;
use App\ShippingInstruction;
use App\Support\DhtmlxGridPagination;
use App\SustainabilityDeclaration;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use App\WeightBridgeTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportChecklistController extends Controller
{
    public const STAGE_CODES = [
        'contract'         => 'Kontrak',
        'si'               => 'SI',
        'booking'          => 'BC',
        'doc_3d'           => '3D',
        'stuffing'         => 'Stuffing',
        'wbt'              => 'WBT',
        'peb'              => 'PEB',
        'npe'              => 'NPE',
        'fpl'              => 'FPL',
        'invoice'          => 'Invoice',
        'coa_surveyor'     => 'COA Surveyor',
        'other_export_doc' => 'Other Export Document',
        'payment'          => 'Payment',
    ];

    public function index()
    {
        return view('cns.export.checklist');
    }

    public function indexXml(Request $request)
    {
        $paginated = $this->paginateChecklistBookings($request);
        $bookings = $paginated['rows'];

        $contractProgressById = $this->buildContractProgress($bookings);
        $progressByBookingId = $contractProgressById['by_booking'];
        $contractStats = $contractProgressById['by_contract'];
        $stageCodes = self::STAGE_CODES;
        $stageKeys = array_keys($stageCodes);
        $totalStages = count($stageKeys);

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($bookings as $booking) {
            $data = $progressByBookingId[$booking->ID];
            $stages = $data['stages'];
            $stats = $contractStats[$booking->Contract_ID] ?? ['percent' => 0, 'bc_count' => 1];

            $createdDate = $booking->Created_Date ? date('d F Y', strtotime($booking->Created_Date)) : '-';
            $verifiedLabel = !empty($booking->Checklist_Verified) ? 'Verified' : 'Pending';
            $contractNo = $booking->contract ? $booking->contract->Contract_No : '-';
            $progressLabel = $stats['percent'] . '% (' . $stats['bc_count'] . ' BC)';

            $stepsHtml = '';
            $activeMarked = false;

            foreach ($stageKeys as $idx => $key) {
                $st = $stages[$key];
                $isDone = !empty($st['exists']);
                $isOptionalEmpty = !$isDone && !empty($st['optional']);
                $isLast = ($idx === $totalStages - 1);

                if ($idx > 0) {
                    $lineColor = $isDone ? '#0d6efd' : '#d1d5db';
                    $stepsHtml .= "<span style=\"display:inline-block;width:10px;height:2px;background:{$lineColor};flex-shrink:0;\"></span>";
                }

                if ($isDone) {
                    $bg = '#dceeff';
                    $border = '#90caf9';
                    $color = '#0d6efd';
                    $icon = $isLast ? '&#9873;' : '&#10003;';
                } elseif ($isOptionalEmpty) {
                    $bg = '#f8fafc';
                    $border = '#cbd5e1';
                    $color = '#64748b';
                    $icon = '&#8211;';
                } elseif (!$activeMarked) {
                    $activeMarked = true;
                    $bg = '#fff8e1';
                    $border = '#ffe082';
                    $color = '#b7791f';
                    $icon = '&#9679;';
                } else {
                    $bg = '#f3f4f6';
                    $border = '#d1d5db';
                    $color = '#9ca3af';
                    $icon = $isLast ? '&#9873;' : '&#9675;';
                }

                $statusText = $isDone ? 'Lengkap' : ($isOptionalEmpty ? 'Opsional' : 'Belum');
                $tooltip = htmlspecialchars(
                    $st['label'] . ' (' . $statusText . ') | No: ' . $st['doc_no'] . ' | Tgl: ' . ($st['date'] ?? '-'),
                    ENT_QUOTES,
                    'UTF-8'
                );
                $code = $stageCodes[$key];

                $stepsHtml .= "<span title=\"{$tooltip}\" style=\"display:inline-flex;flex-direction:column;align-items:center;justify-content:center;min-width:30px;padding:3px 5px;border-radius:5px;background:{$bg};border:1px solid {$border};color:{$color};line-height:1.1;\">"
                    . "<span style=\"font-size:10px;font-weight:700;\">{$icon}</span>"
                    . "<span style=\"font-size:9px;font-weight:700;letter-spacing:0.3px;white-space:nowrap;\">{$code}</span>"
                    . "</span>";
            }

            $timelineHtml = "<div class=\"export-mini-stepper\" style=\"display:inline-flex;align-items:center;justify-content:center;gap:0;white-space:nowrap;\">{$stepsHtml}</div>";

            $bodyXml .= '<row id="' . (int) $booking->ID . '">';
            $bodyXml .= '<cell>' . ($nomor++) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $contractNo) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $booking->Booking_No) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($createdDate) . '</cell>';
            $bodyXml .= '<cell><![CDATA[' . $timelineHtml . ']]></cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($progressLabel) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($verifiedLabel) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) ($booking->Checklist_Notes ?: '')) . '</cell>';
            $bodyXml .= "</row>\n";
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function detail(Request $request)
    {
        $id = $request->input('Task');
        $booking = $this->findBookingWithContractOrFail($id);
        $contractProgress = $this->buildContractProgress(collect([$booking]));
        $progress = $contractProgress['by_booking'][$booking->ID];
        $stats = $contractProgress['by_contract'][$booking->Contract_ID] ?? ['percent' => 0, 'bc_count' => 1];

        return view('cns.export.checklist-detail', [
            'booking'          => $booking,
            'contract'         => $booking->contract,
            'vs'               => $progress['vs'],
            'si'               => $progress['si'],
            'statuses'         => $progress['stages'],
            'contractProgress' => $stats,
        ]);
    }

    public function saveChecklist(Request $request)
    {
        $validated = $request->validate([
            'id'                 => 'required|integer',
            'Checklist_Verified' => 'nullable|boolean',
            'Checklist_Notes'    => 'nullable|string|max:2000',
        ]);

        $booking = $this->findBookingWithContractOrFail($validated['id']);
        $stages = $this->buildProgressData(collect([$booking]))[$booking->ID]['stages'];
        $summary = self::summarizeStages($stages);

        if ($summary['completed'] < $summary['total']) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data BC belum 100% ({$summary['completed']}/{$summary['total']} tahap). Lengkapi seluruh tahap terlebih dahulu sebelum menyimpan verifikasi.",
            ], 422);
        }

        $verified = !empty($validated['Checklist_Verified']);

        $booking->Checklist_Verified = $verified ? 1 : 0;
        $booking->Checklist_Notes = $validated['Checklist_Notes'] ?? null;

        if ($verified) {
            $booking->Checklist_Verified_By = Auth::check() ? Auth::user()->username : 'system';
            $booking->Checklist_Verified_At = now();
        } else {
            $booking->Checklist_Verified_By = null;
            $booking->Checklist_Verified_At = null;
        }

        $this->persistBooking($booking);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Export checklist berhasil disimpan.',
            'verified_by' => $booking->Checklist_Verified_By,
            'verified_at' => $booking->Checklist_Verified_At ? date('d F Y H:i', strtotime($booking->Checklist_Verified_At)) : null,
        ]);
    }

    public function approvePayment(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        $booking = BuyerContractBooking::findOrFail($validated['id']);

        if (empty($booking->Finance_Payment_Verified)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Dana masuk untuk BC ini belum diverifikasi oleh tim Finance.',
            ], 422);
        }

        if (empty($booking->Payment_Approved)) {
            $booking->Payment_Approved = 1;
            $booking->Payment_Approved_By = Auth::check() ? Auth::user()->username : 'system';
            $booking->Payment_Approved_At = now();
            $booking->save();
        }

        return response()->json([
            'status'      => 'success',
            'message'     => 'Payment berhasil di-approve.',
            'approved_by' => $booking->Payment_Approved_By,
            'approved_at' => $booking->Payment_Approved_At ? date('d F Y H:i', strtotime($booking->Payment_Approved_At)) : null,
        ]);
    }

    public function receiveFinancePayment(Request $request, NotificationService $notifications)
    {
        $expectedToken = (string) config('services.finance.webhook_token');
        if ($expectedToken === '') {
            return response()->json(['message' => 'Finance webhook token is not configured.'], 503);
        }

        $providedToken = (string) $request->header('X-API-Key', '');
        if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'Booking_No'  => 'required|string|max:100',
            'Payment_Ref' => 'nullable|string|max:100',
            'Payment_Date'=> 'nullable|date',
            'Verified_By' => 'nullable|string|max:100',
        ]);

        $booking = BuyerContractBooking::with('contract')
            ->where('Booking_No', $validated['Booking_No'])
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking Confirmation not found.'], 404);
        }

        $booking->Finance_Payment_Verified = 1;
        $booking->Finance_Payment_Ref = $validated['Payment_Ref'] ?? $booking->Finance_Payment_Ref;
        $booking->Finance_Payment_Date = !empty($validated['Payment_Date']) ? date('Y-m-d H:i:s', strtotime($validated['Payment_Date'])) : now();
        $booking->Finance_Payment_By = $validated['Verified_By'] ?? 'Finance';
        $booking->save();

        $contractNo = $booking->contract ? $booking->contract->Contract_No : '-';
        $refText = $booking->Finance_Payment_Ref ? " (Ref: {$booking->Finance_Payment_Ref})" : '';
        $notifications->makeNotification(
            "Dana Masuk BC {$booking->Booking_No}",
            "Tim Finance memverifikasi dana masuk untuk BC {$booking->Booking_No} (Kontrak {$contractNo}){$refText}. Silakan Approve Payment di Export Checklist.",
            'export_payment',
            null,
            null
        );

        return response()->json([
            'status'     => 'success',
            'booking_no' => $booking->Booking_No,
            'contract_no'=> $contractNo,
        ]);
    }

    /**
     * Summarize completed vs required stages, excluding unfilled optional stages from denominator.
     */
    public static function summarizeStages(array $stages): array
    {
        $completed = 0;
        $total = 0;

        foreach ($stages as $stage) {
            $exists = !empty($stage['exists']);
            $optional = !empty($stage['optional']);

            if ($exists) {
                $completed++;
                $total++;
            } elseif (!$optional) {
                $total++;
            }
        }

        return [
            'completed' => $completed,
            'total'     => $total,
            'percent'   => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    /**
     * Compute contract-level progress % across all BCs belonging to each contract.
     */
    private function buildContractProgress(Collection $bookings): array
    {
        if ($bookings->isEmpty()) {
            return ['by_booking' => [], 'by_contract' => []];
        }

        $contractIds = $bookings->pluck('Contract_ID')->filter()->unique()->values()->all();
        $allContractBookings = !empty($contractIds)
            ? $this->fetchContractBookings($contractIds)
            : $bookings;

        if ($allContractBookings->isEmpty()) {
            $allContractBookings = $bookings;
        }

        $byBooking = $this->buildProgressData($allContractBookings);

        $byContract = [];
        foreach ($allContractBookings->groupBy('Contract_ID') as $contractId => $group) {
            $bcCount = $group->count();
            $completedSum = 0;
            $stageTotal = 0;

            foreach ($group as $b) {
                $stages = $byBooking[$b->ID]['stages'] ?? [];
                $summary = self::summarizeStages($stages);
                $stageTotal += $summary['total'];
                $completedSum += $summary['completed'];
            }

            $byContract[$contractId] = [
                'bc_count'      => $bcCount,
                'completed_sum' => $completedSum,
                'stage_total'   => $stageTotal,
                'percent'       => $stageTotal > 0 ? (int) round(($completedSum / $stageTotal) * 100) : 0,
            ];
        }

        return [
            'by_booking'  => $byBooking,
            'by_contract' => $byContract,
        ];
    }

    /**
     * Build 13-stage export progress (Kontrak through Payment) in batch.
     */
    public function buildProgressData(Collection $bookings): array
    {
        if ($bookings->isEmpty()) {
            return [];
        }

        $sources = $this->fetchProgressSources($bookings);

        $vesselSchedules = $sources['vesselSchedules'] ?? collect();
        $containerCounts = $sources['containerCounts'] ?? collect();
        $shippingInstructions = $sources['shippingInstructions'] ?? collect();
        $clearances = $sources['clearances'] ?? collect();
        $pls = $sources['pls'] ?? collect();
        $cis = $sources['cis'] ?? collect();
        $wbts = $sources['wbts'] ?? collect();
        $sds = $sources['sds'] ?? collect();
        $dns = $sources['dns'] ?? collect();
        $annexes = $sources['annexes'] ?? collect();
        $invoicingRequests = $sources['invoicingRequests'] ?? collect();
        $coaSurveyors = $sources['coaSurveyors'] ?? collect();

        $formatDate = static function ($value): string {
            if (empty($value)) {
                return '-';
            }
            $ts = strtotime((string) $value);
            return $ts !== false ? date('d M Y', $ts) : '-';
        };

        $result = [];
        foreach ($bookings as $booking) {
            $contract = $booking->contract;
            $contractNo = $contract ? $contract->Contract_No : null;
            $bookingNo = $booking->Booking_No;

            $vs = $vesselSchedules->first(function ($row) use ($booking, $bookingNo) {
                return (!empty($row->Booking_ID) && (int) $row->Booking_ID === (int) $booking->ID)
                    || ($bookingNo && ($row->Booking_No === $bookingNo || $row->Order_No === $bookingNo));
            });

            $orderNo = $vs ? $vs->Order_No : $bookingNo;
            $si = $shippingInstructions->first(function ($row) use ($orderNo, $bookingNo) {
                return ($orderNo && $row->Order_No === $orderNo) || ($bookingNo && $row->Order_No === $bookingNo);
            });

            $refs = $si ? array_values(array_filter([$si->SI_No, $si->Final_SI_No])) : [];

            $cc = $clearances->first(function ($row) use ($booking) {
                return (int) $row->booking_id === (int) $booking->ID;
            });

            $bookingPls = $pls->filter(function ($row) use ($refs, $bookingNo) {
                $matchSi = !empty($refs) && (in_array($row->SI_No, $refs, true) || in_array($row->Final_SI_No, $refs, true));
                $matchBc = !empty($bookingNo) && !empty($row->BC_No) && $row->BC_No === $bookingNo;
                return $matchSi || $matchBc;
            });
            $fplMatch = $bookingPls->first(function ($row) {
                return in_array(strtoupper(trim((string) ($row->Status ?? ''))), ['FINAL', 'APPROVED'], true);
            });
            $plFallback = $fplMatch ?: $bookingPls->first();

            $ciMatch = !empty($refs) ? $cis->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true);
            }) : null;
            $wbtMatch = !empty($refs) ? $wbts->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true) || in_array($row->Final_SI_No, $refs, true);
            }) : null;
            $eiMatch = $invoicingRequests->first(function ($row) use ($vs, $orderNo, $bookingNo) {
                return ($vs && (int) $row->vessel_schedule_id === (int) $vs->id)
                    || ($orderNo && $row->order_no === $orderNo)
                    || ($bookingNo && $row->order_no === $bookingNo);
            });

            $containerCount = (int) ($containerCounts[$orderNo] ?? 0);
            $stuffingDone = $vs && !empty($vs->Checklist_Done_Kapal_Berangkat);

            $buildCcStage = static function (string $docType, string $short, string $label) use ($cc, $formatDate): array {
                $pathProp = "{$docType}_path";
                $nameProp = "{$docType}_name";
                $hasFile = $cc && !empty($cc->{$pathProp});

                return [
                    'short'       => $short,
                    'label'       => $label,
                    'exists'      => $hasFile,
                    'optional'    => false,
                    'doc_no'      => $hasFile ? ($cc->{$nameProp} ?: basename((string) $cc->{$pathProp})) : '-',
                    'date'        => $hasFile ? $formatDate($cc->updated_at ?? ($cc->created_at ?? null)) : '-',
                    'note'        => $hasFile ? 'Terupload (Customs Clearance)' : 'Belum diupload',
                    'preview_url' => ($hasFile && !empty($cc->id))
                        ? route('cns-export-customs-clearance.file', ['id' => $cc->id, 'docType' => $docType], false)
                        : null,
                ];
            };

            $coaMatch = $coaSurveyors->first(function ($row) use ($booking) {
                return (int) ($row->Booking_ID ?? 0) === (int) $booking->ID
                    && strtoupper(trim((string) ($row->QC_Type ?? 'SURVEYOR'))) === 'SURVEYOR'
                    && empty($row->Is_Deleted)
                    && !empty($row->Attachment_Path);
            });
            $coaDoc = $coaMatch ? [
                'doc_no'      => $coaMatch->Kode_QC ?: '-',
                'date'        => $coaMatch->Inspection_Date ?? null,
                'note'        => 'COA Surveyor Tersedia',
                'preview_url' => url($coaMatch->Attachment_Path),
            ] : $this->resolveSurveyorDocument('coa', $booking, $si);
            $subDocs = $this->collectApprovedOtherDocs(
                $booking,
                $contractNo,
                $bookingNo,
                $orderNo,
                $refs,
                $si,
                $annexes,
                $dns,
                $sds,
                $formatDate
            );
            $hasOtherDocs = !empty($subDocs);

            $result[$booking->ID] = [
                'vs'     => $vs,
                'si'     => $si,
                'stages' => [
                    'contract' => [
                        'short'       => 'Kontrak',
                        'label'       => 'Kontrak',
                        'exists'      => (bool) $contract,
                        'optional'    => false,
                        'doc_no'      => $contractNo ?: '-',
                        'date'        => $contract ? $formatDate($contract->Created_Date ?? null) : '-',
                        'note'        => $contract ? ($contract->Status ?: 'Aktif') : '-',
                        'preview_url' => ($contract && !empty($contract->Contract_File))
                            ? route('cns-procurement-buyer-contract.file', ['ID' => $contract->ID], false)
                            : null,
                    ],
                    'si' => [
                        'short'       => 'SI',
                        'label'       => 'SI',
                        'exists'      => (bool) $si,
                        'optional'    => false,
                        'doc_no'      => $si ? ($si->SI_No ?: '-') : '-',
                        'date'        => $si ? $formatDate($si->SI_Date ?? null) : '-',
                        'note'        => $si ? ($si->Status ?: 'Tersedia') : '-',
                        'preview_url' => ($si && !empty($si->SI_No))
                            ? url('cns-export-shipping-instruction-print') . '?Task=' . urlencode($si->SI_No)
                            : null,
                    ],
                    'booking' => [
                        'short'       => 'BC',
                        'label'       => 'BC',
                        'exists'      => !empty($bookingNo),
                        'optional'    => false,
                        'doc_no'      => $bookingNo ?: '-',
                        'date'        => $formatDate($booking->Created_Date ?? null),
                        'note'        => $booking->Label ?: 'Tersedia',
                        'preview_url' => !empty($booking->Booking_File)
                            ? route('cns-procurement-buyer-contract.booking-file', ['ID' => $booking->ID], false)
                            : null,
                    ],
                    'doc_3d'   => $buildCcStage('doc_3d', '3D', '3D'),
                    'stuffing' => [
                        'short'       => 'Stuffing',
                        'label'       => 'Stuffing',
                        'exists'      => $stuffingDone,
                        'optional'    => false,
                        'doc_no'      => $containerCount > 0 ? $containerCount . ' Kontainer' : '-',
                        'date'        => $vs ? $formatDate($vs->Checklist_Updated_Date ?? ($vs->Stuffing ?? null)) : '-',
                        'note'        => $stuffingDone ? 'Done / Kapal Berangkat' : ($containerCount > 0 ? 'Proses Stuffing' : 'Belum'),
                        'preview_url' => null,
                    ],
                    'wbt' => [
                        'short'       => 'WBT',
                        'label'       => 'WBT',
                        'exists'      => (bool) $wbtMatch,
                        'optional'    => false,
                        'doc_no'      => $wbtMatch->WBT_No ?? '-',
                        'date'        => $wbtMatch ? $formatDate($wbtMatch->Departure_Date ?? null) : '-',
                        'note'        => $wbtMatch ? 'Tersedia' : '-',
                        'preview_url' => ($wbtMatch && !empty($wbtMatch->WBT_No))
                            ? url('cns-export-weight-bridge-ticket-print') . '?Task=' . urlencode($wbtMatch->WBT_No)
                            : null,
                    ],
                    'peb' => $buildCcStage('peb', 'PEB', 'PEB'),
                    'npe' => $buildCcStage('npe', 'NPE', 'NPE'),
                    'fpl' => [
                        'short'       => 'FPL',
                        'label'       => 'FPL',
                        'exists'      => (bool) $fplMatch,
                        'optional'    => false,
                        'doc_no'      => $plFallback->Packing_List_No ?? '-',
                        'date'        => $plFallback ? $formatDate($plFallback->PL_Date ?? null) : '-',
                        'note'        => $fplMatch
                            ? ($fplMatch->Status ?? 'FINAL')
                            : ($plFallback ? (($plFallback->Status ?: 'DRAFT') . ' (Menunggu FINAL/APPROVED)') : '-'),
                        'preview_url' => ($fplMatch && !empty($fplMatch->Packing_List_No))
                            ? url('cns-export-packing-list-print') . '?Task=' . urlencode($fplMatch->Packing_List_No)
                            : null,
                    ],
                    'invoice' => [
                        'short'       => 'Invoice',
                        'label'       => 'Invoice',
                        'exists'      => (bool) $ciMatch,
                        'optional'    => false,
                        'doc_no'      => $ciMatch->Inv_No ?? '-',
                        'date'        => $ciMatch ? $formatDate($ciMatch->Inv_Date ?? null) : '-',
                        'note'        => $ciMatch ? 'Tersedia' : '-',
                        'preview_url' => ($ciMatch && !empty($ciMatch->Inv_No))
                            ? url('cns-export-commercial-invoice-print') . '?Task=' . urlencode($ciMatch->Inv_No)
                            : null,
                    ],
                    'coa_surveyor' => [
                        'short'       => 'COA Surveyor',
                        'label'       => 'COA Surveyor',
                        'exists'      => (bool) $coaDoc,
                        'optional'    => false,
                        'doc_no'      => $coaDoc['doc_no'] ?? '-',
                        'date'        => $coaDoc ? $formatDate($coaDoc['date'] ?? null) : '-',
                        'note'        => $coaDoc['note'] ?? 'Menunggu QC Surveyor',
                        'preview_url' => $coaDoc['preview_url'] ?? null,
                    ],
                    'other_export_doc' => [
                        'short'       => 'Other Export Document',
                        'label'       => 'Other Export Document (Annex VII, DN, SD, COW Surveyor)',
                        'exists'      => $hasOtherDocs,
                        'optional'    => !$hasOtherDocs,
                        'doc_no'      => $hasOtherDocs ? implode(', ', array_values(array_unique(array_column($subDocs, 'code')))) : '-',
                        'date'        => $hasOtherDocs ? ($subDocs[0]['date'] ?? '-') : '-',
                        'note'        => $hasOtherDocs ? (count($subDocs) . ' dokumen approved') : 'Belum ada dokumen di-approve (Opsional)',
                        'preview_url' => null,
                        'sub_docs'    => $subDocs,
                    ],
                    'payment' => [
                        'short'       => 'Payment',
                        'label'       => 'Payment',
                        'exists'      => !empty($booking->Payment_Approved),
                        'optional'    => false,
                        'doc_no'      => $booking->Finance_Payment_Ref ?: '-',
                        'date'        => !empty($booking->Payment_Approved_At)
                            ? $formatDate($booking->Payment_Approved_At)
                            : (!empty($booking->Finance_Payment_Date) ? $formatDate($booking->Finance_Payment_Date) : '-'),
                        'note'        => !empty($booking->Payment_Approved)
                            ? 'Approved by ' . ($booking->Payment_Approved_By ?: '-')
                            : (!empty($booking->Finance_Payment_Verified) ? 'Dana Masuk (Menunggu Approve CNS)' : 'Menunggu Finance'),
                        'preview_url' => ($eiMatch && !empty($eiMatch->transfer_proof_path))
                            ? url("finance-export-invoicing-file/{$eiMatch->id}/transfer_proof")
                            : null,
                    ],
                ],
            ];
        }

        return $result;
    }

    /**
     * Collect approved sub-documents for Other Export Document: [Annex VII, DN, SD, COW Surveyor].
     */
    private function collectApprovedOtherDocs(
        $booking,
        ?string $contractNo,
        ?string $bookingNo,
        ?string $orderNo,
        array $refs,
        $si,
        Collection $annexes,
        Collection $dns,
        Collection $sds,
        callable $formatDate
    ): array {
        $isApproved = static function ($row): bool {
            return strtoupper(trim((string) ($row->Status ?? ''))) === 'APPROVED';
        };

        $subDocs = [];

        foreach ($annexes as $annex) {
            if (!$isApproved($annex)) {
                continue;
            }
            $matchSi = !empty($refs) && !empty($annex->SI_No) && in_array($annex->SI_No, $refs, true);
            $matchOrder = !empty($annex->Order_No) && (($orderNo && $annex->Order_No === $orderNo) || ($bookingNo && $annex->Order_No === $bookingNo));
            $matchContract = empty($annex->SI_No) && empty($annex->Order_No) && $contractNo && ($annex->Contract_No ?? null) === $contractNo;

            if ($matchSi || $matchOrder || $matchContract) {
                $subDocs[] = [
                    'code'        => 'Annex VII',
                    'label'       => 'Annex VII',
                    'doc_no'      => $annex->Annex_No ?: '-',
                    'date'        => $formatDate($annex->approved_at ?? ($annex->created_at ?? null)),
                    'preview_url' => !empty($annex->Annex_No)
                        ? url('cns-export-annex-print') . '?Task=' . urlencode($annex->Annex_No)
                        : null,
                ];
            }
        }

        foreach ($dns as $dn) {
            if (!$isApproved($dn)) {
                continue;
            }
            $dnBc = trim((string) ($dn->BC_No ?? ''));
            if ($dnBc !== '') {
                if (!$bookingNo || $dnBc !== $bookingNo) {
                    continue;
                }
            } elseif (!$contractNo || ($dn->Contract_No ?? null) !== $contractNo) {
                continue;
            }
            $subDocs[] = [
                'code'        => 'DN',
                'label'       => 'Delivery Note (DN)',
                'doc_no'      => $dn->DN_No ?: '-',
                'date'        => $formatDate($dn->Delivery_Date ?? ($dn->approved_at ?? null)),
                'preview_url' => !empty($dn->DN_No)
                    ? url('cns-export-delivery-note-print') . '?Task=' . urlencode($dn->DN_No)
                    : null,
            ];
        }

        foreach ($sds as $sd) {
            if (!$isApproved($sd) || !$contractNo || ($sd->Contract_No ?? null) !== $contractNo) {
                continue;
            }
            $subDocs[] = [
                'code'        => 'SD',
                'label'       => 'Sustainability Declaration (SD)',
                'doc_no'      => $sd->SD_No ?: '-',
                'date'        => $formatDate($sd->Issue_Date ?? ($sd->approved_at ?? null)),
                'preview_url' => !empty($sd->SD_No)
                    ? url('cns-export-sustainability-declaration-print') . '?Task=' . urlencode($sd->SD_No)
                    : null,
            ];
        }

        $cowDoc = $this->resolveSurveyorDocument('cow', $booking, $si);
        if ($cowDoc && strtoupper(trim((string) ($cowDoc['status'] ?? ''))) === 'APPROVED') {
            $subDocs[] = [
                'code'        => 'COW',
                'label'       => 'COW Surveyor',
                'doc_no'      => $cowDoc['doc_no'] ?? '-',
                'date'        => $formatDate($cowDoc['date'] ?? null),
                'preview_url' => $cowDoc['preview_url'] ?? null,
            ];
        }

        return $subDocs;
    }

    /**
     * Stub resolver for COW Surveyor until authoritative source table/endpoint is confirmed.
     */
    protected function resolveSurveyorDocument(string $type, $booking, $si = null): ?array
    {
        // shortcut: COW Surveyor source TBD, wire when confirmed
        return null;
    }

    protected function paginateChecklistBookings(Request $request): array
    {
        $query = BuyerContractBooking::with('contract');

        if ($request->filled('tgl1') && $request->filled('tgl2')) {
            $query->whereBetween('Created_Date', [
                $request->input('tgl1') . ' 00:00:00',
                $request->input('tgl2') . ' 23:59:59',
            ]);
        }

        if (!$request->filled('sort_col')) {
            $query->orderByDesc('ID');
        }

        $colMap = [
            2 => 'Booking_No',
            3 => 'Created_Date',
            6 => 'Checklist_Verified',
            7 => 'Checklist_Notes',
        ];

        return DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map'           => $colMap,
            'default_page_size' => 25,
        ]);
    }

    protected function fetchContractBookings(array $contractIds): Collection
    {
        return BuyerContractBooking::with('contract')->whereIn('Contract_ID', $contractIds)->get();
    }

    protected function findBookingWithContractOrFail($id): BuyerContractBooking
    {
        return BuyerContractBooking::with('contract')->findOrFail($id);
    }

    protected function persistBooking(BuyerContractBooking $booking): void
    {
        $booking->save();
    }

    protected function fetchProgressSources(Collection $bookings): array
    {
        $bookingIds = $bookings->pluck('ID')->filter()->unique()->values()->all();
        $bookingNos = $bookings->pluck('Booking_No')->filter()->unique()->values()->all();
        $contractNos = $bookings->map(function ($b) {
            return $b->contract ? $b->contract->Contract_No : null;
        })->filter()->unique()->values()->all();

        $vesselSchedules = !empty($bookingNos)
            ? VesselSchedule::where(function ($q) use ($bookingNos) {
                $q->whereIn('Booking_No', $bookingNos)->orWhereIn('Order_No', $bookingNos);
            })->get()
            : collect();

        $orderNos = array_values(array_unique(array_merge(
            $bookingNos,
            $vesselSchedules->pluck('Order_No')->filter()->all()
        )));

        $containerCounts = !empty($orderNos)
            ? VesselScheduleDtl::active()
                ->whereIn('Order_No', $orderNos)
                ->selectRaw('Order_No, COUNT(*) as total')
                ->groupBy('Order_No')
                ->pluck('total', 'Order_No')
            : collect();

        $shippingInstructions = !empty($orderNos)
            ? ShippingInstruction::whereIn('Order_No', $orderNos)->get()
            : collect();

        $siNos = $shippingInstructions->pluck('SI_No')->filter()->unique()->values()->all();
        $finalSiNos = $shippingInstructions->pluck('Final_SI_No')->filter()->unique()->values()->all();
        $allSiRefs = array_values(array_unique(array_merge($siNos, $finalSiNos)));

        $clearances = (!empty($bookingIds) && Schema::hasTable('cns_customs_clearances'))
            ? CnsCustomsClearance::whereIn('booking_id', $bookingIds)
                ->get([
                    'id',
                    'booking_id',
                    'doc_3d_path',
                    'doc_3d_name',
                    'peb_path',
                    'peb_name',
                    'npe_path',
                    'npe_name',
                    'created_at',
                    'updated_at',
                ])
            : collect();

        $hasPlBcCol = Schema::hasTable('packing_lists') && Schema::hasColumn('packing_lists', 'BC_No');
        $plCols = ['Packing_List_No', 'SI_No', 'Final_SI_No', 'Status', 'PL_Date'];
        if ($hasPlBcCol) {
            $plCols[] = 'BC_No';
        }
        $pls = (!empty($allSiRefs) || ($hasPlBcCol && !empty($bookingNos)))
            ? PackingList::where(function ($q) use ($allSiRefs, $bookingNos, $hasPlBcCol) {
                $applied = false;
                if (!empty($allSiRefs)) {
                    $q->whereIn('SI_No', $allSiRefs)->orWhereIn('Final_SI_No', $allSiRefs);
                    $applied = true;
                }
                if ($hasPlBcCol && !empty($bookingNos)) {
                    $applied ? $q->orWhereIn('BC_No', $bookingNos) : $q->whereIn('BC_No', $bookingNos);
                }
            })->get($plCols)
            : collect();

        $cis = !empty($allSiRefs)
            ? CommercialInvoice::whereIn('SI_No', $allSiRefs)->get(['Inv_No', 'SI_No', 'Inv_Date'])
            : collect();

        $wbts = !empty($allSiRefs)
            ? WeightBridgeTicket::where(function ($q) use ($allSiRefs) {
                $q->whereIn('SI_No', $allSiRefs)->orWhereIn('Final_SI_No', $allSiRefs);
            })->get(['WBT_No', 'SI_No', 'Final_SI_No', 'Departure_Date'])
            : collect();

        $sds = !empty($contractNos)
            ? SustainabilityDeclaration::whereIn('Contract_No', $contractNos)->get()
            : collect();

        $hasDnBcCol = Schema::hasTable('delivery_notes') && Schema::hasColumn('delivery_notes', 'BC_No');
        $dns = (!empty($contractNos) || ($hasDnBcCol && !empty($bookingNos)))
            ? DeliveryNote::where(function ($q) use ($contractNos, $bookingNos, $hasDnBcCol) {
                $applied = false;
                if ($hasDnBcCol && !empty($bookingNos)) {
                    $q->whereIn('BC_No', $bookingNos);
                    $applied = true;
                }
                if (!empty($contractNos)) {
                    $applied ? $q->orWhereIn('Contract_No', $contractNos) : $q->whereIn('Contract_No', $contractNos);
                }
            })->get()
            : collect();

        $annexes = (!empty($allSiRefs) || !empty($orderNos) || !empty($contractNos))
            ? AnnexVII::where(function ($q) use ($allSiRefs, $orderNos, $contractNos) {
                $applied = false;
                if (!empty($allSiRefs)) {
                    $q->whereIn('SI_No', $allSiRefs);
                    $applied = true;
                }
                if (!empty($orderNos)) {
                    $applied ? $q->orWhereIn('Order_No', $orderNos) : $q->whereIn('Order_No', $orderNos);
                    $applied = true;
                }
                if (!empty($contractNos)) {
                    $applied ? $q->orWhereIn('Contract_No', $contractNos) : $q->whereIn('Contract_No', $contractNos);
                }
            })->get()
            : collect();

        $vsIds = $vesselSchedules->pluck('id')->filter()->unique()->values()->all();
        $invoicingRequests = (!empty($vsIds) || !empty($orderNos))
            ? ExportInvoicingRequest::whereNotNull('transfer_proof_path')
                ->where(function ($q) use ($vsIds, $orderNos) {
                    if (!empty($vsIds)) {
                        $q->whereIn('vessel_schedule_id', $vsIds);
                    }
                    if (!empty($orderNos)) {
                        $method = !empty($vsIds) ? 'orWhereIn' : 'whereIn';
                        $q->{$method}('order_no', $orderNos);
                    }
                })
                ->orderByDesc('id')
                ->get(['id', 'vessel_schedule_id', 'order_no', 'transfer_proof_path', 'transfer_proof_name'])
            : collect();

        $coaSurveyors = (!empty($bookingIds) && Schema::hasTable('cns_quality_controls'))
            ? DB::table('cns_quality_controls')
                ->where('QC_Type', 'SURVEYOR')
                ->where('Is_Deleted', 0)
                ->whereIn('Booking_ID', $bookingIds)
                ->whereNotNull('Attachment_Path')
                ->where('Attachment_Path', '!=', '')
                ->orderByDesc('id')
                ->get(['id', 'Booking_ID', 'Kode_QC', 'Inspection_Date', 'Attachment_Path', 'QC_Type', 'Is_Deleted'])
            : collect();

        return [
            'vesselSchedules'      => $vesselSchedules,
            'containerCounts'      => $containerCounts,
            'shippingInstructions' => $shippingInstructions,
            'clearances'           => $clearances,
            'pls'                  => $pls,
            'cis'                  => $cis,
            'wbts'                 => $wbts,
            'sds'                  => $sds,
            'dns'                  => $dns,
            'annexes'              => $annexes,
            'invoicingRequests'    => $invoicingRequests,
            'coaSurveyors'         => $coaSurveyors,
        ];
    }
}
