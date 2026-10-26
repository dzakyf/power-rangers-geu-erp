<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\MasterWarehouseAccount;
use App\MasterWarehouseProfitLoss;
use App\Services\GeneratorService;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerJournal;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use App\Services\WarehouseService;

class SalesController extends Controller
{
    private $performanceStandard;
    private $generatorService;
    private $warehouseService;

    public function __construct(GeneratorService $g, WarehouseService $warehouseService)
    {
        $this->performanceStandard = 70;
        $this->generatorService = $g;
        $this->warehouseService = $warehouseService;
    }

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

    // current data 
    public function tieringSalesCollection()
    {
        $d = DB::table('users_statistics as us')
            ->select('us.*', 'usr.Name as Sales_Name')
            ->join('users as usr', 'usr.id', '=', 'us.User_Id')
            ->get();

        return $d;
    }

    // historical logs
    public function tieringSalesLogsCollection(Request $r)
    {
        $start = $r->start ? Carbon::parse($r->start)->startOfDay() : null;
        $end   = $r->end   ? Carbon::parse($r->end)->endOfDay()     : null;

        $d = DB::table('sales_performance_ranking_logs as sprl')
            ->select(
                'sprl.*',
                'usr.Name as Sales_Name',
                'us.Current_Achievement_Streak',
            )
            ->join('users as usr', 'usr.id', '=', 'sprl.User_Id')
            ->join('users_statistics as us', 'us.User_Id', '=', 'sprl.User_Id')
            ->when($start, fn($q) => $q->where('sprl.Created_At', '>=', $start))
            ->when($end,   fn($q) => $q->where('sprl.Created_At', '<=', $end))
            ->get();

        return $d;
    }

    public function tieringSalesXml(Request $r)
    {
        $t = $this->tieringSalesLogsCollection($r);

        $xml = new SimpleXMLElement('<rows/>');

        $counter = 1;
        foreach ($t as $row) {
            $xmlRow = $xml->addChild('row');
            $xmlRow->addChild('cell', $counter++);
            $xmlRow->addChild('cell', $row->Current_Tier);
            $xmlRow->addChild('cell', $row->Sales_Name);
            $xmlRow->addChild('cell', Carbon::parse($row->Created_At)->format('Y-m'));
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function teamPerformanceSales()
    {
        return view('partner.sales.team-performance');
    }

    private function buildSalesPerformanceData(Request $r): array
    {
        $year = (int) ($r->query('year') ?: date('Y'));

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

        // Get complaint and profit data
        $complaints  = $this->aftersalesHandlingComplainCollection();
        $salesProfit = $this->aftersalesProfitMitraCollection($r);
        $tiering = $this->tieringSalesCollection();
        $tiering = $tiering->groupBy('User_Id')->map(fn($rows) => $rows->keyBy(fn($row) => date('Y-m', strtotime($row->Created_At))));


        // Flatten salesProfit into lookup[$sales_id][$month]
        $profitLookup = [];
        foreach ($salesProfit as $salesRow) {
            foreach ($salesRow['warehouses'] as $warehouse) {
                foreach ($warehouse['monthly'] as $monthlyEntry) {
                    foreach ($monthlyEntry as $month => $data) {
                        $profitLookup[$salesRow['sales_id']][$month]['proyeksi']   = ($profitLookup[$salesRow['sales_id']][$month]['proyeksi'] ?? 0) + $data['proyeksi'];
                        $profitLookup[$salesRow['sales_id']][$month]['bagi_hasil'] = ($profitLookup[$salesRow['sales_id']][$month]['bagi_hasil'] ?? 0) + $data['bagi_hasil'];
                    }
                }
            }
        }

        // Step 1: Build $salesData per username per month
        $salesData = [];
        foreach ($salesUsers as $username => $user) {
            for ($month = 1; $month <= 12; $month++) {
                $t         = $targets[$username][$month] ?? null;
                $complaint = $complaints[$username][$month] ?? null;
                $profit    = $profitLookup[$username][$month] ?? null;

                $dealCount     = $deals[$username][$month]->Deal_Count ?? 0;
                $totalComplain = $complaint->total_complain ?? 0;
                $closed        = $complaint->closed ?? 0;
                $proyeksi      = $profit['proyeksi'] ?? 0;
                $bagiHasil     = $profit['bagi_hasil'] ?? 0;

                $acvDeal              = ($t && $t->Target > 0) ? round(($dealCount / $t->Target) * 100, 2) : 0;
                $acvAftersalesProfit  = $proyeksi > 0 ? round(($bagiHasil / $proyeksi) * 100, 2) : 0;
                $acvHandlingComplaint = $totalComplain > 0 ? round(($closed / $totalComplain) * 100, 2) : 0;
                $performance          = round(($acvDeal + $acvAftersalesProfit + $acvHandlingComplaint) / 3, 2);

                $monthKey    = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $tierEntry   = $tiering[$user->id][$monthKey] ?? null;
                $currentTier = $tierEntry->Current_Tier ?? '-';


                $salesData[$username][$month] = [
                    'user'                 => $user,
                    'acv_deal'              => $acvDeal,
                    'current_tier'           => $currentTier,
                    'acv_aftersales_profit'  => $acvAftersalesProfit,
                    'acv_handling_complaint' => $acvHandlingComplaint,
                    'performance'          => $performance,
                ];
            }
        }

        // Step 2: Calculate rank per month across all sales
        $globalRank = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPerf = array_column(array_map(
                fn($username) => ['username' => $username, 'perf' => $salesData[$username][$month]['performance']],
                array_keys($salesData)
            ), 'perf', 'username');

            arsort($monthPerf);

            $rank =  1;
            $prevPerf = null;
            $prevRank = 1;
            $counter = 1;
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

        return [
            'periode' => $year,
            'performance' => $salesData,
            'rank' => $globalRank
        ];
    }

    // TODO: check output
    public function teamPerformanceSalesXml(Request $r)
    {
        $year     = (int) ($r->query('year') ?: date('Y'));
        $standard = $this->performanceStandard;

        $from = $r->query('month_from');
        $to   = $r->query('month_to');
        $hasMonthFilter = ($from !== null && $from !== '' && $from !== 'All');

        if ($hasMonthFilter) {
            $fromMonth = (int) $from;
            $toMonth   = (int) ($to ?: $from);
            if ($fromMonth > $toMonth) {
                $swap      = $fromMonth;
                $fromMonth = $toMonth;
                $toMonth   = $swap;
            }
        }

        $result     = $this->buildSalesPerformanceData($r);
        $salesData  = $result['performance'];
        $globalRank = $result['rank'];

        $xml = new \SimpleXMLElement('<rows/>');
        $id  = 1;

        foreach ($salesData as $username => $months) {
            foreach ($months as $month => $data) {
                if ($hasMonthFilter && ($month < $fromMonth || $month > $toMonth)) {
                    continue;
                }

                $status = $data['performance'] >= $standard ? 'Above Target' : 'Below Target';

                $row = $xml->addChild('row');
                $row->addChild('cell', $id++);
                $row->addChild('cell', htmlspecialchars($data['user']->Name));
                $row->addChild('cell', date('F', mktime(0, 0, 0, (int) $month, 1)));
                $row->addChild('cell', $year);
                $row->addChild('cell', $data['acv_deal']);
                $row->addChild('cell', $data['acv_aftersales_profit']);
                $row->addChild('cell', $data['acv_handling_complaint']);
                $row->addChild('cell', $data['performance']);
                $row->addChild('cell', $standard);
                $row->addChild('cell', $status);
                $row->addChild('cell', $globalRank[$username][$month]);
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

        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }

        $service = app(\App\Services\LeadsService::class);
        $rows = $service->funnelBySales();

        $nomor = 1;
        foreach ($rows as $Fetch) {
            $salesName = $Fetch['sales_ref'] == null
                ? 'Tidak ada sales'
                : ($usname[$Fetch['sales_ref']] ?? 'Tidak ada sales');

            $rowId = $Fetch['sales_ref'] == null ? '__unassigned__' : $Fetch['sales_ref'];

            $tipAttrs = '';
            foreach ($service->ratioParts($Fetch) as $k => [$num, $den]) {
                $tipAttrs .= ' t_' . $k . '="' . htmlspecialchars($num . ' / ' . $den) . '"';
            }

            echo "<row id=\"" . htmlspecialchars($rowId) . "\"" . $tipAttrs . ">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($salesName) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['total_leads']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['response']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['no_response']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['qualified']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['unqualified']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['unidentified']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['cold']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['meeting_1']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['meeting_2']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['warm']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['deal']) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch['cancel_total']) . "</cell>";
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

        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }

        $service = app(\App\Services\LeadsService::class);
        $rows = $service->funnelBySales();

        $nomor = 1;
        foreach ($rows as $Fetch) {
            $salesName = $Fetch['sales_ref'] == null
                ? 'Not Yet Assign'
                : ($usname[$Fetch['sales_ref']] ?? 'Not Yet Assign');

            $r = $service->ratios($Fetch);

            $tipAttrs = '';
            foreach ($service->ratioParts($Fetch) as $k => [$num, $den]) {
                $tipAttrs .= ' t_' . $k . '="' . htmlspecialchars($num . ' / ' . $den) . '"';
            }

            echo "<row id=\"" . htmlspecialchars($salesName) . "\"" . $tipAttrs . ">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($salesName) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['response']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['no_response']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['qualified']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['unqualified']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['unidentified']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['cold']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['meeting_1']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['meeting_2']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['warm']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['deal']) . "</cell>";
            echo "<cell>" . htmlspecialchars($r['cancel_total']) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function ratioPipelineDeal()
    {
        return view('partner.sales.ratio-pipeline-deal');
    }


    public function ratioPipelineDealXml(Request $r)
    {
        $year = $r->query('year') ?: date('Y');

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

        $getTarget = DB::table('opportunity_target')->where('Year', $year)->get();
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
            ->whereYear('o.Updated_Date', $year)
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


    //based on sales id 
    public function aftersalesProfitMitraCollection(Request $r)
    {
        $year   = (int) ($r->query('year') ?: date('Y'));
        $months = range(1, 12);
        $q      = $r->query();

        // ============================================================
        // 1. Get all SALES users (filter by sales_id if provided)
        // ============================================================
        $salesQuery = DB::table('users')->where('Priv_ID', 'SALES');

        if (!empty($q['sales_id'])) {
            $salesQuery->where('username', $q['sales_id']);
        }

        $salesUsers = $salesQuery->get()->keyBy('username');

        // ============================================================
        // 2. Get all OPEX account definitions from master
        // ============================================================
        $opexAccounts = MasterWarehouseAccount::where('group_account', '>', 501)->get();

        // ============================================================
        // 3. Get profit/loss formula definitions per warehouse type
        // ============================================================
        $profitLossFormulas = MasterWarehouseProfitLoss::all()->groupBy('type');

        // ============================================================
        // 4. Build result collection per sales
        // ============================================================
        $result = $salesUsers->map(function ($salesUser) use ($year, $months, $opexAccounts, $profitLossFormulas) {

            // Get all warehouses assigned to this sales via opportunity
            $warehouseIds = DB::table('opportunity as o')
                ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
                ->join('warehouse_partner as w', 'o.Task_ID', '=', 'w.partner_task_id')
                ->where('s.Sales', $salesUser->username)
                ->where('o.Status', 'Deal')
                ->pluck('w.warehouse_task_id');

            if ($warehouseIds->isEmpty()) {
                return null;
            }

            $warehouses = WarehousePartner::select([
                'warehouse_task_id',
                'warehouse_type',
                'partner_task_id',
                'warehouse_location',
                'warehouse_status',
                'capacity'
            ])->whereIn('warehouse_task_id', $warehouseIds)->get();

            // --------------------------------------------------------
            // Calculate financial data per warehouse per month
            // --------------------------------------------------------
            $warehouseData = $warehouses->map(function ($warehouse) use ($year, $months, $opexAccounts, $profitLossFormulas, $salesUser) {

                // Get the mitra user account
                $mitraUser = User::where('Emp_ID', $warehouse->partner_task_id)->first();

                // Determine projection year mapping based on first projection created
                $firstProjection = DB::table('projections')
                    ->where('User_ID', $mitraUser->id)
                    ->value('Created_At');

                $baseDate        = $firstProjection ?? now();
                $projectionYears = collect(range(0, 4))->mapWithKeys(fn($i) => [
                    date('Y', strtotime("$baseDate +$i years")) => $i + 1
                ]);

                $warehouseType = ucwords($warehouse->warehouse_type) ?: 'Booth';
                $formulas      = $profitLossFormulas[$warehouse->warehouse_type] ?? collect();

                $monthlyData = collect($months)->map(function ($month) use ($warehouse, $mitraUser, $opexAccounts, $formulas, $projectionYears, $year) {
                    $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
                    $end   = date('Y-m-t', strtotime($start));

                    $calculate = [];

                    // Revenue: avg stock price × qty requested
                    $avgStockPrice         = MasterStockPrice::whereBetween('date_for', [$start, $end])->avg('price') ?? 0;
                    $qtyRequested          = WarehouseStockRequest::where('warehouse_task_id', $warehouse->warehouse_task_id)
                        ->whereBetween('request_date', [$start, $end])
                        ->sum('request_qty');

                    $calculate[401][40001] = $avgStockPrice * $qtyRequested;
                    $calculate[402][40002] = $avgStockPrice;

                    // COGS: weighted average purchase price × qty
                    $supplies    = WarehousePartnerSupplies::where('warehouse_task_id', $warehouse->warehouse_task_id)
                        ->whereBetween('received_date', [$start, $end])
                        ->get();

                    $avgBuyPrice = 0;
                    $totalQty    = 0;
                    foreach ($supplies as $supply) {
                        $avgBuyPrice = (($avgBuyPrice * $totalQty) + $supply->total) / ($totalQty + $supply->received_qty);
                        $totalQty   += $supply->received_qty;
                    }

                    $calculate[501][51001]  = $qtyRequested;
                    $calculate[501][51002]  = $avgBuyPrice;
                    $calculate[501]['NULL'] = $qtyRequested * $avgBuyPrice;

                    // OPEX: sum journal entries per account
                    foreach ($opexAccounts as $account) {
                        $calculate[$account->group_account][$account->account_no] = WarehousePartnerJournal::where('warehouse_task_id', $warehouse->warehouse_task_id)
                            ->whereBetween('posting_date', [$start, $end])
                            ->where('account_no', $account->account_no)
                            ->where('group_account', $account->group_account)
                            ->sum('nominal');
                    }

                    $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502] ?? []);

                    // Apply profit/loss formulas
                    foreach ($formulas as $formula) {
                        $exp = explode(',', $formula->Rumus);

                        if ($formula->Level == 2 && $formula->Rumus != '') {
                            $calculate[$formula->Judul]['NULL'] = $formula->Normal == 'CREDIT'
                                ? (array_sum($calculate[$exp[0]] ?? []) - array_sum($calculate[$exp[1]] ?? [])) - (isset($exp[2]) ? array_sum($calculate[$exp[2]] ?? []) : 0)
                                : (array_sum($calculate[$exp[0]] ?? []) + array_sum($calculate[$exp[1]] ?? [])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]] ?? []) : 0);

                            if ($formula->Judul == '701') {
                                $calculate[$formula->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                            }
                        }

                        if ($formula->Level == 3) {
                            $calculate[$formula->Judul]['NULL'] = (array_sum($calculate[$exp[0]] ?? []) * 60) / 100;
                        }
                    }

                    $bagiHasil = $calculate[801]['NULL'] ?? 0;

                    $proyeksi = DB::table('projections')
                        ->where(function ($q) use ($warehouse, $mitraUser) {
                            $q->where('warehouse_task_id', $warehouse->warehouse_task_id)
                                ->orWhere('User_Id', $mitraUser->id);
                        })
                        ->where('Projection_Type', 'low')
                        ->where('Hub_Type', $warehouse->warehouse_type)
                        ->where('Year', $projectionYears[date('Y', strtotime($start))] ?? 1)
                        ->where('Description', 'Profit Mitra')
                        ->sum('Month_' . $month . '_Total');

                    return [
                        $month => [
                            'proyeksi'   => $proyeksi,
                            'bagi_hasil' => $bagiHasil,
                            'month' => $month,
                            'surplus' => $bagiHasil - $proyeksi,
                            'performance' => $proyeksi > 0 ? round(($bagiHasil / $proyeksi) * 100, 2) : 0,
                        ]
                    ];
                    // return [
                    //     'month' => $month,
                    //     'proyeksi'   => $proyeksi,
                    //     'bagi_hasil' => $bagiHasil,
                    //     'surplus' => $bagiHasil - $proyeksi,
                    //     'performance' => $proyeksi > 0 ? round(($bagiHasil / $proyeksi) * 100, 2) : 0,
                    // ];
                });

                return [
                    'warehouse_id'       => $warehouse->warehouse_task_id,
                    'warehouse_location' => $warehouse->warehouse_location,
                    'mitra_name'         => $mitraUser->Name ?? '-',
                    'warehouse_type'     => $warehouseType,
                    'monthly'            => $monthlyData,
                ];
            });

            return [
                'sales_row_id' => $salesUser->id,
                'sales_id'   => $salesUser->username,
                'sales_name' => $salesUser->Name,
                'warehouses' => $warehouseData,
            ];
        })->filter(); // Remove null entries (sales with no warehouses)

        return $result->values();
    }

    public function aftersalesProfitMitraXmlV2()
    {
        $year         = date('Y');
        $opexAccounts = MasterWarehouseAccount::where('group_account', '>', 501)->get();
        $formulas     = MasterWarehouseProfitLoss::all()->groupBy('type');

        // Get all warehouses
        $warehouses = WarehousePartner::select([
            'warehouse_task_id',
            'warehouse_type',
            'partner_task_id',
            'warehouse_location',
        ]);

        if (auth()->user()->Priv_ID == "MITRA") {
            $warehouses->where('partner_task_id', auth()->user()->Emp_ID);
        }

        $warehouses = $warehouses->get();

        $xml    = new SimpleXMLElement('<rows/>');
        $id     = 1;

        foreach ($warehouses as $warehouse) {
            $mitraUser = User::where('Emp_ID', $warehouse->partner_task_id)->first();
            if (!$mitraUser) continue;

            // Get sales assigned to this warehouse

            $salesName = DB::table('opportunity as o')
                ->join('users as u', 'u.username', '=', 'o.Created_By')
                ->where('o.Task_ID', $warehouse->partner_task_id)
                ->value('u.Name') ?? 'NO ASSIGNMENT';

            // Determine projection year mapping
            $firstProjection = DB::table('projections')
                ->where('User_ID', $mitraUser->id)
                ->value('Created_At');

            $baseDate        = $firstProjection ?? now();
            $projectionYears = collect(range(0, 4))->mapWithKeys(fn($i) => [
                date('Y', strtotime("$baseDate +$i years")) => $i + 1
            ]);

            $warehouseFormulas = $formulas[$warehouse->warehouse_type] ?? collect();

            for ($month = 1; $month <= 12; $month++) {
                $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
                $end   = date('Y-m-t', strtotime($start));

                $calculate = [];

                // Revenue
                $avgStockPrice         = MasterStockPrice::whereBetween('date_for', [$start, $end])->avg('price') ?? 0;
                $qtyRequested          = WarehouseStockRequest::where('warehouse_task_id', $warehouse->warehouse_task_id)
                    ->whereBetween('request_date', [$start, $end])
                    ->sum('request_qty');

                $calculate[401][40001] = $avgStockPrice * $qtyRequested;
                $calculate[402][40002] = $avgStockPrice;

                // COGS
                $supplies    = WarehousePartnerSupplies::where('warehouse_task_id', $warehouse->warehouse_task_id)
                    ->whereBetween('received_date', [$start, $end])
                    ->get();

                $avgBuyPrice = 0;
                $totalQty    = 0;
                foreach ($supplies as $supply) {
                    $avgBuyPrice = (($avgBuyPrice * $totalQty) + $supply->total) / ($totalQty + $supply->received_qty);
                    $totalQty   += $supply->received_qty;
                }

                $calculate[501][51001]  = $qtyRequested;
                $calculate[501][51002]  = $avgBuyPrice;
                $calculate[501]['NULL'] = $qtyRequested * $avgBuyPrice;

                // OPEX
                foreach ($opexAccounts as $account) {
                    $calculate[$account->group_account][$account->account_no] = WarehousePartnerJournal::where('warehouse_task_id', $warehouse->warehouse_task_id)
                        ->whereBetween('posting_date', [$start, $end])
                        ->where('account_no', $account->account_no)
                        ->where('group_account', $account->group_account)
                        ->sum('nominal');
                }

                $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502] ?? []);

                // Apply profit/loss formulas
                foreach ($warehouseFormulas as $formula) {
                    if ($formula->Rumus == '') continue;
                    $exp = explode(',', $formula->Rumus);

                    if ($formula->Level == 2) {
                        $calculate[$formula->Judul]['NULL'] = $formula->Normal == 'CREDIT'
                            ? (array_sum($calculate[$exp[0]] ?? []) - array_sum($calculate[$exp[1]] ?? [])) - (isset($exp[2]) ? array_sum($calculate[$exp[2]] ?? []) : 0)
                            : (array_sum($calculate[$exp[0]] ?? []) + array_sum($calculate[$exp[1]] ?? [])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]] ?? []) : 0);

                        if ($formula->Judul == '701') {
                            $calculate[$formula->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                        }
                    }

                    if ($formula->Level == 3) {
                        $calculate[$formula->Judul]['NULL'] = (array_sum($calculate[$exp[0]] ?? []) * 60) / 100;
                    }
                }

                $bagiHasil = $calculate[801]['NULL'] ?? 0;

                $proyeksi = DB::table('projections')
                    ->where(function ($q) use ($warehouse, $mitraUser) {
                        $q->where('warehouse_task_id', $warehouse->warehouse_task_id)
                            ->orWhere('User_Id', $mitraUser->id);
                    })
                    ->where('Projection_Type', 'low')
                    ->where('Hub_Type', $warehouse->warehouse_type)
                    ->where('Year', $projectionYears[date('Y', strtotime($start))] ?? 1)
                    ->where('Description', 'Profit Mitra')
                    ->sum('Month_' . $month . '_Total');

                $surplus = $bagiHasil - $proyeksi ?? 0;
                $performance = $proyeksi != 0 ?  $bagiHasil / $proyeksi : 0;

                $row = $xml->addChild('row');
                $row->addChild('cell', $id++);
                $row->addChild('cell', htmlspecialchars($salesName));
                $row->addChild('cell', $month);
                $row->addChild('cell', $year);
                $row->addChild('cell', htmlspecialchars($warehouse->warehouse_location ?? '-'));
                $row->addChild('cell', $proyeksi);
                $row->addChild('cell', $bagiHasil);
                $row->addChild('cell', $surplus);
                $row->addChild('cell', $performance);
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
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
                    ->where('Projection_Type', 'low')
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
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id] ?? $Fetch->partner_task_id) . "</cell>";
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
        $xml    = new SimpleXMLElement('<rows/>');
        $id     = 1;

        $get = DB::table('ticket as t')
            ->join('warehouse_partner as w', 't.GEU_Express_ID', '=', 'w.warehouse_task_id')
            ->join('opportunity as o', 'o.Task_ID', '=', 'w.partner_task_id')
            ->join('opportunity_sales as s', 's.Sales_ID', '=', 'o.Sales_ID')
            ->join('users as u', 'u.username', '=', 's.Sales')
            ->select([
                'u.Name as Sales_Name',
                's.Sales as Sales_ID',
                'w.warehouse_task_id as Warehouse_ID',
                DB::raw("YEAR(t.Proposed_Date) as tahun"),
                DB::raw("MONTH(t.Proposed_Date) as bulan"),
                DB::raw("COUNT(*) as total_complain"),
                DB::raw("SUM(CASE WHEN t.Ticket_Status = 'Open' THEN 1 ELSE 0 END) as Open"),
                DB::raw("SUM(CASE WHEN t.Ticket_Status = 'In Progress' THEN 1 ELSE 0 END) as In_Progress"),
                DB::raw("SUM(CASE WHEN t.Ticket_Status = 'Closed' THEN 1 ELSE 0 END) as Closed"),
            ])
            ->groupBy('s.Sales', 'u.Name')
            ->groupByRaw("YEAR(t.Proposed_Date)")
            ->groupByRaw("MONTH(t.Proposed_Date)")
            ->orderByRaw("YEAR(t.Proposed_Date), MONTH(t.Proposed_Date)")
            ->get();

        foreach ($get as $row) {
            $performance = $row->total_complain != 0 ? round(($row->Closed / $row->total_complain) * 100, 2) : 0;
            $xmlRow = $xml->addChild('row');
            $xmlRow->addAttribute('Sales_Username', $row->Sales_ID);
            $xmlRow->addAttribute('Warehouse_ID', $row->Warehouse_ID);
            $xmlRow->addChild('cell', $id++);
            $xmlRow->addChild('cell', htmlspecialchars($row->Sales_ID));
            $xmlRow->addChild('cell', htmlspecialchars($row->Warehouse_ID));
            $xmlRow->addChild('cell', htmlspecialchars($row->bulan));
            $xmlRow->addChild('cell', htmlspecialchars($row->tahun));
            $xmlRow->addChild('cell', htmlspecialchars($row->total_complain));
            $xmlRow->addChild('cell', htmlspecialchars($row->Closed));
            $xmlRow->addChild('cell', htmlspecialchars($row->In_Progress + $row->Open));
            $xmlRow->addChild('cell', htmlspecialchars($performance));
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    // public function aftersalesHandlingComplainXml()
    // {
    //     if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
    //         header("Content-type: application/xhtml+xml; charset=UTF-8");
    //     } else {
    //         header("Content-type: text/xml; charset=UTF-8");
    //     }

    //     echo "<?xml version='1.0' encoding='UTF-8'>\n";
    //     echo "<rows>";

    //     // mapping user
    //     $getUsers = User::get();
    //     $usname = [];
    //     foreach ($getUsers as $value) {
    //         $usname[$value->username] = $value->Name;
    //         $usname[$value->Emp_ID] = $value->Name;
    //     }

    //     $nomor = 1;

    //     // query utama
    //     $get = DB::table('ticket')
    //         ->join('warehouse_partner as w', 'ticket.GEU_Express_ID', '=', 'w.warehouse_task_id')
    //         ->join('opportunity as o', 'w.partner_task_id', '=', 'o.Task_ID')
    //         ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
    //         ->select([
    //             's.Sales as Sales_ID',
    //             DB::raw("YEAR(ticket.Proposed_Date) as tahun"),
    //             DB::raw("MONTH(ticket.Proposed_Date) as bulan"),
    //             DB::raw("COUNT(*) as total_complain"),
    //             DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'Open' THEN 1 ELSE 0 END) as Open"),
    //             DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'In Progress' THEN 1 ELSE 0 END) as In_Progress"),
    //             DB::raw("SUM(CASE WHEN ticket.Ticket_Status = 'Closed' THEN 1 ELSE 0 END) as Closed"),
    //         ])
    //         ->groupBy('s.Sales')
    //         ->groupByRaw("YEAR(ticket.Proposed_Date)")
    //         ->groupByRaw("MONTH(ticket.Proposed_Date)")
    //         ->orderByRaw("YEAR(ticket.Proposed_Date), MONTH(ticket.Proposed_Date)")
    //         ->get();

    //     // output
    //     foreach ($get as $Fetch) {

    //         echo "<row id=\"$nomor\">";
    //         echo "<cell>$nomor</cell>";
    //         echo "<cell>" . htmlspecialchars($Fetch->Sales_ID) . "</cell>";
    //         echo "<cell>" . htmlspecialchars($Fetch->tahun) . "</cell>";
    //         echo "<cell>" . htmlspecialchars($Fetch->bulan) . "</cell>";
    //         echo "<cell>" . htmlspecialchars($Fetch->total_complain) . "</cell>";
    //         echo "<cell>" . htmlspecialchars($Fetch->Closed) . "</cell>";
    //         echo "<cell>" . htmlspecialchars($Fetch->In_Progress + $Fetch->Open) . "</cell>";
    //         echo "</row>";

    //         $nomor++;
    //     }

    //     echo "</rows>";
    // }

    // cron api
    public function updateAllSalesPerformance(Request $r)
    {
        $currMonth = Carbon::now()->month;
        $now       = Carbon::now();

        $salesData   = $this->buildSalesPerformanceData($r);
        $performance = $salesData['performance'];

        DB::beginTransaction();

        try {
            foreach ($performance as $username => $months) {
                $monthData = $months[$currMonth] ?? null;

                if (!$monthData) continue;

                $user             = $monthData['user'];
                $performanceStatus = $monthData['performance'] >= $this->performanceStandard ? 'ABOVE' : 'BELOW';

                $currentAchievementStreak = DB::table('users_statistics')
                    ->where('User_Id', $user->id)
                    ->value('Current_Achievement_Streak') ?? 0;

                $currentAchievementStreak = $performanceStatus === 'ABOVE'
                    ? $currentAchievementStreak + 1
                    : 1;

                if ($currentAchievementStreak > 3) {
                    $newTier = 'GOLD';
                } elseif ($currentAchievementStreak === 3) {
                    $newTier = 'BRONZE';
                } else {
                    $newTier = 'SILVER';
                }

                DB::table('users_statistics')->updateOrInsert(
                    ['User_Id' => $user->id],
                    [
                        'Current_Achievement_Streak' => $currentAchievementStreak,
                        'Current_Tier'               => $newTier,
                        'Updated_At'                 => $now,
                    ]
                );

                DB::table('sales_performance_ranking_logs')->insert([
                    'User_Id'      => $user->id,
                    'Current_Tier' => $newTier,
                    'Created_At'   => $now,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Sales performance updated',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update sales performance',
                // 'error'   => $e->getMessage(), // remove this in production
            ], 500);
        }
    }

    public function membershipDashboardPage()
    {
        return view('partner.dashboard.membership-index');
    }

    public function membershipDashboardData(Request $r)
    {
        $q         = $r->query();
        $periode   = $q['periode'] ?? null;
        $dateStart = $r->date_start ? Carbon::parse($r->date_start)->startOfMonth() : null;
        $dateEnd   = $r->date_end   ? Carbon::parse($r->date_end)->endOfMonth()     : null;

        // est dp (partners that reached MOU stage)
        $dp          = DB::table('config_system')->where('Constraint', 'ONBOARDING_DOWNPAYMENT')->first();
        $mouCount = DB::table('opportunity_partner')
            ->where('onboarding_stage', 'MOU')
            ->when($dateStart, fn($q) => $q->whereDate('created_at', '>=', $dateStart))
            ->when($dateEnd,   fn($q) => $q->whereDate('created_at', '<=', $dateEnd))
            ->count();

        $est_dp      = $mouCount * ($dp->Value ?? 0);

        // leads source - count by Source_Channel
        $leadsSource = DB::table('opportunity')
            ->select(
                DB::raw("CASE
            WHEN Source_Channel IS NULL OR Source_Channel = ''
            THEN 'Tidak Diketahui'
            ELSE Source_Channel
        END as source_channel,
        count(Hub_Type) as popular_product
        "),
                DB::raw('COUNT(*) as count')
            )
            ->when($dateStart, fn($q) => $q->whereDate('Created_Date', '>=', $dateStart))
            ->when($dateEnd,   fn($q) => $q->whereDate('Created_Date', '<=', $dateEnd))
            ->groupBy(DB::raw("CASE
        WHEN Source_Channel IS NULL OR Source_Channel = ''
        THEN 'Tidak Diketahui'
        ELSE Source_Channel
    END"))
            ->get();

        $leadFunnelMetrics = app(\App\Services\LeadsService::class)->funnel([
            'date_start' => $dateStart ? $dateStart->toDateString() : null,
            'date_end'   => $dateEnd ? $dateEnd->toDateString() : null,
        ]);

        $funnelStages = [
            ['status' => 'Total Leads', 'count' => $leadFunnelMetrics['total_leads'] ?? 0],
            ['status' => 'Response',    'count' => $leadFunnelMetrics['response'] ?? 0],
            ['status' => 'Qualified',   'count' => $leadFunnelMetrics['qualified'] ?? 0],
            ['status' => 'Meeting 1',   'count' => $leadFunnelMetrics['meeting_1'] ?? 0],
            ['status' => 'Meeting 2',   'count' => $leadFunnelMetrics['meeting_2'] ?? 0],
            ['status' => 'Warm',        'count' => $leadFunnelMetrics['warm'] ?? 0],
            ['status' => 'Negotiation', 'count' => $leadFunnelMetrics['negotiation'] ?? 0],
            ['status' => 'Deal',        'count' => $leadFunnelMetrics['deal'] ?? 0],
        ];

        $salesFunnel = collect($funnelStages)->values()->map(function ($item, $index) use ($funnelStages) {
            $prevCount = $index === 0
                ? $item['count']
                : ($funnelStages[$index - 1]['count'] ?? 0);

            $ratio = $prevCount > 0
                ? round(($item['count'] / $prevCount) * 100, 1)
                : 0;

            return array_merge($item, [
                'ratio'       => $ratio,
                'ratio_label' => $ratio . '%',
            ]);
        });

        $estRevenue = 0;
        $yearNow = (int) date('Y');
        $projectionWarehouses = DB::table('warehouse_partner')->get();
        foreach ($projectionWarehouses as $pwh) {
            $baseYear = (int) DB::table('projections')
                ->where('warehouse_task_id', $pwh->warehouse_task_id)
                ->min(DB::raw('YEAR(Created_At)'));
            $yearIdx = $baseYear > 0 ? ($yearNow - $baseYear) + 1 : 1;
            if ($yearIdx < 1) {
                $yearIdx = 1;
            }
            $estRevenue += (float) DB::table('projections')
                ->where('warehouse_task_id', $pwh->warehouse_task_id)
                ->where('Description', 'Pendapatan')
                ->where('Projection_Type', 'low')
                ->where('Year', $yearIdx)
                ->sum(DB::raw('COALESCE(Month_1_Total,0) + COALESCE(Month_2_Total,0) + COALESCE(Month_3_Total,0) + COALESCE(Month_4_Total,0) + COALESCE(Month_5_Total,0) + COALESCE(Month_6_Total,0) + COALESCE(Month_7_Total,0) + COALESCE(Month_8_Total,0) + COALESCE(Month_9_Total,0) + COALESCE(Month_10_Total,0) + COALESCE(Month_11_Total,0) + COALESCE(Month_12_Total,0)'));
        }

        $brandLicense = 0;

        $hubTypeCounts = DB::table('opportunity')
            ->when($dateStart, fn($q) => $q->whereDate('Created_Date', '>=', $dateStart))
            ->when($dateEnd,   fn($q) => $q->whereDate('Created_Date', '<=', $dateEnd))
            ->selectRaw("LOWER(Hub_Type) as hub_type, COUNT(*) as count")
            ->groupBy('Hub_Type')
            ->pluck('count', 'hub_type');

        $mobil = $hubTypeCounts->get('mobil', 0);
        $booth = $hubTypeCounts->get('booth', 0);
        $topProduct = $mobil >= $booth ? 'Mobil Listrik' : 'Booth';

        // Sales performance (selected period): top 10 sales by deal count.
        $perfStart = $dateStart ?: Carbon::now()->startOfMonth();
        $perfEnd   = $dateEnd ?: Carbon::now()->endOfMonth();
        $perfStartDate = $perfStart->toDateString();
        $perfEndDate   = $perfEnd->toDateString();

        $perfStartLabel = $perfStart->format('Y-m');
        $perfEndLabel   = $perfEnd->format('Y-m');
        $perfPeriodLabel = ($perfStartLabel === $perfEndLabel)
            ? $perfStartLabel
            : ($perfStartLabel . ' s/d ' . $perfEndLabel);

        $salesUsers = DB::table('users')
            ->where('Priv_ID', 'SALES')
            ->get(['username', 'Name']);

        $dealCounts = DB::table('opportunity as o')
            ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
            ->where('o.Status', 'Deal')
            ->whereDate('o.Updated_Date', '>=', $perfStartDate)
            ->whereDate('o.Updated_Date', '<=', $perfEndDate)
            ->selectRaw('s.Sales as sales, COUNT(*) as c')
            ->groupBy('s.Sales')
            ->pluck('c', 'sales');

        $leadCounts = DB::table('opportunity as o')
            ->join('opportunity_sales as s', 'o.Sales_ID', '=', 's.Sales_ID')
            ->whereDate('o.Created_Date', '>=', $perfStartDate)
            ->whereDate('o.Created_Date', '<=', $perfEndDate)
            ->selectRaw('s.Sales as sales, COUNT(*) as c')
            ->groupBy('s.Sales')
            ->pluck('c', 'sales');

        $perfRows = [];
        foreach ($salesUsers as $u) {
            $perfRows[] = [
                'sales' => $u->Name,
                'leads' => (int) ($leadCounts[$u->username] ?? 0),
                'deal'  => (int) ($dealCounts[$u->username] ?? 0),
            ];
        }

        usort($perfRows, function ($a, $b) {
            if ($b['deal'] !== $a['deal']) {
                return $b['deal'] <=> $a['deal'];
            }

            return $b['leads'] <=> $a['leads'];
        });
        $perfRows = array_slice($perfRows, 0, 10);
        foreach ($perfRows as $i => &$row) {
            $row['rank'] = $i + 1;
        }
        unset($row);

        $salesPerformance = [
            'month' => $perfPeriodLabel,
            'rows'  => $perfRows,
        ];

        $data = [
            'est_dp' => $est_dp,
            'est_dp_formatted'        => $this->generatorService->rupiahFormatter($est_dp),
            'est_revenue'   => $estRevenue,
            'est_revenue_formatted' => $this->generatorService->rupiahFormatter($estRevenue),
            'brand_license' => $brandLicense,
            'brand_license_formatted' => $this->generatorService->rupiahFormatter($brandLicense),
            'top_product' => $topProduct,
            'leads_source'  => [
                'labels'  => $leadsSource->pluck('source_channel')->toArray(),
                'data_arr' => $leadsSource->pluck('count')->toArray(),
                'data'    => $leadsSource,
            ],
            'sales_funnel'  => [
                'labels'       => $salesFunnel->pluck('status')->toArray(),
                'data_arr'     => $salesFunnel->pluck('count')->toArray(),
                // 'ratios'       => $salesFunnel->pluck('ratio')->toArray(),
                // 'ratio_labels' => $salesFunnel->pluck('ratio_label')->toArray(),
                'data'         => $salesFunnel,
            ],
            'sales_performance' => $salesPerformance,
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Dashboard data',
            'data' => $data
        ]);
    }

    // get per warehouse, per month
    public function calculateWarehouse(Request $r)
    {
        $result = $this->warehouseService
            ->calculateMonthlyFinancialStatement($r->Task, $r->date_start, $r->date_end);

        return response()->json(['data' => $result]);
    }

    public function aftersalesDashboardPage()
    {

        return view('partner.dashboard.aftersales-index');
    }

    private function resolveContractStatus(?string $contractEndAt): string
    {
        if (!$contractEndAt) {
            return 'UNKNOWN';
        }

        $endDate = Carbon::parse($contractEndAt);
        $now = Carbon::now();

        if ($endDate->isPast()) {
            return 'EXPIRED';
        }

        if ($endDate->lt($now->copy()->addMonths(3))) {
            return 'RENEWAL_SOON';
        }

        return 'ACTIVE';
    }

    public function aftersalesDashboardData(Request $r)
    {
        $whid = $r->query('warehouse');

        if ($whid) {
            $calculatedWh = $this->warehouseService->calculateMonthlyFinancialStatement((string) $whid, $r->date_start, $r->date_end);
            $netProfitList = [['warehouse' => $whid, 'amount' => $calculatedWh['net_profit']]];
        } else {
            $all = $this->warehouseService->calculateFinancialStatementAll($r->date_start, $r->date_end);
            $calculatedWh = $all['aggregate'];
            $netProfitList = [];
            foreach ($all['per_warehouse'] as $taskId => $stmt) {
                $netProfitList[] = ['warehouse' => $taskId, 'amount' => $stmt['net_profit']];
            }
        }

        $hubStats = DB::table('opportunity_partner as wp')
            ->selectRaw("
        SUM(CASE WHEN wp.Status = 'ACTIVE' THEN 1 ELSE 0 END) as operating,
        SUM(CASE WHEN wp.Status = 'INACTIVE' THEN 1 ELSE 0 END) as closed,
        SUM(CASE WHEN wp.Status = 'PREPARING' THEN 1 ELSE 0 END) as prepared
        ")
            ->first();

        $contractMonitoring = DB::table('opportunity_partner as op')
            ->where('op.status', 'ACTIVE')
            ->get()
            ->map(function ($row) {
                $row->contract_status = $this->resolveContractStatus($row->contract_end_at);
                return $row;
            })
            ->groupBy('contract_status')
            ->map(fn($group) => $group->count())
            ->union(collect([
                'ACTIVE'       => 0,
                'RENEWAL_SOON' => 0,
                'EXPIRED'      => 0,
                'UNKNOWN'      => 0,
            ]));

        $carbonDate = $r->date_start ? Carbon::parse($r->date_start)->format('Y-m') : null;

        $ticket = DB::table('ticket as t')
            ->when($carbonDate, fn($q) => $q->whereRaw('DATE_FORMAT(t.Created_Date, "%Y-%m") = ?', [$carbonDate]))
            ->selectRaw("
        SUM(CASE WHEN t.Ticket_Status = 'OPEN' THEN 1 ELSE 0 END) as open_count,
        SUM(CASE WHEN t.Ticket_Status = 'CLOSED' THEN 1 ELSE 0 END) as resolved
    ")
            ->first();

        $dateFilter      = $carbonDate ? "AND DATE_FORMAT(tl_open.Action_Date, '%Y-%m') = '$carbonDate'" : '';
        $warehouseFilter = $whid ? "AND tl_open.GEU_Express_ID = '$whid'" : '';

        $avgTime = DB::table('ticket_log as tl1')
            ->join('ticket_log as tl2', function ($join) {
                $join->on('tl1.Ticket_No', '=', 'tl2.Ticket_No')
                    ->whereRaw('tl2.Action_Date = (
            SELECT MIN(Action_Date)
            FROM ticket_log
            WHERE Ticket_No = tl1.Ticket_No
            AND Action_Date > tl1.Action_Date
            AND Action_Date != "0000-00-00 00:00:00"
         )');
            })
            ->where('tl1.Action_Date', '!=', '0000-00-00 00:00:00')
            ->when($carbonDate, fn($q) => $q->whereRaw('DATE_FORMAT(tl1.Action_Date, "%Y-%m") = ?', [$carbonDate]))
            ->when($whid, fn($q) => $q->where('tl1.GEU_Express_ID', $whid))
            ->selectRaw("
                ROUND(AVG(TIMESTAMPDIFF(HOUR, tl1.Action_Date, tl2.Action_Date))) as avg_hours,
                (
                    SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, tl_open.Action_Date, tl_closed.Action_Date)))
                    FROM ticket_log as tl_open
                    JOIN ticket_log as tl_closed
                        ON tl_open.Ticket_No = tl_closed.Ticket_No
                        AND tl_closed.Ticket_Status = 'CLOSED'
                        AND tl_closed.Action_Date != '0000-00-00 00:00:00'
                        AND tl_closed.Action_Date = (
                            SELECT MIN(Action_Date)
                            FROM ticket_log
                            WHERE Ticket_No = tl_open.Ticket_No
                            AND Ticket_Status = 'CLOSED'
                            AND Action_Date > tl_open.Action_Date
                            AND Action_Date != '0000-00-00 00:00:00'
                        )
                    WHERE tl_open.Ticket_Status = 'OPEN'
                    AND tl_open.Action_Date != '0000-00-00 00:00:00'
                    $dateFilter
                    $warehouseFilter
                ) as avg_resolution_hours
            ")
            ->first();


        $supportRequestCount = DB::table('ticket as t')
            ->when($carbonDate, fn($q) => $q->whereRaw('DATE_FORMAT(t.Created_Date, "%Y-%m") = ?', [$carbonDate]))
            ->when($whid, fn($q) => $q->whereRaw('GEU_Express_ID = ?', [$whid]))
            ->selectRaw("
        SUM(CASE WHEN t.Ticket_Category = 'ERP'         THEN 1 ELSE 0 END) as erp_count,
        SUM(CASE WHEN t.Ticket_Category = 'Operational' THEN 1 ELSE 0 END) as operational_count,
        SUM(CASE WHEN t.Ticket_Category = 'Financial'   THEN 1 ELSE 0 END) as financial_count,
        SUM(CASE WHEN t.Ticket_Category = 'Partnership' THEN 1 ELSE 0 END) as partnership_count,
        SUM(CASE WHEN t.Ticket_Category = 'Other'       THEN 1 ELSE 0 END) as other_count")
            ->first();

        $data = [
            'hub_stats' => [
                'operating' => $hubStats->operating ?? 0,
                'prepared'  => $hubStats->prepared ?? 0,
                'closed'    => $hubStats->closed ?? 0,
            ],
            'performance' => [
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ],
            'support_request_count' => [
                'operational' => $supportRequestCount->operational_count ?? 0,
                'other' => $supportRequestCount->other_count ?? 0,
                'erp' => $supportRequestCount->erp_count ?? 0,
                'financial' => $supportRequestCount->financial_count ?? 0,
                'partnership' => $supportRequestCount->partnership_count ?? 0,
            ],
            'contract_monitoring' => [
                'active' => $contractMonitoring['ACTIVE'],
                'renewal_soon' => $contractMonitoring['RENEWAL_SOON'],
                'expired' => $contractMonitoring['EXPIRED']
            ],
            'support_performance' => [
                'open_ticket' => $ticket->open_count ?? 0,
                'resolved' => $ticket->resolved ?? 0,
                'mttr' => $avgTime->avg_hours ?? 0, // avg time to respond
                'mttf' => $avgTime->avg_resolution_hours ?? 0, // avg time to fix
            ],
            'net_profit' => $netProfitList,

            'complaints_list' => [],
            'total_income' => $calculatedWh['income'],
            'total_net_profit' => $calculatedWh['net_profit'],
            'uco_volume' => $calculatedWh['qty_in'] ?? 0,
            'uco_volume_sold' => $calculatedWh['qty_out'] ?? 0,
            'management_fee' => ($calculatedWh['expense_details'][601][61007] ?? 0) + ($calculatedWh['expense_details'][601][61008] ?? 0),
            'company_royalty' => $calculatedWh['company_profit_share'] ?? ($calculatedWh['net_profit'] * 0.4),
            'mitra_royalty' => $calculatedWh['nominal_royalty'],
            'company_total_profit' => $calculatedWh['company_profit_share'] ?? ($calculatedWh['net_profit'] * 0.4)
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'success',
            'data' => $data
        ]);
    }


    public function financialInsightDashboardPage()
    {
        return view('partner.dashboard.financial-insight-dashboard');
    }

    public function financialInsightDashboardData(Request $r)
    {
        $start = Carbon::parse($r->date_start ?? now())->startOfMonth();
        $end   = Carbon::parse($r->date_end   ?? now())->startOfMonth();

        // Accumulated totals across all months
        $res = [
            'qty_in'              => 0,
            'qty_out'             => 0,
            'total_investation'   => 0,
            'total_transaction'   => 0,
            'opex'                => 0,
            'income'              => 0,
            'total_overhead_cost' => 0,
            'net_profit'          => 0,
            'nominal_royalty'     => 0,
            'raw_material_price'  => 0,
            'direct_cost'         => 0,
            'overhead_cost'       => 0,
            'cogs'                => 0,
            'ebitda'              => 0,
            'gross_margin'        => 0,
            'revenue'             => 0,
            'total_roi'           => 0,
            'net_profit_high' => 0,
            'net_profit_low' => 0
        ];
        $meta = [];

        $projectionVsBEP = []; //projection vs realisation bep 
        $projectionVsRoyalty = []; //projection vs realisation royalty/profit sharing 

        $cursor = $start->copy();
        while ($cursor <= $end) {
            $monthly = $r->Task
                ? $this->warehouseService->calculateMonthlyFinancialStatement(
                    $r->Task,
                    $cursor->format('Y-m-01'),
                    $cursor->format('Y-m-t')
                )
                : $this->warehouseService->calculateMonthlyFinancialStatementAll(
                    $cursor->format('Y-m-01'),
                    $cursor->format('Y-m-t')
                );

            foreach ($res as $key => $_) {
                $res[$key] += $monthly[$key] ?? 0;
            }

            $res['total_investation'] = $monthly['total_investation'];

            $projectionVsBEP[] = [
                'month'          => $cursor->format('Y-m'),
                'total_investation' => $monthly['total_investation'],
                'projection_net_margin_high' => $monthly['projection_net_profit_high'],
                'projection_net_margin_low' => $monthly['projection_net_profit_low'],
                'net_margin' => $monthly['net_profit']
            ];
            // $meta = [
            //     'month' => $cursor->format('Y-m')
            // ];

            $projectionVsRoyalty[] = [
                'month'          => $cursor->format('Y-m'),
                'projection_net_profit_high' => $monthly['projection_royalty_high'],
                'projection_net_profit_low' => $monthly['projection_royalty_low'],
                'profit_sharing' => $monthly['nominal_royalty'],
            ];

            $cursor->addMonth();
        }

        $data = [
            'total_profit_sharing'   => $res['nominal_royalty'],
            'total_revenue'          => $res['revenue'],
            'total_operational_cost' => $res['opex'],
            'total_qty'              => $res['qty_in'],
            'total_investation'      => $res['total_investation'],
            'total_roi'              => $res['total_investation'] > 0 ? round(($res['ebitda'] / $res['total_investation']) * 100, 2) : 0,
            'margin_profit'          => $res['revenue'] > 0 ? round(($res['net_profit'] / $res['revenue']) * 100, 2) : 0,
            'uco_transaction'        => $res['qty_out'] ?? 0,
            'projection_against_bep' => $projectionVsBEP,
            'projection_against_profit_sharing' =>  $projectionVsRoyalty,
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Financial dashboard',
            'meta' => $meta,
            'data'    => $data,
        ]);
    }
}
