<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceCoaService;
use App\Services\Finance\FinanceHrVoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceHrVoucherController extends Controller
{
    /** @var FinanceCoaService */
    protected $coaService;

    /** @var FinanceHrVoucherService */
    protected $voucherService;

    public function __construct(FinanceCoaService $coaService, FinanceHrVoucherService $voucherService)
    {
        $this->coaService = $coaService;
        $this->voucherService = $voucherService;
    }

    public function coaGroups(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $groups = $this->coaService->getTrialBalanceGroups($request->input('category'));
        return response()->json(['status' => 'success', 'data' => $groups]);
    }

    public function coaOptions(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $options = $this->coaService->getCoaOptions(
            $request->input('header_no'),
            $request->input('q'),
            (int) $request->input('limit', 1500)
        );
        return response()->json(['status' => 'success', 'data' => $options]);
    }

    public function bankAccounts()
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->coaService->getBankAccountOptions(),
        ]);
    }

    public function coaMappingIndex()
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'status'         => 'success',
            'mappings'       => $this->coaService->getAllDefaultMappings(),
            'source_modules' => FinanceCoaService::SOURCE_MODULES,
            'component_keys' => FinanceCoaService::COMPONENT_KEYS,
            'tb_groups'      => $this->coaService->getTrialBalanceGroups(),
        ]);
    }

    public function coaMappingSave(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        try {
            $username = (string) (Auth::user()->username ?? 'system');
            $row = $this->coaService->saveDefaultMapping($request->all(), $username);
            return response()->json([
                'status'  => 'success',
                'message' => 'Mapping COA default berhasil disimpan.',
                'data'    => $row,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function coaMappingDelete(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $this->coaService->deleteDefaultMapping((int) $request->input('id', 0));
        return response()->json(['status' => 'success', 'message' => 'Mapping COA dihapus.']);
    }

    public function printVoucher(Request $request)
    {
        if (!$this->canAccess('view')) {
            abort(403, 'Akses ditolak.');
        }

        $voucherId = (int) $request->input('voucher_id', 0);
        if ($voucherId <= 0) {
            $sourceModule = (string) $request->input('source_module', '');
            $sourceId = (int) $request->input('source_id', 0);
            if ($sourceModule === '' || $sourceId <= 0) {
                abort(404, 'Voucher tidak ditemukan.');
            }
            $voucher = $this->voucherService->getOrHydrateFromSource(
                $sourceModule,
                $sourceId,
                (string) (Auth::user()->username ?? 'system')
            );
            $voucherId = (int) $voucher->ID;
        }

        $payload = $this->voucherService->getPrintPayload($voucherId);
        return view('finance.humanresources.voucher-print', $payload);
    }

    public function review(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        try {
            $voucherId = (int) $request->input('voucher_id', 0);
            if ($voucherId <= 0) {
                $sourceModule = (string) $request->input('source_module', '');
                $sourceId = (int) $request->input('source_id', 0);
                if ($sourceModule === '' || $sourceId <= 0) {
                    throw new \InvalidArgumentException('Parameter voucher_id atau (source_module, source_id) wajib diisi.');
                }
                $voucher = $this->voucherService->getOrHydrateFromSource(
                    $sourceModule,
                    $sourceId,
                    (string) (Auth::user()->username ?? 'system')
                );
                $voucherId = (int) $voucher->ID;
            }

            $payload = $this->voucherService->getVoucherReviewPayload($voucherId);
            $payload['tb_groups'] = $this->coaService->getTrialBalanceGroups();
            $payload['bank_accounts'] = $this->coaService->getBankAccountOptions();

            return response()->json(['status' => 'success', 'data' => $payload]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function suggestLines(Request $request)
    {
        if (!$this->canAccess('view')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        $lines = $this->coaService->buildSuggestedLines(
            (string) $request->input('source_module', 'HRGA_MANUAL'),
            $request->input('amounts', []),
            (string) $request->input('channel', 'DIRECT'),
            (string) $request->input('payment_method', 'BANK'),
            $request->input('credit_acc_no'),
            $request->input('department')
        );

        return response()->json(['status' => 'success', 'data' => $lines]);
    }

    public function process(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin verifikasi & terbitkan voucher.'], 403);
        }

        try {
            $voucherId = (int) $request->input('voucher_id', 0);
            $payload = $request->all();
            if (is_string($payload['Lines'] ?? null)) {
                $payload['Lines'] = json_decode($payload['Lines'], true) ?: [];
            }
            if (is_string($payload['Tenors'] ?? null)) {
                $payload['Tenors'] = json_decode($payload['Tenors'], true) ?: [];
            }

            $voucher = $this->voucherService->verifyAndProcessVoucher(
                $voucherId,
                $payload,
                Auth::user(),
                $request->file('proof_file')
            );

            $msg = $voucher->Final_Channel === 'AP'
                ? 'Voucher ' . $voucher->Voucher_No . ' diterbitkan sebagai Hutang Dagang (AP: ' . $voucher->Legacy_AP_Task_ID . ') & jurnal ' . $voucher->Journal_No . ' tercatat ke HO.'
                : 'Voucher ' . $voucher->Voucher_No . ' dibayar langsung (' . $voucher->Payment_Method . ') & jurnal ' . $voucher->Journal_No . ' tercatat ke HO.';

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'data'    => $voucher,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin menolak voucher.'], 403);
        }

        try {
            $voucherId = (int) $request->input('voucher_id', 0);
            if ($voucherId <= 0) {
                $sourceModule = (string) $request->input('source_module', '');
                $sourceId = (int) $request->input('source_id', 0);
                if ($sourceModule !== '' && $sourceId > 0) {
                    $voucher = $this->voucherService->getOrHydrateFromSource(
                        $sourceModule,
                        $sourceId,
                        (string) (Auth::user()->username ?? 'system')
                    );
                    $voucherId = (int) $voucher->ID;
                }
            }

            $voucher = $this->voucherService->rejectVoucher(
                $voucherId,
                (string) $request->input('reason', ''),
                Auth::user()
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Voucher ' . $voucher->Voucher_No . ' ditolak dan dikembalikan (RETURNED) ke modul HR/GA asal.',
                'data'    => $voucher,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function advanceApRequest(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
        }

        try {
            $voucher = $this->voucherService->advanceApRequestStage(
                (int) $request->input('voucher_id', 0),
                Auth::user()
            );
            return response()->json([
                'status'  => 'success',
                'message' => 'AP ' . $voucher->Voucher_No . ' diteruskan ke tahap Payment Request (RECEIVED_APPROVAL1).',
                'data'    => $voucher,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function payAp(Request $request)
    {
        if (!$this->canAccess('approve')) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki izin pembayaran AP.'], 403);
        }

        try {
            $voucher = $this->voucherService->recordFlexiblePayment(
                (int) $request->input('voucher_id', 0),
                $request->all(),
                Auth::user(),
                $request->file('proof_file')
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran AP ' . $voucher->Voucher_No . ' berhasil dicatat. Sisa tagihan: Rp ' . number_format((float) $voucher->Remain_Val, 0, ',', '.') . '.',
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
        if (function_exists('can_menu') && can_menu('SI01160', $action)) {
            return true;
        }

        return in_array(strtoupper((string) ($user->Department ?? '')), ['FIN', 'FINANCE', 'FAT', 'FA'], true);
    }
}
