<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class MarketingController extends Controller
{
    protected $uploadService;
    protected $userService;
    protected $year;
    protected $month;

    public function __construct(UploadService $up, UserService $u)
    {
        $this->uploadService = $up;
        $this->userService = $u;
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->format('m');
    }

    public function indexMarketing()
    {
        return view('partner.branding.marketing-index');
    }

    public function indexMarketingRecap()
    {
        return view('partner.branding.marketing-recap');
    }

    public function indexPromotion()
    {
        return view('partner.branding.promotion-index');
    }

    public function indexMonitoring()
    {
        return view('partner.branding.monitoring-index');
    }

    public function indexEvaluation()
    {
        return view('partner.branding.evaluation-index');
    }

    public function getEvaluationData(Request $r)
    {
        $q = $r->query();

        $d = DB::table('branding_monitoring as bm')
            ->select([
                'bm.*',
                'bm.ID as Monitoring_Id',
                'bp.*',
                'bp.ID as Promotion_Id',
                'bp.Code as Promotion_Code',
                'us.Name as PIC_Name',
                DB::raw('(
            SELECT COUNT(*)
            FROM opportunity o
            WHERE o.Created_Date BETWEEN bm.Campaign_Start_At AND bm.Campaign_End_At
        ) as Total_Leads'),
                DB::raw('(
            SELECT COUNT(*)
            FROM opportunity o
            WHERE o.Status = \'Deal\'
              AND o.Created_Date BETWEEN bm.Campaign_Start_At AND bm.Campaign_End_At
        ) as Total_Deals'),
            ])
            ->leftJoin('branding_promotion as bp', 'bp.ID', '=', 'bm.Promotion_Id')
            ->leftJoin('users as us', 'us.id', '=', 'bm.PIC_Id')
            ->where('bp.ID', '=', $q['promotion_id'])
            ->get();

        $config = DB::table('config_system as cs')->select('*')->where('Constraint', '=', 'TOTAL_HUB_INVESTATION')->first();



        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        $costPerLead = $d[0]->Budget_Realization / $d[0]->Total_Leads;
        $conversionRate = (($d[0]->Total_Deals / $d[0]->Total_Leads) * 100) . "%";
        $costPerAcquisition = $d[0]->Budget_Realization / $d[0]->Total_Deals;
        $priceDealLead = $config->Value;
        $revenue = $priceDealLead * $d[0]->Total_Deals;
        $ROI = (($revenue - $d[0]->Budget_Realization) / $d[0]->Budget_Realization * 100) . "%";
        foreach ($d as $d) {

            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Budget_Realization);
            $row->addChild('cell', $d->Total_Leads);
            $row->addChild('cell', $costPerLead);
            $row->addChild('cell', $d->Total_Deals);
            $row->addChild('cell', $conversionRate);
            $row->addChild('cell', $costPerAcquisition);
            $row->addChild('cell', $priceDealLead);
            $row->addChild('cell', $revenue);
            $row->addChild('cell', $ROI);

            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function submitPromotionEvaluation(Request $r)
    {
        $b = $r->all();

        dd($b);

        return response()->json(['message' => 'success']);
    }

    public function createMarketingData(Request $r)
    {
        return view('partner.branding.marketing-create');
    }

    public function createPromotionData(Request $r)
    {
        return view('partner.branding.promotion-create');
    }

    public function createMonitoringData(Request $r)
    {
        return view('partner.branding.monitoring-create');
    }

    public function selectMarketingData(Request $r)
    {
        $q = $r->query();

        if (!empty($q['marketing_id'])) {
            return $this->getMarketingDataById($r);
        }

        return $this->getMarketingData($r);
    }

    public function getMarketingData(Request $r)
    {

        $q = $r->query();

        $d = DB::table('branding_marketing as m')->select('m.*', 'm.Description as Marketing_Desc', 'm.Created_At as Marketing_Created_At', 'mf.*')
            ->leftJoin('meta_files as mf', 'mf.Map_To', '=', 'm.ID')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Name);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Channel);
            $row->addChild('cell', $d->Marketing_Desc);
            $row->addChild('cell', $d->Status);
            $row->addChild('cell', $d->Marketing_Created_At);
            $row->addChild('cell', $d->Media_Url);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getMarketingDataById(Request $r)
    {
        $q = $r->query();

        // dd($q);

        $d = DB::table('branding_marketing as m')->select('m.*', 'm.ID as Marketing_Id', 'm.Description as Marketing_Desc', 'm.Created_At as Marketing_Created_At', 'mf.ID as File_Id')
            ->leftJoin('meta_files as mf', 'mf.Map_To', '=', 'm.ID')
            ->where('m.ID', '=', $q['marketing_id'])
            ->get();


        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Name);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Channel);
            $row->addChild('cell', $d->Marketing_Desc);
            $row->addChild('cell', $d->Status);
            $row->addChild('cell', $d->Marketing_Created_At);
            $row->addChild('cell', $d->Media_Url);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getPromotionData()
    {
        $d = DB::table('branding_promotion as p')->select('p.ID as Promotion_Id', 'p.*', 'p.Description as Promotion_Desc', 'p.Created_At as Applied_At', 'bm.Code as Marketing_Code', 'mf.ID as File_Id', 'mf.*')
            ->join('branding_marketing as bm', 'bm.ID', '=', 'p.Marketing_Id')
            ->leftJoin('meta_files as mf', 'mf.Map_To', '=', 'p.ID')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');


        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->Promotion_Id);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Applied_At);
            $row->addChild('cell', htmlspecialchars($d->Department));
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Marketing_Code);
            $row->addChild('cell', htmlspecialchars($d->Promotion_Desc));
            $row->addChild('cell', $d->Audience);
            $row->addChild('cell', $d->Estimation);
            $row->addChild('cell', $d->Status);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getMonitoringPromotionData()
    {
        $d = DB::table('branding_monitoring as bm')
            ->select('bm.*', 'bm.ID as Monitoring_Id', 'bp.*', 'bp.ID as Promotion_Id', 'bp.Code as Promotion_Code', 'us.Name as PIC_Name')
            ->leftJoin('branding_promotion as bp', 'bp.ID', '=', 'bm.Promotion_Id')
            ->leftJoin('users as us', 'us.id', '=', 'bm.PIC_Id')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('Monitoring_Id', $d->Monitoring_Id);
            $row->addAttribute('Promotion_Id', $d->Promotion_Id);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Promotion_Code);
            $row->addChild('cell', $d->Campaign_Start_At);
            $row->addChild('cell', $d->Campaign_End_At);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Budget_Realization);
            $row->addChild('cell', $d->Campaign_Status);
            $row->addChild('cell', $d->PIC_Name);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }




    public function submitMarketing(Request $r)
    {
        $b = $r->all();

        $file = $r->file('design');

        $marketingId = DB::table('branding_marketing')->insertGetId([
            "Code" => "XXXX",
            "Name" => $b['name'],
            "Type" => $b['type'],
            "Platform" => $b['platform'],
            "Channel" => $b['channel'],
            "Description" => $b['description'],
            "Status" => $b['status'],
            "Media_Url" => $b['media_url']
        ]);

        $path = $file->store("$this->year/$this->month", 'public');
        $ext = $file->getClientOriginalExtension();

        $filename = basename($path);
        $desc = "Design marketing";
        $topic = "branding-marketing";
        $mapTo = $marketingId;

        $this->uploadService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        // $file = $file->storeAs("$this->year/$this->month", 'public');

        return response()->json(['message' => 'success']);
    }


    //TODO: ganti ke document creation phpoffice
    public function submitPromotion(Request $r)
    {
        $b = $r->all();

        $allFiles = $r->allFiles();
        $user = $this->userService->getCurrentSessionUserInfo();

        $promotionId = DB::table('branding_promotion')->insertGetId([
            "Code"  => "XXXX",
            "Department"  => $b["department"],
            "Type"  => $b["type"],
            "Platform"  => $b["platform"],
            "Marketing_Id"  => $b["marketing_id"],
            "Description"  => $b["description"],
            "Audience"  => $b["audience"],
            "Estimation"  => $b["estimation"],
            "Status"  => $b["status"],
        ]);

        if (isset($allFiles['RAB'])) {
            $files = $allFiles['RAB'];

            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                if ($file->isValid()) {
                    $path = $file->store("$this->year/$this->month", 'public');
                    $ext = $file->getClientOriginalExtension();

                    $filename = basename($path);
                    $desc = "Design marketing";
                    $topic = "branding-marketing";
                    $mapTo = $promotionId;

                    $this->uploadService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);
                }
            }
        }

        $level = DB::table('approval_level as al')->select('*')
            ->where("al.Request_Type", '=', 'BRANDING_PROMOTION')
            ->where("al.Level_Number", '=', 1)
            ->get();

        $approvalId = DB::table('approval_request')->insertGetId([
            "Status" => "NEED_APPROVAL",
            "Requester_Id" => $user->id,
            "Description" => $level[0]->Description,
            "Current_Level" => $level[0]->Level_Number,
            "Request_Type" => $level[0]->Request_Type,
            "Level_Id" => $level[0]->ID,
            // "Opportunity_Partner_Id" => "",
            "Map_To" => $promotionId,
        ]);

        return response()->json(['message' => 'success']);
    }

    public function submitMonitoring(Request $r)
    {
        $b = $r->all();

        $monitoringId = DB::table('branding_monitoring')->insertGetId([
            "Promotion_Id" => $b["promotion_id"],
            "Campaign_Start_At" => $b["campaign_start_at"],
            "Campaign_End_At" => $b["campaign_end_at"],
            "Platform" => $b["platform"],
            "Budget_Realization" => $b["budget_realization"],
            "Campaign_Status" => $b["status"],
            "PIC_Id" => $b["sales"],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function followUpPromotion(Request $r)
    {
        $b = $r->all();

        $followUpId = DB::table('branding_followup')->insertGetId([
            "Promotion_Id" => $b["promotion_id"],
            "Platform" => $b["platform"],
            "Total_Budget" => $b["total_budget"],
            "Total_Leads" => $b["total_leads"],
            "Cost_Per_Lead" => $b["cost_per_lead"],
            "Total_Deal" => $b["total_deal"],
            "Conversion_Rate" => $b["conversion_rate"],
            "Cost_Per_Acquisition" => $b["cost_per_acquisition"],
            "Price_Deal_Lead" => $b["price_deal_lead"],
            "Revenue" => $b["revenue"],
            "ROI" => $b["ROI"],
            "Recommendation" => $b["recommendation"],
        ]);


        return response()->json(['message' => 'success']);
    }

    public function getRecapMarketingData(Request $r)
    {

        $d = DB::table('opportunity')->select('ID',  'Source_Channel', 'Date')->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $allData = [];
        $monthlyData = [];

        foreach ($d as $item) {
            $date = new DateTime($item->Date);
            $month = $date->format('F');
            $year = $date->format('Y');
            $source = $item->Source_Channel ?? 'Unknown';
            $key = $month . '_' . $year . '_' . $source;

            if ($year == '-0001' || $source == 'Unknown' || $source == '') {
                continue;
            }

            if (!isset($monthlyData[$key])) {
                $monthlyData[$key] = [
                    'Month' => $month,
                    'Year' =>  $year,
                    'Platform' => $source,
                    'Ads_Fee' => 0,
                    'Leads' => 0,
                    'Content_Url' => '#'
                ];
            }

            $monthlyData[$key]['Leads']++;

            if (isset($item->Ads_Fee)) {
                $monthlyData[$key]['Ads_Fee'] += $item->Ads_Fee;
            }

            if (isset($item->Leads)) {
                $monthlyData[$key]['Leads'] += $item->Leads;
            }

            if (isset($item->Content_Url) && $item->Content_Url) {
                if (empty($monthlyData[$key]['Content_Url'])) {
                    $monthlyData[$key]['Content_Url'] = $item->Content_Url;
                } else {
                    $monthlyData[$key]['Content_Url'] .= ', ' . $item->Content_Url;
                }
            }
        }

        $allData = array_values($monthlyData);

        usort($allData, function ($a, $b) {
            $monthOrder = array_flip([
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ]);

            if ($a['Year'] != $b['Year']) {
                return $a['Year'] - $b['Year'];
            }

            return $monthOrder[$a['Month']] - $monthOrder[$b['Month']];
        });

        // dd($allData);

        $counter = 1;
        foreach ($allData as $d) {
            $row = $xml->addChild('row');
            // $row->addAttribute('Opportunity_Id', $d->id);
            $row->addChild('cell', $counter);
            $row->addChild('cell', $d['Month']);
            $row->addChild('cell', $d['Year']);
            $row->addChild('cell', $d['Platform']);
            $row->addChild('cell', $d['Ads_Fee']);
            $row->addChild('cell', $d['Leads']);
            $row->addChild('cell', $d['Content_Url']);
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
