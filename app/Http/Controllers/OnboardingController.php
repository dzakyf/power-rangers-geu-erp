<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        if (!$data) {
            throw new \Exception('Partner not found');
        }

        $partner_name = $data->register_as == "individual" ? $data->full_name : ($data->company_name ?? $data->company_official_name ?? $data->full_name);
        $full_address = "$data->address, $data->pi_rt, $data->pi_rw, $data->pi_ward, $data->pi_subdistrict, $data->pi_city, $data->pi_province, $data->pi_postal_code";

        $hubType = in_array($data->package_option, ['option2', 'mobil'], true) ? 'Mobil' : ($data->package_option === 'option1' ? 'Booth' : '');

        $highest   = DB::table('approval_request')->max('ID');
        $mouNumber = $this->generatorService->generateApplicationCode($highest, 'MOU');

        $config = function ($key) {
            return (float) (DB::table('config_system')->where('Constraint', $key)->value('Value') ?? 0);
        };
        $money = function ($val) {
            return number_format((float) $val, 0, ',', '.');
        };

        $doc->setValues([
            'mou_number'     => $mouNumber,
            'hub_type'       => $hubType,
            'mitra_fullname' => $partner_name ?? '${contoh}',
            'partner_region' => $data->pi_city ?? '${contoh}',
            'signed_at'      => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'nama'           => $partner_name,
            'nik'            => $data->ktp,
            'NIK'            => $data->ktp,
            'full_address'   => $full_address,
            'downpayment'    => $money($config('ONBOARDING_DOWNPAYMENT')),
            'payment_phase2' => $money($config('ONBOARDING_SECOND_PHASE_PAYMENT')),
            'repayment'      => $money($config('ONBOARDING_THIRD_PHASE_PAYMENT')),
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

        // Return JSON so the signature UI can offer the download and refresh
        // the stepper (the binary download broke the AJAX handler).
        return response()->json([
            'success'      => true,
            'download_url' => url("partner-onboarding-download-doc?file=mou_$now.docx"),
        ]);
    }


    public function downloadGeneratedDoc(Request $r)
    {
        $file = (string) $r->query('file');
        if (!preg_match('/^(mou|pks)_[0-9]+\.docx$/', $file)) {
            abort(404);
        }
        $path = storage_path("app/public/$file");
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function uploadMou(Request $r)
    {
        try {
            $body = $r->all();
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

            $this->advanceOnboardingStage($body['opportunity_id'], 'MOU', 'INVOICE', 'MOU ditandatangani');

            return response()->json(['success' => true, 'message' => 'success']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function makePks($body, $opportunityId, $templatePath)
    {
        $doc = new TemplateProcessor($templatePath);

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $opportunityId)->first();
        if (!$data) {
            throw new \Exception('Partner not found');
        }

        $partner_name = $data->register_as == "individual" ? $data->full_name : ($data->company_name ?? $data->company_official_name ?? $data->full_name);
        $signer_name  = $data->register_as === 'individual' ? $data->full_name : ($data->pic ?? $partner_name);
        $full_address = "$data->address, $data->pi_rt, $data->pi_rw, $data->pi_ward, $data->pi_subdistrict, $data->pi_city, $data->pi_province, $data->pi_postal_code";

        $config = function ($key) {
            return (float) (DB::table('config_system')->where('Constraint', $key)->value('Value') ?? 0);
        };
        $money = function ($val) {
            return number_format((float) $val, 0, ',', '.');
        };

        $doc->setValues([
            'partner_name'    => $partner_name ?? '${contoh}',
            'signed_at'       => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'partner_region'  => $data->pi_city ?? '${contoh}',
            'ktp'             => $data->ktp,
            'nama'            => $partner_name,
            'alamat'          => $full_address,
            'pic_name'        => $signer_name,
            'npwp'            => $data->npwp,
            'membership_term' => 4,
            'membership_fee'  => $money($config('PKS_MEMBERSHIP_FEE')),
            'warehouse_fee'   => $money($config('WAREHOUSE_FEE')),
            'inventory_fee'   => $money($config('INVENTORY_FEE')),
            'namapaket'       => $data->package_option ?? '',
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

        // Return JSON so the signature UI can offer the download and refresh
        // the stepper (the binary download broke the AJAX handler).
        return response()->json([
            'success'      => true,
            'download_url' => url("partner-onboarding-download-doc?file=pks_$now.docx"),
        ]);
    }

    public function uploadPks(Request $r)
    {
        try {
            $body = $r->all();
            $path = public_path('pks.docx');
            $doc = $this->makePks($body, $body['opportunity_id'], $path);

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

            $this->advanceOnboardingStage($body['opportunity_id'], 'PKS', 'SURVEY', 'PKS ditandatangani');

            return response()->json(['success' => true, 'message' => 'success']);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

        $req = DB::table('approval_request')->where('ID', (int) ($b['id'] ?? 0))->first();
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

        // Idempotent: an already-APPROVED request only needs its MITRA
        // account (re)created - re-running processToNextLevel would only
        // yield "Approval level not found" and block approve AND reject.
        if (!$req || $req->Status !== 'APPROVED') {
            $result = $approvalController->processToNextLevel(
                (int) ($b['id'] ?? 0),
                $b['action'],
                $b['notes'] ?? ''
            );

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            $req = DB::table('approval_request')->where('ID', (int) ($b['id'] ?? 0))->first();
        }

        if ($req && $req->Status === 'APPROVED') {
            try {
                $this->createMitraAccount($req->Opportunity_Partner_Id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('MITRA account creation failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Approval tersimpan, namun pembuatan akun MITRA gagal: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['success' => true, 'message' => 'success'], 200);
    }



    public function advanceOnboardingStage($opportunityId, $currentStage, $nextStage, $notes = '')
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

        DB::transaction(function () use ($opportunityId, $currentStage, $nextStage, $notes, $session, $stageLabelMap) {
            $currentLogData = [
                'Stage' => $currentStage,
                'Stage_Label' => $stageLabelMap[$currentStage] ?? $currentStage,
                'Status' => 'completed',
                'Completed_At' => now(),
                'Completed_By' => $session->id,
                'Notes' => $notes,
                'Updated_At' => now(),
            ];

            $existing = DB::table('partner_onboarding_log')
                ->where('Opportunity_Id', $opportunityId)
                ->where('Stage', $currentStage)
                ->first();

            if ($existing) {
                DB::table('partner_onboarding_log')
                    ->where('Id', $existing->Id)
                    ->update($currentLogData);
            } else {
                $currentLogData['Opportunity_Id'] = $opportunityId;
                $currentLogData['Created_At'] = now();
                DB::table('partner_onboarding_log')->insert($currentLogData);
            }

            $stageOrder = ['DOCUMENT_CHECK', 'MOU', 'INVOICE', 'PKS', 'SURVEY', 'SETUP', 'ACTIVE'];
            $currentStage = DB::table('opportunity_partner')
                ->where('id', $opportunityId)
                ->value('onboarding_stage');
            $currentIndex = $currentStage ? array_search($currentStage, $stageOrder, true) : false;
            $nextIndex = array_search($nextStage, $stageOrder, true);

            // Only move the stage strictly forward; a fully onboarded
            // (ACTIVE) partner is never pulled backwards by a redone step.
            if ($currentIndex === false || $nextIndex > $currentIndex) {
                DB::table('opportunity_partner')
                    ->where('id', $opportunityId)
                    ->update([
                        'onboarding_stage' => $nextStage,
                        'onboarding_stage_updated_at' => now(),
                        'onboarding_stage_updated_by' => $session->id,
                    ]);
            }

            $nextLogData = [
                'Opportunity_Id' => $opportunityId,
                'Stage' => $nextStage,
                'Stage_Label' => $stageLabelMap[$nextStage] ?? $nextStage,
                'Status' => 'in_progress',
                'Created_At' => now(),
                'Updated_At' => now(),
            ];

            $existingNext = DB::table('partner_onboarding_log')
                ->where('Opportunity_Id', $opportunityId)
                ->where('Stage', $nextStage)
                ->first();

            if ($existingNext) {
                unset($nextLogData['Opportunity_Id']);
                unset($nextLogData['Created_At']);
                DB::table('partner_onboarding_log')
                    ->where('Id', $existingNext->Id)
                    ->update($nextLogData);
            } else {
                DB::table('partner_onboarding_log')->insert($nextLogData);
            }
        });
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

        // Uniqueness guard (restored): a duplicate username/email made the
        // onboarding approval fail with a MySQL duplicate-entry error after
        // the approval itself had already been committed.
        $base = $partner->email ?: ((str_replace(' ', '_', strtolower((string) $partner->full_name)) ?: 'mitra') . rand(100, 999));
        $username = $base;
        $attempt = 0;
        while (DB::table('users')->where('username', $username)->exists()) {
            $attempt++;
            if ($attempt >= 10) {
                $username = $base . '_' . substr((string) time(), -6);
                break;
            }
            $username = $base . rand(100, 999);
        }

        // users.email has a unique index; fall back to the (unique) username
        // when the partner email is empty or already taken by another account.
        $email = $partner->email;
        if (!$email || DB::table('users')->where('email', $email)->exists()) {
            $email = $username;
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

        DB::transaction(function () use ($partner, $username, $email, $contact) {
            $userRowId = DB::table('users')->insertGetId([
                'username'   => $username,
                'email'      => $email,
                'password'   => bcrypt('geupartner123'),
                'Emp_ID'     => $partner->lead_id,
                'Position'   => "MITRA",
                'Priv_ID'    => "MITRA",
                'Location'   => 'GEU-HO',
                'Name'       => $partner->full_name ?: ($partner->email ?: 'MITRA'),
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
