<?php

namespace app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function uploadMou(Request $r)
    {
        $q = $r->query();
        $body = $r->all();
        $doc = $r->file('file');

        $path = $this->generatorService->generateFilename();
        $ext = $r->file('file')->getClientOriginalExtension();
        $user = $this->userService->getCurrentSessionUserInfo();

        $filename = "$path.$ext";
        $desc = "Upload request renewal";
        $topic = "renewal";
        $mapTo = $user->id;
        $doc->move(public_path('filevendor/2026/02'));
        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        return response()->json(['message' => 'success']);
    }

    //?user_id=&type=
    public function getOnboardingApprovalData(Request $r)
    {
        $q = $r->query();
        $type = $q['type'] ?? "%";
        $user_id = $q['user_id'] ?? "%";
        $apprId = $q['id'] ?? "%";
        $session = $this->userService->getCurrentSessionUserInfo();

        if (in_array($session->Position, ["SALES", "MANAGER", "BOD", "HEAD"])) {
            $d = DB::table('approval_request as appr')->select('appr.*', 'usr.Name', 'lv.Approver_Position')->join('users as usr', 'appr.Requester_Id', '=', 'usr.id')
                ->join('approval_level as lv', 'appr.Level_Id', '=', 'lv.ID')->where('appr.Request_Type', 'LIKE',  $type)->where('appr.Status', '=', 'NEED_APPROVAL')->where('lv.Approver_Position', '=', $session->Position)->get();

            // MITRA, SUPERADMIN
        } else {
            $d = DB::table('approval_request as appr')->select('appr.*', 'usr.Name')->join('users as usr', 'appr.Requester_Id', '=', 'usr.id')->where('appr.Request_Type', 'LIKE',  $type)->where('appr.ID', 'LIKE', $apprId)->orWhere('appr.Requester_Id', 'LIKE', $user_id)->get();
        }

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->ID);
            $row->addChild('cell', $p->Name);
            $row->addChild('cell', $p->Request_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', htmlspecialchars($p->Description));
            $row->addChild('cell', $p->Name);
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getOnboardingApprovalDataLog(Request $r)
    {
        $q = $r->query();
        $requestId = $q['request_id'] ?? "%";
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
            ->where('request.ID', 'LIKE', $requestId)
            // ->where('requester.id', 'LIKE', $userId)
            ->orderBy('log.ID', 'DESC')
            ->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->ID);
            $row->addChild('cell', $p->Action);
            $row->addChild('cell', htmlspecialchars($p->Level_Name));
            $row->addChild('cell', $p->Request_Type);
            $row->addChild('cell', $p->Requested_By);
            $row->addChild('cell', $p->Approved_By);
            $row->addChild('cell', $p->Action_Date);
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
