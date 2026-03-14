<?php

namespace app\Htpp\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class SurveyController extends Controller
{
    public function __construct()
    {
        throw new \Exception('Not implemented');
    }

    public function indexSurvey()
    {
        return view('partner.survey.index');
    }

    public function createMasterDataSurvey()
    {
        return view('partner.survey.create');
    }

    public function getMasterDataSurvey(Request $r)
    {
        $d = DB::table('survey_location')->select('*')->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $no = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->ID);
            $row->addChild('cell', $no);
            $row->addChild('cell', $p->Province);
            $row->addChild('cell', $p->City);
            $row->addChild('cell', $p->District);
            $row->addChild('cell', $p->Subdistrict);
            $row->addChild('cell', $p->Address);
            $row->addChild('cell', $p->Location_Name);
            $row->addChild('cell', $p->Latitude);
            $row->addChild('cell', $p->Longitude);
            $row->addChild('cell', $p->Recommendation_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', $p->Created_At);

            $no++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    /**TODO :
     * Read value dari template form survey, menunggu form 
     */
    public function submitMasterDataSurvey(Request $r)
    {
        $b = $r->all();
        return response()->json(['message' => 'success']);
    }
};
