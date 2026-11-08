<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Attendance\OvertimeLegacySyncService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OvertimeLegacyController extends Controller
{
    protected $legacySyncService;
    protected $workflowService;

    public function __construct(
        OvertimeLegacySyncService $legacySyncService,
        ApprovalWorkflowService $workflowService
    ) {
        $this->legacySyncService = $legacySyncService;
        $this->workflowService = $workflowService;
    }

    /**
     * Helper untuk mendapatkan daftar opsi filter karyawan (Departemen, Posisi, Site/Location)
     */
    public function getFilterCriteria(Request $request)
    {
        $departments = DB::table('employment')
            ->where('Status', 'ACTIVE')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->distinct()
            ->orderBy('Department', 'ASC')
            ->pluck('Department');

        $positions = DB::table('employment')
            ->where('Status', 'ACTIVE')
            ->whereNotNull('Position')
            ->where('Position', '!=', '')
            ->distinct()
            ->orderBy('Position', 'ASC')
            ->pluck('Position');

        $locations = DB::table('employment')
            ->where('Status', 'ACTIVE')
            ->whereNotNull('Location')
            ->where('Location', '!=', '')
            ->distinct()
            ->orderBy('Location', 'ASC')
            ->pluck('Location');

        return response()->json([
            'status'      => 'success',
            'departments' => $departments,
            'positions'   => $positions,
            'locations'   => $locations,
        ]);
    }

    /**
     * Dapatkan daftar karyawan berdasarkan kriteria (Departemen, Posisi, Site/Location)
     */
    public function getEmployeesByCriteria(Request $request)
    {
        $dept     = $request->query('department');
        $position = $request->query('position');
        $location = $request->query('location');

        $query = DB::table('employment')
            ->where('Status', 'ACTIVE');

        if (!empty($dept) && $dept !== 'ALL') {
            $query->where('Department', $dept);
        }

        if (!empty($position) && $position !== 'ALL') {
            $query->where('Position', $position);
        }

        if (!empty($location) && $location !== 'ALL') {
            $query->where('Location', $location);
        }

        $employees = $query->orderBy('Name', 'ASC')
            ->select('ID', 'NIK', 'Name', 'Department', 'Position', 'Location', 'Salary_Group', 'Salary_Accepted')
            ->get();

        // Hitung rate & basic salary per karyawan
        $result = [];
        foreach ($employees as $emp) {
            $rateInfo = $this->resolveEmployeeSalaryAndRate($emp->NIK);
            $result[] = [
                'ID'              => $emp->ID,
                'NIK'             => $emp->NIK,
                'Name'            => $emp->Name,
                'Department'      => $emp->Department ?? '-',
                'Position'        => $emp->Position ?? '-',
                'Location'        => $emp->Location ?? 'HO',
                'Salary_Group'    => $emp->Salary_Group ?? ($rateInfo['salary_group'] ?? ''),
                'Basic_Salary'    => $rateInfo['basic_salary'],
                'Overtime_Rate'   => $rateInfo['rate'],
            ];
        }

        return response()->json([
            'status' => 'success',
            'count'  => count($result),
            'data'   => $result,
        ]);
    }

    /**
     * Endpoint API untuk menghitung realtime estimasi jam & nominal lembur
     * Berdasarkan UU No 11/2020 Cipta Kerja & PP No 35 Tahun 2021
     */
    public function calculateOvertimeRealtime(Request $request)
    {
        $nik        = $request->input('nik', '');
        $startDate  = $request->input('start_date', date('Y-m-d'));
        $endDate    = $request->input('end_date', date('Y-m-d'));
        $earlyIn    = $request->input('early_in', '00:00:00');
        $earlyOut   = $request->input('early_out', '00:00:00');
        $afterIn    = $request->input('after_in', '17:00:00');
        $afterOut   = $request->input('after_out', '21:00:00');
        $customRate = $request->input('rate', null);

        $calc = $this->calculateOvertimeHoursAndAmount($nik, $startDate, $endDate, $earlyIn, $earlyOut, $afterIn, $afterOut, $customRate);

        return response()->json([
            'status' => 'success',
            'data'   => $calc,
        ]);
    }

    /**
     * Resolusi Gaji Pokok & Tarif Lembur per Jam (1/173 x Gaji Pokok / Komponen Upah Tetap)
     * Sesuai PP No 35 Tahun 2021 Pasal 32
     */
    public function resolveEmployeeSalaryAndRate($nik): array
    {
        $emp = DB::table("employment")->where("NIK", $nik)->first();
        $salgroup = $emp ? ($emp->Salary_Group ?? '') : '';
        $jumlahuang = 0;

        // 1. Ambil dari payrol_group_lists jika ada
        if (!empty($salgroup)) {
            $qxx = DB::table("payrol_group_lists")
                ->where("Main_ID", $salgroup)
                ->where("Group_ID", $nik)
                ->where("Kena_Potongan", 1)
                ->get();

            foreach ($qxx as $mx) {
                $jumlahuang += (float) $mx->Amounts;
            }
        }

        // 2. Jika payrol_group_lists kosong atau 0, cek Salary_Accepted di employment
        if ($jumlahuang <= 0 && $emp && !empty($emp->Salary_Accepted) && (float) $emp->Salary_Accepted > 0) {
            $jumlahuang = (float) $emp->Salary_Accepted;
        }

        // 3. Jika masih kosong, coba cari di payrol_group_lists hanya berdasarkan Group_ID = NIK
        if ($jumlahuang <= 0) {
            $qFallback = DB::table("payrol_group_lists")
                ->where("Group_ID", $nik)
                ->where("Kena_Potongan", 1)
                ->get();

            foreach ($qFallback as $fb) {
                $jumlahuang += (float) $fb->Amounts;
                if (empty($salgroup)) {
                    $salgroup = $fb->Main_ID;
                }
            }
        }

        // 4. Default standar UMR / batas wajar jika belum diisi agar perhitungan tidak bernilai 0
        if ($jumlahuang <= 0) {
            $jumlahuang = 4500000; // Default UMR baseline
        }

        // Rumus PP 35/2021 Pasal 32: Upah Sejam = 1/173 x Upah Sebulan
        $rate = ceil($jumlahuang / 173);

        return [
            'basic_salary' => $jumlahuang,
            'rate'         => $rate,
            'salary_group' => $salgroup,
        ];
    }
    public function xmlOvertimeNew(Request $request)
    {
        $user = Auth::user();
        $username = $user ? $user->username : '';
        $nik = $user ? $user->Emp_ID : '';
        $taskId = $request->query('taskid');

        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }

        echo "<?xml version='1.0' encoding='utf-8'?>\n";
        echo "<rows>";

        $query = DB::table("overtime_lists")
            ->where(function ($q) use ($username, $nik, $taskId) {
                if (!empty($taskId)) {
                    $q->where('Task_ID', 'like', $taskId . '%');
                } else {
                    $q->where("Created_By", $username)
                      ->orWhere("NIK", $nik);
                }
            })
            ->whereIn("Submit_Approval", [0, 1, 2])
            ->orderBy("Start_Date", "DESC")
            ->limit(100)
            ->get();

        $nomor = 1;
        foreach ($query as $fetch) {
            echo "<row id=\"$fetch->ID\">";
            echo "<cell>" . $nomor . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->NIK ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Start_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->End_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Early_IN ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Early_OUT ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->After_IN ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->After_OUT ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Holiday ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Total_OT ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Calculated_OT ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->IS_Outdated ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Salary_Period ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Amounts ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Reference ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Note ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Salary_Group ?? '') . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
        exit;
    }

    /**
     * XML Data untuk All Overtime Data (Laporan / Monitoring Semua Karyawan)
     */
    public function xmlOvertimeAll(Request $request)
    {
        $nik = $request->query('NIK');
        $tgl1 = $request->query('Tgl1', date('Y-m-d'));
        $tgl2 = $request->query('Tgl2', date('Y-m-d'));

        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }

        echo "<?xml version='1.0' encoding='utf-8'?>\n";
        echo "<rows>";

        $queryBuilder = DB::table("overtime_lists")
            ->whereBetween('Start_Date', [$tgl1, $tgl2])
            ->orderBy("Start_Date", "DESC");

        if (!empty($nik)) {
            $queryBuilder->where("NIK", $nik);
        }

        $query = $queryBuilder->limit(500)->get();

        $nomor = 1;
        foreach ($query as $fetch) {
            echo "<row id=\"$fetch->ID\">";
            echo "<cell>" . $nomor . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->NIK ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Start_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->End_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Early_IN ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Early_OUT ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->After_IN ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->After_OUT ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Holiday ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Total_OT ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Calculated_OT ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->IS_Outdated ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Salary_Period ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Amounts ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Reference ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Note ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Salary_Group ?? '') . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
        exit;
    }

    /**
     * XML Data untuk Overtime Approval (Pending Submission)
     */
    public function xmlOvertimeApproval(Request $request)
    {
        $tgl1 = $request->query('Tgl1', date('Y-m-d'));
        $tgl2 = $request->query('Tgl2', date('Y-m-d'));

        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }

        echo "<?xml version='1.0' encoding='utf-8'?>\n";
        echo "<rows>";

        // Menampilkan yang Submit_Approval in [0, 2] (Pending Approval)
        $query = DB::table("overtime_lists")
            ->whereBetween('Start_Date', [$tgl1, $tgl2])
            ->where(function ($q) {
                $q->where('Submit_Approval', 0)
                  ->orWhere('Submit_Approval', 2);
            })
            ->orderBy("Start_Date", "DESC")
            ->get();

        foreach ($query as $fetch) {
            echo "<row id=\"$fetch->ID\">";
            echo "<cell>0</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->NIK ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Start_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->End_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Early_IN ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Early_OUT ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->After_IN ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->After_OUT ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Holiday ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Total_OT ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Calculated_OT ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->IS_Outdated ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Salary_Period ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Amounts ?? '0') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Reference ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->File_Path ?? '') . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
        exit;
    }

    /**
     * Handler Simpan & Submit Overtime dari dhtmlXGrid New Overtime
     */
    public function updateNew(Request $request)
    {
        header("Content-type: text/xml");
        echo "<?xml version='1.0' encoding='utf-8'?>";
        echo "<data>";

        $requestBody = urldecode(file_get_contents('php://input'));
        $user = Auth::user();
        $username = $user ? $user->username : 'admin';
        $location = $user ? ($user->Location ?? 'HO') : 'HO';

        $params = [];
        $items = explode("&", $requestBody);
        foreach ($items as $item) {
            $kv = explode("=", $item);
            if (isset($kv[0])) {
                $params[$kv[0]] = $kv[1] ?? '';
            }
        }

        $checkIds = isset($params["ids"]) ? explode(",", $params["ids"]) : [];

        foreach ($checkIds as $rowId) {
            if (empty($rowId)) continue;

            $mode = $params[$rowId . "_!nativeeditor_status"] ?? 'updated';
            $action = 'update';

            if ($mode === 'deleted') {
                DB::table('overtime_lists')->where('ID', $rowId)->delete();
                $action = 'delete';
            } elseif ($mode === 'inserted' || $mode === 'updated') {
                $statusFlag = $params[$rowId . "_c0"] ?? '';
                $taskId     = $params[$rowId . "_c1"] ?? '';
                $nik        = $params[$rowId . "_c2"] ?? '';
                $name       = $params[$rowId . "_c3"] ?? '';
                $startDate  = $params[$rowId . "_c4"] ?? date('Y-m-d');
                $endDate    = $params[$rowId . "_c5"] ?? date('Y-m-d');
                $earlyIn    = $params[$rowId . "_c6"] ?? '00:00:00';
                $earlyOut   = $params[$rowId . "_c7"] ?? '00:00:00';
                $afterIn    = $params[$rowId . "_c8"] ?? '17:00:00';
                $afterOut   = $params[$rowId . "_c9"] ?? '21:00:00';
                $rateCustom = isset($params[$rowId . "_c13"]) && is_numeric($params[$rowId . "_c13"]) ? (float)$params[$rowId . "_c13"] : null;
                $salPeriod  = $params[$rowId . "_c14"] ?? '';
                $reference  = $params[$rowId . "_c16"] ?? '';
                $note       = $params[$rowId . "_c17"] ?? 'Lembur operasional';
                $salGroup   = $params[$rowId . "_c18"] ?? '';

                // Hitung jam & nominal sesuai UU Cipta Kerja / PP No 35 Tahun 2021
                $calc = $this->calculateOvertimeHoursAndAmount($nik, $startDate, $endDate, $earlyIn, $earlyOut, $afterIn, $afterOut, $rateCustom);

                // Jika statusFlag == 'SB' (Submit Approval), kita set Submit_Approval = 2 (Pending Approval)
                // dan daftarkan ke Dynamic Approval Engine!
                $submitApproval = ($statusFlag === 'SB') ? 2 : 0;
                $approvalRequestId = null;

                if ($mode === 'inserted') {
                    $insertedId = DB::table('overtime_lists')->insertGetId([
                        'Task_ID'         => $taskId ?: ('OV/' . $location . '/' . date('ymd') . '/' . sprintf('%04d', mt_rand(1, 9999))),
                        'NIK'             => $nik,
                        'Name'            => $name,
                        'Start_Date'      => $startDate,
                        'End_Date'        => $endDate,
                        'Early_IN'        => $earlyIn,
                        'Early_OUT'       => $earlyOut,
                        'After_IN'        => $afterIn,
                        'After_OUT'       => $afterOut,
                        'Holiday'         => $calc['holiday'],
                        'Total_OT'        => $calc['decimal_hours'],
                        'Calculated_OT'   => $calc['jumlah_jam'],
                        'IS_Outdated'     => $calc['rate'],
                        'Salary_Period'   => $salPeriod,
                        'Amounts'         => $calc['amounts'],
                        'Reference'       => $reference,
                        'Created_Date'    => Carbon::now(),
                        'Created_By'      => $username,
                        'Note'            => $note,
                        'Level1'          => $calc['jam1'],
                        'Level2'          => $calc['jam2'],
                        'Level3'          => $calc['jam3'],
                        'Level4'          => $calc['jam4'],
                        'Submit_Approval' => $submitApproval,
                        'Salary_Group'    => $salGroup ?: $calc['salary_group'],
                    ]);

                    $rowIdToUse = $insertedId;
                    $action = 'insert';
                } else {
                    DB::table('overtime_lists')->where('ID', $rowId)->update([
                        'Start_Date'      => $startDate,
                        'End_Date'        => $endDate,
                        'Early_IN'        => $earlyIn,
                        'Early_OUT'       => $earlyOut,
                        'After_IN'        => $afterIn,
                        'After_OUT'       => $afterOut,
                        'Holiday'         => $calc['holiday'],
                        'Total_OT'        => $calc['decimal_hours'],
                        'Calculated_OT'   => $calc['jumlah_jam'],
                        'IS_Outdated'     => $calc['rate'],
                        'Salary_Period'   => $salPeriod,
                        'Amounts'         => $calc['amounts'],
                        'Reference'       => $reference,
                        'Note'            => $note,
                        'Level1'          => $calc['jam1'],
                        'Level2'          => $calc['jam2'],
                        'Level3'          => $calc['jam3'],
                        'Level4'          => $calc['jam4'],
                        'Submit_Approval' => $submitApproval,
                        'Salary_Group'    => $salGroup ?: $calc['salary_group'],
                    ]);
                    $rowIdToUse = $rowId;
                    $action = 'update';
                }

                // Jika di-submit ('SB'), hubungkan ke Dynamic Approval Engine & attendance_overtime_requests
                if ($statusFlag === 'SB') {
                    $this->submitToDynamicApprovalEngine($rowIdToUse);
                }
            }

            echo "<action type='{$action}' sid='{$rowId}' tid='{$rowId}'/>";
        }

        echo "</data>";
        exit;
    }

    /**
     * Handler Approval Update dari Grid Overtime Approval
     */
    public function updateApproval(Request $request)
    {
        header("Content-type: text/xml");
        echo "<?xml version='1.0' encoding='utf-8'?>";
        echo "<data>";

        $requestBody = urldecode(file_get_contents('php://input'));
        $params = [];
        $items = explode("&", $requestBody);
        foreach ($items as $item) {
            $kv = explode("=", $item);
            if (isset($kv[0])) {
                $params[$kv[0]] = $kv[1] ?? '';
            }
        }

        $checkIds = isset($params["ids"]) ? explode(",", $params["ids"]) : [];
        foreach ($checkIds as $rowId) {
            if (empty($rowId)) continue;

            $checkboxVal = $params[$rowId . "_c0"] ?? 0;
            if ($checkboxVal == 1 || $checkboxVal === '1' || $checkboxVal === 'true' || $checkboxVal === 'On') {
                // Update Submit_Approval di overtime_lists menjadi 1 (Approved)
                DB::table('overtime_lists')->where('ID', $rowId)->update([
                    'Submit_Approval' => 1,
                    'Level1'          => 1,
                ]);

                // Sinkronkan ke attendance_overtime_requests
                $legacyRow = DB::table('overtime_lists')->where('ID', $rowId)->first();
                if ($legacyRow) {
                    $query = DB::table('attendance_overtime_requests');
                    if (!empty($legacyRow->Reference)) {
                        $query->where('overtime_no', $legacyRow->Reference);
                    } else {
                        $query->where('legacy_overtime_id', $rowId);
                    }
                    $query->update([
                        'status'     => 'APPROVED',
                        'updated_by' => Auth::user()->username ?? 'admin',
                        'updated_at' => Carbon::now(),
                    ]);

                    // Jika ada task approval di Dynamic Approval Engine, tandai approved
                    if (!empty($legacyRow->approval_request_id)) {
                        try {
                            $apprTasks = DB::table('approval_request_task')
                                ->where('Request_ID', $legacyRow->approval_request_id)
                                ->whereIn('Status', ['PENDING', 'CLAIMED'])
                                ->get();

                            foreach ($apprTasks as $at) {
                                $this->workflowService->act((int) $at->ID, 'APPROVE', 'Disetujui melalui menu Overtime Approval', (int) (Auth::id() ?? 1));
                            }
                        } catch (\Throwable $e) {
                            Log::warning("Gagal auto-approve Dynamic Approval Task ID {$legacyRow->approval_request_id}: " . $e->getMessage());
                        }
                    }
                }
            }

            echo "<action type='update' sid='{$rowId}' tid='{$rowId}'/>";
        }

        echo "</data>";
        exit;
    }

    /**
     * Submit Record Overtime ke Dynamic Approval Engine & buat/update attendance_overtime_requests
     */
    protected function submitToDynamicApprovalEngine(int $overtimeListId): void
    {
        $ot = DB::table('overtime_lists')->where('ID', $overtimeListId)->first();
        if (!$ot) return;

        $targetUser = DB::table('users')->where('Emp_ID', $ot->NIK)->first();
        $requesterId = $targetUser ? (int) $targetUser->id : (int) (Auth::id() ?? 1);
        $userLoc = $targetUser ? ($targetUser->Location ?? 'HO') : 'HO';
        $userDept = $targetUser ? ($targetUser->Department ?? 'ALL') : 'ALL';
        $userComp = $targetUser ? (int) ($targetUser->Company_ID ?? 1) : 1;

        $requestNo = $ot->Reference ?: $ot->Task_ID;

        // Context untuk Approval Engine
        $context = [
            'Request_Type' => 'ATTENDANCE_OVERTIME',
            'Location'     => $userLoc,
            'Company_ID'   => $userComp,
            'Department'   => $userDept,
            'Amount'       => (float) $ot->Amounts,
        ];

        $approvalRequestId = null;
        try {
            $apprResult = $this->workflowService->submit([
                'Request_No'  => $requestNo,
                'Description' => "Pengajuan Lembur (Batch / New Overtime) NIK: {$ot->NIK} ({$ot->Name}) - {$ot->Total_OT} Jam. Nominal: Rp " . number_format($ot->Amounts, 0, ',', '.') . ". Catatan: {$ot->Note}",
            ], $context, $requesterId);

            $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
        } catch (\Throwable $e) {
            try {
                $context['Location'] = null;
                $context['Department'] = null;
                $apprResult = $this->workflowService->submit([
                    'Request_No'  => $requestNo,
                    'Description' => "Pengajuan Lembur (Batch / New Overtime) NIK: {$ot->NIK} ({$ot->Name}) - {$ot->Total_OT} Jam. Nominal: Rp " . number_format($ot->Amounts, 0, ',', '.') . ". Catatan: {$ot->Note}",
                ], $context, $requesterId);

                $approvalRequestId = is_object($apprResult) ? ($apprResult->ID ?? null) : ($apprResult['ID'] ?? ($apprResult['request_id'] ?? null));
            } catch (\Throwable $ex) {
                Log::warning("Dynamic Approval Engine fallback untuk {$requestNo}: " . $ex->getMessage());
            }
        }

        // Update approval_request_id di overtime_lists
        if ($approvalRequestId) {
            DB::table('overtime_lists')->where('ID', $overtimeListId)->update([
                'approval_request_id' => $approvalRequestId,
            ]);
        }

        // Sync ke attendance_overtime_requests
        $existingModern = DB::table('attendance_overtime_requests')
            ->where('overtime_no', $requestNo)
            ->orWhere('legacy_overtime_id', $overtimeListId)
            ->first();

        $overtimeType = ($ot->Early_IN != '00:00:00' && $ot->Early_IN != '00:00') ? 'BEFORE_SHIFT' : 'AFTER_SHIFT';
        $startTime = ($overtimeType === 'BEFORE_SHIFT') ? $ot->Early_IN : $ot->After_IN;
        $endTime = ($overtimeType === 'BEFORE_SHIFT') ? $ot->Early_OUT : $ot->After_OUT;
        $durationMinutes = round(((float) $ot->Total_OT) * 60);

        if ($existingModern) {
            DB::table('attendance_overtime_requests')->where('id', $existingModern->id)->update([
                'status'              => 'SUBMITTED',
                'approval_request_id' => $approvalRequestId ?: $existingModern->approval_request_id,
                'calculated_amount'   => $ot->Amounts,
                'task_description'    => $ot->Note,
                'updated_at'          => Carbon::now(),
            ]);
        } else {
            DB::table('attendance_overtime_requests')->insert([
                'overtime_no'         => $requestNo,
                'nik'                 => $ot->NIK,
                'overtime_date'       => $ot->Start_Date,
                'day_type'            => $ot->Holiday ? 'HOLIDAY' : 'WORKDAY',
                'overtime_type'       => $overtimeType,
                'start_time'          => $startTime ?: '17:00:00',
                'end_time'            => $endTime ?: '21:00:00',
                'duration_minutes'    => $durationMinutes,
                'compensation_type'   => 'PAID',
                'calculated_amount'   => $ot->Amounts,
                'task_description'    => $ot->Note ?: 'Lembur via New Overtime Batch',
                'status'              => 'SUBMITTED',
                'approval_request_id' => $approvalRequestId,
                'legacy_overtime_id'  => $overtimeListId,
                'created_by'          => Auth::user()->username ?? 'admin',
                'created_at'          => Carbon::now(),
                'updated_at'          => Carbon::now(),
            ]);
        }
    }

    /**
     * Hitung durasi jam dan nominal lembur sesuai UU Cipta Kerja & PP No 35 Tahun 2021
     */
    public function calculateOvertimeHoursAndAmount($nik, $startDate, $endDate, $earlyIn, $earlyOut, $afterIn, $afterOut, $customRate = null): array
    {
        $rateInfo = $this->resolveEmployeeSalaryAndRate($nik);
        $salgroup = $rateInfo['salary_group'];
        $basicSalary = $rateInfo['basic_salary'];
        
        $rate = ($customRate !== null && (float)$customRate > 0) ? (float)$customRate : $rateInfo['rate'];

        $hari = 0;
        $libur = DB::table("libur")->where("libur_tgl", $startDate)->first();

        // Hitung slot pra-shift (sebelum jam kerja)
        $dec1 = 0;
        if (!empty($earlyIn) && !empty($earlyOut) && $earlyIn != '00:00' && $earlyIn != '00:00:00' && $earlyOut != '00:00' && $earlyOut != '00:00:00') {
            $time = new DateTime($startDate . " " . $earlyIn);
            $timeStop = new DateTime($endDate . " " . $earlyOut);
            $diff = $timeStop->diff($time);
            $dec1 = ($diff->h) + ($diff->i / 60) + ($diff->s / 3600);
            $hari = $diff->d * 24;
        }

        // Hitung slot pasca-shift (setelah jam kerja)
        $dec2 = 0;
        if (!empty($afterIn) && !empty($afterOut) && $afterIn != '00:00' && $afterIn != '00:00:00' && $afterOut != '00:00' && $afterOut != '00:00:00') {
            $time2 = new DateTime($startDate . " " . $afterIn);
            $timeStop2 = new DateTime($endDate . " " . $afterOut);
            $diff2 = $timeStop2->diff($time2);
            $dec2 = ($diff2->h) + ($diff2->i / 60) + ($diff2->s / 3600);
            $hari = $diff2->d * 24;
        }

        $decimalHours = round($hari + $dec1 + $dec2, 2);
        $jam1 = 0; $jam2 = 0; $jam3 = 0; $jam4 = 0;
        $holiday = 0;

        // Evaluasi hari libur nasional atau akhir pekan (Sabtu / Minggu)
        if ($libur) {
            $holiday = 1;
        } else {
            $nomorhari = date("N", strtotime($startDate));
            if ($nomorhari == 6 || $nomorhari == 7) {
                $holiday = 1;
            }
        }

        // PP No 35 Tahun 2021 Pasal 31:
        // 1. Hari Libur Resmi / Istirahat Mingguan (5/6 hari kerja):
        //    - 8 jam pertama: dibayar 2x upah sejam
        //    - Jam ke-9: dibayar 3x upah sejam
        //    - Jam ke-10, 11, dst: dibayar 4x upah sejam
        if ($holiday == 1) {
            if ($decimalHours > 9) {
                $jam1 = 8;
                $jam2 = 1;
                $jam3 = $decimalHours - 9;
                $jumlahjam = ($jam1 * 2) + ($jam2 * 3) + ($jam3 * 4);
            } elseif ($decimalHours > 8 && $decimalHours <= 9) {
                $jam1 = 8;
                $jam2 = $decimalHours - 8;
                $jumlahjam = ($jam1 * 2) + ($jam2 * 3);
            } elseif ($decimalHours <= 8) {
                $jam1 = $decimalHours;
                $jumlahjam = $jam1 * 2;
            } else {
                $jumlahjam = 0;
            }
        } else {
            // 2. Hari Kerja Biasa:
            //    - Jam pertama: 1.5x upah sejam
            //    - Jam-jam berikutnya: 2x upah sejam
            if ($decimalHours >= 1) {
                $jam1 = 1;
                $jam2 = $decimalHours - 1;
                $jumlahjam = ($jam1 * 1.5) + ($jam2 * 2);
            } else {
                $jam1 = $decimalHours;
                $jumlahjam = $jam1 * 1.5;
            }
        }

        $jumlahjam = round($jumlahjam, 2);
        $amounts = round($rate * $jumlahjam);

        return [
            'holiday'        => $holiday,
            'decimal_hours'  => $decimalHours,
            'jumlah_jam'     => $jumlahjam,
            'rate'           => $rate,
            'basic_salary'   => $basicSalary,
            'amounts'        => $amounts,
            'jam1'           => $jam1,
            'jam2'           => $jam2,
            'jam3'           => $jam3,
            'jam4'           => $jam4,
            'salary_group'   => $salgroup,
        ];
    }
}
