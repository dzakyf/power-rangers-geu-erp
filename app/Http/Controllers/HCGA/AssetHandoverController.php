<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetHandoverController extends Controller
{
    public function index()
    {
        return view('humanresources.asset-handover.index');
    }

    public function data(Request $request)
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

        $getHandover = DB::table('asset_handover');
        if ($priv != 'HCGA' && $priv != 'ICT') {
            $getHandover->where("User_NIK", $nik)->orWhere("PIC_NIK", $nik);
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

    public function new()
    {
        $LSP = Auth::user()->Location;
        $NIK = Auth::user()->Emp_ID;
        $Saya = Auth::user()->username;
        $counter = DB::table('asset_handover')->where("Created_By", "LIKE", date("Y-m")."%")->count() + 1;
        $Handover_No = "HA/GA/$LSP/".date("ymdhis") . "-" . $counter;
        return view('humanresources.asset-handover.create', compact("Handover_No", 'NIK', 'Saya'));
    }

    public function product()
    {
        return view('humanresources.asset-handover.product');
    }

    public function productData()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $getAsset = DB::table('asset_list')->groupBy("Product_Code")->where("User_Responsible", null)->selectRaw("Product_Code, COUNT(ID) as cid")->get()->pluck("cid", "Product_Code");

        $getSupplies = DB::table('supplies')->groupBy("Product_Code")->selectRaw("Product_Code, SUM(Qty) as qty")->get()->pluck("qty", "Product_Code");
        
        $nomor = 1;
        echo "<rows>";
        $getProduct = DB::table('product_code')->get();
        foreach ($getProduct as $key => $value) {
            $isAsset = isset($getAsset[$value->Product_Code]) && $getAsset[$value->Product_Code] > 0;
            $isSupplies = isset($getSupplies[$value->Product_Code]) && $getSupplies[$value->Product_Code] > 0;

            if ($isAsset || $isSupplies) {
                if ($isAsset) {
                    $cekstok = $getAsset[$value->Product_Code];
                }else {
                    $cekstok = $getSupplies[$value->Product_Code];
                }
                echo "<row id=\"$value->ID\">";
                    echo "<cell>$nomor</cell>";
                    echo "<cell>".htmlspecialchars(($value->Product_Code))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Description))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Specification))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Main_Category))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Category))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Supplies_Type))."</cell>";
                    echo "<cell>".$cekstok."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Unit_Name))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Foto))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Dimension))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Price))."</cell>";
                    echo "<cell>".htmlspecialchars(($value->Product_Brand))."</cell>";
                echo "</row>";	

                $nomor++;


            }
        }
        echo "</rows>";
    }

    public function store(Request $request)
    {
        $r = $request->all();

        unset($r['_token']);
        unset($r['AddAssetHandover']);
        unset($r['DelAssetHandover']);
        unset($r['SubmitAssetHandover']);

        $r['Created_By'] = Auth::user()->username;
        $r['Created_Date'] = now();
        $r['Status'] = "CREATED";
        $r['Reference'] = $r['Reference'] ?? '';

        DB::table('asset_handover')->insert($r);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menginput data",
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
                case "inserted":
                    $action = $this->storeDetailInsert($rowId, $param);
                    break;
            }
            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
    }

    function storeDetailInsert(string $rowId, array $param)
    {
        $a = $param[$rowId . "_c1"]; // Supplies_Type
        $b = $param[$rowId . "_c2"]; // Product_Code
        $c = $param[$rowId . "_c3"]; // Description
        $d = $param[$rowId . "_c4"]; // Qty
        $e = $param[$rowId . "_c5"]; // Product_Brand
        $f = $param[$rowId . "_c6"]; // Product_Type
        $g = $param[$rowId . "_c7"]; // Specification
        $h = $param[$rowId . "_c8"]; // Condition
        $ix = $param[$rowId . "_c9"]; // Measurement
        $j = $param[$rowId . "_c10"]; // Note
        $k = $param[$rowId . "_c11"]; // Main_Category
        $l = $param[$rowId . "_c12"]; // Category
        $m = $param[$rowId . "_c13"] ?? 0; // Price
        $n = $param[$rowId . "_c14"] ?? 0; // Total
        $o = $param[$rowId . "_c15"]; // Received_Date

        if ($o == "") {
            $o = null;
        }

        // $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        DB::table('asset_handover_dtl')->insert([
            'Handover_No' => $_GET['Handover_No'],
            'Purchase_Category' => $l,
            'Purchase_Type' => $a,
            'Category' => $k,
            'Product_Code' => $b,
            'Description' => $c,
            'Specification' => $g,
            'Product_Type' => $f,
            'Product_Brand' => $e,
            'Condition' => $h,
            'Qty' => $d,
            'Default_Price' => $m,
            'Unit' => $ix,
            'Remark' => $j,
            'Location' => $LSP,
            'Received_Date' => $o,
            'Prices' => $n,
        ]);

        return "insert";
    }

    function storeDetailUpdate(string $rowId, array $param)
    {
        $a = $param[$rowId . "_c1"]; // Supplies_Type
        $b = $param[$rowId . "_c2"]; // Product_Code
        $c = $param[$rowId . "_c3"]; // Description
        $d = $param[$rowId . "_c4"]; // Qty
        $e = $param[$rowId . "_c5"]; // Product_Brand
        $f = $param[$rowId . "_c6"]; // Product_Type
        $g = $param[$rowId . "_c7"]; // Specification
        $h = $param[$rowId . "_c8"]; // Condition
        $ix = $param[$rowId . "_c9"]; // Measurement
        $j = $param[$rowId . "_c10"]; // Note
        $k = $param[$rowId . "_c11"]; // Main_Category
        $l = $param[$rowId . "_c12"]; // Category
        $m = $param[$rowId . "_c13"] ?? 0; // Price
        $n = $param[$rowId . "_c14"] ?? 0; // Total
        $o = $param[$rowId . "_c15"]; // Received_Date

        if ($o == "") {
            $o = null;
        }

        // $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        DB::table('asset_handover_dtl')->where('ID', $rowId)->update([
            'Purchase_Category' => $l,
            'Purchase_Type' => $a,
            'Category' => $k,
            'Product_Code' => $b,
            'Description' => $c,
            'Specification' => $g,
            'Product_Type' => $f,
            'Product_Brand' => $e,
            'Condition' => $h,
            'Qty' => $d,
            'Default_Price' => $m,
            'Unit' => $ix,
            'Remark' => $j,
            'Location' => $LSP,
            'Received_Date' => $o,
            'Prices' => $n,
        ]);

        return "update";
    }

    public function detail(Request $request)
    {
        $ah = DB::table('asset_handover')->where('Handover_No', $request->Handover_No)->first();
        return view('humanresources.asset-handover.detail', compact('ah'));
    }

    public function detailData(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        $getHandover = DB::table('asset_handover_dtl')->where("Handover_No", $request->Handover_No)->get();
        $nomor = 1;
        echo "<rows>";
        foreach ($getHandover as $key => $value) {
            echo "<row id='$value->ID'>";
                echo "<cell>$nomor</cell>";
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
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function update(Request $request)
    {
        $r = $request->all();

        unset($r['ID']);
        unset($r['_token']);
        unset($r['AddAssetHandover']);
        unset($r['DelAssetHandover']);
        unset($r['SubmitAssetHandover']);

        $r['Updated_By'] = Auth::user()->username;
        $r['Updated_Date'] = now();
        $r['Reference'] = $r['Reference'] ?? '';

        DB::table('asset_handover')->where('ID', $request->ID)->insert($r);

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil update data",
        ]);
    }

    public function log(Request $request)
    {
        return view('humanresources.asset-handover.log');
    }
}
