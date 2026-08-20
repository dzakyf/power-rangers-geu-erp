<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetFixingApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-fixing.approval.index');
    }

    public function data()
    {
        if (stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo('<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");

        $username      = Auth::user()->username;
        $dept          = Auth::user()->Department;
        $saya          = Auth::user()->Name ?: $username;
        $nomor         = 1;

        $approver = DB::table('master_hr_approval')
            ->where('username', $username)
            ->where('Status', 'ACTIVE')
            ->first();
        $userLevel     = $approver ? (string)$approver->Level : '99';
        $approverDept  = $approver ? $approver->Department : $dept;

        $Query = DB::table('asset_fixing')->orderBy('created_at', 'DESC');

        // Level 1 (VP/BOD): lihat yang menunggu Direktur dan yang sudah selesai
        if ($userLevel == '1') {
            $Query->where(function($q) {
                $q->where('Status', 'APPROVAL_DIRECTOR')
                  ->orWhere('Status', 'APPROVED');
            });
        }
        // Dept HCGA: lihat yang menunggu HCGA
        elseif ($approverDept == 'HCGA') {
            $Query->where('Status', 'APPROVAL_HCGA');
        }
        // Dept OPERATION: lihat yang menunggu VP Operation
        elseif ($approverDept == 'OPERATION') {
            $Query->where('Status', 'APPROVAL_VP');
        }
        // Manager level 3: lihat yang menunggu PIC (milik dept-nya)
        elseif ($userLevel == '3') {
            $Query->where(function($q) use ($approverDept) {
                $q->where('Status', 'APPROVAL_PIC')
                  ->where('Department', $approverDept);
            });
        }
        // User biasa: tidak ada data
        else {
            $Query->whereRaw('0 = 1');
        }

        $results = $Query->get();

        echo "<rows>";
        foreach ($results as $Fetch) {
            echo "<row id=\"" . $Fetch->ID . "\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->No_Trx) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Jenis_Perbaikan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Nama_Pengaju) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Tanggal_Pengajuan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Nama_Aset) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Product_Code ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Asset_No ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->SN ?? ($Fetch->Nomor_Seri ?? '')) . "</cell>";
            echo "<cell>" . htmlspecialchars(number_format($Fetch->Estimasi_Biaya, 0, ',', '.')) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Status) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $data = DB::table('asset_fixing')->where('ID', $request->ID)->first();
        if (!$data) {
            abort(404);
        }
        return view('humanresources.asset-fixing.approval.detail', compact('data'));
    }

    public function approval(Request $request)
    {
        $id          = $request->Data;
        $approvalType = $request->Approval; // 1 = approve, 2 = reject
        $note         = $request->Note ?? '';

        $asset = DB::table('asset_fixing')->where('ID', $id)->first();
        if (!$asset) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $approver = DB::table('master_hr_approval')
            ->where('username', Auth::user()->username)
            ->where('Status', 'ACTIVE')
            ->first();

        if (!$approver) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User tidak memiliki konfigurasi approval',
            ]);
        }

        $currentStatus  = $asset->Status;
        $newStatus      = null;
        $userLevel      = (string)$approver->Level;
        $approverDept   = $approver->Department;
        $isOverLimit    = (float)$asset->Estimasi_Biaya > 2500000;

        // ---- Validasi wewenang & tentukan status berikutnya ----
        switch ($currentStatus) {

            case 'APPROVAL_PIC':
                // PIC Aset / Ast. Man. Ops. Support & Controller
                // Siapa yang berhak? Manager level 3 dari departemen pengaju
                if ($userLevel != '3' || $approverDept != $asset->Department) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Anda tidak berwenang approve tahap PIC (harus manager level 3 dari dept pengaju)',
                    ]);
                }
                $newStatus = 'APPROVAL_VP';
                break;

            case 'APPROVAL_VP':
                // VP Operation
                if ($approverDept != 'OPERATION') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hanya VP Operation (Dept OPERATION) yang berwenang approve tahap ini',
                    ]);
                }
                if ($isOverLimit) {
                    $newStatus = 'APPROVAL_DIRECTOR';
                } else {
                    $newStatus = 'APPROVAL_HCGA';
                }
                break;

            case 'APPROVAL_HCGA':
                // HCGA Manager
                if ($approverDept != 'HCGA') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hanya HCGA Manager yang berwenang approve tahap ini',
                    ]);
                }
                if ($isOverLimit) {
                    $newStatus = 'APPROVAL_DIRECTOR';
                } else {
                    $newStatus = 'APPROVED';
                }
                break;

            case 'APPROVAL_DIRECTOR':
                // Direktur / VP (Level 1)
                if ($userLevel != '1') {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hanya Direktur/VP Level 1 yang berwenang approve tahap Director',
                    ]);
                }
                $newStatus = 'APPROVED';
                break;

            case 'APPROVED':
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Transaksi ini sudah selesai',
                ]);

            case 'DRAFT':
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data masih dalam draft, belum bisa di-approve',
                ]);

            default:
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Status tidak dikenal: ' . $currentStatus,
                ]);
        }

        // ---- Proses approve / reject ----
        $username     = Auth::user()->username;
        $apprDept     = Auth::user()->Department;
        $timestamp    = date('Y-m-d H:i:s');
        $existingNote = $asset->Approval_Note ? $asset->Approval_Note . "\n" : '';

        if ($approvalType == 2) {
            // Reject
            $newNote    = $existingNote . "$timestamp REJECTED by $username ($apprDept) :: $note";
            $newStatus  = 'REJECTED';
        } else {
            // Approve
            $newNote    = $existingNote . "$timestamp APPROVED by $username ($apprDept) :: $note";
        }

        DB::table('asset_fixing')
            ->where('ID', $id)
            ->update([
                'Approval_Note'     => $newNote,
                'Status'            => $newStatus,
                'Approval_User'     => $username,
                'Approval_Datetime' => $timestamp,
            ]);

        Log::info('AssetFixing approval: id=' . $id . ' newStatus=' . $newStatus . ' by=' . $username);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil melakukan approval',
            'newStatus' => $newStatus,
        ]);
    }
}
