<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function masterTarget()
    {
        return view('partner.sales.master-target');
    }

    public function masterTargetXml()
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
        $master = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
            if ($value->Priv_ID == 'SALES') {
                $master[$value->username] = [];
            }
        }

        $nomor = 1;
        $get = DB::table('opportunity_target')->get();
        foreach ($get as $key => $value) {
            $master[$value->Sales][$value->Year][(int)$value->Month] = $value->Target;
        }

        foreach ($master as $key => $Fetch) {
            foreach ($Fetch as $kunci => $item) {
                echo "<row id=\"$nomor\">";
                echo "<cell>$nomor</cell>";
                echo "<cell>" . htmlspecialchars($key) . "</cell>";
                echo "<cell>" . htmlspecialchars($kunci) . "</cell>";
                for ($i=1; $i <=12; $i++) { 
                    echo "<cell>" . htmlspecialchars($item[($i)] ?? 0) . "</cell>";
                }

                echo "</row>";

                $nomor++;
            }
        }
        echo "</rows>";
    }

    public function masterTargetUpdate()
    {
        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));
        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        $ids = explode("&",$request_body);
        foreach($ids as $datax){
            
            $idenya = explode("=",$datax);
            $param[$idenya[0]] = $idenya[1];
            
        }
        $checkids = explode(",",$param["ids"]);

        for ($i=0; $i < sizeof($checkids); $i++) { 
            $rowId = $checkids[$i]; //id or row which was updated 
            $newId = $rowId; //will be used for insert operation	
            $mode = $param[$rowId."_!nativeeditor_status"]; 
                $d = $param[$rowId."_c4"]; // transport
                $a = $param[$rowId."_c1"]; // origin
                $nomorx = $param[$rowId."_c0"];

                switch($mode){
                    case "updated":
                        $action = $this->masterTargetUpdatekan($rowId,$param);
                        break;
                    case "inserted":
                        $action = $this->masterTargetUpdatekan($rowId,$param);
                        break;
                }
                
            echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
        }
        echo "</data>";
    }
    
    function masterTargetUpdatekan($rowId,$param) {
        
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name

        $Saya = auth()->user()->username;
        for ($i=1; $i <= 12; $i++) { 
            $cek = DB::table('opportunity_target')->where("Sales", $a)->where('Year', $b)->where('Month', sprintf('%02d', $i))->first();
            if (!$cek) {
                DB::table('opportunity_target')->insert([
                    'Sales' => $a,
                    'Year' => $b,
                    'Month' => sprintf('%02d', $i),
                    'Target' => $param[$rowId."_c".($i+2)],
                    'Created_by' => $Saya,
                    'Created_Date' => date('Y-m-d H:i:s')
                ]);
            } else {
                DB::table('opportunity_target')->where("Sales", $a)->where('Year', $b)->where('Month', sprintf('%02d', $i))->update([
                    'Target' => $param[$rowId."_c".($i+2)],
                    'Updated_by' => $Saya,
                    'Updated_Date' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return "update";
    }

    public function tieringSales()
    {
        return view('partner.sales.tiering');
    }

    public function tieringSalesXml()
    {
        //
    }

    public function teamPerformanceSales()
    {
        return view('partner.sales.team-performance');
    }

    public function pipelineLeads()
    {
        return view('partner.sales.pipeline-leads');
    }

    public function pipelineLeadsXml()
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
            $usname[$value->Emp_ID] = $value->Name;
        }

        $opptSales = DB::table('opportunity_sales')->get();
        foreach ($opptSales as $key => $value) {
            $sales[$value->Sales_ID] = $value->Sales;
        }

        $nomor = 1;
        $getPartner = DB::table('opportunity as o')
            ->leftJoin('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
            ->select(
                's.sales as Sales_ID',

                DB::raw('COUNT(*) as pipeline_leads'),
                DB::raw('COUNT(*) as total_leads'),

                DB::raw("SUM(CASE WHEN Response = 'Response' THEN 1 ELSE 0 END) as response"),
                DB::raw("SUM(CASE WHEN Response = 'No Response' THEN 1 ELSE 0 END) as no_response"),

                DB::raw("SUM(CASE WHEN Qualification = 'Qualified' THEN 1 ELSE 0 END) as qualified"),
                DB::raw("SUM(CASE WHEN Qualification = 'Unqualified' THEN 1 ELSE 0 END) as unqualified"),
                DB::raw("SUM(CASE WHEN Qualification = 'Unidentified' THEN 1 ELSE 0 END) as unidentified"),

                DB::raw("SUM(CASE WHEN Classification = 'Meeting 1' THEN 1 ELSE 0 END) as meeting_1"),
                DB::raw("SUM(CASE WHEN Classification = 'Meeting 2' THEN 1 ELSE 0 END) as meeting_2"),

                DB::raw("SUM(CASE WHEN o.Status = 'Cold' THEN 1 ELSE 0 END) as cold"),
                DB::raw("SUM(CASE WHEN o.Status = 'Warm' THEN 1 ELSE 0 END) as warm"),
                DB::raw("SUM(CASE WHEN o.Status = 'Deal' THEN 1 ELSE 0 END) as deal"),
                DB::raw("SUM(CASE WHEN o.Status = 'Cancel' THEN 1 ELSE 0 END) as cancel")
            )
            ->groupBy('Sales_ID')
            ->orderBy('Sales_ID')
            ->get();

        foreach ($getPartner as $key => $Fetch) {
            if ($Fetch->Sales_ID == null) {
                $Fetch->Sales_ID = 'Not Yet Assign';
            } else {
                $Fetch->Sales_ID = $usname[$Fetch->Sales_ID] ?? 'Not Yet Assign';
            }
            echo "<row id=\"$Fetch->Sales_ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Sales_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->total_leads) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->response) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->no_response) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->qualified) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->unqualified) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->unidentified) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->meeting_1) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->meeting_2) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->cold) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warm) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->deal) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->cancel) . "</cell>";

            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function ratioPipelineLeads()
    {
        return view('partner.sales.ratio-pipeline-leads');
    }

    public function ratioPipelineLeadsXml()
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
            $usname[$value->Emp_ID] = $value->Name;
        }

        $opptSales = DB::table('opportunity_sales')->get();
        foreach ($opptSales as $key => $value) {
            $sales[$value->Sales_ID] = $value->Sales;
        }

        $nomor = 1;
        $getPartner = DB::table('opportunity as o')
            ->leftJoin('opportunity_sales as s','o.Sales_ID','=','s.Sales_ID')
            ->select(
                's.sales as Sales_ID',

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Response='Response' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_response"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Response='No Response' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_no_response"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Qualification='Qualified' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_qualified"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Qualification='Unqualified' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_unqualified"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Qualification='Unidentified' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_unidentified"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Classification='Meeting 1' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_meeting1"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Classification='Meeting 2' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_meeting2"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Status='Warm' AND o.Qualification='Qualified' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_warm"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Status='Deal' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_deal"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Status='Cold' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_cold"),

                DB::raw("ROUND(
                    SUM(CASE WHEN o.Status='Cancel' THEN 1 ELSE 0 END) 
                    / COUNT(o.ID) * 100
                ,0) as ratio_cancel")
            )
            ->groupBy('Sales_ID')
            ->get();

        foreach ($getPartner as $key => $Fetch) {
            if ($Fetch->Sales_ID == null) {
                $Fetch->Sales_ID = 'Not Yet Assign';
            } else {
                $Fetch->Sales_ID = $usname[$Fetch->Sales_ID] ?? 'Not Yet Assign';
            }
            echo "<row id=\"$Fetch->Sales_ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Sales_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_response) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_no_response) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_qualified) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_unqualified) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_unidentified) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_meeting1) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_meeting2) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_warm) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_deal) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_cold) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ratio_cancel) . "</cell>";

            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function ratioPipelineDeal()
    {
        return view('partner.sales.ratio-pipeline-deal');
    }

    public function ratioPipelineDealXml()
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
        $target = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
            if ($value->Priv_ID == 'SALES') {
                $target[$value->username] = [];
            }
        }
        for ($i=1; $i <= 12; $i++) { 
            foreach ($target as $key => $value) {
                $target[$key][$i] = 0;
            }
        }

        $opptSales = DB::table('opportunity_sales')->get();
        foreach ($opptSales as $key => $value) {
            $sales[$value->Sales_ID] = $value->Sales;
        }

        $getTarget = DB::table('opportunity_target')->where('Year', date('Y'))->get();
        foreach ($getTarget as $key => $value) {
            $target[$value->Sales][(int)$value->Month] = $value->Target;
        }

        $nomor = 1;
        $getPartner = DB::table('opportunity as o')
                ->join('opportunity_sales as s','o.Sales_ID','=','s.Sales_ID')
                ->select(
                's.sales as Sales_ID',
                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=1 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as jan_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=2 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as feb_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=3 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as mar_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=4 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as apr_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=5 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as may_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=6 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as jun_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=7 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as jul_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=8 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as aug_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=9 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as sep_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=10 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as oct_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=11 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as nov_acv"),

                DB::raw("SUM(CASE WHEN MONTH(o.Updated_Date)=12 AND o.Status = 'Deal' THEN 1 ELSE 0 END) as dec_acv")
                )
                ->whereYear('o.Updated_Date', 'LIKE', date('Y'). '%')
                ->groupBy('Sales_ID')
                ->get();

        foreach ($getPartner as $key => $Fetch) {
            $salesName = "";
            if ($Fetch->Sales_ID == null) {
                $salesName = 'Not Yet Assign';
            } else {
                $salesName = $usname[$Fetch->Sales_ID] ?? 'Not Yet Assign';
            }
            echo "<row id=\"$Fetch->Sales_ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($salesName) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][1]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->jan_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][2]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->feb_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][3]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->mar_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][4]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->apr_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][5]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->may_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][6]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->jun_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][7]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->jul_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][8]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->aug_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][9]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->sep_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][10]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->oct_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][11]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->nov_acv) . "</cell>";
            echo "<cell>" . htmlspecialchars($target[$Fetch->Sales_ID][12]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->dec_acv) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function aftersalesProfitMitra()
    {
        return view('partner.sales.aftersales-profit-mitra');
    }

    public function aftersalesHandlingComplain()
    {
        return view('partner.sales.aftersales-handling-complain');
    }
}
