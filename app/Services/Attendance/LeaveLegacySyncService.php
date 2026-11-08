<?php

namespace App\Services\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LeaveLegacySyncService
{
    /**
     * Sinkronisasikan pengajuan izin/cuti modern (attendance_leave_requests) ke tabel legacy (izin).
     * Memecah range tanggal cuti ke record harian izin (mengecualikan weekend & hari libur nasional).
     *
     * @param object|array $leaveReq Data pengajuan dari attendance_leave_requests
     * @param object $user User pengaju
     * @return int|null ID record pertama di tabel izin
     */
    public function syncToLegacy($leaveReq, $user): ?int
    {
        $req = is_array($leaveReq) ? (object) $leaveReq : $leaveReq;

        try {
            $nik = $req->nik ?? $user->Emp_ID;
            $leaveType = $req->leave_type ?? 'LEAVE';
            $startDate = $req->start_date ?? date('Y-m-d');
            $endDate = $req->end_date ?? $startDate;
            $reason = $req->reason ?? '';
            $attachment = $req->attachment_path ?? ($req->attachment ?? null);
            $clockIn = $req->clock_in_time ?? null;
            $clockOut = $req->clock_out_time ?? null;
            $approvalRequestId = $req->approval_request_id ?? null;
            $modernLeaveId = $req->id ?? ($req->leave_id ?? null);
            $requestNo = $req->request_no ?? ('IZIN/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999)));

            // Dapatkan detail employee
            $emp = DB::table('employment')->where('NIK', $nik)->first();
            $empName = $emp ? $emp->Name : ($user->Name ?? $nik);
            $empCategory = $emp ? ($emp->Emp_Status ?? ($emp->Department ?? 'STAFF')) : 'STAFF';
            $location = $user->Location ?? ($emp ? $emp->Location : 'HO');

            // Resolusi kategori izin (kat_izin_id dan izin_jenis_id)
            $category = DB::table('kategori_izin')
                ->where('kat_izin_code', $leaveType)
                ->orWhere('kat_izin_nama', $leaveType)
                ->first();

            $katIzinId = $category ? (int) $category->kat_izin_id : 1;
            $izinJenisId = $category ? (int) $category->izin_jenis_id : 1;

            // Siapkan informasi waktu jika ada jam parsial
            $infoWaktu = null;
            if ($clockIn && $clockOut) {
                $infoWaktu = substr($clockIn, 0, 5) . ' - ' . substr($clockOut, 0, 5);
            } elseif ($clockIn) {
                $infoWaktu = substr($clockIn, 0, 5);
            } elseif ($clockOut) {
                $infoWaktu = substr($clockOut, 0, 5);
            }

            // Ambil daftar hari libur
            $holidays = $this->getHolidayMap();

            $firstInsertedId = null;
            $current = strtotime($startDate);
            $last = strtotime($endDate);
            $step = '+1 day';

            // Status awal di tabel izin: 3 = Menunggu Approval, 1 = Approved
            $initialLegacyStatus = ($req->status ?? 'SUBMITTED') === 'APPROVED' ? 1 : 3;

            while ($current <= $last) {
                $tgl = date('Y-m-d', $current);
                $daynum = (int) date('N', $current); // 1 = Senin, 7 = Minggu

                // Skip hari libur nasional atau weekend (Sabtu & Minggu) jika rentang hari
                // Namun jika pengajuan satu hari di weekend/libur (misal piket kerja), tetap izinkan
                $isWeekend = ($daynum === 6 || $daynum === 7);
                $isHoliday = isset($holidays[$tgl]);

                $shouldInsert = true;
                if ($startDate !== $endDate && ($isWeekend || $isHoliday)) {
                    $shouldInsert = false;
                }

                if ($shouldInsert) {
                    $insertedId = DB::table('izin')->insertGetId([
                        'pegawai_id'          => (int) ($emp->ID ?? ($user->id ?? 0)),
                        'izin_urutan'         => 0,
                        'izin_tgl_pengajuan'  => date('Y-m-d'),
                        'izin_tgl'            => $tgl,
                        'izin_jenis_id'       => $izinJenisId,
                        'izin_catatan'        => $reason,
                        'izin_status'         => $initialLegacyStatus,
                        'cuti_n_id'           => 0,
                        'izin_ket_lain'       => $location,
                        'kat_izin_id'         => $katIzinId,
                        'Task_ID'             => $requestNo,
                        'Created_By'          => $user->username ?? 'portal',
                        'Informasi_Waktu'     => $infoWaktu,
                        'Location'            => $location,
                        'File_Path'           => $attachment,
                        'Employee_Name'       => $empName,
                        'Employee_Category'   => $empCategory,
                        'NIK'                 => $nik,
                        'Created_Date'        => Carbon::now(),
                        'modern_leave_id'     => $modernLeaveId,
                        'approval_request_id' => $approvalRequestId,
                    ]);

                    if (!$firstInsertedId) {
                        $firstInsertedId = $insertedId;
                    }
                }

                $current = strtotime($step, $current);
            }

            return $firstInsertedId;
        } catch (\Throwable $e) {
            Log::error('LeaveLegacySyncService error syncToLegacy: ' . $e->getMessage(), [
                'leaveReq' => $leaveReq,
                'user'     => $user->username ?? null,
            ]);
            return null;
        }
    }

    /**
     * Selaraskan status permohonan dari Dynamic Approval atau modern status ke tabel legacy izin.
     *
     * @param int|null $approvalRequestId
     * @param string $status TARGET status ('APPROVED', 'REJECTED', 'CANCELLED')
     * @param int|null $modernLeaveId
     * @return void
     */
    public function syncApprovalStatusToLegacy(?int $approvalRequestId, string $status, ?int $modernLeaveId = null): void
    {
        try {
            // Mapping status: APPROVED -> 1 (Approved), REJECTED/CANCELLED -> 2 (Rejected / Batal)
            $legacyStatus = 3;
            if ($status === 'APPROVED') {
                $legacyStatus = 1;
            } elseif (in_array($status, ['REJECTED', 'CANCELLED'])) {
                $legacyStatus = 2;
            }

            $query = DB::table('izin');
            $hasFilter = false;

            if ($approvalRequestId) {
                $query->where('approval_request_id', $approvalRequestId);
                $hasFilter = true;
            } elseif ($modernLeaveId) {
                $query->where('modern_leave_id', $modernLeaveId);
                $hasFilter = true;
            }

            if ($hasFilter) {
                $query->update([
                    'izin_status' => $legacyStatus,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('LeaveLegacySyncService error syncApprovalStatusToLegacy: ' . $e->getMessage(), [
                'approval_request_id' => $approvalRequestId,
                'status'              => $status,
                'modern_leave_id'     => $modernLeaveId,
            ]);
        }
    }

    /**
     * Dapatkan daftar tanggal libur nasional
     */
    public function getHolidayMap(): array
    {
        $currentYear = (int) date('Y');
        $years = [$currentYear - 1, $currentYear, $currentYear + 1];

        $holidays = [];
        $query = DB::table('libur')
            ->where(function ($q) use ($years) {
                foreach ($years as $y) {
                    $q->orWhere('libur_tgl', 'like', "{$y}%");
                }
            })
            ->pluck('libur_tgl');

        foreach ($query as $tgl) {
            $holidays[$tgl] = true;
        }

        return $holidays;
    }
}

