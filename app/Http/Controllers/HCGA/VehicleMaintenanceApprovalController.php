<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleMaintenanceApprovalController extends Controller
{
    // =========================================================================
    // INDEX — tampilan list approval pending
    // =========================================================================
    public function index()
    {
        return view('humanresources.vehicle-maintenance.approval.index');
    }

    // =========================================================================
    // DATA — XML grid untuk daftar approval (mirip AssetFixingApprovalController)
    // =========================================================================
    public function data()
    {
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? 'text/xml';
        if (!headers_sent() && stristr($httpAccept, 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } elseif (!headers_sent()) {
            header('Content-type: text/xml');
        }
        echo('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $username   = Auth::user()->username;
        $dept       = Auth::user()->Department;
        $saya       = Auth::user()->Name ?: $username;
        $nomor      = 1;

        // Tentukan level approver berdasarkan department
        // Level 1 = atasan departemen
        // Level 2 = VP departemen
        // Level 3 = manager HCGA
        $userLevel = '0';
        if ($dept === 'HCGA') {
            $userLevel = '3';
        } elseif (in_array($dept, ['OPERATION', 'DIRECTORATE'])) {
            $userLevel = '2';
        } else {
            $userLevel = '1';
        }

        $Query = DB::table('vehicle_maintenance')
            ->join('users', 'vehicle_maintenance.User_Trx', '=', 'users.username')
            ->select(
                'vehicle_maintenance.*',
                'users.Name as Nama_Pengaju',
                'users.Position as Jabatan',
                'users.Department as Dept_Pengaju',
            )
            ->orderBy('vehicle_maintenance.created_at', 'DESC');

        // ---- Filtering berdasarkan level approver ----
        if ($userLevel === '1') {
            // Atasan departemen: lihat yang menunggu approval atasan, milik dept-nya
            $Query->where('vehicle_maintenance.Status', 'APPROVAL_ATASAN')
                  ->where('vehicle_maintenance.Department', $dept);
        } elseif ($userLevel === '2') {
            // VP departemen: lihat yang menunggu VP, milik dept-nya
            $Query->where('vehicle_maintenance.Status', 'APPROVAL_VP')
                  ->where('vehicle_maintenance.Department', $dept);
        } elseif ($userLevel === '3') {
            // Manager HCGA: lihat yang menunggu HCGA Manager
            $Query->where('vehicle_maintenance.Status', 'APPROVAL_HCGA_MANAGER');
        } else {
            // User biasa: tidak ada data
            $Query->whereRaw('0 = 1');
        }

        $results = $Query->get();

        echo "<rows>";
        foreach ($results as $Fetch) {
            echo "<row id=\"" . $Fetch->id . "\">";
            echo "<cell>" . $nomor . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->No_Trx) . "</cell>";
            echo "<cell>" . htmlspecialchars(ucfirst($Fetch->Jenis_Perawatan)) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Asset_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Product_Description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Plat_Nomor) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($Fetch->Estimasi_Biaya, 0, ',', '.')) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Nama_Pengaju) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Dept_Pengaju) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    // =========================================================================
    // DETAIL — tampilan detail + tombol approve/reject
    // =========================================================================
    public function detail(Request $request)
    {
        $data = DB::table('vehicle_maintenance')
            ->where('id', $request->ID)
            ->first();
        if (!$data) {
            abort(404);
        }

        $approver = DB::table('users')
            ->where('username', Auth::user()->username)
            ->select('Name', 'Department', 'Position', 'Status as UserStatus')
            ->first();

        return view('humanresources.vehicle-maintenance.approval.detail', compact('data', 'approver'));
    }

    // =========================================================================
    // APPROVE/REJECT ACTION — mengikuti pola AssetFixingApprovalController
    // Menggunakan master_hr_approval untuk menentukan wewenang approver
    // =========================================================================
    public function approve(Request $request)
    {
        $id             = $request->Data;
        $action         = $request->Action; // 'APPROVE' atau 'REJECT'
        $note           = $request->Note ?? '';

        $maintenance = DB::table('vehicle_maintenance')
            ->where('id', $id)
            ->first();

        if (!$maintenance) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $currentStatus = $maintenance->Status;
        $newStatus     = null;
        $username      = Auth::user()->username;
        $dept          = Auth::user()->Department;
        $approverName  = Auth::user()->Name ?: $username;
        $timestamp     = date('Y-m-d H:i:s');

        // =========================================================================
        // Tentukan level approver berdasarkan master_hr_approval
        // =========================================================================
        $approverMaster = DB::table('master_hr_approval')
            ->where('username', $username)
            ->where('Status', 'ACTIVE')
            ->first();

        $userLevel     = $approverMaster ? (string)$approverMaster->Level : '99';
        $approverDept  = $approverMaster ? $approverMaster->Department : $dept;
        $isOverLimit    = (float)$maintenance->Estimasi_Biaya > 2500000;

        // =========================================================================
        // LOGIKA APPROVAL 4 TINGKAT (mengikuti pola AssetFixingApprovalController):
        //
        // Level 3 (PIC/Manager Dept) → approve APPROVAL_PIC → next: APPROVAL_ATASAN
        //   Syarat: userLevel == '3' DAN approverDept == dept pengaju
        //
        // Level 1 (VP/Atasan Dept) → approve APPROVAL_ATASAN atau APPROVAL_VP → next: APPROVAL_HCGA_MANAGER
        //   Syarat: userLevel == '1'
        //
        // Level 3 (Manager HCGA) → approve APPROVAL_HCGA_MANAGER → next: APPROVED
        //   Syarat: userLevel == '3' DAN approverDept == 'HCGA'
        //
        // APPROVED, REJECTED → tidak bisa di-approve lagi
        // =========================================================================

        switch ($currentStatus) {

            case 'APPROVAL_PIC':
                // PIC / Manager Dept — Level 3 dari departemen yang sama dengan dept pengaju
                if ($userLevel != '3' || $approverDept != $maintenance->Department) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Anda tidak berwenang approve tahap PIC (harus manager level 3 dari dept pengaju)',
                    ]);
                }
                // Jika estimasi > 2.5 jt, lewat ke VP (seperti AssetFixing pattern)
                $newStatus = $isOverLimit ? 'APPROVAL_VP' : 'APPROVAL_ATASAN';
                break;

            case 'APPROVAL_ATASAN':
                // Atasan departemen — Level 1 dari DIR/OPERATION
                if ($userLevel != '1') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hanya VP/Atasan Departemen (Level 1) yang berwenang approve tahap ini',
                    ]);
                }
                // Lewat ke manager HCGA
                $newStatus = 'APPROVAL_HCGA_MANAGER';
                break;

            case 'APPROVAL_VP':
                // VP departemen — Level 1 dari DIR/OPERATION (sama seperti atasan)
                if ($userLevel != '1') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hanya VP/Atasan Departemen (Level 1) yang berwenang approve tahap ini',
                    ]);
                }
                // Lewat ke manager HCGA
                $newStatus = 'APPROVAL_HCGA_MANAGER';
                break;

            case 'APPROVAL_HCGA_MANAGER':
                // Manager HCGA — Level 3 dari HCGA
                if ($userLevel != '3' || $approverDept != 'HCGA') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hanya Manager HCGA (Level 3, Dept HCGA) yang berwenang approve tahap ini',
                    ]);
                }
                $newStatus = 'APPROVED';
                break;

            case 'APPROVED':
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Transaksi ini sudah disetujui',
                ]);

            case 'REJECTED':
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Transaksi ini sudah ditolak',
                ]);

            default:
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Status tidak dikenal: ' . $currentStatus,
                ]);
        }

        // =========================================================================
        // Proses approve / reject
        // =========================================================================
        if ($action === 'REJECT') {
            $newStatus = 'REJECTED';
        }

        $existingNote = $maintenance->Keterangan_Lain ? $maintenance->Keterangan_Lain . "\n" : '';
        if ($action === 'REJECT') {
            $newNote = $existingNote . "$timestamp REJECTED by $approverName ($dept) :: $note";
        } else {
            $newNote = $existingNote . "$timestamp APPROVED by $approverName ($dept) :: $note";
        }

        // Simpan ke tabel vehicle_maintenance_approval (riwayat approval)
        DB::table('vehicle_maintenance_approval')->insert([
            'vehicle_maintenance_id' => $id,
            'approver_username'      => $username,
            'approver_name'          => $approverName,
            'approver_dept'          => $dept,
            'level'                  => $userLevel,
            'action'                 => $action,
            'notes'                  => $note,
            'approved_at'            => $timestamp,
            'created_at'             => $timestamp,
            'updated_at'             => $timestamp,
        ]);

        // Update status di vehicle_maintenance
        DB::table('vehicle_maintenance')
            ->where('id', $id)
            ->update([
                'Status'           => $newStatus,
                'Keterangan_Lain'  => $newNote,
            ]);

        Log::info('VehicleMaintenance approval: id=' . $id . ' action=' . $action .
                  ' newStatus=' . $newStatus . ' by=' . $username);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil melakukan ' . ($action === 'REJECT' ? 'penolakan' : 'approval') . '',
            'newStatus' => $newStatus,
        ]);
    }

    // =========================================================================
    // History approval (log approver lengkap)
    // =========================================================================
    public function approvalHistory(Request $request)
    {
        $id = $request->ID;
        $history = DB::table('vehicle_maintenance_approval')
            ->where('vehicle_maintenance_id', $id)
            ->orderBy('approved_at', 'ASC')
            ->get();

        return response()->json([
            'status'  => 'success',
            'history' => $history,
        ]);
    }
}
