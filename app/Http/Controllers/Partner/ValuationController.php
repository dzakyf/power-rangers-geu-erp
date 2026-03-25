<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Opportunity;
use App\OpportunityPartnerFacility;
use App\OpportunityPartnerFacilityDtl;
use App\Services\UserService;
use App\User;
use App\WarehousePartner;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ValuationController extends Controller
{
    protected $userService;
    public function __construct(UserService $u)
    {
        $this->userService = $u;
    }

    public function indexValuation()
    {
        return view('partner.valuation.list');
    }

    public function indexValuationXml()
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
        $getPartner = DB::table('opportunity_partner')->get();
        foreach ($getPartner as $key => $value) {
            $usname[$value->lead_id] = $value->full_name;
        }
        $nomor = 1;

        if ($priv == 'MITRA') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }

        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailValuation(Request $request)
    {
        $warehouse = WarehousePartner::where("warehouse_task_id", $request->Task)->first();
        $partner = DB::table('opportunity_partner')->where('lead_id', $warehouse->partner_task_id)->first();

        return view('partner.valuation.list-detail', compact('warehouse', 'partner'));
    }

    public function detailValuationXml(Request $request)
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
        $nomor = 1;

        $getAsset = DB::table('master_facility')->where('asset_type', 'Asset')->get();
        $asId = $getAsset->pluck('id');
        $asset = $getAsset->keyBy('id');
        $startDate = DB::table('opportunity_partner')->where("id", $request->partner)->select('contract_start_at')->first();

        // $facility = OpportunityPartnerFacility::where("Partner_ID", $request->partner)->whereIn('Facility_ID', $asId)->where('warehouse_task_id', $request->warehouse)->first();
        // $facId = $facility->pluck('ID');

        $facilityDtl = OpportunityPartnerFacilityDtl::whereIn("Facility_ID", $asId)->where('Partner_ID', $request->partner)->where('warehouse_task_id', $request->warehouse)->get();

        $cat = "";
        foreach ($facilityDtl as $key => $Fetch) {
            $startDate = new DateTime($Fetch->Order_Date);
            $endDate = new DateTime();

            $interval = $startDate->diff($endDate);
            $month = floor($interval->days / 12);

            $prosen = round(1 / $asset[$Fetch->Facility_ID]->depreciation_year);
            $depr = $Fetch->Total * $prosen;
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Order_Date) . "</cell>";
            echo "<cell>" . htmlspecialchars($asset[$Fetch->Facility_ID]->description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Price) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
            echo "<cell>" . htmlspecialchars($asset[$Fetch->Facility_ID]->depreciation_year) . "</cell>";
            echo "<cell>" . htmlspecialchars($prosen) . "</cell>";
            echo "<cell>" . htmlspecialchars($depr) . "</cell>";
            echo "<cell>" . htmlspecialchars($asset[$Fetch->Facility_ID]->asset_type) . "</cell>";
            echo "<cell>" . htmlspecialchars($month) . "</cell>";
            echo "<cell>" . htmlspecialchars($depr * $month) . "</cell>";
            echo "<cell>" . htmlspecialchars(($asset[$Fetch->Facility_ID]->depreciation_year * 12) - $month) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Total - ($depr * $month)) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    // Gabungan valuasi semua gudang di satu akun mitra tersebut
    public function detailAggregateValuationXml(Request $r)
    {
        $q = $r->query();

        // partner
        $session = $this->userService->getCurrentSessionUserInfo();

        $partner = DB::table('opportunity_partner as op')->select('op.id', 'op.lead_id')
            // ->where('op.user_row_id', '=', '2345')
            ->where('op.user_row_id', '=', $session->id)
            ->get();


        // gudang
        // dari session tarik ke opportunity partner tarik lagi ke warehouse 
        $warehouse = DB::table('warehouse_partner as wp')
            // ->where('wp.partner_task_id', '=', $partner[0]->lead_id ?? "BP/GEU-HO/20260306113133/212")
            ->where('wp.partner_task_id', '=', $partner[0]->lead_id)
            ->pluck('wp.warehouse_task_id')->toArray();

        $r->merge([
            'partner' => $partner[0]->id,
            'warehouse' => $warehouse
        ]);

        $r->query->add([
            'partner' => $partner[0]->id,
            'warehouse' => $warehouse
        ]);


        $getAsset = DB::table('master_facility')->where('asset_type', 'Asset')->get();
        $asId = $getAsset->pluck('id');
        $asset = $getAsset->keyBy('id');
        $startDate = DB::table('opportunity_partner')->where("id", $r->partner)->select('contract_start_at')->first();


        $facilityDtl = OpportunityPartnerFacilityDtl::whereIn("Facility_ID", $asId)->where('Partner_ID', $r->partner)
            ->whereIn('warehouse_task_id', $r->warehouse)
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $nomor = 1;
        foreach ($facilityDtl as $key => $Fetch) {
            $startDate = new DateTime($Fetch->Order_Date);
            $endDate = new DateTime();

            $interval = $startDate->diff($endDate);
            $month = floor($interval->days / 12);

            $prosen = round(1 / $asset[$Fetch->Facility_ID]->depreciation_year);
            $depr = $Fetch->Total * $prosen;

            $row = $xml->addChild('row');
            $row->addAttribute("ID", $Fetch->ID);
            $row->addChild('cell', $nomor);
            $row->addChild('cell', $Fetch->Order_Data);
            $row->addChild('cell', htmlspecialchars($asset[$Fetch->Facility_ID]->description));
            $row->addChild('cell', $Fetch->Qty);
            $row->addChild('cell', $Fetch->Price);
            $row->addChild('cell', $Fetch->Total);
            $row->addChild('cell', $Fetch->$asset[$Fetch->Facility_ID]->depreciation_year ?? 0);
            $row->addChild('cell', $Fetch->$prosen);
            $row->addChild('cell', $Fetch->$depr);
            $row->addChild('cell', $Fetch->$asset[$Fetch->Facility_ID]->asset_type ?? "");
            $row->addChild('cell', $month);
            $row->addChild('cell', $depr * $month);
            $row->addChild('cell', ($asset[$Fetch->Facility_ID]->depreciation_year * 12) - $month);
            $row->addChild('cell', $Fetch->Total - ($depr * $month));

            $nomor++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function summaryValuationXml(Request $request)
    {
        //
    }
}
