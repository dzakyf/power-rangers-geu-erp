<?php

namespace App\Services\Attendance;

use App\AttendanceMain;
use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ManualAttendanceCorrectionService
{
    protected $attendanceService;
    protected $workflowService;
    protected $cutoffService;

    public function __construct(
        AttendanceService $attendanceService,
        ApprovalWorkflowService $workflowService,
        AttendanceCutoffService $cutoffService
    ) {
        $this->attendanceService = $attendanceService;
        $this->workflowService = $workflowService;
        $this->cutoffService = $cutoffService;
    }

    /**
     * Ambil konfigurasi aktif perbaikan absen manual (dengan fallback default).
     */
    public function getSettings(): object
    {
        $defaultUsernames = array_values(array_filter(array_map(function ($u) {
            return strtolower(trim((string) $u));
        }, force_approve_allowed_usernames())));

        $row = null;
        if (Schema::hasTable('attendance_manual_correction_settings')) {
            $row = DB::table('attendance_manual_correction_settings')->orderBy('id', 'asc')->first();
        }

        $allowed = $defaultUsernames;
        if ($row && !empty($row->allowed_usernames)) {
            $decoded = json_decode($row->allowed_usernames, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $allowed = array_values(array_filter(array_map(function ($u) {
                    return strtolower(trim((string) $u));
                }, $decoded)));
            }
        }

        return (object) [
            'id'                       => $row ? $row->id : null,
            'allowed_usernames'        => $allowed,
            'default_mode'             => $row ? $row->default_mode : 'APPROVAL_REQUIRED',
            'allow_mode_selection'     => $row ? (bool) $row->allow_mode_selection : true,
            'require_reason'           => $row ? (bool) $row->require_reason : true,
            'require_evidence'         => $row ? (bool) $row->require_evidence : false,
            'max_backdate_days'        => ($row && $row->max_backdate_days !== null) ? (int) $row->max_backdate_days : null,
            'allow_self_direct_update' => $row ? (bool) $row->allow_self_direct_update : false,
            'updated_by'               => $row ? $row->updated_by : null,
            'updated_at'               => $row ? $row->updated_at : null,
        ];
    }

    /**
     * Simpan perubahan setting perbaikan absen manual + catat audit log.
     */
    public function updateSettings($actor, array $payload, ?string $ip = null): object
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk mengubah pengaturan ini.');
        }

        $before = $this->getSettings();

        $rawUsernames = $payload['allowed_usernames'] ?? [];
        if (is_string($rawUsernames)) {
            $rawUsernames = preg_split('/[\s,;]+/', $rawUsernames);
        }
        $usernames = array_values(array_unique(array_filter(array_map(function ($u) {
            return strtolower(trim((string) $u));
        }, is_array($rawUsernames) ? $rawUsernames : []))));

        if (empty($usernames)) {
            throw new InvalidArgumentException('Minimal harus ada 1 username yang diizinkan.');
        }

        // Pastikan aktor saat ini tidak mengunci dirinya sendiri keluar tanpa sengaja jika tidak ada admin lain
        $defaultMode = strtoupper(trim((string) ($payload['default_mode'] ?? 'APPROVAL_REQUIRED')));
        if (!in_array($defaultMode, ['APPROVAL_REQUIRED', 'DIRECT_UPDATE'], true)) {
            $defaultMode = 'APPROVAL_REQUIRED';
        }

        $maxBackdate = isset($payload['max_backdate_days']) && $payload['max_backdate_days'] !== ''
            ? max(0, (int) $payload['max_backdate_days'])
            : null;
        if ($maxBackdate === 0) {
            $maxBackdate = null;
        }

        $data = [
            'allowed_usernames'        => json_encode($usernames),
            'default_mode'             => $defaultMode,
            'allow_mode_selection'     => !empty($payload['allow_mode_selection']) ? 1 : 0,
            'require_reason'           => !empty($payload['require_reason']) ? 1 : 0,
            'require_evidence'         => !empty($payload['require_evidence']) ? 1 : 0,
            'max_backdate_days'        => $maxBackdate,
            'allow_self_direct_update' => !empty($payload['allow_self_direct_update']) ? 1 : 0,
            'updated_by'               => $actor->username,
            'updated_at'               => Carbon::now(),
        ];

        DB::transaction(function () use ($before, $data, $actor, $ip) {
            $existing = DB::table('attendance_manual_correction_settings')->orderBy('id', 'asc')->first();
            if ($existing) {
                DB::table('attendance_manual_correction_settings')->where('id', $existing->id)->update($data);
            } else {
                $data['created_at'] = Carbon::now();
                DB::table('attendance_manual_correction_settings')->insert($data);
            }

            $this->writeAuditLog([
                'action'         => 'SETTINGS_UPDATED',
                'actor_username' => $actor->username,
                'before_data'    => $before,
                'after_data'     => $data,
                'reason'         => 'Pembaruan konfigurasi perbaikan absen manual',
                'ip_address'     => $ip,
            ]);
        });

        return $this->getSettings();
    }

    /**
     * Periksa konteks karyawan & tanggal sebelum perbaikan dilakukan (untuk guard celah di UI & server).
     */
    public function inspectContext(string $nik, string $date, $actor): array
    {
        $nik = trim($nik);
        $date = Carbon::parse($date)->toDateString();

        $targetUser = DB::table('users')
            ->where('Emp_ID', $nik)
            ->select('id', 'username', 'Emp_ID', 'Name', 'Department', 'Position', 'Location', 'Company_ID', 'Status')
            ->first();

        if (!$targetUser) {
            throw new InvalidArgumentException("Karyawan dengan NIK {$nik} tidak ditemukan.");
        }

        $existingLog = DB::table('attendant_log')
            ->where('NIK', $nik)
            ->where('Scan_Date', $date)
            ->first();

        $pendingCorrection = DB::table('attendance_corrections')
            ->where('nik', $nik)
            ->where('attendance_date', $date)
            ->where('status', 'SUBMITTED')
            ->orderBy('id', 'desc')
            ->first();

        $approvedLeave = DB::table('attendance_leave_requests')
            ->where('nik', $nik)
            ->where('status', 'APPROVED')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->orderBy('id', 'desc')
            ->first();

        $leaveInfo = null;
        if ($approvedLeave) {
            $isSingleDay = ($approvedLeave->start_date === $approvedLeave->end_date);
            $leaveInfo = [
                'id'            => (int) $approvedLeave->id,
                'request_no'    => $approvedLeave->request_no,
                'leave_type'    => $approvedLeave->leave_type,
                'start_date'    => $approvedLeave->start_date,
                'end_date'      => $approvedLeave->end_date,
                'total_days'    => (float) ($approvedLeave->total_days ?? 1),
                'reason'        => $approvedLeave->reason,
                'is_single_day' => $isSingleDay,
            ];
        }

        $payrollLockInfo = $this->checkPayrollLockForDate($nik, $date, $targetUser->Department);
        $schedule = $this->attendanceService->getWorkScheduleForDate($nik, $date);
        $settings = $this->getSettings();

        $isSelfCorrection = !empty($actor->Emp_ID) && strcasecmp((string) $actor->Emp_ID, $nik) === 0;
        $mustUseApproval = $isSelfCorrection && !$settings->allow_self_direct_update;

        return [
            'target_user'            => $targetUser,
            'date'                   => $date,
            'schedule'               => $schedule,
            'existing_log'           => $existingLog ? [
                'id'               => $existingLog->ID,
                'clock_in'         => $existingLog->Start_Time,
                'clock_out'        => $existingLog->End_Time,
                'clock_in_status'  => $existingLog->Clock_In_Status,
                'late_minutes'     => (int) ($existingLog->Late_Minutes ?? 0),
                'overtime_minutes' => (int) ($existingLog->Overtime_Minutes ?? 0),
                'gps_anomaly_flag' => $existingLog->GPS_Anomaly_Flag ?? 'CLEAN',
                'notes'            => $existingLog->Notes ?? '',
            ] : null,
            'has_pending_correction' => (bool) $pendingCorrection,
            'pending_correction'     => $pendingCorrection ? [
                'id'            => $pendingCorrection->id,
                'correction_no' => $pendingCorrection->correction_no,
                'created_by'    => $pendingCorrection->created_by,
                'created_at'    => $pendingCorrection->created_at,
            ] : null,
            'approved_leave'         => $leaveInfo,
            'is_payroll_locked'      => $payrollLockInfo['is_locked'],
            'payroll_lock_detail'    => $payrollLockInfo,
            'is_self_correction'     => $isSelfCorrection,
            'must_use_approval'      => $mustUseApproval,
            'settings'               => $settings,
        ];
    }

    /**
     * Cek apakah tanggal masuk dalam periode payroll yang sudah LOCKED pada attendance_monthly_summaries.
     */
    public function checkPayrollLockForDate(string $nik, string $date, ?string $department = null): array
    {
        if (!Schema::hasTable('attendance_monthly_summaries')) {
            return ['is_locked' => false, 'periode' => null];
        }

        $userCol = Schema::hasColumn('attendance_monthly_summaries', 'user_id') ? 'user_id' : 'nik';
        $lockedRow = DB::table('attendance_monthly_summaries')
            ->where($userCol, $nik)
            ->where('payroll_post_status', 'LOCKED')
            ->where('cutoff_start_date', '<=', $date)
            ->where('cutoff_end_date', '>=', $date)
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
     * Eksekusi Perbaikan Absen Manual (Single).
     */
    public function submitManualCorrection($actor, array $data, $evidenceFile = null, ?string $ip = null, string $sourceTag = ''): array
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk melakukan perbaikan absen manual.');
        }

        $settings = $this->getSettings();
        $nik = trim((string) ($data['nik'] ?? ''));
        $date = Carbon::parse($data['attendance_date'])->toDateString();

        if ($nik === '') {
            throw new InvalidArgumentException('NIK karyawan wajib diisi.');
        }

        // Validasi batas hari mundur (max_backdate_days)
        if (!empty($settings->max_backdate_days) && $settings->max_backdate_days > 0) {
            $diffDays = Carbon::parse($date)->startOfDay()->diffInDays(Carbon::now()->startOfDay(), false);
            if ($diffDays > (int) $settings->max_backdate_days) {
                throw new InvalidArgumentException("Tanggal {$date} melebihi batas maksimal mundur ({$settings->max_backdate_days} hari).");
            }
        }

        $corrType = strtoupper(trim((string) ($data['correction_type'] ?? 'BOTH')));
        if (!in_array($corrType, ['BOTH', 'IN', 'OUT'], true)) {
            $corrType = 'BOTH';
        }

        $corrIn = $this->normalizeTime($data['corrected_clock_in'] ?? null);
        $corrOut = $this->normalizeTime($data['corrected_clock_out'] ?? null);

        if ($corrType === 'IN') {
            $corrOut = null;
            if (!$corrIn) {
                throw new InvalidArgumentException('Jam Masuk perbaikan wajib diisi.');
            }
        } elseif ($corrType === 'OUT') {
            $corrIn = null;
            if (!$corrOut) {
                throw new InvalidArgumentException('Jam Pulang perbaikan wajib diisi.');
            }
        } else {
            if (!$corrIn && !$corrOut) {
                throw new InvalidArgumentException('Minimal salah satu Jam Masuk atau Jam Pulang wajib diisi.');
            }
        }

        $reason = trim((string) ($data['reason'] ?? ''));
        if ($settings->require_reason && $reason === '') {
            throw new InvalidArgumentException('Alasan perbaikan absen wajib diisi.');
        }

        if ($settings->require_evidence && !$evidenceFile && empty($data['evidence_path'])) {
            throw new InvalidArgumentException('Lampiran bukti perbaikan absen wajib diunggah sesuai pengaturan aktif.');
        }

        $ctx = $this->inspectContext($nik, $date, $actor);
        $targetUser = $ctx['target_user'];

        // Guard 1: Anti duplikasi pending correction
        if ($ctx['has_pending_correction']) {
            $pNo = $ctx['pending_correction']['correction_no'] ?? '-';
            throw new InvalidArgumentException("Masih terdapat pengajuan koreksi berstatus SUBMITTED ({$pNo}) untuk NIK {$nik} pada tanggal {$date}.");
        }

        // Guard 2: Mode eksekusi (DIRECT_UPDATE vs APPROVAL_REQUIRED)
        $requestedMode = strtoupper(trim((string) ($data['execution_mode'] ?? $settings->default_mode)));
        if (!in_array($requestedMode, ['APPROVAL_REQUIRED', 'DIRECT_UPDATE'], true)) {
            $requestedMode = $settings->default_mode;
        }
        if (!$settings->allow_mode_selection) {
            $requestedMode = $settings->default_mode;
        }
        if ($ctx['must_use_approval'] && $requestedMode === 'DIRECT_UPDATE') {
            throw new InvalidArgumentException('Perbaikan absen untuk NIK Anda sendiri tidak diizinkan menggunakan Update Langsung. Silakan gunakan mode Proses Approval.');
        }

        // Guard 3: Bentrok Cuti APPROVED
        $cancelApprovedLeave = !empty($data['cancel_approved_leave']);
        $approvedLeave = $ctx['approved_leave'];
        if ($approvedLeave) {
            if (!$cancelApprovedLeave) {
                throw new InvalidArgumentException("Karyawan memiliki Cuti/Izin APPROVED ({$approvedLeave['request_no']}) pada tanggal {$date}. Centang opsi Batalkan Cuti & Refund Kuota untuk melanjutkan.");
            }
            if (!$approvedLeave['is_single_day']) {
                throw new InvalidArgumentException("Cuti {$approvedLeave['request_no']} berdurasi multi-hari ({$approvedLeave['start_date']} s/d {$approvedLeave['end_date']}). Sistem hanya mendukung pembatalan penuh untuk cuti 1 hari; silakan batalkan/revisi cuti secara manual terlebih dahulu.");
            }
        }

        // Guard 4: Periode Payroll LOCKED
        $isPayrollLocked = $ctx['is_payroll_locked'];
        $postCutoffAction = strtoupper(trim((string) ($data['post_cutoff_action'] ?? 'NONE')));
        $postCutoffNotes = trim((string) ($data['post_cutoff_notes'] ?? ''));
        if ($isPayrollLocked) {
            if (!in_array($postCutoffAction, ['RAPEL_NEXT_PERIOD', 'FINANCE_DIRECT'], true)) {
                throw new InvalidArgumentException("Periode payroll untuk tanggal {$date} sudah berstatus LOCKED. Wajib pilih mekanisme Post-Cutoff (Rapel Periode Berikutnya atau Proses Finance Langsung).");
            }
        } else {
            $postCutoffAction = 'NONE';
        }

        $postCutoffStatus = 'NONE';
        if ($postCutoffAction === 'RAPEL_NEXT_PERIOD') {
            $postCutoffStatus = 'PENDING_RAPEL';
        } elseif ($postCutoffAction === 'FINANCE_DIRECT') {
            $postCutoffStatus = 'FORWARDED_TO_FINANCE';
        }

        $clearGpsAnomaly = !empty($data['clear_gps_anomaly']);
        $evidencePath = !empty($data['evidence_path'])
            ? $data['evidence_path']
            : $this->attendanceService->saveAttachmentFile($evidenceFile, 'correction_documents');

        $beforeSnapshot = $ctx['existing_log'];
        $correctionNo = 'CORR/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999));

        $sourceMode = $sourceTag !== ''
            ? $sourceTag
            : ($requestedMode === 'DIRECT_UPDATE' ? 'ADMIN_DIRECT' : 'ADMIN_APPROVAL');

        return DB::transaction(function () use (
            $actor, $targetUser, $nik, $date, $corrType, $corrIn, $corrOut, $reason,
            $evidencePath, $correctionNo, $requestedMode, $sourceMode, $beforeSnapshot,
            $cancelApprovedLeave, $approvedLeave, $isPayrollLocked, $postCutoffAction,
            $postCutoffStatus, $postCutoffNotes, $clearGpsAnomaly, $ip
        ) {
            $origIn = $beforeSnapshot ? $beforeSnapshot['clock_in'] : null;
            $origOut = $beforeSnapshot ? $beforeSnapshot['clock_out'] : null;

            $insertPayload = [
                'correction_no'              => $correctionNo,
                'nik'                        => $nik,
                'target_user_id'             => $targetUser->id,
                'attendance_date'            => $date,
                'correction_type'            => $corrType,
                'source_mode'                => $sourceMode,
                'original_clock_in'          => $origIn,
                'original_clock_out'         => $origOut,
                'corrected_clock_in'         => $corrIn,
                'corrected_clock_out'        => $corrOut,
                'reason'                     => $reason,
                'before_snapshot'            => $beforeSnapshot ? json_encode($beforeSnapshot) : null,
                'evidence_file'              => $evidencePath,
                'attachment_path'            => $evidencePath,
                'status'                     => $requestedMode === 'DIRECT_UPDATE' ? 'APPROVED' : 'SUBMITTED',
                'pending_cancel_leave_id'    => ($requestedMode === 'APPROVAL_REQUIRED' && $cancelApprovedLeave && $approvedLeave) ? $approvedLeave['id'] : null,
                'is_post_cutoff_adjustment'  => $isPayrollLocked ? 1 : 0,
                'post_cutoff_action'         => $postCutoffAction,
                'post_cutoff_status'         => $postCutoffStatus,
                'post_cutoff_notes'          => $postCutoffNotes !== '' ? $postCutoffNotes : null,
                'created_by'                 => $actor->username,
                'updated_by'                 => $requestedMode === 'DIRECT_UPDATE' ? $actor->username : null,
                'created_at'                 => Carbon::now(),
                'updated_at'                 => Carbon::now(),
            ];

            $corrId = DB::table('attendance_corrections')->insertGetId($insertPayload);

            $leaveActionSummary = null;
            $afterSnapshot = null;
            $approvalRequestId = null;

            if ($requestedMode === 'DIRECT_UPDATE') {
                // 1. Batalkan cuti 1 hari & refund kuota jika diminta
                if ($cancelApprovedLeave && $approvedLeave) {
                    $refundRes = $this->cancelAndRefundSingleDayLeave(
                        (int) $approvedLeave['id'],
                        $actor,
                        "Dibatalkan otomatis karena perbaikan absen manual {$correctionNo}: {$reason}",
                        $ip
                    );
                    $leaveActionSummary = $refundRes['summary'];

                    DB::table('attendance_corrections')->where('id', $corrId)->update([
                        'cancelled_leave_id'  => $approvedLeave['id'],
                        'leave_refunded_days' => $refundRes['refunded_days'],
                    ]);
                }

                // 2. Perbarui / buat attendant_log
                $notePrefix = $isPayrollLocked
                    ? "Koreksi Manual Post-Cutoff [{$postCutoffAction}] ({$correctionNo} oleh {$actor->username})"
                    : "Koreksi Manual Direct ({$correctionNo} oleh {$actor->username})";
                $fullNote = $reason !== '' ? "{$notePrefix}: {$reason}" : $notePrefix;

                $afterSnapshot = $this->applyDirectLogUpdate(
                    $nik,
                    $date,
                    $corrType,
                    $corrIn,
                    $corrOut,
                    $fullNote,
                    $clearGpsAnomaly,
                    $actor->username
                );

                DB::table('attendance_corrections')->where('id', $corrId)->update([
                    'after_snapshot' => json_encode($afterSnapshot),
                    'updated_at'     => Carbon::now(),
                ]);

                $this->writeAuditLog([
                    'correction_id'      => $corrId,
                    'action'             => $sourceMode === 'BULK_UPLOAD' ? 'BULK_UPLOAD' : 'MANUAL_DIRECT',
                    'actor_username'     => $actor->username,
                    'target_nik'         => $nik,
                    'scan_date'          => $date,
                    'mode'               => 'DIRECT_UPDATE',
                    'before_data'        => $beforeSnapshot,
                    'after_data'         => $afterSnapshot,
                    'leave_action'       => $leaveActionSummary,
                    'post_cutoff_action' => $postCutoffAction,
                    'reason'             => $reason,
                    'ip_address'         => $ip,
                ]);

                return [
                    'status'              => 'success',
                    'mode'                => 'DIRECT_UPDATE',
                    'correction_id'       => $corrId,
                    'correction_no'       => $correctionNo,
                    'approval_request_id' => null,
                    'leave_action'        => $leaveActionSummary,
                    'post_cutoff_action'  => $postCutoffAction,
                    'message'             => "Absensi {$nik} tanggal {$date} berhasil diperbarui langsung ({$correctionNo}).",
                ];
            }

            // Mode APPROVAL_REQUIRED -> submit atas nama target employee!
            $typeLabel = $corrType === 'IN' ? 'Jam Masuk' : ($corrType === 'OUT' ? 'Jam Pulang' : 'Jam Masuk & Pulang');
            $context = [
                'Request_Type' => 'ATTENDANCE_CORRECTION',
                'Location'     => $targetUser->Location ?? 'HEAD_OFFICE',
                'Company_ID'   => (int) ($targetUser->Company_ID ?? 1),
                'Department'   => $targetUser->Department ?? 'ALL',
            ];

            try {
                $apprResult = $this->workflowService->submit([
                    'Request_No'  => $correctionNo,
                    'Description' => "Koreksi Absensi Manual ({$typeLabel}) tgl {$date} [Input oleh {$actor->username}]: {$reason}",
                ], $context, (int) $targetUser->id);

                $approvalRequestId = is_object($apprResult)
                    ? ($apprResult->ID ?? null)
                    : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
            } catch (\Throwable $e) {
                try {
                    $context['Location'] = null;
                    $context['Department'] = null;
                    $apprResult = $this->workflowService->submit([
                        'Request_No'  => $correctionNo,
                        'Description' => "Koreksi Absensi Manual ({$typeLabel}) tgl {$date} [Input oleh {$actor->username}]: {$reason}",
                    ], $context, (int) $targetUser->id);

                    $approvalRequestId = is_object($apprResult)
                        ? ($apprResult->ID ?? null)
                        : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
                } catch (\Throwable $ex) {
                    Log::warning("Manual Correction Approval fallback untuk {$correctionNo}: " . $ex->getMessage());
                }
            }

            if ($approvalRequestId) {
                DB::table('attendance_corrections')
                    ->where('id', $corrId)
                    ->update(['approval_request_id' => $approvalRequestId]);
            }

            $this->writeAuditLog([
                'correction_id'      => $corrId,
                'action'             => $sourceMode === 'BULK_UPLOAD' ? 'BULK_UPLOAD' : 'MANUAL_APPROVAL_SUBMIT',
                'actor_username'     => $actor->username,
                'target_nik'         => $nik,
                'scan_date'          => $date,
                'mode'               => 'APPROVAL_REQUIRED',
                'before_data'        => $beforeSnapshot,
                'after_data'         => ['corrected_clock_in' => $corrIn, 'corrected_clock_out' => $corrOut, 'approval_request_id' => $approvalRequestId],
                'leave_action'       => ($cancelApprovedLeave && $approvedLeave) ? "PENDING_CANCEL_LEAVE:{$approvedLeave['request_no']}" : null,
                'post_cutoff_action' => $postCutoffAction,
                'reason'             => $reason,
                'ip_address'         => $ip,
            ]);

            return [
                'status'              => 'success',
                'mode'                => 'APPROVAL_REQUIRED',
                'correction_id'       => $corrId,
                'correction_no'       => $correctionNo,
                'approval_request_id' => $approvalRequestId,
                'post_cutoff_action'  => $postCutoffAction,
                'message'             => "Pengajuan perbaikan absensi {$nik} tanggal {$date} berhasil dikirim ke alur Approval ({$correctionNo}).",
            ];
        });
    }

    /**
     * Terapkan perubahan langsung ke tabel attendant_log dan hitung ulang telat.
     */
    public function applyDirectLogUpdate(
        string $nik,
        string $date,
        string $corrType,
        ?string $corrIn,
        ?string $corrOut,
        string $notes,
        bool $clearGpsAnomaly = false,
        string $actorUsername = 'SYSTEM'
    ): array {
        $schedule = $this->attendanceService->getWorkScheduleForDate($nik, $date);
        $expectedStartStr = $schedule['default_start'] ?? '08:00:00';

        $existing = DB::table('attendant_log')
            ->where('NIK', $nik)
            ->where('Scan_Date', $date)
            ->first();

        $newStart = $existing ? $existing->Start_Time : null;
        $newEnd = $existing ? $existing->End_Time : null;

        if ($corrType !== 'OUT' && !empty($corrIn)) {
            $newStart = $corrIn;
        }
        if ($corrType !== 'IN' && !empty($corrOut)) {
            $newEnd = $corrOut;
        }

        $lateMinutes = 0;
        $clockInStatus = 'ON_TIME';
        if (!empty($newStart) && !empty($schedule['is_working'])) {
            $expectedCarbon = Carbon::parse($date . ' ' . $expectedStartStr);
            $actualCarbon = Carbon::parse($date . ' ' . $newStart);
            if ($actualCarbon->gt($expectedCarbon)) {
                $diff = $actualCarbon->diffInMinutes($expectedCarbon);
                $lateMinutes = $diff > 15 ? $diff : 0;
                $clockInStatus = $diff > 15 ? 'LATE' : 'ON_TIME';
            }
        }

        if ($existing) {
            $updateData = [
                'Start_Time'      => $newStart,
                'End_Time'        => $newEnd,
                'Clock_In_Status' => $clockInStatus,
                'Late_Minutes'    => $lateMinutes,
                'Notes'           => $notes,
            ];
            if ($clearGpsAnomaly) {
                $updateData['GPS_Anomaly_Flag'] = 'CLEAN';
                $updateData['GPS_Anomaly_Reason'] = null;
            }
            DB::table('attendant_log')->where('ID', $existing->ID)->update($updateData);
        } else {
            $periode = (int) date('Ym', strtotime($date));
            $main = AttendanceMain::firstOrCreate(
                ['NIK' => $nik, 'Periode' => $periode],
                [
                    'Task_ID'      => 'ATT-' . $nik . '-' . $periode . '-000001',
                    'Status'       => 'CREATED',
                    'Created_By'   => $actorUsername,
                    'Created_Date' => Carbon::now(),
                ]
            );

            DB::table('attendant_log')->insert([
                'Task_ID'          => $main->Task_ID,
                'NIK'              => $nik,
                'Periode'          => $periode,
                'Scan_Date'        => $date,
                'Start_Time'       => $newStart,
                'End_Time'         => $newEnd,
                'Clock_In_Status'  => $clockInStatus,
                'Late_Minutes'     => $lateMinutes,
                'GPS_Anomaly_Flag' => 'CLEAN',
                'Notes'            => $notes,
            ]);
        }

        return [
            'clock_in'        => $newStart,
            'clock_out'       => $newEnd,
            'clock_in_status' => $clockInStatus,
            'late_minutes'    => $lateMinutes,
            'notes'           => $notes,
        ];
    }

    /**
     * Batalkan cuti APPROVED 1 hari penuh & kembalikan kuota cuti ke user_leave_balances + leave_balance_ledgers.
     */
    public function cancelAndRefundSingleDayLeave(int $leaveId, $actor, string $reason = '', ?string $ip = null): array
    {
        return DB::transaction(function () use ($leaveId, $actor, $reason, $ip) {
            $leave = DB::table('attendance_leave_requests')
                ->where('id', $leaveId)
                ->lockForUpdate()
                ->first();

            if (!$leave) {
                throw new InvalidArgumentException('Data cuti yang akan dibatalkan tidak ditemukan.');
            }
            if (strtoupper($leave->status) !== 'APPROVED') {
                return [
                    'refunded_days' => 0,
                    'summary'       => "LEAVE_ALREADY_{$leave->status}:{$leave->request_no}",
                ];
            }
            if ($leave->start_date !== $leave->end_date) {
                throw new InvalidArgumentException("Cuti {$leave->request_no} lebih dari 1 hari dan tidak dapat dibatalkan otomatis.");
            }

            DB::table('attendance_leave_requests')
                ->where('id', $leave->id)
                ->update([
                    'status'     => 'CANCELLED',
                    'updated_by' => $actor->username ?? 'SYSTEM',
                    'updated_at' => Carbon::now(),
                ]);

            if (!empty($leave->approval_request_id)) {
                try {
                    $this->workflowService->cancel(
                        (int) $leave->approval_request_id,
                        (int) ($actor->id ?? 0),
                        $reason ?: 'Dibatalkan karena perbaikan absen manual'
                    );
                } catch (\Throwable $e) {
                    Log::warning("Approval cancel untuk cuti {$leave->request_no}: " . $e->getMessage());
                }
            }

            app(LeaveLegacySyncService::class)->syncApprovalStatusToLegacy(
                $leave->approval_request_id ? (int) $leave->approval_request_id : null,
                'CANCELLED',
                (int) $leave->id
            );

            // Refund kuota berdasarkan catatan DEDUCTION pada leave_balance_ledgers
            $refundedTotal = 0;
            $year = (int) date('Y', strtotime($leave->start_date));
            $balance = DB::table('user_leave_balances')
                ->where('nik', $leave->nik)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($balance && Schema::hasTable('leave_balance_ledgers')) {
                $deductions = DB::table('leave_balance_ledgers')
                    ->where('nik', $leave->nik)
                    ->where('reference_no', $leave->request_no)
                    ->where('transaction_type', 'DEDUCTION')
                    ->get();

                $refundCarry = 0;
                $refundCurrent = 0;
                foreach ($deductions as $ded) {
                    $absAmt = abs((int) $ded->amount);
                    if ($ded->source_balance === 'CARRY_FORWARD') {
                        $refundCarry += $absAmt;
                    } else {
                        $refundCurrent += $absAmt;
                    }
                }

                $refundedTotal = $refundCarry + $refundCurrent;

                if ($refundedTotal > 0) {
                    $newUsedCarry = max(0, (int) $balance->used_carry_forward - $refundCarry);
                    $newUsedCurrent = max(0, (int) $balance->used_current_year - $refundCurrent);
                    $newRemaining = (int) $balance->remaining_total + $refundedTotal;

                    DB::table('user_leave_balances')->where('id', $balance->id)->update([
                        'used_carry_forward' => $newUsedCarry,
                        'used_current_year'  => $newUsedCurrent,
                        'remaining_total'    => $newRemaining,
                        'updated_by'         => $actor->username ?? 'SYSTEM',
                        'updated_at'         => Carbon::now(),
                    ]);

                    if ($refundCarry > 0) {
                        DB::table('leave_balance_ledgers')->insert([
                            'nik'              => $leave->nik,
                            'reference_no'     => $leave->request_no,
                            'transaction_type' => 'ADJUSTMENT',
                            'amount'           => $refundCarry,
                            'source_balance'   => 'CARRY_FORWARD',
                            'balance_after'    => $newRemaining,
                            'description'      => "Refund kuota carry-forward akibat pembatalan {$leave->request_no} ({$reason})",
                            'created_by'       => $actor->username ?? 'SYSTEM',
                        ]);
                    }
                    if ($refundCurrent > 0) {
                        DB::table('leave_balance_ledgers')->insert([
                            'nik'              => $leave->nik,
                            'reference_no'     => $leave->request_no,
                            'transaction_type' => 'ADJUSTMENT',
                            'amount'           => $refundCurrent,
                            'source_balance'   => 'CURRENT_YEAR',
                            'balance_after'    => $newRemaining,
                            'description'      => "Refund kuota tahun berjalan akibat pembatalan {$leave->request_no} ({$reason})",
                            'created_by'       => $actor->username ?? 'SYSTEM',
                        ]);
                    }
                }
            }

            $summary = "CANCELLED:{$leave->request_no} (Refund {$refundedTotal} hari)";

            $this->writeAuditLog([
                'action'         => 'LEAVE_CANCEL_REFUND',
                'actor_username' => $actor->username ?? 'SYSTEM',
                'target_nik'     => $leave->nik,
                'scan_date'      => $leave->start_date,
                'leave_action'   => $summary,
                'reason'         => $reason,
                'ip_address'     => $ip,
            ]);

            return [
                'refunded_days' => $refundedTotal,
                'summary'       => $summary,
            ];
        });
    }

    /**
     * Buat file template Excel (.xlsx) untuk Bulk Upload Perbaikan Absen.
     */
    public function buildBulkTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Koreksi Absen');

        $headers = [
            'NIK',
            'Tanggal (YYYY-MM-DD)',
            'Tipe (BOTH/IN/OUT)',
            'Jam Masuk (HH:MM)',
            'Jam Pulang (HH:MM)',
            'Alasan Perbaikan',
            'Mode (DIRECT/APPROVAL)',
            'Batalkan Cuti 1 Hari (YA/TIDAK)',
            'Aksi Payroll Terkunci (NONE/RAPEL_NEXT_PERIOD/FINANCE_DIRECT)',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E8FB');

        // Contoh pengisian baris 2
        $sheet->fromArray([
            'EMP001',
            date('Y-m-d'),
            'BOTH',
            '08:00',
            '17:00',
            'Lupa clock-in/out karena dinas luar',
            'APPROVAL',
            'TIDAK',
            'NONE',
        ], null, 'A2');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Preview atau Eksekusi Bulk Upload Excel (.xlsx / .csv).
     */
    public function processBulkUpload($actor, $uploadedFile, string $actionMode = 'preview', ?string $ip = null): array
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk fitur Bulk Upload.');
        }

        $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) <= 1) {
            throw new InvalidArgumentException('File upload kosong atau tidak memiliki baris data (minimal mulai baris ke-2).');
        }

        $results = [];
        $validCount = 0;
        $errorCount = 0;
        $executedCount = 0;

        foreach ($rows as $idx => $row) {
            if ($idx === 0) {
                continue; // skip header
            }

            $rowNum = $idx + 1;
            $nik = trim((string) ($row[0] ?? ''));
            $dateRaw = trim((string) ($row[1] ?? ''));
            $corrType = strtoupper(trim((string) ($row[2] ?? 'BOTH')));
            $clockIn = trim((string) ($row[3] ?? ''));
            $clockOut = trim((string) ($row[4] ?? ''));
            $reason = trim((string) ($row[5] ?? ''));
            $modeRaw = strtoupper(trim((string) ($row[6] ?? 'APPROVAL')));
            $cancelLeaveRaw = strtoupper(trim((string) ($row[7] ?? 'TIDAK')));
            $postCutoffRaw = strtoupper(trim((string) ($row[8] ?? 'NONE')));

            if ($nik === '' && $dateRaw === '' && $reason === '') {
                continue; // skip empty trailing row
            }

            $execMode = ($modeRaw === 'DIRECT' || $modeRaw === 'DIRECT_UPDATE') ? 'DIRECT_UPDATE' : 'APPROVAL_REQUIRED';
            $cancelLeave = in_array($cancelLeaveRaw, ['YA', 'YES', '1', 'TRUE'], true);

            try {
                $parsedDate = Carbon::parse($dateRaw)->toDateString();
                $ctx = $this->inspectContext($nik, $parsedDate, $actor);

                $warnings = [];
                if ($ctx['has_pending_correction']) {
                    throw new InvalidArgumentException("Sudah ada pengajuan SUBMITTED ({$ctx['pending_correction']['correction_no']})");
                }
                if ($ctx['must_use_approval'] && $execMode === 'DIRECT_UPDATE') {
                    throw new InvalidArgumentException('Tidak boleh DIRECT_UPDATE untuk NIK sendiri');
                }
                if ($ctx['approved_leave']) {
                    if (!$cancelLeave) {
                        throw new InvalidArgumentException("Bentrok Cuti APPROVED ({$ctx['approved_leave']['request_no']}). Set kolom Batalkan Cuti = YA");
                    }
                    if (!$ctx['approved_leave']['is_single_day']) {
                        throw new InvalidArgumentException("Cuti {$ctx['approved_leave']['request_no']} multi-hari tidak dapat dibatalkan otomatis");
                    }
                    $warnings[] = "Akan membatalkan cuti {$ctx['approved_leave']['request_no']} & refund kuota";
                }
                if ($ctx['is_payroll_locked']) {
                    if (!in_array($postCutoffRaw, ['RAPEL_NEXT_PERIOD', 'FINANCE_DIRECT'], true)) {
                        throw new InvalidArgumentException('Payroll periode ini LOCKED. Pilih RAPEL_NEXT_PERIOD atau FINANCE_DIRECT');
                    }
                    $warnings[] = "Post-Cutoff: {$postCutoffRaw}";
                }

                $validCount++;
                $execResult = null;

                if ($actionMode === 'execute') {
                    $execResult = $this->submitManualCorrection($actor, [
                        'nik'                   => $nik,
                        'attendance_date'       => $parsedDate,
                        'correction_type'       => $corrType,
                        'corrected_clock_in'    => $clockIn,
                        'corrected_clock_out'   => $clockOut,
                        'reason'                => $reason,
                        'execution_mode'        => $execMode,
                        'cancel_approved_leave' => $cancelLeave,
                        'post_cutoff_action'    => $postCutoffRaw,
                    ], null, $ip, 'BULK_UPLOAD');
                    $executedCount++;
                }

                $results[] = [
                    'row'      => $rowNum,
                    'nik'      => $nik,
                    'name'     => $ctx['target_user']->Name ?? $nik,
                    'date'     => $parsedDate,
                    'type'     => $corrType,
                    'clock_in' => $clockIn,
                    'clock_out'=> $clockOut,
                    'mode'     => $execMode,
                    'status'   => $actionMode === 'execute' ? 'EXECUTED' : 'VALID',
                    'message'  => $execResult ? $execResult['message'] : (empty($warnings) ? 'Siap diproses' : implode('; ', $warnings)),
                ];
            } catch (\Throwable $e) {
                $errorCount++;
                $results[] = [
                    'row'      => $rowNum,
                    'nik'      => $nik,
                    'name'     => '-',
                    'date'     => $dateRaw,
                    'type'     => $corrType,
                    'clock_in' => $clockIn,
                    'clock_out'=> $clockOut,
                    'mode'     => $execMode,
                    'status'   => 'ERROR',
                    'message'  => $e->getMessage(),
                ];
            }
        }

        return [
            'action_mode'    => $actionMode,
            'total_rows'     => count($results),
            'valid_count'    => $validCount,
            'error_count'    => $errorCount,
            'executed_count' => $executedCount,
            'rows'           => $results,
        ];
    }

    /**
     * Ambil daftar pengajuan koreksi absensi berstatus SUBMITTED untuk diproses massal.
     */
    public function getPendingCorrectionRequests(array $filters = []): array
    {
        $q = DB::table('attendance_corrections as c')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'c.nik')
            ->where('c.status', 'SUBMITTED')
            ->select(
                'c.*',
                'u.Name as employee_name',
                'u.Department as department'
            )
            ->orderBy('c.attendance_date', 'desc')
            ->orderBy('c.id', 'desc');

        if (!empty($filters['department']) && $filters['department'] !== 'ALL') {
            $q->where('u.Department', $filters['department']);
        }

        return $q->limit(200)->get()->toArray();
    }

    /**
     * Setujui & terapkan banyak pengajuan koreksi SUBMITTED sekaligus (dengan audit log).
     */
    public function bulkApprovePendingRequests($actor, array $correctionIds, ?string $ip = null): array
    {
        if (!can_manual_correct_attendance($actor)) {
            throw new InvalidArgumentException('Anda tidak memiliki hak akses untuk memproses koreksi massal.');
        }

        $ids = array_values(array_unique(array_map('intval', $correctionIds)));
        if (empty($ids)) {
            throw new InvalidArgumentException('Pilih minimal 1 pengajuan koreksi untuk diproses.');
        }

        $approved = 0;
        $skipped = [];

        foreach ($ids as $id) {
            try {
                DB::transaction(function () use ($id, $actor, $ip, &$approved) {
                    $corr = DB::table('attendance_corrections')->where('id', $id)->lockForUpdate()->first();
                    if (!$corr || $corr->status !== 'SUBMITTED') {
                        throw new InvalidArgumentException('Status bukan SUBMITTED.');
                    }

                    // Cegah self-approval jika allow_self_direct_update = false
                    $settings = $this->getSettings();
                    if (!$settings->allow_self_direct_update && !empty($actor->Emp_ID) && strcasecmp($actor->Emp_ID, $corr->nik) === 0) {
                        throw new InvalidArgumentException('Tidak diizinkan menyetujui koreksi NIK sendiri secara langsung.');
                    }

                    // Jika ada approval_request_id aktif, sinkronkan statusnya
                    if (!empty($corr->approval_request_id) && Schema::hasTable('approval_request')) {
                        DB::table('approval_request')
                            ->where('ID', $corr->approval_request_id)
                            ->whereNotIn('Status', ['APPROVED', 'REJECTED', 'CANCELLED'])
                            ->update([
                                'Status'     => 'APPROVED',
                                'Updated_By' => $actor->username,
                                'Updated_At' => Carbon::now(),
                            ]);
                    }

                    $leaveAction = null;
                    if (!empty($corr->pending_cancel_leave_id)) {
                        $refundRes = $this->cancelAndRefundSingleDayLeave(
                            (int) $corr->pending_cancel_leave_id,
                            $actor,
                            "Pembatalan cuti terkait bulk approve {$corr->correction_no}",
                            $ip
                        );
                        $leaveAction = $refundRes['summary'];
                        DB::table('attendance_corrections')->where('id', $corr->id)->update([
                            'cancelled_leave_id'  => $corr->pending_cancel_leave_id,
                            'leave_refunded_days' => $refundRes['refunded_days'],
                        ]);
                    }

                    $note = "Bulk Approve ({$corr->correction_no} oleh {$actor->username}): {$corr->reason}";
                    $afterSnapshot = $this->applyDirectLogUpdate(
                        $corr->nik,
                        $corr->attendance_date,
                        strtoupper((string) ($corr->correction_type ?? 'BOTH')),
                        $corr->corrected_clock_in,
                        $corr->corrected_clock_out,
                        $note,
                        false,
                        $actor->username
                    );

                    DB::table('attendance_corrections')->where('id', $corr->id)->update([
                        'status'         => 'APPROVED',
                        'after_snapshot' => json_encode($afterSnapshot),
                        'updated_by'     => $actor->username,
                        'updated_at'     => Carbon::now(),
                    ]);

                    $this->writeAuditLog([
                        'correction_id'      => $corr->id,
                        'action'             => 'BULK_APPROVE',
                        'actor_username'     => $actor->username,
                        'target_nik'         => $corr->nik,
                        'scan_date'          => $corr->attendance_date,
                        'mode'               => 'BULK_APPROVE',
                        'before_data'        => $corr->before_snapshot ? json_decode($corr->before_snapshot, true) : null,
                        'after_data'         => $afterSnapshot,
                        'leave_action'       => $leaveAction,
                        'post_cutoff_action' => $corr->post_cutoff_action ?? 'NONE',
                        'reason'             => $corr->reason,
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
            'message'        => "Berhasil menyetujui {$approved} pengajuan koreksi." . (!empty($skipped) ? ' Dilewati: ' . implode(' | ', $skipped) : ''),
        ];
    }

    /**
     * Ambil daftar riwayat audit log perbaikan absen manual.
     */
    public function getAuditLogs(array $filters = []): array
    {
        if (!Schema::hasTable('attendance_manual_audit_logs')) {
            return [];
        }

        $q = DB::table('attendance_manual_audit_logs as a')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'a.target_nik')
            ->leftJoin('attendance_corrections as c', 'c.id', '=', 'a.correction_id')
            ->select(
                'a.*',
                'u.Name as target_name',
                'c.correction_no',
                'c.post_cutoff_status'
            )
            ->orderBy('a.id', 'desc');

        if (!empty($filters['nik'])) {
            $q->where('a.target_nik', trim($filters['nik']));
        }
        if (!empty($filters['action']) && $filters['action'] !== 'ALL') {
            $q->where('a.action', $filters['action']);
        }

        return $q->limit(150)->get()->toArray();
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

    protected function writeAuditLog(array $payload): void
    {
        if (!Schema::hasTable('attendance_manual_audit_logs')) {
            return;
        }

        DB::table('attendance_manual_audit_logs')->insert([
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
        ]);
    }
}
