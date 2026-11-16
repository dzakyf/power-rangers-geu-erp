<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CandidatePortalService
{
    /**
     * Check if user is an administrator allowed to preview/control candidate portal.
     */
    public function isAdminUser($user): bool
    {
        if (!$user) {
            return false;
        }

        if (strtolower((string) ($user->username ?? '')) === 'admin') {
            return true;
        }

        if (strtoupper((string) ($user->Priv_ID ?? '')) === 'ADM') {
            return true;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('Admin')) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can access candidate portal (CANDIDATE or Admin).
     */
    public function canAccessPortal($user): bool
    {
        if (!$user) {
            return false;
        }

        return strtoupper((string) ($user->Position ?? '')) === 'CANDIDATE' || $this->isAdminUser($user);
    }

    /**
     * Resolve active Task_ID for the current request.
     * Admins may pass ?task_id=... to inspect/control a specific candidate.
     */
    public function resolveTaskId(Request $request, $user): string
    {
        if ($this->isAdminUser($user)) {
            $requested = trim((string) $request->input('task_id', ''));
            if ($requested !== '') {
                session(['candidate_preview_task_id' => $requested]);
                return $requested;
            }
            $sessionTaskId = trim((string) session('candidate_preview_task_id', ''));
            if ($sessionTaskId !== '') {
                return $sessionTaskId;
            }
        }

        return (string) ($user->username ?? '');
    }

    /**
     * Build active/upcoming schedule notifications for candidate.
     */
    public function getUpcomingSchedules($rec): array
    {
        $notifications = [];
        if (!$rec) {
            return $notifications;
        }

        $interviewDate = $rec->Interview_Schedule ?: $rec->Interview_Date;
        if ($interviewDate && $rec->Interview_Status !== 'ACCEPT' && $rec->Interview_Status !== 'REJECT') {
            $notifications[] = [
                'type'     => 'interview_hr',
                'title'    => 'Jadwal Interview HR',
                'schedule' => $interviewDate,
                'location' => $rec->Location ?? 'Kantor HCGA / Online Meeting',
                'note'     => $rec->Interview_Note ?? 'Harap hadir tepat waktu sesuai jadwal yang telah ditentukan.',
                'badge'    => 'Interview HR',
                'icon'     => 'fa-calendar-check',
                'color'    => 'primary',
            ];
        }

        if ($rec->User_Interview_Date && $rec->User_Interview_Status !== 'ACCEPT' && $rec->User_Interview_Status !== 'REJECT') {
            $notifications[] = [
                'type'     => 'interview_user',
                'title'    => 'Jadwal Interview User',
                'schedule' => $rec->User_Interview_Date,
                'location' => $rec->Location ?? 'Kantor / Divisi Terkait',
                'note'     => $rec->User_Interview_Note ?? 'Wawancara dengan User / Department Head.',
                'badge'    => 'Interview User',
                'icon'     => 'fa-users',
                'color'    => 'info',
            ];
        }

        if ($rec->Psikotest_Date && $rec->Psikotest_Status !== 'ACCEPT' && $rec->Psikotest_Status !== 'REJECT') {
            $notifications[] = [
                'type'     => 'psikotest',
                'title'    => 'Jadwal Psikotest',
                'schedule' => $rec->Psikotest_Date,
                'location' => $rec->Location ?? 'Online / Ruang Asesmen HCGA',
                'note'     => $rec->Psikotest_Note ?? 'Silakan persiapkan diri untuk pelaksanaan asesmen psikotes.',
                'badge'    => 'Psikotest',
                'icon'     => 'fa-edit',
                'color'    => 'warning',
            ];
        }

        if ($rec->Technical_Date && $rec->Technical_Test_Status !== 'ACCEPT' && $rec->Technical_Test_Status !== 'REJECT') {
            $notifications[] = [
                'type'     => 'technical_test',
                'title'    => 'Jadwal Tes Teknik',
                'schedule' => $rec->Technical_Date,
                'location' => $rec->Location ?? 'Divisi Terkait',
                'note'     => $rec->Technical_Test_Note ?? 'Uji kompetensi teknis.',
                'badge'    => 'Tes Teknik',
                'icon'     => 'fa-cogs',
                'color'    => 'info',
            ];
        }

        if ($rec->MCU_Schedule && $rec->MCU_Status !== 'ACCEPT' && $rec->MCU_Status !== 'REJECT') {
            $notifications[] = [
                'type'     => 'mcu',
                'title'    => 'Jadwal Medical Check-Up (MCU)',
                'schedule' => $rec->MCU_Schedule,
                'location' => $rec->MCU_Location ?? 'Klinik / Laboratorium Rekanan',
                'note'     => $rec->MCU_Note ?? 'Harap puasa minimal 10-12 jam sebelum pelaksanaan MCU jika diinstruksikan.',
                'badge'    => 'Medical Check-Up',
                'icon'     => 'fa-stethoscope',
                'color'    => 'danger',
            ];
        }

        if ($rec->Status === 'OFFERING' || $rec->Offering_Status === 'PENDING') {
            $notifications[] = [
                'type'     => 'offering',
                'title'    => 'Penawaran Kerja (Offering Letter)',
                'schedule' => $rec->Start_Date,
                'location' => 'Portal Candidate / Kantor HCGA',
                'note'     => 'Silakan download Offering Letter, bubuhkan materai & tanda tangan, lalu unggah kembali melalui menu Progress.',
                'badge'    => 'Offering',
                'icon'     => 'fa-file-text',
                'color'    => 'success',
            ];
        }

        return $notifications;
    }

    /**
     * Build 8-step recruitment progress array.
     */
    public function buildSteps($rec): array
    {
        $currentStatus = $rec->Status ?? 'CREATED';
        $isDocDone = ($rec->Document_Status === 'ACCEPT');
        $isPsikoDone = ($rec->Psikotest_Status === 'ACCEPT');
        $isHrDone = ($rec->Interview_Status === 'ACCEPT');
        $isUserDone = ($rec->User_Interview_Status === 'ACCEPT');
        $isTechDone = ($rec->Technical_Test_Status === 'ACCEPT');
        $isMcuDone = ($rec->MCU_Status === 'ACCEPT');
        $isOfferingDone = ($rec->Offering_Status === 'ACCEPT' || in_array($currentStatus, ['HIRED', 'CLOSED']));

        return [
            [
                'no'      => 1,
                'label'   => 'Input Candidate',
                'done'    => true,
                'current' => ($currentStatus === 'CREATED' || $currentStatus === 'UPLOADED') && !$isDocDone,
                'date'    => $rec->Created_Date,
            ],
            [
                'no'      => 2,
                'label'   => 'Verifikasi Dokumen',
                'done'    => $isDocDone,
                'current' => in_array($currentStatus, ['UPLOADED', 'PENDING']) && !$isDocDone,
                'date'    => $rec->Document_Date,
            ],
            [
                'no'      => 3,
                'label'   => 'Psikotest',
                'done'    => $isPsikoDone,
                'current' => ($currentStatus === 'PSIKOTEST' && !$isPsikoDone),
                'date'    => $rec->Psikotest_Date,
            ],
            [
                'no'      => 4,
                'label'   => 'Interview HR',
                'done'    => $isHrDone,
                'current' => ($currentStatus === 'INTERVIEW_HR' && !$isHrDone),
                'date'    => $rec->Interview_Schedule ?: $rec->Interview_Date,
            ],
            [
                'no'      => 5,
                'label'   => 'Interview User',
                'done'    => $isUserDone,
                'current' => ($currentStatus === 'INTERVIEW_USER' && !$isUserDone),
                'date'    => $rec->User_Interview_Date,
            ],
            [
                'no'      => 6,
                'label'   => 'Tes Teknik',
                'done'    => $isTechDone,
                'current' => ($currentStatus === 'TECHNICAL_TEST' && !$isTechDone),
                'date'    => $rec->Technical_Date,
            ],
            [
                'no'      => 7,
                'label'   => 'MCU',
                'done'    => $isMcuDone,
                'current' => ($currentStatus === 'MCU' && !$isMcuDone),
                'date'    => $rec->MCU_Schedule,
            ],
            [
                'no'      => 8,
                'label'   => 'Offering Letter',
                'done'    => $isOfferingDone,
                'current' => ($currentStatus === 'OFFERING' && !$isOfferingDone),
                'date'    => $rec->Start_Date,
            ],
        ];
    }

    /**
     * Load all profile sub-tables for candidate.
     */
    public function getProfileBundle(string $taskId): array
    {
        $fetch = function ($table) use ($taskId) {
            if (!Schema::hasTable($table)) {
                return collect([]);
            }
            return DB::table($table)->where('Task_ID', $taskId)->get();
        };

        return [
            'academics'    => $fetch('recruitment_akademic'),
            'trainings'    => $fetch('recruitment_informal'),
            'experiences'  => $fetch('recruitment_experiences'),
            'families'     => $fetch('recruitment_family'),
            'healths'      => $fetch('recruitment_health'),
            'drivings'     => $fetch('recruitment_driving'),
            'contacts'     => $fetch('recruitment_contact_point'),
            'insurances'   => $fetch('recruitment_insurances'),
        ];
    }

    /**
     * Replace candidate sub-table rows safely within a transaction, scoped to Task_ID.
     */
    public function replaceSubTableRows(string $table, string $taskId, array $rows, array $allowedColumns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $tableCols = Schema::getColumnListing($table);
        $validCols = array_values(array_intersect($allowedColumns, $tableCols));
        $hasCreatedAt = in_array('created_at', $tableCols, true);

        DB::transaction(function () use ($table, $taskId, $rows, $validCols, $hasCreatedAt) {
            DB::table($table)->where('Task_ID', $taskId)->delete();

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $payload = [];
                $hasValue = false;
                foreach ($validCols as $col) {
                    $val = isset($row[$col]) ? trim((string) $row[$col]) : '';
                    if ($val !== '') {
                        $hasValue = true;
                    }
                    $payload[$col] = ($val !== '') ? $val : null;
                }

                if (!$hasValue) {
                    continue;
                }

                $payload['Task_ID'] = $taskId;
                if ($hasCreatedAt) {
                    $payload['created_at'] = now();
                }

                DB::table($table)->insert($payload);
            }
        });
    }

    /**
     * Save non-sensitive confirmation / availability / signed offering file.
     */
    public function saveConfirmation(string $taskId, array $data): bool
    {
        if (!Schema::hasTable('recruitment_candidate_confirmations')) {
            return false;
        }

        DB::table('recruitment_candidate_confirmations')->insert([
            'task_id'              => $taskId,
            'stage_type'           => $data['stage_type'],
            'confirmation_status'  => $data['confirmation_status'],
            'preferred_date'       => $data['preferred_date'] ?? null,
            'notice_period'        => $data['notice_period'] ?? null,
            'candidate_note'       => $data['candidate_note'] ?? null,
            'signed_offering_file' => $data['signed_offering_file'] ?? null,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return true;
    }

    /**
     * Get candidate confirmation history.
     */
    public function getConfirmations(string $taskId)
    {
        if (!Schema::hasTable('recruitment_candidate_confirmations')) {
            return collect([]);
        }

        return DB::table('recruitment_candidate_confirmations')
            ->where('task_id', $taskId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Find latest signed offering file path from confirmations or disk.
     */
    public function getSignedOfferingPath(string $taskId): ?string
    {
        if (Schema::hasTable('recruitment_candidate_confirmations')) {
            $latest = DB::table('recruitment_candidate_confirmations')
                ->where('task_id', $taskId)
                ->whereNotNull('signed_offering_file')
                ->orderBy('id', 'desc')
                ->first();
            if ($latest && !empty($latest->signed_offering_file) && file_exists(public_path($latest->signed_offering_file))) {
                return $latest->signed_offering_file;
            }
        }

        foreach (['pdf', 'jpg', 'jpeg', 'png'] as $ext) {
            $rel = 'uploads/recruitment/' . $taskId . '/offering_signed.' . $ext;
            if (file_exists(public_path($rel))) {
                return $rel;
            }
        }

        return null;
    }
}
