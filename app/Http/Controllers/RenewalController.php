<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;

class RenewalController extends Controller
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

    public function indexTransferMembership()
    {
        return view('partner.renewal.transfer-membership-index');
    }

    public function getTransferMembershipData()
    {

        $session = $this->userService->getCurrentSessionUserInfo();

        $d = DB::table('opportunity_partner as op')->select('op.*', 'ptm.*', 'ptm.ID as Transfer_Id')
            ->join('partner_transfer_membership as ptm', 'op.id', '=', 'ptm.Opportunity_Id');

        if (in_array($session->Position, ["MITRA"])) {
            $d = $d->where('ptm.ID', '=', $session->id);
        }

        $xml = new SimpleXMLElement('<rows></rows>');
        $d = $d->get();

        $no = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('Opportunity_Id', $p->id);
            $row->addAttribute('Transfer_Application_Id', $p->Transfer_Id);
            $row->addChild('cell', $no);
            $row->addChild('cell', $p->lead_id);
            $row->addChild('cell', $p->full_name);
            $row->addChild('cell', $p->register_as);
            $row->addChild('cell', $p->contract_end_at);
            $row->addChild('cell', $p->full_name);
            $row->addChild('cell', $p->New_Mitra_Name);
            $row->addChild('cell', $p->Applied_At);

            $no++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function submitTransferMembershipApplication(Request $r)
    {
        $b = $r->all();

        $ins = DB::table('partner_transfer_membership')->insertGetId([
            'Opportunity_Id' => $b['opportunity_id'],
            "New_Mitra_Name" => $b['new_mitra_name'],
            "Type" => $b['type'],
            "Reason" => $b['reason'],
        ]);




        return response()->json(['message' => 'success']);
    }

    public function createTransferMembershipApplication()
    {
        return view('partner.renewal.transfer-membership-create-application');
    }

    public function indexRenewal()
    {
        return view('renewal.renewal');
    }

    public function indexTermination()
    {
        return view('partner.renewal.termination');
    }

    public function indexTransferMitraOwnership()
    {
        return view('renewal.transfer-ownership');
    }

    public function valuationHUBMitra()
    {
        return view('renewal.valuation');
    }

    public function indexApproval()
    {
        return view('renewal.approval');
    }


    public function createApproval()
    {
        return view('renewal.approval-create');
    }

    public function indexTerminationApproval()
    {
        return view('partner.renewal.termination-approval');
    }

    public function createTerminationApproval()
    {
        return view('partner.renewal.termination-approval-create');
    }


    public function makeTransferMembershipDocs($body)
    {

        $path = public_path('template-surat-transfer-kepemilikan-kemitraan.docx');
        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        $doc = new TemplateProcessor($path);

        $data = DB::table('opportunity_partner')->select('*')->where('ID', '=', $body['opportunity_id'])->first();

        $partner_name = $data->register_as == "individual" ? $data->full_name : $data->company_full_name;

        $ref_no = '43001/PTF/20262403';

        $doc->setValues([
            'ref_no' => $ref_no,
            'current_mitra_name' => $partner_name ?? '${contoh}',
            'new_mitra_name' => $body['new_mitra_name'],
            'reason' => $body['reason'],
            'now' => Carbon::now(),
            "type{$body['type']}" => '☒',
        ]);

        for ($i = 1; $i < 4; $i++) {
            if ($body['type'] == $i) {

                $doc->setValue(
                    "type{$i}",
                    '☒'
                );
                continue;
            }
            $doc->setValue(
                "type{$i}",
                '☐'
            );
        }

        if (!empty($body['signature'])) {

            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $body['signature']);
            $imageData = base64_decode($base64Image);

            $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.png';
            file_put_contents($tempImage, $imageData);

            $doc->setImageValue('signature', [
                'path' => $tempImage,
                'height' => 100,
                'ratio' => false
            ]);

            unlink($tempImage);
        }

        return [
            'doc' => $doc,
            'reference_no' => $ref_no,
        ];
    }

    public function updateTransferMembershipApplication(Request $r)
    {
        $b = $r->all();

        $id = DB::table('partner_transfer_membership as ptm')->where('ptm.ID', '=', $b['transfer_application_id'])->update([
            "Result" => $b['result'],
            "Effective_Transfer_At" => $b["effective_transfer_at"],
            "Fee" => $b['fee'],
            "Notes" => $b['notes'],
        ]);

        $ptm = DB::table('partner_transfer_membership as ptm')->select('ptm.Opportunity_Id')->where('ptm.ID', '=', $b['transfer_application_id'])->first();

        DB::table('opportunity_partner as op')->where('op.id', '=', $ptm->Opportunity_Id)->update([
            "status" => $b['status'],
        ]);

        return response()->json(["message" => "success"]);
    }

    public function previewTransferMembershipApplication(Request $r)
    {
        $b = $r->all();

        $doc = $this->makeTransferMembershipDocs($b);

        $code = Str::uuid()->toString();

        $outputPath = storage_path("app/public/filevendor/transfer_membership_$code.docx");

        $doc['doc']->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function finalizeTransferMembershipApplication(Request $r)
    {
        $b = $r->all();

        $doc = $this->makeTransferMembershipDocs($b);
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        $code = Str::uuid()->toString();

        $path = "filevendor/$year/$month/ptf_$code.docx";
        $outputPath = public_path("$path");
        $doc['doc']->saveAs($outputPath);

        $ext = "png";
        $user = $this->userService->getCurrentSessionUserInfo();


        // update data 
        $id = DB::table('partner_transfer_membership')->insertGetId([
            "Opportunity_Id" => $b['opportunity_id'],
            "Contract_No" => $doc['reference_no'],
            "New_Mitra_Name" => $b['new_mitra_name'],
            "Type" => $b['type'],
            "Reason" => $b['reason'],
        ]);

        $filename = "$path.$ext";
        $desc = "Upload dokumen transfer kemitraan";
        $topic = "transfer-membership";
        $mapTo = $id; //transfer application id

        $fileId = $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        return response()->json(['message' => 'success']);
    }


    public function uploadRenewalApplication(Request $r)
    {
        $q = $r->query();
        $body = $r->all();
        $doc = $r->file('file');

        $path = $this->generatorService->generateFilename();
        $ext = $r->file('file')->getClientOriginalExtension();
        $user = $this->userService->getCurrentSessionUserInfo();

        $id = DB::table('approval_request')->insertGetId([
            'Status' => 'NEED_APPROVAL',
            'Requester_Id' => $user->id,
            'Description' => "Initial Review",
            'Request_Type' => "RENEWAL",
            'Opportunity_Partner_Id' => ""
        ]);

        $currentLevel = 1;
        $logId = DB::table('approval_log')->insertGetId([
            'Approver_Id' => $this->userService->getCurrentSessionUserInfo()->id,
            'Notes' => "",
            'Action' => 'IN_PROGRESS',
            'Request_Id' => $id,
            'Level_Number' => $currentLevel,
            'Next_Level' => $currentLevel + 1
        ]);

        $filename = "$path.$ext";
        $desc = "Upload request renewal";
        $topic = "renewal";
        $mapTo = $user->id;
        $doc->move(public_path('filevendor/2026/02'));
        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        return response()->json(['message' => 'success']);
    }

    public function downloadRenewalApplication(Request $r)
    {
        $b = $r->query();
        $template = public_path('template-perpanjangan-kemitraan.docx');
        $doc = new TemplateProcessor($template);

        $session = $this->userService->getCurrentSessionUserInfo();
        $userId = $b['user_id'] ?? $session->id;
        $partner = $this->userService->getMitraById($userId);


        $month = Carbon::now()->translatedFormat('F');
        $year = Carbon::now()->year;
        $position = "";
        $contractEnd = "";
        $mitraFullname = $partner->Name;
        $referenceNo = "000/RS/PFS/APL/{$month}/{$year}";

        $doc->setValues([
            'reference_no' => $referenceNo,
            'contract_end' => $contractEnd,
            'now' => Carbon::now(),
            'mitra_fullname' => $mitraFullname,
            'position' => $position
        ]);

        if (!file_exists($template)) {
            abort(404, 'File tidak ditemukan!');
        }

        $uuid = Uuid::uuid4();

        $outputPath = storage_path("app/public/renewal_$uuid.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function downloadTerminationApplication(Request $r)
    {
        $b = $r->query();
        $template = public_path('template-surat-terminasi-kemitraan.docx');
        $doc = new TemplateProcessor($template);

        $session = $this->userService->getCurrentSessionUserInfo();
        $userId = $b['user_id'] ?? $session->id;
        $partner = $this->userService->getMitraById($userId);

        $month = Carbon::now()->translatedFormat('F');
        $year = Carbon::now()->year;
        $position = "";
        $contractEnd = "";
        $mitraFullname = $partner->Name;
        $referenceNo = "000/RS/PFS/APL/{$month}/{$year}";

        $doc->setValues([
            'reference_no' => $referenceNo,
            'contract_end' => $contractEnd,
            'now' => Carbon::now(),
            'mitra_fullname' => $mitraFullname,
            'position' => $position
        ]);

        if (!file_exists($template)) {
            abort(404, 'File tidak ditemukan!');
        }

        $uuid = Uuid::uuid4();

        $outputPath = storage_path("app/public/termination_$uuid.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }


    public function getRenewalData(Request $r)
    {
        $d = DB::table('opportunity_partner')->select('*')->get();
        $xml = new SimpleXMLElement('<rows></rows>');

        $no = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->id);
            $row->addChild('cell', $no);
            $row->addChild('cell', $p->lead_id);
            $row->addChild('cell', $p->full_name);
            $row->addChild('cell', $p->register_as);
            $row->addChild('cell', $p->contract_end_at);

            $no++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getTerminationDAta()
    {
        $d = DB::table('opportunity_partner')->select('*')->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $no = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->id);
            $row->addChild('cell', $no);
            $row->addChild('cell', $p->lead_id);
            $row->addChild('cell', $p->full_name);
            $row->addChild('cell', $p->register_as);
            $row->addChild('cell', $p->contract_end_at);
            $row->addChild('cell', $p->id);

            $no++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
