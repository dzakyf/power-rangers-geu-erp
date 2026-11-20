<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\EmployeeThrSchemeService;
use App\Services\Payroll\PayrollThrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollThrController extends Controller
{
    /**
     * @var PayrollThrService
     */
    protected $thrService;

    public function __construct(PayrollThrService $thrService)
    {
        $this->thrService = $thrService;
    }

    /**
     * Halaman utama Perhitungan & Manajemen THR.
     */
    public function index()
    {
        $mainGroups = DB::table('payrol_main_group')->orderBy('ID', 'ASC')->get();
        $batches = DB::table('payroll_thr_batches')
            ->orderBy('id', 'DESC')
            ->limit(30)
            ->get();

        return view('humanresources.payroll.payroll-thr', [
            'mainGroups'    => $mainGroups,
            'thrSchemes'    => EmployeeThrSchemeService::schemeOptions(),
            'batches'       => $batches,
            'defaultPeriod' => date('Y-m'),
        ]);
    }

    /**
     * JSON daftar batch THR untuk refresh dropdown/tabel.
     */
    public function listBatches()
    {
        $batches = DB::table('payroll_thr_batches')
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $batches,
        ]);
    }

    /**
     * XML dhtmlXGrid daftar item karyawan pada suatu batch THR.
     */
    public function batchItemsXml(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);

        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rows>';

        if ($batchId > 0) {
            $items = DB::table('payroll_thr_items')
                ->where('batch_id', $batchId)
                ->orderBy('is_eligible', 'DESC')
                ->orderBy('employee_name', 'ASC')
                ->get();

            foreach ($items as $it) {
                $eligible = (int)$it->is_eligible;
                $tenureLabel = $it->tenure_months . ' bln (' . $it->tenure_days . ' hr)';
                $schemeValue = !empty($it->thr_scheme) ? $it->thr_scheme : 'DEFAULT_PERMENAKER';
                $proratePct = round(((float)$it->prorate_ratio) * 100, 2) . '%';
                $noteDisplay = $it->note ?: ($it->ineligible_reason ?: ($it->is_payroll_processed ? 'Sudah masuk Payroll: ' . $it->salary_id : 'Eligible'));

                echo "<row id='{$it->id}'>";
                echo "<cell>{$eligible}</cell>";
                echo "<cell><![CDATA[{$it->nik}]]></cell>";
                echo "<cell><![CDATA[{$it->employee_name}]]></cell>";
                echo "<cell><![CDATA[" . ($it->department ?: '-') . "]]></cell>";
                echo "<cell><![CDATA[" . ($it->religion ?: '-') . "]]></cell>";
                echo "<cell><![CDATA[" . ($it->date_in ?: '-') . "]]></cell>";
                echo "<cell><![CDATA[{$tenureLabel}]]></cell>";
                echo "<cell><![CDATA[{$schemeValue}]]></cell>";
                echo "<cell><![CDATA[{$proratePct}]]></cell>";
                echo "<cell>{$it->base_salary_amount}</cell>";
                echo "<cell>{$it->thr_calculated_amount}</cell>";
                echo "<cell>{$it->thr_adjustment_amount}</cell>";
                echo "<cell>{$it->thr_final_amount}</cell>";
                echo "<cell><![CDATA[{$noteDisplay}]]></cell>";
                echo "</row>";
            }
        }

        echo '</rows>';
        exit;
    }

    /**
     * Generate draft batch perhitungan THR.
     */
    public function generateDraft(Request $request)
    {
        $schemeKeys = implode(',', array_keys(EmployeeThrSchemeService::schemeOptions()));
        $request->validate([
            'salary_period'       => 'required|regex:/^\d{4}-\d{2}$/',
            'thr_event_name'      => 'required|string|max:150',
            'reference_date'      => 'required|date',
            'prorate_method'      => 'required|in:FOLLOW_EMPLOYEE,DEFAULT_PERMENAKER,MONTH_FLOOR,DAY_365,' . $schemeKeys,
            'fallback_thr_scheme' => 'nullable|in:DEFAULT_PERMENAKER,' . $schemeKeys,
        ]);

        $operator = Auth::user() ? Auth::user()->username : 'SYSTEM';

        try {
            $result = $this->thrService->generateBatchDraft($request->all(), $operator);

            return response()->json([
                'success' => true,
                'message' => "Draft THR {$result['batch_no']} berhasil digenerate ({$result['total_employees']} karyawan eligible).",
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate draft THR: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update eligibilitas, adjustment, atau catatan item THR pada batch DRAFT.
     */
    public function updateItem(Request $request)
    {
        $itemId = (int)$request->input('item_id', 0);
        if ($itemId <= 0) {
            return response()->json(['success' => false, 'message' => 'Item ID tidak valid.'], 400);
        }

        try {
            $res = $this->thrService->updateItem($itemId, $request->only([
                'is_eligible',
                'thr_scheme',
                'thr_adjustment_amount',
                'note',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Item THR berhasil diperbarui.',
                'data'    => $res,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Lock batch THR agar otomatis masuk ke perhitungan Payroll & Payslip bulan berjalan.
     */
    public function lockBatch(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);
        $operator = Auth::user() ? Auth::user()->username : 'SYSTEM';

        try {
            $this->thrService->lockBatch($batchId, $operator);

            return response()->json([
                'success' => true,
                'message' => 'Batch THR berhasil di-LOCK dan siap digabungkan ke Payroll Generator bulan berjalan.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unlock batch THR kembali ke DRAFT.
     */
    public function unlockBatch(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);

        try {
            $this->thrService->unlockBatch($batchId);

            return response()->json([
                'success' => true,
                'message' => 'Batch THR berhasil dikembalikan ke status DRAFT.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Hapus batch THR berstatus DRAFT.
     */
    public function deleteDraft(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);

        try {
            $this->thrService->deleteDraftBatch($batchId);

            return response()->json([
                'success' => true,
                'message' => 'Draft batch THR berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
