<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
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

    public function uploadProjection(Request $r)
    {
        $b = $r->all();
        $type = $b['type'];
        $year = $b['year'];

        $file = $r->file('file');
        $path = $file->storeAs('temp', 'upload_' . time() . '.' . $file->getClientOriginalExtension());
        $fullPath = Storage::path($path);
        $spreadsheet = IOFactory::load($fullPath);

        // load sheet, table name
        $sheet = $spreadsheet->getSheetByName('high-projection');

        // // expenditure
        $rowIterator = $sheet->getRowIterator(2, 17);
        $data = [];
        foreach ($rowIterator as $row) {
            $columnIterator = $row->getCellIterator();
            $columnIterator->setIfNotExists(CellIterator::IF_NOT_EXISTS_RETURN_NULL);
            $rowData = [];
            foreach ($columnIterator as $cell) {
                if ($cell !== null) {
                    $value = $cell->getCalculatedValue();

                    // $coordinate = $cell->getCoordinate();
                    $rowData[] = $value;
                }
            }
            $data[] = $rowData;
        }

        for ($i = 2; $i < count($data); $i++) {
            $b = $data[$i];

            $insExp = DB::table('expenditure')->insertGetId([
                "User_Id" => '2345',
                "Hub_Type" => "booth",
                "Description" => $b[0] ?? "",
                "Short_Desc" => $b[1],
                // "Standard_Value" => $b['standard_value'],
                "Offset" => $b[2],
                "Year" => $year,
                "Projection_Type" => $type,
                "Month_1_Total" => $b[3],
                "Month_2_Total" => $b[4],
                "Month_3_Total" => $b[5],
                "Month_4_Total" => $b[6],
                "Month_5_Total" => $b[7],
                "Month_6_Total" => $b[8],
                "Month_7_Total" => $b[9],
                "Month_8_Total" => $b[10],
                "Month_9_Total" => $b[11],
                "Month_10_Total" => $b[12],
                "Month_11_Total" => $b[13],
                "Month_12_Total" => $b[14],
            ]);
        }

        // projection
        $projRowIterator = $sheet->getRowIterator(26, 60);
        $projData = [];
        foreach ($projRowIterator as $row) {
            $columnIterator = $row->getCellIterator();
            $columnIterator->setIfNotExists(CellIterator::IF_NOT_EXISTS_RETURN_NULL);
            $rowData = [];

            $firstCell = $columnIterator->current();
            $firstValue = $firstCell ? $firstCell->getValue() : null;

            if ($firstValue === null || trim($firstValue) === '') {
                continue;
            }

            foreach ($columnIterator as $cell) {
                if ($cell !== null) {
                    $value = $cell->getCalculatedValue();

                    // $coordinate = $cell->getCoordinate();
                    $rowData[] = $value;
                }
            }
            $projData[] = $rowData;
        }
        // dd($projData);

        for ($i = 0; $i < count($projData); $i++) {
            $b = $projData[$i];

            $ins = DB::table('projections')->insertGetId([
                "User_Id" => '2345',
                "Hub_Type" => 'booth',
                "Description" => $b[0] ?? "",
                "Standard_Value" => $b[1],
                "Offset" => $b[2],
                "Year" => $year,
                "Projection_Type" => $type,
                "Month_1_A" => $b[3],
                "Month_1_B" => $b[4],
                "Month_1_Offset" => $b[5],
                "Month_1_Total" => $b[6],
                "Month_2_A" => $b[7],
                "Month_2_B" => $b[8],
                "Month_2_Total" => $b[9],
                "Month_2_Offset" => $b[10],
                "Month_3_A" => $b[11],
                "Month_3_B" => $b[12],
                "Month_3_Total" => $b[13],
                "Month_3_Offset" => $b[14],
                "Month_4_A" => $b[15],
                "Month_4_B" => $b[16],
                "Month_4_Total" => $b[17],
                "Month_4_Offset" => $b[18],
                "Month_5_A" => $b[19],
                "Month_5_B" => $b[20],
                "Month_5_Total" => $b[21],
                "Month_5_Offset" => $b[22],
                "Month_6_A" => $b[23],
                "Month_6_B" => $b[24],
                "Month_6_Total" => $b[25],
                "Month_6_Offset" => $b[26],
                "Month_7_A" => $b[27],
                "Month_7_B" => $b[28],
                "Month_7_Total" => $b[29],
                "Month_7_Offset" => $b[30],
                "Month_8_A" => $b[31],
                "Month_8_B" => $b[32],
                "Month_8_Total" => $b[33],
                "Month_8_Offset" => $b[34],
                "Month_9_A" => $b[35],
                "Month_9_B" => $b[36],
                "Month_9_Total" => $b[37],
                "Month_9_Offset" => $b[38],
                "Month_10_A" => $b[39],
                "Month_10_B" => $b[40],
                "Month_10_Total" => $b[41],
                "Month_10_Offset" => $b[42],
                "Month_11_A" => $b[43],
                "Month_11_B" => $b[44],
                "Month_11_Total" => $b[45],
                "Month_11_Offset" => $b[46],
                "Month_12_A" => $b[47],
                "Month_12_B" => $b[48],
                "Month_12_Total" => $b[49],
                "Month_12_Offset" => $b[50],
            ]);
        }

        return response()->json([
            "message" => "success"
        ]);
    }

    public function submitProjection(Request $r)
    {
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
            $row->addChild('cell', htmlspecialchars($p->Description));
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
        $year = '%';

        $exp = DB::table('expenditure')->select('*')->where('User_Id', '=', $userId)->where('Projection_Type', '=', $projectionType)->where('Hub_Type', '=', $hubType)->where('Year', 'LIKE', $year)->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($exp as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->ID);
            $row->addChild('cell', htmlspecialchars($p->Description));
            $row->addChild('cell', $p->Short_Desc);
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
