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
        
        $nik = Auth::user()->Emp_ID;
        $dept = Auth::user()->Department;
        $saya = Auth::user()->Name;
        $nomor = 1;

        echo "<rows>";

        $Query = DB::table("moving_asset")->orderBy("DateTrx","DESC");
        if ($dept != 'HCGA' && $dept != 'DIR') {
            $Query->where(function($q) use ($dept) {
                $q->where("Department", $dept)
                ->where("Status", 'APPROVAL_UPPER');
            })->orWhere(function($q) use ($dept) {
                $q->where("Receipent_Department", $dept)
                ->where("Status", 'APPROVAL_RECEIPENT');
            });
        }else {
            if ($dept == 'HCGA') {
                $Query->where("Status", "APPROVAL_GA");
            }else {
                $Query->where("Status", "APPROVAL_BOD");
            }
        }

        $Query = $Query->get();

        foreach($Query as $Fetch){
            // $gudangs = DB::table('gudang_list')->whereIn("Gudang_ID", [$Fetch->Move_To, $Fetch->Move_From])->get()->pluck('Description', 'Gudang_ID');
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
        $assetMoving = DB::table('asset_moving')->where("ID", $request->ID)->first();
        return view('humanresources.asset-moving.approval.detail', compact("assetMoving"));
    }

    public function approval(Request $request)
    {
        $note = "";
        if($request->Data == '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat membaca data',
            ]);
        }
        $asset = DB::table('asset_moving')->where("ID", $request->Data)->first();
        if ($asset->Approval_No != '' && $asset->Approval_No != null) {
            $note = $asset->Approval_Note . " \n ";
        }
        if ($request->Approval == 2) {
            $note .= date('Y-m-d H:i:s') . " REJECTED by " . Auth::user()->username . " :: " . $request->Note;
            $status = "REJECTED";
        }else {
            $note .= date('Y-m-d H:i:s') . " APPROVED by " . Auth::user()->username . " :: " . $request->Note;
            if ($asset->Status == 'APPROVAL_RECEIPENT') {
                $status = "APPROVAL_GA";
            }else if ($asset->Status == 'APPROVAL_GA'){
                $status = "APPROVAL_BOD";
            }else {
                $status = "APPROVAL_RECEIPENT";
            }
        }

        DB::table('asset_moving')->where("ID", $asset->ID)->update([
            'Approval_Note' => $note,
            'Status' => $status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil melakukan approval',
        ]);
    }
}
