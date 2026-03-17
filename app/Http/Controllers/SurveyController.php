<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class SurveyController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $up)
    {
        $this->uploadService = $up;
    }

    public function indexSurvey()
    {
        return view('partner.survey.index');
    }

    public function indexApprovalSurvey()
    {
        return view('partner.survey.approval-index');
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
            $row->addChild('cell', $p->Location_Name);
            $row->addChild('cell', $p->Recommendation_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', $p->Surveyed_At);
            $row->addChild('cell', $p->Province);
            $row->addChild('cell', $p->City);
            $row->addChild('cell', $p->District);
            $row->addChild('cell', $p->Subdistrict);
            $row->addChild('cell', $p->Address);
            $row->addChild('cell', $p->Latitude);
            $row->addChild('cell', $p->Longitude);

            $no++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function submitMasterDataSurvey(Request $r)
    {
        $b = $r->all();
        $files = $r->files->all();

        $frontPic = $r->file('front_facing');
        $operationalPic = $r->file('operational');
        $vehicleAccessPic = $r->file('vehicle_access');
        $environtmentPic = $r->file('environtment');

        // dd($r->files->all());


        $id = DB::table('survey_location')->insertGetId([
            "Province" => $b['Province'] ?? "",
            "City" => $b['City'],
            "District" => $b['District'],
            "Subdistrict" => $b['Subdistrict'],
            "Address" => $b['Address'],
            "Location_Name" => $b['Location_Name'] ?? "",
            "Latitude" => $b['Latitude'],
            "Longitude" => $b['Longitude'],
            "Recommendation_Type" => $b['Recommendation_Type'] ?? "INTERNAL",
            "Status" => $b['Status'] ?? "ACTIVE",
            "Surveyor_Name" => $b['Surveyor_Name'],
            "Location_Owner_Name" => $b['Location_Owner_Name'],
            "Area" => $b['Area'],
            "Location_Type" => $b['Location_Type'],
            "C_c1" =>   $b["C_c1"],
            "C_c2" =>   $b["C_c2"],
            "C_c3" =>   $b["C_c3"],
            "C_c4" =>   $b["C_c4"],
            "D_c1" =>   $b["D_c1"],
            "D_c2" =>   $b["D_c2"],
            "D_c3" =>   $b["D_c3"],
            "D_c4" =>   $b["D_c4"],
            "D_c5" =>   $b["D_c5"],
            "D_c6" =>   $b["D_c6"],
            "D_c7" =>   $b["D_c7"],
            "D_c8" =>   $b["D_c8"],
            "D_c9" =>   $b["D_c9"],
            "D_c1_Notes" => $b['D_c1_Notes'],
            "D_c2_Notes" => $b['D_c2_Notes'],
            "D_c3_Notes" => $b['D_c3_Notes'],
            "D_c4_Notes" => $b['D_c4_Notes'],
            "D_c5_Notes" => $b['D_c5_Notes'],
            "D_c6_Notes" => $b['D_c6_Notes'],
            "D_c7_Notes" => $b['D_c7_Notes'],
            "D_c8_Notes" => $b['D_c8_Notes'],
            "D_c9_Notes" => $b['D_c9_Notes'],
            "F_p1_col1" =>   $b["F_p1_col1"],
            "F_p1_col2" =>   $b["F_p1_col2"],
            "F_p2_col1" =>   $b["F_p2_col1"],
            "F_p2_col2" =>   $b["F_p2_col2"],
            "F_p3_col1" =>   $b["F_p3_col1"],
            "F_p3_col2" =>   $b["F_p3_col2"],
            "F_p4_col1" =>   $b["F_p4_col1"],
            "F_p4_col2" =>   $b["F_p4_col2"],
            "G_p1" =>   $b["G_p1"] ?? "",
            "G_p2" =>   $b["G_p2"] ?? "",
            "G_p3" =>   $b["G_p3"] ?? "",
            "H_p1_col1" =>   $b["H_p1_col1"] ?? "",
            "H_p1_col2" =>   $b["H_p1_col2"] ?? "",
            "Approval_Surveyor" =>   $b["Approval_Surveyor"] ?? 1,
            "Approval_Manager" =>   $b["Approval_Manager"] ?? 0,
            "Approval_COO" =>   $b["Approval_COO"] ?? 0,
        ]);

        $uploadedFiles = [];

        $fileInputs = ['C_c1_File', 'C_c2_File', 'C_c3_File', 'C_c4_File'];

        foreach ($fileInputs as $inputName) {
            if ($r->hasFile($inputName) && $r->file($inputName)->isValid()) {
                $file = $r->file($inputName);
                $path = $file->store("uploads/{$inputName}", 'public');
                $uploadedFiles[$inputName] = $path;
            }
        }

        $path = "";
        $filename = "";
        $desc = "foto tampak depan";
        $ext = "";
        $topic = "survey";
        $mapTo = null;

        $this->uploadService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        return response()->json(['message' => 'success']);
    }
};
