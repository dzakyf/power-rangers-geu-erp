<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class SurveyController extends Controller
{
    protected $uploadService;
    protected $userService;
    protected $generatorService;
    protected ApprovalController $approvalController;

    public function __construct(GeneratorService $g, UploadService $up, UserService $us, ApprovalController $ac)
    {
        $this->uploadService = $up;
        $this->userService = $us;
        $this->generatorService = $g;
        $this->approvalController = $ac;
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

    public function dashboardSurvey()
    {
        // butuh data : 
        // - monitoring proses pencarian lokasi
        // - target kota 
        // - jumlah lokasi disurvey 
        // - jumlah hub disetujui 
        // - aging survey  >30 hari 

        $statistic = DB::table('survey_location as sl')
            ->select([
                DB::raw('COUNT(*) as Total_All'),
                DB::raw("SUM(CASE WHEN sl.status = 'DONE_SURVEY' THEN 1 ELSE 0 END) as Total_Done_Survey"),
                DB::raw('SUM(CASE WHEN DATEDIFF(CURDATE(), sl.Surveyed_At) > 90 THEN 1 ELSE 0 END) as Total_Expired'),
            ])
            ->first();

        $locationCounter = DB::table('survey_location')
            ->select(['City', DB::raw('COUNT(*) as Total')])
            ->groupBy('City')
            ->orderBy('Total', 'Desc')
            ->get();

        $locationCounterWStatus = DB::table('survey_location')
            ->select([
                'City',
                DB::raw('COUNT(*) as Total'),
                'Status',
                DB::raw('DATEDIFF(CURDATE(), Surveyed_At) as Days_Since_Created')
            ])
            ->groupBy('City')
            ->orderBy('Total', 'Desc')
            ->get();

        $newestEntry = DB::table('survey_location')
            ->select([
                DB::raw("CONCAT('Alternatif', ' : ', Address, ', ',City) as Alternative"),
            ])
            ->orderBy('ID', 'desc')
            ->limit(4)
            ->get();

        // dd($locationCounterWStatus, $newestEntry);


        return view('partner.survey.dashboard', compact('statistic', 'locationCounter', 'locationCounterWStatus', 'newestEntry'));
    }


    public function getMasterDataSurvey(Request $r)
    {
        if ($r->query('format') === 'json') {
            return $this->getMasterDataSurveyJson($r);
        }

        return $this->getMasterDataSurveyXml($r);
    }

    private function getMasterDataSurveyXml(Request $r)
    {
        $query = DB::table('survey_location')->select('*');

        if ($id = $r->query('id')) {
            $query->where('ID', $id);
        }

        $d = $query->get();

        $xml = new SimpleXMLElement('<rows></rows>');
        $no  = 1;

        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->ID);
            $row->addChild('cell', $no);
            $row->addChild('cell', $p->Location_Name);
            $row->addChild('cell', $p->Recommendation_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', $p->Surveyed_At);
            $row->addChild('cell', $p->Total_Score);
            $row->addChild('cell', $this->resolveRecommendation($p->Total_Score));
            $no++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function getMasterDataSurveyJson(Request $r)
    {
        $query = DB::table('survey_location')->select('*');

        if ($id = $r->query('id')) {
            $query->where('ID', $id);
        }

        $d = $query->get()->map(function ($p) {
            $p->Recommendation = $this->resolveRecommendation($p->Total_Score);
            return $p;
        });

        return response()->json(['data' => $d]);
    }

    private function resolveRecommendation(int $score): string
    {
        if ($score >= 80) return 'Layak';
        if ($score >= 70) return 'Layak Bersyarat';
        return 'Tidak Layak';
    }

    public function getSurveyTotalScore(Request $r)
    {
        return response()->json(['message' => 'success']);
    }

    public function submitMasterDataSurvey(Request $r)
    {
        $b = $r->all();
        $files = $r->files->all();
        // dd($b);

        // dd($r->files->all());

        $photo = 0; //section C
        $checklist = 0; // section D
        $environtment = 0; // Section E
        $ucoEstimation = 0; // Section F

        // section c
        for ($i = 1; $i <= 4; $i++) {
            $photo += $b["C_c$i"] * 2.5;
        }

        // section D
        for ($j = 1; $j <= 9; $j++) {
            $count = $b["D_c$j"];

            if ($count == 1 && $j !== 9) {
                $checklist += ($count * 6);
            } else if ($count == 1) {
                $checklist += ($count * 7);
            }
        }

        // Section E
        for ($k = 1; $k <= 4; $k++) {
            $input = $b["E_p$k"];

            if ($k == 1) {

                if ($input == 1) {
                    $environtment += 1;
                }

                if ($input == 2) {
                    $environtment += 3;
                }

                if ($input == 3) {
                    $environtment += 5;
                }
            }

            if ($k == 2) {

                if ($input == 1) {
                    $environtment += 1;
                }

                if ($input == 2) {
                    $environtment += 3;
                }

                if ($input == 3) {
                    $environtment += 5;
                }
            }

            if ($k == 3) {

                if ($input == 1) {
                    $environtment += 1;
                }

                if ($input == 2) {
                    $environtment += 3;
                }

                if ($input == 3) {
                    $environtment += 5;
                }
            }

            if ($k == 4) {

                if ($input == 1) {
                    $environtment += 1;
                }

                if ($input == 2) {
                    $environtment += 3;
                }

                if ($input == 3) {
                    $environtment += 5;
                }
            }
        }

        // section F
        $ton = $b["F_p5_col2"];


        if ($ton > 15) {
            $ucoEstimation += 15;
        }

        if ($ton >= 10 && $ton <= 15) {
            $ucoEstimation += 12;
        }

        if ($ton >= 6 && $ton <= 10) {
            $ucoEstimation += 8;
        }


        if ($ton >= 4 && $ton <= 6) {
            $ucoEstimation += 5;
        }


        if ($ton < 4) {
            $ucoEstimation += 2;
        }

        $totalScore = $photo + $checklist + $environtment + $ucoEstimation;

        $highest = DB::table('survey_location as sl')->max('ID');
        $applicatonCode = $this->generatorService->generateApplicationCode($highest, 'SVY');

        try {
            $surveyId = DB::transaction(function () use ($b, $applicatonCode, $photo, $checklist, $environtment, $ucoEstimation, $totalScore, $r) {
                $surveyId = DB::table('survey_location')->insertGetId([
                    "Code"                => $applicatonCode,
                    "Province"            => $b['Province'] ?? "",
                    "City"                => $b['City'],
                    "District"            => $b['District'],
                    "Subdistrict"         => $b['Subdistrict'],
                    "Address"             => $b['Address'],
                    "Location_Name"       => $b['Location_Name'] ?? "",
                    "Latitude"            => $b['Latitude'],
                    "Longitude"           => $b['Longitude'],
                    "Recommendation_Type" => $b['Recommendation_Type'] ?? "INTERNAL",
                    "Status"              => "NEED_APPROVAL",
                    "Surveyor_Name"       => $b['Surveyor_Name'],
                    "Location_Owner_Name" => $b['Location_Owner_Name'],
                    "Area"                => $b['Area'],
                    "Location_Type"       => $b['Location_Type'],
                    "C_c1"                => $b["C_c1"],
                    "C_c2"                => $b["C_c2"],
                    "C_c3"                => $b["C_c3"],
                    "C_c4"                => $b["C_c4"],
                    "C_Total_Score"       => $photo,
                    "D_c1"                => $b["D_c1"],
                    "D_c2"                => $b["D_c2"],
                    "D_c3"                => $b["D_c3"],
                    "D_c4"                => $b["D_c4"],
                    "D_c5"                => $b["D_c5"],
                    "D_c6"                => $b["D_c6"],
                    "D_c7"                => $b["D_c7"],
                    "D_c8"                => $b["D_c8"],
                    "D_c9"                => $b["D_c9"],
                    "D_Total_Score"       => $checklist,
                    "D_c1_Notes"          => $b['D_c1_Notes'],
                    "D_c2_Notes"          => $b['D_c2_Notes'],
                    "D_c3_Notes"          => $b['D_c3_Notes'],
                    "D_c4_Notes"          => $b['D_c4_Notes'],
                    "D_c5_Notes"          => $b['D_c5_Notes'],
                    "D_c6_Notes"          => $b['D_c6_Notes'],
                    "D_c7_Notes"          => $b['D_c7_Notes'],
                    "D_c8_Notes"          => $b['D_c8_Notes'],
                    "D_c9_Notes"          => $b['D_c9_Notes'],
                    "E_p1"                => $b["E_p1"],
                    "E_p2"                => $b["E_p2"],
                    "E_p3"                => $b["E_p3"],
                    "E_p4"                => $b["E_p4"],
                    "E_Total_Score"       => $environtment,
                    "F_p1_col1"           => $b["F_p1_col1"],
                    "F_p1_col2"           => $b["F_p1_col2"],
                    "F_p2_col1"           => $b["F_p2_col1"],
                    "F_p2_col2"           => $b["F_p2_col2"],
                    "F_p3_col1"           => $b["F_p3_col1"],
                    "F_p3_col2"           => $b["F_p3_col2"],
                    "F_p4_col1"           => $b["F_p4_col1"],
                    "F_p4_col2"           => $b["F_p4_col2"],
                    "F_Total_Score"       => $ucoEstimation,
                    "G_p1"                => $b["G_p1"] ?? "",
                    "G_p2"                => $b["G_p2"] ?? "",
                    "G_p3"                => $b["G_p3"] ?? "",
                    "Total_Score"         => $totalScore,
                    "Approval_Surveyor"   => $b["Approval_Surveyor"] ?? 1,
                    "Approval_Manager"    => $b["Approval_Manager"] ?? 0,
                    "Approval_COO"        => $b["Approval_COO"] ?? 0,
                ]);

                $level   = DB::table('approval_level')->where('Level_Number', 1)->where('Request_Type', 'SURVEY')->first();
                $session = $this->userService->getCurrentSessionUserInfo();
                $action  = $session->Position === 'BOD' ? 'APPROVED' : 'NEED_APPROVAL';

                $this->approvalController->createApprovalRequest(
                    'SURVEY',
                    $session->id,
                    $action,
                    [
                        'Current_Level' => 1,
                        'Level_Id'      => $level->Next_Level_Id,
                        'Map_To'        => $surveyId,
                    ]
                );

                $fileInputs = ['C_c1_File', 'C_c2_File', 'C_c3_File', 'C_c4_File'];

                foreach ($fileInputs as $inputName) {
                    if ($r->hasFile($inputName) && $r->file($inputName)->isValid()) {
                        $file     = $r->file($inputName);
                        $year     = Carbon::now()->year;
                        $month    = Carbon::now()->format('m');
                        $path     = $file->store("$year/$month", 'public');
                        $ext      = $file->getClientOriginalExtension();

                        $this->uploadService->uploadMetaFile($path, basename($path), $inputName, $ext, 'survey', $surveyId);
                    }
                }

                return $surveyId;
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }

        $analysisResult = "Tidak Diketahui";

        if ($totalScore >= 80 && $totalScore <= 100) {
            $analysisResult = "Layak";
        }

        if ($totalScore >= 70 && $totalScore <= 79) {
            $analysisResult = "Layak Bersyarat";
        }

        if ($totalScore < 70) {
            $analysisResult = "Tidak Layak";
        }


        return response()->json(['message' => 'success', 'data' => [
            // 'survey_id' => $id,
            'C_Total_Score' => $photo,
            'D_Total_Score' => $checklist,
            'E_Total_Score' => $environtment,
            'F_Total_Score' => $ucoEstimation,
            'Total_Score' => $totalScore,
            'Analysis_Result' => $analysisResult
        ]]);
    }

    public function getSurvey(Request $r)
    {
        return;
    }
};
