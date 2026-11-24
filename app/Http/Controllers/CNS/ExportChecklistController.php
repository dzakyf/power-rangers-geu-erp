<?php

namespace App\Http\Controllers\CNS;

use App\AnnexVII;
use App\BuyerContractBooking;
use App\CertificateOfOrigin;
use App\CommercialInvoice;
use App\DeliveryNote;
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

class ExportChecklistController extends Controller
{
    public function index()
    {
        return view('cns.export.checklist');
    }

    public function indexXml(Request $request)
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

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => $colMap,
            'default_page_size' => 25,
        ]);

        $bookings = $paginated['rows'];

        $contractProgressById = $this->buildContractProgress($bookings);
        $progressByBookingId = $contractProgressById['by_booking'];
        $contractStats = $contractProgressById['by_contract'];
        $stageKeys = ['contract', 'booking', 'vs', 'stuffing', 'si', 'fsi', 'pl', 'ci', 'wbt', 'coo', 'sd', 'dn', 'annex', 'payment'];

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

            $stageCodes = [
                'contract' => 'KT',
                'booking'  => 'BC',
                'vs'       => 'VS',
                'stuffing' => 'ST',
                'si'       => 'SI',
                'fsi'      => 'FSI',
                'pl'       => 'PL',
                'ci'       => 'CI',
                'wbt'      => 'WBT',
                'coo'      => 'COO',
                'sd'       => 'SD',
                'dn'       => 'DN',
                'annex'    => 'AX',
                'payment'  => 'PAY',
            ];

            $stepsHtml = '';
            $activeMarked = false;
            $totalStages = count($stageKeys);

            foreach ($stageKeys as $idx => $key) {
                $st = $stages[$key];
                $isDone = (bool) $st['exists'];
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

                $statusText = $isDone ? 'Lengkap' : 'Belum';
                $tooltip = htmlspecialchars(
                    $st['label'] . ' (' . $statusText . ') | No: ' . $st['doc_no'] . ' | Tgl: ' . ($st['date'] ?? '-'),
                    ENT_QUOTES,
                    'UTF-8'
                );
                $code = $stageCodes[$key];

                $stepsHtml .= "<span title=\"{$tooltip}\" style=\"display:inline-flex;flex-direction:column;align-items:center;justify-content:center;min-width:30px;padding:3px 5px;border-radius:5px;background:{$bg};border:1px solid {$border};color:{$color};line-height:1.1;\">"
                    . "<span style=\"font-size:10px;font-weight:700;\">{$icon}</span>"
                    . "<span style=\"font-size:9px;font-weight:700;letter-spacing:0.3px;\">{$code}</span>"
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
        $booking = BuyerContractBooking::with('contract')->findOrFail($id);
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

        $booking = BuyerContractBooking::with('contract')->findOrFail($validated['id']);
        $stages = $this->buildProgressData(collect([$booking]))[$booking->ID]['stages'];
        $total = count($stages);
        $completed = collect($stages)->filter(function ($item) {
            return $item['exists'];
        })->count();

        if ($completed < $total) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data BC belum 100% ({$completed}/{$total} tahap). Lengkapi seluruh tahap terlebih dahulu sebelum menyimpan verifikasi.",
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

        $booking->save();

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
     * Compute contract-level progress % across all BCs belonging to each contract.
     */
    private function buildContractProgress(Collection $bookings): array
    {
        if ($bookings->isEmpty()) {
            return ['by_booking' => [], 'by_contract' => []];
        }

        $contractIds = $bookings->pluck('Contract_ID')->filter()->unique()->values()->all();
        $allContractBookings = !empty($contractIds)
            ? BuyerContractBooking::with('contract')->whereIn('Contract_ID', $contractIds)->get()
            : $bookings;

        $byBooking = $this->buildProgressData($allContractBookings);

        $byContract = [];
        foreach ($allContractBookings->groupBy('Contract_ID') as $contractId => $group) {
            $bcCount = $group->count();
            $completedSum = 0;
            $stageTotal = 0;

            foreach ($group as $b) {
                $stages = $byBooking[$b->ID]['stages'] ?? [];
                $stageTotal += count($stages);
                $completedSum += collect($stages)->filter(function ($item) {
                    return $item['exists'];
                })->count();
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
     * Build 13-stage export progress (Contract & Booking Confirmation through Annex VII) in batch.
     */
    private function buildProgressData(Collection $bookings): array
    {
        if ($bookings->isEmpty()) {
            return [];
        }

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

        $pls = !empty($allSiRefs)
            ? PackingList::where(function ($q) use ($allSiRefs) {
                $q->whereIn('SI_No', $allSiRefs)->orWhereIn('Final_SI_No', $allSiRefs);
            })->get(['Packing_List_No', 'SI_No', 'Final_SI_No', 'Status', 'PL_Date'])
            : collect();

        $cis = !empty($allSiRefs)
            ? CommercialInvoice::whereIn('SI_No', $allSiRefs)->get(['Inv_No', 'SI_No', 'Inv_Date'])
            : collect();

        $wbts = !empty($allSiRefs)
            ? WeightBridgeTicket::where(function ($q) use ($allSiRefs) {
                $q->whereIn('SI_No', $allSiRefs)->orWhereIn('Final_SI_No', $allSiRefs);
            })->get(['WBT_No', 'SI_No', 'Final_SI_No', 'Departure_Date'])
            : collect();

        $coos = !empty($allSiRefs)
            ? CertificateOfOrigin::where(function ($q) use ($allSiRefs) {
                $q->whereIn('Fin_Inv_No', $allSiRefs);
            })->get(['COO_No', 'Fin_Inv_No', 'Created_Date'])
            : collect();

        $sds = !empty($contractNos)
            ? SustainabilityDeclaration::whereIn('Contract_No', $contractNos)->get(['SD_No', 'Contract_No', 'Issue_Date'])
            : collect();

        $dns = !empty($contractNos)
            ? DeliveryNote::whereIn('Contract_No', $contractNos)->get(['DN_No', 'Contract_No', 'Delivery_Date'])
            : collect();

        $annexes = !empty($allSiRefs)
            ? AnnexVII::whereIn('SI_No', $allSiRefs)->get(['Annex_No', 'SI_No', 'created_at'])
            : collect();

        $result = [];
        foreach ($bookings as $booking) {
            $contract = $booking->contract;
            $contractNo = $contract ? $contract->Contract_No : null;
            $bookingNo = $booking->Booking_No;

            $vs = $vesselSchedules->first(function ($row) use ($bookingNo) {
                return $row->Booking_No === $bookingNo || $row->Order_No === $bookingNo;
            });

            $orderNo = $vs ? $vs->Order_No : $bookingNo;
            $si = $shippingInstructions->first(function ($row) use ($orderNo, $bookingNo) {
                return $row->Order_No === $orderNo || $row->Order_No === $bookingNo;
            });

            $refs = $si ? array_filter([$si->SI_No, $si->Final_SI_No]) : [];

            $plMatch = !empty($refs) ? $pls->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true) || in_array($row->Final_SI_No, $refs, true);
            }) : null;
            $ciMatch = !empty($refs) ? $cis->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true);
            }) : null;
            $wbtMatch = !empty($refs) ? $wbts->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true) || in_array($row->Final_SI_No, $refs, true);
            }) : null;
            $cooMatch = !empty($refs) ? $coos->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true) || in_array($row->Final_SI_No, $refs, true);
            }) : null;
            $sdMatch = $contractNo ? $sds->first(function ($row) use ($contractNo) {
                return $row->Contract_No === $contractNo;
            }) : null;
            $dnMatch = $contractNo ? $dns->first(function ($row) use ($contractNo) {
                return $row->Contract_No === $contractNo;
            }) : null;
            $annexMatch = !empty($refs) ? $annexes->first(function ($row) use ($refs) {
                return in_array($row->SI_No, $refs, true);
            }) : null;

            $hasFsi = $si && (!empty($si->Final_SI_No) || strtoupper((string) $si->Status) === 'FINAL');
            $containerCount = (int) ($containerCounts[$orderNo] ?? 0);
            $stuffingDone = $vs && !empty($vs->Checklist_Done_Kapal_Berangkat);

            $formatDate = function ($value) {
                if (empty($value)) {
                    return '-';
                }
                $ts = strtotime((string) $value);
                return $ts !== false ? date('d M Y', $ts) : '-';
            };

            $result[$booking->ID] = [
                'vs'     => $vs,
                'si'     => $si,
                'stages' => [
                    'contract' => [
                        'short'       => 'Kontrak',
                        'label'       => 'Kontrak Pembelian',
                        'exists'      => (bool) $contract,
                        'doc_no'      => $contractNo ?: '-',
                        'date'        => $contract ? $formatDate($contract->Created_Date ?? null) : '-',
                        'note'        => $contract ? ($contract->Status ?: 'Aktif') : '-',
                        'preview_url' => ($contract && !empty($contract->Contract_File))
                            ? route('cns-procurement-buyer-contract.file', ['ID' => $contract->ID], false)
                            : null,
                    ],
                    'booking' => [
                        'short'       => 'BC',
                        'label'       => 'Booking Confirmation',
                        'exists'      => !empty($bookingNo),
                        'doc_no'      => $bookingNo ?: '-',
                        'date'        => $formatDate($booking->Created_Date ?? null),
                        'note'        => $booking->Label ?: 'Tersedia',
                        'preview_url' => !empty($booking->Booking_File)
                            ? route('cns-procurement-buyer-contract.booking-file', ['ID' => $booking->ID], false)
                            : null,
                    ],
                    'vs' => [
                        'short'       => 'VS',
                        'label'       => 'Vessel Schedule',
                        'exists'      => (bool) $vs,
                        'doc_no'      => $vs ? ($vs->Vessel ?: $vs->Order_No) : '-',
                        'date'        => $vs ? $formatDate($vs->Created_Date ?? null) : '-',
                        'note'        => $vs ? ($vs->ETD ? 'ETD ' . date('d M Y', strtotime($vs->ETD)) : 'Terjadwal') : '-',
                        'preview_url' => null,
                    ],
                    'stuffing' => [
                        'short'       => 'Stuffing',
                        'label'       => 'Stuffing',
                        'exists'      => $stuffingDone,
                        'doc_no'      => $containerCount > 0 ? $containerCount . ' Kontainer' : '-',
                        'date'        => $vs ? $formatDate($vs->Checklist_Updated_Date ?? ($vs->Stuffing ?? null)) : '-',
                        'note'        => $stuffingDone ? 'Done / Kapal Berangkat' : ($containerCount > 0 ? 'Proses Stuffing' : 'Belum'),
                        'preview_url' => null,
                    ],
                    'si' => [
                        'short'       => 'SI',
                        'label'       => 'Shipping Instructions (SI)',
                        'exists'      => (bool) $si,
                        'doc_no'      => $si ? ($si->SI_No ?: '-') : '-',
                        'date'        => $si ? $formatDate($si->SI_Date ?? null) : '-',
                        'note'        => $si ? ($si->Status ?: '-') : '-',
                        'preview_url' => ($si && !empty($si->SI_No))
                            ? url('cns-export-shipping-instruction-print') . '?Task=' . urlencode($si->SI_No)
                            : null,
                    ],
                    'fsi' => [
                        'short'       => 'FSI',
                        'label'       => 'Final Shipping Instructions (FSI)',
                        'exists'      => $hasFsi,
                        'doc_no'      => $si ? ($si->Final_SI_No ?: '-') : '-',
                        'date'        => ($hasFsi && $si) ? $formatDate($si->Final_SI_Date ?? ($si->SI_Date ?? null)) : '-',
                        'note'        => $hasFsi ? 'FINAL' : 'Belum Final',
                        'preview_url' => ($hasFsi && $si && !empty($si->Final_SI_No))
                            ? url('cns-export-final-shipping-instruction-print') . '?Task=' . urlencode($si->Final_SI_No)
                            : null,
                    ],
                    'pl' => [
                        'short'       => 'PL',
                        'label'       => 'Packing List (PL)',
                        'exists'      => (bool) $plMatch,
                        'doc_no'      => $plMatch->Packing_List_No ?? '-',
                        'date'        => $plMatch ? $formatDate($plMatch->PL_Date ?? null) : '-',
                        'note'        => $plMatch->Status ?? '-',
                        'preview_url' => ($plMatch && !empty($plMatch->Packing_List_No))
                            ? url('cns-export-packing-list-print') . '?Task=' . urlencode($plMatch->Packing_List_No)
                            : null,
                    ],
                    'ci' => [
                        'short'       => 'CI',
                        'label'       => 'Commercial Invoice (CI)',
                        'exists'      => (bool) $ciMatch,
                        'doc_no'      => $ciMatch->Inv_No ?? '-',
                        'date'        => $ciMatch ? $formatDate($ciMatch->Inv_Date ?? null) : '-',
                        'note'        => $ciMatch ? 'Tersedia' : '-',
                        'preview_url' => ($ciMatch && !empty($ciMatch->Inv_No))
                            ? url('cns-export-commercial-invoice-print') . '?Task=' . urlencode($ciMatch->Inv_No)
                            : null,
                    ],
                    'wbt' => [
                        'short'       => 'WBT',
                        'label'       => 'Weight Bridge Ticket (WBT)',
                        'exists'      => (bool) $wbtMatch,
                        'doc_no'      => $wbtMatch->WBT_No ?? '-',
                        'date'        => $wbtMatch ? $formatDate($wbtMatch->Departure_Date ?? null) : '-',
                        'note'        => $wbtMatch ? 'Tersedia' : '-',
                        'preview_url' => ($wbtMatch && !empty($wbtMatch->WBT_No))
                            ? url('cns-export-weight-bridge-ticket-print') . '?Task=' . urlencode($wbtMatch->WBT_No)
                            : null,
                    ],
                    'coo' => [
                        'short'       => 'COO',
                        'label'       => 'Certificate of Origin (COO)',
                        'exists'      => (bool) $cooMatch,
                        'doc_no'      => $cooMatch->COO_No ?? '-',
                        'date'        => $cooMatch ? $formatDate($cooMatch->Created_Date ?? null) : '-',
                        'note'        => $cooMatch ? 'Tersedia' : '-',
                        'preview_url' => ($cooMatch && !empty($cooMatch->COO_No))
                            ? url('cns-export-certificate-origin-print') . '?Task=' . urlencode($cooMatch->COO_No)
                            : null,
                    ],
                    'sd' => [
                        'short'       => 'SD',
                        'label'       => 'Sustainability Declaration (SD)',
                        'exists'      => (bool) $sdMatch,
                        'doc_no'      => $sdMatch->SD_No ?? '-',
                        'date'        => $sdMatch ? $formatDate($sdMatch->Issue_Date ?? null) : '-',
                        'note'        => $sdMatch ? 'By Contract' : '-',
                        'preview_url' => ($sdMatch && !empty($sdMatch->SD_No))
                            ? url('cns-export-sustainability-declaration-print') . '?Task=' . urlencode($sdMatch->SD_No)
                            : null,
                    ],
                    'dn' => [
                        'short'       => 'DN',
                        'label'       => 'Delivery Note (DN)',
                        'exists'      => (bool) $dnMatch,
                        'doc_no'      => $dnMatch->DN_No ?? '-',
                        'date'        => $dnMatch ? $formatDate($dnMatch->Delivery_Date ?? null) : '-',
                        'note'        => $dnMatch ? 'By Contract' : '-',
                        'preview_url' => ($dnMatch && !empty($dnMatch->DN_No))
                            ? url('cns-export-delivery-note-print') . '?Task=' . urlencode($dnMatch->DN_No)
                            : null,
                    ],
                    'annex' => [
                        'short'       => 'Annex VII',
                        'label'       => 'Annex VII',
                        'exists'      => (bool) $annexMatch,
                        'doc_no'      => $annexMatch->Annex_No ?? '-',
                        'date'        => $annexMatch ? $formatDate($annexMatch->created_at ?? null) : '-',
                        'note'        => $annexMatch ? 'Tersedia' : '-',
                        'preview_url' => ($annexMatch && !empty($annexMatch->Annex_No))
                            ? url('cns-export-annex-print') . '?Task=' . urlencode($annexMatch->Annex_No)
                            : null,
                    ],
                    'payment' => [
                        'short'       => 'Payment',
                        'label'       => 'Payment (Finance)',
                        'exists'      => !empty($booking->Payment_Approved),
                        'doc_no'      => $booking->Finance_Payment_Ref ?: '-',
                        'date'        => !empty($booking->Payment_Approved_At)
                            ? $formatDate($booking->Payment_Approved_At)
                            : (!empty($booking->Finance_Payment_Date) ? $formatDate($booking->Finance_Payment_Date) : '-'),
                        'note'        => !empty($booking->Payment_Approved)
                            ? 'Approved by ' . ($booking->Payment_Approved_By ?: '-')
                            : (!empty($booking->Finance_Payment_Verified) ? 'Dana Masuk (Menunggu Approve CNS)' : 'Menunggu Finance'),
                        'preview_url' => null,
                    ],
                ],
            ];
        }

        return $result;
    }
}
