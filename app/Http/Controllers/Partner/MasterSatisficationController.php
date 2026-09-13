<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterSatisfication;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterSatisficationController extends Controller
{
    public function indexSatisfication()
    {
        return view('partner.training.master-satisfication');
    }

    public function indexSatisficationXml()
    {
        if (!headers_sent()) {
            if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
                header("Content-type: application/xhtml+xml");
            } else {
                header("Content-type: text/xml");
            }
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

        $get = MasterSatisfication::orderBy('created_date', 'asc')->get();
        
        foreach ($get as $key => $Fetch) {
            $name = $usname[$Fetch->created_by];

            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->target) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->question) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->created_date) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function updateSatisfication(Request $request)
    {
        
        if (!headers_sent()) {
            header("Content-type: text/xml");
        }
        echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 
        echo "<data>";

        $request_body = urldecode(request()->getContent());
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
                            $action = $this->updateSatisficationUpdate($rowId,$param);
                            break;
                    case "deleted":
                            $action = $this->updateSatisficationDelete($rowId,$param);
                            break;
                    case "inserted":
                            $action = $this->updateSatisficationInsert($rowId,$param);
                            break;
                    }
                    
                echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
            }
        }
        echo "</data>";

    }

    public function getDateMonth($month)
    {
        $getDate = (int)date("m", strtotime($month));
        return $getDate;
    }

    public function updateSatisficationInsert($rowId, $param)
    {
        $a = $param[$rowId."_c1"] ?? null; // peruntukan
        $b = $param[$rowId."_c2"] ?? null; // pertanyaan

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;
        
        MasterSatisfication::create([
            'target' => $a,
            'question' => $b,
            'created_by' => $Saya,
            'created_date' => now(),
        ]);

    }

    public function updateSatisficationUpdate($rowId, $param)
    {
        $a = $param[$rowId."_c1"] ?? null; // peruntukan
        $b = $param[$rowId."_c2"] ?? null; // pertanyaan

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;

        MasterSatisfication::where('id', $rowId)->update([
            'target' => $a,
            'question' => $b,
        ]);
    }

    public function updateSatisficationDelete($rowId, $param)
    {
        $a = $param[$rowId."_c1"] ?? null; // peruntukan
        $b = $param[$rowId."_c2"] ?? null; // pertanyaan

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;

        MasterSatisfication::where('id', $rowId)->delete();
    }
}
