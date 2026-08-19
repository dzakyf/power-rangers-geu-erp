<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetHandoverReturnedApprovalController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-handover.returned.approval.index');
    }

    public function data()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        $productList = [];
        $products = DB::table('asset_handover_dtl')->whereIn('Status', ['RETURN_PENDING', 'RETURNED'])->get();
        foreach ($products as $key => $value) {
            $productList[$value->Handover_No][] = $value->Description;
        }

        $priv = Auth::user()->Priv_ID;
        $nik = Auth::user()->Emp_ID;

        // Ambil handover yang memiliki detail berstatus RETURN_PENDING atau RETURNED
        $pendingHandoverNos = DB::table('asset_handover_dtl')->whereIn('Status', ['RETURN_PENDING', 'RETURNED'])->pluck('Handover_No')->toArray();

        $getHandover = DB::table('asset_handover')
                        ->whereIn("Handover_No", $pendingHandoverNos);
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
        return view('humanresources.asset-handover.returned.approval.detail', compact('ah'));
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
                echo "<cell>0</cell>";
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
                echo "<cell>".htmlspecialchars($value->Status)."</cell>";
                echo "<cell>".htmlspecialchars($value->Returned_Qty ?? $value->Qty)."</cell>";
                echo "<cell>".htmlspecialchars($value->Returned_Condition ?? 'Baik')."</cell>";
                echo "<cell>".htmlspecialchars($value->Returned_Date ?? date('Y-m-d'))."</cell>";
                echo "<cell>".htmlspecialchars($value->Returned_Remark ?? '')."</cell>";
            echo "</row>";
        }
        echo "</rows>";
    }

    public function store(Request $request)
    {
        $r = $request->all();
        $handoverNo = $r['Handover_No'] ?? null;
        $status = $r['status'] ?? 'RETURNED';

        if ($handoverNo) {
            // Cek apakah seluruh detail item sudah berstatus RETURNED
            $unreturnedCount = DB::table('asset_handover_dtl')
                                ->where('Handover_No', $handoverNo)
                                ->where('Status', '!=', 'RETURNED')
                                ->count();

            if ($unreturnedCount == 0) {
                DB::table('asset_handover')->where('Handover_No', $handoverNo)->update([
                    'Status' => 'RETURNED',
                    'Updated_By' => Auth::user()->username,
                    'Updated_Date' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memproses approval pengembalian $handoverNo",
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
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
    }

    function storeDetailUpdate(string $rowId, array $param)
    {
        $check = $param[$rowId . "_c0"] ?? 0; // Checkbox
        $targetStatus = $param[$rowId . "_c16"] ?? 'RETURNED'; // Status per detail

        if ($check == "" || $check == "0") {
            return "not update";
        }

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update([
            'Status' => $targetStatus,
        ]);

        return "update";
    }
}
