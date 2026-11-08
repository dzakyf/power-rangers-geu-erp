<?php

namespace App\Services\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CollectiveLeaveBatchService
{
    protected $leaveEntitlementService;
    protected $legacySyncService;

    public function __construct(
        LeaveEntitlementService $leaveEntitlementService,
        LeaveLegacySyncService $legacySyncService
    ) {
        $this->leaveEntitlementService = $leaveEntitlementService;
        $this->legacySyncService = $legacySyncService;
    }

    /**
     * Terapkan Batch Cuti Bersama yang diinisiasi oleh Team HCGA.
     * Menginsert data ke libur/master_holiday, generate attendance_leave_requests (APPROVED),
     * sinkron ke tabel legacy izin, dan otomatis memotong saldo cuti tahunan (jika diaktifkan).
     *
     * @param array $payload
     * @param object $adminUser
     * @return array
     */
    public function applyCollectiveLeave(array $payload, $adminUser): array
    {
        $title = trim($payload['title'] ?? '');
        $startDate = $payload['start_date'] ?? null;
        $endDate = $payload['end_date'] ?? $startDate;
        $isDeduct = strtoupper(trim($payload['is_deduct_annual_leave'] ?? 'YES')) === 'YES' ? 'YES' : 'NO';
        $scope = strtoupper(trim($payload['target_scope'] ?? 'ALL')); // ALL, DEPARTMENT, LOCATION
        $dept = $payload['department'] ?? null;
        $loc = $payload['location'] ?? null;
        $description = $payload['description'] ?? "Cuti Bersama: {$title}";

        if (empty($title) || empty($startDate)) {
            throw new InvalidArgumentException("Judul Cuti Bersama dan Tanggal Mulai wajib diisi.");
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        if ($end->lt($start)) {
            throw new InvalidArgumentException("Tanggal akhir tidak boleh sebelum tanggal mulai.");
        }

        $totalDays = $end->diffInDays($start) + 1;
        $batchNo = 'CB/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999));

        // Query karyawan aktif sesuai scope target
        $empQuery = DB::table('employment')->where('Status', 'ACTIVE');
        if ($scope === 'DEPARTMENT' && !empty($dept) && $dept !== 'ALL') {
            $empQuery->where('Department', $dept);
        } elseif ($scope === 'LOCATION' && !empty($loc) && $loc !== 'ALL') {
            $empQuery->where('Location', $loc);
        }

        $employees = $empQuery->get();
        if ($employees->isEmpty()) {
            throw new InvalidArgumentException("Tidak ditemukan karyawan aktif pada scope target yang dipilih.");
        }

        return DB::transaction(function () use (
            $batchNo, $title, $startDate, $endDate, $totalDays, $isDeduct,
            $scope, $dept, $loc, $description, $employees, $adminUser, $start
        ) {
            // 1. Simpan Header Batch Cuti Bersama
            $batchId = DB::table('attendance_collective_leaves')->insertGetId([
                'batch_no'                 => $batchNo,
                'title'                    => $title,
                'start_date'               => $startDate,
                'end_date'                 => $endDate,
                'total_days'               => $totalDays,
                'is_deduct_annual_leave'   => $isDeduct,
                'target_scope'             => $scope,
                'department'               => $dept,
                'location'                 => $loc,
                'total_employees_affected' => $employees->count(),
                'description'              => $description,
                'status'                   => 'APPLIED',
                'created_by'               => $adminUser->username ?? 'admin.hcga',
                'created_at'               => Carbon::now(),
                'updated_at'               => Carbon::now(),
            ]);

            // 2. Daftarkan hari cuti bersama ke tabel hari libur (libur & master_holiday)
            // agar kalender absensi mengenali hari tersebut
            $cur = clone $start;
            $endClone = Carbon::parse($endDate);
            while ($cur->lte($endClone)) {
                $tglStr = $cur->toDateString();
                
                // Sinkron ke legacy table libur jika belum ada
                $existsLibur = DB::table('libur')->where('libur_tgl', $tglStr)->first();
                if (!$existsLibur) {
                    DB::table('libur')->insert([
                        'libur_tgl'        => $tglStr,
                        'libur_keterangan' => $title,
                    ]);
                }

                // Sinkron ke master_holiday jika belum ada
                $existsMasterHol = DB::table('master_holiday')->where('Holiday_Date', $tglStr)->first();
                if (!$existsMasterHol) {
                    DB::table('master_holiday')->insert([
                        'Year'         => (int) $cur->year,
                        'Day_Name'     => $cur->format('l'),
                        'Holiday_Date' => $tglStr,
                        'Is_Holiday'   => 'YES',
                        'Description'  => $title,
                        'Created_By'   => $adminUser->username ?? 'admin.hcga',
                        'created_at'   => Carbon::now(),
                        'updated_at'   => Carbon::now(),
                    ]);
                }

                $cur->addDay();
            }

            // 3. Batch insert ke attendance_leave_requests & izin per karyawan
            $processedCount = 0;
            $year = (int) $start->year;

            foreach ($employees as $emp) {
                $reqNo = 'LV-CB/' . date('Ymd') . '/' . $emp->NIK;

                // Hindari duplikasi jika sudah ada pengajuan cuti bersama yang sama
                $existing = DB::table('attendance_leave_requests')
                    ->where('nik', $emp->NIK)
                    ->where('collective_leave_id', $batchId)
                    ->first();

                if ($existing) continue;

                $leaveId = DB::table('attendance_leave_requests')->insertGetId([
                    'request_no'          => $reqNo,
                    'nik'                 => $emp->NIK,
                    'leave_type'          => 'CUTI_BERSAMA',
                    'start_date'          => $startDate,
                    'end_date'            => $endDate,
                    'total_days'          => $totalDays,
                    'reason'              => $description,
                    'status'              => 'APPROVED',
                    'collective_leave_id' => $batchId,
                    'created_by'          => $adminUser->username ?? 'admin.hcga',
                    'created_at'          => Carbon::now(),
                    'updated_at'          => Carbon::now(),
                ]);

                // Dual-write ke legacy table `izin`
                $userObj = (object) [
                    'id'         => $emp->ID ?? 0,
                    'Emp_ID'     => $emp->NIK,
                    'username'   => $emp->NIK,
                    'Name'       => $emp->Name ?? $emp->NIK,
                    'Location'   => $emp->Location ?? 'HO',
                    'Company_ID' => 1,
                    'Department' => $emp->Department ?? 'ALL',
                ];

                $legacyIzinId = $this->legacySyncService->syncToLegacy([
                    'id'                  => $leaveId,
                    'request_no'          => $reqNo,
                    'nik'                 => $emp->NIK,
                    'leave_type'          => 'CUTI_BERSAMA',
                    'start_date'          => $startDate,
                    'end_date'            => $endDate,
                    'reason'              => $description,
                    'status'              => 'APPROVED',
                ], $userObj);

                if ($legacyIzinId) {
                    DB::table('attendance_leave_requests')
                        ->where('id', $leaveId)
                        ->update(['legacy_permission_id' => $legacyIzinId]);
                }

                // 4. Potong Saldo Cuti Tahunan Karyawan (jika diaktifkan)
                if ($isDeduct === 'YES') {
                    try {
                        $this->leaveEntitlementService->deductLeaveBalance(
                            $emp->NIK,
                            $totalDays,
                            $reqNo,
                            'CUTI_BERSAMA',
                            $adminUser
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Potong saldo cuti bersama untuk {$emp->NIK} lewati batas / error: " . $e->getMessage());
                    }
                }

                $processedCount++;
            }

            return [
                'batch_id'           => $batchId,
                'batch_no'           => $batchNo,
                'title'              => $title,
                'total_employees'    => $processedCount,
                'total_days'         => $totalDays,
                'is_deduct'          => $isDeduct,
                'message'            => "Cuti Bersama '{$title}' berhasil diterapkan ke {$processedCount} karyawan.",
            ];
        });
    }

    /**
     * Dapatkan daftar riwayat batch cuti bersama yang telah disetup oleh HCGA
     */
    public function getCollectiveLeavesList()
    {
        return DB::table('attendance_collective_leaves')
            ->orderBy('start_date', 'DESC')
            ->get();
    }
}

