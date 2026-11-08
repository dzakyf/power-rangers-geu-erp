<?php

namespace App\Http\Controllers\HCGA;

use App\Attendance;
use App\AttendanceMain;
use App\Http\Controllers\Controller;
use App\Libur;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendancesController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    /**
     * Web: Pusat Monitoring & Rekap Data Presensi Seluruh Karyawan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = [
            'date'           => $request->input('date', date('Y-m-d')),
            'department'     => $request->input('department', 'ALL'),
            'periode'        => $request->input('periode', date('Y-m')),
            'employee_nik'   => $request->input('employee_nik', 'ALL'),
            'cutoff_rule_id' => $request->input('cutoff_rule_id', null),
        ];

        $data = $this->attendanceService->getAttendanceMonitoringData($user, $filters);

        return view('humanresources.attendant.data', compact('data', 'user'));
    }

    /**
     * AJAX: Ambil data presensi & metrik tanpa reload seluruh halaman (ringan & instan)
     */
    public function getMonitoringDataAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $filters = [
            'date'           => $request->input('date', date('Y-m-d')),
            'department'     => $request->input('department', 'ALL'),
            'periode'        => $request->input('periode', date('Y-m')),
            'employee_nik'   => $request->input('employee_nik', 'ALL'),
            'cutoff_rule_id' => $request->input('cutoff_rule_id', null),
        ];

        $data = $this->attendanceService->getAttendanceMonitoringData($user, $filters);

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Export Excel (.xlsx) Laporan Presensi Karyawan dengan Hyperlink Foto & Google Maps
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $type = $request->input('type', 'daily'); // 'daily' atau 'monthly'
        $filters = [
            'date'           => $request->input('date', date('Y-m-d')),
            'department'     => $request->input('department', 'ALL'),
            'periode'        => $request->input('periode', date('Y-m')),
            'employee_nik'   => $request->input('employee_nik', 'ALL'),
            'cutoff_rule_id' => $request->input('cutoff_rule_id', null),
        ];

        $data = $this->attendanceService->getAttendanceMonitoringData($user, $filters);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setShowGridLines(true);

        $baseUrl = url('/');
        $isEmpDetail = !empty($data['isEmployeeDetailMode']) && !empty($data['selectedEmployee']);

        if ($isEmpDetail && $type === 'daily') {
            // EXPORT KHUSUS: Rincian Presensi Harian Karyawan Tertentu Selama 1 Bulan Penuh
            $emp = $data['selectedEmployee'];
            $sheet->setTitle('Rincian Presensi Bulanan');

            // Header Judul & Info Karyawan
            $sheet->setCellValue('A1', 'LAPORAN RINCIAN PRESENSI BULANAN KARYAWAN');
            $sheet->setCellValue('A2', 'Nama: ' . ($emp->Name ?: $emp->username) . ' | NIK: ' . $emp->Emp_ID . ' | Dept: ' . ($emp->Department ?: '-') . ' | Jabatan: ' . ($emp->Position ?: '-'));
            $sheet->setCellValue('A3', 'Periode Payroll: ' . date('F Y', strtotime($data['filterPeriode'] . '-01')) . ' (' . ($data['cutoffRange']['formatted_label'] ?? '') . ')');
            $sheet->getStyle('A1:A3')->getFont()->setBold(true);

            // Table Head
            $headers = [
                'No', 'Tanggal', 'Hari', 'Jam Masuk', 'Jam Pulang',
                'Status Kehadiran', 'Menit Telat', 'Jam Lembur',
                'Keterangan', 'Koordinat GPS', 'Link Peta (Google Maps)',
                'Foto Masuk (Link)', 'Foto Pulang (Link)'
            ];
            $sheet->fromArray($headers, null, 'A5');
            $sheet->getStyle('A5:M5')->getFont()->setBold(true);
            $sheet->getStyle('A5:M5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0EBF5');

            $rowIdx = 6;
            foreach ($data['dailyList'] as $i => $row) {
                $otHours = floor($row['overtime_minutes'] / 60);
                $otMins = $row['overtime_minutes'] % 60;
                $otText = $row['overtime_minutes'] > 0 ? "{$otHours} Jam {$otMins} Mnt" : '-';

                $latLngText = (!empty($row['clock_in_lat']) && !empty($row['clock_in_lng'])) 
                    ? "{$row['clock_in_lat']}, {$row['clock_in_lng']}" 
                    : '-';

                $mapsUrl = (!empty($row['clock_in_lat']) && !empty($row['clock_in_lng'])) 
                    ? "https://maps.google.com/?q={$row['clock_in_lat']},{$row['clock_in_lng']}" 
                    : null;

                $photoInUrl = !empty($row['clock_in_photo']) ? "{$baseUrl}/{$row['clock_in_photo']}" : null;
                $photoOutUrl = !empty($row['clock_out_photo']) ? "{$baseUrl}/{$row['clock_out_photo']}" : null;

                $sheet->setCellValue('A' . $rowIdx, $i + 1);
                $sheet->setCellValue('B' . $rowIdx, date('d/m/Y', strtotime($row['date'])));
                $sheet->setCellValue('C' . $rowIdx, $row['day_name']);
                $sheet->setCellValue('D' . $rowIdx, $row['clock_in']);
                $sheet->setCellValue('E' . $rowIdx, $row['clock_out']);
                $sheet->setCellValue('F' . $rowIdx, $row['status_kehadiran']);
                $sheet->setCellValue('G' . $rowIdx, $row['late_minutes'] > 0 ? $row['late_minutes'] : 0);
                $sheet->setCellValue('H' . $rowIdx, $otText);
                $sheet->setCellValue('I' . $rowIdx, $row['notes'] ?: '-');
                $sheet->setCellValue('J' . $rowIdx, $latLngText);

                // Hyperlink Google Maps
                if ($mapsUrl) {
                    $sheet->setCellValue('K' . $rowIdx, 'Buka Peta Lokasi');
                    $sheet->getCell('K' . $rowIdx)->getHyperlink()->setUrl($mapsUrl);
                    $sheet->getStyle('K' . $rowIdx)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                    $sheet->getStyle('K' . $rowIdx)->getFont()->setUnderline(true);
                } else {
                    $sheet->setCellValue('K' . $rowIdx, '-');
                }

                // Hyperlink Foto Masuk
                if ($photoInUrl) {
                    $sheet->setCellValue('L' . $rowIdx, 'Lihat Foto Masuk');
                    $sheet->getCell('L' . $rowIdx)->getHyperlink()->setUrl($photoInUrl);
                    $sheet->getStyle('L' . $rowIdx)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                    $sheet->getStyle('L' . $rowIdx)->getFont()->setUnderline(true);
                } else {
                    $sheet->setCellValue('L' . $rowIdx, '-');
                }

                // Hyperlink Foto Pulang
                if ($photoOutUrl) {
                    $sheet->setCellValue('M' . $rowIdx, 'Lihat Foto Pulang');
                    $sheet->getCell('M' . $rowIdx)->getHyperlink()->setUrl($photoOutUrl);
                    $sheet->getStyle('M' . $rowIdx)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                    $sheet->getStyle('M' . $rowIdx)->getFont()->setUnderline(true);
                } else {
                    $sheet->setCellValue('M' . $rowIdx, '-');
                }

                $rowIdx++;
            }

            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $safeEmpName = preg_replace('/[^A-Za-z0-9_-]/', '_', $emp->Name ?: $emp->username);
            $filename = 'Rincian_Presensi_' . $safeEmpName . '_' . $data['filterPeriode'] . '.xlsx';
        } elseif ($type === 'monthly') {
            $sheet->setTitle('Rekap Bulanan');

            // Header Judul
            $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PRESENSI BULANAN KARYAWAN (SIKLUS CUTOFF)');
            $sheet->setCellValue('A2', 'Periode Payroll: ' . date('F Y', strtotime($data['filterPeriode'] . '-01')) . ' | Rentang Cutoff: ' . ($data['cutoffRange']['formatted_label'] ?? '-') . ' | Departemen: ' . $data['filterDept']);
            $sheet->getStyle('A1:A2')->getFont()->setBold(true);

            // Table Head
            $headers = ['No', 'NIK', 'Nama Karyawan', 'Departemen', 'Total Hadir (Hari)', 'Total Telat (Kali)', 'Akumulasi Menit Telat', 'Akumulasi Jam Lembur'];
            $sheet->fromArray($headers, null, 'A4');
            $sheet->getStyle('A4:H4')->getFont()->setBold(true);
            $sheet->getStyle('A4:H4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0EBF5');

            $rowIdx = 5;
            foreach ($data['monthlyList'] as $i => $row) {
                $otHours = floor($row['total_overtime_minutes'] / 60);
                $otMins = $row['total_overtime_minutes'] % 60;
                $otText = $row['total_overtime_minutes'] > 0 ? "{$otHours} Jam {$otMins} Mnt" : '-';

                $sheet->setCellValue('A' . $rowIdx, $i + 1);
                $sheet->setCellValueExplicit('B' . $rowIdx, $row['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $rowIdx, $row['name']);
                $sheet->setCellValue('D' . $rowIdx, $row['department']);
                $sheet->setCellValue('E' . $rowIdx, $row['total_present']);
                $sheet->setCellValue('F' . $rowIdx, $row['total_late']);
                $sheet->setCellValue('G' . $rowIdx, $row['total_late_minutes']);
                $sheet->setCellValue('H' . $rowIdx, $otText);
                $rowIdx++;
            }

            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'Laporan_Presensi_Bulanan_' . $data['filterPeriode'] . '.xlsx';
        } else {
            // Daily Export (Default)
            $sheet->setTitle('Monitoring Harian');

            // Header Judul
            $sheet->setCellValue('A1', 'LAPORAN MONITORING PRESENSI HARIAN KARYAWAN');
            $sheet->setCellValue('A2', 'Tanggal: ' . date('d F Y', strtotime($data['filterDate'])) . ' | Departemen: ' . $data['filterDept']);
            $sheet->getStyle('A1:A2')->getFont()->setBold(true);

            // Table Head
            $headers = [
                'No', 'NIK', 'Nama Karyawan', 'Departemen', 'Jabatan',
                'Jam Masuk', 'Jam Pulang', 'Status Kehadiran', 'Menit Telat',
                'Jam Lembur', 'Keterangan', 'Koordinat GPS', 'Link Peta (Google Maps)',
                'Foto Masuk (Link)', 'Foto Pulang (Link)'
            ];
            $sheet->fromArray($headers, null, 'A4');
            $sheet->getStyle('A4:O4')->getFont()->setBold(true);
            $sheet->getStyle('A4:O4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0EBF5');

            $rowIdx = 5;
            foreach ($data['dailyList'] as $i => $row) {
                $otHours = floor($row['overtime_minutes'] / 60);
                $otMins = $row['overtime_minutes'] % 60;
                $otText = $row['overtime_minutes'] > 0 ? "{$otHours} Jam {$otMins} Mnt" : '-';

                $latLngText = (!empty($row['clock_in_lat']) && !empty($row['clock_in_lng'])) 
                    ? "{$row['clock_in_lat']}, {$row['clock_in_lng']}" 
                    : '-';

                $mapsUrl = (!empty($row['clock_in_lat']) && !empty($row['clock_in_lng'])) 
                    ? "https://maps.google.com/?q={$row['clock_in_lat']},{$row['clock_in_lng']}" 
                    : null;

                $photoInUrl = !empty($row['clock_in_photo']) ? "{$baseUrl}/{$row['clock_in_photo']}" : null;
                $photoOutUrl = !empty($row['clock_out_photo']) ? "{$baseUrl}/{$row['clock_out_photo']}" : null;

                $sheet->setCellValue('A' . $rowIdx, $i + 1);
                $sheet->setCellValueExplicit('B' . $rowIdx, $row['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $rowIdx, $row['name']);
                $sheet->setCellValue('D' . $rowIdx, $row['department']);
                $sheet->setCellValue('E' . $rowIdx, $row['position']);
                $sheet->setCellValue('F' . $rowIdx, $row['clock_in']);
                $sheet->setCellValue('G' . $rowIdx, $row['clock_out']);
                $sheet->setCellValue('H' . $rowIdx, $row['status_kehadiran']);
                $sheet->setCellValue('I' . $rowIdx, $row['late_minutes'] > 0 ? $row['late_minutes'] : 0);
                $sheet->setCellValue('J' . $rowIdx, $otText);
                $sheet->setCellValue('K' . $rowIdx, $row['notes'] ?: '-');
                $sheet->setCellValue('L' . $rowIdx, $latLngText);

                // Hyperlink Google Maps
                if ($mapsUrl) {
                    $sheet->setCellValue('M' . $rowIdx, 'Buka Peta Lokasi');
                    $sheet->getCell('M' . $rowIdx)->getHyperlink()->setUrl($mapsUrl);
                    $sheet->getStyle('M' . $rowIdx)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                    $sheet->getStyle('M' . $rowIdx)->getFont()->setUnderline(true);
                } else {
                    $sheet->setCellValue('M' . $rowIdx, '-');
                }

                // Hyperlink Foto Masuk
                if ($photoInUrl) {
                    $sheet->setCellValue('N' . $rowIdx, 'Lihat Foto Masuk');
                    $sheet->getCell('N' . $rowIdx)->getHyperlink()->setUrl($photoInUrl);
                    $sheet->getStyle('N' . $rowIdx)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                    $sheet->getStyle('N' . $rowIdx)->getFont()->setUnderline(true);
                } else {
                    $sheet->setCellValue('N' . $rowIdx, '-');
                }

                // Hyperlink Foto Pulang
                if ($photoOutUrl) {
                    $sheet->setCellValue('O' . $rowIdx, 'Lihat Foto Pulang');
                    $sheet->getCell('O' . $rowIdx)->getHyperlink()->setUrl($photoOutUrl);
                    $sheet->getStyle('O' . $rowIdx)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
                    $sheet->getStyle('O' . $rowIdx)->getFont()->setUnderline(true);
                } else {
                    $sheet->setCellValue('O' . $rowIdx, '-');
                }

                $rowIdx++;
            }

            foreach (range('A', 'O') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'Laporan_Presensi_Harian_' . $data['filterDate'] . '.xlsx';
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * XML: sumber data grid halaman utama
     */
    public function indexXml()
    {
        $this->sendXmlHeaders();
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $user = Auth::user();
        $nomor = 1;

        echo "<rows>";

        $query = AttendanceMain::where('NIK', $user->Emp_ID)
            ->orderBy('ID', 'DESC')
            ->get();

        foreach ($query as $row) {
            echo "<row id=\"{$row->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($row->Task_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars($user->Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Periode) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Created_By) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Created_Date) . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Web Desktop: Portal Absensi & Timesheet Mandiri Karyawan
     */
    public function portalIndex()
    {
        $user = Auth::user();
        $today = date('Y-m-d');

        // Resolusi Aturan Cutoff Karyawan
        $cutoffRule = app(\App\Services\Attendance\AttendanceCutoffService::class)->resolveCutoffRule($user->Emp_ID, $user->Department ?? null);
        $activePeriodeYm = app(\App\Services\Attendance\AttendanceCutoffService::class)->getPeriodeByDate($today, $cutoffRule);
        $cutoffRange = app(\App\Services\Attendance\AttendanceCutoffService::class)->getCutoffPeriodRange($activePeriodeYm, $cutoffRule);
        $periode = (int) str_replace('-', '', $activePeriodeYm);

        // Pastikan record periode bulan ini ada
        AttendanceMain::firstOrCreate(
            ['NIK' => $user->Emp_ID, 'Periode' => $periode],
            [
                'Task_ID'      => 'ATT-' . $user->Emp_ID . '-' . $periode . '-000001',
                'Status'       => 'CREATED',
                'Created_By'   => $user->username,
                'Created_Date' => now(),
            ]
        );

        $todayEntry = Attendance::where('NIK', $user->Emp_ID)->where('Scan_Date', $today)->first();
        $schedule = $this->attendanceService->getWorkScheduleForDate($user->Emp_ID, $today);

        // Ringkasan Kehadiran Karyawan dalam Siklus Cutoff Berjalan
        $cutoffStats = DB::table('attendant_log')
            ->where('NIK', $user->Emp_ID)
            ->whereBetween('Scan_Date', [$cutoffRange['start_date'], $cutoffRange['end_date']])
            ->select(
                DB::raw('COUNT(CASE WHEN Start_Time IS NOT NULL THEN 1 END) as total_present'),
                DB::raw('COUNT(CASE WHEN Late_Minutes > 0 OR Clock_In_Status = "LATE" THEN 1 END) as total_late'),
                DB::raw('SUM(Late_Minutes) as total_late_minutes'),
                DB::raw('SUM(Overtime_Minutes) as total_overtime_minutes')
            )
            ->first();

        // Riwayat Pengajuan Cuti & Izin
        $leaveRequests = DB::table('attendance_leave_requests')
            ->where('nik', $user->Emp_ID)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Riwayat Pengajuan Koreksi Absensi
        $corrections = DB::table('attendance_corrections')
            ->where('nik', $user->Emp_ID)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // Riwayat Pengajuan Lembur (Overtime)
        $overtimeRequests = DB::table('attendance_overtime_requests as ot')
            ->leftJoin('overtime_rate_matrices as m', 'm.id', '=', 'ot.matrix_id')
            ->where('ot.nik', $user->Emp_ID)
            ->select('ot.*', 'm.matrix_name')
            ->orderBy('ot.id', 'desc')
            ->limit(10)
            ->get();

        // Riwayat Pengajuan Change Shift / Tukar Shift
        $changeShiftRequests = DB::table('attendance_change_shifts as cs')
            ->leftJoin('master_shifts as s_curr', 's_curr.id', '=', 'cs.current_shift_id')
            ->leftJoin('master_shifts as s_tgt', 's_tgt.id', '=', 'cs.target_shift_id')
            ->leftJoin('users as u_peer', 'u_peer.Emp_ID', '=', 'cs.peer_nik')
            ->where('cs.nik', $user->Emp_ID)
            ->select(
                'cs.*',
                's_curr.shift_name as current_shift_name',
                's_curr.start_time as current_shift_start',
                's_curr.end_time as current_shift_end',
                's_tgt.shift_name as target_shift_name',
                's_tgt.start_time as target_shift_start',
                's_tgt.end_time as target_shift_end',
                'u_peer.Name as peer_name'
            )
            ->orderBy('cs.id', 'desc')
            ->limit(10)
            ->get();

        // Master Shift Aktif untuk Pilihan Ganti Shift
        $availableShifts = DB::table('master_shifts')->where('status', 'ACTIVE')->orderBy('id', 'asc')->get();

        // Rekan Kerja Satu Departemen untuk Opsi Tukar Shift (Peer Exchange)
        $colleagues = DB::table('users')
            ->where('Status', 'ACTIVE')
            ->where('Emp_ID', '!=', $user->Emp_ID)
            ->whereNotNull('Emp_ID')
            ->when(!empty($user->Department), function ($q) use ($user) {
                return $q->where('Department', $user->Department);
            })
            ->select('Emp_ID', 'Name', 'Department', 'Position')
            ->orderBy('Name', 'asc')
            ->get();

        // Lokasi Kantor untuk penentuan Zipcode & Wilayah
        $locations = DB::table('attendance_locations')->where('status', 'ACTIVE')->get();

        $kategoriList = DB::table('kategori_izin')
            ->where('status', 'ACTIVE')
            ->whereNotNull('kat_izin_code')
            ->orderBy('izin_jenis_id', 'asc')
            ->orderBy('kat_izin_id', 'asc')
            ->get();

        // Saldo Cuti Karyawan Berjalan
        $leaveBalance = app(\App\Services\Attendance\LeaveEntitlementService::class)->getOrCreateUserBalance($user->Emp_ID);

        // Resolusi Kompensasi Lembur Default Karyawan Berdasarkan Posisi / Org / Employment / Users
        $overtimeCompensation = app(\App\Services\Attendance\OvertimeMatrixService::class)->resolveCompensationType($user);

        return view('humanresources.attendant.portal', compact('user', 'todayEntry', 'schedule', 'leaveRequests', 'corrections', 'overtimeRequests', 'changeShiftRequests', 'availableShifts', 'colleagues', 'locations', 'kategoriList', 'cutoffRule', 'cutoffRange', 'cutoffStats', 'activePeriodeYm', 'overtimeCompensation', 'leaveBalance'));
    }

    /**
     * Web Desktop / Modal: Detail Progress Alur Approval Pengajuan
     */
    public function getApprovalProgress($approvalRequestId)
    {
        $requestData = DB::table('approval_request as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.Requester_Id')
            ->where('r.ID', $approvalRequestId)
            ->select('r.*', 'u.name as Requester_Name', 'u.username as Requester_Username')
            ->first();

        if (!$requestData) {
            return response()->json(['status' => 'error', 'message' => 'Data approval tidak ditemukan.'], 404);
        }

        $tasks = DB::table('approval_request_task as t')
            ->where('t.Request_ID', $approvalRequestId)
            ->orderBy('t.Stage_Order', 'asc')
            ->orderBy('t.Level_Number', 'asc')
            ->get();

        $taskIds = $tasks->pluck('ID')->toArray();
        $candidates = DB::table('approval_task_candidate as c')
            ->join('users as u', 'u.id', '=', 'c.User_ID')
            ->whereIn('c.Task_ID', $taskIds)
            ->select('c.*', 'u.name as Approver_Name', 'u.username as Approver_Username', 'u.Email as Approver_Email', 'u.Department as Approver_Dept')
            ->get()
            ->groupBy('Task_ID');

        $tasksByStage = $tasks->keyBy('Stage_Order');

        // Ambil semua steps lengkap dari Policy_Snapshot atau tabel approval_policy_step
        $steps = [];
        if (!empty($requestData->Policy_Snapshot)) {
            $snapshot = json_decode($requestData->Policy_Snapshot, true);
            $steps = $snapshot['Steps'] ?? [];
        }
        if (empty($steps) && !empty($requestData->Policy_ID)) {
            $steps = DB::table('approval_policy_step')
                ->where('Policy_ID', $requestData->Policy_ID)
                ->orderBy('Stage_Order', 'asc')
                ->get()
                ->map(function ($s) { return (array) $s; })
                ->toArray();
        }

        $allStages = [];
        foreach ($steps as $step) {
            $stageOrder = (int) ($step['Stage_Order'] ?? 1);
            $levelNum   = (int) ($step['Level_Number'] ?? 1);
            $label      = $step['Label'] ?? ('Level ' . $levelNum);

            if ($tasksByStage->has($stageOrder)) {
                $task = $tasksByStage->get($stageOrder);
                $taskCandidates = $candidates->get($task->ID, collect([]))->map(function($c) {
                    return [
                        'name'       => $c->Approver_Name,
                        'username'   => $c->Approver_Username,
                        'email'      => $c->Approver_Email,
                        'department' => $c->Approver_Dept,
                        'status'     => $c->Status,
                    ];
                })->toArray();

                $allStages[] = [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNum,
                    'label'         => $label,
                    'position_name' => $task->Position_Name ?? ($step['Position_Code'] ?? 'Approver'),
                    'department'    => $task->Department ?? ($step['Department'] ?? null),
                    'status'        => $task->Status,
                    'is_current'    => in_array($task->Status, ['PENDING', 'IN_PROGRESS']),
                    'is_completed'  => $task->Status === 'APPROVED',
                    'approvers'     => $taskCandidates,
                    'acted_at'      => $task->Acted_At,
                    'notes'         => $task->Notes,
                ];
            } else {
                // Tahap antrean berikutnya (waiting tahap sebelumnya)
                $prospectives = DB::table('master_position as p')
                    ->join('position_assignment as pa', 'pa.Position_ID', '=', 'p.ID')
                    ->join('users as u', 'u.id', '=', 'pa.User_ID')
                    ->where('pa.Status', 'ACTIVE')
                    ->where(function ($q) use ($step, $requestData) {
                        if (!empty($step['Position_Code'])) {
                            $q->where('p.Initial', $step['Position_Code']);
                        }
                        if (!empty($step['Department'])) {
                            $q->where('p.Department', $step['Department']);
                        } elseif (!empty($requestData->Department) && $requestData->Department !== 'ALL') {
                            $q->where('p.Department', $requestData->Department);
                        }
                        if (!empty($step['Level_Number'])) {
                            $q->where('p.Level', $step['Level_Number']);
                        }
                    })
                    ->select('u.name as name', 'u.username as username', 'u.Email as email', 'p.Department as department', 'p.Position as position')
                    ->get()
                    ->toArray();

                $allStages[] = [
                    'stage_order'   => $stageOrder,
                    'level_number'  => $levelNum,
                    'label'         => $label,
                    'position_name' => $step['Position_Code'] ?? 'Approver',
                    'department'    => $step['Department'] ?? null,
                    'status'        => 'WAITING',
                    'is_current'    => false,
                    'is_completed'  => false,
                    'approvers'     => $prospectives,
                    'acted_at'      => null,
                    'notes'         => 'Menunggu persetujuan tahap sebelumnya.',
                ];
            }
        }

        return response()->json([
            'status'     => 'success',
            'request'    => $requestData,
            'tasks'      => $tasks,
            'stages'     => $allStages,
            'candidates' => $candidates,
        ]);
    }

    /**
     * Dev/Admin Helper: Reset Clock-In / Clock-Out / Keduanya untuk kemudahan testing development
     */
    public function resetAttendance(Request $request)
    {
        $user = Auth::user();
        if (!$user || strtolower($user->username) !== 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Fitur reset hanya diperuntukkan untuk akun admin/developer.',
            ], 403);
        }

        $type = $request->input('type', 'both'); // 'in', 'out', 'both'
        $date = $request->input('date', date('Y-m-d'));
        $targetNik = $request->input('nik', $user->Emp_ID);

        $entry = Attendance::where('NIK', $targetNik)->where('Scan_Date', $date)->first();

        if (!$entry) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data log absensi tanggal ' . $date . ' belum ada.',
            ], 404);
        }

        if ($type === 'in') {
            DB::table('attendant_log')->where('ID', $entry->ID)->update([
                'Start_Time'           => null,
                'Clock_In_Lat'         => null,
                'Clock_In_Lng'         => null,
                'Clock_In_Photo'       => null,
                'Clock_In_Status'      => null,
                'Late_Minutes'         => 0,
            ]);
            $msg = 'Clock-In berhasil di-reset.';
        } elseif ($type === 'out') {
            DB::table('attendant_log')->where('ID', $entry->ID)->update([
                'End_Time'              => null,
                'Clock_Out_Lat'         => null,
                'Clock_Out_Lng'         => null,
                'Clock_Out_Photo'       => null,
                'Clock_Out_Status'      => null,
                'Early_Leave_Minutes'   => 0,
                'Overtime_Minutes'      => 0,
            ]);
            $msg = 'Clock-Out berhasil di-reset.';
        } else {
            // Reset Keduanya
            DB::table('attendant_log')->where('ID', $entry->ID)->update([
                'Start_Time'           => null,
                'Clock_In_Lat'         => null,
                'Clock_In_Lng'         => null,
                'Clock_In_Photo'       => null,
                'Clock_In_Status'      => null,
                'Late_Minutes'         => 0,
                'End_Time'              => null,
                'Clock_Out_Lat'         => null,
                'Clock_Out_Lng'         => null,
                'Clock_Out_Photo'       => null,
                'Clock_Out_Status'      => null,
                'Early_Leave_Minutes'   => 0,
                'Overtime_Minutes'      => 0,
            ]);
            $msg = 'Clock-In dan Clock-Out berhasil di-reset ke kondisi awal.';
        }

        return response()->json([
            'status'  => 'success',
            'message' => $msg,
        ]);
    }

    /**
     * Web: halaman entry absensi (pilih periode)
     */
    public function entryIndex()
    {
        return view('humanresources.attendant.attendant-entry');
    }

    /**
     * XML: sumber data grid entry (periode-level)
     */
    public function entryXml()
    {
        $this->sendXmlHeaders();
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $user = Auth::user();
        $nomor = 1;
        $currentPeriode = (int) date('Ym');

        // Otomatis pastikan periode bulan berjalan aktif untuk karyawan ini (Opsi A)
        AttendanceMain::firstOrCreate(
            ['NIK' => $user->Emp_ID, 'Periode' => $currentPeriode],
            [
                'Task_ID'      => 'ATT-' . $user->Emp_ID . '-' . $currentPeriode . '-000001',
                'Status'       => 'CREATED',
                'Created_By'   => $user->username,
                'Created_Date' => now(),
            ]
        );

        echo "<rows>";

        $query = AttendanceMain::where('NIK', $user->Emp_ID)
            ->where('Status', '!=', 'CANCELED')
            ->orderBy('Periode', 'DESC')
            ->get();

        foreach ($query as $row) {
            echo "<row id=\"{$row->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($row->Task_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars($user->Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Periode) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Created_By) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Created_Date) . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Web: halaman daftar harian absensi per periode
     */
    public function entryLists(Request $request)
    {
        return view('humanresources.attendant.attendant-entry-lists', [
            'taskId' => $request->ID,
        ]);
    }

    /**
     * XML: sumber data grid harian per periode
     *
     * Memindahkan logika dari blade xml/attendant-entry-lists.blade.php
     * - Menggabungkan libur nasional, libur weekend, dan data absensi aktual
     * - Default: 08:00–17:00, shift 1 aktif
     */
    public function entryListsXml(Request $request)
    {
        $this->sendXmlHeaders();
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $libur = $this->getHolidayMap();
        $user = Auth::user();
        $nomor = 1;

        $main = AttendanceMain::where('Task_ID', $request->ID)->first();
        if (!$main) {
            echo "<rows></rows>";
            return;
        }

        $existing = Attendance::where('Task_ID', $request->ID)
            ->orderBy('Scan_Date', 'asc')
            ->get();

        $dx = [];
        foreach ($existing as $row) {
            $dx[$row->Scan_Date] = [
                'ID'          => $row->ID,
                'Start_Time'  => $row->Start_Time,
                'End_Time'    => $row->End_Time,
                'Created_By'  => $row->Created_By,
                'Created_Date'=> $row->Created_Date,
                'Shift_1'     => $row->Shift_1,
                'Shift_2'     => $row->Shift_2,
                'Shift_3'     => $row->Shift_3,
            ];
        }

        $periode = $main->Periode;
        $date = substr($periode, 0, 4) . '-' . substr($periode, -2) . '-01';
        $endDate = date('Y-m-t', strtotime($date));

        echo "<rows>";

        while (strtotime($date) <= strtotime($endDate)) {
            $ada = $dx[$date] ?? null;
            $dayname = date('D', strtotime($date));
            $daynum = date('N', strtotime($date));
            $isHoliday = $libur['dates'][$date] ?? false;

            if ($isHoliday) {
                $kategori = 'LIBUR - ' . $libur['names'][$date];
            } elseif (in_array($daynum, [6, 7])) {
                $kategori = 'LIBUR WEEKEND';
            } else {
                $kategori = '';
            }

            if ($ada) {
                $jammulai  = $ada['Start_Time'];
                $jamend    = $ada['End_Time'];
                $dibuatoleh = $ada['Created_By'];
                $dibuattgl  = $ada['Created_Date'];
                $shift1     = $ada['Shift_1'] + 0;
                $shift2     = $ada['Shift_2'] + 0;
                $shift3     = $ada['Shift_3'] + 0;
                $idnya      = $ada['ID'];
                $eksis      = 1;
            } else {
                $jammulai    = '08:00';
                $jamend      = '17:00';
                $dibuatoleh  = $user->username;
                $dibuattgl   = date('c');
                $shift1 = 1;
                $shift2 = 0;
                $shift3 = 0;
                $idnya   = 'non' . $nomor;
                $eksis   = 0;
            }

            echo "<row id=\"{$idnya}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($date) . "</cell>";
            echo "<cell>" . htmlspecialchars($dayname) . "</cell>";
            echo "<cell>" . htmlspecialchars($kategori) . "</cell>";
            echo "<cell>" . htmlspecialchars($jammulai) . "</cell>";
            echo "<cell>" . htmlspecialchars($jamend) . "</cell>";
            echo "<cell>" . htmlspecialchars($shift1) . "</cell>";
            echo "<cell>" . htmlspecialchars($shift2) . "</cell>";
            echo "<cell>" . htmlspecialchars($shift3) . "</cell>";
            echo "<cell>{$eksis}</cell>";
            echo "</row>";

            $nomor++;
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        echo "</rows>";
    }

    /**
     * Web: halaman detail attendance (read-only lists)
     */
    public function dataLists()
    {
        return view('humanresources.attendant.attendant-data-lists');
    }

    /**
     * XML: sumber data grid halaman data lists
     */
    public function dataListsXml()
    {
        $this->sendXmlHeaders();
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $user = Auth::user();
        $nomor = 1;

        echo "<rows>";

        $query = AttendanceMain::where('NIK', $user->Emp_ID)
            ->orderBy('ID', 'DESC')
            ->get();

        foreach ($query as $row) {
            echo "<row id=\"{$row->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($row->Task_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars($user->Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Periode) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Created_By) . "</cell>";
            echo "<cell>" . htmlspecialchars($row->Created_Date) . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * POST: update data absensi absensi harian (dhtmlx dataProcessor)
     *
     * Memindahkan logika dari blade xmL/view-attendant-entry.blade.php
     */
    public function entryUpdate(Request $request)
    {
        $this->sendXmlHeaders();
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $input = $this->parseGridInput();
        $checkIds = explode(',', $input['ids'] ?? '');
        $user = Auth::user();
        $username = $user->username;

        echo "<data>";

        foreach ($checkIds as $rowId) {
            if ($rowId === '') continue;

            $mode = $input[$rowId . '_!nativeeditor_status'] ?? '';
            $date    = $input[$rowId . '_c1'] ?? '';
            $category = $input[$rowId . '_c4'] ?? '';

            switch ($mode) {
                case 'updated':
                    $action = $this->doUpdateEntry($rowId, $input, $username);
                    break;
                case 'deleted':
                    $action = $this->doDeleteEntry($rowId);
                    break;
                case 'inserted':
                    $action = $this->doInsertEntry($input, $date);
                    break;
                default:
                    $action = 'update';
            }

            echo "<action type='{$action}' sid='{$rowId}' tid='{$rowId}'/>";
        }

        echo "</data>";
    }

    /**
     * POST: update data attendance lists
     */
    public function entryListsUpdate(Request $request)
    {
        $this->sendXmlHeaders();
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $input = $this->parseGridInput();
        $checkIds = explode(',', $input['ids'] ?? '');

        echo "<data>";

        foreach ($checkIds as $rowId) {
            if ($rowId === '') continue;

            $mode = $input[$rowId . '_!nativeeditor_status'] ?? '';

            switch ($mode) {
                case 'updated':
                    $action = $this->doUpdateEntry($rowId, $input, Auth::user()->username);
                    break;
                case 'deleted':
                    $action = $this->doDeleteEntry($rowId);
                    break;
                case 'inserted':
                    $action = $this->doInsertEntry($input, $input[$rowId . '_c1'] ?? '');
                    break;
                default:
                    $action = 'update';
            }

            echo "<action type='{$action}' sid='{$rowId}' tid='{$rowId}'/>";
        }

        echo "</data>";
    }

    // ─── PARSER ──────────────────────────────────────────────

    /**
     * Parse url-encoded dhtmlx dataProcessor input into associative array
     */
    private function parseGridInput(): array
    {
        $body = urldecode(file_get_contents('php://input'));
        $pairs = explode('&', $body);
        $param = [];
        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $param[$parts[0]] = $parts[1];
            }
        }
        return $param;
    }

    /**
     * Load holiday map from libur table
     */
    private function getHolidayMap(): array
    {
        $hrilibur = DB::table('libur')->orderBy('libur_tgl')->get();
        $dates = [];
        $names = [];
        foreach ($hrilibur as $row) {
            $dates[$row->libur_tgl] = 1;
            $names[$row->libur_tgl] = $row->libur_keterangan;
        }
        return ['dates' => $dates, 'names' => $names];
    }

    /**
     * Update an existing attendance entry
     */
    private function doUpdateEntry(string $rowId, array $param, string $username): string
    {
        $scanDate = $param[$rowId . '_c1'] ?? '';
        $category = $param[$rowId . '_c3'] ?? ($param[$rowId . '_c4'] ?? '');
        $startTime = $param[$rowId . '_c4'] ?? ($param[$rowId . '_c5'] ?? '');
        $endTime = $param[$rowId . '_c5'] ?? ($param[$rowId . '_c6'] ?? '');
        $shift1 = (int) ($param[$rowId . '_c6'] ?? ($param[$rowId . '_c7'] ?? 0));
        $shift2 = (int) ($param[$rowId . '_c7'] ?? ($param[$rowId . '_c8'] ?? 0));
        $shift3 = (int) ($param[$rowId . '_c8'] ?? ($param[$rowId . '_c9'] ?? 0));

        Attendance::where('ID', $rowId)->update([
            'Scan_Date'   => $scanDate,
            'Category'    => $category,
            'Start_Time'  => $startTime,
            'End_Time'    => $endTime,
            'Shift_1'     => $shift1,
            'Shift_2'     => $shift2,
            'Shift_3'     => $shift3,
            'Created_By'  => $username,
        ]);

        return 'update';
    }

    /**
     * Delete an attendance entry
     */
    private function doDeleteEntry(string $rowId): string
    {
        AttendanceMain::where('ID', $rowId)->delete();
        return 'delete';
    }

    /**
     * Insert a new attendance entry from a periode
     */
    private function doInsertEntry(array $param, string $date): string
    {
        $user = Auth::user();
        $username = $user->username;
        $nik = $user->Emp_ID;

        $currentPeriode = (int) date('Ym');
        $catCount = AttendanceMain::where('NIK', $nik)->count() + 1;
        $taskId = 'ATT-' . $nik . '-' . $currentPeriode . '-' . sprintf('%06d', $catCount);

        // 1. Buat header di attendant_log_main agar tampil di tabel periode utama
        $main = AttendanceMain::firstOrCreate(
            ['NIK' => $nik, 'Periode' => $currentPeriode],
            [
                'Task_ID'      => $taskId,
                'Status'       => 'CREATED',
                'Created_By'   => $username,
                'Created_Date' => now(),
            ]
        );

        // 2. Buat log hari pertama jika ada parameter tanggal
        if (!empty($date)) {
            DB::table('attendant_log')->insert([
                'Task_ID'      => $main->Task_ID,
                'NIK'          => $nik,
                'Periode'      => $currentPeriode,
                'Scan_Date'    => $date,
                'Category'     => $param['Task_ID_c4'] ?? 'KERJA',
                'Start_Time'   => $param['Task_ID_c5'] ?? '08:00:00',
                'End_Time'     => $param['Task_ID_c6'] ?? '17:00:00',
                'Created_Date' => now(),
                'Shift_1'      => (int) ($param['Task_ID_c7'] ?? 1),
                'Shift_2'      => (int) ($param['Task_ID_c8'] ?? 0),
                'Shift_3'      => (int) ($param['Task_ID_c9'] ?? 0),
                'Created_By'   => $username,
            ]);
        }

        return 'insert';
    }

    /**
     * Send XML content-type headers
     */
    private function sendXmlHeaders(): void
    {
        if (stristr($_SERVER['HTTP_ACCEPT'] ?? '', 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
    }

    // ─── API JSON (Mobile) ───────────────────────────────────

    /**
     * GET /api/attendance/list
     * Daftar periode absensi user yang terautentikasi
     */
    public function apiList(Request $request)
    {
        $user = $request->user();

        $periode = $request->query('periode');
        $status = $request->query('status');

        $query = AttendanceMain::where('NIK', $user->Emp_ID);

        if ($periode) {
            $query->where('Periode', $periode);
        }
        if ($status) {
            $query->where('Status', $status);
        }

        $data = $query->orderBy('ID', 'DESC')
            ->get()
            ->map(function ($row) {
                return [
                    'id'          => (int) $row->ID,
                    'task_id'     => $row->Task_ID,
                    'nik'         => $row->NIK,
                    'periode'     => $row->Periode,
                    'status'      => $row->Status,
                    'created_by'  => $row->Created_By,
                    'created_at'  => $row->Created_Date,
                ];
            });

        return response()->json([
            'status'  => 'success',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/attendance/{periode}/entries
     * Daftar absensi harian untuk sebuah periode
     */
    public function apiEntriesByPeriode(Request $request, string $periode)
    {
        $user = $request->user();
        $libur = $this->getHolidayMap();

        $main = AttendanceMain::where('NIK', $user->Emp_ID)
            ->where('Periode', $periode)
            ->first();

        if (!$main) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Periode absensi tidak ditemukan',
            ], 404);
        }

        $existing = Attendance::where('Task_ID', $main->Task_ID)
            ->orderBy('Scan_Date', 'asc')
            ->get()
            ->keyBy('Scan_Date');

        $entries = [];
        $date = substr($periode, 0, 4) . '-' . substr($periode, -2) . '-01';
        $endDate = date('Y-m-t', strtotime($date));
        $counter = 1;

        while (strtotime($date) <= strtotime($endDate)) {
            $row = $existing[$date] ?? null;
            $daynum = (int) date('N', strtotime($date));
            $isHoliday = $libur['dates'][$date] ?? false;

            if ($isHoliday) {
                $category = 'LIBUR - ' . $libur['names'][$date];
            } elseif (in_array($daynum, [6, 7])) {
                $category = 'LIBUR WEEKEND';
            } else {
                $category = 'KERJA';
            }

            $entries[] = [
                'date'         => $date,
                'day_name'     => date('D', strtotime($date)),
                'category'     => $category,
                'start_time'   => $row ? $row->Start_Time : '08:00:00',
                'end_time'     => $row ? $row->End_Time : '17:00:00',
                'shift_1'      => $row ? (int) $row->Shift_1 : 1,
                'shift_2'      => $row ? (int) $row->Shift_2 : 0,
                'shift_3'      => $row ? (int) $row->Shift_3 : 0,
                'created_by'   => $row ? $row->Created_By : null,
                'created_at'   => $row ? $row->Created_Date : null,
                'is_recorded'  => $row !== null,
                'entry_id'     => $row ? (int) $row->ID : null,
            ];

            $counter++;
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        return response()->json([
            'status'    => 'success',
            'task_id'   => $main->Task_ID,
            'periode'   => $main->Periode,
            'status'    => $main->Status,
            'entries'   => $entries,
        ]);
    }

    /**
     * POST /api/attendance/scan
     * Clock-in / clock-out / update shift untuk hari ini
     */
    public function apiScan(Request $request)
    {
        $user = $request->user();
        $nik = $user->Emp_ID;

        $request->validate([
            'date'      => 'required|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'shift_1'    => 'nullable|integer',
            'shift_2'    => 'nullable|integer',
            'shift_3'    => 'nullable|integer',
            'category'   => 'nullable|string|max:100',
        ]);

        $today = $request->input('date');
        $currentPeriode = (int) date('Ym', strtotime($today));

        $main = AttendanceMain::firstOrCreate(
            ['NIK' => $nik, 'Periode' => $currentPeriode],
            [
                'Task_ID'    => $this->generateTaskId($nik, $currentPeriode),
                'Status'     => 'CREATED',
                'Created_By' => $user->username,
            ]
        );

        $data = [
            'Task_ID'    => $main->Task_ID,
            'NIK'        => $nik,
            'Periode'    => $currentPeriode,
            'Scan_Date'  => $today,
            'Category'   => $request->input('category', 'LIBUR' . ' ' . date('D', strtotime($today))),
            'Start_Time' => $request->input('start_time'),
            'End_Time'   => $request->input('end_time'),
            'Shift_1'    => $request->input('shift_1', 1),
            'Shift_2'    => $request->input('shift_2', 0),
            'Shift_3'    => $request->input('shift_3', 0),
            'Created_By' => $user->username,
        ];

        $existing = Attendance::where('Task_ID', $main->Task_ID)
            ->where('Scan_Date', $today)
            ->first();

        if ($existing) {
            $existing->update($data);
            $entry = $existing;
            $message = 'Absensi berhasil diperbarui';
        } else {
            $entry = Attendance::create($data);
            $message = 'Absensi berhasil dicatat';
        }

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => [
                'entry_id'   => (int) $entry->ID,
                'date'       => $entry->Scan_Date,
                'start_time' => $entry->Start_Time,
                'end_time'   => $entry->End_Time,
                'shift_1'    => (int) $entry->Shift_1,
                'shift_2'    => (int) $entry->Shift_2,
                'shift_3'    => (int) $entry->Shift_3,
            ],
        ], 201);
    }

    /**
     * GET /api/attendance/scan/today
     * Cek apakah user sudah absen hari ini
     */
    public function apiTodayScan(Request $request)
    {
        $user = $request->user();
        $nik = $user->Emp_ID;
        $today = date('Y-m-d');

        $main = AttendanceMain::where('NIK', $nik)
            ->where('Periode', (int) date('Ym'))
            ->first();

        if (!$main) {
            return response()->json([
                'status'       => 'success',
                'has_attended' => false,
            ]);
        }

        $entry = Attendance::where('Task_ID', $main->Task_ID)
            ->where('Scan_Date', $today)
            ->first();

        if (!$entry) {
            return response()->json([
                'status'       => 'success',
                'has_attended' => false,
            ]);
        }

        return response()->json([
            'status'       => 'success',
            'has_attended' => true,
            'data'         => [
                'entry_id'    => (int) $entry->ID,
                'date'        => $entry->Scan_Date,
                'start_time'  => $entry->Start_Time,
                'end_time'    => $entry->End_Time,
                'category'    => $entry->Category,
                'shift_1'     => (int) $entry->Shift_1,
                'shift_2'     => (int) $entry->Shift_2,
                'shift_3'     => (int) $entry->Shift_3,
                'task_id'     => $entry->Task_ID,
            ],
        ]);
    }

    /**
     * PUT /api/attendance/scan/{id}
     * Update absensi yang sudah ada
     */
    public function apiUpdateScan(Request $request, int $id)
    {
        $user = $request->user();

        $request->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i',
            'shift_1'    => 'nullable|integer',
            'shift_2'    => 'nullable|integer',
            'shift_3'    => 'nullable|integer',
            'category'   => 'nullable|string|max:100',
        ]);

        $entry = Attendance::where('ID', $id)
            ->where('NIK', $user->Emp_ID)
            ->first();

        if (!$entry) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data absensi tidak ditemukan',
            ], 404);
        }

        $entry->update([
            'Start_Time' => $request->input('start_time'),
            'End_Time'   => $request->input('end_time'),
            'Shift_1'    => $request->input('shift_1', $entry->Shift_1),
            'Shift_2'    => $request->input('shift_2', $entry->Shift_2),
            'Shift_3'    => $request->input('shift_3', $entry->Shift_3),
            'Category'   => $request->input('category', $entry->Category),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Absensi berhasil diperbarui',
            'data'    => [
                'entry_id'   => (int) $entry->ID,
                'date'       => $entry->Scan_Date,
                'start_time' => $entry->Start_Time,
                'end_time'   => $entry->End_Time,
                'category'   => $entry->Category,
                'shift_1'    => (int) $entry->Shift_1,
                'shift_2'    => (int) $entry->Shift_2,
                'shift_3'    => (int) $entry->Shift_3,
            ],
        ]);
    }

    /**
     * Generate a unique Task_ID for a new periode
     */
    private function generateTaskId(string $nik, int $periode): string
    {
        $prefix = 'ATT-' . $nik . '-' . $periode . '-';
        $last = AttendanceMain::where('NIK', $nik)
            ->where('Periode', $periode)
            ->max('ID');

        return $prefix . sprintf('%06d', ($last ?? 0) + 1);
    }

    /**
     * API: Clock-In Karyawan dengan validasi Geofence & Foto
     */
    public function apiClockIn(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo'     => 'nullable|string',
            'notes'     => 'nullable|string|max:255',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }
            $result = $this->attendanceService->clockIn($user, $request->all());

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Clock-Out Karyawan dengan kalkulasi Lembur / Early Leave
     */
    public function apiClockOut(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo'     => 'nullable|string',
            'notes'     => 'nullable|string|max:255',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }
            $result = $this->attendanceService->clockOut($user, $request->all());

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Dapatkan Ringkasan Timesheet Bulanan
     */
    public function apiTimesheet(Request $request)
    {
        $user = $request->user();
        $nik = $user->Emp_ID;
        $month = $request->input('month', date('Y-m')); // e.g. 2026-11
        $periode = (int) date('Ym', strtotime($month . '-01'));

        $main = AttendanceMain::where('NIK', $nik)->where('Periode', $periode)->first();
        $entries = [];

        if ($main) {
            $entries = Attendance::where('Task_ID', $main->Task_ID)
                ->orderBy('Scan_Date', 'asc')
                ->get();
        }

        return response()->json([
            'status'  => 'success',
            'month'   => $month,
            'periode' => $periode,
            'data'    => $entries,
        ]);
    }

    /**
     * API: Pengajuan Izin/Cuti
     */
    public function apiSubmitLeave(Request $request)
    {
        $request->validate([
            'leave_type'          => 'required|string|max:100',
            'start_date'          => 'required|date_format:Y-m-d',
            'end_date'            => 'required|date_format:Y-m-d',
            'clock_in_time'       => 'nullable|string',
            'clock_out_time'      => 'nullable|string',
            'reason'              => 'required|string',
            'attachment'          => 'nullable',
            'attachment_file'     => 'nullable|file|max:5120',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }
            $uploaded = $request->hasFile('attachment_file') ? $request->file('attachment_file') : null;
            $result = $this->attendanceService->submitLeaveRequest($user, $request->all(), $uploaded);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API: Pengajuan Koreksi Absensi
     */
    public function apiSubmitCorrection(Request $request)
    {
        $request->validate([
            'attendance_date'     => 'required|date_format:Y-m-d',
            'correction_type'     => 'nullable|string|in:BOTH,IN,OUT',
            'corrected_clock_in'  => 'nullable|string',
            'corrected_clock_out' => 'nullable|string',
            'reason'              => 'required|string',
            'evidence_attachment' => 'nullable|file|max:5120',
            'evidence_file'       => 'nullable',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }
            $uploaded = $request->hasFile('evidence_attachment') ? $request->file('evidence_attachment') : null;
            $result = $this->attendanceService->submitCorrectionRequest($user, $request->all(), $uploaded);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API / Web: Batalkan Pengajuan Izin / Cuti Karyawan Mandiri
     */
    public function apiCancelLeave(Request $request)
    {
        $request->validate([
            'leave_id'      => 'required|integer',
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        try {
            $user = $request->user() ?: Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir.',
                ], 401);
            }

            $result = $this->attendanceService->cancelLeaveRequest(
                $user,
                (int) $request->input('leave_id'),
                (string) $request->input('cancel_reason', '')
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Webview Mobile: Halaman Utama Presensi & Clock-in/out
     */
    public function mobileIndex(Request $request)
    {
        $user = Auth::user();
        $today = date('Y-m-d');
        $periode = (int) date('Ym');

        // Cari log hari ini langsung berdasarkan NIK dan Scan_Date agar selalu sinkron
        $todayEntry = Attendance::where('NIK', $user->Emp_ID)
            ->where('Scan_Date', $today)
            ->first();

        // Fallback jika hanya terikat ke Task_ID
        if (!$todayEntry) {
            $main = AttendanceMain::where('NIK', $user->Emp_ID)->where('Periode', $periode)->first();
            if ($main) {
                $todayEntry = Attendance::where('Task_ID', $main->Task_ID)->where('Scan_Date', $today)->first();
            }
        }

        $schedule = $this->attendanceService->getWorkScheduleForDate($user->Emp_ID, $today);
        $locations = DB::table('attendance_locations')->where('status', 'ACTIVE')->get();

        return view('humanresources.attendant.mobile.index', compact('user', 'todayEntry', 'schedule', 'locations'));
    }

    /**
     * Webview Mobile: Halaman Timesheet & Kalender Mandiri
     */
    public function mobileTimesheet(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', date('Y-m'));
        $periode = (int) date('Ym', strtotime($month . '-01'));

        $main = AttendanceMain::where('NIK', $user->Emp_ID)->where('Periode', $periode)->first();
        $entries = [];
        if ($main) {
            $entries = Attendance::where('Task_ID', $main->Task_ID)->orderBy('Scan_Date', 'asc')->get();
        }

        return view('humanresources.attendant.mobile.timesheet', compact('user', 'entries', 'month'));
    }

    /**
     * Webview Mobile: Form Pengajuan Izin / Cuti
     */
    public function mobileLeaveForm()
    {
        $user = Auth::user();
        $leaves = DB::table('attendance_leave_requests')
            ->where('nik', $user->Emp_ID)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('humanresources.attendant.mobile.leave-form', compact('user', 'leaves'));
    }
}
