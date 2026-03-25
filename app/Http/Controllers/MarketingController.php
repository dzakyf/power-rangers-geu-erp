<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class MarketingController extends Controller
{
    protected $uploadService;
    protected $year;
    protected $month;

    public function __construct(UploadService $up)
    {
        $this->uploadService = $up;
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->format('m');
    }

    public function indexMarketing()
    {
        return view('partner.branding.marketing-index');
    }

    public function indexPromotion()
    {
        return view('partner.branding.promotion-index');
    }

    public function indexMonitoring()
    {
        return view('partner.branding.monitoring-index');
    }

    public function getMarketingData()
    {

        $d = DB::table('branding_marketing as m')->select('m.*', 'mf.*')
            ->leftJoin('meta_files as mf', 'mf.Map_To', '=', 'm.ID')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', $d->Name);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Channel);
            $row->addChild('cell', $d->Description);
            $row->addChild('cell', $d->Created_At);
            $row->addChild('cell', $d->Status);
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getPromotionData()
    {
        $d = DB::table('branding_promotion as p')->select('p.ID as Promotion_Id', 'p.*', 'p.Description as Promotion_Desc', 'mf.ID as File_Id', 'mf.*')
            ->leftJoin('meta_files as mf', 'mf.Map_To', '=', 'p.ID')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->Promotion_Id);
            $row->addChild('cell', $d->Code);
            $row->addChild('cell', htmlspecialchars($d->Department));
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Marketing_Id);
            $row->addChild('cell', htmlspecialchars($d->Promotion_Desc));
            $row->addChild('cell', $d->Audience);
            $row->addChild('cell', $d->Estimation);
            $row->addChild('cell', $d->Status);
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getMonitoringPromotionData()
    {
        $d = DB::table('branding_monitoring as bm')
            ->select('bm.*', 'bm.ID as Monitoring_Id', 'bp.*', 'bp.ID as Promotion_Id', 'bp.Code as Promotion_Code')
            ->leftJoin('branding_promotion as bp', 'bp.ID', '=', 'bm.Promotion_Id')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('Monitoring_Id', $d->Monitoring_Id);
            $row->addAttribute('Promotion_Id', $d->Promotion_Id);
            $row->addChild('cell', $d->Promotion_Code);
            $row->addChild('cell', $d->Campaign_Start_At);
            $row->addChild('cell', $d->Campaign_End_At);
            $row->addChild('cell', $d->Platform);
            $row->addChild('cell', $d->Budget_Realization);
            $row->addChild('cell', $d->Campaign_Status);
            $row->addChild('cell', $d->PIC_Id);
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


        $promotionId = DB::table('branding_promotion')->insertGetId([
            "Code"  => $b["code"],
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
            "Campaign_Status" => $b["campaign_status"],
            "PIC_Id" => $b["PIC_id"],
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

        $d = DB::table('opportunity_partner')->select('*')->get();

        $xml = new SimpleXMLElement('<rows></rows>');


        $data = [[
            "Month" => "",
            "Year" => "",
            "Platform" => "",
            "Ads_Fee" => "",
            "Leads" => "",
            "Content_Url" => "",
        ]];

        foreach ($data as $d) {
            $row = $xml->addChild('row');
            // $row->addAttribute('Opportunity_Id', $d->id);
            $row->addChild('cell', $d['Month']);
            $row->addChild('cell', $d['Year']);
            $row->addChild('cell', $d['Platform']);
            $row->addChild('cell', $d['Ads_Fee']);
            $row->addChild('cell', $d['Content']);
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
