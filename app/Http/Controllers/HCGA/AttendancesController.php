<?php

namespace App\Http\Controllers\HCGA;

use App\Attendance;
use App\AttendanceMain;
use App\Http\Controllers\Controller;
use App\Libur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendancesController extends Controller
{
    /**
     * Web: daftar periode absensi utama (halaman grid utama)
     */
    public function index()
    {
        return view('humanresources.attendant.data');
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

        echo "<rows>";

        $query = AttendanceMain::where('NIK', $user->Emp_ID)
            ->where('Status', '!=', 'CANCELED')
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
        $category = $param[$rowId . '_c4'] ?? '';
        $startTime = $param[$rowId . '_c5'] ?? '';
        $endTime = $param[$rowId . '_c6'] ?? '';
        $shift1 = (int) ($param[$rowId . '_c7'] ?? 0);
        $shift2 = (int) ($param[$rowId . '_c8'] ?? 0);
        $shift3 = (int) ($param[$rowId . '_c9'] ?? 0);

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

        $catCount = AttendanceMain::where('NIK', $nik)->count() + 1;
        $pid = 'ATT-' . $nik . '-' . sprintf('%06d', $catCount);

        DB::table('attendant_log')->insert([
            'Task_ID'      => $pid,
            'NIK'          => $nik,
            'Periode'      => substr($date, 0, 7),
            'Scan_Date'    => $date,
            'Category'     => $param['Task_ID_c4'] ?? null,
            'Start_Time'   => $param['Task_ID_c5'] ?? null,
            'End_Time'     => $param['Task_ID_c6'] ?? null,
            'Created_Date' => now(),
            'Shift_1'      => (int) ($param['Task_ID_c7'] ?? 1),
            'Shift_2'      => (int) ($param['Task_ID_c8'] ?? 0),
            'Shift_3'      => (int) ($param['Task_ID_c9'] ?? 0),
            'Created_By'   => $username,
        ]);

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
}
