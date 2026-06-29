<?php

namespace app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use SimpleXMLElement;

class OnboardingController extends Controller
{
    protected $userService;
    protected $generatorService;
    protected $uploaderService;
    public function __construct(UserService $u, GeneratorService $g, UploadService $up)
    {
        $this->userService = $u;
        $this->generatorService = $g;
        $this->uploaderService = $up;
    }

    public function makeMou($body, $opportunityId, $templatePath)
    {
        $doc = new TemplateProcessor($templatePath);

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $opportunityId)->first();

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

        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $body['signature']);
        $imageData = base64_decode($base64Image);

        $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.png';
        file_put_contents($tempImage, $imageData);

        $doc->setImageValue('mitra_signature', [
            'path' => $tempImage,
            'height' => 100,
            'ratio' => false
        ]);

        unlink($tempImage);

        return $doc;
    }

    public function previewMou(Request $r)
    {
        $id = $r->query('id');
        $b = $r->all();
        $path = public_path('mou.docx');

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $now = Carbon::now()->format('YmdHisv');
        $doc = $this->makeMou($b, $id, $path);


        $outputPath = storage_path("app/public/mou_$now.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }


    public function uploadMou(Request $r)
    {

        $body = $r->all();
        // $doc = $r->file('file');
        $path = public_path('mou.docx');
        $doc = $this->makeMou($body, $body['opportunity_id'], $path);

        $path = $this->generatorService->generateUUID();
        $ext = "png";
        $user = $this->userService->getCurrentSessionUserInfo();

        $filename = "$path.$ext";
        $desc = "Upload mou";
        $topic = "mou";
        $mapTo = $user->id;

        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        $now = Carbon::now()->format('YmdHisv');
        $path = "filevendor/$year/$month/mou_$now.docx";
        $doc->saveAs(public_path("$path"));

        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        DB::table('opportunity_partner')->where('id', $body['opportunity_id'])->update([
            'contract_start_at' => Carbon::now(),
            'contract_end_at' => Carbon::now()->addYear(4)
        ]);

        return response()->json(['message' => 'success']);
    }


    public function makePks($body, $opportunityId, $templatePath)
    {
        $doc = new TemplateProcessor($templatePath);

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $opportunityId)->first();

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

        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $body['signature']);
        $imageData = base64_decode($base64Image);

        $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.png';
        file_put_contents($tempImage, $imageData);

        $doc->setImageValue('mitra_signature', [
            'path' => $tempImage,
            'height' => 100,
            'ratio' => false
        ]);

        unlink($tempImage);
        return $doc;
    }

    public function previewPks(Request $r)
    {
        $id = $r->query('id');
        $b = $r->all();
        $path = public_path('pks.docx');

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $now = Carbon::now()->format('YmdHisv');
        $doc = $this->makePks($b, $id, $path);

        $outputPath = storage_path("app/public/pks_$now.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function uploadPks(Request $r)
    {
        $body = $r->all();
        // $doc = $r->file('file');
        $path = public_path('pks.docx');
        $doc = $this->makeMou($body, $body['opportunity_id'], $path);

        $path = $this->generatorService->generateUUID();
        $ext = "png";
        $user = $this->userService->getCurrentSessionUserInfo();

        $filename = "$path.$ext";
        $desc = "Upload pks";
        $topic = "pks";
        $mapTo = $user->id;

        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        $now = Carbon::now()->format('YmdHisv');
        $path = "filevendor/$year/$month/pks_$now.docx";
        $doc->saveAs(public_path("$path"));

        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        return response()->json(['message' => 'success']);
    }

    //?user_id=&type=
    public function getOnboardingApprovalData(Request $r)
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

    public function getOnboardingApprovalDataLog(Request $r)
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


    public function processOnboardingApproval(Request $r)
    {
        $b = $r->all();

        $req = DB::table('approval_request')->where('ID', $b['id'])->first();
        if ($req && !$req->Level_Id) {
            $firstLevel = DB::table('approval_level')
                ->where('Request_Type', 'ONBOARDING')
                ->where('Level_Number', 1)
                ->first();

            if ($firstLevel) {
                DB::table('approval_request')->where('ID', $req->ID)->update([
                    'Level_Id' => $firstLevel->ID,
                ]);
            }
        }

        $approvalController = app(\App\Http\Controllers\ApprovalController::class);
        $result = $approvalController->processToNextLevel(
            (int) $b['id'],
            $b['action'],
            $b['notes'] ?? ''
        );

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        $req = DB::table('approval_request')->where('ID', $b['id'])->first();
        if ($req->Status === 'APPROVED') {
            $this->createMitraAccount($req->Opportunity_Partner_Id);
        }

        return response()->json(['success' => true, 'message' => 'success'], 200);
    }


    private function createMitraAccount($opportunityPartnerId)
    {
        $partner = DB::table('opportunity_partner')
            ->where('id', $opportunityPartnerId)
            ->first();

        if (!$partner || !$partner->lead_id) return;

        $existingUser = DB::table('users')
            ->where('Emp_ID', $partner->lead_id)
            ->first();

        if ($existingUser) return;

        $username = str_replace(' ', '_', strtolower($partner->full_name));
        $existingUsernames = DB::table('users')->pluck('username')->toArray();
        if (in_array($username, $existingUsernames)) {
            $username .= rand(100, 999);
        }

        $contact = $partner->register_as == 'individual'
            ? $partner->contact
            : $partner->pic_phone;

        if ($partner->lead_id && !$contact) {
            $opportunity = DB::table('opportunity')
                ->where("Task_ID", $partner->lead_id)
                ->first();
            $contact = $opportunity->Phone_Number ?? '';
        }

        DB::transaction(function () use ($partner, $username, $contact) {
            $userRowId = DB::table('users')->insertGetId([
                'username'   => $username,
                'password'   => bcrypt('geupartner123'),
                'Emp_ID'     => $partner->lead_id,
                'Position'   => "MITRA",
                'Priv_ID'    => "MITRA",
                'Location'   => 'GEU-HO',
                'Name'       => $partner->full_name,
                'Contact'    => $contact,
                'created_at' => now(),
                'Area'       => $partner->city ?? 'JABODETABEK',
                'Status'     => 'ACTIVE',
                'Department' => 'MITRA',
                'Company_ID' => 'GEU',
                'Sub_Menu'   => "MS00010,SM00047,SS00065,SI00231,SI00233,SM00048,SS00066,SI00237,SI00236",
            ]);

            DB::table('opportunity_partner')
                ->where('id', $partner->id)
                ->update([
                    'status'      => 'ACTIVE',
                    'user_row_id' => $userRowId,
                ]);
        });
    }

    public function forceApprove(Request $r)
    {
        $session = $this->userService->getCurrentSessionUserInfo();
        if (!in_array($session->Position, ['Administrator', 'BOD'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $requestId = $r->input('id');
        $req = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$req) {
            return response()->json(['success' => false, 'message' => 'Approval request not found'], 404);
        }

        try {
            DB::transaction(function () use ($req, $session) {
                DB::table('approval_request')->where('ID', $req->ID)->update([
                    'Status' => 'APPROVED',
                ]);

                DB::table('approval_log')->insert([
                    'Approver_Id'   => $session->id,
                    'Notes'         => 'Force approved (bypass)',
                    'Action'        => 'APPROVED',
                    'Request_Id'    => $req->ID,
                    'Level_Number'  => $req->Current_Level,
                    'Next_Level'    => $req->Current_Level + 1,
                ]);
            });

            $this->createMitraAccount($req->Opportunity_Partner_Id);

            return response()->json(['success' => true, 'message' => 'Force approve success']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
