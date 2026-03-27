<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ComplianceController extends Controller
{
    public function indexTax()
    {
        return view('partner.compliance.tax-index');
    }

    public function indexLegal()
    {
        return view('partner.compliance.legal-index');
    }

    public function getTaxComplianceData(Request $r)
    {
        $d = DB::table('config_tax as ct')->select('ct.*')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Type);
            $row->addChild('cell', $d->Object);
            $row->addChild('cell', $d->Percentile);
            $row->addChild('cell', $d->Base_Calculation);
            $row->addChild('cell', $d->In_Charge);
            $row->addChild('cell', $d->Description);
            $row->addChild('cell', $d->Hub_Type);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getLegalComplianceData(Request $r)
    {
        $d = DB::table('config_legal as cl')->select('cl.*')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $count = 1;
        foreach ($d as $d) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $d->ID);
            $row->addChild('cell', $count);
            $row->addChild('cell', $d->Document_Name);
            $row->addChild('cell', $d->Description);
            $count++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
