<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\EmailSettingsService;
use App\Services\HCGA\EmployeeThrSchemeService;
use App\Services\Payroll\PayrollThrApprovalService;
use App\Services\Payroll\PayrollThrPayslipService;
use App\Services\Payroll\PayrollThrService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PayrollThrController extends Controller
{
    /**
     * @var PayrollThrService
     */
    protected $thrService;

    /**
     * @var PayrollThrApprovalService
     */
    protected $thrApprovalService;

    /**
     * @var PayrollThrPayslipService
     */
    protected $thrPayslipService;

    /**
     * @var EmailSettingsService
     */
    protected $emailSettingsService;

    public function __construct(
        PayrollThrService $thrService,
        PayrollThrApprovalService $thrApprovalService = null,
        PayrollThrPayslipService $thrPayslipService = null,
        EmailSettingsService $emailSettingsService = null
    ) {
        $this->thrService = $thrService;
        $this->thrApprovalService = $thrApprovalService ?: app(PayrollThrApprovalService::class);
        $this->thrPayslipService = $thrPayslipService ?: app(PayrollThrPayslipService::class);
        $this->emailSettingsService = $emailSettingsService ?: app(EmailSettingsService::class);
    }

    /**
     * Halaman utama Perhitungan & Manajemen THR Mandiri.
     */
    public function index()
    {
        $mainGroups = DB::table('payrol_main_group')->orderBy('ID', 'ASC')->get();
        $batches = DB::table('payroll_thr_batches')
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $pendingApprovalCount = DB::table('payroll_thr_batches')
            ->whereNotIn('status', ['DRAFT', 'APPROVED', 'REJECTED', 'CANCELLED', 'CANCELED', 'LOCKED', 'POSTED'])
            ->count();

        return view('humanresources.payroll.payroll-thr', [
            'mainGroups'           => $mainGroups,
            'thrSchemes'           => EmployeeThrSchemeService::schemeOptions(),
            'batches'              => $batches,
            'defaultPeriod'        => date('Y-m'),
            'pendingApprovalCount' => $pendingApprovalCount,
        ]);
    }

    /**
     * JSON daftar batch THR untuk refresh dropdown/tabel beserta ringkasan status approval.
     */
    public function listBatches()
    {
        $batches = DB::table('payroll_thr_batches')
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get()
            ->map(function ($b) {
                $b->tax_mode = strtoupper((string)($b->tax_mode ?? 'NONE'));
                $b->total_pph21 = isset($b->total_pph21) ? (float)$b->total_pph21 : 0.0;
                $b->total_tax_allowance = isset($b->total_tax_allowance) ? (float)$b->total_tax_allowance : 0.0;
                $b->total_net_amount = isset($b->total_net_amount) && (float)$b->total_net_amount > 0
                    ? (float)$b->total_net_amount
                    : (float)$b->total_amount;
                return $b;
            });

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

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<rows>';

        if ($batchId > 0) {
            $items = DB::table('payroll_thr_items')
                ->where('batch_id', $batchId)
                ->orderBy('is_eligible', 'DESC')
                ->orderBy('employee_name', 'ASC')
                ->get();

            $nikList = $items->pluck('nik')->filter()->unique()->values()->all();
            $empEmailMap = !empty($nikList)
                ? DB::table('employment')->whereIn('NIK', $nikList)->pluck('Email', 'NIK')->all()
                : [];
            $userEmailMap = !empty($nikList)
                ? DB::table('users')->whereIn('Emp_ID', $nikList)->pluck('Email', 'Emp_ID')->all()
                : [];

            foreach ($items as $it) {
                $eligible = (int)$it->is_eligible;
                $tenureLabel = $it->tenure_months . ' bln (' . $it->tenure_days . ' hr)';
                $schemeValue = !empty($it->thr_scheme) ? $it->thr_scheme : 'DEFAULT_PERMENAKER';
                $proratePct = round(((float)$it->prorate_ratio) * 100, 2) . '%';
                $ptkp = !empty($it->ptkp_status) ? $it->ptkp_status : 'TK/0';
                $pph21 = isset($it->thr_pph21_amount) ? (float)$it->thr_pph21_amount : 0.0;
                $netThr = isset($it->thr_net_amount) && (float)$it->thr_net_amount > 0
                    ? (float)$it->thr_net_amount
                    : (((int)$it->is_eligible === 1) ? max(0.0, (float)$it->thr_final_amount - $pph21) : 0.0);
                $noteDisplay = $it->note ?: ($it->ineligible_reason ?: 'Eligible');
                $empEmail = trim((string)($empEmailMap[$it->nik] ?? ($userEmailMap[$it->nik] ?? '')));

                $xml .= "<row id='{$it->id}'>";
                $xml .= '<userdata name="nik"><![CDATA[' . $it->nik . ']]></userdata>';
                $xml .= '<userdata name="email"><![CDATA[' . $empEmail . ']]></userdata>';
                $xml .= "<cell>{$eligible}</cell>";
                $xml .= "<cell><![CDATA[{$it->nik}]]></cell>";
                $xml .= "<cell><![CDATA[{$it->employee_name}]]></cell>";
                $xml .= "<cell><![CDATA[" . ($it->department ?: '-') . "]]></cell>";
                $xml .= "<cell><![CDATA[" . ($it->religion ?: '-') . "]]></cell>";
                $xml .= "<cell><![CDATA[" . ($it->date_in ?: '-') . "]]></cell>";
                $xml .= "<cell><![CDATA[{$tenureLabel}]]></cell>";
                $xml .= "<cell><![CDATA[{$schemeValue}]]></cell>";
                $xml .= "<cell><![CDATA[{$proratePct}]]></cell>";
                $xml .= "<cell>{$it->base_salary_amount}</cell>";
                $xml .= "<cell>{$it->thr_calculated_amount}</cell>";
                $xml .= "<cell>{$it->thr_adjustment_amount}</cell>";
                $xml .= "<cell>{$it->thr_final_amount}</cell>";
                $xml .= "<cell><![CDATA[{$ptkp}]]></cell>";
                $xml .= "<cell>{$pph21}</cell>";
                $xml .= "<cell>{$netThr}</cell>";
                $xml .= "<cell><![CDATA[{$noteDisplay}]]></cell>";
                $xml .= '</row>';
            }
        }

        $xml .= '</rows>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
        ]);
    }

    /**
     * Generate draft batch perhitungan THR mandiri.
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
            'tax_mode'            => 'nullable|in:NONE,TER_STANDALONE',
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
     * Update eligibilitas, skema, adjustment, atau catatan item THR pada batch DRAFT.
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
     * Toggle mode pemotongan PPh 21 pada batch THR berstatus DRAFT (NONE <-> TER_STANDALONE).
     */
    public function updateTaxMode(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|integer|min:1',
            'tax_mode' => 'required|in:NONE,TER_STANDALONE',
        ]);

        try {
            $res = $this->thrService->updateBatchTaxMode(
                (int)$request->input('batch_id'),
                (string)$request->input('tax_mode')
            );

            $label = $res['tax_mode'] === PayrollThrService::TAX_MODE_TER_STANDALONE
                ? 'Potong PPh 21 Otomatis (TER PP 58/2023)'
                : 'Tanpa Potongan PPh 21 (THR Utuh)';

            return response()->json([
                'success' => true,
                'message' => "Mode pajak batch berhasil diubah ke: {$label}.",
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
     * Ajukan batch THR DRAFT ke Dynamic Approval Engine (Request_Type: PAYROLL_THR).
     */
    public function submitApproval(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);
        if ($batchId <= 0) {
            return response()->json(['success' => false, 'message' => 'Batch THR tidak valid.'], 400);
        }

        try {
            $res = $this->thrApprovalService->submitBatchForApproval($batchId, Auth::user());

            return response()->json($res);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Kembalikan batch REJECTED / CANCELLED / LOCKED ke DRAFT untuk direvisi.
     */
    public function revertToDraft(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);

        try {
            $this->thrService->revertToDraft($batchId);

            return response()->json([
                'success' => true,
                'message' => 'Batch THR berhasil dikembalikan ke status DRAFT untuk direvisi.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Legacy compatibility endpoint: THR tidak lagi digabung ke Payroll Generator.
     */
    public function lockBatch(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur Lock & Gabung ke Payroll sudah dinonaktifkan. Gunakan tombol "Ajukan Approval" untuk menerbitkan Payslip THR mandiri.',
        ], 422);
    }

    /**
     * Legacy compatibility alias ke revertToDraft.
     */
    public function unlockBatch(Request $request)
    {
        return $this->revertToDraft($request);
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

    // =========================================================================
    // DIGITAL PAYSLIP THR, PDF, EMAIL SATUAN & BLASTING MASSAL
    // =========================================================================

    /**
     * Tampilan Digital Payslip THR khusus (Web & Print).
     */
    public function showDigitalPayslip(Request $request)
    {
        $batchId = (int)($request->input('batch_id') ?: $request->input('Batch_ID', 0));
        $nik = trim((string)($request->input('nik') ?: $request->input('NIK', '')));

        if ($nik === '' && Auth::user()) {
            $nik = trim((string)(Auth::user()->Emp_ID ?? ''));
        }

        if ($batchId <= 0 || $nik === '') {
            return redirect('payroll-thr')->with('error', 'Parameter batch THR dan NIK karyawan wajib diisi.');
        }

        try {
            $payslip = $this->thrPayslipService->getThrPayslipData($batchId, $nik);
            return view('humanresources.payroll.prints.payslip-thr-digital', [
                'payslip' => $payslip,
            ]);
        } catch (\Throwable $e) {
            return response('Error memuat payslip THR: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Download PDF Digital Payslip THR via Dompdf.
     */
    public function downloadPayslipPdf(Request $request)
    {
        $batchId = (int)($request->input('batch_id') ?: $request->input('Batch_ID', 0));
        $nik = trim((string)($request->input('nik') ?: $request->input('NIK', '')));

        if ($nik === '' && Auth::user()) {
            $nik = trim((string)(Auth::user()->Emp_ID ?? ''));
        }

        if ($batchId <= 0 || $nik === '') {
            abort(400, 'Parameter batch_id dan nik wajib diisi.');
        }

        $payslip = $this->thrPayslipService->getThrPayslipData($batchId, $nik);

        $html = view('humanresources.payroll.prints.payslip-thr-digital', [
            'payslip'   => $payslip,
            'isPdfMode' => true,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeNik = preg_replace('/[^A-Za-z0-9_-]/', '_', $nik);
        $safeBatch = preg_replace('/[^A-Za-z0-9_-]/', '_', $payslip['batch_no'] ?? ('BATCH_' . $batchId));
        $filename = "Payslip_THR_{$safeNik}_{$safeBatch}.pdf";

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Kirim email Digital Payslip THR satuan (Mendukung status DRAFT [Preview] & APPROVED [Final]).
     */
    public function apiSendThrPayslipEmail(Request $request)
    {
        $batchId = (int)($request->input('batch_id') ?: $request->input('Batch_ID', 0));
        $nik = trim((string)($request->input('nik') ?: $request->input('NIK', '')));

        if ($batchId <= 0 || $nik === '') {
            return response()->json(['success' => false, 'message' => 'Parameter batch_id dan nik wajib diisi.'], 400);
        }

        try {
            $payslip = $this->thrPayslipService->getThrPayslipData($batchId, $nik);
            $batchStatus = strtoupper(trim((string)($payslip['batch_status'] ?? '')));

            if (!in_array($batchStatus, ['DRAFT', 'APPROVED'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kirim email Payslip THR hanya diizinkan pada batch berstatus DRAFT (Preview) atau APPROVED (Final).',
                ], 422);
            }

            $isFinal = ($batchStatus === 'APPROVED');
            $logMode = $isFinal ? 'FINAL' : 'PREVIEW';
            $subjectSuffix = $isFinal ? '' : ' [PREVIEW / DRAFT]';

            $recipientEmail = $payslip['email'];
            if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Karyawan {$payslip['name']} ({$nik}) belum memiliki alamat email yang valid.",
                ]);
            }

            $htmlBody = view('humanresources.payroll.prints.payslip-thr-digital', [
                'payslip'     => $payslip,
                'isEmailMode' => true,
            ])->render();

            $subject = "Payslip THR - {$payslip['event_name']} ({$payslip['period']}) - {$payslip['name']}{$subjectSuffix}";
            list($isSent, $errorMsg) = $this->emailSettingsService->send($recipientEmail, $subject, $htmlBody);

            $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';
            $this->logThrEmail($payslip['batch_no'], $payslip['period'], $nik, $recipientEmail, $isSent ? 'SENT' : 'FAILED', $isSent ? null : $errorMsg, $currentUser, $logMode);

            if ($isSent) {
                $modeText = $isFinal ? 'final' : 'preview (draft)';
                return response()->json([
                    'success'   => true,
                    'message'   => "Payslip THR digital ({$modeText}) berhasil dikirimkan ke email karyawan.",
                    'recipient' => $recipientEmail,
                    'mode'      => $logMode,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $errorMsg,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email payslip THR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Blasting Email Massal Payslip THR (Mendukung batch DRAFT [Preview] & APPROVED [Final]).
     */
    public function apiBlastThrPayslipEmails(Request $request)
    {
        $batchId = (int)($request->input('batch_id') ?: $request->input('Batch_ID', 0));
        if ($batchId <= 0) {
            return response()->json(['success' => false, 'message' => 'Parameter batch_id wajib diisi.'], 400);
        }

        $batch = DB::table('payroll_thr_batches')->where('id', $batchId)->first();
        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch THR tidak ditemukan.'], 404);
        }

        $statusUpper = strtoupper(trim((string)$batch->status));
        if (!in_array($statusUpper, ['DRAFT', 'APPROVED'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Blasting email Payslip THR hanya diizinkan pada batch berstatus DRAFT (Preview) atau APPROVED (Final).',
            ], 422);
        }

        $isFinal = ($statusUpper === 'APPROVED');
        $logMode = $isFinal ? 'FINAL' : 'PREVIEW';
        $subjectSuffix = $isFinal ? '' : ' [PREVIEW / DRAFT]';

        $niksInput = $request->input('niks');
        $niks = [];
        if (is_array($niksInput)) {
            $niks = array_values(array_filter(array_map('trim', $niksInput)));
        } elseif (is_string($niksInput) && trim($niksInput) !== '') {
            $niks = array_values(array_filter(array_map('trim', explode(',', $niksInput))));
        }

        if (empty($niks)) {
            $niks = DB::table('payroll_thr_items')
                ->where('batch_id', $batchId)
                ->where('is_eligible', 1)
                ->where('thr_final_amount', '>', 0)
                ->orderBy('employee_name', 'ASC')
                ->pluck('nik')
                ->all();
        }

        if (empty($niks)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada karyawan eligible untuk blasting email Payslip THR.',
            ], 400);
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';
        $results = [];
        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($niks as $nik) {
            $nik = trim((string)$nik);
            if ($nik === '') {
                continue;
            }

            try {
                $payslip = $this->thrPayslipService->getThrPayslipData($batchId, $nik);
                $recipientEmail = $payslip['email'];
                $empName = $payslip['name'] ?? $nik;

                if ((int)$payslip['is_eligible'] !== 1 || (float)$payslip['take_home_pay'] <= 0) {
                    $skippedCount++;
                    $results[] = [
                        'nik'     => $nik,
                        'name'    => $empName,
                        'email'   => $recipientEmail ?: '-',
                        'status'  => 'SKIPPED',
                        'message' => 'Karyawan tidak eligible atau nominal THR 0.',
                    ];
                    continue;
                }

                if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    $skippedCount++;
                    $results[] = [
                        'nik'     => $nik,
                        'name'    => $empName,
                        'email'   => $recipientEmail ?: '-',
                        'status'  => 'SKIPPED',
                        'message' => 'Alamat email tidak valid atau belum terdaftar.',
                    ];
                    $this->logThrEmail($batch->batch_no, $batch->salary_period, $nik, $recipientEmail ?: '-', 'SKIPPED', 'Email tidak valid atau belum terdaftar.', $currentUser, $logMode);
                    continue;
                }

                $htmlBody = view('humanresources.payroll.prints.payslip-thr-digital', [
                    'payslip'     => $payslip,
                    'isEmailMode' => true,
                ])->render();

                $subject = "Payslip THR - {$payslip['event_name']} ({$payslip['period']}) - {$empName}{$subjectSuffix}";
                list($isSent, $errorMsg) = $this->emailSettingsService->send($recipientEmail, $subject, $htmlBody);

                if ($isSent) {
                    $sentCount++;
                    $results[] = [
                        'nik'     => $nik,
                        'name'    => $empName,
                        'email'   => $recipientEmail,
                        'status'  => 'SENT',
                        'message' => 'Berhasil terkirim.',
                    ];
                } else {
                    $failedCount++;
                    $results[] = [
                        'nik'     => $nik,
                        'name'    => $empName,
                        'email'   => $recipientEmail,
                        'status'  => 'FAILED',
                        'message' => $errorMsg ?: 'Gagal terkirim via SMTP.',
                    ];
                }

                $this->logThrEmail($batch->batch_no, $batch->salary_period, $nik, $recipientEmail, $isSent ? 'SENT' : 'FAILED', $isSent ? null : $errorMsg, $currentUser, $logMode);
            } catch (\Throwable $e) {
                $failedCount++;
                $results[] = [
                    'nik'     => $nik,
                    'name'    => $nik,
                    'email'   => '-',
                    'status'  => 'ERROR',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $modeLabel = $isFinal ? 'Final' : 'Preview (Draft)';

        return response()->json([
            'success' => true,
            'mode'    => $logMode,
            'message' => "Blasting Payslip THR {$modeLabel} selesai: {$sentCount} terkirim, {$failedCount} gagal, {$skippedCount} dilewati.",
            'total'   => count($niks),
            'sent'    => $sentCount,
            'failed'  => $failedCount,
            'skipped' => $skippedCount,
            'results' => $results,
        ]);
    }

    protected function logThrEmail(string $batchNo, string $period, string $nik, string $email, string $status, ?string $errorMsg, string $sentBy, string $mode = 'FINAL'): void
    {
        if (!Schema::hasTable('payroll_payslip_email_logs')) {
            return;
        }

        $normalizedMode = in_array(strtoupper($mode), ['FINAL', 'PREVIEW'], true) ? strtoupper($mode) : 'FINAL';

        $payload = [
            'task_id'         => $batchNo,
            'salary_period'   => $period,
            'nik'             => $nik,
            'recipient_email' => $email,
            'mode'            => $normalizedMode,
            'status'          => $status,
            'error_message'   => $errorMsg,
            'sent_by'         => $sentBy,
            'created_at'      => Carbon::now()->toDateTimeString(),
            'updated_at'      => Carbon::now()->toDateTimeString(),
        ];

        if (Schema::hasColumn('payroll_payslip_email_logs', 'slip_type')) {
            $payload['slip_type'] = 'THR';
        }

        DB::table('payroll_payslip_email_logs')->insert($payload);
    }

    // =========================================================================
    // EXPORT EXCEL REKAP THR & BANK TRANSFER SUMMARY
    // =========================================================================

    public function exportExcel(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);
        if ($batchId <= 0) {
            abort(400, 'Parameter batch_id wajib diisi.');
        }

        $exportData = $this->thrService->getExportRowsForBatch($batchId);
        $batch = $exportData['batch'];
        $rows = $exportData['rows'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap THR & Transfer');

        $sheet->setCellValue('A1', 'REKAP PERHITUNGAN THR & BANK TRANSFER SUMMARY');
        $sheet->setCellValue(
            'A2',
            "Batch: {$batch->batch_no} | Event: {$batch->thr_event_name} | Periode: {$batch->salary_period} | Status: {$batch->status} | Mode Pajak: " . ($batch->tax_mode ?? 'NONE')
        );
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        $headers = [
            'A4'  => 'No',
            'B4'  => 'NIK',
            'C4'  => 'Nama Karyawan',
            'D4'  => 'Jabatan',
            'E4'  => 'Departemen',
            'F4'  => 'Site',
            'G4'  => 'Agama',
            'H4'  => 'Tanggal Join',
            'I4'  => 'Masa Kerja',
            'J4'  => 'Skema THR',
            'K4'  => 'Prorata (%)',
            'L4'  => 'Basis Upah',
            'M4'  => 'THR Sistem',
            'N4'  => 'Adjustment (+/-)',
            'O4'  => 'THR Bruto',
            'P4'  => 'Mode Pajak',
            'Q4'  => 'PTKP',
            'R4'  => 'Gross-Up',
            'S4'  => 'Tunjangan PPh',
            'T4'  => 'PPh 21 THR',
            'U4'  => 'Nett THR (THP)',
            'V4'  => 'Terbilang (Indonesia)',
            'W4'  => 'Terbilang (English)',
            'X4'  => 'BCA NOREK',
            'Y4'  => 'Atas Nama (BCA)',
            'Z4'  => 'SELAIN BCA (BANK)',
            'AA4' => 'SELAIN BCA NOREK',
            'AB4' => 'Atas Nama (Non-BCA)',
            'AC4' => 'Keterangan',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E7E34'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A4:AC4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        $rowNum = 5;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowNum, $r['no']);
            $sheet->setCellValueExplicit('B' . $rowNum, (string)$r['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $r['nama']);
            $sheet->setCellValue('D' . $rowNum, $r['jabatan']);
            $sheet->setCellValue('E' . $rowNum, $r['departemen']);
            $sheet->setCellValue('F' . $rowNum, $r['site']);
            $sheet->setCellValue('G' . $rowNum, $r['agama']);
            $sheet->setCellValue('H' . $rowNum, $r['tanggal_join']);
            $sheet->setCellValue('I' . $rowNum, $r['masa_kerja']);
            $sheet->setCellValue('J' . $rowNum, $r['skema_thr']);
            $sheet->setCellValue('K' . $rowNum, $r['prorate_percent']);
            $sheet->setCellValue('L' . $rowNum, $r['basis_upah']);
            $sheet->setCellValue('M' . $rowNum, $r['thr_sistem']);
            $sheet->setCellValue('N' . $rowNum, $r['adjustment']);
            $sheet->setCellValue('O' . $rowNum, $r['thr_bruto']);
            $sheet->setCellValue('P' . $rowNum, $r['tax_mode']);
            $sheet->setCellValue('Q' . $rowNum, $r['ptkp']);
            $sheet->setCellValue('R' . $rowNum, $r['is_gross_up']);
            $sheet->setCellValue('S' . $rowNum, $r['tunjangan_pph']);
            $sheet->setCellValue('T' . $rowNum, $r['pph21_thr']);
            $sheet->setCellValue('U' . $rowNum, $r['nett_thr']);
            $sheet->setCellValue('V' . $rowNum, $r['terbilang_id']);
            $sheet->setCellValue('W' . $rowNum, $r['terbilang_en']);
            $sheet->setCellValueExplicit('X' . $rowNum, (string)$r['bca_norek'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('Y' . $rowNum, $r['bca_atas_nama']);
            $sheet->setCellValue('Z' . $rowNum, $r['selain_bca_bank']);
            $sheet->setCellValueExplicit('AA' . $rowNum, (string)$r['selain_bca_norek'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('AB' . $rowNum, $r['selain_bca_atas_nama']);
            $sheet->setCellValue('AC' . $rowNum, $r['keterangan']);

            foreach (['L', 'M', 'N', 'O', 'S', 'T', 'U'] as $col) {
                $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            }
            $rowNum++;
        }

        foreach (array_merge(range('A', 'Z'), ['AA', 'AB', 'AC']) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $safeBatchNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $batch->batch_no);
        $filename = "Rekap_THR_Transfer_{$safeBatchNo}_" . date('Ymd_His') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // =========================================================================
    // HALAMAN KHUSUS APPROVAL THR (/payroll-thr-approval)
    // =========================================================================

    public function approvalIndex()
    {
        return view('humanresources.payroll.payroll-thr-approval');
    }

    public function xmlApprovalList()
    {
        $batches = $this->thrApprovalService->getPendingBatchesWithApproval(Auth::user());
        $nomor = 1;

        $xml = "<?xml version='1.0' encoding='utf-8'?>\n<rows>";

        foreach ($batches as $item) {
            $b = $item['batch'];
            $appr = $item['approval'];
            $taxLabel = strtoupper((string)($b->tax_mode ?? 'NONE')) === 'TER_STANDALONE' ? 'TER PPh 21' : 'Tanpa Pajak';
            $pph21 = isset($b->total_pph21) ? (float)$b->total_pph21 : 0.0;
            $net = isset($b->total_net_amount) && (float)$b->total_net_amount > 0
                ? (float)$b->total_net_amount
                : (float)$b->total_amount;

            $xml .= '<row id="' . (int)$b->id . '">';
            $xml .= "<cell>{$nomor}</cell>";
            $xml .= '<cell><![CDATA[' . $b->batch_no . ']]></cell>';
            $xml .= '<cell><![CDATA[' . $b->salary_period . ']]></cell>';
            $xml .= '<cell><![CDATA[' . $b->thr_event_name . ']]></cell>';
            $xml .= '<cell><![CDATA[' . $taxLabel . ']]></cell>';
            $xml .= '<cell>' . (int)$b->total_employees . '</cell>';
            $xml .= '<cell>' . (float)$b->total_amount . '</cell>';
            $xml .= '<cell>' . $pph21 . '</cell>';
            $xml .= '<cell>' . $net . '</cell>';
            $xml .= '<cell><![CDATA[' . ($appr['current_step_label'] ?? '-') . ']]></cell>';
            $xml .= '<cell><![CDATA[' . ($appr['current_approvers'] ?? '-') . ']]></cell>';
            $xml .= '<cell><![CDATA[' . ($b->created_by ?? '-') . ']]></cell>';
            $xml .= '<cell><![CDATA[' . ($appr['display_status'] ?? $b->status) . ']]></cell>';
            $xml .= '</row>';

            $nomor++;
        }

        $xml .= '</rows>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
        ]);
    }

    public function approvalDetail(Request $request)
    {
        $batchId = (int)$request->input('ID', 0);
        $details = $this->thrApprovalService->getBatchApprovalDetails($batchId, Auth::user());

        return view('humanresources.payroll.payroll-thr-approval-detail', [
            'batchId'  => $batchId,
            'batch'    => $details['batch'],
            'approval' => $details['approval'],
            'logs'     => $details['logs'],
        ]);
    }

    public function xmlApprovalDetail(Request $request)
    {
        $batchId = (int)$request->input('batch_id', 0);
        $xml = "<?xml version='1.0' encoding='utf-8'?>\n<rows>";

        if ($batchId > 0) {
            $items = DB::table('payroll_thr_items')
                ->where('batch_id', $batchId)
                ->where('is_eligible', 1)
                ->orderBy('employee_name', 'ASC')
                ->get();

            $no = 1;
            foreach ($items as $it) {
                $tenureLabel = $it->tenure_months . ' bln (' . $it->tenure_days . ' hr)';
                $schemeLabel = EmployeeThrSchemeService::label($it->thr_scheme ?? null);
                $proratePct = round(((float)$it->prorate_ratio) * 100, 2) . '%';
                $pph21 = isset($it->thr_pph21_amount) ? (float)$it->thr_pph21_amount : 0.0;
                $netThr = isset($it->thr_net_amount) && (float)$it->thr_net_amount > 0
                    ? (float)$it->thr_net_amount
                    : max(0.0, (float)$it->thr_final_amount - $pph21);

                $xml .= '<row id="' . htmlspecialchars((string)$it->nik, ENT_QUOTES) . '">';
                $xml .= "<cell>{$no}</cell>";
                $xml .= '<cell><![CDATA[' . $it->nik . ']]></cell>';
                $xml .= '<cell><![CDATA[' . $it->employee_name . ']]></cell>';
                $xml .= '<cell><![CDATA[' . ($it->department ?: '-') . ']]></cell>';
                $xml .= '<cell><![CDATA[' . ($it->religion ?: '-') . ']]></cell>';
                $xml .= '<cell><![CDATA[' . $tenureLabel . ']]></cell>';
                $xml .= '<cell><![CDATA[' . $schemeLabel . ']]></cell>';
                $xml .= '<cell><![CDATA[' . $proratePct . ']]></cell>';
                $xml .= '<cell>' . (float)$it->base_salary_amount . '</cell>';
                $xml .= '<cell>' . (float)$it->thr_calculated_amount . '</cell>';
                $xml .= '<cell>' . (float)$it->thr_adjustment_amount . '</cell>';
                $xml .= '<cell>' . (float)$it->thr_final_amount . '</cell>';
                $xml .= '<cell>' . $pph21 . '</cell>';
                $xml .= '<cell>' . $netThr . '</cell>';
                $xml .= '</row>';
                $no++;
            }
        }

        $xml .= '</rows>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
        ]);
    }

    public function approvalApprove(Request $request)
    {
        $batchId = (int)$request->input('ID', 0);
        $notes = trim((string)$request->input('notes', ''));

        $result = $this->thrApprovalService->approveBatch($batchId, Auth::user(), $notes);
        $safeMsg = addslashes($result['message']);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response(
                '<script>'
                . "alert('{$safeMsg}');"
                . "if (typeof refreshdata === 'function') { refreshdata(); }"
                . "if ($('#divThrApprovalDetail').children().length > 0 && '{$result['status']}' !== 'APPROVED') {"
                . "  LoadM('divThrApprovalDetail', '" . url('payroll-thr-approval-detail') . '?ID=' . $batchId . "');"
                . '} else {'
                . "  $('#divThrApprovalDetail').empty();"
                . '}'
                . '</script>'
            );
        }

        return redirect('payroll-thr-approval')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approvalReject(Request $request)
    {
        $batchId = (int)$request->input('ID', 0);
        $reason = trim((string)$request->input('reason', ''));

        $result = $this->thrApprovalService->rejectOrCancelBatch($batchId, Auth::user(), 'REJECT', $reason);
        $safeMsg = addslashes($result['message']);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response("<script>alert('{$safeMsg}'); $('#divThrApprovalDetail').empty(); if (typeof refreshdata === 'function') { refreshdata(); } else { location.href='" . url('payroll-thr-approval') . "'; }</script>");
        }

        return redirect('payroll-thr-approval')->with($result['success'] ? 'info' : 'error', $result['message']);
    }

    public function approvalCancel(Request $request)
    {
        $batchId = (int)$request->input('ID', 0);
        $reason = trim((string)$request->input('reason', ''));

        $result = $this->thrApprovalService->rejectOrCancelBatch($batchId, Auth::user(), 'CANCEL', $reason);
        $safeMsg = addslashes($result['message']);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response("<script>alert('{$safeMsg}'); $('#divThrApprovalDetail').empty(); if (typeof refreshdata === 'function') { refreshdata(); } else { location.href='" . url('payroll-thr-approval') . "'; }</script>");
        }

        return redirect('payroll-thr-approval')->with($result['success'] ? 'info' : 'error', $result['message']);
    }
}
