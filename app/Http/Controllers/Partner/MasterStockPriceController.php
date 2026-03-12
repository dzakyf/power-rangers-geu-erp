<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterStockPriceController extends Controller
{
    public function index()
    {
        return view('partner.master-stock-price');
    }

    public function indexXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;

        $get = MasterStockPrice::orderBy('created_date', 'desc')->get();
        
        foreach ($get as $key => $Fetch) {
            $name = "AUTO_SYSTEM";
            if ($Fetch->created_by != 'auto_sys') {
                $name = $usname[$Fetch->created_by];
            }
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->date_for) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->price) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function update()
    {
        header("Content-type: text/xml");
        echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 
        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));
        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        $ids = explode("&",$request_body);
        foreach($ids as $datax){
            
            $idenya = explode("=",$datax);
            if (isset($idenya[1])) {
                $param[$idenya[0]] = $idenya[1];
            }
            
        }
        $checkids = explode(",",$param["ids"]);

        for ($i=0; $i < sizeof($checkids); $i++) { 
            $rowId = $checkids[$i]; //id or row which was updated 
            if ($rowId != 'customPayload') {
                $newId = $rowId; //will be used for insert operation	
                $mode = $param[$rowId."_!nativeeditor_status"]; 
            
                    switch($mode){
                        case "updated":
                            $action = $this->updatekan($rowId,$param);
                            break;
                    case "deleted":
                            $action = $this->deletekan($rowId,$param);
                            break;
                    case "inserted":
                            $action = $this->insertkan($rowId,$param);
                            break;
                    }
                    
                echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
            }
        }
        echo "</data>";

    }

    public function insertkan($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;
        
        $cekDate = MasterStockPrice::where("date_for", $a)->first();

        if ($cekDate) {
            MasterStockPrice::where('id', $rowId)->update([
                'price' => $b,
            ]);
        }else {
            MasterStockPrice::create([
                'location' => $LSP,
                'price' => $b,
                'date_for' => $a,
                'created_by' => $Saya,
                'created_date' => now(),
            ]);
        }

    }

    public function updatekan($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;

        MasterStockPrice::where('id', $rowId)->update([
            'price' => $b,
        ]);
    }

    public function deletekan($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;

        MasterStockPrice::where('id', $rowId)->delete();
    }

    public function getCurrentPrice(Request $request)
    {
        $getPrice = MasterStockPrice::where("date_for", $request->date)->first();
        
        $res = [];
        if ($getPrice) {
            $res = [
                'status' => 'success',
                'data' => [
                    'price' => $getPrice->price,
                ]
            ];
        }else {
            $res = [
                'status' => 'error',
                'data' => [],
            ];
        }

        echo json_encode($res);
    }
}
