<?php

namespace App\Services\Attendance;

use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class ShiftExchangeService
{
    /**
     * @var ApprovalWorkflowService
     */
    protected $workflowService;

    /**
     * @var AttendanceService
     */
    protected $attendanceService;

    public function __construct(ApprovalWorkflowService $workflowService, AttendanceService $attendanceService)
    {
        $this->workflowService = $workflowService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Ambil informasi jadwal shift karyawan pada tanggal tertentu.
     *
     * @param string $nik
     * @param string $date
     * @return array
     */
    public function getScheduleDetail(string $nik, string $date): array
    {
        return $this->attendanceService->getWorkScheduleForDate($nik, $date);
    }

    /**
     * Submit pengajuan Change / Exchange Shift.
     *
     * @param object $user
     * @param array $data
     * @param UploadedFile|null $attachmentFile
     * @return array
     */
    public function submitChangeShiftRequest($user, array $data, $attachmentFile = null): array
    {
        $nik = $user->Emp_ID;
        $requestMode = strtoupper(trim($data['request_mode'] ?? 'CHANGE'));
        if (!in_array($requestMode, ['CHANGE', 'EXCHANGE'])) {
            $requestMode = 'CHANGE';
        }

        $originalDate = trim($data['original_date'] ?? '');
        $targetShiftId = (int) ($data['target_shift_id'] ?? 0);
        $reason = trim($data['reason'] ?? '');

        if (empty($originalDate)) {
            throw new InvalidArgumentException('Tanggal dinas yang ingin diubah wajib diisi.');
        }

        if (empty($targetShiftId)) {
            throw new InvalidArgumentException('Shift tujuan pengganti wajib dipilih.');
        }

        if (empty($reason)) {
            throw new InvalidArgumentException('Alasan perubahan / tukar shift wajib diisi.');
        }

        // Cek shift asal pemohon
        $userCurrentSched = $this->attendanceService->getWorkScheduleForDate($nik, $originalDate);
        $currentShiftId = $userCurrentSched['shift_id'] ?? null;

        $targetDate = !empty($data['target_date']) ? trim($data['target_date']) : $originalDate;
        $peerNik = null;
        $peerCurrentShiftId = null;
        $peerTargetShiftId = null;

        if ($requestMode === 'EXCHANGE') {
            $peerNik = trim($data['peer_nik'] ?? '');
            if (empty($peerNik)) {
                throw new InvalidArgumentException('Rekan kerja untuk tukar shift wajib dipilih.');
            }
            if ($peerNik === $nik) {
                throw new InvalidArgumentException('Tidak dapat memilih diri sendiri sebagai rekan pertukaran shift.');
            }

            // Ambil jadwal shift rekan pada target_date
            $peerSched = $this->attendanceService->getWorkScheduleForDate($peerNik, $targetDate);
            $peerCurrentShiftId = $peerSched['shift_id'] ?? null;
            $peerTargetShiftId = (int) ($data['peer_target_shift_id'] ?? $currentShiftId);
        }

        // Cek duplikasi request yang masih pending
        $existing = DB::table('attendance_change_shifts')
            ->where('nik', $nik)
            ->where('original_date', $originalDate)
            ->whereIn('status', ['SUBMITTED', 'WAITING_PEER', 'IN_PROCESS'])
            ->first();

        if ($existing) {
            throw new InvalidArgumentException("Pengajuan perubahan shift untuk tanggal {$originalDate} sudah ada dan sedang diproses ({$existing->change_shift_no}).");
        }

        // Simpan attachment jika ada
        $attachmentPath = null;
        if ($attachmentFile instanceof UploadedFile) {
            $folder = 'uploads/attendance/change_shift/' . date('Ym');
            $filename = 'CS_' . $nik . '_' . time() . '.' . $attachmentFile->getClientOriginalExtension();
            $attachmentFile->move(public_path($folder), $filename);
            $attachmentPath = $folder . '/' . $filename;
        }

        $changeShiftNo = 'CS/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999));

        return DB::transaction(function () use (
            $user, $nik, $requestMode, $originalDate, $currentShiftId, $targetShiftId,
            $targetDate, $peerNik, $peerCurrentShiftId, $peerTargetShiftId,
            $reason, $attachmentPath, $changeShiftNo
        ) {
            $insertId = DB::table('attendance_change_shifts')->insertGetId([
                'change_shift_no'       => $changeShiftNo,
                'nik'                   => $nik,
                'request_mode'          => $requestMode,
                'original_date'         => $originalDate,
                'current_shift_id'      => $currentShiftId,
                'target_shift_id'       => $targetShiftId,
                'target_date'           => $targetDate,
                'peer_nik'              => $peerNik,
                'peer_current_shift_id' => $peerCurrentShiftId,
                'peer_target_shift_id'  => $peerTargetShiftId,
                'reason'                => $reason,
                'attachment'            => $attachmentPath,
                'status'                => 'SUBMITTED',
                'created_by'            => $user->username,
                'created_at'            => Carbon::now(),
                'updated_at'            => Carbon::now(),
            ]);

            // Submit ke Dynamic Approval Engine
            $approvalRequestId = null;
            $context = [
                'Request_Type' => 'ATTENDANCE_CHANGE_SHIFT',
                'Location'     => $user->Location ?? 'HEAD_OFFICE',
                'Company_ID'   => (int) ($user->Company_ID ?? 1),
                'Department'   => $user->Department ?? 'ALL',
            ];

            try {
                $apprResult = $this->workflowService->submit([
                    'Request_No'  => $changeShiftNo,
                    'Description' => "Pengajuan Perubahan Shift [{$requestMode}] Tanggal {$originalDate}: {$reason}",
                ], $context, (int) $user->id);

                $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
            } catch (\Throwable $e) {
                // Fallback dengan wildcard jika policy spesifik belum ada
                try {
                    $context['Location'] = null;
                    $context['Department'] = null;
                    $apprResult = $this->workflowService->submit([
                        'Request_No'  => $changeShiftNo,
                        'Description' => "Pengajuan Perubahan Shift [{$requestMode}] Tanggal {$originalDate}: {$reason}",
                    ], $context, (int) $user->id);

                    $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
                } catch (\Throwable $ex) {
                    Log::warning("Approval Engine fallback untuk {$changeShiftNo}: " . $ex->getMessage());
                }
            }

            if ($approvalRequestId) {
                DB::table('attendance_change_shifts')
                    ->where('id', $insertId)
                    ->update(['approval_request_id' => $approvalRequestId]);
            }

            return [
                'id'                  => $insertId,
                'change_shift_no'     => $changeShiftNo,
                'approval_request_id' => $approvalRequestId,
                'message'             => 'Pengajuan perubahan shift berhasil dikirimkan.',
            ];
        });
    }

    /**
     * Terapkan hasil perubahan shift ke employee_schedules saat disetujui (APPROVED).
     *
     * @param int $approvalRequestId
     * @return void
     */
    public function applyApprovedChangeShift(int $approvalRequestId): void
    {
        $changeShift = DB::table('attendance_change_shifts')
            ->where('approval_request_id', $approvalRequestId)
            ->first();

        if (!$changeShift) {
            return;
        }

        DB::transaction(function () use ($changeShift) {
            // Update status pengajuan
            DB::table('attendance_change_shifts')
                ->where('id', $changeShift->id)
                ->update([
                    'status'      => 'APPROVED',
                    'approved_at' => Carbon::now(),
                    'approved_by' => 'SYSTEM_APPROVAL',
                    'updated_at'  => Carbon::now(),
                ]);

            if (Schema::hasTable('employee_schedules')) {
                // 1. Terapkan jadwal shift baru untuk pemohon
                DB::table('employee_schedules')->updateOrInsert(
                    [
                        'user_id'   => $changeShift->nik,
                        'work_date' => $changeShift->original_date,
                    ],
                    [
                        'shift_id'    => $changeShift->target_shift_id,
                        'source_type' => 'MANUAL_OVERRIDE',
                        'notes'       => "Change Shift No: {$changeShift->change_shift_no}",
                        'updated_by'  => 'APPROVAL_ENGINE',
                        'updated_at'  => Carbon::now(),
                    ]
                );

                // 2. Jika mode EXCHANGE (tukar dengan rekan), update juga jadwal shift rekan
                if ($changeShift->request_mode === 'EXCHANGE' && !empty($changeShift->peer_nik) && !empty($changeShift->peer_target_shift_id)) {
                    $peerDate = $changeShift->target_date ?: $changeShift->original_date;
                    DB::table('employee_schedules')->updateOrInsert(
                        [
                            'user_id'   => $changeShift->peer_nik,
                            'work_date' => $peerDate,
                        ],
                        [
                            'shift_id'    => $changeShift->peer_target_shift_id,
                            'source_type' => 'MANUAL_OVERRIDE',
                            'notes'       => "Shift Exchange No: {$changeShift->change_shift_no} (with {$changeShift->nik})",
                            'updated_by'  => 'APPROVAL_ENGINE',
                            'updated_at'  => Carbon::now(),
                        ]
                    );
                }
            }
        });
    }
}

