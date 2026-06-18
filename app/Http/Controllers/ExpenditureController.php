<?php

namespace app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use SimpleXMLIterator;

class ExpenditureController extends Controller
{

    protected $userService;
    public function __construct(UserService $us)
    {
        $this->userService = $us;
        // throw new \Exception('Not implemented');
    }

    public function indexCapitalExpenditure()
    {
        return view('partner.capex.index');
    }



    public function getCapitalExpenditureData(Request $r)
    {
        $b = $r->all();
        $q = $r->query();


        // opportunity_partner_facility_dtl <- opportunity_partner_facility -> master_facility
        // $d = DB::table('opportunity_partner as op')->select('op.full_name', 'opfd.*', 'mf.description')
        //     ->join('opportunity_partner_facility_dtl as opfd', 'opfd.Partner_ID', '=', 'op.id')
        //     ->join('master_facility as mf', 'mf.id', '=', 'opfd.Facility_ID')
        //     ->where('op.id', '=', $q['opportunity_id']);

        // $d = $d->get();

        $grouped = DB::table('opportunity_partner as op')
            ->select(
                'op.full_name',
                DB::raw("CASE WHEN op.package_option = 'mobil' THEN 'Mobile Unit' ELSE 'Store (Booth)' END as description"),
                DB::raw('SUM(opfd.Total) as Total'),
                DB::raw('NULL as Facility_ID'),
                DB::raw('NULL as ID')
            )
            ->join('opportunity_partner_facility_dtl as opfd', 'opfd.Partner_ID', '=', 'op.id')
            ->join('master_facility as mf', 'mf.id', '=', 'opfd.Facility_ID')
            ->where('op.id', '=', $q['opportunity_id'])
            ->whereBetween('opfd.Facility_ID', [1, 27])
            ->groupBy('op.full_name', 'op.package_option');

        $ungrouped = DB::table('opportunity_partner as op')
            ->select(
                'op.full_name',
                DB::raw('COALESCE(mf.Description, opfd.Description) as Description'),
                'opfd.Total',
                'opfd.Facility_ID',
                'opfd.ID'
            )
            ->join('opportunity_partner_facility_dtl as opfd', 'opfd.Partner_ID', '=', 'op.id')
            ->leftJoin('master_facility as mf', 'mf.id', '=', 'opfd.Facility_ID')
            ->where('op.id', '=', $q['opportunity_id'])
            // ->where('opfd.Facility_ID', '>', 27)
            ->whereRaw('opfd.Facility_ID IS NULL'); // null reserved buat yang gk ada di master facilty


        $d = $grouped->unionAll($ungrouped)->get();


        // $d = DB::table('capital_expenditure')->select('*')->get();
        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('id', $counter);
            $row->addAttribute('db_id', $p->ID ?? '');
            $row->addChild('cell', $counter);
            $row->addChild('cell', htmlspecialchars($p->description));
            $row->addChild('cell', $p->Total);
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    // public function getCapitalExpenditureData(Request $r)
    // {
    //     $b = $r->all();
    //     $q = $r->query();

    //     $d = DB::table('capital_expenditure as ce')
    //         ->join('opportunity_partner as op', 'op.id', '=', 'ce.Opportunity_Id')
    //         ->select('ce.Asset_Name', 'ce.Cost')
    //         ->where('ce.Opportunity_Id', $b['opportunity_id'])
    //         ->get();

    //     $xml = new SimpleXMLElement('<rows></rows>');

    //     $counter = 1;
    //     foreach ($d as $p) {
    //         $row = $xml->addChild('row');
    //         $row->addAttribute('ID', $counter);
    //         $row->addChild('cell', $counter);
    //         $row->addChild('cell', htmlspecialchars($p->Asset_Name));
    //         $row->addChild('cell', $p->Cost);
    //         $counter++;
    //     }

    //     return response()->make($xml->asXML(), 200, [
    //         'Content-Type' => 'application/xml'
    //     ]);
    // }

    public function createCapitalExpenditure()
    {
        return view('partner.capex.create');
    }


    public function getCapexDetail(Request $r)
    {
        $data = DB::table('opportunity_partner_facility_dtl')->where('ID', $r->id)->first();
        return response()->json(['data' => $data]);
    }

    public function editCapitalExpenditure(Request $r)
    {
        $b = $r->all();
        DB::table('opportunity_partner_facility_dtl')->where('ID', $b['id'])->update([
            'Description' => $b['Description'] ?? '',
            'Total'       => $b['Total'] ?? 0,
            'Qty'         => $b['Qty'] ?? 0,
            'Price'       => $b['Price'] ?? 0,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Updated']);
    }

    public function submitCapitalExpenditure(Request $r)
    {
        $b = $r->all();

        $session = $this->userService->getCurrentSessionUserInfo();
        $wh = DB::table('opportunity_partner as op')
            ->join('warehouse_partner as wp', 'wp.partner_task_id', '=', 'op.lead_id')
            ->select('wp.warehouse_task_id')
            ->where('op.id', '=', $b['opportunity_id'])->get();


        $ins = DB::table('opportunity_partner_facility_dtl')->insertGetId([
            'Partner_ID'             => $b['opportunity_id'],
            'warehouse_task_id'      => $wh[0]->warehouse_task_id,
            'Opportunity_Facility_ID' => 0,
            'Facility_ID'            => null,
            'Qty'                    => $b['Qty'],
            'Price'                  => $b['Price'],
            'Total'                  => $b['Total'],
            'Order_Date'             => now(),
            'Created_By'             => $session->username,
            'Created_Date'           => now(),
            'Description'            => $b['Description'],
        ]);

        return response()->json(['message' => 'success']);
    }

    public function deleteCapitalExpenditure(Request $r)
    {
        $id = $r->input('id');
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        DB::table('opportunity_partner_facility_dtl')->where('ID', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Deleted successfully']);
    }
}
