<?php

namespace app\Htpp\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use SimpleXMLIterator;

class ExpenditureController extends Controller
{
    public function __construct()
    {
        throw new \Exception('Not implemented');
    }

    public function indexCapitalExpenditure()
    {
        return view('partner.capex.index');
    }

    public function getCapitalExpenditureData(Request $r)
    {
        $d = DB::table('capital_expenditure')->select('*')->get();
        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->ID);
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->AssetName);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', $p->Cost);
            $row->addChild('cell', $p->Purchased_At);
            $row->addChild('cell', $p->Estimated_Life_Years);
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

    public function submitCapitalExpenditure(Request $r)
    {
        $b = $r->all();

        $ins = DB::table('capital_expenditure')->insertGetId([
            'Asset_Name' => $b['Asset_Name'],
            'Cost' => $b['Cost'],
            'Status' => 'ACTIVE',
            'Hub_Type' => $b['Hub_Type']
        ]);

        return response()->json(['message' => 'success']);
    }
}
