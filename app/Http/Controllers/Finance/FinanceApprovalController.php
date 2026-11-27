<?php

namespace App\Http\Controllers\Finance;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceApprovalController extends Controller
{
    /** @var FinanceApprovalService */
    protected $financeApprovalService;

    public function __construct(FinanceApprovalService $financeApprovalService)
    {
        $this->financeApprovalService = $financeApprovalService;
    }

    public function index()
    {
        if (!$this->canAccess('view')) {
            abort(403, 'Anda tidak memiliki akses ke menu Finance Approval.');
        }

        return view('finance.humanresources.approval');
    }

    public function data()
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $userId = (int) Auth::id();
        $tasks = $this->financeApprovalService->getPendingWorkflowTasks($userId);
        $vouchers = $this->financeApprovalService->getPendingVouchers();

        $totalVoucherAmount = collect($vouchers['maintenance'])->sum('Estimasi_Biaya')
            + collect($vouchers['vehicle_tax'])->sum('Estimasi_Biaya')
            + collect($vouchers['ga_vouchers'])->sum('Amount')
            + collect($vouchers['manual_vouchers'] ?? [])->sum('Total');

        return response()->json([
            'status'                  => 'success',
            'tasks'                   => $tasks,
            'maintenance'             => $vouchers['maintenance'],
            'vehicle_tax'             => $vouchers['vehicle_tax'],
            'ga_vouchers'             => $vouchers['ga_vouchers'],
            'manual_vouchers'         => $vouchers['manual_vouchers'] ?? [],
            'ap_vouchers'             => $vouchers['ap_vouchers'] ?? [],
            'history_maintenance'     => $vouchers['history_maintenance'],
            'history_tax'             => $vouchers['history_tax'],
            'history_ga_vouchers'     => $vouchers['history_ga_vouchers'],
            'history_manual_vouchers' => $vouchers['history_manual_vouchers'] ?? [],
            'history_workflows'       => $vouchers['history_workflows'] ?? [],
            'summary'                 => [
                'inbox_count'          => count($tasks),
                'maintenance_count'    => count($vouchers['maintenance']),
                'vehicle_tax_count'    => count($vouchers['vehicle_tax']),
                'ga_voucher_count'     => count($vouchers['ga_vouchers']),
                'manual_voucher_count' => count($vouchers['manual_vouchers'] ?? []),
                'ap_voucher_count'     => count(array_filter($vouchers['ap_vouchers'] ?? [], function ($r) {
                    $st = strtoupper((string) ($r->AP_Status ?? $r->Status));
                    return !in_array($st, ['CLOSED', 'PAID'], true);
                })),
                'total_pending'        => count($tasks) + count($vouchers['maintenance']) + count($vouchers['vehicle_tax']) + count($vouchers['ga_vouchers']) + count($vouchers['manual_vouchers'] ?? []),
                'total_voucher_amount' => (float) $totalVoucherAmount,
            ],
        ]);
    }

    public function workflowDetail(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $detail = $this->financeApprovalService->getWorkflowTaskDetail((int) $request->input('task_id'));
        if (!$detail) {
            return response()->json(['status' => 'error', 'message' => 'Data task tidak ditemukan.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $detail]);
    }

    public function detail(Request $request)
    {
        if (!$this->canAccess('view')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $type = strtolower(trim((string) $request->input('type', 'workflow')));
        $id = (int) $request->input('id', 0);

        if ($type === 'maintenance') {
            $item = $this->financeApprovalService->getMaintenanceVoucherDetail($id);
            return view('finance.humanresources.detail', ['type' => 'maintenance', 'item' => $item]);
        }

        if ($type === 'tax') {
            $item = $this->financeApprovalService->getTaxVoucherDetail($id);
            return view('finance.humanresources.detail', ['type' => 'tax', 'item' => $item]);
        }

        if ($type === 'ga_voucher') {
            $item = $this->financeApprovalService->getGaVoucherDetail($id);
            return view('finance.humanresources.detail', ['type' => 'ga_voucher', 'item' => $item]);
        }

        $detail = $this->financeApprovalService->getWorkflowTaskDetail($id);
        return view('finance.humanresources.detail', [
            'type'             => 'workflow',
            'item'             => $detail ? $detail['task'] : null,
            'logs'             => $detail ? $detail['logs'] : [],
            'context'          => $detail ? $detail['context'] : [],
            'policy'           => $detail ? $detail['policy'] : [],
            'payrollBreakdown' => $detail ? ($detail['payroll_breakdown'] ?? null) : null,
        ]);
    }

    public function actWorkflow(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin approve.'], 403);
        }

        try {
            $this->financeApprovalService->actWorkflowTask(
                (int) $request->input('task_id'),
                (int) Auth::id(),
                (string) $request->input('action', 'APPROVE'),
                (string) $request->input('notes', '')
            );
            return response()->json(['status' => 'success', 'message' => 'Approval workflow berhasil diproses.']);
        } catch (ApprovalWorkflowException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getStatusCode() ?: 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function confirmMaintenanceVoucher(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin verifikasi voucher.'], 403);
        }

        try {
            $row = $this->financeApprovalService->approveMaintenanceVoucher((int) $request->input('id'));
            return response()->json([
                'status'  => 'success',
                'message' => 'Voucher ' . ($row->Voucher_No ?? '') . ' disetujui. Jadwal perawatan aktif.',
                'data'    => $row,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function payVehicleTaxVoucher(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin pembayaran voucher.'], 403);
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $row = $this->financeApprovalService->payVehicleTaxVoucher(
                (int) $request->input('id'),
                (string) $request->input('paid_date', date('Y-m-d')),
                (string) $request->input('new_due_date', date('Y-m-d', strtotime('+1 year'))),
                $username,
                $request->input('bukti_bayar'),
                $request->file('bukti_file')
            );
            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran voucher pajak ' . ($row->Voucher_No ?? '') . ' berhasil dicatat.',
                'data'    => $row,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function payGaVoucher(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin pembayaran voucher GA.'], 403);
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $row = $this->financeApprovalService->payGaVoucher(
                (int) $request->input('id'),
                [
                    'Paid_Date'   => (string) $request->input('paid_date', date('Y-m-d')),
                    'Payment_Ref' => $request->input('payment_ref'),
                ],
                $username,
                $request->file('proof_file')
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran voucher GA ' . ($row->Voucher_No ?? '') . ' berhasil dicatat.',
                'data'    => $row,
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
        if (function_exists('can_menu') && can_menu('SI01160', $action)) {
            return true;
        }

        return in_array(strtoupper((string) ($user->Department ?? '')), ['FIN', 'FINANCE', 'FAT', 'FA'], true);
    }
}
