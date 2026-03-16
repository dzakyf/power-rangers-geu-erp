<?php

namespace app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use Carbon\Carbon;
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
        $desc = "Upload renewal";
        $topic = "renewal";
        $mapTo = $user->id;
        $doc->move(public_path('filevendor/2026/02'));
        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        DB::table('opportunity_partner')->where('id', $body['opportunity_id'])->update([
            'contract_end_at' => Carbon::now(),
            'contract_end_at' => Carbon::now()->addYear(4)
        ]);

        return response()->json(['message' => 'success']);
    }

    public function uploadPks(Request $r)
    {
        $q = $r->query();
        $body = $r->all();
        $doc = $r->file('file');

        $path = $this->generatorService->generateFilename();
        $ext = $r->file('file')->getClientOriginalExtension();
        $user = $this->userService->getCurrentSessionUserInfo();

        $filename = "$path.$ext";
        $desc = "Upload pks";
        $topic = "renewal";
        $mapTo = $user->id;
        $doc->move(public_path('filevendor/2026/02'));
        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        DB::table('opportunity_partner')->where('id', $body['opportunity_id'])->update([
            'contract_end_at' => Carbon::now(),
            'contract_end_at' => Carbon::now()->addYear(4)
        ]);

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
