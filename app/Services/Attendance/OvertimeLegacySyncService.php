<?php

namespace App\Services\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OvertimeLegacySyncService
{
    /**
     * Sinkronisasikan pengajuan lembur modern ke tabel legacy overtime_lists.
     * Ini menjamin proses payroll / gaji yang masih membaca overtime_lists tetap berjalan mulus.
     *
     * @param object|array $request Data pengajuan dari attendance_overtime_requests
     * @param object $user User pengaju
     * @return int|null ID record di overtime_lists
     */
    public function syncToLegacy($request, $user): ?int
    {
        $req = is_array($request) ? (object) $request : $request;

        try {
            $nik = $req->nik ?? $user->Emp_ID;
            $name = $user->Name ?? $nik;
            $overtimeDate = $req->overtime_date ?? Carbon::now()->toDateString();
            $startTime = $req->start_time;
            $endTime = $req->end_time;
            $durationMinutes = (int) ($req->duration_minutes ?? 0);
            $totalHours = round($durationMinutes / 60, 2);
            $calcAmount = (float) ($req->calculated_amount ?? 0);
            $hardcopyFile = $req->hardcopy_file ?? null;
            $taskDesc = $req->task_description ?? ($req->reference_note ?? '');
            $dayType = $req->day_type ?? 'WORKDAY';
            $overtimeType = $req->overtime_type ?? 'AFTER_SHIFT';

            // Dapatkan informasi Salary_Group dari employment
            $emp = DB::table('employment')->where('NIK', $nik)->first();
            $salaryGroup = $emp ? ($emp->Salary_Group ?? '') : '';

            // Tentukan slot jam pra-shift atau pasca-shift
            $earlyIn = '00:00:00';
            $earlyOut = '00:00:00';
            $afterIn = '00:00:00';
            $afterOut = '00:00:00';

            if ($overtimeType === 'BEFORE_SHIFT') {
                $earlyIn = $startTime;
                $earlyOut = $endTime;
            } else {
                $afterIn = $startTime;
                $afterOut = $endTime;
            }

            $isHoliday = ($dayType === 'HOLIDAY') ? 1 : 0;
            // Di legacy, Submit_Approval = 1 berarti Submitted (muncul di list)
            $submitApproval = 1;

            $taskId = $req->overtime_no ?? ('OV/' . ($user->Location ?? 'HO') . '/' . date('ymd') . '/' . sprintf('%04d', mt_rand(1, 9999)));

            $legacyId = DB::table('overtime_lists')->insertGetId([
                'Task_ID'         => $taskId,
                'Start_Date'      => $overtimeDate,
                'End_Date'        => $overtimeDate,
                'NIK'             => $nik,
                'Name'            => $name,
                'Early_IN'        => $earlyIn,
                'Early_OUT'       => $earlyOut,
                'After_IN'        => $afterIn,
                'After_OUT'       => $afterOut,
                'Holiday'         => $isHoliday,
                'Total_OT'        => $totalHours,
                'Calculated_OT'   => $totalHours,
                'IS_Outdated'     => 0,
                'Salary_ID'       => null,
                'Salary_Period'   => null,
                'Amounts'         => $calcAmount,
                'Reference'       => $req->overtime_no ?? '',
                'Created_Date'        => Carbon::now(),
                'Created_By'          => $user->username ?? 'portal',
                'File_Path'           => $hardcopyFile,
                'Submit_Approval'     => $submitApproval,
                'approval_request_id' => $req->approval_request_id ?? null,
                'Level1'              => 0,
                'Level2'              => 0,
                'Level3'              => 0,
                'Level4'              => 0,
                'Note'                => $taskDesc,
                'Salary_Group'        => $salaryGroup,
            ]);

            return $legacyId;
        } catch (\Throwable $e) {
            Log::error('OvertimeLegacySyncService Error syncToLegacy: ' . $e->getMessage(), [
                'request' => $request,
                'user'    => $user->username ?? null,
            ]);
            return null;
        }
    }

    /**
     * Update status di legacy ketika di-approve atau di-reject dari alur approval modern
     */
    public function updateLegacyStatusByOvertimeNo(string $overtimeNo, string $status): void
    {
        try {
            // Jika APPROVED, status legacy dipertahankan Submit_Approval = 1 (layak hitung gaji)
            // Jika REJECTED, kita update Submit_Approval = 0 agar tidak dihitung di payroll
            $submitApproval = ($status === 'APPROVED') ? 1 : 0;

            DB::table('overtime_lists')
                ->where('Reference', $overtimeNo)
                ->orWhere('Task_ID', $overtimeNo)
                ->update([
                    'Submit_Approval' => $submitApproval,
                ]);
        } catch (\Throwable $e) {
            Log::warning('OvertimeLegacySyncService Error updateLegacyStatus: ' . $e->getMessage());
        }
    }
}

