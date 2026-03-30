<?php

namespace app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use SimpleXMLIterator;

class ExpenditureController extends Controller
{
    public function __construct()
    {
        // throw new \Exception('Not implemented');
    }

    public function indexCapitalExpenditure()
    {
        return view('partner.capex.index');
    }



    // public function getCapitalExpenditureData(Request $r)
    // {
    //     $b = $r->all();
    //     $q = $r->query();

    //     // opportunity_partner_facility_dtl <- opportunity_partner_facility -> master_facility
    //     $d = DB::table('opportunity_partner as op')->select('op.full_name', 'opfd.*', 'mf.description')
    //         ->join('opportunity_partner_facility_dtl as opfd', 'opfd.Partner_ID', '=', 'op.id')
    //         ->join('master_facility as mf', 'mf.id', '=', 'opfd.Facility_ID')
    //         ->where('op.id', '=', $q['opportunity_id']);

    //     $d = $d->get();
    //     // dd($d);

    //     // $d = DB::table('capital_expenditure')->select('*')->get();
    //     $xml = new SimpleXMLElement('<rows></rows>');

    //     $counter = 1;
    //     foreach ($d as $p) {
    //         $row = $xml->addChild('row');
    //         $row->addAttribute('ID', $p->ID);
    //         $row->addChild('cell', $counter);
    //         $row->addChild('cell', $p->full_name);
    //         $row->addChild('cell', htmlspecialchars($p->description));
    //         $row->addChild('cell', $p->Qty);
    //         $row->addChild('cell', $p->Total);
    //         $counter++;
    //     }

    //     return response()->make($xml->asXML(), 200, [
    //         'Content-Type' => 'application/xml'
    //     ]);
    // }

    public function getCapitalExpenditureData(Request $r)
    {
        $b = $r->all();
        $q = $r->query();

        $d = DB::table('capital_expenditure as ce')
            ->join('opportunity_partner as op', 'op.id', '=', 'ce.Opportunity_Id')
            ->select('ce.Asset_Name', 'ce.Cost')
            ->where('ce.Opportunity_Id', $b['opportunity_id'])
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $counter);
            $row->addChild('cell', $counter);
            $row->addChild('cell', htmlspecialchars($p->Asset_Name));
            $row->addChild('cell', $p->Cost);
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function createCapitalExpenditure()
    {
        return view('partner.capex.create');
    }

    public function editCapitalExpenditure(Request $r)
    {

        dd($r);
        return response()->json(['message' => 'success']);
    }

    public function submitCapitalExpenditure(Request $r)
    {
        $b = $r->all();

        $ins = DB::table('capital_expenditure')->insertGetId([
            'Opportunity_Id' => $b['opportunity_id'],
            'Asset_Name' => $b['Asset_Name'],
            'Cost' => $b['Cost'],
            'Status' => 'ACTIVE',
            // 'Hub_Type' => $b['Hub_Type']
        ]);

        return response()->json(['message' => 'success']);
    }
}
