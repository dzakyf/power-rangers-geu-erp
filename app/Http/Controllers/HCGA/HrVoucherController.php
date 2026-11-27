<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceCoaService;
use App\Services\Finance\FinanceHrVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrVoucherController extends Controller
{
    /** @var FinanceHrVoucherService */
    protected $voucherService;

    /** @var FinanceCoaService */
    protected $coaService;

    public function __construct(FinanceHrVoucherService $voucherService, FinanceCoaService $coaService = null)
    {
        $this->voucherService = $voucherService;
        $this->coaService = $coaService ?: app(FinanceCoaService::class);
    }

    public function index()
    {
        if (!$this->canAccess('view')) {
            abort(403, 'Anda tidak memiliki akses ke menu Pengajuan Voucher & AP.');
        }

        return view('humanresources.voucher.index', [
            'canCreate' => $this->canAccess('create'),
            'canEdit'   => $this->canAccess('edit'),
        ]);
    }

    public function data(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $rows = $this->voucherService->listManualVouchers([
            'status' => $request->input('status', 'ALL'),
        ]);

        $summary = [
            'total'            => count($rows),
            'waiting_approval' => count(array_filter($rows, function ($r) { return $r->Status === 'WAITING_APPROVAL'; })),
            'submitted'        => count(array_filter($rows, function ($r) { return $r->Status === 'SUBMITTED'; })),
            'returned'         => count(array_filter($rows, function ($r) { return ($r->Effective_Status ?? $r->Status) === 'RETURNED'; })),
            'ap_active'        => count(array_filter($rows, function ($r) { return in_array(($r->Effective_Status ?? $r->Status), ['PROPOSED', 'NEED_APPROVE', 'RECEIVED_FINANCE', 'RECEIVED_APPROVAL1', 'RECEIVED', 'PARTIAL'], true); })),
            'completed'        => count(array_filter($rows, function ($r) { return in_array(($r->Effective_Status ?? $r->Status), ['PAID', 'CLOSED'], true); })),
            'total_amount'     => array_sum(array_map(function ($r) { return in_array(($r->Effective_Status ?? $r->Status), ['CANCELLED', 'CANCELED'], true) ? 0 : (float) $r->Total; }, $rows)),
            'remain_amount'    => array_sum(array_map(function ($r) { return in_array(($r->Effective_Status ?? $r->Status), ['PROPOSED', 'NEED_APPROVE', 'RECEIVED_FINANCE', 'RECEIVED_APPROVAL1', 'RECEIVED', 'PARTIAL'], true) ? (float) $r->Remain_Val : 0; }, $rows)),
        ];

        return response()->json([
            'status'  => 'success',
            'data'    => array_values($rows),
            'summary' => $summary,
        ]);
    }

    public function detail(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        try {
            $id = (int) $request->input('id', 0);
            $payload = $this->voucherService->getVoucherReviewPayload($id);
            return response()->json(['status' => 'success', 'data' => $payload]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function searchPurchaseOrders(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $rows = $this->voucherService->searchPurchaseOrders(
            (string) $request->input('q', ''),
            (int) $request->input('limit', 30)
        );

        return response()->json(['status' => 'success', 'data' => $rows]);
    }

    public function searchSuppliers(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $rows = $this->voucherService->searchSuppliers(
            (string) $request->input('q', ''),
            (int) $request->input('limit', 30)
        );

        return response()->json(['status' => 'success', 'data' => $rows]);
    }

    public function coaCatalog()
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->coaService->getNonAccountingCoaCatalog(),
        ]);
    }

    public function suggestLines(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $lines = $this->coaService->buildSuggestedLines(
            'HRGA_MANUAL',
            $request->input('amounts', []),
            (string) $request->input('channel', 'AP'),
            (string) $request->input('payment_method', 'BANK'),
            null,
            $request->input('department')
        );

        return response()->json(['status' => 'success', 'data' => $lines]);
    }

    public function printVoucher(Request $request)
    {
        if (!$this->canAccess('view')) {
            abort(403, 'Akses ditolak.');
        }

        $id = (int) $request->input('id', 0);
        $payload = $this->voucherService->getPrintPayload($id);
        return view('finance.humanresources.voucher-print', $payload);
    }

    public function store(Request $request)
    {
        if (!$this->canAccess('create')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin membuat pengajuan voucher.'], 403);
        }

        try {
            $payload = $request->all();
            $voucher = $this->voucherService->createManualVoucher(
                $payload,
                Auth::user(),
                $request->file('attachment')
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan Voucher ' . $voucher->Voucher_No . ' (' . $voucher->Requested_Channel . ') berhasil dikirim ke Finance.',
                'data'    => $voucher,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function resubmit(Request $request)
    {
        if (!$this->canAccess('edit')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin revisi voucher.'], 403);
        }

        try {
            $id = (int) $request->input('id', 0);
            $voucher = $this->voucherService->resubmitReturnedVoucher(
                $id,
                $request->all(),
                Auth::user(),
                $request->file('attachment')
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Revisi Voucher ' . $voucher->Voucher_No . ' berhasil dikirim ulang ke Finance.',
                'data'    => $voucher,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request)
    {
        if (!$this->canAccess('delete')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin membatalkan voucher.'], 403);
        }

        try {
            $id = (int) $request->input('id', 0);
            $voucher = $this->voucherService->cancelVoucher($id, Auth::user());

            return response()->json([
                'status'  => 'success',
                'message' => 'Voucher ' . $voucher->Voucher_No . ' berhasil dibatalkan.',
                'data'    => $voucher,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    protected function canAccess(string $action): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if (strtoupper((string) ($user->Priv_ID ?? '')) === 'ADMIN' || strtolower((string) ($user->username ?? '')) === 'admin') {
            return true;
        }
        if (function_exists('can_menu') && (can_menu('SI01161', $action) || can_menu('SI01014', $action) || can_menu('SI01013', $action))) {
            return true;
        }

        return in_array(strtoupper((string) ($user->Department ?? '')), ['HCGA', 'HRD', 'GA', 'CORSECGA', 'FIN', 'FINANCE', 'FAT', 'FA'], true);
    }
}
