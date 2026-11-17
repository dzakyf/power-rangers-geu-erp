<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\Survey\SurveyScoringService;
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
    protected SurveyScoringService $surveyScoring;

    public function __construct(GeneratorService $g, UploadService $up, UserService $us, ApprovalController $ac, SurveyScoringService $scoring)
    {
        $this->uploadService = $up;
        $this->userService = $us;
        $this->generatorService = $g;
        $this->approvalController = $ac;
        $this->surveyScoring = $scoring;
    }

    public function indexSurvey()
    {
        return view('partner.survey.index');
    }

    public function indexApprovalSurvey()
    {
        return view('partner.survey.approval-index');
    }

    public function createMasterDataSurvey(Request $r)
    {
        $form = null;
        if ($r->query('id')) {
            $survey = DB::table('survey_location')->where('ID', $r->query('id'))->first();
            if ($survey && !empty($survey->Survey_Form_Version_ID)) {
                $form = $this->surveyScoring->versionWithItems($survey->Survey_Form_Version_ID);
            }
        }

        if (!$form) {
            $form = $this->surveyScoring->activeVersionWithItems();
        }

        return view('partner.survey.create', compact('form'));
    }

    public function createReplyMasterDataSurvey()
    {
        $form = $this->surveyScoring->activeVersionWithItems();

        return view('partner.survey.survey-reply', compact('form'));
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
                $row->addChild("cell", $p->Recommendation ?: $this->resolveRecommendation($p->Total_Score));
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
            $row->addChild('cell', $p->Recommendation ?: $this->resolveRecommendation($p->Total_Score));
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
            $p->Recommendation = $p->Recommendation ?: $this->resolveRecommendation($p->Total_Score);
            $answers = DB::table('survey_answers')
                ->where('Survey_Location_ID', $p->ID)
                ->get()
                ->map(function ($answer) {
                    $answer->Answer_JSON = json_decode($answer->Answer_JSON, true);
                    return $answer;
                })
                ->keyBy('Item_Code');
            $p->Answers = $answers;

            if (!empty($p->Survey_Form_Version_ID)) {
                $p->Survey_Form = $this->surveyScoring->versionWithItems($p->Survey_Form_Version_ID);
            }

            return $p;
        });

        return response()->json(['data' => $d, 'form' => $this->surveyScoring->activeVersionWithItems()]);
    }

    private function resolveRecommendation($score = 0): string
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
        $version = $this->surveyScoring->activeVersionWithItems();

        if (!$version) {
            return response()->json(['success' => false, 'message' => 'Form survey aktif belum tersedia'], 400);
        }

        $isPartner = optional(auth()->user())->Priv_ID === 'MITRA';
        $highest = DB::table('survey_location as sl')->max('ID');
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
            $answers = $this->answerInput($r, $version);
            $result = $this->surveyScoring->calculate($version, $version->items, $answers);
            $legacy = $this->legacyColumns($result);
            $parentId = $b["Parent_Id"] ?? null;
            $surveyStatus = "NEED_APPROVAL";
            if (!$isPartner && $parentId) {
                $surveyStatus = "DONE_SURVEY";
            }

            $surveyId = DB::transaction(function () use ($b, $applicatonCode, $legacy, $result, $version, $surveyStatus, $r) {
                $surveyId = DB::table('survey_location')->insertGetId([
                    "Opportunity_Partner_Id" => $b['Opportunity_Partner_Id'] ?? "",
                    "Code" => $applicatonCode,
                    "Surveyed_At" => $b["Survey_At"] ?? now(),
                    "Parent_Id" => $b["Parent_Id"] ?? null,
                    "Suggestion_Type" => $b["Suggestion_Type"] ?? "ORIGINAL",
                    "Province" => $b['Province'] ?? "",
                    "City" => $b['City'] ?? '',
                    "District" => $b['District'] ?? '',
                    "Subdistrict" => $b['Subdistrict'] ?? '',
                    "Address" => $b['Address'] ?? '',
                    "Location_Name" => $b['Location_Name'] ?? "",
                    "Latitude" => $b['Latitude'] ?? '',
                    "Longitude" => $b['Longitude'] ?? '',
                    "Recommendation_Type" => $b['Recommendation_Type'] ?? "INTERNAL",
                    "Status" => $surveyStatus,
                    "Surveyor_Name" => $b['Surveyor_Name'] ?? '',
                    "Location_Owner_Name" => $b['Location_Owner_Name'] ?? '',
                    "Area" => $b['Area'] ?? '',
                    "Location_Type" => $b['Location_Type'] ?? '',
                    "G_p1" => $b["G_p1"] ?? "",
                    "G_p2" => $b["G_p2"] ?? "",
                    "G_p3" => $b["G_p3"] ?? "",
                    "Approval_Surveyor" => $b["Approval_Surveyor"] ?? 1,
                    "Approval_Manager" => $b["Approval_Manager"] ?? 0,
                    "Approval_COO" => $b["Approval_COO"] ?? 0,
                    "Survey_Form_Version_ID" => $version->ID,
                    "Max_Score" => $result['max_score'],
                    "Score_Percentage" => $result['percentage'],
                    "Recommendation" => $result['recommendation'],
                ] + $legacy);

                $this->persistDynamicAnswers($surveyId, $version, $result, $r);

                return $surveyId;
            });

            if (!$isPartner && !$parentId) {
                $session = $this->userService->getCurrentSessionUserInfo();
                $this->approvalController->createApprovalRequest(
                    'SURVEY',
                    (string) $session->id,
                    'NEED_APPROVAL',
                    [
                        'Map_To' => $surveyId,
                        'Opportunity_Partner_Id' => $b['Opportunity_Partner_Id'] ?? null,
                    ]
                );
            }

            return response()->json(['message' => 'success', 'data' => $this->surveyResponseData($surveyId, $result)]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateMasterDataSurvey(Request $r)
    {
        $b = $r->all();
        $rowId = $r->input('ID');
        if (!$rowId) {
            return response()->json(['success' => false, 'message' => 'ID is required'], 400);
        }

        $survey = DB::table('survey_location')->where('ID', $rowId)->first();
        if (!$survey) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        $version = !empty($survey->Survey_Form_Version_ID)
            ? $this->surveyScoring->versionWithItems($survey->Survey_Form_Version_ID)
            : $this->surveyScoring->activeVersionWithItems();

        if (!$version) {
            return response()->json(['success' => false, 'message' => 'Form survey tidak ditemukan'], 400);
        }

        try {
            $answers = $this->answerInput($r, $version);
            $result = $this->surveyScoring->calculate($version, $version->items, $answers);
            $legacy = $this->legacyColumns($result);

            DB::transaction(function () use ($rowId, $b, $legacy, $result, $version, $r) {
                DB::table('survey_location')->where('ID', $rowId)->update([
                    "Parent_Id" => $b["Parent_Id"] ?? null,
                    "Suggestion_Type" => $b["Suggestion_Type"] ?? "ORIGINAL",
                    "Province" => $b['Province'] ?? "",
                    "City" => $b['City'],
                    "District" => $b['District'],
                    "Subdistrict" => $b['Subdistrict'],
                    "Address" => $b['Address'],
                    "Location_Name" => $b['Location_Name'] ?? "",
                    "Latitude" => $b['Latitude'],
                    "Longitude" => $b['Longitude'],
                    "Recommendation_Type" => $b['Recommendation_Type'] ?? "INTERNAL",
                    "Surveyor_Name" => $b['Surveyor_Name'],
                    "Location_Owner_Name" => $b['Location_Owner_Name'],
                    "Area" => $b['Area'],
                    "Location_Type" => $b['Location_Type'],
                    "G_p1" => $b["G_p1"] ?? "",
                    "G_p2" => $b["G_p2"] ?? "",
                    "G_p3" => $b["G_p3"] ?? "",
                    "Approval_Surveyor" => $b["Approval_Surveyor"] ?? 1,
                    "Approval_Manager" => $b["Approval_Manager"] ?? 0,
                    "Approval_COO" => $b["Approval_COO"] ?? 0,
                    "Survey_Form_Version_ID" => $version->ID,
                    "Max_Score" => $result['max_score'],
                    "Score_Percentage" => $result['percentage'],
                    "Recommendation" => $result['recommendation'],
                ] + $legacy);

                DB::table('survey_answers')->where('Survey_Location_ID', $rowId)->delete();
                $this->persistDynamicAnswers($rowId, $version, $result, $r);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }

        $survey = DB::table('survey_location')->where('ID', $rowId)->first();
        $isPartner = optional(auth()->user())->Priv_ID === 'MITRA';
        $hasApprovalRequest = DB::table('approval_request')
            ->where('Request_Type', 'SURVEY')
            ->where('Map_To', $rowId)
            ->exists();

        if ($survey && !$isPartner && !$hasApprovalRequest) {
            DB::table('survey_location')->where('ID', $rowId)->update(['Status' => 'DONE_SURVEY']);

            if ($survey->Opportunity_Partner_Id) {
                $this->advanceOnboardingStageToSetup($survey->Opportunity_Partner_Id);
            }
        }

        return response()->json(['message' => 'success', 'data' => $this->surveyResponseData($rowId, $result)]);
    }

    private function answerInput(Request $r, $version): array
    {
        $incoming = $r->input('answers', []);
        if (is_array($incoming) && count($incoming) > 0) {
            return $incoming;
        }

        $b = $r->all();
        $answers = [];
        foreach ($version->items as $item) {
            $code = $item->Item_Code;
            $section = $item->Section_Code;
            $number = (int) preg_replace('/\D+/', '', $code);

            if ($section === 'C') {
                $answers[$code] = ['value' => $b["C_c$number"] ?? null];
            } elseif ($section === 'D') {
                $answers[$code] = [
                    'value' => $b["D_c$number"] ?? null,
                    'notes' => $b["D_c{$number}_Notes"] ?? null,
                ];
            } elseif ($section === 'E') {
                $answers[$code] = ['value' => $b["E_p$number"] ?? null];
            } elseif ($section === 'F') {
                $answers[$code] = [
                    'source_value' => $b["F_p{$number}_col1"] ?? null,
                    'uco_estimation' => $b["F_p{$number}_col2"] ?? null,
                ];
            }
        }

        return $answers;
    }

    private function legacyColumns(array $result): array
    {
        $columns = [
            'C_Total_Score' => $result['section_scores']['C'] ?? 0,
            'D_Total_Score' => $result['section_scores']['D'] ?? 0,
            'E_Total_Score' => $result['section_scores']['E'] ?? 0,
            'F_Total_Score' => $result['section_scores']['F'] ?? 0,
            'Total_Score' => $result['total'],
        ];


        foreach ($result['items'] as $code => $entry) {
            if (!preg_match('/^([CDEF])(\d+)$/', $code, $m)) {
                continue;
            }
            $section = $m[1];
            $number = (int) $m[2];
            $value = $entry['value'];

            if ($section === 'C' && $number <= 4) {
                $columns["C_c$number"] = $value ?: 0;
            } elseif ($section === 'D' && $number <= 9) {
                $columns["D_c$number"] = $value ?: 0;
                $columns["D_c{$number}_Notes"] = $entry['notes'] ?? '';
            } elseif ($section === 'E' && $number <= 4) {
                $columns["E_p$number"] = $value ?: 0;
            } elseif ($section === 'F' && $number <= 4) {
                $source = (float) ($entry['answer_json']['source_value'] ?? 0);
                $uco = (float) ($entry['answer_json']['uco_estimation'] ?? 0);
                $columns["F_p{$number}_col1"] = $source;
                $columns["F_p{$number}_col2"] = $uco;
            }
        }

        return $columns;
    }

    private function persistDynamicAnswers(int $surveyId, $version, array $result, Request $r): void
    {
        foreach ($result['items'] as $code => $entry) {
            $item = $entry['item'];
            $filePath = null;
            $file = $r->file("answers.$code.file");

            if (!$file && preg_match('/^C(\d+)$/', $code, $m)) {
                $file = $r->file("C_c{$m[1]}_File");
            }

            if ($file && $file->isValid()) {
                $year = Carbon::now()->year;
                $month = Carbon::now()->format('m');
                $path = $file->store("$year/$month", 'public');
                $filePath = $path;
                $description = preg_match('/^C(\d+)$/', $code, $m) ? "C_c{$m[1]}_File" : "{$code}_File";

                DB::table('meta_files')
                    ->where('Topic', 'survey')
                    ->where('Map_To', $surveyId)
                    ->where('Description', $description)
                    ->delete();

                $this->uploadService->uploadMetaFile($path, basename($path), $description, $file->getClientOriginalExtension(), 'survey', $surveyId);
            }

            DB::table('survey_answers')->insert([
                'Survey_Location_ID' => $surveyId,
                'Form_Item_ID' => $item->ID,
                'Item_Code' => $code,
                'Section_Code' => $item->Section_Code,
                'Criteria_Snapshot' => $item->Criteria,
                'Keterangan_Snapshot' => $item->Keterangan,
                'Minimum_Standard_Snapshot' => $item->Minimum_Standard,
                'Parameter_Evaluation_Snapshot' => $item->Parameter_Evaluation,
                'Answer_JSON' => json_encode($entry['answer_json']),
                'Score' => $entry['score'],
                'Notes' => $entry['notes'],
                'File_Path' => $filePath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function surveyResponseData(int $surveyId, array $result): array
    {
        return [
            'survey_id' => $surveyId,
            'C_Total_Score' => $result['section_scores']['C'] ?? 0,
            'D_Total_Score' => $result['section_scores']['D'] ?? 0,
            'E_Total_Score' => $result['section_scores']['E'] ?? 0,
            'F_Total_Score' => $result['section_scores']['F'] ?? 0,
            'Total_Score' => $result['total'],
            'Max_Score' => $result['max_score'],
            'Score_Percentage' => $result['percentage'],
            'Analysis_Result' => $result['recommendation'],
            'section_scores' => $result['section_scores'],
            'section_max' => $result['section_max'],
        ];
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
