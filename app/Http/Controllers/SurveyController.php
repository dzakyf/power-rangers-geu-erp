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

    public function createReplyMasterDataSurvey()
    {
        return view('partner.survey.survey-reply');
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


    public function getSurveyHistoryData(Request $r)
    {
        try {
            $query = DB::table("survey_location as sl")
                ->leftJoin("opportunity_partner as opp", "sl.Opportunity_Partner_Id", "=", "opp.id")
                ->select("sl.*", "opp.lead_id as Opportunity_Code")
                ->orderBy("sl.Created_At", "DESC");

            if ($oppId = $r->query("opportunity_partner_id")) {
                $query->where("sl.Opportunity_Partner_Id", $oppId);
            }

            $d = $query->orderBy('ID', "DESC")->get();

            $xml = new SimpleXMLElement("<rows></rows>");
            $no = 1;

            foreach ($d as $p) {
                $row = $xml->addChild("row");
                $row->addAttribute("ID", $p->ID);
                $row->addChild("cell", $no);
                $row->addChild("cell", $p->Opportunity_Code);
                $row->addChild("cell", $p->Suggestion_Type ?? "ORIGINAL");
                $row->addChild("cell", $p->Recommendation_Type ?? "-");
                $row->addChild("cell", $p->City ?? "-");
                $row->addChild("cell", $p->District ?? "-");
                $row->addChild("cell", $this->resolveRecommendation((int) $p->Total_Score));
                $row->addChild("cell", $p->Created_At ?? $p->Surveyed_At);
                $no++;
            }

            return response()->make($xml->asXML(), 200, [
                "Content-Type" => "application/xml",
            ]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 500);
        }
    }

    private function getMasterDataSurveyXml(Request $r)
    {
        $query = DB::table('survey_location as sl')->leftJoin('opportunity_partner as opp', 'sl.Opportunity_Partner_Id', '=', 'opp.id')->select('sl.*', 'opp.lead_id as Opportunity_Code', 'opp.full_name as Partner_Name')->orderBy('sl.ID', 'DESC');

        if ($id = $r->query('id')) {
            $query->where('sl.ID', $id);
        }
        if ($st = $r->query("suggestion_type")) {
            $query->where("sl.Suggestion_Type", $st);
        }

        if ($oppId = $r->query("opportunity_partner_id")) {
            $query->where("sl.Opportunity_Partner_Id", $oppId);
        }

        $d = $query->get();

        $xml = new SimpleXMLElement('<rows></rows>');
        $no  = 1;

        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->ID);
            $row->addChild('cell', '');
            $row->addChild('cell', $no);
            $row->addChild('cell', $p->Location_Name);
            $row->addChild('cell', $p->Opportunity_Code);
            $row->addChild('cell', $p->Code);
            $row->addChild('cell', $p->Suggestion_Type);
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

        $query = DB::table('survey_location as sl')->leftJoin('opportunity_partner as opp', 'sl.Opportunity_Partner_Id', '=', 'opp.id')->select('sl.*', 'opp.lead_id as Opportunity_Code', 'opp.full_name as Partner_Name');

        if ($id = $r->query('id')) {
            $query->where('sl.ID', $id);
        }

        if ($st = $r->query("suggestion_type")) {
            $query->where("sl.Suggestion_Type", $st);
        }

        if ($oppId = $r->query("opportunity_partner_id")) {
            $query->where("sl.Opportunity_Partner_Id", $oppId);
        }

        $d = $query->get()->map(function ($p) {
            $p->Recommendation = $this->resolveRecommendation($p->Total_Score);
            return $p;
        });

        return response()->json(['data' => $d]);
    }

    private function resolveRecommendation(int $score = 0): string
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

        // The 3-times limit applies only when a MITRA inputs their location
        // through the survey form during onboarding approval. Internal
        // submissions via partner-survey-create are unlimited.
        $isPartner = optional(auth()->user())->Priv_ID === 'MITRA';

        $highest       = DB::table('survey_location as sl')->max('ID');
        $applicatonCode = $this->generatorService->generateApplicationCode($highest, 'SVY');

        if ($isPartner) {
            $count = DB::table('survey_location')
                ->where('Opportunity_Partner_Id', $b['Opportunity_Partner_Id'] ?? 0)
                ->count();

            if ($count >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maksimal 3 kali input survey untuk partner yang sama'
                ], 400);
            }
        }

        try {
                            $photo        = 0;
        $checklist    = 0;
        $environtment = 0;
        $ucoEstimation = 0;

        for ($i = 1; $i <= 4; $i++) {
            $photo += ($b["C_c$i"] ?? 0) * 2.5;
        }

        for ($j = 1; $j <= 9; $j++) {
            $count = ($b["D_c$j"] ?? 0);
            if ($count == 1 && $j !== 9) {
                $checklist += ($count * 6);
            } else if ($count == 1) {
                $checklist += ($count * 7);
            }
        }

        for ($k = 1; $k <= 4; $k++) {
            $input = ($b["E_p$k"] ?? 0);
            if ($input == 1) {
                $environtment += 1;
            } else if ($input == 2) {
                $environtment += 3;
            } else if ($input == 3) {
                $environtment += 5;
            }
        }

        $ton = ($b["F_p5_col2"] ?? 0);
        if ($ton > 15) {
            $ucoEstimation += 15;
        } else if ($ton >= 10) {
            $ucoEstimation += 12;
        } else if ($ton >= 6) {
            $ucoEstimation += 8;
        } else if ($ton >= 4) {
            $ucoEstimation += 5;
        } else {
            $ucoEstimation += 2;
        }

        $totalScore = $photo + $checklist + $environtment + $ucoEstimation;

        $parentId  = $b["Parent_Id"] ?? null;

        $surveyStatus = "NEED_APPROVAL";
        if (!$isPartner && $parentId) {
            $surveyStatus = "APPROVED";
        }

        $surveyId = DB::transaction(function () use ($b, $applicatonCode, $photo, $checklist, $environtment, $ucoEstimation, $totalScore, $surveyStatus) {
                return DB::table('survey_location')->insertGetId([
                    "Opportunity_Partner_Id" => $b['Opportunity_Partner_Id'] ?? "",
                    "Code"                => $applicatonCode,
                    "Surveyed_At"         => $b["Survey_At"] ?? now(),
                    "Parent_Id"          => $b["Parent_Id"] ?? null,
                    "Suggestion_Type"    => $b["Suggestion_Type"] ?? "ORIGINAL",
                    "Province"            => $b['Province'] ?? "",
                    "City"                => $b['City'] ?? '',
                    "District"            => $b['District'] ?? '',
                    "Subdistrict"         => $b['Subdistrict'] ?? '',
                    "Address"             => $b['Address'] ?? '',
                    "Location_Name"       => $b['Location_Name'] ?? "",
                    "Latitude"            => $b['Latitude'] ?? '',
                    "Longitude"           => $b['Longitude'] ?? '',
                    "Recommendation_Type" => $b['Recommendation_Type'] ?? "INTERNAL",
                    "Status"              => $surveyStatus,
                    "Surveyor_Name"       => $b['Surveyor_Name'] ?? '',
                    "Location_Owner_Name" => $b['Location_Owner_Name'] ?? '',
                    "Area"                => $b['Area'] ?? '',
                    "Location_Type"       => $b['Location_Type'] ?? '',
                    "C_c1"                => $b["C_c1"] ?? 0,
                    "C_c2"                => $b["C_c2"] ?? 0,
                    "C_c3"                => $b["C_c3"] ?? 0,
                    "C_c4"                => $b["C_c4"] ?? 0,
                    "C_Total_Score"       => $photo ?? 0,
                    "D_c1"                => $b["D_c1"] ?? 0,
                    "D_c2"                => $b["D_c2"] ?? 0,
                    "D_c3"                => $b["D_c3"] ?? 0,
                    "D_c4"                => $b["D_c4"] ?? 0,
                    "D_c5"                => $b["D_c5"] ?? 0,
                    "D_c6"                => $b["D_c6"] ?? 0,
                    "D_c7"                => $b["D_c7"] ?? 0,
                    "D_c8"                => $b["D_c8"] ?? 0,
                    "D_c9"                => $b["D_c9"] ?? 0,
                    "D_Total_Score"       => $checklist ?? 0,
                    "D_c1_Notes"          => $b['D_c1_Notes'] ?? '',
                    "D_c2_Notes"          => $b['D_c2_Notes'] ?? '',
                    "D_c3_Notes"          => $b['D_c3_Notes'] ?? '',
                    "D_c4_Notes"          => $b['D_c4_Notes'] ?? '',
                    "D_c5_Notes"          => $b['D_c5_Notes'] ?? '',
                    "D_c6_Notes"          => $b['D_c6_Notes'] ?? '',
                    "D_c7_Notes"          => $b['D_c7_Notes'] ?? '',
                    "D_c8_Notes"          => $b['D_c8_Notes'] ?? '',
                    "D_c9_Notes"          => $b['D_c9_Notes'] ?? '',
                    "E_p1"                => $b["E_p1"] ?? 0,
                    "E_p2"                => $b["E_p2"] ?? 0,
                    "E_p3"                => $b["E_p3"] ?? 0,
                    "E_p4"                => $b["E_p4"] ?? 0,
                    "E_Total_Score"       => $environtment ?? 0,
                    "F_p1_col1"           => $b["F_p1_col1"] ?? 0,
                    "F_p1_col2"           => $b["F_p1_col2"] ?? 0,
                    "F_p2_col1"           => $b["F_p2_col1"] ?? 0,
                    "F_p2_col2"           => $b["F_p2_col2"] ?? 0,
                    "F_p3_col1"           => $b["F_p3_col1"] ?? 0,
                    "F_p3_col2"           => $b["F_p3_col2"] ?? 0,
                    "F_p4_col1"           => $b["F_p4_col1"] ?? 0,
                    "F_p4_col2"           => $b["F_p4_col2"] ?? 0,
                    "F_Total_Score"       => $ucoEstimation ?? 0,
                    "G_p1"                => $b["G_p1"] ?? "",
                    "G_p2"                => $b["G_p2"] ?? "",
                    "G_p3"                => $b["G_p3"] ?? "",
                    "Total_Score"         => $totalScore ?? 0,
                    "Approval_Surveyor"   => $b["Approval_Surveyor"] ?? 1,
                    "Approval_Manager"    => $b["Approval_Manager"] ?? 0,
                    "Approval_COO"        => $b["Approval_COO"] ?? 0,
                ]);
            });

            $fileInputs = ['C_c1_File', 'C_c2_File', 'C_c3_File', 'C_c4_File'];

            foreach ($fileInputs as $inputName) {
                if ($r->hasFile($inputName) && $r->file($inputName)->isValid()) {
                    $file  = $r->file($inputName);
                    $year  = Carbon::now()->year;
                    $month = Carbon::now()->format('m');
                    $path  = $file->store("$year/$month", 'public');
                    $ext   = $file->getClientOriginalExtension();

                    $this->uploadService->uploadMetaFile($path, basename($path), $inputName, $ext, 'survey', $surveyId);
                }
            }

            if (!$isPartner && !$parentId) {
                $session = $this->userService->getCurrentSessionUserInfo();
                $this->approvalController->createApprovalRequest(
                    'SURVEY',
                    (string) $session->id,
                    'NEED_APPROVAL',
                    [
                        'Map_To'                 => $surveyId,
                        'Opportunity_Partner_Id' => $b['Opportunity_Partner_Id'] ?? null,
                    ]
                );
            }

            // Return the computed section scores so the client can render the
            // "Analisis Surveyor" (Penilaian) table right after submit - the
            // same contract as updateMasterDataSurvey(). Missing totals here
            // used to render "undefined" in the Penilaian column.
            return response()->json(['message' => 'success', 'data' => [
                'survey_id'       => $surveyId,
                'C_Total_Score'   => $photo,
                'D_Total_Score'   => $checklist,
                'E_Total_Score'   => $environtment,
                'F_Total_Score'   => $ucoEstimation,
                'Total_Score'     => $totalScore,
                'Analysis_Result' => $this->resolveRecommendation($totalScore),
            ]]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateMasterDataSurvey(Request $r)
    {
        $b     = $r->all();
        $rowId = $r->input('ID');
        if (!$rowId) {
            return response()->json(['success' => false, 'message' => 'ID is required'], 400);
        }

        $exists = DB::table('survey_location')->where('ID', $rowId)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        $photo        = 0;
        $checklist    = 0;
        $environtment = 0;
        $ucoEstimation = 0;

        for ($i = 1; $i <= 4; $i++) {
            $photo += $b["C_c$i"] * 2.5;
        }

        for ($j = 1; $j <= 9; $j++) {
            $count = $b["D_c$j"];
            if ($count == 1 && $j !== 9) {
                $checklist += ($count * 6);
            } else if ($count == 1) {
                $checklist += ($count * 7);
            }
        }

        for ($k = 1; $k <= 4; $k++) {
            $input = $b["E_p$k"];
            if ($input == 1) {
                $environtment += 1;
            } else if ($input == 2) {
                $environtment += 3;
            } else if ($input == 3) {
                $environtment += 5;
            }
        }

        $ton = $b["F_p5_col2"];
        if ($ton > 15) {
            $ucoEstimation += 15;
        } else if ($ton >= 10) {
            $ucoEstimation += 12;
        } else if ($ton >= 6) {
            $ucoEstimation += 8;
        } else if ($ton >= 4) {
            $ucoEstimation += 5;
        } else {
            $ucoEstimation += 2;
        }

        $totalScore = $photo + $checklist + $environtment + $ucoEstimation;

        try {
            DB::transaction(function () use ($rowId, $b, $photo, $checklist, $environtment, $ucoEstimation, $totalScore, $r) {
                DB::table('survey_location')->where('ID', $rowId)->update([
                    // "Opportunity_Partner_Id" => $b["Opportunity_Partner_Id"],
                    "Parent_Id"       => $b["Parent_Id"] ?? null,
                    "Suggestion_Type" => $b["Suggestion_Type"] ?? "ORIGINAL",
                    "Province"            => $b['Province'] ?? "",
                    "City"                => $b['City'],
                    "District"            => $b['District'],
                    "Subdistrict"         => $b['Subdistrict'],
                    "Address"             => $b['Address'],
                    "Location_Name"       => $b['Location_Name'] ?? "",
                    "Latitude"            => $b['Latitude'],
                    "Longitude"           => $b['Longitude'],
                    "Recommendation_Type" => $b['Recommendation_Type'] ?? "INTERNAL",
                    "Surveyor_Name"       => $b['Surveyor_Name'],
                    "Location_Owner_Name" => $b['Location_Owner_Name'],
                    "Area"                => $b['Area'],
                    "Location_Type"       => $b['Location_Type'],
                    "C_c1"                => $b["C_c1"],
                    "C_c2"                => $b["C_c2"],
                    "C_c3"                => $b["C_c3"],
                    "C_c4"                => $b["C_c4"],
                    "C_Total_Score"       => $photo,
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
                    "F_Total_Score"       => $ucoEstimation,
                    "G_p1"                => $b["G_p1"] ?? "",
                    "G_p2"                => $b["G_p2"] ?? "",
                    "G_p3"                => $b["G_p3"] ?? "",
                    "Total_Score"         => $totalScore,
                    "Total_Score"         => $totalScore,
                    "Approval_Surveyor"   => $b["Approval_Surveyor"] ?? 1,
                    "Approval_Manager"    => $b["Approval_Manager"] ?? 0,
                    "Approval_COO"        => $b["Approval_COO"] ?? 0,
                ]);

                $fileInputs = ['C_c1_File', 'C_c2_File', 'C_c3_File', 'C_c4_File'];

                foreach ($fileInputs as $inputName) {
                    if ($r->hasFile($inputName) && $r->file($inputName)->isValid()) {
                        $file  = $r->file($inputName);
                        $year  = Carbon::now()->year;
                        $month = Carbon::now()->format('m');
                        $path  = $file->store("$year/$month", 'public');
                        $ext   = $file->getClientOriginalExtension();

                        DB::table('meta_files')
                            ->where('Topic', 'survey')
                            ->where('Map_To', $rowId)
                            ->where('Description', $inputName)
                            ->delete();

                        $this->uploadService->uploadMetaFile($path, basename($path), $inputName, $ext, 'survey', $rowId);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }

        $analysisResult = "Tidak Diketahui";
        if ($totalScore >= 80) {
            $analysisResult = "Layak";
        } else if ($totalScore >= 70) {
            $analysisResult = "Layak Bersyarat";
        } else {
            $analysisResult = "Tidak Layak";
        }

        $survey = DB::table('survey_location')->where('ID', $rowId)->first();

        $isPartner = optional(auth()->user())->Priv_ID === 'MITRA';
        $hasApprovalRequest = DB::table('approval_request')
            ->where('Request_Type', 'SURVEY')
            ->where('Map_To', $rowId)
            ->exists();

        if ($survey && !$isPartner && !$hasApprovalRequest) {
            DB::table('survey_location')->where('ID', $rowId)->update(['Status' => 'APPROVED']);

            if ($survey->Opportunity_Partner_Id) {
                $this->advanceOnboardingStageToSetup($survey->Opportunity_Partner_Id);
            }
        }

        return response()->json(['message' => 'success', 'data' => [
            'C_Total_Score'   => $photo,
            'D_Total_Score'   => $checklist,
            'E_Total_Score'   => $environtment,
            'F_Total_Score'   => $ucoEstimation,
            'Total_Score'     => $totalScore,
            'Analysis_Result' => $analysisResult
        ]]);
    }

    public function getSurvey(Request $r)
    {
        return;
    }

    public function deleteMasterDataSurvey(Request $r)
    {
        $ids = $r->input('ids');
        if (!$ids || !is_array($ids) || empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'IDs are required'], 400);
        }

        DB::table('survey_location')->whereIn('ID', $ids)->delete();

        return response()->json(['status' => 'success', 'message' => count($ids) . ' survey(s) deleted successfully']);
    }

    private function advanceOnboardingStageToSetup($opportunityId)
    {
        $session = $this->userService->getCurrentSessionUserInfo();
        $stageLabelMap = [
            'DOCUMENT_CHECK' => 'Cek Dokumen',
            'MOU' => 'Penandatanganan MOU',
            'INVOICE' => 'Penerbitan Invoice DP',
            'PKS' => 'Perjanjian Kerjasama',
            'SURVEY' => 'Survey Lokasi',
            'SETUP' => 'Installment',
            'ACTIVE' => 'Aktif',
        ];

        $partner = DB::table('opportunity_partner')->where('id', $opportunityId)->first();
        if (!$partner || $partner->onboarding_stage !== 'SURVEY') {
            return;
        }

        DB::transaction(function () use ($opportunityId, $session, $stageLabelMap) {
            // Mark SURVEY as completed
            $surveyLogData = [
                'Stage' => 'SURVEY',
                'Stage_Label' => $stageLabelMap['SURVEY'],
                'Status' => 'completed',
                'Completed_At' => now(),
                'Completed_By' => $session->id,
                'Notes' => 'Survey selesai dilengkapi',
                'Updated_At' => now(),
            ];

            $existingSurvey = DB::table('partner_onboarding_log')
                ->where('Opportunity_Id', $opportunityId)
                ->where('Stage', 'SURVEY')
                ->first();

            if ($existingSurvey) {
                DB::table('partner_onboarding_log')
                    ->where('Id', $existingSurvey->Id)
                    ->update($surveyLogData);
            } else {
                $surveyLogData['Opportunity_Id'] = $opportunityId;
                $surveyLogData['Created_At'] = now();
                DB::table('partner_onboarding_log')->insert($surveyLogData);
            }

            // Advance to SETUP
            DB::table('opportunity_partner')
                ->where('id', $opportunityId)
                ->update([
                    'onboarding_stage' => 'SETUP',
                    'onboarding_stage_updated_at' => now(),
                    'onboarding_stage_updated_by' => $session->id,
                ]);

            $setupLogData = [
                'Opportunity_Id' => $opportunityId,
                'Stage' => 'SETUP',
                'Stage_Label' => $stageLabelMap['SETUP'],
                'Status' => 'in_progress',
                'Created_At' => now(),
                'Updated_At' => now(),
            ];

            $existingSetup = DB::table('partner_onboarding_log')
                ->where('Opportunity_Id', $opportunityId)
                ->where('Stage', 'SETUP')
                ->first();

            if ($existingSetup) {
                unset($setupLogData['Opportunity_Id']);
                unset($setupLogData['Created_At']);
                DB::table('partner_onboarding_log')
                    ->where('Id', $existingSetup->Id)
                    ->update($setupLogData);
            } else {
                DB::table('partner_onboarding_log')->insert($setupLogData);
            }
        });
    }
};
