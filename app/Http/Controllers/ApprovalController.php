<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleXMLElement;

class ApprovalController extends Controller
{
    protected $generatorService;
    protected $userService;

    public function __construct(GeneratorService $g, UserService $s)
    {
        $this->generatorService = $g;
        $this->userService = $s;
    }

    public function index()
    {
        return view('approval.index');
    }

    public function indexTransferMembershipApproval()
    {
        return view('transfer-membership.index');
    }

    public function createTransferMembershipApproval()
    {
        return view('approval.transfer-membership-create');
    }

    public function indexProfitSharing()
    {
        return view('approval.profit-sharing-index');
    }

    public function indexMarketingApproval()
    {
        return view('partner.branding.approval-index');
    }

    public function createProfitSharingApplication()
    {
        $partner = $this->userService->getCurrentSessionUserInfo();

        return view('approval.profit-sharing-create', compact('partner'));
    }

    public function detail(Request $request)
    {
        $partner = DB::table('opportunity_partner')->where('id', $request->ID)->first();

        $pic = [];
        if (Schema::hasTable('users')) {
            $pic = DB::table('users')->select('username', DB::raw("COALESCE(name, username) as Name"))->get();
        }

        $documents = DB::table('opportunity_partner_file')->where('opportunity_id', $request->ID)->get();


        foreach ($documents as $key => $value) {
            $documents[$key]->url = "https://dev.hexamatika.com/geu/hcga/public/filevendor/" . $value->file_path;
        };

        return view('approval.detail', compact('partner', 'pic', 'documents'));
    }

    public function generatePKS(Request $r)
    {
        $id = $r->query('id');
        $path = public_path('pks.docx');
        $doc = new TemplateProcessor($path);
        $now = Carbon::now()->format('YmdHisv');

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $id)->first();

        $partner_name = $data->register_as == "individual" ? $data->full_name : $data->company_full_name;
        $full_address = "$data->address, $data->pi_rt, $data->pi_rw, $data->pi_ward, $data->pi_subdistrict, $data->pi_city, $data->pi_province, $data->pi_postal_code";

        $doc->setValues([
            'partner_name' => $partner_name ?? '${contoh}',
            'signed_at' => Carbon::Now(),
            'partner_region' => $data->pi_city ?? '${contoh}',
            'ktp' => $data->ktp,
            'alamat' => $data->pic ?? $partner_name,
            'pic_name' => $data->pic ?? $partner_name,
            'npwp' => $data->npwp,
            'membership_term' => 0,
            'membership_fee' => 0,
            'payment_phase2' => 0,
            'warehouse_fee' => 0,
            'inventory_fee' => 0,
            'namapaket' => 0,
        ]);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $outputPath = storage_path("app/public/pks_$now.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    /** TODO :
     * tamhain downpayment, payment thapa 2, pelunasan, generate mou number
     */
    public function generateMOU(Request $req)
    {
        $id = $req->query('id');
        $path = public_path('mou.docx');
        $doc = new TemplateProcessor($path);
        $now = Carbon::now()->format('YmdHisv');

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $id)->first();

        $partner_name = $data->register_as == "individual" ? $data->full_name : $data->company_full_name;
        $full_address = "$data->address, $data->pi_rt, $data->pi_rw, $data->pi_ward, $data->pi_subdistrict, $data->pi_city, $data->pi_province, $data->pi_postal_code";

        $doc->setValues([
            'mou_number' => '',
            'hub_type' => '',
            'mitra_fullname' => $partner_name ?? '${contoh}',
            'partner_region' => $data->pi_city ?? '${contoh}',
            'signed_at' => Carbon::now(),
            'nama' => $data->pic ?? $partner_name,
            'nik' => $data->ktp,
            'NIK' => $data->ktp,
            'full_address' => $full_address,
            'downpayment' => 0,
            'payment_phase2' => 0,
            'repayment' => 0
        ]);


        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $outputPath = storage_path("app/public/mou_$now.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function submit(Request $r)
    {
        $body = $r->all();
        $user = 0;

        try {
            $partner = DB::table('opportunity_partner')->where('id', $body['id'])->first();
            // User creation deferred to BOD approval (processOnboardingApproval)
        } catch (\Throwable $th) {
            Log::error($th);
        }

        DB::transaction(function () use ($body, $user) {
            $id = DB::table('approval_request')->insertGetId([
                'Status' => $body['type'],
                'Requester_Id' => $user,
                'Description' => "Initial Review",
                'Request_Type' => "ONBOARDING",
                'Opportunity_Partner_Id' => $body['id']
            ]);

            $lastRow = DB::table('approval_request')->select('*')->where('ID', '=', $id)->first();

            DB::table('approval_log')->insert([
                'Approver_Id' => $this->userService->getCurrentSessionUserInfo()->id,
                'Notes' => "",
                'Action' => $body['type'],
                'Request_Id' => $id,
                'Level_Number' => $lastRow->Current_Level,
                'Next_Level' => (int)$lastRow->Current_Level + 1
            ]);
        });

        return "submitted";
    }

    public function processTransferMembershipToNextProgress(Request $r)
    {
        $result = $this->processToNextLevel((int) $r->input('id'), $r->input('action'), $r->input('notes', ''));
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function processOnboardingToNextProgress(Request $r)
    {
        $result = $this->processToNextLevel((int) $r->input('id'), $r->input('action'), $r->input('notes', ''));
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function processProfitSharingToNextProgress(Request $r)
    {
        $result = $this->processToNextLevel((int) $r->input('id'), $r->input('action'), $r->input('notes', ''));
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function processRenewalToNextProgress(Request $r)
    {
        $result = $this->processToNextLevel((int) $r->input('id'), $r->input('action'), $r->input('notes', ''));
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function processSurveyToNextProgress(Request $r)
    {
        $result = $this->processToNextLevel((int) $r->input('id'), $r->input('action'), $r->input('notes', ''));
        return response()->json($result, $result['success'] ? 200 : 500);
    }


    public function showProgress(Request $request)
    {
        $q = $request->query();
        $params = $request->route()->parameters();


        $partner = DB::table('opportunity_partner')->where('id', $q['id'])->first();

        $pic = [];
        if (Schema::hasTable('users')) {
            $pic = DB::table('users')->select('username', DB::raw("COALESCE(name, username) as Name"))->get();
        }

        $documents = DB::table('opportunity_partner_file')->where('opportunity_id', $q['id'])->get();


        foreach ($documents as $key => $value) {
            $documents[$key]->url = "https://dev.hexamatika.com/geu/hcga/public/filevendor/" . $value->file_path;
        };

        $status = DB::table('approval_request as ar')
            ->select('ar.*', 'al.*')
            ->join('approval_log as al', 'ar.ID', '=', 'al.Request_Id')
            ->where('ar.Requester_Id', '=', $q['id'])
            ->where('al.Level_Number', '=', '1')
            ->orderBy('al.Action_Date', 'Desc')
            ->first();

        return view('approval.progress', compact('partner', 'pic', 'documents', 'status'));
    }

    public function getBenefitFacility(Request $r)
    {
        // {
        //     $q = $r->query();
        //     $hub = $q['hub_type'];

        //     $benefit = DB::Table('master_facility')->select('*')->where('type', $hub)->get();

        //     return response()->json($benefit);
        // return view('approval.benefit-facility', compact(['benefit' => $benefit]));
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partners = DB::table('broadcast')->get();
        foreach ($partners as $key => $partner) {
            echo "<row id=\"$partner->ID\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($partner->Title) . "</cell>";
            echo "<cell>1</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    /**
     * Create a new approval request with its initial log entry.
     *
     * @param  string  $requestType   e.g. 'ONBOARDING', 'PROFIT_SHARING'
     * @param  int     $requesterId
     * @param  string  $action        initial action written to the log
     * @param  array   $extra         extra columns merged into approval_request (e.g. Map_To, Opportunity_Partner_Id)
     * @return int     the new approval_request ID
     */
    public function createApprovalRequest(string $requestType, ?string $requesterId, string $action, array $extra = []): int
    {
        return DB::transaction(function () use ($requestType, $requesterId, $action, $extra) {
            $id = DB::table('approval_request')->insertGetId(array_merge([
                'Status'       => $action,
                'Requester_Id' => $requesterId,
                'Description'  => 'Initial Review',
                'Request_Type' => $requestType,
            ], $extra));

            $request = DB::table('approval_request')->where('ID', $id)->first();

            DB::table('approval_log')->insert([
                'Approver_Id'  => $this->userService->getCurrentSessionUserInfo()->id,
                'Notes'        => '',
                'Action'       => $action,
                'Request_Id'   => $id,
                'Level_Number' => $request->Current_Level,
                'Next_Level'   => (int) $request->Current_Level + 1,
            ]);

            return $id;
        });
    }

    /**
     * Advance an approval request to its next level and log the action.
     * Works for any Request_Type — driven entirely by the level config in approval_level.
     *
     * @param  int     $requestId
     * @param  string  $action    'APPROVED' | 'REJECTED' | any string
     * @param  string  $notes
     * @return array   ['success' => bool, 'message' => string]
     */
    public function processToNextLevel(int $requestId, string $action, string $notes = ''): array
    {
        try {
            $session = $this->userService->getCurrentSessionUserInfo();

            $req = DB::table('approval_request')->where('ID', $requestId)->first();
            if (!$req) {
                return ['success' => false, 'message' => 'Approval request not found'];
            }

            $level = DB::table('approval_level')
                ->where('Request_Type', $req->Request_Type)
                ->where('Level_Number', $req->Current_Level + 1)
                ->first();

            if (!$level) {
                return ['success' => false, 'message' => 'Approval level not found'];
            }

            $nextLevel = DB::table('approval_level')
                ->where('Request_Type', $req->Request_Type)
                ->where('Level_Number', $req->Current_Level + 2)
                ->first();

            DB::transaction(function () use ($req, $level, $nextLevel, $session, $action, $notes) {
                DB::table('approval_log')->insert([
                    'Approver_Id'   => $session->id,
                    'Notes'         => $notes,
                    'Action'        => $action,
                    'Level_Number'  => $level->Level_Number,
                    'Next_Level'    => $action === 'APPROVED' ? $level->Level_Number + 1 : $level->Level_Number,
                    'Level_Id'      => $level->ID,
                    'Next_Level_Id' => $nextLevel->ID ?? null,
                    'Request_Id'    => $req->ID,
                ]);

                DB::table('approval_request')->where('ID', $req->ID)->update([
                    'Status'        => $nextLevel === null ? 'APPROVED' : 'NEED_APPROVAL',
                    'Description'   => 'Berhasil menuju tahap selanjutnya',
                    'Current_Level' => $req->Current_Level + 1,
                    'Level_Id'      => $nextLevel->ID ?? null,
                    'Request_Type'  => $req->Request_Type,
                ]);
            });

            return ['success' => true, 'message' => 'success'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ->where('requester.id', 'LIKE', $userId)
    //?user_id=&type=
    public function getApprovalData(Request $r)
    {
        $q = $r->query();
        $type = $q['type'] ?? "%";
        $session = $this->userService->getCurrentSessionUserInfo();
        $user_id = $session->id ?? "%";
        $apprId = $q['id'] ?? "%";

        // Head Table 
        $d = DB::table('approval_request as appr');



        // Join untuk informasi tambahan  
        if ($type == "ONBOARDING") {
            $d = $d->select('appr.*',  'usr.Name', 'opp.is_recommended_to_be_approved as Is_Recommended_To_Be_Approved', 'opp.lead_id as Code', 'opp.full_name as Partner_Name')
                ->join('opportunity_partner as opp', 'appr.Opportunity_Partner_Id', '=', 'opp.id');
        }

        if ($type == "BRANDING_PROMOTION") {
            $d = $d->select('appr.*',  'usr.Name', 'bp.Code')
                ->join('branding_promotion as bp', 'appr.Map_To', '=', 'bp.ID');
        }

        if ($type == "PROFIT_SHARING") {
            $d = $d->select('appr.*',  'usr.Name', 'opp.full_name as Partner_Name')
                ->join('opportunity_partner as opp', 'appr.Opportunity_Partner_Id', '=', 'opp.id');
        }

        if ($type == "RENEWAL") {
            $d = $d->select('appr.*',  'usr.Name', 'pr.Created_At as Renewal_Created_At', 'pr.Status as Renewal_Status', 'op.full_name as Partner_Name', 'op.lead_id as Lead_ID')
                ->join('partner_renewal as pr', 'appr.Opportunity_Partner_Id', '=', 'pr.Opportunity_Id')
                ->join('opportunity_partner as op', 'pr.Opportunity_Id', '=', 'op.id');
        }

        if ($type == "TERMINATION") {
            $d = $d->select('appr.*',  'usr.Name', 'op.lead_id as Code', 'op.full_name as Partner_Name')
                ->join('partner_termination as opp', 'appr.Map_To', '=', 'opp.ID')
                ->join('opportunity_partner as op', 'opp.Opportunity_Id', '=', 'op.id');
        }

        if ($type == "TRANSFER_MEMBERSHIP") {
            $d = $d->select('appr.*',  'usr.Name', 'op.lead_id as Code', 'op.full_name as Partner_Name')
                ->join('partner_transfer_membership as opp', 'appr.Map_To', '=', 'opp.ID')
                ->join('opportunity_partner as op', 'opp.Opportunity_Id', '=', 'op.id');
        }

        if ($type == "SURVEY") {
            $d = $d->select('appr.*',  'usr.Name', 'sl.Code as Code')
                ->join('survey_location as sl', 'appr.Map_To', '=', 'sl.ID');
        }

        // Filtering jabatan
        $userLevel = DB::table('approval_level')
            ->where('Request_Type', $type)
            ->where('Approver_Position', $session->Position)
            ->value('Level_Number');

        if ($userLevel) {
            $d->where('appr.Current_Level', '=', $userLevel - 1)
              ->where('appr.Status', '=', 'NEED_APPROVAL')
              ->where('appr.Request_Type', 'LIKE', $type);
        } else if (in_array($session->Position, ["MITRA"])) {
            $d->where('appr.Request_Type', 'LIKE', $type)->where('appr.Requester_Id', 'LIKE', $user_id);
        } else {
            $d->where('appr.Request_Type', 'LIKE', $type)
                ->where(function ($query) use ($user_id, $apprId) {
                    $query->where('appr.Requester_Id', 'LIKE', $user_id)
                        ->orWhere('appr.Opportunity_Partner_Id', 'LIKE', $apprId);
                });
        }


        $d = $d->leftJoin('users as usr', 'appr.Requester_Id', '=', 'usr.id');


        $d = $d->orderBy('ID', 'DESC')->get();



        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        $approvalMapper = [
            "1" => "Yes",
            "2" => "No",
            "3" => "Dalam Pertimbangan"
        ];
        foreach ($d as $p) {

            $recommendation = $p->Is_Recommended_To_Be_Approved ?? 3;
            $row = $xml->addChild('row');
            $row->addAttribute('id', $p->ID);
            $row->addAttribute('Oppt_Id', $p->Opportunity_Partner_Id);
            $row->addAttribute('Mapper_Id', $p->Map_To);
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->Partner_Name ?? $p->Name);
            $row->addChild('cell', $p->Code ?? "");
            $row->addChild('cell', $p->Request_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', $p->Submitted_At);
            $row->addChild('cell', $approvalMapper[$recommendation]);

            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getApprovalDataLog(Request $r)
    {
        $q = $r->query();
        $apprId = $q['appr_id'] ?? "%";


        $d = DB::table('approval_request as request')
            ->select(
                'request.*',
                'log.Action',
                'log.Approver_Id',
                'log.Action_Date',
                'log.Notes',
                'log.Level_Number',
                'level.Level_Name',
                'requester.Name as Requested_By',
                'approver.Name as Approved_By'
            )
            ->join('approval_log as log', 'request.ID', '=', 'log.Request_Id')
            ->leftJoin('approval_level as level', 'log.Level_Id', '=', 'level.ID')
            ->leftJoin('users as requester', 'requester.id', '=', 'request.Requester_Id')
            ->leftJoin('users as approver', 'approver.id', '=', 'log.Approver_Id')
            ->where('log.Request_Id', 'LIKE', $apprId)
            ->orderBy('log.ID', 'DESC')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->Action);
            $row->addChild('cell', htmlspecialchars($p->Level_Name));
            $row->addChild('cell', $p->Request_Type);
            $row->addChild('cell', $p->Requested_By);
            $row->addChild('cell', $p->Approved_By);
            $row->addChild('cell', $p->Action_Date);
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function processPromotionApproval(Request $r)
    {
        $result = $this->processToNextLevel((int) $r->input('id'), $r->input('action'), $r->input('notes', ''));
        return response()->json($result, $result['success'] ? 200 : 500);
    }
    public function getProfitSharingApprovalDetail(Request $r)
    {
        $apprId = $r->query('appr_id');
        $approval = DB::table('approval_request')->where('ID', $apprId)->first();

        if (!$approval) {
            return response()->json(['data' => null], 404);
        }

        $partner = DB::table('opportunity_partner')->where('id', $approval->Opportunity_Partner_Id)->first();
        $ap = DB::table('account_payable')->where('Opportunity_Id', $approval->Opportunity_Partner_Id)->first();
        $files = DB::table('meta_files')
            ->where('Topic', 'profit-sharing')
            ->where('Map_To', $approval->Requester_Id)
            ->get();
        $log = DB::table('approval_log')->where('Request_Id', $apprId)->get();

        return response()->json(['data' => [
            'approval'        => $approval,
            'partner'         => $partner,
            'account_payable' => $ap,
            'files'           => $files,
            'log'             => $log,
        ]]);
    }

    public function getTransferMembershipApprovalDetail(Request $r)
    {
        $apprId = $r->query('appr_id');
        if (!$apprId) {
            return response()->json(['data' => null], 400);
        }

        $data = DB::table('approval_request as appr')
            ->join('partner_transfer_membership as ptm', 'appr.Map_To', '=', 'ptm.ID')
            ->join('opportunity_partner as op', 'ptm.Opportunity_Id', '=', 'op.id')
            ->select('appr.*', 'ptm.*', 'op.full_name', 'op.lead_id')
            ->where('appr.ID', $apprId)
            ->first();

        return response()->json(['data' => $data]);
    }
}
