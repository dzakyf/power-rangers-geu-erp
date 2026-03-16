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

    public function indexProfitSharing()
    {
        return view('approval.profit-sharing-index');
    }

    public function createProfitSharingApplication()
    {
        return view('approval.profit-sharing-create');
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
            // $getUser = DB::table('users')->where('id', $id)->first();
            $partner = DB::table('opportunity_partner')->where('id', $body['id'])->first();
            if ($partner && $partner->lead_id != null) {
                $cekUser = DB::table('users')->where("Emp_ID", $partner->lead_id)->first();
                if (!$cekUser) {
                    $pass = 'geupartner123';
                    $username = str_replace(' ', '_', strtolower($partner->full_name));
                    if (!auth()->check()) {
                        $cekUser = DB::table('users')->get();
                        foreach ($cekUser as $key => $value) {
                            if ($value->username == $username) {
                                $username = $username . rand(100, 999);
                            }
                        }
                    }
                    $contact = $partner->register_as == 'individu' ? $partner->contact : $partner->pic_phone;

                    if ($partner->lead_id != null && $contact == '') {
                        $opportunity = DB::table('opportunity')->where("Task_ID", $partner->lead_id)->first();
                        $contact = $opportunity->Phone_Number;
                    }

                    DB::table('users')->insert([
                        'username' => $username,
                        'password' => bcrypt($pass),
                        'Emp_ID' => $partner->lead_id,
                        'Position' => "MITRA",
                        'Priv_ID' => "MITRA",
                        'Location' => 'GEU-HO',
                        'Name' => $partner->full_name,
                        'Contact' => $contact,
                        'created_at' => date('Y-m-d H:i:s'),
                        'Area' => $partner->city,
                        'Status' => 'ACTIVE',
                        'Department' => 'MITRA',
                        'Company_ID' => 'GEU',
                        'Sub_Menu' => "MS00010,SM00047,SS00065,SI00231,SI00233,SM00048,SS00066,SI00237,SI00236",
                    ]);
                    $cekUser = DB::table('users')->where("Emp_ID", $partner->lead_id)->first();
                    if ($cekUser) {
                        $user = $cekUser->id;
                    }
                }
                DB::table('opportunity_partner')->where('id', $partner->id)->update([
                    'status' => 'ACTIVE',
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
        }

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

        return "submitted";
    }

    public function processOnboardingToNextProgress(Request $r)
    {
        try {
            $b = $r->all();
            $req = DB::table('approval_request')->select('*')->where('ID', '=', $b['id'])->first();
            $level = DB::table('approval_level')->select('*')->where('ID', $req->Level_Id)->where('Request_Type', '=', 'ONBOARDING')->first();
            $nextLevel = DB::table('approval_level')->select('*')->where('ID', $level->Next_Level_Id)->where('Request_Type', '=', 'ONBOARDING')->first();

            $session = $this->userService->getCurrentSessionUserInfo();

            $result = DB::transaction(function () use ($b, $req, $session, $level, $nextLevel) {
                $logId = DB::table('approval_log')->insertGetId([
                    'Approver_Id' => $session->id,
                    'Notes' => "",
                    'Action' =>  $b['action'],
                    'Level_Number' => $level->Level_Number,
                    'Next_Level' => $b['action'] == "APPROVED" ? $level->Level_Number + 1 : $level->Level_Number,
                    'Level_Id' => $level->ID,
                    'Next_Level_Id' => $nextLevel->ID,
                    'Request_Id' => $req->ID,
                ]);

                $updated = DB::table('approval_request')
                    ->where('ID', '=', $req->ID)
                    ->update([
                        'Status' => $session->Position === "BOD" ? "APPROVED" : "NEED_APPROVAL",
                        'Description' => "Berhasil menuju tahap selanjutnya",
                        'Current_Level' => $req->Current_Level + 1,
                        'Level_Id' => $nextLevel->ID,
                        'Request_Type' => $req->Request_Type,
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function processProfitSharingToNextProgress(Request $r)
    {
        try {
            $b = $r->all();
            $req = DB::table('approval_request')->select('*')->where('ID', '=', $b['id'])->first();
            $level = DB::table('approval_level')->select('*')->where('ID', $req->Level_Id)->where('Request_Type', '=', 'PROFIT_SHARING')->first();
            $nextLevel = DB::table('approval_level')->select('*')->where('ID', $level->Next_Level_Id)->where('Request_Type', '=', 'PROFIT_SHARING')->first();

            $session = $this->userService->getCurrentSessionUserInfo();

            $result = DB::transaction(function () use ($b, $req, $session, $level, $nextLevel) {
                $logId = DB::table('approval_log')->insertGetId([
                    'Approver_Id' => $session->id,
                    'Notes' => "",
                    'Action' =>  $b['action'],
                    'Level_Number' => $level->Level_Number,
                    'Next_Level' => $b['action'] == "APPROVED" ? $level->Level_Number + 1 : $level->Level_Number,
                    'Level_Id' => $level->ID,
                    'Next_Level_Id' => $nextLevel->ID,
                    'Request_Id' => $req->ID,
                ]);

                $updated = DB::table('approval_request')
                    ->where('ID', '=', $req->ID)
                    ->update([
                        'Status' => $session->Position === "BOD" ? "APPROVED" : "NEED_APPROVAL",
                        'Description' => "Berhasil menuju tahap selanjutnya",
                        'Current_Level' => $req->Current_Level + 1,
                        'Level_Id' => $nextLevel->ID,
                        'Request_Type' => $req->Request_Type,
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processRenewalToNextProgress(Request $r)
    {
        try {
            $b = $r->all();
            $req = DB::table('approval_request')->select('*')->where('ID', '=', $b['id'])->first();
            $level = DB::table('approval_level')->select('*')->where('ID', $req->Level_Id)->where('Request_Type', '=', 'RENEWAL')->first();
            $nextLevel = DB::table('approval_level')->select('*')->where('ID', $level->Next_Level_Id)->where('Request_Type', '=', 'RENEWAL')->first();

            $session = $this->userService->getCurrentSessionUserInfo();

            $result = DB::transaction(function () use ($b, $req, $session, $level, $nextLevel) {
                $logId = DB::table('approval_log')->insertGetId([
                    'Approver_Id' => $session->id,
                    'Notes' => "",
                    'Action' =>  $b['action'],
                    'Level_Number' => $level->Level_Number,
                    'Next_Level' => $b['action'] == "APPROVED" ? $level->Level_Number + 1 : $level->Level_Number,
                    'Level_Id' => $level->ID,
                    'Next_Level_Id' => $nextLevel->ID,
                    'Request_Id' => $req->ID,
                ]);

                $updated = DB::table('approval_request')
                    ->where('ID', '=', $req->ID)
                    ->update([
                        'Status' => $session->Position === "BOD" ? "APPROVED" : "NEED_APPROVAL",
                        'Description' => "Berhasil menuju tahap selanjutnya",
                        'Current_Level' => $req->Current_Level + 1,
                        'Level_Id' => $nextLevel->ID,
                        'Request_Type' => $req->Request_Type,
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processSurveyToNextProgress(Request $r)
    {
        try {
            $b = $r->all();
            $req = DB::table('approval_request')->select('*')->where('ID', '=', $b['id'])->first();
            $level = DB::table('approval_level')->select('*')->where('ID', $req->Level_Id)->where('Request_Type', '=', 'RENEWAL')->first();
            $nextLevel = DB::table('approval_level')->select('*')->where('ID', $level->Next_Level_Id)->where('Request_Type', '=', 'RENEWAL')->first();

            $session = $this->userService->getCurrentSessionUserInfo();

            $result = DB::transaction(function () use ($b, $req, $session, $level, $nextLevel) {
                $logId = DB::table('approval_log')->insertGetId([
                    'Approver_Id' => $session->id,
                    'Notes' => "",
                    'Action' =>  $b['action'],
                    'Level_Number' => $level->Level_Number,
                    'Next_Level' => $b['action'] == "APPROVED" ? $level->Level_Number + 1 : $level->Level_Number,
                    'Level_Id' => $level->ID,
                    'Next_Level_Id' => $nextLevel->ID,
                    'Request_Id' => $req->ID,
                ]);

                $updated = DB::table('approval_request')
                    ->where('ID', '=', $req->ID)
                    ->update([
                        'Status' => $session->Position === "BOD" ? "APPROVED" : "NEED_APPROVAL",
                        'Description' => "Berhasil menuju tahap selanjutnya",
                        'Current_Level' => $req->Current_Level + 1,
                        'Level_Id' => $nextLevel->ID,
                        'Request_Type' => $req->Request_Type,
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function showProgress(Request $request)
    {
        $params = $request->route()->parameters();


        $partner = DB::table('opportunity_partner')->where('id', $params['id'])->first();

        $pic = [];
        if (Schema::hasTable('users')) {
            $pic = DB::table('users')->select('username', DB::raw("COALESCE(name, username) as Name"))->get();
        }

        $documents = DB::table('opportunity_partner_file')->where('opportunity_id', $params['id'])->get();


        foreach ($documents as $key => $value) {
            $documents[$key]->url = "https://dev.hexamatika.com/geu/hcga/public/filevendor/" . $value->file_path;
        };

        $status = DB::table('approval_request as ar')
            ->select('ar.*', 'al.*')
            ->join('approval_log as al', 'ar.ID', '=', 'al.Request_Id')
            ->where('ar.Requester_Id', '=', $params['id'])
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

    // ->where('requester.id', 'LIKE', $userId)
    //?user_id=&type=
    public function getApprovalData(Request $r)
    {
        $q = $r->query();
        $type = $q['type'] ?? "%";
        $session = $this->userService->getCurrentSessionUserInfo();
        $user_id = $session->id ?? "%";
        $apprId = $q['id'] ?? "%";

        $d = DB::table('approval_request as appr')
            ->select('appr.*', 'opp.*', 'usr.Name', 'lv.Approver_Position')
            ->join('opportunity_partner as opp', 'appr.Opportunity_Partner_Id', '=', 'opp.id')
            ->join('users as usr', 'appr.Requester_Id', '=', 'usr.id')
            ->join('approval_level as lv', 'appr.Level_Id', '=', 'lv.ID');

        if (in_array($session->Position, ["SALES", "MANAGER", "BOD", "HEAD", "CFO"])) {
            $d->where('appr.Request_Type', 'LIKE',  $type)->where('appr.Status', '=', 'NEED_APPROVAL')->where('lv.Approver_Position', '=', $session->Position);
            // MITRA
        } else if (in_array($session->Position, ["MITRA"])) {
            $d->where('appr.Request_Type', 'LIKE',  $type)->where('appr.Requester_Id', 'LIKE', $user_id);
        } else {
            $d->where('appr.Request_Type', 'LIKE',  $type)->orWhere(function ($query) use ($user_id, $apprId) {
                $query->where('appr.Requester_Id', 'LIKE', $user_id)
                    ->orWhere('appr.Opportunity_Partner_Id', 'LIKE', $apprId);
            });
        }

        $d = $d->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->ID);
            $row->addAttribute('Oppt_Id', $p->id);
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->Name);
            $row->addChild('cell', $p->Request_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', htmlspecialchars($p->Description));
            $row->addChild('cell', $p->Name);
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
        $type = $q['type'] ?? "%";
        $userId = $q['user_id'] ?? "";

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
            ->join('approval_level as level', 'log.Level_Id', '=', 'level.ID')
            ->join('users as requester', 'requester.id', '=', 'request.Requester_Id')
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
}
