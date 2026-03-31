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
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;

class ProfitSharingController extends Controller
{
    protected $userService;
    protected $uploaderService;
    protected $generatorService;
    protected $now;
    protected $currDate;
    protected $year;
    protected $month;

    public function __construct(UserService $u, GeneratorService $g, UploadService $up)
    {

        $this->generatorService = $g;
        $this->uploaderService = $up;
        $this->userService = $u;
        $this->now = Carbon::now();
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->format('m');

        $this->currDate = Carbon::now()->format('Y-m-d');
        Carbon::setLocale('id');
    }
    public function indexProfitSharing()
    {
        return view('partner.profit-sharing');
    }

    public function indexRoyaltyPayment()
    {
        return view('partner.profit-sharing.royalty-history-index');
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

        if ($priv == 'MITRA') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }

        foreach ($get as $key => $Fetch) {
            $type = ucwords($Fetch->warehouse_type);
            if ($type == '') {
                $type = 'Booth';
            }
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($type) . "</cell>";
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
                            if ($value->Judul == '701') {
                                $calculate[$value->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                            }
                        } else {
                            $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) + array_sum($calculate[$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                        }
                        // $percentage[$value->Judul] = $calculate[$value->Judul]['NULL']/$calculate[401][40001] * 100;
                    }
                }
            } else if ($value->Level == 3) {
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
            $style = "style='padding-left:" . $Fetch->Indentation . "px !important;'";
            if ($Fetch->Bold != null) {
                $style = "style='padding-left:" . $Fetch->Indentation . "px !important;font-weight:bold;'";
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
                        echo "<cell $bold>" . htmlspecialchars(round($calculate[$Fetch->Judul][$Fetch->Code] / $rev * 100, 2)) . "%</cell>";
                } else {
                    if (isset($calculate[$Fetch->Judul]['NULL'])) {
                        echo "<cell $style>" . htmlspecialchars($calculate[$Fetch->Judul]['NULL']) . "</cell>";
                        echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                        if ($Fetch->Unit_Name != 'Kg')
                            echo "<cell $bold>" . htmlspecialchars(round($calculate[$Fetch->Judul]['NULL'] / $rev * 100, 2)) . "%</cell>";
                    } else {
                        echo "<cell $style>" . htmlspecialchars(array_sum($calculate[$Fetch->Judul])) . "</cell>";
                        echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                        if ($Fetch->Unit_Name != 'Kg')
                            echo "<cell $bold>" . htmlspecialchars(round(array_sum($calculate[$Fetch->Judul]) / $rev * 100, 2)) . "%</cell>";
                    }
                }
            } else {
                if ($Fetch->Code != '' && $Fetch->Code != null && $Fetch->Rumus != '' && $Fetch->Rumus != null) {
                    echo "<cell $style>" . $calculate[$Fetch->Rumus][$Fetch->Code] . "</cell>";
                    echo "<cell $bold>" . htmlspecialchars($Fetch->Unit_Name) . "</cell>";
                    if ($Fetch->Unit_Name != 'Kg')
                        echo "<cell $bold>" . round($calculate[$Fetch->Rumus][$Fetch->Code] / $rev * 100, 2) . "%</cell>";
                } else {
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

    public function overviewProfitSharing(Request $request)
    {
        $getWarehouse = WarehousePartner::where('warehouse_task_id', $request->Task)->first();
        $user = DB::table('users')->where("Emp_ID", $getWarehouse->partner_task_id)->first()->id ?? 0;
        return view('partner.profit-sharing-overview', compact('user'));
    }

    public function overviewUserProfitSharing()
    {
        return view('partner.profit-sharing-overview-user');
    }

    public function overviewProfitSharingXml(Request $request)
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

        $getStartProjection = DB::table('projections')->where("User_ID", $request->mitra)->select('Created_At')->first();
        if ($getStartProjection) {
            $tahunPembuatan = [
                date('Y', strtotime($getStartProjection->Created_At)),
                date('Y', strtotime($getStartProjection->Created_At . ' +1 year')),
                date('Y', strtotime($getStartProjection->Created_At . ' +2 years')),
                date('Y', strtotime($getStartProjection->Created_At . ' +3 years')),
                date('Y', strtotime($getStartProjection->Created_At . ' +4 years')),
            ];
        } else {
            $tahunPembuatan = [
                date('Y'),
                date('Y', strtotime('+1 year')),
                date('Y', strtotime('+2 years')),
                date('Y', strtotime('+3 years')),
                date('Y', strtotime('+4 years')),
            ];
        }

        $nomor = 1;

        // Hitung Opex
        $getAccount = MasterWarehouseAccount::where('group_account', '>', 501)->get();
        $get = MasterWarehouseProfitLoss::where('type', $request->type)->get();

        $calculate = [];
        for ($i = 1; $i <= 12; $i++) {
            $calculate[$i][401][40001] = 0;
            // $percentage[401] = 100;

            // harga transfer
            $calculate[$i][402][40002] = MasterStockPrice::where('date_for', 'LIKE', "$request->date%")->avg('price');

            // quantity
            $calculate[$i][501][51001] = WarehouseStockRequest::where('warehouse_task_id', $request->Task)->where('request_date', 'LIKE', $request->date . '%')->sum('request_qty');

            // harga beli
            // $calculate[$i][501][50002] = WarehousePartnerSupplies::where('received_date', 'LIKE', $request->date . '%')->avg('price');

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
            $calculate[$i][501][51002] = $supplies['avg'];

            // pendapatan
            $calculate[$i][401][40001] = $calculate[$i][402][40002] * $calculate[$i][501][51001];

            $calculate[$i][501]['NULL'] = $calculate[$i][501][51001] * $calculate[$i][501][51002];


            foreach ($getAccount as $key => $value) {
                if (!isset($calculate[$i][$value->group_account])) {
                    $calculate[$i][$value->group_account] = [];
                }
                if (!isset($calculate[$i][$value->group_account][$value->account_no])) {
                    $calculate[$i][$value->group_account][$value->account_no] = 0;
                }

                $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)
                    ->where('posting_date', 'LIKE', $request->date . '-' . sprintf('%02d', $i) . '%')
                    ->where('account_no', $value->account_no)
                    ->where('group_account', $value->group_account)
                    ->sum('nominal');

                $calculate[$i][$value->group_account][$value->account_no] += $getOpex;
            }

            //Hitung Pendaptan
            // $percentage[501] = $calculate[$i][501]['NULL'] / $calculate[$i][401][40001];

            $calculate[$i][599]['NULL'] = $calculate[$i][501]['NULL'] + array_sum($calculate[$i][502]);

            foreach ($get as $key => $value) {
                if ($value->Level == 2) {
                    if ($value->Rumus != '') {
                        $exp = explode(',', $value->Rumus);
                        if (count($exp) > 0) {
                            if (!isset($calculate[$i][$value->Judul])) {
                                $calculate[$i][$value->Judul]['NULL'] = 0;
                            }
                            if ($value->Normal == 'CREDIT') {
                                $calculate[$i][$value->Judul]['NULL'] += (array_sum($calculate[$i][$exp[0]]) - array_sum($calculate[$i][$exp[1]])) - (isset($exp[2]) ? array_sum($calculate[$i][$exp[2]]) : 0);
                            } else {
                                $calculate[$i][$value->Judul]['NULL'] += (array_sum($calculate[$i][$exp[0]]) + array_sum($calculate[$i][$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$i][$exp[2]]) : 0);
                            }
                            // $percentage[$value->Judul] = $calculate[$i][$value->Judul]['NULL']/$calculate[$i][401][40001] * 100;
                        }
                    }
                } else if ($value->Level == 3) {
                    $exp = explode(',', $value->Rumus);
                    if (!isset($calculate[$i][$value->Judul])) {
                        $calculate[$i][$value->Judul]['NULL'] = 0;
                    }
                    $calculate[$i][$value->Judul]['NULL'] += (array_sum($calculate[$i][$exp[0]]) * 60) / 100;
                }
            }
        }

        $tahunKe = 0;
        for ($i = 0; $i < 4; $i++) {
            $j = $i + 1;
            if ($tahunPembuatan[$i] <= $request->date && $tahunPembuatan[$j] > $request->date) {
                $tahunKe = $j;
            }
        }

        $getProjection = DB::table('projections')->whereIn('Description', ['Pendapatan', 'Bagi Hasil Mitra'])->where('Hub_Type', $request->type)->where('User_Id', $request->mitra)->where('Year', $tahunKe)->get();
        $proyeksi = [];
        foreach ($getProjection as $key => $value) {
            for ($i = 1; $i <= 12; $i++) {
                $value = json_decode(json_encode($value), true);
                $proyeksi[$i][strtolower($value['Description'])] = $value['Month_' . $i . '_Total'];
            }
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!isset($proyeksi[$i])) {
                continue;
            }
            echo "<row id='$i'>";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . date('F', strtotime($request->date . '-' . sprintf('%02d', $i) . '-01')) . "</cell>";
            echo "<cell>" . $proyeksi[$i]['pendapatan'] . "</cell>";
            echo "<cell>" . $calculate[$i][401][40001] . "</cell>";
            echo "<cell>" . ($calculate[$i][599]['NULL'] + array_sum($calculate[$i][601])) . "</cell>";
            echo "<cell>" . $proyeksi[$i]['bagi hasil mitra'] . "</cell>";
            echo "<cell>" . $calculate[$i][801]['NULL'] . "</cell>";
            echo "<cell>" . $calculate[$i][401][40001] / $proyeksi[$i]['pendapatan'] . "</cell>";
            echo "<cell>" . $calculate[$i][801]['NULL'] / $proyeksi[$i]['bagi hasil mitra'] . "</cell>";
            echo "<cell></cell>";
            echo "<cell>" . ($calculate[$i][801]['NULL'] - ($calculate[$i][599]['NULL'] + array_sum($calculate[$i][601])) > 0 ? 'Surplis' : 'Defisit') . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }


    //?user_id=&type=
    public function getProfitSharingApprovalData(Request $r)
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


    public function partnerSubmitProfitSharingApplication(Request $r)
    {
        $q = $r->query();
        $body = $r->all();
        $doc = $r->file('file');

        $path = $this->generatorService->generateFilename();
        $ext = $r->file('file')->getClientOriginalExtension();
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
        $partner = $this->userService->getMitraById($userId);


        $nominal = 0;
        $location = $partner->City;
        $date = Carbon::now()->day;
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
            'date' => $date,
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

        $outputPath = storage_path("app/public/template-royalti-application_$uuid.docx");

        $doc->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function makeProfitSharingApplication($b, $templatePath)
    {
        $doc = new TemplateProcessor($templatePath);

        $session = $this->userService->getCurrentSessionUserInfo();
        $userId = $b['user_id'] ?? $session->id;
        $partner = $this->userService->getMitraById($userId);

        $nominal = 0;
        $location = $partner->City;
        $date = Carbon::now()->day;
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
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'nominal' => $nominal,
            'bank_name' => $bankName,
            'account_name' => $accountName,
            'account_no' => $accountNo,
            'mitra_fullname' => $mitraFullname
        ]);

        $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $b['signature']);
        $imageData = base64_decode($base64Image);

        $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.png';
        file_put_contents($tempImage, $imageData);

        $doc->setImageValue('mitra_signature', [
            'path' => $tempImage,
            'height' => 100,
            'ratio' => false
        ]);

        unlink($tempImage);

        return ['doc' => $doc, 'contract_no' => $referenceNo];
    }

    public function previewProfitSharingApplication(Request $r)
    {
        $id = $r->query('id');
        $b = $r->all();
        $path = public_path('template-pengajuan-profit-sharing.docx');

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $now = Carbon::now()->format('YmdHisv');
        $doc = $this->makeProfitSharingApplication($b, $path);


        $outputPath = storage_path("app/public/royalti-application-_$now.docx");

        $doc['doc']->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }

    public function finalizeProfitSharingApplication(Request $r)
    {
        $body = $r->all();

        $session = $this->userService->getCurrentSessionUserInfo();
        // $doc = $r->file('file');
        $path = public_path('template-pengajuan-profit-sharing.docx');

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $doc = $this->makeProfitSharingApplication($body, $path);

        $path = $this->generatorService->generateFilename();
        $ext = "png";
        $user = $this->userService->getCurrentSessionUserInfo();

        $filename = "$path.$ext";
        $desc = "Upload pengajuan royalti";
        $topic = "profit-sharing";
        $mapTo = $user->id;

        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        $now = Carbon::now()->format('YmdHisv');
        $path = "filevendor/$year/$month/royalti-application-$now.docx";
        $doc['doc']->saveAs(public_path("$path"));


        try {
            DB::beginTransaction();
            $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

            $taskId = "XXXX";

            $partner = DB::table('opportunity_partner as op')
                ->select('op.*')
                ->join('users as usr', 'usr.id', '=', 'op.user_row_id')
                ->where('op.user_row_id', '=', $session->id)
                ->get();

            // dd($partner);

            //TODO: ambil dari laporan keuangan
            $royalti = 9900000;
            $total = ($royalti * 0.11) + $royalti;

            $ap = DB::table('erp_ksps.account_payable')->insertGetId([
                'Task_ID'                => $taskId,        // ID tugas/tiket internal sistem
                'Location'               => $partner[0]->city,                      // lokasi cabang/gudang terkait transaksi
                'Department'             => '',                      // departemen yang mengajukan pembayaran
                'PIC'                    => '',                      // penanggung jawab transaksi dari internal
                'Purchase_Category'      => '',                      // kategori pembelian (royalti, operasional, dll)
                'Purchase_Sub_Category'  => '',                      // sub-kategori pembelian
                'Purchase_Type'          => '',                      // jenis pembelian (direct, indirect, dll)
                'Reference'              => $doc['contract_no'],   // nomor kontrak atau nomor PO sebagai referensi
                'Description'            => "Pembayaran royalti $year", // keterangan singkat transaksi
                'Subtotal'               => $royalti,             // nilai sebelum pajak (DPP - Dasar Pengenaan Pajak)
                'PPN'                    => $royalti * 0.11,              // Pajak Pertambahan Nilai 11% dari DPP
                'PPN_Type'               => null,                    // jenis PPN (masukan/keluaran), null jika tidak berlaku
                'PPH'                    => 0,               // PPh 23 dipotong oleh pembayar, 2% dari bruto
                'Total'                  => $total,             // total yang dibayarkan = Subtotal + PPN - PPH
                'Remain_Val'             => $total,             // sisa nilai yang belum dibayar, awal = Total
                'Status'                 => 'WAITING_PAYMENT',               // status pembayaran: PENDING = belum diproses
                'Dateinsert'             => $now,                    // tanggal data diinput ke sistem
                'Created_By'             => $session->username,                      // user yang membuat pengajuan
                'Date_Received'          => $this->currDate,         // tanggal invoice diterima oleh tim finance
                'Received_By'            => '',                      // nama penerima invoice dari vendor
                'Inv_Type'               => 'SINGLE',                // tipe invoice: SINGLE = satu invoice per transaksi
                'Due_Date'               => '',                      // tanggal jatuh tempo pembayaran ke vendor
                'Inv_Vendor'             => '',                      // nomor invoice yang diterbitkan oleh vendor
                'Subject_Payment'        => "Pembayaran Royalti $month $year", // judul/perihal pembayaran
                'Inv_Vendor_Date'        => '',                      // tanggal terbit invoice dari vendor
                'Vendor_ID'              => '',                      // ID vendor/mitra di sistem
                'Vendor_Name'            => '',                      // nama vendor/mitra penerima pembayaran
                'Finance_Ref'            => '',                      // nomor referensi internal tim finance
                'Finance_Received_Date'  => '',                      // tanggal finance menerima dan memverifikasi dokumen
                'Finance_Received_By'    => '',                      // nama staff finance yang menerima dokumen
                'Manual'                 => 0,                       // flag apakah entry manual (1) atau otomatis sistem (0)
                'Paid'                   => 0,                       // flag pembayaran: Jumlah yang sudah dibayarkan ke mitra 
                'PIC_Phone'              => '',                      // nomor telepon PIC untuk konfirmasi pembayaran
                'Note'                   => "Pembayaran royalti bulan $month", // catatan tambahan transaksi
                'PIC_Paid'               => '',                      // nama PIC yang melakukan pembayaran
                'Last_Date_Payment'      => '',                      // tanggal realisasi pembayaran terakhir
                'PO_References'          => '',                      // nomor PO jika ada, opsional untuk membership/royalti
                'PPH_Type'               => '0',                     // jenis PPh yang dikenakan (23, 21, 4(2), dll)
                'PPH23_4'                => 0.00,                    // PPh 23 tarif 4% (tanpa NPWP)
                'PPH23_2'                => 0.00,               // PPh 23 tarif 2% (memiliki NPWP) dari bruto
                'PPH23_15'               => 0.00,                    // PPh 23 tarif 15% (dividen, bunga, royalti tertentu)
                'PPH42'                  => 0.00,                    // PPh pasal 4 ayat 2 (pajak final, sewa tanah/bangunan)
                'PPH_21_5'               => 0.00,                    // PPh 21 tarif 5% untuk penghasilan untuk tipe individu
                'DP'                     => 0.00,                    // uang muka / down payment yang sudah dibayarkan
                'Management_Fee'         => 0,                       // biaya manajemen jika ada
                'Delivery_Costs'         => 0,                       // biaya pengiriman/ongkos kirim dokumen fisik
                'Currency'               => 'IDR',                   // mata uang transaksi
                'Currency_Kurs'          => 1.00,                    // kurs konversi ke IDR, 1.00 jika sudah IDR
                'Currency_Value'         => $total,             // nilai transaksi setelah konversi kurs
                'Revisi_Journ'           => 0,                       // flag apakah ada revisi jurnal akuntansi (0 = tidak)
                'Status_Approval'        => 'PENDING',              // status persetujuan: APPROVED/PENDING/REJECTED
                'COA_Approval'           => '',                // kode akun COA (Chart of Accounts) yang dibebankan
                'File_Document'          => $path,                      // path file dokumen pendukung (invoice, kontrak, dll)
                'Bank_Name'              => $session->Bank_Name,                      // nama bank tujuan pembayaran vendor
                'Bank_Acc'               => $session->Bank_Account_No,                      // nomor rekening bank tujuan
                'Bank_Acc_Name'          => $session->Bank_Account_Name,                      // nama pemilik rekening tujuan
                'Bank_Type'              => '',                      // jenis rekening: Giro / Tabungan
                'Payment_Type'           => '',                      // metode pembayaran: Transfer / Cek / Giro
                'Payment_Reference'      => '',                      // nomor referensi bukti transfer
                'Payment_Acc_No'         => '',                      // nomor rekening sumber pembayaran (rekening perusahaan)
                'Payment_Note'           => '',                      // keterangan pada slip transfer
                'materai'                => 0,                       // flag meterai: 1 jika dokumen perlu meterai Rp10.000
                'Opportunity_Id' => $partner[0]->id,
            ]);

            DB::commit();

            // return view('approval.profit-sharing-create', compact('partner'));

            return response()->json([
                'status'  => 'success',
                'message' => 'Data berhasil disimpan'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRoyaltyPaymentData(Request $r)
    {
        $b = $r->all();
        $allFiles = $r->allFiles();

        $id = DB::table('account_payable as ap')->where('ap.ID', '=', $b['Application_Id'])->update([
            'Status' => $b['Status'],
        ]);

        if (isset($allFiles['Transfer_Doc'])) {
            $files = $allFiles['Transfer_Doc'];

            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                if ($file->isValid()) {
                    $path = $file->store("$this->year/$this->month", 'public');
                    $ext = $file->getClientOriginalExtension();

                    $filename = basename($path);
                    $desc = "Bukti Transfer";
                    $topic = "profit-sharing";
                    $mapTo = $id;

                    $this->uploaderService->uploadMetaFile($path, $filename, $desc, $ext, $topic, $mapTo);

                    $updatedId = DB::table('account_payable as ap')->where('ap.ID', '=', $b['Application_Id'])->update([
                        'Url_Transfer' => "https://dev.hexamatika.com/geu/hcga/public/filevendor/$path",
                    ]);
                }
            }
        }




        return response()->json(['message' => 'success'], 200);
    }

    public function monthlyMitraprofitSharing()
    {
        $getMitra = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->first();
        if (!$getMitra) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }
        return view('partner.mitra-profit-sharing-lk-monthly', compact('getMitra'));
    }

    public function annuallyMitraprofitSharing()
    {
        $getMitra = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->first();
        if (!$getMitra) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }
        return view('partner.mitra-profit-sharing-lk-annually', compact('getMitra'));
    }

    public function indexProfitSharingOverview2()
    {
        return view('partner.profit-sharing.overview2');
    }

    public function indexProfitSharingOverview2Xml()
    {
        /*
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        */

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

        if ($priv == 'MITRA') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }
        $percentage[401] = 100;
        $bep = 24;

        foreach ($get as $key => $Fetch) {
            $calculate = [];
            //Hitung Pendaptan
            $calculate[401][40001] = 0;

            // harga transfer
            $calculate[402][40002] = MasterStockPrice::whereBetween('date_for', [$Fetch->Created_Date, Carbon::now()])->avg('price');

            // quantity
            $calculate[501][51001] = WarehouseStockRequest::where('warehouse_task_id', $Fetch->warehouse_task_id)->whereBetween('request_date', [$Fetch->Created_Date, Carbon::now()])->sum('request_qty');

            // harga beli
            // $calculate[501][50002] = WarehousePartnerSupplies::where('received_date', 'LIKE', $request->date . '%')->avg('price');

            $whs = WarehousePartnerSupplies::where("warehouse_task_id", $Fetch->warehouse_task_id)->whereBetween('received_date', [$Fetch->Created_Date, Carbon::now()])->get();
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

                $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $Fetch->warehouse_task_id)
                    ->whereBetween('posting_date', [$Fetch->Created_Date, Carbon::now()])
                    ->where('account_no', $value->account_no)
                    ->where('group_account', $value->group_account)
                    ->sum('nominal');

                $calculate[$value->group_account][$value->account_no] += $getOpex;
            }

            $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502]);

            $get = MasterWarehouseProfitLoss::where('type', $Fetch->warehouse_type)->get();
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
                                if ($value->Judul == '701') {
                                    $calculate[$value->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                                }
                            } else {
                                $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) + array_sum($calculate[$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                            }
                            // $percentage[$value->Judul] = $calculate[$value->Judul]['NULL']/$calculate[401][40001] * 100;
                        }
                    }
                } else if ($value->Level == 3) {
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

            $type = ucwords($Fetch->warehouse_type);
            if ($type == '') {
                $type = 'Booth';
            }

            $d = DB::table('opportunity_partner as op')->select('op.full_name', 'opfd.*', 'mf.description')
                ->join('opportunity_partner_facility_dtl as opfd', 'opfd.Partner_ID', '=', 'op.id')
                ->where('op.lead_id', '=', $Fetch->partner_task_id)->sum('opfd.Total');

            $netprofit = isset($calculate[701]) ? $calculate[701]['NULL'] : 0;
            $ebitda = isset($calculate[899]) ? $calculate[899]['NULL'] : 0;
            $opex = $ebitda - $netprofit;
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($d) . "</cell>";
            echo "<cell>" . htmlspecialchars($netprofit) . "</cell>";
            echo "<cell>" . htmlspecialchars($opex) . "</cell>";
            echo "<cell>" . htmlspecialchars($ebitda) . "</cell>";
            echo "<cell>" . htmlspecialchars($bep) . "</cell>";
            echo "<cell>" . htmlspecialchars(round((isset($calculate[899]) ? $calculate[899]['NULL'] : 0) / ($d ?: 1) * 100, 2)) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function indexProfitSharingManagementFee()
    {
        return view('partner.profit-sharing.management-fee');
    }

    public function indexProfitSharingManagementFeeXml()
    {
        /*
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        */

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

        if ($priv == 'MITRA') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }
        $percentage[401] = 100;

        foreach ($get as $key => $Fetch) {
            $us = User::where('Emp_ID', $Fetch->partner_task_id)->first();
            $getStartProjection = DB::table('projections')->where("User_ID", $us->id)->select('Created_At')->first();
            if ($getStartProjection) {
                $tahunPembuatan = [
                    date('Y', strtotime($getStartProjection->Created_At)) => 1,
                    date('Y', strtotime($getStartProjection->Created_At . ' +1 year')) => 2,
                    date('Y', strtotime($getStartProjection->Created_At . ' +2 years')) => 3,
                    date('Y', strtotime($getStartProjection->Created_At . ' +3 years')) => 4,
                    date('Y', strtotime($getStartProjection->Created_At . ' +4 years')) => 5,
                ];
            } else {
                $tahunPembuatan = [
                    date('Y') => 1,
                    date('Y', strtotime('+1 year')) => 2,
                    date('Y', strtotime('+2 years')) => 3,
                    date('Y', strtotime('+3 years')) => 4,
                    date('Y', strtotime('+4 years')) => 5,
                ];
            }

            // $ambilProjection = DB::table('projections')->where("User_ID", $us->id)->whereYear('Created_At', $tahunPembuatan[date('Y')])->first()->toArray();


            $tahun = DB::table('projections')->where("User_ID", $us->id)->select('Created_At')->orderBy('Created_At', 'desc')->first();
            $tahun = date('Y', strtotime($tahun->Created_At ?? date('Y-m-d')));

            $calculate = [];
            //Hitung Pendaptan
            $calculate[401][40001] = 0;

            // harga transfer
            $calculate[402][40002] = MasterStockPrice::whereBetween('date_for', [$Fetch->Created_Date, Carbon::now()])->avg('price');

            // quantity
            $calculate[501][51001] = WarehouseStockRequest::where('warehouse_task_id', $Fetch->warehouse_task_id)->whereBetween('request_date', [$Fetch->Created_Date, Carbon::now()])->sum('request_qty');

            // harga beli
            // $calculate[501][50002] = WarehousePartnerSupplies::where('received_date', 'LIKE', $request->date . '%')->avg('price');

            $whs = WarehousePartnerSupplies::where("warehouse_task_id", $Fetch->warehouse_task_id)->whereBetween('received_date', [$Fetch->Created_Date, Carbon::now()])->get();
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

                $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $Fetch->warehouse_task_id)
                    ->whereBetween('posting_date', [$Fetch->Created_Date, Carbon::now()])
                    ->where('account_no', $value->account_no)
                    ->where('group_account', $value->group_account)
                    ->sum('nominal');

                $calculate[$value->group_account][$value->account_no] += $getOpex;
            }

            $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502]);

            $get = MasterWarehouseProfitLoss::where('type', $Fetch->warehouse_type)->get();
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
                                if ($value->Judul == '701') {
                                    $calculate[$value->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                                }
                            } else {
                                $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) + array_sum($calculate[$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                            }
                            // $percentage[$value->Judul] = $calculate[$value->Judul]['NULL']/$calculate[401][40001] * 100;
                        }
                    }
                } else if ($value->Level == 3) {
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

            $type = ucwords($Fetch->warehouse_type);
            if ($type == '') {
                $type = 'Booth';
            }

            // $d = DB::table('opportunity_partner as op')->select('op.full_name', 'opfd.*', 'mf.description')
            //     ->join('opportunity_partner_facility_dtl as opfd', 'opfd.Partner_ID', '=', 'op.id')
            //     ->join('master_facility as mf', 'mf.id', '=', 'opfd.Facility_ID')
            //     ->where('op.lead_id', '=', $Fetch->partner_task_id)->sum('Total');

            $netprofit = isset($calculate[701]) ? $calculate[701]['NULL'] : 0;
            $managementfee = isset($calculate[601][61007]) ? $calculate[601][61007] : 0;
            $bagiHasilMitra = isset($calculate[801]['NULL']) ? $calculate[801]['NULL'] : 0;
            $bagiHasil = $netprofit - $bagiHasilMitra;
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($managementfee) . "</cell>";
            echo "<cell>" . htmlspecialchars($bagiHasil) . "</cell>";
            echo "<cell>" . htmlspecialchars($managementfee - $bagiHasil) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function indexOverviewMitraProfitSharing()
    {
        return view('partner.profit-sharing.overview-mitra');
    }

    public function indexOverviewMitraProfitSharingXml(Request $request)
    {
        /*
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");
        */

        echo "<rows>";
        $nomor = 1;

        $Fetch = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->first();

        $startDate = new DateTime($Fetch->Created_Date);
        $endDate = new DateTime(Carbon::now());
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($startDate, $interval, $endDate);
        foreach ($period as $date) {
            $ym = $date->format("Y-m");
            $month = $date->format("F");
            $year = $date->format("Y");
            $calculate = [];
            //Hitung Pendaptan
            $calculate[401][40001] = 0;

            // harga transfer
            $calculate[402][40002] = MasterStockPrice::where('date_for', 'LIKE', $ym . '%')->avg('price');

            // quantity
            $calculate[501][51001] = WarehouseStockRequest::where('warehouse_task_id', $Fetch->warehouse_task_id)->where('request_date', 'LIKE', $ym . '%')->sum('request_qty');

            // harga beli
            // $calculate[501][50002] = WarehousePartnerSupplies::where('received_date', 'LIKE', $request->date . '%')->avg('price');

            $whs = WarehousePartnerSupplies::where("warehouse_task_id", $Fetch->warehouse_task_id)->where('received_date', 'LIKE', $ym . '%')->get();
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

                $getOpex = WarehousePartnerJournal::where('warehouse_task_id', $Fetch->warehouse_task_id)
                    ->where('posting_date', 'LIKE', $ym . '%')
                    ->where('account_no', $value->account_no)
                    ->where('group_account', $value->group_account)
                    ->sum('nominal');

                $calculate[$value->group_account][$value->account_no] += $getOpex;
            }

            $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502]);

            $get = MasterWarehouseProfitLoss::where('type', $Fetch->warehouse_type)->get();
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
                                if ($value->Judul == '701') {
                                    $calculate[$value->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                                }
                            } else {
                                $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) + array_sum($calculate[$exp[1]])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]]) : 0);
                            }
                            // $percentage[$value->Judul] = $calculate[$value->Judul]['NULL']/$calculate[401][40001] * 100;
                        }
                    }
                } else if ($value->Level == 3) {
                    $exp = explode(',', $value->Rumus);
                    if (!isset($calculate[$value->Judul])) {
                        $calculate[$value->Judul]['NULL'] = 0;
                    }
                    $calculate[$value->Judul]['NULL'] += (array_sum($calculate[$exp[0]]) * 60) / 100;
                }
            }

            echo "<row id=\"$Fetch->warehouse_task_id-$ym\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($year) . "</cell>";
            echo "<cell>" . htmlspecialchars($month) . "</cell>";
            echo "<cell>" . htmlspecialchars($calculate[801]['NULL']) . "</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function detailOverviewMitraProfitSharing(Request $request)
    {
        $warehouse = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->select(['warehouse_task_id', 'type'])->first();
        $date = date('Y-m', strtotime('first date of ' . $request->date));
        return view('partner.profit-sharing.overview-mitra', compact('warehouse', 'date'));
    }

    public function getRoyaltyPaymentData(Request $r)
    {
        $b  = $r->all();
        $session = $this->userService->getCurrentSessionUserInfo();

        $d = DB::table('account_payable as ap');

        if (in_array($session->Position, ["MITRA"])) {
            $d = $d->join('opportunity_partner as op', 'op.id', '=', 'ap.Opportunity_Id')
                ->where("op.user_row_id", "=", $session->id);
        }

        $d =  $d->select('ap.*')
            ->orderBy('ID', 'DESC')->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;

        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addAttribute('ID', $p->ID);
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->Task_ID);
            $row->addChild('cell', $p->Reference);
            $row->addChild('cell', $p->Total);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', $p->Created_By);
            if (!$p->Url_Transfer) {
                $row->addChild('cell', "Belum Ada Bukti Transfer");
            } else {
                $row->addChild('cell', "Lihat Bukti Transfer^ $p->Url_Transfer");
            }
            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
