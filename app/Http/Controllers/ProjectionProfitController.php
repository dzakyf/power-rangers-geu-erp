<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ProjectionProfitController extends Controller
{
    public function __construct() {}

    public function index()
    {
        return view('projection.index');
    }

    public function downloadExpenditureTemplate() {}

    public function downloadProjectionTemplate() {}

    public function indexExpenditure()
    {
        return view('expenditure.index');
    }

    public function createExpenditure()
    {
        throw new Exception("Not implemented");
    }

    public function createProjection()
    {
        return view('projection.create');
    }

    public function submitExpenditure()
    {
        throw new Exception("Not implemented");
    }

    public function uploadProjection(Request $r) {}

    public function submitProjection(Request $r)
    {
        $b = $r->all();

        $ins = DB::table('projections')->insertGetId([
            "User_Id" => $b['user_id'],
            "Hub_Type" => $b['hub_type'],
            "Description" => $b['description'],
            "Standard_Value" => $b['standard_value'],
            "Offset" => $b['offset'],
            "Year" => $b['year'],
            "Projection_Type" => $b['projection_type'],
            "Month_1_A" => $b['month_1_a'],
            "Month_1_B" => $b['month_1_b'],
            "Month_1_Offset" => $b['month_1_offset'],
            "Month_1_Total" => $b['month_1_total'],
            "Month_2_A" => $b['month_2_a'],
            "Month_2_B" => $b['month_2_b'],
            "Month_2_Total" => $b['month_2_total'],
            "Month_2_Offset" => $b['month_2_offset'],
            "Month_3_A" => $b['month_3_a'],
            "Month_3_B" => $b['month_3_b'],
            "Month_3_Total" => $b['month_3_total'],
            "Month_3_Offset" => $b['month_3_offset'],
            "Month_4_A" => $b['month_4_a'],
            "Month_4_B" => $b['month_4_b'],
            "Month_4_Total" => $b['month_4_total'],
            "Month_4_Offset" => $b['month_4_offset'],
            "Month_5_A" => $b['month_5_a'],
            "Month_5_B" => $b['month_5_b'],
            "Month_5_Total" => $b['month_5_total'],
            "Month_5_Offset" => $b['month_5_offset'],
            "Month_6_A" => $b['month_6_a'],
            "Month_6_B" => $b['month_6_b'],
            "Month_6_Total" => $b['month_6_total'],
            "Month_6_Offset" => $b['month_6_offset'],
            "Month_7_A" => $b['month_7_a'],
            "Month_7_B" => $b['month_7_b'],
            "Month_7_Total" => $b['month_7_total'],
            "Month_7_Offset" => $b['month_7_offset'],
            "Month_8_A" => $b['month_8_a'],
            "Month_8_B" => $b['month_8_b'],
            "Month_8_Total" => $b['month_8_total'],
            "Month_8_Offset" => $b['month_8_offset'],
            "Month_9_A" => $b['month_9_a'],
            "Month_9_B" => $b['month_9_b'],
            "Month_9_Total" => $b['month_9_total'],
            "Month_9_Offset" => $b['month_9_offset'],
            "Month_10_A" => $b['month_10_a'],
            "Month_10_B" => $b['month_10_b'],
            "Month_10_Total" => $b['month_10_total'],
            "Month_10_Offset" => $b['month_10_offset'],
            "Month_11_A" => $b['month_11_a'],
            "Month_11_B" => $b['month_11_b'],
            "Month_11_Total" => $b['month_11_total'],
            "Month_11_Offset" => $b['month_11_offset'],
            "Month_12_A" => $b['month_12_a'],
            "Month_12_B" => $b['month_12_b'],
            "Month_12_Total" => $b['month_12_total'],
            "Month_12_Offset" => $b['month_12_offset'],
        ]);

        return response()->json([
            "message" => "success"
        ], 200);
    }

    public function showProjection() {}

    // ?user_id=&hub_type=&projection_type=&year=
    public function projectionData(Request $r)
    {
        $q = $r->query();
        $userId = $q['user_id'];
        $projectionType = $q['projection_type'];
        $hubType = $q['hub_type'];
        $year = $q['year'];

        $proj = DB::table('projections')->select('*')->where('User_Id', '=', $userId)->where('Projection_Type', '=', $projectionType)->where('Hub_Type', '=', $hubType)->where('Year', '=', $year)->get();


        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($proj as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->ID);
            $row->addChild('cell', $p->Description);
            $row->addChild('cell', $p->Standard_Value);
            $row->addChild('cell', $p->Offset);
            $row->addChild('cell', $p->Year);

            for ($month = 1; $month <= 12; $month++) {
                $rowA = "Month_{$month}_A";
                $rowB = "Month_{$month}_B";
                $rowTotal = "Month_{$month}_Total";
                $rowOffset = "Month_{$month}_Offset";

                $row->addChild('cell', $p->$rowA);
                $row->addChild('cell', $p->$rowB);
                $row->addChild('cell', $p->$rowTotal);
                $row->addChild('cell', $p->$rowOffset);
            }
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function showExpenditure() {}

    // ?user_id=&hub_type=&projection_type=&year=
    public function expenditureData(Request $r)
    {
        $q = $r->query();
        $userId = $q['user_id'];
        $projectionType = $q['projection_type'];
        $hubType = $q['hub_type'];
        $year = $q['year'];

        $exp = DB::table('expenditure')->select('*')->where('User_Id', '=', $userId)->where('Projection_Type', '=', $projectionType)->where('Hub_Type', '=', $hubType)->where('Year', '=', $year)->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($exp as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->ID);
            $row->addChild('cell', $p->Description);
            $row->addChild('cell', $p->Standard_Value);
            $row->addChild('cell', $p->Offset);
            $row->addChild('cell', $p->Year);

            for ($month = 1; $month <= 12; $month++) {
                $rowTotal = "Month_{$month}_Total";

                $row->addChild('cell', $p->$rowTotal);
            }
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
