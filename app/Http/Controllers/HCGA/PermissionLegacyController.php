<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalWorkflowService;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\CollectiveLeaveBatchService;
use App\Services\Attendance\LeaveEntitlementService;
use App\Services\Attendance\LeaveLegacySyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionLegacyController extends Controller
{
    protected $attendanceService;
    protected $leaveLegacySyncService;
    protected $leaveEntitlementService;
    protected $workflowService;
    protected $collectiveLeaveBatchService;

    public function __construct(
        AttendanceService $attendanceService,
        LeaveLegacySyncService $leaveLegacySyncService,
        LeaveEntitlementService $leaveEntitlementService,
        ApprovalWorkflowService $workflowService,
        CollectiveLeaveBatchService $collectiveLeaveBatchService
    ) {
        $this->attendanceService = $attendanceService;
        $this->leaveLegacySyncService = $leaveLegacySyncService;
        $this->leaveEntitlementService = $leaveEntitlementService;
        $this->workflowService = $workflowService;
        $this->collectiveLeaveBatchService = $collectiveLeaveBatchService;
    }

    /**
     * Tampilan Halaman Legacy: New Permission Request
     */
    public function newIndex()
    {
        $user = Auth::user();
        $username = $user ? $user->username : '';
        $location = $user ? ($user->Location ?? 'HO') : 'HO';

        $jenisIzin = DB::table("jns_izin")->orderBy('izin_jenis_id', 'ASC')->get();

        $taskId = "IZIN/{$location}-" . date('ymdHis') . '/' . strtoupper(substr(md5($username . rand(999, 9999)), -5));

        $holidayMap = $this->leaveLegacySyncService->getHolidayMap();
        $listLibur = implode(',', array_keys($holidayMap));

        return view('humanresources.permission.new', compact('jenisIzin', 'taskId', 'listLibur', 'user'));
    }

    /**
     * Tampilan Halaman Legacy: All Permission
     */
    public function allIndex()
    {
        return view('humanresources.permission.all');
    }

    /**
     * Data XML untuk Grid All Permission (dhtmlXGrid)
     */
    public function xmlAll(Request $request)
    {
        header("Content-type: text/xml");
        echo "<?xml version='1.0' encoding='utf-8'?>\n";

        $jns = DB::table("jns_izin")->pluck('izin_jenis_name', 'izin_jenis_id')->all();
        $kats = DB::table("kategori_izin")->pluck('kat_izin_nama', 'kat_izin_id')->all();

        $nik = $request->query('NIK');
        $tgl1 = $request->query('Tgl1', date('Y-m-d'));
        $tgl2 = $request->query('Tgl2', date('Y-m-d'));

        $query = DB::table("izin")->whereBetween('izin_tgl', [$tgl1, $tgl2]);
        if (!empty($nik)) {
            $query->where('NIK', $nik);
        }

        $rows = $query->orderBy('izin_tgl', 'DESC')->get();

        echo "<rows>";
        $nomor = 1;
        foreach ($rows as $row) {
            $kategori = $kats[$row->kat_izin_id] ?? '-';
            $subkategori = $jns[$row->izin_jenis_id] ?? '-';

            echo "<row id=\"{$row->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->Task_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->Employee_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->Employee_Category) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $subkategori) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $kategori) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($row->izin_ket_lain ?? $row->Location)) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->izin_catatan) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->izin_tgl) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
        exit;
    }

    /**
     * Tampilan Halaman Legacy: Approval Permission
     */
    public function approvalIndex()
    {
        return view('humanresources.permission.approval');
    }

    /**
     * Data XML untuk Grid Approval Permission (dhtmlXGrid)
     */
    public function xmlApproval(Request $request)
    {
        header("Content-type: text/xml");
        echo "<?xml version='1.0' encoding='utf-8'?>\n";

        $jns = DB::table("jns_izin")->pluck('izin_jenis_name', 'izin_jenis_id')->all();
        $kats = DB::table("kategori_izin")->pluck('kat_izin_nama', 'kat_izin_id')->all();

        // Di legacy izin, status 3 = Waiting Approval
        $rows = DB::table("izin")
            ->where("izin_status", 3)
            ->orderBy("Employee_Name", "ASC")
            ->orderBy("izin_tgl", "DESC")
            ->get();

        echo "<rows>";
        $nomor = 1;
        foreach ($rows as $row) {
            $kategori = $kats[$row->kat_izin_id] ?? '-';
            $subkategori = $jns[$row->izin_jenis_id] ?? '-';

            echo "<row id=\"{$row->ID}\">";
            echo "<cell>0</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->Task_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->NIK) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->Employee_Name) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->Employee_Category) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $subkategori) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $kategori) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) ($row->izin_ket_lain ?? $row->Location)) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->izin_catatan) . "</cell>";
            echo "<cell>" . htmlspecialchars((string) $row->izin_tgl) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
        exit;
    }

    /**
     * Submit Approval dari dhtmlX DataProcessor
     */
    public function updateApproval(Request $request)
    {
        header("Content-type: text/xml");
        echo "<?xml version='1.0' encoding='utf-8'?>\n";
        echo "<data>\n";

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
        $currentUser = Auth::user();

        foreach ($checkIds as $rowId) {
            if (empty($rowId)) continue;

            $checkboxVal = $params[$rowId . "_c0"] ?? 0;
            // Jika checkbox diceklis untuk di-approve
            if ($checkboxVal == 1 || $checkboxVal === '1' || $checkboxVal === 'true' || $checkboxVal === 'On') {
                $legacyIzin = DB::table('izin')->where('ID', $rowId)->first();
                if ($legacyIzin) {
                    DB::table('izin')->where('ID', $rowId)->update([
                        'izin_status' => 1, // 1 = APPROVED
                    ]);

                    // Selaraskan ke modern table & approval engine
                    $approvalRequestId = $legacyIzin->approval_request_id;
                    if (!empty($legacyIzin->modern_leave_id)) {
                        $modernLeave = DB::table('attendance_leave_requests')
                            ->where('id', $legacyIzin->modern_leave_id)
                            ->first();

                        if ($modernLeave) {
                            $approvalRequestId = $modernLeave->approval_request_id ?: $approvalRequestId;
                            DB::table('attendance_leave_requests')
                                ->where('id', $modernLeave->id)
                                ->update([
                                    'status'     => 'APPROVED',
                                    'updated_by' => $currentUser ? $currentUser->username : 'admin',
                                    'updated_at' => Carbon::now(),
                                ]);
                        }
                    }

                    // Jika ada task approval di ApprovalWorkflowService, eksekusi action APPROVE
                    if (!empty($approvalRequestId)) {
                        try {
                            $apprTasks = DB::table('approval_request_task')
                                ->where('Request_ID', $approvalRequestId)
                                ->whereIn('Status', ['PENDING', 'CLAIMED'])
                                ->get();

                            $actorId = $currentUser ? (int) $currentUser->id : 1;
                            foreach ($apprTasks as $at) {
                                $this->workflowService->act((int) $at->ID, 'APPROVE', 'Disetujui melalui menu Permission Approval', $actorId);
                            }
                        } catch (\Throwable $e) {
                            Log::warning("Gagal auto-approve Dynamic Approval Task ID {$approvalRequestId}: " . $e->getMessage());
                        }
                    }

                    // Potong saldo cuti jika kategori memotong cuti
                    $cat = DB::table('kategori_izin')->where('kat_izin_id', $legacyIzin->kat_izin_id)->first();
                    if ($cat && $cat->is_deduct_annual_leave === 'YES') {
                        try {
                            $this->leaveEntitlementService->deductLeaveBalance(
                                $legacyIzin->NIK,
                                1,
                                $legacyIzin->Task_ID ?? ('IZIN-' . $rowId),
                                $cat->kat_izin_code ?? $cat->kat_izin_nama,
                                $currentUser
                            );
                        } catch (\Throwable $e) {
                            Log::warning("Legacy approval deduct balance error: " . $e->getMessage());
                        }
                    }
                }
            }

            echo "<action type='update' sid='{$rowId}' tid='{$rowId}' />\n";
        }

        echo "</data>\n";
        exit;
    }

    /**
     * Batal / Cancel permohonan dari grid approval legacy
     */
    public function cancelApproval(Request $request)
    {
        $id = $request->query('ID');
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID wajib diisi.'], 400);
        }

        $legacyIzin = DB::table('izin')->where('ID', $id)->first();
        if ($legacyIzin) {
            DB::table('izin')->where('ID', $id)->update(['izin_status' => 2]); // 2 = Rejected / Cancelled

            $approvalRequestId = $legacyIzin->approval_request_id;
            if (!empty($legacyIzin->modern_leave_id)) {
                $modernLeave = DB::table('attendance_leave_requests')
                    ->where('id', $legacyIzin->modern_leave_id)
                    ->first();

                if ($modernLeave) {
                    $approvalRequestId = $modernLeave->approval_request_id ?: $approvalRequestId;
                    DB::table('attendance_leave_requests')
                        ->where('id', $modernLeave->id)
                        ->update([
                            'status'     => 'CANCELLED',
                            'updated_by' => Auth::user()->username ?? 'admin',
                            'updated_at' => Carbon::now(),
                        ]);
                }
            }

            // Batalkan tiket pada ApprovalWorkflowService jika ada
            if (!empty($approvalRequestId)) {
                try {
                    $this->workflowService->cancel(
                        (int) $approvalRequestId,
                        (int) (Auth::id() ?? 1),
                        'Dibatalkan melalui menu Permission Approval Legacy'
                    );
                } catch (\Throwable $e) {
                    Log::warning("Gagal membatalkan Dynamic Approval ID {$approvalRequestId}: " . $e->getMessage());
                }
            }
        }

        return response("<script>alert('Permission berhasil dibatalkan.'); if(typeof PermissionApproval !== 'undefined'){ PermissionApproval.clearAndLoad('xml-permission-approval'); }</script>");
    }

    /**
     * Detail Modal / Partial Permission
     */
    public function detail(Request $request)
    {
        $id = $request->query('ID');
        $izin = DB::table('izin')->where('ID', $id)->first();
        if (!$izin) {
            return "<div class='alert alert-danger p-2'>Data Izin tidak ditemukan.</div>";
        }

        $cat = DB::table('kategori_izin')->where('kat_izin_id', $izin->kat_izin_id)->first();
        $jns = DB::table('jns_izin')->where('izin_jenis_id', $izin->izin_jenis_id)->first();

        $statusBadge = '<span class="badge bg-warning text-dark">Menunggu Persetujuan</span>';
        if ($izin->izin_status == 1) {
            $statusBadge = '<span class="badge bg-success text-white">Disetujui (Approved)</span>';
        } elseif ($izin->izin_status == 2) {
            $statusBadge = '<span class="badge bg-danger text-white">Ditolak / Dibatalkan</span>';
        }

        $html = "
        <div class='card border-0 shadow-sm rounded-3 mt-3'>
            <div class='card-header bg-light py-2 fw-bold d-flex justify-content-between align-items-center'>
                <span>Detail Permohonan Izin / Cuti #{$izin->Task_ID}</span>
                {$statusBadge}
            </div>
            <div class='card-body p-3'>
                <div class='row g-2'>
                    <div class='col-md-6'><strong>NIK:</strong> {$izin->NIK}</div>
                    <div class='col-md-6'><strong>Nama Karyawan:</strong> {$izin->Employee_Name}</div>
                    <div class='col-md-6'><strong>Kategori:</strong> " . ($cat->kat_izin_nama ?? '-') . "</div>
                    <div class='col-md-6'><strong>Jenis:</strong> " . ($jns->izin_jenis_name ?? '-') . "</div>
                    <div class='col-md-6'><strong>Tanggal Izin:</strong> {$izin->izin_tgl}</div>
                    <div class='col-md-6'><strong>Info Jam:</strong> " . ($izin->Informasi_Waktu ?: '-') . "</div>
                    <div class='col-12'><strong>Alasan / Keterangan:</strong> " . htmlspecialchars($izin->izin_catatan ?? '-') . "</div>
                    " . (!empty($izin->File_Path) ? "<div class='col-12 mt-2'><strong>Dokumen:</strong> <a href='/" . ltrim($izin->File_Path, '/') . "' target='_blank' class='btn btn-sm btn-outline-primary rounded-pill px-3'><i class='fe fe-file me-1'></i> Unduh Dokumen Pendukung</a></div>" : "") . "
                </div>
            </div>
        </div>";

        return response($html);
    }

    /**
     * Submit Pengajuan Baru dari Form Legacy (permission-new)
     */
    public function submitNew(Request $request)
    {
        $nik = $request->input('NIK');
        $katIzinId = (int) $request->input('kat_izin_id');
        $izinJenisId = (int) $request->input('izin_jenis_id');
        $startDate = $request->input('izin_tgl');
        $endDate = $request->input('izin_tgl2', $startDate);
        $reason = $request->input('izin_catatan', '');
        $infoWaktu = $request->input('Informasi_Waktu');
        $filePath = $request->input('File_Path');
        $location = $request->input('izin_ket_lain');

        if (empty($nik) || empty($startDate)) {
            return response("Error: NIK dan Tanggal Izin wajib diisi.", 400);
        }

        $emp = DB::table('employment')->where('NIK', $nik)->first();
        $targetUser = DB::table('users')->where('Emp_ID', $nik)->orWhere('username', $nik)->first();

        // Cari kategori izin
        $category = DB::table('kategori_izin')->where('kat_izin_id', $katIzinId)->first();
        $leaveType = $category ? ($category->kat_izin_code ?? $category->kat_izin_nama) : 'LEAVE';

        // Panggil service Attendance modern submitLeaveRequest
        try {
            $userObj = $targetUser ?: (object) [
                'id'         => $emp->ID ?? 0,
                'Emp_ID'     => $nik,
                'username'   => $nik,
                'Name'       => $emp->Name ?? $nik,
                'Location'   => $location ?? ($emp->Location ?? 'HO'),
                'Company_ID' => 1,
                'Department' => $emp->Department ?? 'ALL',
            ];

            // Pecah info waktu ke clock in/out bila format HH:mm
            $clockInTime = null;
            $clockOutTime = null;
            if ($infoWaktu) {
                if (strpos($infoWaktu, '-') !== false) {
                    $parts = explode('-', $infoWaktu);
                    $clockInTime = trim($parts[0]);
                    $clockOutTime = trim($parts[1]);
                } else {
                    $clockInTime = trim($infoWaktu);
                }
            }

            $submitData = [
                'leave_type'     => $leaveType,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'reason'         => $reason,
                'clock_in_time'  => $clockInTime,
                'clock_out_time' => $clockOutTime,
                'attachment'     => $filePath,
            ];

            $res = $this->attendanceService->submitLeaveRequest($userObj, $submitData);

            return "success";
        } catch (\Throwable $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Load Dropdown Kategori Izin secara AJAX
     */
    public function loadKategoriIzin(Request $request)
    {
        $id = (int) $request->query('ID');
        $result = DB::table('kategori_izin')->where('izin_jenis_id', $id)->get();

        $mx = "<select id=\"kat_izin_id\" class='form-select rounded-3'>";
        foreach ($result as $data) {
            $mx .= "<option value='{$data->kat_izin_id}'>{$data->kat_izin_nama}</option>";
        }
        $mx .= "</select>";

        return response($mx);
    }

    /**
     * Halaman Pengaturan / Batch Cuti Bersama HCGA
     */
    public function collectiveLeaveIndex()
    {
        $batches = $this->collectiveLeaveBatchService->getCollectiveLeavesList();
        $departments = DB::table('employment')->where('Status', 'ACTIVE')->whereNotNull('Department')->distinct()->pluck('Department');
        $locations = DB::table('employment')->where('Status', 'ACTIVE')->whereNotNull('Location')->distinct()->pluck('Location');

        return view('humanresources.permission.collective-leave', compact('batches', 'departments', 'locations'));
    }

    /**
     * Submit Batch Cuti Bersama oleh HCGA
     */
    public function submitCollectiveLeave(Request $request)
    {
        $request->validate([
            'title'                  => 'required|string|max:200',
            'start_date'             => 'required|date_format:Y-m-d',
            'end_date'               => 'required|date_format:Y-m-d',
            'is_deduct_annual_leave' => 'required|in:YES,NO',
            'target_scope'           => 'required|in:ALL,DEPARTMENT,LOCATION',
        ]);

        try {
            $user = Auth::user();
            $result = $this->collectiveLeaveBatchService->applyCollectiveLeave($request->all(), $user);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
