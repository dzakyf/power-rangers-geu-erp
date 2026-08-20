<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetMovingApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-moving.approval.index');
    }

    public function data()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        
        $username = Auth::user()->username;
        $dept = Auth::user()->Department;
        $saya = Auth::user()->Name;
        $nomor = 1;

        // Ambil level approval dari master_hr_approval
        $approver = DB::table('master_hr_approval')
            ->where('username', $username)
            ->where('Status', 'ACTIVE')
            ->first();
        $userLevel = $approver ? (string)$approver->Level : '99';

        echo "<rows>";

        $Query = DB::table("moving_asset")->orderBy("DateTrx","DESC");

        if ($userLevel == '1' || $dept == 'DIR') {
            // VP / BOD: tampilkan yang menunggu BOD dan yang sudah selesai
            $Query->where(function($q) {
                $q->where("Status", "APPROVAL_BOD")
                  ->orWhere("Status", "APPROVED")
                  ->orWhere("Status", "REJECTED");
            });
        } elseif ($dept == 'HCGA') {
            $Query->where("Status", "APPROVAL_GA");
        } elseif ($userLevel == '3') {
            // Manager (level 3): lihat yang menunggu atasan (milik bawahannya)
            // dan yang menunggu pimpinan departemennya (sebagai tujuan)
            $Query->where(function($q) use ($dept) {
                $q->where("Department", $dept)
                  ->where("Status", "APPROVAL_UPPER");
            })->orWhere(function($q) use ($dept) {
                $q->where("Receipent_Department", $dept)
                  ->where("Status", "APPROVAL_RECEIPENT");
            });
        } else {
            // User bukan operator approval yang berwenang: tidak ada data
            $Query->whereRaw("0 = 1");
        }

        $results = $Query->get();

        foreach($results as $Fetch){
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>".htmlspecialchars($Fetch->No_Trx)."</cell>";
            echo "<cell>".htmlspecialchars($saya)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Department)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->DateTrx)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Asset_No)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Product_Description)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Asset_Description)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Moving_Reason)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Moving_Date)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Receipent_Name)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Receipent_Department)."</cell>";
            echo "<cell>".htmlspecialchars($Fetch->Status)."</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $assetMoving = DB::table('moving_asset')->where("ID", $request->ID)->first();
        return view('humanresources.asset-moving.approval.detail', compact("assetMoving"));
    }

    public function approval(Request $request)
    {
        $id = $request->Data;
        $approvalType = $request->Approval; // 1 = approve, 2 = reject
        $note = $request->Note ?? '';

        // 1. Ambil data
        $asset = DB::table('moving_asset')->where('ID', $id)->first();
        if (!$asset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan',
            ]);
        }

        // 2. Ambil konfigurasi approval user dari master_hr_approval
        $approver = DB::table('master_hr_approval')
            ->where('username', Auth::user()->username)
            ->where('Status', 'ACTIVE')
            ->first();

        if (!$approver) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak memiliki konfigurasi approval',
            ]);
        }

        // 3. Tentukan status berikutnya berdasarkan status saat ini DAN validasi wewenang
        $currentStatus = $asset->Status;
        $newStatus = null;

        switch ($currentStatus) {
            case 'APPROVAL_UPPER':
                // Hanya manager (level 3) dari departemen pengaju yang boleh approve
                if ($approver->Level != '3' || $approver->Department != $asset->Department) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda tidak berwenang approve tahap ini',
                    ]);
                }
                $newStatus = 'APPROVAL_RECEIPENT';
                break;

            case 'APPROVAL_RECEIPENT':
                // Hanya pimpinan departemen tujuan yang boleh approve
                if ($approver->Department != $asset->Receipent_Department) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Anda tidak berwenang approve tahap ini',
                    ]);
                }
                $newStatus = 'APPROVAL_GA';
                break;

            case 'APPROVAL_GA':
                // HCGA approve
                if ($approver->Department != 'HCGA') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Hanya HCGA yang berhak approve',
                    ]);
                }
                $newStatus = 'APPROVAL_BOD';
                break;

            case 'APPROVAL_BOD':
                // BOD/VP (level 1) approve
                if ($approver->Level != '1') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Hanya VP/BOD yang berhak approve',
                    ]);
                }
                $newStatus = 'APPROVED';
                break;

            case 'REJECTED':
            case 'APPROVED':
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi ini sudah selesai',
                ]);

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status tidak dikenal: ' . $currentStatus,
                ]);
        }

        // 4. Proses approve atau reject
        $username = Auth::user()->username;
        $apprDept = Auth::user()->Department;
        $timestamp = date('Y-m-d H:i:s');
        $existingNote = $asset->Approval_Note ? $asset->Approval_Note . "\n" : '';

        if ($approvalType == 2) {
            // Reject
            $newNote = $existingNote . "$timestamp REJECTED by $username ($apprDept) :: $note";
            $newStatus = 'REJECTED';
        } else {
            // Approve
            $newNote = $existingNote . "$timestamp APPROVED by $username ($apprDept) :: $note";
        }

        DB::table('moving_asset')
            ->where('ID', $id)
            ->update([
                'Approval_Note' => $newNote,
                'Status' => $newStatus,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil melakukan approval',
            'newStatus' => $newStatus,
        ]);
    }
}
