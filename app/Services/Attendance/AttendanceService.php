<?php

namespace App\Services\Attendance;

use App\Attendance;
use App\AttendanceMain;
use App\Exceptions\ApprovalWorkflowException;
use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AttendanceService
{
    protected $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Hitung jarak dua koordinat GPS menggunakan Formula Haversine (dalam meter).
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Cari lokasi kantor terdekat & validasi radius geofencing.
     */
    public function validateGeofence(float $latitude, float $longitude): array
    {
        $locations = DB::table('attendance_locations')
            ->where('status', 'ACTIVE')
            ->get();

        if ($locations->isEmpty()) {
            // Default fallback toleransi jika master lokasi belum didefinisikan
            return [
                'is_valid' => true,
                'location' => null,
                'distance' => 0,
                'message'  => 'Geofence bypass (Belum ada master lokasi aktif)',
            ];
        }

        // Fallback jika koordinat 0 atau belum terdeteksi dari GPS
        if (empty($latitude) || empty($longitude) || ((float) $latitude == 0.0 && (float) $longitude == 0.0)) {
            $defaultLoc = $locations->first();
            return [
                'is_valid' => false,
                'location' => $defaultLoc,
                'distance' => 0,
                'radius'   => (int) ($defaultLoc->radius_meters ?? 100),
                'message'  => 'Titik koordinat GPS tidak terdeteksi. Pastikan GPS perangkat Anda telah diaktifkan dan berikan izin akses lokasi pada browser.',
            ];
        }

        $nearest = null;
        $minDistance = null;

        foreach ($locations as $loc) {
            $dist = $this->calculateDistance($latitude, $longitude, (float) $loc->latitude, (float) $loc->longitude);
            if ($minDistance === null || $dist < $minDistance) {
                $minDistance = $dist;
                $nearest = $loc;
            }
        }

        $isValid = $minDistance <= (float) $nearest->radius_meters;

        return [
            'is_valid' => $isValid,
            'location' => $nearest,
            'distance' => round($minDistance, 2),
            'radius'   => (int) $nearest->radius_meters,
            'message'  => $isValid
                ? "Dalam radius kantor {$nearest->name} ({$minDistance}m)"
                : "Di luar jangkauan kantor {$nearest->name}. Jarak: " . round($minDistance) . "m (Radius: {$nearest->radius_meters}m)",
        ];
    }

    /**
     * Dapatkan jadwal kerja harian karyawan dari MasterWorkCalendar.
     */
    public function getWorkScheduleForDate(string $nik, string $date): array
    {
        $dayOfWeek = date('l', strtotime($date)); // e.g. 'Monday'
        $carbonDate = Carbon::parse($date);
        $isWeekend = in_array($dayOfWeek, ['Saturday', 'Sunday']);

        // Ambil penugasan kalender kerja pengguna (atau default kalender pertama)
        $user = DB::table('users')->where('Emp_ID', $nik)->first();
        $calendarId = $user->Work_Calendar_ID ?? null;

        if (!$calendarId) {
            $defaultCal = DB::table('work_calendars')->where('Employee_Type', 'OFFICE')->first();
            if (!$defaultCal) {
                $defaultCal = DB::table('work_calendars')->first();
            }
            $calendarId = $defaultCal ? $defaultCal->ID : null;
        }

        $schedule = [
            'calendar_id'   => $calendarId,
            'shift_id'      => null,
            'is_working'    => $isWeekend ? 0 : 1, // Fallback cerdas untuk akhir pekan jika detail belum disetup
            'default_start' => '08:00:00',
            'default_end'   => '17:00:00',
            'break_minutes' => 60,
            'day_of_week'   => $dayOfWeek,
        ];

        if ($calendarId) {
            $detail = DB::table('work_calendar_details')
                ->where('Calendar_ID', $calendarId)
                ->where('Day_Of_Week', $dayOfWeek)
                ->first();

            if ($detail) {
                $schedule['is_working'] = (int) $detail->Is_Working;
                $schedule['default_start'] = $detail->Default_Start ?: '08:00:00';
                $schedule['default_end'] = $detail->Default_End ?: '17:00:00';
                $schedule['break_minutes'] = (int) $detail->Break_Minutes;
            }
        }

        // Cek hari libur nasional / cuti bersama (cek tabel libur legacy atau master_holiday)
        $holidayName = null;
        $isHoliday = false;

        $legacyLibur = DB::table('libur')->where('libur_tgl', $date)->first();
        if ($legacyLibur) {
            $isHoliday = true;
            $holidayName = $legacyLibur->libur_keterangan ?? 'Hari Libur';
        } else {
            // Fallback jika tabel master_holiday digunakan
            if (Schema::hasTable('master_holiday')) {
                $mHoliday = DB::table('master_holiday')->whereDate('Holiday_Date', $date)->first();
                if ($mHoliday) {
                    $isHoliday = true;
                    $holidayName = $mHoliday->Description ?? 'Hari Libur';
                }
            }
        }

        $schedule['is_holiday'] = $isHoliday;
        $schedule['holiday_name'] = $holidayName;

        return $schedule;
    }

    /**
     * Catat Clock-In Karyawan.
     */
    public function clockIn($user, array $params): array
    {
        $nik = $user->Emp_ID;
        $today = Carbon::now()->toDateString();
        $nowTime = Carbon::now()->toTimeString();
        $periode = (int) Carbon::now()->format('Ym');

        $lat = (float) ($params['latitude'] ?? 0);
        $lng = (float) ($params['longitude'] ?? 0);
        $photo = $params['photo'] ?? null;
        $notes = $params['notes'] ?? null;

        // Kompresi & simpan foto selfie ke file storage (mencegah overflow VARCHAR 255 DB)
        $photoPath = $this->saveAttachmentFile($photo, 'attendance_photos');

        // Validasi Geofencing
        $geoCheck = $this->validateGeofence($lat, $lng);
        if (!$geoCheck['is_valid']) {
            throw new InvalidArgumentException($geoCheck['message']);
        }

        // Dapatkan jadwal kerja
        $schedule = $this->getWorkScheduleForDate($nik, $today);
        $expectedStart = Carbon::parse($today . ' ' . $schedule['default_start']);
        $actualTime = Carbon::parse($today . ' ' . $nowTime);

        $lateMinutes = 0;
        $status = 'ON_TIME';
        if ($actualTime->gt($expectedStart)) {
            $lateMinutes = $actualTime->diffInMinutes($expectedStart);
            $status = $lateMinutes > 15 ? 'LATE' : 'ON_TIME'; // Toleransi 15 menit
        }

        $main = AttendanceMain::firstOrCreate(
            ['NIK' => $nik, 'Periode' => $periode],
            [
                'Task_ID'    => 'ATT-' . $nik . '-' . $periode . '-' . sprintf('%06d', mt_rand(1, 999999)),
                'Status'     => 'CREATED',
                'Created_By' => $user->username ?? 'SYSTEM',
            ]
        );

        $existing = Attendance::where('Task_ID', $main->Task_ID)
            ->where('Scan_Date', $today)
            ->first();

        if ($existing && !empty($existing->Start_Time)) {
            throw new InvalidArgumentException('Anda sudah melakukan Clock-In hari ini pukul ' . $existing->Start_Time);
        }

        $payload = [
            'Task_ID'              => $main->Task_ID,
            'NIK'                  => $nik,
            'Periode'              => $periode,
            'Scan_Date'            => $today,
            'Category'             => $schedule['is_holiday'] ? 'LIBUR NASIONAL' : 'KERJA',
            'Start_Time'           => $nowTime,
            'Shift_1'              => 1,
            'Shift_2'              => 0,
            'Shift_3'              => 0,
            'Clock_In_Lat'         => $lat,
            'Clock_In_Lng'         => $lng,
            'Clock_In_Photo'       => $photoPath ?: $photo,
            'Clock_In_Location_ID' => $geoCheck['location']->id ?? null,
            'Clock_In_Status'      => $status,
            'Late_Minutes'         => $lateMinutes,
            'Work_Calendar_ID'     => $schedule['calendar_id'],
            'Notes'                => $notes,
            'Created_By'           => $user->username ?? 'SYSTEM',
            'Created_Date'         => Carbon::now(),
        ];

        if ($existing) {
            $existing->update($payload);
            $entry = $existing;
        } else {
            $entry = Attendance::create($payload);
        }

        return [
            'entry'        => $entry,
            'status'       => $status,
            'late_minutes' => $lateMinutes,
            'message'      => 'Clock-In berhasil dicatat pada ' . $nowTime,
        ];
    }

    /**
     * Catat Clock-Out Karyawan.
     */
    public function clockOut($user, array $params): array
    {
        $nik = $user->Emp_ID;
        $today = Carbon::now()->toDateString();
        $nowTime = Carbon::now()->toTimeString();
        $periode = (int) Carbon::now()->format('Ym');

        $lat = (float) ($params['latitude'] ?? 0);
        $lng = (float) ($params['longitude'] ?? 0);
        $photo = $params['photo'] ?? null;
        $notes = $params['notes'] ?? null;

        // Kompresi & simpan foto selfie ke file storage (mencegah overflow VARCHAR 255 DB)
        $photoPath = $this->saveAttachmentFile($photo, 'attendance_photos');

        // Validasi Geofencing
        $geoCheck = $this->validateGeofence($lat, $lng);
        if (!$geoCheck['is_valid']) {
            throw new InvalidArgumentException($geoCheck['message']);
        }

        // Cari log hari ini langsung berdasarkan NIK dan Scan_Date agar tidak gagal jika Task_ID baru
        $entry = Attendance::where('NIK', $nik)->where('Scan_Date', $today)->first();

        // Fallback jika via Task_ID di AttendanceMain
        if (!$entry) {
            $main = AttendanceMain::where('NIK', $nik)->where('Periode', $periode)->first();
            if ($main) {
                $entry = Attendance::where('Task_ID', $main->Task_ID)->where('Scan_Date', $today)->first();
            }
        }

        if (!$entry || empty($entry->Start_Time)) {
            throw new InvalidArgumentException('Anda belum melakukan Clock-In hari ini.');
        }

        $schedule = $this->getWorkScheduleForDate($nik, $today);
        $expectedEnd = Carbon::parse($today . ' ' . $schedule['default_end']);
        $actualEnd = Carbon::parse($today . ' ' . $nowTime);

        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;
        $outStatus = 'NORMAL';

        if ($actualEnd->lt($expectedEnd)) {
            $earlyLeaveMinutes = $expectedEnd->diffInMinutes($actualEnd);
            $outStatus = 'EARLY_LEAVE';
        } elseif ($actualEnd->gt($expectedEnd)) {
            $overtimeMinutes = $actualEnd->diffInMinutes($expectedEnd);
        }

        $entryUpdate = [
            'End_Time'              => $nowTime,
            'Clock_Out_Lat'         => $lat,
            'Clock_Out_Lng'         => $lng,
            'Clock_Out_Photo'       => $photoPath ?: $photo,
            'Clock_Out_Location_ID' => $geoCheck['location']->id ?? null,
            'Clock_Out_Status'      => $outStatus,
            'Early_Leave_Minutes'   => $earlyLeaveMinutes,
            'Overtime_Minutes'      => $overtimeMinutes,
            'Notes'                 => $notes ?: ($entry->Notes ?? null),
        ];

        DB::table('attendant_log')->where('ID', $entry->ID)->update($entryUpdate);
        $entry = DB::table('attendant_log')->where('ID', $entry->ID)->first();

        return [
            'entry'               => $entry,
            'status'              => $outStatus,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes'    => $overtimeMinutes,
            'message'             => 'Clock-Out berhasil dicatat pada ' . $nowTime,
        ];
    }

    /**
     * Helper untuk kompresi dan simpan dokumen / blob ke file storage agar tidak memberatkan DB
     */
    public function saveAttachmentFile($fileOrBase64, string $subfolder = 'attendance_attachments'): ?string
    {
        if (empty($fileOrBase64)) {
            return null;
        }

        $publicUploads = public_path("uploads/{$subfolder}");
        if (!file_exists($publicUploads)) {
            @mkdir($publicUploads, 0777, true);
        }
        $destDir = $publicUploads;

        $filename = uniqid('att_') . '_' . time();

        // Kasus 1: UploadedFile (dari multipart form request)
        if ($fileOrBase64 instanceof \Illuminate\Http\UploadedFile) {
            if ($fileOrBase64->getSize() > 5 * 1024 * 1024) {
                throw new InvalidArgumentException('Ukuran file melebihi batas maksimal 5MB.');
            }
            $ext = strtolower($fileOrBase64->getClientOriginalExtension());
            $fullName = "{$filename}.{$ext}";
            $fileOrBase64->move($destDir, $fullName);
            return "uploads/{$subfolder}/{$fullName}";
        }

        // Kasus 2: Base64 string / Data URI Blob dari Frontend / Webview
        if (is_string($fileOrBase64) && preg_match('/^data:(image\/(\w+)|application\/pdf);base64,/', $fileOrBase64, $matches)) {
            $type = $matches[1];
            $ext = ($type === 'application/pdf') ? 'pdf' : ($matches[2] ?? 'jpg');
            $data = substr($fileOrBase64, strpos($fileOrBase64, ',') + 1);
            $decoded = base64_decode($data);

            if (strlen($decoded) > 5 * 1024 * 1024) {
                throw new InvalidArgumentException('Ukuran dokumen base64 melebihi batas maksimal 5MB.');
            }

            // Jika file berupa gambar, lakukan kompresi otomatis via GD jika tersedia
            if (in_array($ext, ['jpeg', 'jpg', 'png'])) {
                $img = @imagecreatefromstring($decoded);
                if ($img !== false) {
                    $fullName = "{$filename}.jpg";
                    $targetPath = "{$destDir}/{$fullName}";
                    imagejpeg($img, $targetPath, 75); // Kompresi kualitas 75%
                    imagedestroy($img);
                    return "uploads/{$subfolder}/{$fullName}";
                }
            }

            $fullName = "{$filename}.{$ext}";
            file_put_contents("{$destDir}/{$fullName}", $decoded);
            return "uploads/{$subfolder}/{$fullName}";
        }

        return is_string($fileOrBase64) ? $fileOrBase64 : null;
    }
    public function submitLeaveRequest($user, array $data, $uploadedFile = null): array
    {
        $nik = $user->Emp_ID;
        $leaveType = strtoupper(trim($data['leave_type']));
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $reason = $data['reason'] ?? '';
        $clockInTime = $data['clock_in_time'] ?? null;
        $clockOutTime = $data['clock_out_time'] ?? null;

        // Proses kompresi & simpan attachment
        $attachmentFile = $uploadedFile ?: ($data['attachment'] ?? null);
        $attachmentPath = $this->saveAttachmentFile($attachmentFile, 'leave_documents');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        if ($end->lt($start)) {
            throw new InvalidArgumentException('Tanggal akhir tidak boleh sebelum tanggal mulai.');
        }
        $totalDays = $end->diffInDays($start) + 1;

        $requestNo = 'LV/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999));

        return DB::transaction(function () use ($user, $nik, $leaveType, $startDate, $endDate, $totalDays, $reason, $attachmentPath, $clockInTime, $clockOutTime, $requestNo) {
            $leaveId = DB::table('attendance_leave_requests')->insertGetId([
                'request_no'       => $requestNo,
                'nik'              => $nik,
                'leave_type'       => $leaveType,
                'start_date'       => $startDate,
                'end_date'         => $endDate,
                'clock_in_time'    => $clockInTime,
                'clock_out_time'   => $clockOutTime,
                'total_days'       => $totalDays,
                'reason'           => $reason,
                'attachment'       => $attachmentPath,
                'attachment_path'  => $attachmentPath,
                'status'           => 'SUBMITTED',
                'created_by'       => $user->username,
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]);

            // Submit ke Dynamic Approval Engine dengan multi-tier fallback
            $approvalRequestId = null;
            $context = [
                'Request_Type' => 'ATTENDANCE_LEAVE',
                'Location'     => $user->Location ?? 'HEAD_OFFICE',
                'Company_ID'   => (int) ($user->Company_ID ?? 1),
                'Department'   => $user->Department ?? 'ALL',
            ];

            try {
                $apprResult = $this->workflowService->submit([
                    'Request_No'  => $requestNo,
                    'Description' => "Pengajuan {$leaveType} ({$totalDays} hari): {$reason}",
                ], $context, (int) $user->id);

                $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
            } catch (\Throwable $e) {
                // Fallback 1: Coba dengan context umum (wildcard location & dept) jika policy spesifik departemen/lokasi tidak ditemukan
                try {
                    $context['Location'] = null;
                    $context['Department'] = null;
                    $apprResult = $this->workflowService->submit([
                        'Request_No'  => $requestNo,
                        'Description' => "Pengajuan {$leaveType} ({$totalDays} hari): {$reason}",
                    ], $context, (int) $user->id);

                    $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
                } catch (\Throwable $ex) {
                    // Fallback 2: Catat log agar admin/HCGA mengetahui jika kebijakan approval belum aktif
                    Log::warning("Approval Engine fallback untuk {$requestNo}: " . $ex->getMessage());
                }
            }

            if ($approvalRequestId) {
                DB::table('attendance_leave_requests')
                    ->where('id', $leaveId)
                    ->update(['approval_request_id' => $approvalRequestId]);
            }

            return [
                'leave_id'            => $leaveId,
                'request_no'          => $requestNo,
                'approval_request_id' => $approvalRequestId,
                'attachment_path'     => $attachmentPath,
                'message'             => 'Pengajuan izin/cuti berhasil dikirimkan.',
            ];
        });
    }

    /**
     * Submit Pengajuan Koreksi Absensi dengan integrasi ApprovalWorkflowService.
     */
    public function submitCorrectionRequest($user, array $data, $uploadedFile = null): array
    {
        $nik = $user->Emp_ID;
        $date = $data['attendance_date'];
        $corrType = strtoupper(trim($data['correction_type'] ?? 'BOTH'));
        if (!in_array($corrType, ['BOTH', 'IN', 'OUT'])) {
            $corrType = 'BOTH';
        }

        $corrIn = $data['corrected_clock_in'] ?? null;
        $corrOut = $data['corrected_clock_out'] ?? null;
        if ($corrType === 'IN') {
            $corrOut = null;
        } elseif ($corrType === 'OUT') {
            $corrIn = null;
        }

        $reason = $data['reason'] ?? '';
        $typeLabel = $corrType === 'IN' ? 'Jam Masuk Saja' : ($corrType === 'OUT' ? 'Jam Pulang Saja' : 'Jam Masuk & Pulang');

        $evidenceFile = $uploadedFile ?: ($data['evidence_file'] ?? null);
        $evidencePath = $this->saveAttachmentFile($evidenceFile, 'correction_documents');

        $existingLog = DB::table('attendant_log')->where('NIK', $nik)->where('Scan_Date', $date)->first();
        $origIn = $existingLog ? $existingLog->Start_Time : null;
        $origOut = $existingLog ? $existingLog->End_Time : null;

        $correctionNo = 'CORR/' . date('Ymd') . '/' . sprintf('%04d', mt_rand(1, 9999));

        return DB::transaction(function () use ($user, $nik, $date, $corrType, $typeLabel, $origIn, $origOut, $corrIn, $corrOut, $reason, $evidencePath, $correctionNo) {
            $corrId = DB::table('attendance_corrections')->insertGetId([
                'correction_no'       => $correctionNo,
                'nik'                 => $nik,
                'attendance_date'     => $date,
                'correction_type'     => $corrType,
                'original_clock_in'   => $origIn,
                'original_clock_out'  => $origOut,
                'corrected_clock_in'  => $corrIn,
                'corrected_clock_out' => $corrOut,
                'reason'              => $reason,
                'evidence_file'       => $evidencePath,
                'attachment_path'     => $evidencePath,
                'status'              => 'SUBMITTED',
                'created_by'          => $user->username,
                'created_at'          => Carbon::now(),
                'updated_at'          => Carbon::now(),
            ]);

            // Submit ke Dynamic Approval Engine dengan multi-tier fallback
            $approvalRequestId = null;
            $context = [
                'Request_Type' => 'ATTENDANCE_CORRECTION',
                'Location'     => $user->Location ?? 'HEAD_OFFICE',
                'Company_ID'   => (int) ($user->Company_ID ?? 1),
                'Department'   => $user->Department ?? 'ALL',
            ];

            try {
                $apprResult = $this->workflowService->submit([
                    'Request_No'  => $correctionNo,
                    'Description' => "Koreksi Absensi ({$typeLabel}) tgl {$date}: {$reason}",
                ], $context, (int) $user->id);

                $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
            } catch (\Throwable $e) {
                // Fallback 1: Coba dengan context umum (wildcard location & dept) jika policy spesifik departemen/lokasi tidak ditemukan
                try {
                    $context['Location'] = null;
                    $context['Department'] = null;
                    $apprResult = $this->workflowService->submit([
                        'Request_No'  => $correctionNo,
                        'Description' => "Koreksi Absensi ({$typeLabel}) tgl {$date}: {$reason}",
                    ], $context, (int) $user->id);

                    $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
                } catch (\Throwable $ex) {
                    // Fallback 2: Catat log agar admin/HCGA mengetahui jika kebijakan approval belum aktif
                    Log::warning("Approval Engine fallback untuk {$correctionNo}: " . $ex->getMessage());
                }
            }

            if ($approvalRequestId) {
                DB::table('attendance_corrections')
                    ->where('id', $corrId)
                    ->update(['approval_request_id' => $approvalRequestId]);
            }

            return [
                'correction_id'       => $corrId,
                'correction_no'       => $correctionNo,
                'approval_request_id' => $approvalRequestId,
                'message'             => 'Pengajuan koreksi absensi berhasil dikirimkan.',
            ];
        });
    }
}

