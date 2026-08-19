<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetIncomingApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.incoming-asset.approval.index');
    }

    public function data()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $lsp = Auth::user()->Location;
        $username = Auth::user()->username;
        $dept = Auth::user()->Department;

        // Ambil konfigurasi master_hr_approval untuk user aktif
        $approvalConfig = DB::table('master_hr_approval')
            ->where('username', $username)
            ->where('Status', 'ACTIVE')
            ->first();

        $userLevel = $approvalConfig ? (int)$approvalConfig->Level : 3;

        $query = DB::table("good_receive")
            ->where("Status", '!=', 'CANCELED')
            ->where('Location', $lsp)
            ->orderBy("ID", "ASC");

        // Filter berdasarkan level approval:
        // Stage 1 (Level 3 - Manager HCGA/Finance): Tampilkan status 'CREATED' atau 'PROPOSED'
        // Stage 2 (Level 1 - Vice President / VP / DIR): Tampilkan status 'APPROVAL_MANAGER'
        if ($userLevel == 1 || $dept == 'DIR') {
            $query->whereIn("Status", ['APPROVAL_MANAGER', 'APPROVED']);
        } else {
            $query->whereIn("Status", ['CREATED', 'PROPOSED', 'APPROVAL_MANAGER']);
        }

        $records = $query->get();
        $nomor = 1;

        echo "<rows>";
        foreach ($records as $dapat) {
            echo "<row id=\"$dapat->ID\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>" . htmlspecialchars($dapat->GR_No) . "</cell>";
                echo "<cell>" . htmlspecialchars($dapat->PO_No) . "</cell>";
                echo "<cell>" . htmlspecialchars($dapat->Reference) . "</cell>";
                echo "<cell>" . htmlspecialchars($dapat->PR_No) . "</cell>";
                echo "<cell>" . htmlspecialchars($dapat->Status) . "</cell>";
                echo "<cell>" . htmlspecialchars($dapat->Apply_To ?? '-') . "</cell>";
                echo "<cell>" . htmlspecialchars($dapat->Date_Created) . "</cell>";
            echo "</row>";
            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $gr = DB::table('good_receive')->where('ID', $request->ID)->first();
        $po = null;
        $poDetails = [];

        if ($gr && $gr->PO_No && $gr->PO_No != 'Manual') {
            $po = DB::table('purchase_order')->where('PO_No', $gr->PO_No)->first();
            $poDetails = DB::table('purchase_order_dtl')->where('PO_No', $gr->PO_No)->get();
        }

        $assets = [];
        if ($gr) {
            $assets = DB::table('asset_list')->where('GR_No', $gr->GR_No)->orWhere('PO_No', $gr->PO_No)->get();
        }

        return view('humanresources.incoming-asset.approval.detail', compact('gr', 'po', 'poDetails', 'assets'));
    }

    public function approval(Request $request)
    {
        $id = $request->ID;
        $approvalType = $request->Approval; // 1 = Approve, 2 = Reject
        $note = $request->Note ?? '';
        $username = Auth::user()->username;
        $dept = Auth::user()->Department;

        $approvalConfig = DB::table('master_hr_approval')
            ->where('username', $username)
            ->where('Status', 'ACTIVE')
            ->first();

        $userLevel = $approvalConfig ? (int)$approvalConfig->Level : 3;

        $gr = DB::table('good_receive')->where('ID', $id)->first();
        if (!$gr) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Good Receive tidak ditemukan',
            ]);
        }

        if ($approvalType == 2) {
            // Rejected
            $newStatus = 'REJECTED';
            $appendNote = date('Y-m-d H:i:s') . " REJECTED by $username ($dept) :: $note";
        } else {
            // Approved
            if ($userLevel == 1 || $dept == 'DIR') {
                // Vice President / VP Final Approval
                $newStatus = 'APPROVED';
                $appendNote = date('Y-m-d H:i:s') . " FINAL APPROVED (VP) by $username :: $note";
            } else {
                // Manager Approval (Stage 1)
                $newStatus = 'APPROVAL_MANAGER';
                $appendNote = date('Y-m-d H:i:s') . " MANAGER APPROVED by $username ($dept) :: $note";
            }
        }

        $existingRemark = $gr->Remark ? $gr->Remark . "\n" : "";
        $updatedRemark = $existingRemark . $appendNote;

        DB::table('good_receive')->where('ID', $id)->update([
            'Status' => $newStatus,
            'Remark' => $updatedRemark,
        ]);

        if ($newStatus === "APPROVED") {
            $assets = DB::table('asset_list')->where("GR_No", $gr->GR_No)->get();
            foreach ($assets as $key => $value) {
                DB::table('asset_list')->where("Asset_No", $value->Asset_No)->update([
                    'Status' => "ACTIVE",
                    'Last_User' => $username,
                    'Last_Modified_Date' => now(),
                ]);
            }
        } else if ($newStatus === "REJECTED") {
            $assets = DB::table('asset_list')->where("GR_No", $gr->GR_No)->get();
            foreach ($assets as $key => $value) {
                DB::table('asset_list')->where("Asset_No", $value->Asset_No)->update([
                    'Status' => "REJECTED",
                    'Last_User' => $username,
                    'Last_Modified_Date' => now(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memproses approval Asset Incoming (' . $newStatus . ')',
        ]);
    }
}
