<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Opportunity;
use App\OpportunityPartnerFacility;
use App\OpportunityPartnerFacilityDtl;
use App\Services\UserService;
use App\User;
use App\WarehousePartner;
use Carbon\Carbon;
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
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id] ?? $Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By] ?? $Fetch->Created_By) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailValuation(Request $request)
    {
        $warehouse = WarehousePartner::where("warehouse_task_id", $request->Task)->first();
        if (!$warehouse) {
            abort(404, 'Warehouse tidak ditemukan');
        }
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

        $getAsset = DB::table('master_facility')->where('is_deleted', 0)->where('asset_type', 'Asset')->get();
        $asId = $getAsset->pluck('id');
        $asset = $getAsset->keyBy('id');
        $startDate = DB::table('opportunity_partner')->where("id", $request->partner)->select('contract_start_at')->first();

        // $facility = OpportunityPartnerFacility::where("Partner_ID", $request->partner)->whereIn('Facility_ID', $asId)->where('warehouse_task_id', $request->warehouse)->first();
        // $facId = $facility->pluck('ID');

        $facilityDtl = OpportunityPartnerFacilityDtl::whereIn("Facility_ID", $asId)->where('Partner_ID', $request->partner)->where('warehouse_task_id', $request->warehouse)->get();

        $cat = "";
        foreach ($facilityDtl as $key => $Fetch) {
            $orderDate = ($Fetch->Order_Date && $Fetch->Order_Date !== '0000-00-00') ? $Fetch->Order_Date : now()->format('Y-m-d');
            $depreciationYear = $asset[$Fetch->Facility_ID]->depreciation_year ?: 1;

            $month = ($Fetch->Order_Date && $Fetch->Order_Date !== '0000-00-00')
                ? (int) Carbon::parse($Fetch->Order_Date)->startOfDay()->diffInMonths(Carbon::now()->startOfDay())
                : 0;
            $month = min($month, $depreciationYear * 12);

            $prosen = 1 / $depreciationYear;
            $deprPerBulan = $Fetch->Total * $prosen / 12;
            $sisaWaktu = max(0, ($depreciationYear * 12) - $month);
            echo "<row id=\"$Fetch->ID\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($orderDate) . "</cell>";
            echo "<cell>" . htmlspecialchars($asset[$Fetch->Facility_ID]->description) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Qty) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Price) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Total) . "</cell>";
            echo "<cell>" . htmlspecialchars($depreciationYear) . "</cell>";
            echo "<cell>" . htmlspecialchars(round($prosen * 100, 2) . '%') . "</cell>";
            echo "<cell>" . htmlspecialchars($deprPerBulan) . "</cell>";
            echo "<cell>" . htmlspecialchars(strtoupper($asset[$Fetch->Facility_ID]->asset_type ?: '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars($month) . "</cell>";
            echo "<cell>" . htmlspecialchars($deprPerBulan * $month) . "</cell>";
            echo "<cell>" . htmlspecialchars($sisaWaktu) . "</cell>";
            echo "<cell>" . htmlspecialchars($deprPerBulan * $sisaWaktu) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    // Gabungan valuasi semua gudang di satu akun mitra tersebut
    public function detailAggregateValuationXml(Request $r)
    {
        $session = $this->userService->getCurrentSessionUserInfo();
        $requestedPartner = $r->query('partner') ?? $r->query('opportunity_id');
        $partnerRow = null;

        if ($requestedPartner) {
            $partnerRow = DB::table('opportunity_partner')
                ->select('id', 'lead_id', 'user_row_id')
                ->where('id', $requestedPartner)
                ->first();

            if (!$partnerRow) {
                return $this->emptyValuationXml();
            }

            if (($session->Position ?? '') === 'MITRA'
                && (int) $partnerRow->user_row_id !== (int) $session->id) {
                abort(403, 'Anda tidak memiliki akses ke data mitra ini.');
            }
        } elseif (($session->Position ?? '') === 'MITRA') {
            $partnerRow = DB::table('opportunity_partner')
                ->select('id', 'lead_id', 'user_row_id')
                ->where('user_row_id', $session->id)
                ->first();

            if (!$partnerRow) {
                return $this->emptyValuationXml();
            }
        } else {
            // Internal users must pass the selected mitra.
            return $this->emptyValuationXml();
        }

        $getAsset = DB::table('master_facility')
            ->where('is_deleted', 0)
            ->where('asset_type', 'Asset')
            ->get();
        $asId = $getAsset->pluck('id');
        $asset = $getAsset->keyBy('id');

        $facilityDtl = OpportunityPartnerFacilityDtl::whereIn('Facility_ID', $asId)
            ->where('Partner_ID', $partnerRow->id)
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');
        $nomor = 1;

        foreach ($facilityDtl as $Fetch) {
            $assetRow = $asset[$Fetch->Facility_ID] ?? null;
            if (!$assetRow) {
                continue;
            }

            $orderDate = ($Fetch->Order_Date && $Fetch->Order_Date !== '0000-00-00')
                ? $Fetch->Order_Date
                : now()->format('Y-m-d');
            $depreciationYear = $assetRow->depreciation_year ?: 1;

            $month = ($Fetch->Order_Date && $Fetch->Order_Date !== '0000-00-00')
                ? (int) Carbon::parse($Fetch->Order_Date)->startOfDay()->diffInMonths(Carbon::now()->startOfDay())
                : 0;
            $month = min($month, $depreciationYear * 12);

            $prosen = 1 / $depreciationYear;
            $deprPerBulan = $Fetch->Total * $prosen / 12;
            $sisaWaktu = max(0, ($depreciationYear * 12) - $month);

            $row = $xml->addChild('row');
            $row->addAttribute('ID', $Fetch->ID);
            $row->addChild('cell', $nomor);
            $row->addChild('cell', $orderDate);
            $row->addChild('cell', htmlspecialchars($assetRow->description));
            $row->addChild('cell', $Fetch->Qty);
            $row->addChild('cell', $Fetch->Price);
            $row->addChild('cell', $Fetch->Total);
            $row->addChild('cell', $depreciationYear);
            $row->addChild('cell', round($prosen * 100, 2) . '%');
            $row->addChild('cell', $deprPerBulan);
            $row->addChild('cell', strtoupper($assetRow->asset_type ?: '-'));
            $row->addChild('cell', $month);
            $row->addChild('cell', $deprPerBulan * $month);
            $row->addChild('cell', $sisaWaktu);
            $row->addChild('cell', $deprPerBulan * $sisaWaktu);

            $nomor++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    private function emptyValuationXml()
    {
        $xml = new SimpleXMLElement('<rows></rows>');

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function summaryValuation()
    {
        return view('partner.valuation.summary');
    }

    public function summaryValuationXml(Request $request)
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
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }

        $getAsset = DB::table('master_facility')->where('is_deleted', 0)->where('asset_type', 'Asset')->get();
        $asId = $getAsset->pluck('id');
        $asset = $getAsset->keyBy('id');

        // $facility = OpportunityPartnerFacility::where("Partner_ID", $request->partner)->whereIn('Facility_ID', $asId)->where('warehouse_task_id', $request->warehouse)->first();
        // $facId = $facility->pluck('ID');

        $get = WarehousePartner::get();
        foreach ($get as $key => $value) {
            $optp = DB::table('opportunity_partner')->where("lead_id", $value->partner_task_id)->select(['contract_start_at', 'id'])->first();

            $constraintName = strtoupper($value->warehouse_type) === 'MOBIL' ? 'TOTAL_HUB_INVESTATION_CAR' : 'TOTAL_HUB_INVESTATION_BOOTH';
            $sumInvest = DB::table('config_system')->where('Constraint', $constraintName)->value('Value') ?? 0;

            $facilityDtl = OpportunityPartnerFacilityDtl::whereIn("Facility_ID", $asId)->where('Partner_ID', $optp->id ?? 0)->where('warehouse_task_id', $value->warehouse_task_id)->get();

            $cat = "";
            $sumTotal = 0;
            $sumDepr = 0;
            $sumSisa = 0;
            foreach ($facilityDtl as $key => $Fetch) {
                // $warehouse = WarehousePartner::where('warehouse_task_id', $Fetch->warehouse_task_id)->first();
                // $d = DB::table('capital_expenditure as ce')
                //     ->join('opportunity_partner as op', 'op.id', '=', 'ce.Opportunity_Id')
                //     ->select('ce.Asset_Name', 'ce.Cost')
                //     ->where('op.lead_id', $warehouse->partner_task_id)
                //     ->get();

                $orderDate = ($Fetch->Order_Date && $Fetch->Order_Date !== '0000-00-00') ? $Fetch->Order_Date : now()->format('Y-m-d');
                $depreciationYear = $asset[$Fetch->Facility_ID]->depreciation_year ?: 1;

                $month = ($Fetch->Order_Date && $Fetch->Order_Date !== '0000-00-00')
                    ? (int) Carbon::parse($Fetch->Order_Date)->startOfDay()->diffInMonths(Carbon::now()->startOfDay())
                    : 0;
                $month = min($month, $depreciationYear * 12);

                $prosen = 1 / $depreciationYear;
                $deprPerBulan = $Fetch->Total * $prosen / 12;
                $sisaWaktu = max(0, ($depreciationYear * 12) - $month);

                $sumTotal += $Fetch->Total;
                $sumDepr += $deprPerBulan * $month;
                $sumSisa += $deprPerBulan * $sisaWaktu;
            }


            echo "<row id=\"$value->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($value->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$value->partner_task_id] ?? $value->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($value->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($sumInvest) . "</cell>";
            echo "<cell>" . htmlspecialchars($sumTotal) . "</cell>";
            echo "<cell>" . htmlspecialchars($sumDepr) . "</cell>";
            echo "<cell>" . htmlspecialchars($sumSisa) . "</cell>";
            echo "</row>";
            $nomor++;
        }

        echo "</rows>";
    }
}
