<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\MasterWarehouseAccount;
use App\MasterWarehouseProfitLoss;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerJournal;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

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
                for ($i = 1; $i <= 12; $i++) {
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
            $d = $param[$rowId . "_c4"]; // transport
            $a = $param[$rowId . "_c1"]; // origin
            $nomorx = $param[$rowId . "_c0"];

            switch ($mode) {
                case "updated":
                    $action = $this->masterTargetUpdatekan($rowId, $param);
                    break;
                case "inserted":
                    $action = $this->masterTargetUpdatekan($rowId, $param);
                    break;
            }

            echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
        }
        echo "</data>";
    }

    function masterTargetUpdatekan($rowId, $param)
    {

        $a = $param[$rowId . "_c1"]; // origin
        $b = $param[$rowId . "_c2"]; // destination
        $c = $param[$rowId . "_c3"]; // destination name

        $Saya = auth()->user()->username;
        for ($i = 1; $i <= 12; $i++) {
            $cek = DB::table('opportunity_target')->where("Sales", $a)->where('Year', $b)->where('Month', sprintf('%02d', $i))->first();
            if (!$cek) {
                DB::table('opportunity_target')->insert([
                    'Sales' => $a,
                    'Year' => $b,
                    'Month' => sprintf('%02d', $i),
                    'Target' => $param[$rowId . "_c" . ($i + 2)],
                    'Created_by' => $Saya,
                    'Created_Date' => date('Y-m-d H:i:s')
                ]);
            } else {
                DB::table('opportunity_target')->where("Sales", $a)->where('Year', $b)->where('Month', sprintf('%02d', $i))->update([
                    'Target' => $param[$rowId . "_c" . ($i + 2)],
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


    // TODO hitung profit sharing achievement (after sales profit mitra)
    public function teamPerformanceSalesXml(Request $r)
    {
        $year     = date('Y');
        $standard = 70;

        // Get all sales users
        $salesUsers = DB::table('users')
            ->where('Priv_ID', 'SALES')
            ->get()
            ->keyBy('username');

        // Get targets per sales per month
        $targets = DB::table('opportunity_target')
            ->where('Year', $year)
            ->get()
            ->groupBy('Sales')
            ->map(fn($rows) => $rows->keyBy('Month'));

        // Get achieved deals per sales per month
        $deals = DB::table('opportunity as o')
            ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
            ->select(
                's.Sales as Sales_ID',
                DB::raw("MONTH(o.Updated_Date) as Month"),
                DB::raw("COUNT(*) as Deal_Count")
            )
            ->where('o.Status', 'Deal')
            ->whereYear('o.Updated_Date', $year)
            ->groupBy('s.Sales', DB::raw("MONTH(o.Updated_Date)"))
            ->get()
            ->groupBy('Sales_ID')
            ->map(fn($rows) => $rows->keyBy('Month'));

        // get complaint data 
        $complaints = $this->aftersalesHandlingComplainCollection();

        // Step 1: Build $salesData as single source of truth per username per month
        $salesData = [];
        foreach ($salesUsers as $username => $user) {
            for ($month = 1; $month <= 12; $month++) {
                $t                   = $targets[$username][$month] ?? null;
                $complaint           = $complaints[$username][$month] ?? null;

                $dealCount           = $deals[$username][$month]->Deal_Count ?? 0;
                $totalComplain       = $complaint->total_complain ?? 0;
                $closed              = $complaint->closed ?? 0;

                $acvDeal              = ($t && $t->Target > 0) ? round(($dealCount / $t->Target) * 100, 2) : 0;
                $acvAftersalesProfit  = 0; // plug your query here
                $acvHandlingComplaint = $totalComplain > 0 ? round(($closed / $totalComplain) * 100, 2) : 0;
                $performance          = round(($acvDeal + $acvAftersalesProfit + $acvHandlingComplaint) / 3, 2);

                $salesData[$username][$month] = [
                    'acvDeal'              => $acvDeal,
                    'acvAftersalesProfit'  => $acvAftersalesProfit,
                    'acvHandlingComplaint' => $acvHandlingComplaint,
                    'performance'          => $performance,
                ];
            }
        }

        // Step 2: Calculate rank per month across all sales
        $globalRank = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPerf = [];

            foreach ($salesUsers as $username => $user) {
                $monthPerf[$username] = $salesData[$username][$month]['performance'];
            }

            arsort($monthPerf);

            $rank     = 1;
            $prevPerf = null;
            $prevRank = 1;

            foreach ($monthPerf as $username => $perf) {
                if ($prevPerf !== null && $perf < $prevPerf) {
                    $rank = $prevRank + 1;
                }
                $globalRank[$username][$month] = $rank;
                $prevPerf                      = $perf;
                $prevRank                      = $rank;
                $rank++;
            }
        }

        // Step 3: Build XML
        $xml = new \SimpleXMLElement('<rows/>');
        $id  = 1;

        foreach ($salesUsers as $username => $user) {
            for ($month = 1; $month <= 12; $month++) {
                $data   = $salesData[$username][$month];
                $status = $data['performance'] >= $standard ? 'Above Target' : 'Below Target';
                $rank   = $globalRank[$username][$month];

                $row = $xml->addChild('row');
                $row->addChild('cell', $id++);
                $row->addChild('cell', htmlspecialchars($user->Name));
                $row->addChild('cell', $month);
                $row->addChild('cell', $year);
                $row->addChild('cell', $data['acvDeal']);
                $row->addChild('cell', $data['acvAftersalesProfit']);
                $row->addChild('cell', $data['acvHandlingComplaint']);
                $row->addChild('cell', $data['performance']);
                $row->addChild('cell', $standard);
                $row->addChild('cell', $status);
                $row->addChild('cell', $rank);
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
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
            ->leftJoin('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
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

        for ($i = 1; $i <= 12; $i++) {
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
            ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
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
            ->whereYear('o.Updated_Date', 'LIKE', date('Y') . '%')
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

    public function aftersalesProfitMitraXml()
    {
        /*
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        */

        echo "<rows>";
        $getUsers = User::get();
        $usname = [];
        $sales = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
            if ($value->Priv_ID == 'SALES') {
                $sales[$value->username] = [];
            }
        }
        $nomor = 1;

        $get = DB::table('opportunity_sales')->get();
        foreach ($get as $key => $value) {
            $sales[$value->Sales][] = DB::table('opportunity_partner as p')->select('lead_id')->join('opportunity as o', 'p.lead_id', '=', 'o.Task_ID')
                ->where('o.Sales_ID', $value->Sales_ID)
                ->where('o.Status', 'Deal')
                ->first();
        }

        $warehouse = WarehousePartner::select(['warehouse_task_id', 'warehouse_type', 'partner_task_id', 'Created_Date', 'Created_By', 'warehouse_location', 'warehouse_status', 'capacity']);
        if (auth()->user()->Priv_ID == "MITRA") {
            $warehouse->where('partner_task_id', auth()->user()->Emp_ID);
        }
        $warehouse = $warehouse->get();
        foreach ($warehouse as $key => $Fetch) {
            $bulanList = [];
            for ($i = 1; $i <= 12; $i++) {
                $bulan = date('Y-m', mktime(0, 0, 0, $i, 1));
                $bulanList[$bulan] = [
                    'proyeksi' => 0,
                    'bagi_hasil' => 0
                ];
            }
            $us = User::where('Emp_ID', $Fetch->partner_task_id)->first();
            $getStartProjection = DB::table('projections')->where("User_ID", $us->id)->select('Created_At')->first();
            if ($getStartProjection) {
                $tahunPembuatan = [
                    date('Y', strtotime($getStartProjection->Created_At)) => 1,
                    date('Y', strtotime($getStartProjection->Created_At . ' +1 year')) => 2,
                    date('Y', strtotime($getStartProjection->Created_At . ' +2 years')) => 3,
                    date('Y', strtotime($getStartProjection->Created_At . ' +3 years')) => 4,
                    date('Y', strtotime($getStartProjection->Created_At . ' +4 years')) => 5,
                ];
            } else {
                $tahunPembuatan = [
                    date('Y') => 1,
                    date('Y', strtotime('+1 year')) => 2,
                    date('Y', strtotime('+2 years')) => 3,
                    date('Y', strtotime('+3 years')) => 4,
                    date('Y', strtotime('+4 years')) => 5,
                ];
            }

            // $tahun = DB::table('projections')->where("User_ID", $us->id)->select('Created_At')->orderBy('Created_At', 'desc')->first();
            // $tahun = date('Y', strtotime($tahun->Created_At ?? date('Y-m-d')));

            foreach ($bulanList as $kunci => $value) {
                $start = $kunci . '-01';
                $end = date('Y-m-t', strtotime($start));
                $calculate = [];
                //Hitung Pendaptan
                $calculate[401][40001] = 0;

                // harga transfer
                $calculate[402][40002] = MasterStockPrice::whereBetween('date_for', [$start, $end])->avg('price');

                // quantity
                $calculate[501][51001] = WarehouseStockRequest::where('warehouse_task_id', $Fetch->warehouse_task_id)->whereBetween('request_date', [$start, $end])->sum('request_qty');

                $whs = WarehousePartnerSupplies::where("warehouse_task_id", $Fetch->warehouse_task_id)->whereBetween('received_date', [$start, $end])->get();
                $supplies = [];
                $supplies['avg'] = 0;
                foreach ($whs as $key => $value) {
                    $month = date('Y-m', strtotime($value->received_date));
                    if (!isset($supplies['qty'])) {
                        $supplies['qty'] = 0;
                    }
                    if (!isset($supplies['avg'])) {
                        $supplies['avg'] = $value->price;
                    }
                    $supplies['avg'] = (($supplies['avg'] * $supplies['qty']) + $value->total) / ($supplies['qty'] + $value->received_qty);
                }

                // harga beli
                $calculate[501][51002] = $supplies['avg'];

                // pendapatan
                $calculate[401][40001] = $calculate[402][40002] * $calculate[501][51001];

                $calculate[501]['NULL'] = $calculate[501][51001] * $calculate[501][51002];


                // Hitung Opex
                $getAccount = MasterWarehouseAccount::where('group_account', '>', 501)->get();
                foreach ($getAccount as $key => $value) {
                    if (!isset($calculate[$value->group_account])) {
                        $calculate[$value->group_account] = [];
                    }
                    if (!isset($calculate[$value->group_account][$value->account_no])) {
                        $calculate[$value->group_account][$value->account_no] = 0;
                    }

                    $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $Fetch->warehouse_task_id)
                        ->whereBetween('posting_date', [$start, $end])
                        ->where('account_no', $value->account_no)
                        ->where('group_account', $value->group_account)
                        ->sum('nominal');

                    $calculate[$value->group_account][$value->account_no] += $getOpex;
                }

                $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502]);

                $get = MasterWarehouseProfitLoss::where('type', $Fetch->warehouse_type)->get();
                $normal = [];
                foreach ($get as $key => $value) {
                    if ($value->Normal != '' && $value->Normal != null) {
                        $normal[$value->Judul] = $value->Normal;
                    }
                }

                foreach ($get as $key => $value) {
                    if ($value->Level == 2) {
                        if ($value->Rumus != '') {
                            $exp = explode(',', $value->Rumus);
                            if (count($exp) > 0) {
                                if (!isset($calculate[$value->Judul])) {
                                    $calculate[$value->Judul]['NULL'] = 0;
                                }
                                if ($value->Normal == 'CREDIT') {
                                    $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) - array_sum($calculate[$exp[1]])) - (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                                    if ($value->Judul == '701') {
                                        $calculate[$value->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                                    }
                                } else {
                                    $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) + array_sum($calculate[$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                                }
                            }
                        }
                    } else if ($value->Level == 3) {
                        $exp = explode(',', $value->Rumus);
                        if (!isset($calculate[$value->Judul])) {
                            $calculate[$value->Judul]['NULL'] = 0;
                        }
                        $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) * 60) / 100;
                    }
                }

                $rev = $calculate[401][40001];
                if ($rev == 0) {
                    $rev = 1;
                }

                $type = ucwords($Fetch->warehouse_type);
                if ($type == '') {
                    $type = 'Booth';
                }

                // $netprofit = isset($calculate[701]) ? $calculate[701]['NULL'] : 0;
                // $managementfee = isset($calculate[601][61007]) ? $calculate[601][61007] : 0;
                $bagiHasilMitra = isset($calculate[801]['NULL']) ? $calculate[801]['NULL'] : 0;
                // $bagiHasil = $netprofit - $bagiHasilMitra;

                $bulanList[$kunci]['proyeksi'] += DB::table('projections')
                    ->where(function ($q) use ($Fetch, $us) {
                        $q->where('warehouse_task_id', $Fetch->warehouse_task_id)
                            ->orWhere("User_Id", $us->id);
                    })
                    ->where('Projection_Type', 'high')
                    ->where("Hub_Type", $Fetch->warehouse_type)
                    ->where('Year', $tahunPembuatan[date('Y', strtotime($start))] ?? 1)
                    ->where('Description', 'Profit Mitra')
                    ->sum('Month_' . date('n', strtotime($start)) . '_Total');
                $bulanList[$kunci]['bagi_hasil'] += $bagiHasilMitra;
            }


            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(DB::table('opportunity')->where("Task_ID", $Fetch->partner_task_id)->first()->Sales_ID ?? "NO ASSIGNMENT") . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($type) . "</cell>";
            foreach ($bulanList as $kunci => $value) {
                echo "<cell>" . htmlspecialchars($value['proyeksi']) . "</cell>";
                echo "<cell>" . htmlspecialchars($value['bagi_hasil']) . "</cell>";
            }
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function aftersalesHandlingComplain()
    {
        return view('partner.sales.aftersales-handling-complain');
    }

    public function aftersalesHandlingComplainCollection()
    {
        $get = DB::table('ticket')
            ->join('warehouse_partner as w', 'ticket.GEU_Express_ID', '=', 'w.warehouse_task_id')
            ->join('opportunity as o', 'w.partner_task_id', '=', 'o.Task_ID')
            ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
            ->select([
                's.Sales as Sales_ID',
                DB::raw("YEAR(ticket.Proposed_Date) as tahun"),
                DB::raw("MONTH(ticket.Proposed_Date) as bulan"),
                DB::raw("COUNT(*) as total_complain"),
                DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'In Progress' OR ticket.Ticket_Status = 'Open' THEN 1 ELSE 0 END) as unresolved"),
                DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'Closed' THEN 1 ELSE 0 END) as closed"),
            ])
            ->groupBy('s.Sales')
            ->groupByRaw("YEAR(ticket.Proposed_Date)")
            ->groupByRaw("MONTH(ticket.Proposed_Date)")
            ->orderByRaw("YEAR(ticket.Proposed_Date), MONTH(ticket.Proposed_Date)")
            ->get()
            ->groupBy('Sales_ID')
            ->map(fn($rows) => $rows->keyBy('bulan'));

        return $get;
    }

    public function aftersalesHandlingComplainXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml; charset=UTF-8");
        } else {
            header("Content-type: text/xml; charset=UTF-8");
        }

        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        echo "<rows>";

        // mapping user
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }

        $nomor = 1;

        // query utama
        $get = DB::table('ticket')
            ->join('warehouse_partner as w', 'ticket.GEU_Express_ID', '=', 'w.warehouse_task_id')
            ->join('opportunity as o', 'w.partner_task_id', '=', 'o.Task_ID')
            ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
            ->select([
                's.Sales as Sales_ID',
                DB::raw("YEAR(ticket.Proposed_Date) as tahun"),
                DB::raw("MONTH(ticket.Proposed_Date) as bulan"),
                DB::raw("COUNT(*) as total_complain"),
                DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'Open' THEN 1 ELSE 0 END) as Open"),
                DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'In Progress' THEN 1 ELSE 0 END) as In_Progress"),
                DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'Closed' THEN 1 ELSE 0 END) as Closed"),
            ])
            ->groupBy('s.Sales')
            ->groupByRaw("YEAR(ticket.Proposed_Date)")
            ->groupByRaw("MONTH(ticket.Proposed_Date)")
            ->orderByRaw("YEAR(ticket.Proposed_Date), MONTH(ticket.Proposed_Date)")
            ->get();

        // output
        foreach ($get as $Fetch) {

            echo "<row id=\"$nomor\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Sales_ID) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->tahun) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->bulan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->total_complain) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Closed) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->In_Progress + $Fetch->Open) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }
}
