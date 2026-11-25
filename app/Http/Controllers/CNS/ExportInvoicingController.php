<?php

namespace App\Http\Controllers\CNS;

use App\BuyerContractBooking;
use App\ExportInvoicingRequest;
use App\Http\Controllers\Controller;
use App\PackingList;
use App\Services\NotificationService;
use App\ShippingInstruction;
use App\Support\DhtmlxGridPagination;
use App\VesselSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExportInvoicingController extends Controller
{
    public function index()
    {
        return view('cns.finance.export-invoicing.index');
    }

    public function indexXml(Request $request)
    {
        $query = VesselSchedule::where('Checklist_Get_In_CY', 1);

        if ($request->filled('tgl1') && $request->filled('tgl2')) {
            $query->whereBetween('Stuffing', [$request->input('tgl1'), $request->input('tgl2')]);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === ExportInvoicingRequest::STATUS_READY) {
                $submittedIds = ExportInvoicingRequest::pluck('vessel_schedule_id')->all();
                if (!empty($submittedIds)) {
                    $query->whereNotIn('id', $submittedIds);
                }
            } else {
                $matchingIds = ExportInvoicingRequest::where('status', $status)->pluck('vessel_schedule_id')->all();
                $query->whereIn('id', !empty($matchingIds) ? $matchingIds : [0]);
            }
        }

        if (!$request->filled('sort_col')) {
            $query->orderByDesc('id');
        }

        $paginated = DhtmlxGridPagination::paginateQuery($query, $request, [
            'col_map' => [
                1 => 'Order_No',
                2 => 'Stuffing_No',
                3 => 'Vessel',
                4 => 'Stuffing',
            ],
            'filter_map' => [
                1 => 'Order_No',
                2 => 'Stuffing_No',
                3 => 'Vessel',
                // shortcut: buyer/contract/PL filter matches ExportInvoicingRequest records; unsubmitted rows' linked metadata is resolved post-query
                5 => function ($q, string $term) {
                    $ids = ExportInvoicingRequest::where('buyer_name', 'like', "%{$term}%")->pluck('vessel_schedule_id')->all();
                    $q->whereIn('id', !empty($ids) ? $ids : [0]);
                },
                6 => function ($q, string $term) {
                    $ids = ExportInvoicingRequest::where('contract_no', 'like', "%{$term}%")->pluck('vessel_schedule_id')->all();
                    $q->whereIn('id', !empty($ids) ? $ids : [0]);
                },
                7 => function ($q, string $term) {
                    $ids = ExportInvoicingRequest::where('packing_list_no', 'like', "%{$term}%")->pluck('vessel_schedule_id')->all();
                    $q->whereIn('id', !empty($ids) ? $ids : [0]);
                },
                8 => function ($q, string $term) {
                    if ($term === ExportInvoicingRequest::STATUS_READY) {
                        $submittedIds = ExportInvoicingRequest::pluck('vessel_schedule_id')->all();
                        if (!empty($submittedIds)) {
                            $q->whereNotIn('id', $submittedIds);
                        }
                    } else {
                        $ids = ExportInvoicingRequest::where('status', 'like', "%{$term}%")->pluck('vessel_schedule_id')->all();
                        $q->whereIn('id', !empty($ids) ? $ids : [0]);
                    }
                },
                9 => function ($q, string $term) {
                    $ids = ExportInvoicingRequest::where('submitted_by', 'like', "%{$term}%")->pluck('vessel_schedule_id')->all();
                    $q->whereIn('id', !empty($ids) ? $ids : [0]);
                },
            ],
            'default_page_size' => 25,
        ]);

        $schedules = $paginated['rows'];
        $scheduleIds = $schedules->pluck('id')->all();

        $requestsBySchedule = !empty($scheduleIds)
            ? ExportInvoicingRequest::whereIn('vessel_schedule_id', $scheduleIds)->get()->keyBy('vessel_schedule_id')
            : collect();

        $linkedMeta = $this->resolveLinkedMetadata($schedules);

        $bodyXml = '';
        $nomor = $paginated['posStart'] + 1;

        foreach ($schedules as $vs) {
            $req = $requestsBySchedule->get($vs->id);
            $meta = $linkedMeta[$vs->id] ?? ['contract_no' => '-', 'packing_list_no' => '-', 'buyer_name' => '-'];

            $status = $req ? $req->status : ExportInvoicingRequest::STATUS_READY;
            $contractNo = $req && $req->contract_no ? $req->contract_no : $meta['contract_no'];
            $plNo = $req && $req->packing_list_no ? $req->packing_list_no : $meta['packing_list_no'];
            $buyerName = $req && $req->buyer_name ? $req->buyer_name : $meta['buyer_name'];
            $stuffingDate = $vs->Stuffing ? date('d M Y', strtotime($vs->Stuffing)) : '-';
            $submittedInfo = $req && $req->submitted_at
                ? $req->submitted_by . ' (' . $req->submitted_at->format('d M Y H:i') . ')'
                : '-';

            $bodyXml .= '<row id="' . (int) $vs->id . '">';
            $bodyXml .= '<cell>' . $nomor++ . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) ($vs->Order_No ?: '-')) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) ($vs->Stuffing_No ?: '-')) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) ($vs->Vessel ?: '-')) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($stuffingDate) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $buyerName) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $contractNo) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars((string) $plNo) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($status) . '</cell>';
            $bodyXml .= '<cell>' . htmlspecialchars($submittedInfo) . '</cell>';
            $bodyXml .= '</row>';
        }

        return DhtmlxGridPagination::response($paginated['total'], $paginated['posStart'], $bodyXml);
    }

    public function detail($vesselScheduleId)
    {
        $vs = VesselSchedule::findOrFail($vesselScheduleId);
        $meta = $this->resolveLinkedMetadata(collect([$vs]))[$vs->id] ?? [
            'contract_no' => '-',
            'packing_list_no' => '-',
            'buyer_name' => '-',
        ];

        $req = ExportInvoicingRequest::where('vessel_schedule_id', $vs->id)->first();

        return response()->json([
            'vessel_schedule' => [
                'id' => $vs->id,
                'order_no' => $vs->Order_No,
                'stuffing_no' => $vs->Stuffing_No,
                'vessel' => $vs->Vessel,
                'pol' => $vs->POL,
                'pod' => $vs->POD,
                'qty' => $vs->Qty,
                'stuffing_date' => $vs->Stuffing,
                'checklist_get_in_cy' => (int) $vs->Checklist_Get_In_CY,
            ],
            'linked' => $meta,
            'request' => $req ? [
                'id' => $req->id,
                'status' => $req->status,
                'contract_no' => $req->contract_no,
                'packing_list_no' => $req->packing_list_no,
                'buyer_name' => $req->buyer_name,
                'contract_file_name' => $req->contract_file_name,
                'packing_list_file_name' => $req->packing_list_file_name,
                'contract_file_url' => url("finance-export-invoicing-file/{$req->id}/contract"),
                'packing_list_file_url' => url("finance-export-invoicing-file/{$req->id}/packing_list"),
                'cns_notes' => $req->cns_notes,
                'submitted_by' => $req->submitted_by,
                'submitted_at' => $req->submitted_at ? $req->submitted_at->format('d M Y H:i') : null,
                'reviewed_by' => $req->reviewed_by,
                'reviewed_at' => $req->reviewed_at ? $req->reviewed_at->format('d M Y H:i') : null,
                'finance_notes' => $req->finance_notes,
                'rejection_reason' => $req->rejection_reason,
            ] : null,
        ]);
    }

    public function submit(Request $request, NotificationService $notifications)
    {
        $validated = $request->validate([
            'vessel_schedule_id' => 'required|integer|exists:vessel_schedules,id',
            'contract_file' => 'required|file|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:10240',
            'packing_list_file' => 'required|file|mimes:pdf,xlsx,xls,jpg,jpeg,png|max:10240',
            'cns_notes' => 'nullable|string|max:2000',
        ]);

        $vs = VesselSchedule::findOrFail($validated['vessel_schedule_id']);
        if ((int) $vs->Checklist_Get_In_CY !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stuffing belum menyelesaikan tahap Get In CY.',
            ], 422);
        }

        $existing = ExportInvoicingRequest::where('vessel_schedule_id', $vs->id)->first();
        if ($existing && $existing->status !== ExportInvoicingRequest::STATUS_REJECTED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan invoice sudah berstatus ' . $existing->status . ' dan tidak dapat diajukan ulang.',
            ], 422);
        }

        $meta = $this->resolveLinkedMetadata(collect([$vs]))[$vs->id] ?? [
            'contract_no' => null,
            'packing_list_no' => null,
            'buyer_name' => null,
        ];

        $dir = 'export-invoicing/' . $vs->id;
        $contractFile = $request->file('contract_file');
        $plFile = $request->file('packing_list_file');

        $contractPath = $contractFile->store($dir, 'public');
        $plPath = $plFile->store($dir, 'public');

        $username = Auth::check() ? (Auth::user()->username ?? Auth::user()->name ?? 'CNS') : 'CNS';
        $isResubmit = $existing !== null;

        $record = ExportInvoicingRequest::updateOrCreate(
            ['vessel_schedule_id' => $vs->id],
            [
                'order_no' => (string) ($vs->Order_No ?: ''),
                'stuffing_no' => $vs->Stuffing_No,
                'contract_no' => $meta['contract_no'] !== '-' ? $meta['contract_no'] : null,
                'packing_list_no' => $meta['packing_list_no'] !== '-' ? $meta['packing_list_no'] : null,
                'buyer_name' => $meta['buyer_name'] !== '-' ? $meta['buyer_name'] : null,
                'status' => ExportInvoicingRequest::STATUS_PENDING_FAT,
                'contract_file_path' => $contractPath,
                'contract_file_name' => $contractFile->getClientOriginalName(),
                'packing_list_file_path' => $plPath,
                'packing_list_file_name' => $plFile->getClientOriginalName(),
                'cns_notes' => $validated['cns_notes'] ?? null,
                'submitted_by' => $username,
                'submitted_at' => now(),
                'rejection_reason' => null,
            ]
        );

        $actionLabel = $isResubmit ? 'Revisi Pengajuan' : 'Pengajuan Baru';
        $notifications->makeNotification(
            "Export Invoicing: {$actionLabel}",
            "Order {$record->order_no} diajukan oleh {$username} dengan status pending FAT.",
            'EXPORT_INVOICING',
            $username,
            'FINANCE',
            'SENT',
            ['export_invoicing_request_id' => $record->id, 'order_no' => $record->order_no]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan Export Invoice berhasil dikirim ke Finance (status: pending FAT).',
            'data' => $record,
        ]);
    }

    public function approve(Request $request, $id, NotificationService $notifications)
    {
        $validated = $request->validate([
            'finance_notes' => 'nullable|string|max:2000',
        ]);

        $username = Auth::check() ? (Auth::user()->username ?? Auth::user()->name ?? 'Finance') : 'Finance';

        return $this->processApprove($id, $username, $validated['finance_notes'] ?? null, $notifications);
    }

    public function reject(Request $request, $id, NotificationService $notifications)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:3|max:2000',
            'finance_notes' => 'nullable|string|max:2000',
        ]);

        $username = Auth::check() ? (Auth::user()->username ?? Auth::user()->name ?? 'Finance') : 'Finance';

        return $this->processReject(
            $id,
            $username,
            $validated['rejection_reason'],
            $validated['finance_notes'] ?? null,
            $notifications
        );
    }

    public function apiIndex(Request $request)
    {
        if ($authError = $this->authorizeFinanceApi($request)) {
            return $authError;
        }

        $query = ExportInvoicingRequest::orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->paginate((int) $request->input('per_page', 25));
        $items->getCollection()->transform(function ($item) {
            return $this->formatApiItem($item);
        });

        return response()->json($items);
    }

    public function apiShow(Request $request, $id)
    {
        if ($authError = $this->authorizeFinanceApi($request)) {
            return $authError;
        }

        $item = ExportInvoicingRequest::findOrFail($id);

        return response()->json(['data' => $this->formatApiItem($item)]);
    }

    public function apiDownloadFile(Request $request, $id, $type)
    {
        if ($authError = $this->authorizeFinanceApi($request)) {
            return $authError;
        }

        return $this->downloadFile($id, $type);
    }

    public function apiApprove(Request $request, $id, NotificationService $notifications)
    {
        if ($authError = $this->authorizeFinanceApi($request)) {
            return $authError;
        }

        $validated = $request->validate([
            'reviewed_by' => 'nullable|string|max:100',
            'finance_notes' => 'nullable|string|max:2000',
        ]);

        return $this->processApprove(
            $id,
            $validated['reviewed_by'] ?? 'FAT',
            $validated['finance_notes'] ?? null,
            $notifications
        );
    }

    public function apiReject(Request $request, $id, NotificationService $notifications)
    {
        if ($authError = $this->authorizeFinanceApi($request)) {
            return $authError;
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:3|max:2000',
            'reviewed_by' => 'nullable|string|max:100',
            'finance_notes' => 'nullable|string|max:2000',
        ]);

        return $this->processReject(
            $id,
            $validated['reviewed_by'] ?? 'FAT',
            $validated['rejection_reason'],
            $validated['finance_notes'] ?? null,
            $notifications
        );
    }

    private function authorizeFinanceApi(Request $request)
    {
        $expectedToken = (string) config('services.finance.webhook_token');
        if ($expectedToken === '') {
            return response()->json(['message' => 'Finance webhook token is not configured.'], 503);
        }

        $providedToken = (string) $request->header('X-API-Key', '');
        if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return null;
    }

    private function processApprove($id, string $username, ?string $financeNotes, NotificationService $notifications)
    {
        $record = ExportInvoicingRequest::findOrFail($id);
        if ($record->status !== ExportInvoicingRequest::STATUS_PENDING_FAT) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pengajuan berstatus pending FAT yang dapat di-approve.',
            ], 422);
        }

        $record->update([
            'status' => ExportInvoicingRequest::STATUS_APPROVED,
            'reviewed_by' => $username,
            'reviewed_at' => now(),
            'finance_notes' => $financeNotes,
            'rejection_reason' => null,
        ]);

        $notifications->makeNotification(
            'Export Invoicing: Approved',
            "Pengajuan invoice Order {$record->order_no} telah di-approve oleh {$username}.",
            'EXPORT_INVOICING',
            $username,
            $record->submitted_by ?: 'CNS',
            'SENT',
            ['export_invoicing_request_id' => $record->id, 'order_no' => $record->order_no]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan Export Invoice berhasil di-approve.',
            'data' => $record,
        ]);
    }

    private function processReject($id, string $username, string $rejectionReason, ?string $financeNotes, NotificationService $notifications)
    {
        $record = ExportInvoicingRequest::findOrFail($id);
        if ($record->status !== ExportInvoicingRequest::STATUS_PENDING_FAT) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pengajuan berstatus pending FAT yang dapat di-reject.',
            ], 422);
        }

        $record->update([
            'status' => ExportInvoicingRequest::STATUS_REJECTED,
            'reviewed_by' => $username,
            'reviewed_at' => now(),
            'rejection_reason' => $rejectionReason,
            'finance_notes' => $financeNotes,
        ]);

        $notifications->makeNotification(
            'Export Invoicing: Rejected',
            "Pengajuan invoice Order {$record->order_no} ditolak oleh {$username}: {$rejectionReason}",
            'EXPORT_INVOICING',
            $username,
            $record->submitted_by ?: 'CNS',
            'SENT',
            ['export_invoicing_request_id' => $record->id, 'order_no' => $record->order_no]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan Export Invoice ditolak. Tim CNS dapat melakukan revisi dan upload ulang.',
            'data' => $record,
        ]);
    }

    private function formatApiItem(ExportInvoicingRequest $item): array
    {
        return [
            'id' => $item->id,
            'vessel_schedule_id' => $item->vessel_schedule_id,
            'order_no' => $item->order_no,
            'stuffing_no' => $item->stuffing_no,
            'contract_no' => $item->contract_no,
            'packing_list_no' => $item->packing_list_no,
            'buyer_name' => $item->buyer_name,
            'status' => $item->status,
            'contract_file_name' => $item->contract_file_name,
            'contract_file_url' => url("api/finance/export-invoicing/{$item->id}/file/contract"),
            'packing_list_file_name' => $item->packing_list_file_name,
            'packing_list_file_url' => url("api/finance/export-invoicing/{$item->id}/file/packing_list"),
            'cns_notes' => $item->cns_notes,
            'submitted_by' => $item->submitted_by,
            'submitted_at' => $item->submitted_at ? $item->submitted_at->toIso8601String() : null,
            'reviewed_by' => $item->reviewed_by,
            'reviewed_at' => $item->reviewed_at ? $item->reviewed_at->toIso8601String() : null,
            'finance_notes' => $item->finance_notes,
            'rejection_reason' => $item->rejection_reason,
        ];
    }

    public function downloadFile($id, $type)
    {
        $record = ExportInvoicingRequest::findOrFail($id);
        if ($type === 'contract') {
            $path = $record->contract_file_path;
            $name = $record->contract_file_name;
        } elseif ($type === 'packing_list') {
            $path = $record->packing_list_file_path;
            $name = $record->packing_list_file_name;
        } else {
            abort(404);
        }

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($path, $name ?: basename($path));
    }

    private function resolveLinkedMetadata($schedules)
    {
        $orderNos = $schedules->pluck('Order_No')->filter()->unique()->values()->all();
        $bookingNos = $schedules->pluck('Booking_No')->filter()->unique()->values()->all();
        $allBookingRefs = array_values(array_unique(array_merge($orderNos, $bookingNos)));

        $bookings = !empty($allBookingRefs)
            ? BuyerContractBooking::with('contract')->whereIn('Booking_No', $allBookingRefs)->get()->keyBy('Booking_No')
            : collect();

        $sis = !empty($orderNos)
            ? ShippingInstruction::whereIn('Order_No', $orderNos)->get()
            : collect();

        $siRefs = array_values(array_unique(array_merge(
            $sis->pluck('SI_No')->filter()->all(),
            $sis->pluck('Final_SI_No')->filter()->all()
        )));

        $pls = !empty($siRefs)
            ? PackingList::where(function ($q) use ($siRefs) {
                $q->whereIn('SI_No', $siRefs)->orWhereIn('Final_SI_No', $siRefs);
            })->get(['Packing_List_No', 'SI_No', 'Final_SI_No'])
            : collect();

        $result = [];
        foreach ($schedules as $vs) {
            $booking = $bookings->get($vs->Booking_No) ?: $bookings->get($vs->Order_No);
            $contract = $booking ? $booking->contract : null;

            $matchingSi = $sis->firstWhere('Order_No', $vs->Order_No);
            $plNo = '-';
            if ($matchingSi) {
                $pl = $pls->first(function ($item) use ($matchingSi) {
                    return ($matchingSi->SI_No && $item->SI_No === $matchingSi->SI_No)
                        || ($matchingSi->Final_SI_No && $item->Final_SI_No === $matchingSi->Final_SI_No);
                });
                if ($pl && $pl->Packing_List_No) {
                    $plNo = $pl->Packing_List_No;
                }
            }

            $buyerName = '-';
            if ($contract) {
                $buyerName = $contract->Buyer_Name ?? $contract->Customer_Name ?? '-';
            }

            $result[$vs->id] = [
                'contract_no' => $contract && $contract->Contract_No ? $contract->Contract_No : '-',
                'packing_list_no' => $plNo,
                'buyer_name' => $buyerName,
            ];
        }

        return $result;
    }
}
