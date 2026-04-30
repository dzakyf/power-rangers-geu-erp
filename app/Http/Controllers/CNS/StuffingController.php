<?php

namespace App\Http\Controllers\CNS;

use App\Http\Controllers\Controller;
use App\VesselSchedule;
use App\VesselScheduleDtl;
use Illuminate\Http\Request;

class StuffingController extends Controller
{
    public function indexStuffing()
    {
        return view('cns.vs.stuffing');
    }

    public function indexStuffingXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $dtl = VesselScheduleDtl::get();
        foreach ($dtl as $key => $value) {
            $vsd[$value->Order_No][] = $value->Container . " (" . $value->Seal . ")";
        }

        // Sample data, replace with actual database query
        $vessels = VesselSchedule::whereBetween('Stuffing',[$request->tgl1,$request->tgl2])->get();
        foreach ($vessels as $key => $vessel) {
            $warna = "style='background-color: #ffccccff'";
            if (isset($vsd[$vessel->Order_No])) {
                if (count($vsd[$vessel->Order_No]) < $vessel->Qty1) {
                    $warna = "style='background-color: #feffccff'";
                }else if (count($vsd[$vessel->Order_No]) == $vessel->Qty1) {
                    $warna = "style='background-color: #ccffcfff'";
                }
            }
            echo "<row id=\"$vessel->id\" $warna>";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Order_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Vessel) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->POL) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->POD) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Stuffing != "" ? date("d F Y",strtotime($vessel->Stuffing)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Checking_BC != "" ? date("d F Y",strtotime($vessel->Checking_BC)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Closing_CY != "" ? date("d F Y",strtotime($vessel->Closing_CY)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ETD != "" ? date("d F Y",strtotime($vessel->ETD)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ATD != "" ? date("d F Y",strtotime($vessel->ATD)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ETA != "" ? date("d F Y",strtotime($vessel->ETA)) : "") . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->ATA != "" ? date("d F Y",strtotime($vessel->ATA)) : "") . "</cell>";
            if ($vessel->ETD != "0000-00-00" && $vessel->ETA != "0000-00-00") {
                $etd = new \DateTime($vessel->ETD);
                $eta = new \DateTime($vessel->ETA);
                $interval = $etd->diff($eta);
                echo "<cell>" . htmlspecialchars($interval->format('%a days')) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            if ($vessel->ATD != "0000-00-00" && $vessel->ATA != "0000-00-00") {
                $atd = new \DateTime($vessel->ATD);
                $ata = new \DateTime($vessel->ATA);
                $interval = $atd->diff($ata);
                echo "<cell>" . htmlspecialchars($interval->format('%a days')) . "</cell>";
            } else {
                echo "<cell></cell>";
            }
            echo "<cell>" . htmlspecialchars($vessel->Note) . "</cell>";
            if (isset($vsd[$vessel->Order_No])) {
                echo "<cell>" . htmlspecialchars(implode(', ',$vsd[$vessel->Order_No])) . "</cell>";
            }else {
                echo "<cell></cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function detailStuffing(Request $request)
    {
        $vs = VesselSchedule::where('id', $request->task)->first();
        return view('cns.vs.stuffing-detail', compact('vs'));
    }

    public function detailStuffingXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $vessels = VesselScheduleDtl::where('Task_ID', $request->task)->get();
        foreach ($vessels as $key => $vessel) {
            echo "<row id=\"$vessel->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i",strtotime($vessel->Time_From))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date("H:i",strtotime($vessel->Time_To))) . "</cell>";
            echo "<cell>" . htmlspecialchars(date('d F Y', strtotime($vessel->Date))) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Police_No) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Container) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Seal) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Tare) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Netto_KG) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Netto_MT) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Gross_KG) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Gross_MT) . "</cell>";
            echo "<cell>" . htmlspecialchars($vessel->Note) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function updateDetailStuffing(Request $request)
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
                    $action = $this->updateDetailStuffingUpdate($rowId, $param);
                    break;
                case "inserted":
                    $action = $this->updateDetailStuffingInsert($rowId, $param);
                    break;
                case "deleted":
                    $action = $this->updateDetailStuffingDelete($rowId);
                    break;
            }

            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo "</data>";
    }

    public function updateDetailStuffingUpdate($rowId, $param)
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
            $vsd->Date = date("Y-m-d", strtotime($c));
            $vsd->Police_No = $d;
            $vsd->Container = $e;
            $vsd->Seal = $f;
            $vsd->Tare = $g;
            $vsd->Netto_KG = $h;
            $vsd->Netto_MT = $ix;
            $vsd->Gross_KG = $j;
            $vsd->Gross_MT = $k;
            $vsd->Note = $l;
            $vsd->Status = 'CLOSED';
            $vsd->Updated_By = auth()->user()->username;
            $vsd->Updated_Date = date('Y-m-d H:i:s');
        }

        if ($vsd->save()) {
            return "updated";
        } else {
            return "error";
        }
    }

    public function updateDetailStuffingInsert($rowId, $param)
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

        $cekOrder = VesselSchedule::where('Order_No', $_GET["task"])->first();
        if ($cekOrder) {
            $vsd = new VesselScheduleDtl();
            $vsd->Order_No = $_GET["task"];
            $vsd->Task_ID = $cekOrder->Task_ID;
            $vsd->Time_From = $a;
            $vsd->Time_To = $b;
            $vsd->Date = date("Y-m-d", strtotime($c));
            $vsd->Police_No = $d;
            $vsd->Container = $e;
            $vsd->Seal = $f;
            $vsd->Tare = $g;
            $vsd->Netto_KG = $h;
            $vsd->Netto_MT = $ix;
            $vsd->Gross_KG = $j;
            $vsd->Gross_MT = $k;
            $vsd->Note = $l;
            $vsd->Status = 'CLOSED';
            $vsd->Created_By = auth()->user()->username;
            $vsd->Created_Date = date('Y-m-d H:i:s');

            if ($vsd->save()) {
                return "updated";
            } else {
                return "error";
            }
        }else {
            return "error";
        }
    }

    public function updateDetailStuffingDelete($rowId)
    {
        $vsd = VesselScheduleDtl::where('id', $rowId)->first();
        if ($vsd) {
            $vsd->Status = 'CANCELED';

            return "deleted";
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
