<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        // throw new \Exception('Not implemented');
    }

    public function indexRenewal()
    {
        return view('renewal.renewal');
    }

    public function indexExitTermination()
    {
        return view('renewal.termination');
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

        $outputPath = storage_path("app/public/mou_$uuid.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function getApprovalData() {}


    public function getRenewalData(Request $r)
    {
        $d = DB::table('opportunity_partner')->select('*')->get();
        $xml = new SimpleXMLElement('<rows></rows>');

        $no = 1;
        foreach ($d as $p) {
            $row = $xml->addChild('row');
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
