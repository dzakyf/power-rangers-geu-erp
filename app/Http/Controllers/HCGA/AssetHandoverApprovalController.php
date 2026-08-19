<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetHandoverApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-handover.approval.index');
    }

    public function data()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        $productList = [];
        $products = DB::table('asset_handover_dtl')->get();
        foreach ($products as $key => $value) {
            $productList[$value->Handover_No][] = $value->Description;
        }

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        $getHandover = DB::table('asset_handover')
                        ->whereIn("Status", ["CREATED","UPDATED"]);
        if ($priv != 'HCGA' && $priv != 'ICT') {
            $getHandover->where("PIC_NIK", $nik);
        }
        $getHandover = $getHandover->get();

        $nomor = 1;
        echo "<rows>";
        foreach ($getHandover as $key => $value) {
            echo "<row id='$value->ID'>";
                echo "<cell>$nomor</cell>";
                echo "<cell>".htmlspecialchars($value->Handover_No)."</cell>";
                echo "<cell>".htmlspecialchars($value->Handover_Date)."</cell>";
                echo "<cell>".htmlspecialchars($value->User_Name)."</cell>";
                echo "<cell>".htmlspecialchars($value->User_NIK)."</cell>";
                echo "<cell>".htmlspecialchars($value->PIC_Name)."</cell>";
                echo "<cell>".htmlspecialchars($value->PIC_Department)."</cell>";
                echo "<cell>".htmlspecialchars($value->PIC_Phone)."</cell>";
                echo "<cell>".htmlspecialchars($value->Reference)."</cell>";
                echo "<cell>".htmlspecialchars($value->Remark)."</cell>";
                echo "<cell>".htmlspecialchars(implode(', ',$productList[$value->Handover_No] ?? []))."</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detail(Request $request)
    {
        $ah = DB::table('asset_handover')->where("Handover_No", $request->Handover_No)->first();
        return view('humanresources.asset-handover.approval.detail', compact('ah'));
    }

    public function detailData(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $getHandover = DB::table('asset_handover_dtl')->where("Handover_No", $request->Handover_No)->get();
        echo "<rows>";
        foreach ($getHandover as $key => $value) {
            echo "<row id='$value->ID'>";
                echo "<cell></cell>";
                echo "<cell>".htmlspecialchars($value->Purchase_Type)."</cell>";
                echo "<cell>".htmlspecialchars($value->Product_Code)."</cell>";
                echo "<cell>".htmlspecialchars($value->Description)."</cell>";
                echo "<cell>".htmlspecialchars($value->Qty)."</cell>";
                echo "<cell>".htmlspecialchars($value->Product_Brand)."</cell>";
                echo "<cell>".htmlspecialchars($value->Product_Type)."</cell>";
                echo "<cell>".htmlspecialchars($value->Specification)."</cell>";
                echo "<cell>".htmlspecialchars($value->Condition)."</cell>";
                echo "<cell>".htmlspecialchars($value->Unit)."</cell>";
                echo "<cell>".htmlspecialchars($value->Remark)."</cell>";
                echo "<cell>".htmlspecialchars($value->Category)."</cell>";
                echo "<cell>".htmlspecialchars($value->Purchase_Category)."</cell>";
                echo "<cell>".htmlspecialchars($value->Default_Price)."</cell>";
                echo "<cell>".htmlspecialchars($value->Prices)."</cell>";
                echo "<cell>".htmlspecialchars($value->Received_Date)."</cell>";
            echo "</row>";
        }
        echo "</rows>";
    }

    public function store(Request $request)
    {
        $r = $request->all();

        $handoverNo = $r['Handover_No'] ?? null;
        $status = $r['status'] ?? 'APPROVED';
        $approvalNote = $r['Approval_Note'] ?? '';
        $username = Auth::user()->username;

        if ($handoverNo) {
            $dataHandover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
            DB::table('asset_handover')->where('Handover_No', $handoverNo)->update([
                'Status' => $status,
                'Approval_Note' => $approvalNote,
                'Updated_By' => $username,
                'Updated_Date' => now(),
            ]);

            if ($status === 'APPROVED') {
                $assetHandover = DB::table('asset_handover_dtl')->where("Handover_No", $handoverNo)->get();

                foreach ($assetHandover as $key => $value) {
                    $assetExists = DB::table('asset_list')
                        ->where('GR_No', $value->GR_No)
                        ->where('Asset_Name', $value->Description)
                        ->first();

                    if ($assetExists) {
                        $getPICWH = DB::table('users')->select("WH_ID")->where("Emp_ID", $dataHandover->PIC_NIK)->first();
                        DB::table('asset_list')->where("ID", $assetExists->ID)->update([
                            'WH_ID' => $getPICWH->WH_ID,
                            'User_Responsible' => $dataHandover->PIC_Name,
                            'User_Responsible_NIK' => $dataHandover->PIC_NIK,
                        ]);
                    }
                }
            } else if ($status === 'REJECTED') {
                $assetHandover = DB::table('asset_handover_dtl')->where("Handover_No", $handoverNo)->get();

                foreach ($assetHandover as $key => $value) {
                    $assetExists = DB::table('asset_list')
                        ->where('GR_No', $value->GR_No)
                        ->where('Asset_Name', $value->Description)
                        ->first();

                    if ($assetExists) {
                        DB::table('asset_list')->where("ID", $assetExists->ID)->update([
                            'Status' => "REJECTED",
                            'Handover_Date' => now(),
                            'Last_User' => $username,
                            'Last_Modified_Date' => now(),
                        ]);
                    }
                }  
            }

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memproses approval handover $handoverNo",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Handover_No tidak ditemukan',
        ]);
    }

    public function storeDetail()
    {
        echo "<data>";
        $request_body = urldecode(file_get_contents('php://input'));

        $param = [];
        $ids = explode("&", $request_body);
        foreach ($ids as $datax) {

            $idenya = explode("=", $datax);
            $param[$idenya[0]] = $idenya[1];
        }
        $checkids = explode(",", $param["ids"]);
        for ($i = 0; $i < sizeof($checkids); $i++) {
            $action = "";
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId . "_!nativeeditor_status"];
            switch ($mode) {
                case "updated":
                    $action = $this->storeDetailUpdate($rowId, $param);
                    break;
                // case "deleted":
                //     $action = deletekan($rowId, $param);
                //     break;
                // case "inserted":
                //     $action = $this->storeDetailInsert($rowId, $param);
                //     break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
    }

    function storeDetailUpdate(string $rowId, array $param)
    {
        $check = $param[$rowId . "_c0"] ?? 0; // Checkbox
        $a = $param[$rowId . "_c1"] ?? ''; // Supplies_Type
        $b = $param[$rowId . "_c2"] ?? ''; // Product_Code
        $c = $param[$rowId . "_c3"] ?? ''; // Description
        $d = $param[$rowId . "_c4"] ?? 0; // Qty
        $e = $param[$rowId . "_c5"] ?? ''; // Product_Brand
        $f = $param[$rowId . "_c6"] ?? ''; // Product_Type
        $g = $param[$rowId . "_c7"] ?? ''; // Specification
        $h = $param[$rowId . "_c8"] ?? ''; // Condition
        $ix = $param[$rowId . "_c9"] ?? ''; // Measurement
        $j = $param[$rowId . "_c10"] ?? ''; // Note
        $k = $param[$rowId . "_c11"] ?? ''; // Main_Category
        $l = $param[$rowId . "_c12"] ?? ''; // Category
        $m = $param[$rowId . "_c13"] ?? 0; // Price
        $n = $param[$rowId . "_c14"] ?? 0; // Total
        $o = $param[$rowId . "_c15"] ?? null; // Received_Date
        $itemStatus = $param[$rowId . "_c16"] ?? 'APPROVED'; // Status per detail

        if ($o == "") {
            $o = null;
        }

        // Hanya update detail yang dicentang
        if ($check == "" || $check == "0") {
            return "not update";
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update([
            'Status' => $itemStatus,
            'Received_Date' => $o,
        ]);

        return "update";
    }
}
