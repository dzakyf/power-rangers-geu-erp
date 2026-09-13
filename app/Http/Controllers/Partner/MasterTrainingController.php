<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterTraining;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterTrainingController extends Controller
{
    public function indexTraining()
    {
        $frequency = (new MasterTraining)->frequencyMonth;
        return view('partner.master-training', compact('frequency'));
    }

    public function indexTrainingXml()
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

        $get = MasterTraining::orderBy('created_date', 'asc')->get();

        $frequency = (new MasterTraining)->frequencyMonth;
        
        foreach ($get as $key => $Fetch) {
            $name = $usname[$Fetch->created_by];
            // Schedule only applies to non-monthly frequencies with a valid month.
            $month = '';
            if ($Fetch->frequency != 'month' && (int) $Fetch->training_month >= 1 && (int) $Fetch->training_month <= 12) {
                $month = date('F Y', strtotime(date('Y').'-'.sprintf('%02d', $Fetch->training_month).'-01'));
            }

            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->target) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->training_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->frequency) . "</cell>";
            echo "<cell>" . htmlspecialchars($month) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function updateTraining(Request $request)
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
                            $action = $this->updateTrainingUpdate($rowId,$param);
                            break;
                    case "deleted":
                            $action = $this->updateTrainingDelete($rowId,$param);
                            break;
                    case "inserted":
                            $action = $this->updateTrainingInsert($rowId,$param);
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

    public function updateTrainingInsert($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;
        $counter = MasterTraining::where("created_date", "LIKE", date('Y') . "%")->count();
        $task = "TRN/GEU/".date("Y")."/".sprintf("%04d", $counter + 1);
        
        MasterTraining::create([
            'training_no' => $task,
            'target' => $a,
            'training_name' => $b,
            'frequency' => $c,
            'training_month' => ($c !== 'month' && $d && strtotime($d)) ? (int) date('m', strtotime($d)) : 0,
            'created_by' => $Saya,
            'created_date' => now(),
        ]);

    }

    public function updateTrainingUpdate($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;

        MasterTraining::where('id', $rowId)->update([
            'target' => $a,
            'training_name' => $b,
            'frequency' => $c,
            'training_month' => ($c !== 'month' && $d && strtotime($d)) ? (int) date('m', strtotime($d)) : 0,
        ]);
    }

    public function updateTrainingDelete($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport

        $Saya = auth()->user()->username;
        $LSP = auth()->user()->Location;

        MasterTraining::where('id', $rowId)->delete();
    }
}
