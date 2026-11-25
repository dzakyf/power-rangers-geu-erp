<?php

namespace App\Services\Attendance;

use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ManualLeaveEntryService
{
    protected $attendanceService;
    protected $workflowService;
    protected $cutoffService;
    protected $leaveEntitlementService;
    protected $legacySyncService;
    protected $manualCorrectionService;

    public function __construct(
        AttendanceService $attendanceService,
        ApprovalWorkflowService $workflowService,
        AttendanceCutoffService $cutoffService,
        LeaveEntitlementService $leaveEntitlementService,
        LeaveLegacySyncService $legacySyncService,
        ManualAttendanceCorrectionService $manualCorrectionService
    ) {
        $this->attendanceService = $attendanceService;
        $this->workflowService = $workflowService;
        $this->cutoffService = $cutoffService;
        $this->leaveEntitlementService = $leaveEntitlementService;
        $this->legacySyncService = $legacySyncService;
        $this->manualCorrectionService = $manualCorrectionService;
    }

    /**
     * Ambil daftar kategori izin/cuti aktif dari master kategori_izin.
     */
    public function getActiveLeaveCategories(): array
    {
        return DB::table('kategori_izin')
            ->where('status', 'ACTIVE')
            ->orderBy('izin_jenis_id', 'asc')
            ->orderBy('kat_izin_id', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Resolusi kategori aktif berdasarkan kode atau nama (case-insensitive).
     */
    public function resolveCategory(string $leaveTypeOrCode): ?object
    {
        $needle = strtolower(trim($leaveTypeOrCode));
        if ($needle === '') {
            return null;
        }

        $categories = $this->getActiveLeaveCategories();
        foreach ($categories as $cat) {
            $code = strtolower(trim((string) ($cat->kat_izin_code ?? '')));
            $name = strtolower(trim((string) ($cat->kat_izin_nama ?? '')));
            if (($code !== '' && $code === $needle) || ($name !== '' && $name === $needle)) {
                return $cat;
            }
        }

        return null;
    }

    /**
     * Periksa konteks karyawan, saldo, overlap cuti, log presensi, payroll lock, dan self-guard.
     */
    public function inspectContext(string $nik, string $startDate, string $endDate, ?string $leaveType, $actor): array
    {
        $nik = trim($nik);
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate !== '' ? $endDate : $startDate)->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException('Tanggal selesai tidak boleh sebelum tanggal mulai.');
        }

        $startStr = $start->toDateString();
        $endStr = $end->toDateString();
        $totalDays = $end->diffInDays($start) + 1;

        $targetUser = DB::table('users')
            ->where('Emp_ID', $nik)
            ->select('id', 'username', 'Emp_ID', 'Name', 'Department', 'Position', 'Location', 'Company_ID', 'Status')
            ->first();

        if (!$targetUser) {
            throw new InvalidArgumentException("Karyawan dengan NIK {$nik} tidak ditemukan.");
        }

        $category = $leaveType ? $this->resolveCategory($leaveType) : null;
        $isDeductAnnual = $category ? ($category->is_deduct_annual_leave === 'YES') : false;
        $isNeedAttachment = $category ? ($category->is_need_attachment === 'YES') : false;

        // 1. Cek overlap cuti aktif (BLOKIR)
        $overlappingLeave = DB::table('attendance_leave_requests')
            ->where('nik', $nik)
            ->whereIn('status', ['SUBMITTED', 'IN_PROGRESS', 'APPROVED'])
            ->where('start_date', '<=', $endStr)
            ->where('end_date', '>=', $startStr)
            ->orderBy('id', 'desc')
            ->first();

        // 2. Cek log presensi pada rentang tanggal (WARNING non-blocking, misal untuk izin parsial)
        $existingLogs = DB::table('attendant_log')
            ->where('NIK', $nik)
            ->whereBetween('Scan_Date', [$startStr, $endStr])
            ->orderBy('Scan_Date', 'asc')
            ->get();

        $attendanceLogList = [];
        foreach ($existingLogs as $log) {
            if (!empty($log->Start_Time) || !empty($log->End_Time)) {
                $attendanceLogList[] = [
                    'date'      => $log->Scan_Date,
                    'clock_in'  => $log->Start_Time ?: '--:--',
                    'clock_out' => $log->End_Time ?: '--:--',
                    'status'    => $log->Clock_In_Status ?: '-',
                ];
            }
        }

        // 3. Cek saldo cuti tahunan karyawan (WARNING bila minus, diperbolehkan lanjut)
        $year = (int) $start->year;
        $balance = $this->leaveEntitlementService->getOrCreateUserBalance($nik, $year);
        $remainingBefore = (int) $balance->remaining_total;
        $projectedRemaining = $isDeductAnnual ? ($remainingBefore - $totalDays) : $remainingBefore;
        $willBeNegative = $isDeductAnnual && $projectedRemaining < 0;

        // 4. Cek Payroll LOCKED pada rentang tanggal
        $payrollLockInfo = $this->checkPayrollLockForRange($nik, $startStr, $endStr);

        // 5. Cek self-entry guard berdasarkan shared settings
        $settings = $this->manualCorrectionService->getSettings();
        $isSelfEntry = !empty($actor->Emp_ID) && strcasecmp((string) $actor->Emp_ID, $nik) === 0;
        $mustUseApproval = $isSelfEntry && !$settings->allow_self_direct_update;

        return [
            'target_user'             => $targetUser,
            'start_date'              => $startStr,
            'end_date'                => $endStr,
            'total_days'              => $totalDays,
            'category'                => $category ? [
                'id'                     => (int) $category->kat_izin_id,
                'code'                   => $category->kat_izin_code ?: $category->kat_izin_nama,
                'name'                   => $category->kat_izin_nama,
                'is_deduct_annual_leave' => $isDeductAnnual,
                'is_need_attachment'     => $isNeedAttachment,
                'jatah_hari'             => (int) ($category->jatah_hari ?? 0),
            ] : null,
            'has_overlapping_leave'   => (bool) $overlappingLeave,
            'overlapping_leave'       => $overlappingLeave ? [
                'id'         => (int) $overlappingLeave->id,
                'request_no' => $overlappingLeave->request_no,
                'leave_type' => $overlappingLeave->leave_type,
                'start_date' => $overlappingLeave->start_date,
                'end_date'   => $overlappingLeave->end_date,
                'status'     => $overlappingLeave->status,
            ] : null,
            'has_attendance_conflict' => count($attendanceLogList) > 0,
            'existing_attendance_logs'=> $attendanceLogList,
            'balance'                 => [
                'year'                => $year,
                'allocated_quota'     => (int) $balance->allocated_quota,
                'carry_forward_quota' => (int) $balance->carry_forward_quota,
                'remaining_before'    => $remainingBefore,
                'projected_remaining' => $projectedRemaining,
                'will_be_negative'    => $willBeNegative,
                'is_deduct_annual'    => $isDeductAnnual,
            ],
            'is_payroll_locked'       => $payrollLockInfo['is_locked'],
            'payroll_lock_detail'     => $payrollLockInfo,
            'is_self_entry'           => $isSelfEntry,
            'must_use_approval'       => $mustUseApproval,
            'settings'                => $settings,
        ];
    }

    /**
     * Cek apakah rentang tanggal cuti bersinggungan dengan periode payroll LOCKED.
     */
    public function checkPayrollLockForRange(string $nik, string $startDate, string $endDate): array
    {
        if (!Schema::hasTable('attendance_monthly_summaries')) {
            return ['is_locked' => false, 'periode' => null];
        }

        $userCol = Schema::hasColumn('attendance_monthly_summaries', 'user_id') ? 'user_id' : 'nik';
        $lockedRow = DB::table('attendance_monthly_summaries')
            ->where($userCol, $nik)
            ->where('payroll_post_status', 'LOCKED')
            ->where('cutoff_start_date', '<=', $endDate)
            ->where('cutoff_end_date', '>=', $startDate)
            ->orderBy('id', 'desc')
            ->first();

        if ($lockedRow) {
            return [
                'is_locked'   => true,
                'periode'     => $lockedRow->periode,
                'cutoff_from' => $lockedRow->cutoff_start_date,
                'cutoff_to'   => $lockedRow->cutoff_end_date,
            ];
        }

        return ['is_locked' => false, 'periode' => null];
    }

    /**
     * Simpan Pengajuan Cuti / Izin Manual untuk Karyawan Lain (Single atau dari Bulk Upload).
     */
    public function submitManualLeave($actor, array $data, $attachmentFile = null, ?string $ip = null, string $sourceTag = 'MANUAL_SINGLE'): array
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk fitur Input Cuti Manual.');
        }

        $settings = $this->manualCorrectionService->getSettings();
        $nik = trim((string) ($data['nik'] ?? ''));
        $leaveTypeInput = trim((string) ($data['leave_type'] ?? ''));
        $startRaw = trim((string) ($data['start_date'] ?? ''));
        $endRaw = trim((string) ($data['end_date'] ?? $startRaw));

        if ($nik === '') {
            throw new InvalidArgumentException('NIK karyawan wajib diisi.');
        }
        if ($leaveTypeInput === '') {
            throw new InvalidArgumentException('Kategori cuti/izin wajib dipilih.');
        }
        if ($startRaw === '') {
            throw new InvalidArgumentException('Tanggal mulai cuti wajib diisi.');
        }

        $start = Carbon::parse($startRaw)->startOfDay();
        $end = Carbon::parse($endRaw !== '' ? $endRaw : $startRaw)->startOfDay();
        if ($end->lt($start)) {
            throw new InvalidArgumentException('Tanggal selesai tidak boleh sebelum tanggal mulai.');
        }
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        // Validasi batas hari mundur (mengikuti setting shared)
        if (!empty($settings->max_backdate_days) && $settings->max_backdate_days > 0) {
            $diffDays = $start->diffInDays(Carbon::now()->startOfDay(), false);
            if ($diffDays > (int) $settings->max_backdate_days) {
                throw new InvalidArgumentException("Tanggal mulai {$startDate} melebihi batas maksimal mundur ({$settings->max_backdate_days} hari).");
            }
        }

        $category = $this->resolveCategory($leaveTypeInput);
        if (!$category) {
            throw new InvalidArgumentException("Kategori cuti/izin '{$leaveTypeInput}' tidak ditemukan atau tidak aktif.");
        }
        $canonicalLeaveType = $category->kat_izin_code ?: $category->kat_izin_nama;

        $reason = trim((string) ($data['reason'] ?? ''));
        if ($settings->require_reason && $reason === '') {
            throw new InvalidArgumentException('Alasan / keterangan cuti wajib diisi.');
        }

        $hasAttachmentInput = (bool) $attachmentFile || !empty($data['attachment_path']);
        if ($sourceTag !== 'BULK_UPLOAD' && $category->is_need_attachment === 'YES' && !$hasAttachmentInput) {
            throw new InvalidArgumentException("Kategori {$category->kat_izin_nama} wajib melampirkan dokumen pendukung.");
        }
        if ($sourceTag === 'BULK_UPLOAD' && $reason === '') {
            throw new InvalidArgumentException('Pada Bulk Upload, kolom Alasan wajib diisi jelas.');
        }

        $ctx = $this->inspectContext($nik, $startDate, $endDate, $canonicalLeaveType, $actor);
        $targetUser = $ctx['target_user'];
        $totalDays = (int) $ctx['total_days'];

        // Guard 1: Overlap cuti aktif -> BLOKIR
        if ($ctx['has_overlapping_leave']) {
            $ov = $ctx['overlapping_leave'];
            throw new InvalidArgumentException("Tanggal bertabrakan dengan pengajuan {$ov['leave_type']} ({$ov['request_no']}: {$ov['start_date']} s/d {$ov['end_date']}, status: {$ov['status']}).");
        }

        // Guard 2: Mode eksekusi & anti-self-direct-update
        $requestedMode = strtoupper(trim((string) ($data['execution_mode'] ?? $settings->default_mode)));
        if (!in_array($requestedMode, ['APPROVAL_REQUIRED', 'DIRECT_UPDATE'], true)) {
            $requestedMode = $settings->default_mode;
        }
        if (!$settings->allow_mode_selection) {
            $requestedMode = $settings->default_mode;
        }
        if ($ctx['must_use_approval'] && $requestedMode === 'DIRECT_UPDATE') {
            throw new InvalidArgumentException('Input cuti untuk NIK Anda sendiri tidak diizinkan menggunakan Update Langsung. Gunakan mode Proses Approval.');
        }

        // Guard 3: Payroll LOCKED
        $isPayrollLocked = $ctx['is_payroll_locked'];
        $postCutoffAction = strtoupper(trim((string) ($data['post_cutoff_action'] ?? 'NONE')));
        $postCutoffNotes = trim((string) ($data['post_cutoff_notes'] ?? ''));
        if ($isPayrollLocked) {
            if (!in_array($postCutoffAction, ['RAPEL_NEXT_PERIOD', 'FINANCE_DIRECT'], true)) {
                throw new InvalidArgumentException("Periode payroll untuk rentang {$startDate} s/d {$endDate} sudah berstatus LOCKED. Wajib pilih mekanisme Post-Cutoff (RAPEL_NEXT_PERIOD atau FINANCE_DIRECT).");
            }
        } else {
            $postCutoffAction = 'NONE';
        }

        $postCutoffStatus = 'NONE';
        if ($isPayrollLocked) {
            $postCutoffStatus = ($postCutoffAction === 'RAPEL_NEXT_PERIOD') ? 'PENDING_RAPEL' : 'FORWARDED_TO_FINANCE';
        }

        $clockInTime = $this->normalizeTime($data['clock_in_time'] ?? null);
        $clockOutTime = $this->normalizeTime($data['clock_out_time'] ?? null);

        $attachmentPath = $data['attachment_path'] ?? null;
        if ($attachmentFile) {
            $attachmentPath = $this->attendanceService->saveAttachmentFile($attachmentFile, 'leave_documents');
        }

        $requestNo = 'LV/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999));

        return DB::transaction(function () use (
            $actor,
            $targetUser,
            $nik,
            $canonicalLeaveType,
            $category,
            $sourceTag,
            $startDate,
            $endDate,
            $totalDays,
            $clockInTime,
            $clockOutTime,
            $reason,
            $attachmentPath,
            $requestNo,
            $requestedMode,
            $isPayrollLocked,
            $postCutoffAction,
            $postCutoffStatus,
            $postCutoffNotes,
            $ctx,
            $ip
        ) {
            $initialStatus = ($requestedMode === 'DIRECT_UPDATE') ? 'APPROVED' : 'SUBMITTED';

            $insertData = [
                'request_no'      => $requestNo,
                'nik'             => $nik,
                'leave_type'      => $canonicalLeaveType,
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'clock_in_time'   => $clockInTime,
                'clock_out_time'  => $clockOutTime,
                'total_days'      => $totalDays,
                'reason'          => $reason,
                'attachment'      => $attachmentPath,
                'attachment_path' => $attachmentPath,
                'status'          => $initialStatus,
                'created_by'      => $actor->username,
                'updated_by'      => $actor->username,
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ];

            if (Schema::hasColumn('attendance_leave_requests', 'source_mode')) {
                $insertData['source_mode'] = $sourceTag;
                $insertData['target_user_id'] = $targetUser->id;
                $insertData['allow_negative_balance'] = 1;
                $insertData['is_post_cutoff_adjustment'] = $isPayrollLocked ? 1 : 0;
                $insertData['post_cutoff_action'] = $postCutoffAction;
                $insertData['post_cutoff_status'] = $postCutoffStatus;
                $insertData['post_cutoff_notes'] = $postCutoffNotes ?: null;
            }

            $leaveId = DB::table('attendance_leave_requests')->insertGetId($insertData);
            $approvalRequestId = null;

            if ($requestedMode === 'APPROVAL_REQUIRED') {
                $apprContext = [
                    'Request_Type' => 'ATTENDANCE_LEAVE',
                    'Location'     => $targetUser->Location ?? 'HEAD_OFFICE',
                    'Company_ID'   => (int) ($targetUser->Company_ID ?? 1),
                    'Department'   => $targetUser->Department ?? 'ALL',
                ];

                try {
                    $apprResult = $this->workflowService->submit([
                        'Request_No'  => $requestNo,
                        'Description' => "Input Cuti Manual ({$canonicalLeaveType}, {$totalDays} hari) oleh {$actor->username}: {$reason}",
                    ], $apprContext, (int) $targetUser->id);

                    $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
                } catch (\Throwable $e) {
                    try {
                        $apprContext['Location'] = null;
                        $apprContext['Department'] = null;
                        $apprResult = $this->workflowService->submit([
                            'Request_No'  => $requestNo,
                            'Description' => "Input Cuti Manual ({$canonicalLeaveType}, {$totalDays} hari) oleh {$actor->username}: {$reason}",
                        ], $apprContext, (int) $targetUser->id);

                        $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
                    } catch (\Throwable $ex) {
                        Log::warning("Approval Engine fallback untuk manual leave {$requestNo}: " . $ex->getMessage());
                    }
                }

                if ($approvalRequestId) {
                    DB::table('attendance_leave_requests')
                        ->where('id', $leaveId)
                        ->update(['approval_request_id' => $approvalRequestId]);
                }
            }

            $deductResult = null;
            if ($requestedMode === 'DIRECT_UPDATE' && $category->is_deduct_annual_leave === 'YES') {
                $deductResult = $this->leaveEntitlementService->deductLeaveBalance(
                    $nik,
                    $totalDays,
                    $requestNo,
                    $canonicalLeaveType,
                    $actor,
                    true,
                    (int) date('Y', strtotime($startDate))
                );
            }

            // Dual-write ke tabel legacy izin atas nama karyawan target
            $legacyUserObj = (object) [
                'id'       => $targetUser->id,
                'Emp_ID'   => $nik,
                'Name'     => $targetUser->Name ?? $nik,
                'username' => $actor->username ?? ($targetUser->username ?? 'portal'),
                'Location' => $targetUser->Location ?? 'HO',
            ];

            $legacyIzinId = $this->legacySyncService->syncToLegacy([
                'id'                  => $leaveId,
                'request_no'          => $requestNo,
                'nik'                 => $nik,
                'leave_type'          => $canonicalLeaveType,
                'start_date'          => $startDate,
                'end_date'            => $endDate,
                'clock_in_time'       => $clockInTime,
                'clock_out_time'      => $clockOutTime,
                'reason'              => $reason,
                'attachment_path'     => $attachmentPath,
                'status'              => $initialStatus,
                'approval_request_id' => $approvalRequestId,
            ], $legacyUserObj);

            if ($legacyIzinId) {
                DB::table('attendance_leave_requests')
                    ->where('id', $leaveId)
                    ->update(['legacy_permission_id' => $legacyIzinId]);
            }

            $auditAction = $sourceTag === 'BULK_UPLOAD'
                ? ($requestedMode === 'DIRECT_UPDATE' ? 'LEAVE_BULK_UPLOAD_DIRECT' : 'LEAVE_BULK_UPLOAD_APPROVAL')
                : ($requestedMode === 'DIRECT_UPDATE' ? 'LEAVE_DIRECT_UPDATE' : 'LEAVE_SUBMIT_APPROVAL');

            $leaveActionSummary = "{$canonicalLeaveType} ({$startDate} s/d {$endDate}, {$totalDays} hari)";
            if ($deductResult && isset($deductResult['remaining_balance']) && $deductResult['remaining_balance'] < 0) {
                $leaveActionSummary .= " [Saldo Minus: {$deductResult['remaining_balance']}]";
            }

            $this->writeLeaveAuditLog([
                'leave_request_id'   => $leaveId,
                'action'             => $auditAction,
                'actor_username'     => $actor->username,
                'target_nik'         => $nik,
                'scan_date'          => $startDate,
                'mode'               => $requestedMode,
                'before_data'        => [
                    'remaining_balance'       => $ctx['balance']['remaining_before'],
                    'has_attendance_conflict' => $ctx['has_attendance_conflict'],
                ],
                'after_data'         => [
                    'leave_id'            => $leaveId,
                    'request_no'          => $requestNo,
                    'status'              => $initialStatus,
                    'leave_type'          => $canonicalLeaveType,
                    'start_date'          => $startDate,
                    'end_date'            => $endDate,
                    'total_days'          => $totalDays,
                    'remaining_balance'   => $deductResult['remaining_balance'] ?? $ctx['balance']['remaining_before'],
                    'legacy_permission_id'=> $legacyIzinId,
                ],
                'leave_action'       => $leaveActionSummary,
                'post_cutoff_action' => $postCutoffAction,
                'reason'             => $reason,
                'ip_address'         => $ip,
            ]);

            $msg = ($requestedMode === 'DIRECT_UPDATE')
                ? "Cuti/izin {$requestNo} ({$canonicalLeaveType}) berhasil diterapkan langsung (APPROVED) untuk NIK {$nik}."
                : "Pengajuan cuti/izin {$requestNo} ({$canonicalLeaveType}) berhasil dikirim ke alur Approval.";

            if ($deductResult && isset($deductResult['remaining_balance']) && $deductResult['remaining_balance'] < 0) {
                $msg .= " Catatan: Saldo cuti menjadi minus ({$deductResult['remaining_balance']} hari).";
            }

            return [
                'status'               => 'success',
                'leave_id'             => $leaveId,
                'request_no'           => $requestNo,
                'mode'                 => $requestedMode,
                'approval_request_id'  => $approvalRequestId,
                'legacy_permission_id' => $legacyIzinId,
                'post_cutoff_action'   => $postCutoffAction,
                'remaining_balance'    => $deductResult['remaining_balance'] ?? $ctx['balance']['remaining_before'],
                'message'              => $msg,
            ];
        });
    }

    /**
     * Buat Template Excel (.xlsx) 2 Sheet untuk Bulk Upload Cuti/Izin Manual.
     */
    public function buildBulkTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Template Input Cuti Manual
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Cuti Manual');

        $headers = [
            'NIK',
            'Kode/Nama Kategori Cuti',
            'Tanggal Mulai (YYYY-MM-DD)',
            'Tanggal Selesai (YYYY-MM-DD)',
            'Jam Masuk Parsial (HH:MM)',
            'Jam Pulang Parsial (HH:MM)',
            'Alasan',
            'Mode (DIRECT/APPROVAL)',
            'Post Cutoff (NONE/RAPEL_NEXT_PERIOD/FINANCE_DIRECT)',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E8FB');

        $sheet->fromArray([
            'EMP001',
            'CUTI_TAHUNAN',
            date('Y-m-d'),
            date('Y-m-d'),
            '',
            '',
            'Cuti keperluan keluarga (diinputkan oleh HR)',
            'APPROVAL',
            'NONE',
        ], null, 'A2');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet 2: Referensi Kategori Izin Aktif dari Database
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi Kategori Izin');
        $refHeaders = ['Kode Kategori', 'Nama Kategori', 'Potong Cuti Tahunan', 'Wajib Lampiran (Input Single)', 'Maks Hari'];
        $refSheet->fromArray($refHeaders, null, 'A1');
        $refSheet->getStyle('A1:E1')->getFont()->setBold(true);
        $refSheet->getStyle('A1:E1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2F0D9');

        $categories = $this->getActiveLeaveCategories();
        $rIdx = 2;
        foreach ($categories as $cat) {
            $refSheet->fromArray([
                $cat->kat_izin_code ?: $cat->kat_izin_nama,
                $cat->kat_izin_nama,
                $cat->is_deduct_annual_leave ?? 'NO',
                $cat->is_need_attachment ?? 'NO',
                (int) ($cat->jatah_hari ?? 0),
            ], null, 'A' . $rIdx);
            $rIdx++;
        }

        foreach (range('A', 'E') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Preview atau Eksekusi Bulk Upload Excel (.xlsx / .csv) untuk Cuti Manual.
     */
    public function processBulkUpload($actor, $uploadedFile, string $actionMode = 'preview', ?string $ip = null): array
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk fitur Bulk Upload Cuti.');
        }

        $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
        $rows = $spreadsheet->getSheet(0)->toArray(null, true, true, false);

        if (count($rows) <= 1) {
            throw new InvalidArgumentException('File upload kosong atau tidak memiliki baris data (minimal mulai baris ke-2).');
        }

        $results = [];
        $validCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $executedCount = 0;
        $seenRanges = [];

        foreach ($rows as $idx => $row) {
            if ($idx === 0) {
                continue; // skip header
            }

            $rowNum = $idx + 1;
            $nik = trim((string) ($row[0] ?? ''));
            $leaveTypeRaw = trim((string) ($row[1] ?? ''));
            $startRaw = $this->parseSpreadsheetDate($row[2] ?? '');
            $endRaw = $this->parseSpreadsheetDate($row[3] ?? '');
            if ($endRaw === '') {
                $endRaw = $startRaw;
            }
            $clockInRaw = $this->parseSpreadsheetTime($row[4] ?? '');
            $clockOutRaw = $this->parseSpreadsheetTime($row[5] ?? '');
            $reason = trim((string) ($row[6] ?? ''));
            $modeRaw = strtoupper(trim((string) ($row[7] ?? 'APPROVAL')));
            $postCutoffRaw = strtoupper(trim((string) ($row[8] ?? 'NONE')));

            if ($nik === '' && $leaveTypeRaw === '' && $startRaw === '' && $reason === '') {
                continue; // skip empty trailing row
            }

            $execMode = ($modeRaw === 'DIRECT' || $modeRaw === 'DIRECT_UPDATE') ? 'DIRECT_UPDATE' : 'APPROVAL_REQUIRED';

            try {
                if ($nik === '') {
                    throw new InvalidArgumentException('NIK wajib diisi.');
                }
                if ($leaveTypeRaw === '') {
                    throw new InvalidArgumentException('Kategori cuti/izin wajib diisi.');
                }
                if ($startRaw === '') {
                    throw new InvalidArgumentException('Tanggal mulai wajib diisi.');
                }
                if ($reason === '') {
                    throw new InvalidArgumentException('Alasan wajib diisi pada Bulk Upload.');
                }

                $parsedStart = Carbon::parse($startRaw)->toDateString();
                $parsedEnd = Carbon::parse($endRaw)->toDateString();
                $ctx = $this->inspectContext($nik, $parsedStart, $parsedEnd, $leaveTypeRaw, $actor);

                if (!$ctx['category']) {
                    throw new InvalidArgumentException("Kategori '{$leaveTypeRaw}' tidak ditemukan/tidak aktif.");
                }

                // Cek intra-file overlap untuk NIK yang sama
                foreach ($seenRanges[$nik] ?? [] as $prevRange) {
                    if ($prevRange['start'] <= $parsedEnd && $prevRange['end'] >= $parsedStart) {
                        throw new InvalidArgumentException("Bertabrakan dengan baris {$prevRange['row']} di file yang sama ({$prevRange['start']} s/d {$prevRange['end']}).");
                    }
                }

                if ($ctx['has_overlapping_leave']) {
                    $ov = $ctx['overlapping_leave'];
                    throw new InvalidArgumentException("Overlap cuti aktif {$ov['request_no']} ({$ov['start_date']} s/d {$ov['end_date']}, {$ov['status']})");
                }
                if ($ctx['must_use_approval'] && $execMode === 'DIRECT_UPDATE') {
                    throw new InvalidArgumentException('Tidak boleh DIRECT_UPDATE untuk NIK sendiri.');
                }

                $warnings = [];
                if ($ctx['balance']['will_be_negative']) {
                    $warnings[] = "Saldo akan minus ({$ctx['balance']['projected_remaining']} hari)";
                }
                if ($ctx['has_attendance_conflict']) {
                    $warnings[] = 'Ada log absensi pada tanggal ini (' . count($ctx['existing_attendance_logs']) . ' hari)';
                }
                if ($ctx['is_payroll_locked']) {
                    if (!in_array($postCutoffRaw, ['RAPEL_NEXT_PERIOD', 'FINANCE_DIRECT'], true)) {
                        throw new InvalidArgumentException('Payroll periode ini LOCKED. Pilih RAPEL_NEXT_PERIOD atau FINANCE_DIRECT.');
                    }
                    $warnings[] = "Post-Cutoff: {$postCutoffRaw}";
                }

                $seenRanges[$nik][] = [
                    'row'   => $rowNum,
                    'start' => $parsedStart,
                    'end'   => $parsedEnd,
                ];

                $validCount++;
                if (!empty($warnings)) {
                    $warningCount++;
                }

                $execResult = null;
                if ($actionMode === 'execute') {
                    $execResult = $this->submitManualLeave($actor, [
                        'nik'                => $nik,
                        'leave_type'         => $ctx['category']['code'],
                        'start_date'         => $parsedStart,
                        'end_date'           => $parsedEnd,
                        'clock_in_time'      => $clockInRaw ?: null,
                        'clock_out_time'     => $clockOutRaw ?: null,
                        'reason'             => $reason,
                        'execution_mode'     => $execMode,
                        'post_cutoff_action' => $postCutoffRaw,
                    ], null, $ip, 'BULK_UPLOAD');
                    $executedCount++;
                }

                $rowStatus = $actionMode === 'execute'
                    ? 'EXECUTED'
                    : (!empty($warnings) ? 'WARNING' : 'VALID');

                $results[] = [
                    'row'        => $rowNum,
                    'nik'        => $nik,
                    'name'       => $ctx['target_user']->Name ?? $nik,
                    'leave_type' => $ctx['category']['code'],
                    'start_date' => $parsedStart,
                    'end_date'   => $parsedEnd,
                    'total_days' => $ctx['total_days'],
                    'mode'       => $execMode,
                    'status'     => $rowStatus,
                    'message'    => $execResult ? $execResult['message'] : (empty($warnings) ? 'Siap diproses' : implode('; ', $warnings)),
                ];
            } catch (\Throwable $e) {
                $errorCount++;
                $results[] = [
                    'row'        => $rowNum,
                    'nik'        => $nik,
                    'name'       => '-',
                    'leave_type' => $leaveTypeRaw,
                    'start_date' => $startRaw,
                    'end_date'   => $endRaw,
                    'total_days' => 0,
                    'mode'       => $execMode,
                    'status'     => 'ERROR',
                    'message'    => $e->getMessage(),
                ];
            }
        }

        return [
            'action_mode'    => $actionMode,
            'total_rows'     => count($results),
            'valid_count'    => $validCount,
            'warning_count'  => $warningCount,
            'error_count'    => $errorCount,
            'executed_count' => $executedCount,
            'rows'           => $results,
        ];
    }

    /**
     * Ambil daftar pengajuan cuti/izin berstatus SUBMITTED untuk diproses massal.
     */
    public function getPendingLeaveRequests(array $filters = []): array
    {
        $q = DB::table('attendance_leave_requests as l')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'l.nik')
            ->leftJoin('kategori_izin as k', function ($join) {
                $join->on('k.kat_izin_code', '=', 'l.leave_type')
                    ->orOn('k.kat_izin_nama', '=', 'l.leave_type');
            })
            ->where('l.status', 'SUBMITTED')
            ->select(
                'l.*',
                'u.Name as employee_name',
                'u.Department as department',
                'k.kat_izin_nama as category_name'
            )
            ->orderBy('l.start_date', 'desc')
            ->orderBy('l.id', 'desc');

        if (!empty($filters['department']) && $filters['department'] !== 'ALL') {
            $q->where('u.Department', $filters['department']);
        }

        return $q->limit(200)->get()->toArray();
    }

    /**
     * Setujui & terapkan banyak pengajuan cuti SUBMITTED sekaligus (potong saldo + sync legacy + audit log).
     */
    public function bulkApprovePendingLeaves($actor, array $leaveIds, ?string $ip = null): array
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk memproses persetujuan cuti massal.');
        }

        $ids = array_values(array_unique(array_map('intval', $leaveIds)));
        if (empty($ids)) {
            throw new InvalidArgumentException('Pilih minimal 1 pengajuan cuti untuk diproses.');
        }

        $settings = $this->manualCorrectionService->getSettings();
        $approved = 0;
        $skipped = [];

        foreach ($ids as $id) {
            try {
                DB::transaction(function () use ($id, $actor, $settings, $ip, &$approved) {
                    $leave = DB::table('attendance_leave_requests')->where('id', $id)->lockForUpdate()->first();
                    if (!$leave || $leave->status !== 'SUBMITTED') {
                        throw new InvalidArgumentException('Status bukan SUBMITTED.');
                    }

                    if (!$settings->allow_self_direct_update && !empty($actor->Emp_ID) && strcasecmp($actor->Emp_ID, $leave->nik) === 0) {
                        throw new InvalidArgumentException('Tidak diizinkan menyetujui cuti NIK sendiri secara langsung.');
                    }

                    // Sinkronkan approval_request jika terhubung
                    if (!empty($leave->approval_request_id) && Schema::hasTable('approval_request')) {
                        DB::table('approval_request')
                            ->where('ID', $leave->approval_request_id)
                            ->whereNotIn('Status', ['APPROVED', 'REJECTED', 'CANCELLED'])
                            ->update([
                                'Status'       => 'APPROVED',
                                'Completed_At' => Carbon::now(),
                                'Updated_At'   => Carbon::now(),
                            ]);

                        if (Schema::hasTable('approval_request_task')) {
                            DB::table('approval_request_task')
                                ->where('Request_ID', $leave->approval_request_id)
                                ->whereIn('Status', ['PENDING', 'CLAIMED', 'HELD'])
                                ->update([
                                    'Status'     => 'AUTO_APPROVED',
                                    'Acted_By'   => (int) ($actor->id ?? 0),
                                    'Acted_At'   => Carbon::now(),
                                    'Action'     => 'BULK_APPROVE',
                                    'Notes'      => "Disetujui massal oleh {$actor->username}",
                                    'Updated_At' => Carbon::now(),
                                ]);
                        }
                    }

                    $leaveYear = !empty($leave->start_date) ? (int) date('Y', strtotime($leave->start_date)) : (int) date('Y');
                    $deductRes = $this->leaveEntitlementService->deductLeaveBalance(
                        $leave->nik,
                        (int) ($leave->total_days ?? 1),
                        $leave->request_no,
                        $leave->leave_type,
                        $actor,
                        true,
                        $leaveYear
                    );

                    $this->legacySyncService->syncApprovalStatusToLegacy(
                        $leave->approval_request_id ? (int) $leave->approval_request_id : null,
                        'APPROVED',
                        (int) $leave->id
                    );

                    $updatePayload = [
                        'status'     => 'APPROVED',
                        'updated_at' => Carbon::now(),
                    ];
                    if (Schema::hasColumn('attendance_leave_requests', 'updated_by')) {
                        $updatePayload['updated_by'] = $actor->username;
                    }
                    if (Schema::hasColumn('attendance_leave_requests', 'allow_negative_balance')) {
                        $updatePayload['allow_negative_balance'] = 1;
                    }

                    DB::table('attendance_leave_requests')->where('id', $leave->id)->update($updatePayload);

                    $summary = "{$leave->leave_type} ({$leave->start_date} s/d {$leave->end_date}, {$leave->total_days} hari)";
                    if (isset($deductRes['remaining_balance']) && $deductRes['remaining_balance'] < 0) {
                        $summary .= " [Saldo Minus: {$deductRes['remaining_balance']}]";
                    }

                    $this->writeLeaveAuditLog([
                        'leave_request_id'   => $leave->id,
                        'action'             => 'LEAVE_BULK_APPROVE',
                        'actor_username'     => $actor->username,
                        'target_nik'         => $leave->nik,
                        'scan_date'          => $leave->start_date,
                        'mode'               => 'BULK_APPROVE',
                        'before_data'        => ['status' => $leave->status],
                        'after_data'         => [
                            'status'            => 'APPROVED',
                            'remaining_balance' => $deductRes['remaining_balance'] ?? null,
                        ],
                        'leave_action'       => $summary,
                        'post_cutoff_action' => $leave->post_cutoff_action ?? 'NONE',
                        'reason'             => $leave->reason,
                        'ip_address'         => $ip,
                    ]);

                    $approved++;
                });
            } catch (\Throwable $e) {
                $skipped[] = "ID {$id}: " . $e->getMessage();
            }
        }

        return [
            'approved_count' => $approved,
            'skipped'        => $skipped,
            'message'        => "Berhasil menyetujui {$approved} pengajuan cuti." . (!empty($skipped) ? ' Dilewati: ' . implode(' | ', $skipped) : ''),
        ];
    }

    protected function normalizeTime(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }
        $t = trim($time);
        if ($t === '' || $t === '--:--') {
            return null;
        }
        if (preg_match('/^\d{2}:\d{2}$/', $t)) {
            return $t . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $t)) {
            return $t;
        }
        return Carbon::parse($t)->format('H:i:s');
    }

    protected function parseSpreadsheetDate($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }
        return trim((string) $value);
    }

    protected function parseSpreadsheetTime($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('H:i');
        }
        return trim((string) $value);
    }

    protected function writeLeaveAuditLog(array $payload): void
    {
        if (!Schema::hasTable('attendance_manual_audit_logs')) {
            return;
        }

        $row = [
            'correction_id'      => $payload['correction_id'] ?? null,
            'action'             => $payload['action'],
            'actor_username'     => $payload['actor_username'] ?? 'SYSTEM',
            'target_nik'         => $payload['target_nik'] ?? null,
            'scan_date'          => $payload['scan_date'] ?? null,
            'mode'               => $payload['mode'] ?? null,
            'before_data'        => isset($payload['before_data']) ? json_encode($payload['before_data']) : null,
            'after_data'         => isset($payload['after_data']) ? json_encode($payload['after_data']) : null,
            'leave_action'       => $payload['leave_action'] ?? null,
            'post_cutoff_action' => $payload['post_cutoff_action'] ?? null,
            'reason'             => $payload['reason'] ?? null,
            'ip_address'         => $payload['ip_address'] ?? null,
            'created_at'         => Carbon::now(),
        ];

        if (Schema::hasColumn('attendance_manual_audit_logs', 'leave_request_id')) {
            $row['leave_request_id'] = $payload['leave_request_id'] ?? null;
        }

        DB::table('attendance_manual_audit_logs')->insert($row);
    }
}
