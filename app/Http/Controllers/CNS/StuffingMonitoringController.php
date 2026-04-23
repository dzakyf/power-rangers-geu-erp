<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\VesselScheduleDtl;
use Illuminate\Http\Request;

class StuffingMonitoringController extends Controller
{
    public function indexStuffingMonitoring()
    {
        return view('cns.export.stuffing-monitoring');
    }

    public function indexStuffingMonitoringXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vessels = VesselScheduleDtl::get();
        foreach ($vessels as $key => $vessel) {
            echo "<row id=\"$vessel->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i",strtotime($vessel->Time_From))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i",strtotime($vessel->Time_To))) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Police_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Container) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Seal) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Tare) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Netto_KG) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Netto_MT) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Gross_KG) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Gross_MT) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function updateStuffingMonitoring(Request $request)
    {
        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));

        $ids = explode("&", $request_body);
        foreach ($ids as $datax) {

            $idenya = explode("=", $datax);
            $param[$idenya[0]] = $idenya[1];
        }
        $checkids = explode(",", $param["ids"]);

        for ($i = 0; $i < sizeof($checkids); $i++) {
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId . "_!nativeeditor_status"];

            switch ($mode) {
                case "updated":
                    $action = $this->updateStuffingMonitoringUpdate($rowId, $param);
                    break;
                case "inserted":
                    $action = $this->updateStuffingMonitoringInsert($rowId, $param);
                    break;
                case "deleted":
                    $action = $this->updateStuffingMonitoringDelete($rowId);
                    break;
            }

            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo "</data>";
    }

    public function updateStuffingMonitoringUpdate($rowId, $param)
    {
        $a = $param[$rowId . "_c1"]; // Time From
        $b = $param[$rowId . "_c2"]; // Time To
        $c = $param[$rowId . "_c3"]; // Date
        $d = $param[$rowId . "_c4"]; // Police No
        $e = $param[$rowId . "_c5"]; // Container
        $f = $param[$rowId . "_c6"]; // Seal
        $g = $param[$rowId . "_c7"]; // Tare
        $h = $param[$rowId . "_c8"]; // Netto KG
        $ix = $param[$rowId . "_c9"]; // Netto MT
        $j = $param[$rowId . "_c10"]; // Gross KG
        $k = $param[$rowId . "_c11"]; // Gross MT
        $l = $param[$rowId . "_c12"]; // Gross MT

        $vsd = VesselScheduleDtl::where('id', $rowId)->first();
        if ($vsd) {
            $vsd->Time_From = $a;
            $vsd->Time_To = $b;
            $vsd->Date = $c;
            $vsd->Police_No = $d;
            $vsd->Container = $e;
            $vsd->Seal = $f;
            $vsd->Tare = $g;
            $vsd->Netto_KG = $h;
            $vsd->Netto_MT = $ix;
            $vsd->Gross_KG = $j;
            $vsd->Gross_MT = $k;
            $vsd->Note = $l;
            $vsd->Updated_By = auth()->user()->username;
            $vsd->Updated_Date = date('Y-m-d H:i:s');
        }

        if ($vsd->save()) {
            return "updated";
        } else {
            return "error";
        }
    }

    public function updateStuffingMonitoringInsert($rowId, $param)
    {
        $a = $param[$rowId . "_c1"]; // Time From
        $b = $param[$rowId . "_c2"]; // Time To
        $c = $param[$rowId . "_c3"]; // Date
        $d = $param[$rowId . "_c4"]; // Police No
        $e = $param[$rowId . "_c5"]; // Container
        $f = $param[$rowId . "_c6"]; // Seal
        $g = $param[$rowId . "_c7"]; // Tare
        $h = $param[$rowId . "_c8"]; // Netto KG
        $ix = $param[$rowId . "_c9"]; // Netto MT
        $j = $param[$rowId . "_c10"]; // Gross KG
        $k = $param[$rowId . "_c11"]; // Gross MT
        $l = $param[$rowId . "_c12"]; // Gross MT

        $vsd = new VesselScheduleDtl();
        $vsd->Time_From = $a;
        $vsd->Time_To = $b;
        $vsd->Date = $c;
        $vsd->Police_No = $d;
        $vsd->Container = $e;
        $vsd->Seal = $f;
        $vsd->Tare = $g;
        $vsd->Netto_KG = $h;
        $vsd->Netto_MT = $ix;
        $vsd->Gross_KG = $j;
        $vsd->Gross_MT = $k;
        $vsd->Note = $l;
        $vsd->Created_By = auth()->user()->username;
        $vsd->Created_Date = date('Y-m-d H:i:s');

        if ($vsd->save()) {
            return "updated";
        } else {
            return "error";
        }
    }

    public function updateStuffingMonitoringDelete($rowId)
    {
        $vsd = VesselScheduleDtl::where('id', $rowId)->first();
        if ($vsd) {
            if ($vsd->delete()) {
                return "deleted";
            } else {
                return "error";
            }
        }else {
            return "error";
        }
    }

    

    public function comboPoliceNumber()
    {
        header('Content-Type: application/json');
            
        echo "{
        options: ["; 
        $qy = VesselScheduleDtl::get();
        $dx = "";
        foreach ($qy as $ax){
            
                $dx .= "{value:'$ax->Police_No',text:'$ax->Police_No - $ax->Order_No'},";

            
        }
        $dx = substr($dx,0,-1);

        echo "
        $dx
        ]
        }"; 
    }
}
