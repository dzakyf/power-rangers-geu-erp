<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\MasterWarehouseAccount;
use App\MasterWarehouseProfitLoss;
use App\Services\GeneratorService;
use App\Services\UploadService;
use App\Services\UserService;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerJournal;
use App\WarehousePartnerSupplies;
use App\WarehouseStockRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;

class ProfitSharingController extends Controller
{
    protected $userService;
    protected $uploaderService;
    protected $generatorService;
    public function __construct(UserService $u, GeneratorService $g, UploadService $up)
    {

        $this->generatorService = $g;
        $this->uploaderService = $up;
        $this->userService = $u;
        Carbon::setLocale('id');
    }
    public function indexProfitSharing()
    {
        return view('partner.profit-sharing');
    }

    public function indexProfitSharingXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }
        $getPartner = DB::table('opportunity_partner')->get();
        foreach ($getPartner as $key => $value) {
            $usname[$value->lead_id] = $value->full_name;
        }
        $nomor = 1;

        if ($priv == 'PARTNER') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }

        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function lkMonthlyProfitSharing(Request $request)
    {
        return view('partner.profit-sharing-lk-monthly');
    }

    public function lkProfitSharingXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $nomor = 1;
        $calculate = [];

        //Hitung Pendaptan
        $calculate[401][40001] = 0;
        $percentage[401] = 100;

        // harga transfer
        $calculate[402][40002] = MasterStockPrice::where('date_for', 'LIKE', "$request->date%")->avg('price');

        // quantity
        $calculate[501][51001] = WarehouseStockRequest::where('warehouse_task_id', $request->Task)->where('request_date', 'LIKE', $request->date . '%')->sum('request_qty');

        // harga beli
        // $calculate[501][50002] = WarehousePartnerSupplies::where('received_date', 'LIKE', $request->date . '%')->avg('price');

        $whs = WarehousePartnerSupplies::where("warehouse_task_id", $request->Task)->where('received_date', 'LIKE', $request->date . '%')->get();
        $supplies = [];
        $supplies['avg'] = 0;
        foreach ($whs as $key => $value) {
            $month = date('Y-m', strtotime($value->received_date));
            if (!isset($supplies['qty'])) {
                $supplies['qty'] = 0;
            }
            if (!isset($supplies['avg'])) {
                $supplies['avg'] = $value->price;
            }
            $supplies['avg'] = (($supplies['avg'] * $supplies['qty']) + $value->total) / ($supplies['qty'] + $value->received_qty);
        }

        // harga beli
        $calculate[501][51002] = $supplies['avg'];

        // pendapatan
        $calculate[401][40001] = $calculate[402][40002] * $calculate[501][51001];

        $calculate[501]['NULL'] = $calculate[501][51001] * $calculate[501][51002];
        // $percentage[501] = $calculate[501]['NULL'] / $calculate[401][40001];
        

        // Hitung Opex
        $getAccount = MasterWarehouseAccount::where('group_account', '>', 501)->get();
        foreach ($getAccount as $key => $value) {
            if (!isset($calculate[$value->group_account])) {
                $calculate[$value->group_account] = [];
            }
            if (!isset($calculate[$value->group_account][$value->account_no])) {
                $calculate[$value->group_account][$value->account_no] = 0;
            }

            $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)
                ->where('posting_date', 'LIKE', $request->date . '%')
                ->where('account_no', $value->account_no)
                ->where('group_account', $value->group_account)
                ->sum('nominal');

            $calculate[$value->group_account][$value->account_no] += $getOpex;
        }

        $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502]);

        $get = MasterWarehouseProfitLoss::where('type', $request->type)->get();
        $normal = [];
        foreach ($get as $key => $value) {
            if ($value->Normal != '' && $value->Normal != null) {
                $normal[$value->Judul] = $value->Normal;
            }
        }

        foreach ($get as $key => $value) {
            if ($value->Level == 2) {
                if ($value->Rumus != '') {
                    $exp = explode(',', $value->Rumus);
                    if (count($exp) > 0) {
                        if (!isset($calculate[$value->Judul])) {
                            $calculate[$value->Judul]['NULL'] = 0; 
                        }
                        if ($value->Normal == 'CREDIT') {
                            $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) - array_sum($calculate[$exp[1]])) - (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                        }else {
                            $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) + array_sum($calculate[$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                        }
                        // $percentage[$value->Judul] = $calculate[$value->Judul]['NULL']/$calculate[401][40001] * 100;
                    }
                }
            }else if($value->Level == 3) {
                $exp = explode(',', $value->Rumus);
                if (!isset($calculate[$value->Judul])) {
                    $calculate[$value->Judul]['NULL'] = 0; 
                }
                $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) * 60) / 100;
            }
        }

        $rev = $calculate[401][40001];
        if ($rev == 0) {
            $rev = 1;
        }
        foreach ($get as $key => $Fetch) {
            $bold = "";
            $style = "style='padding-left:".$Fetch->Indentation."px !important;'";
            if ($Fetch->Bold != null) {
                $style = "style='padding-left:".$Fetch->Indentation."px !important;font-weight:bold;'";
                $bold = "style='font-weight:bold;'";
            }

            echo "<row id=\"$key\">";
            echo "<cell $style></cell>";
            echo "<cell $style>" . htmlspecialchars($Fetch->Description) . "</cell>";
            if ($Fetch->Judul != '' && $Fetch->Judul != null) {
                if ($Fetch->Code != '' && $Fetch->Code != null) {
                    echo "<cell $style>" . htmlspecialchars($calculate[$Fetch->Judul][$Fetch->Code]) . "</cell>";
                    echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                    if ($Fetch->Unit_Name != 'Kg')
                        echo "<cell $bold>" . htmlspecialchars(round($calculate[$Fetch->Judul][$Fetch->Code]/$rev * 100, 2)) . "%</cell>";
                }else {
                    if (isset($calculate[$Fetch->Judul]['NULL'])) {
                        echo "<cell $style>" . htmlspecialchars($calculate[$Fetch->Judul]['NULL']) . "</cell>";
                        echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                        if ($Fetch->Unit_Name != 'Kg')
                            echo "<cell $bold>" . htmlspecialchars(round($calculate[$Fetch->Judul]['NULL']/$rev * 100, 2)) . "%</cell>";
                    }else {
                        echo "<cell $style>" . htmlspecialchars(array_sum($calculate[$Fetch->Judul])) . "</cell>";
                        echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                        if ($Fetch->Unit_Name != 'Kg')
                            echo "<cell $bold>" . htmlspecialchars(round(array_sum($calculate[$Fetch->Judul])/$rev * 100,2)) . "%</cell>";
                    }
                }
            }else {
                if ($Fetch->Code != '' && $Fetch->Code != null && $Fetch->Rumus != '' && $Fetch->Rumus != null) {
                    echo "<cell $style>".$calculate[$Fetch->Rumus][$Fetch->Code]."</cell>";
                    echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                    if ($Fetch->Unit_Name != 'Kg')
                        echo "<cell $bold>".round($calculate[$Fetch->Rumus][$Fetch->Code]/$rev * 100, 2)."%</cell>";
                }else {
                    echo "<cell $style></cell>";
                    echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                    echo "<cell $bold></cell>";
                }
            }
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function lkAnnuallyProfitSharing()
    {
        return view('partner.profit-sharing-lk-annually');
    }


    //?user_id=&type=
    public function getProfitSharingApprovalData(Request $r)
    {
        $q = $r->query();
        $type = $q['type'] ?? "%";
        $user_id = $q['user_id'] ?? "%";

        $d = DB::table('approval_request as appr')->select('appr.*', 'usr.Name')->join('users as usr', 'appr.Requester_Id', '=', 'usr.id')->where('appr.Request_Type', '=', $type)->where('appr.Request_Type', 'LIKE',  $type)->where('usr.id', 'LIKE', $user_id)->get();


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

    public function getProfitSharingApprovalDataLog(Request $r)
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
            ->join('approval_level as level', 'log.Level_Number', '=', 'level.ID')
            ->join('users as requester', 'requester.id', '=', 'request.Requester_Id')
            ->leftJoin('users as approver', 'approver.id', '=', 'log.Approver_Id')
            ->where('request.ID', '=', $requestId)
            ->where('requester.id', '=', $userId)
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


    public function partnerSubmitProfitSharingApplication(Request $r)
    {
        $q = $r->query();
        $body = $r->all();
        $doc = $r->file('Doc');

        $path = $this->generatorService->generateFilename();
        $ext = $r->file('Doc')->getClientOriginalExtension();
        $user = $this->userService->getCurrentSessionUserInfo();

        $id = DB::table('approval_request')->insertGetId([
            'Status' => 'IN_PROGRESS',
            'Requester_Id' => $user->id,
            'Description' => "Initial Review",
            'Request_Type' => "PROFIT_SHARING",
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
        $desc = "Upload request profit sharing";
        $topic = "profit-sharing";
        $mapTo = $user->id;
        $doc->move(public_path('filevendor/2026/02'));
        $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

        return response()->json(['message' => 'success']);
    }

    // ?user_id=
    /** TODO: 
     * Ambil nominal dari laporan keuangan 
     * Input tanda tangan digital
     * */
    public function downloadProfitSharingApplication(Request $r)
    {
        $b = $r->query();
        $template = public_path('template-pengajuan-profit-sharing.docx');
        $doc = new TemplateProcessor($template);

        $session = $this->userService->getCurrentSessionUserInfo();
        $userId = $b['user_id'] ?? $session->id;
        dd($userId);
        $partner = $this->userService->getMitraById($userId)[0];

        $nominal = 0;
        $location = $partner->City;
        $month = Carbon::now()->translatedFormat('F');
        $year = Carbon::now()->year;
        $bankName = $partner->Bank_Name;
        $accountName = $partner->Bank_Account_Name;
        $accountNo = $partner->Bank_Account_No;
        $mitraFullname = $partner->Name;
        $referenceNo = "000/RS/PFS/APL/{$month}/{$year}";

        $doc->setValues([
            'reference_no' => $referenceNo,
            'location' => $location,
            'month' => $month,
            'year' => $year,
            'nominal' => $nominal,
            'bank_name' => $bankName,
            'account_name' => $accountName,
            'account_no' => $accountNo,
            'mitra_fullname' => $mitraFullname
        ]);

        if (!file_exists($template)) {
            abort(404, 'File tidak ditemukan!');
        }

        $uuid = Uuid::uuid4();

        $outputPath = storage_path("app/public/mou_$uuid.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}
