<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterStockPrice;
use App\MasterWarehouseAccount;
use App\MasterWarehouseProfitLoss;
use App\Services\GeneratorService;
use App\Services\NotificationService;
use App\Services\UploadService;
use App\Services\UserService;
use App\Services\WarehouseService;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
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
    protected $notificationService;
    protected $warehouseService;

    public function __construct(UserService $u, GeneratorService $g, UploadService $up, NotificationService $n, WarehouseService $wh)
    {

        $this->warehouseService = $wh;
        $this->generatorService = $g;
        $this->uploaderService = $up;
        $this->userService = $u;
        $this->notificationService = $n;
        $this->now = Carbon::now();
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->format('m');

        $this->currDate = Carbon::now()->format('Y-m-d');
        Carbon::setLocale('id');
    }

    public function indexProjection()
    {
        $session = $this->userService->getCurrentSessionUserInfo();

        return view('projection.index', compact('session'));
    }

    public function createProjection()
    {
        return view('projection.create');
    }

    public function indexProfitSharing()
    {
        $session = $this->userService->getCurrentSessionUserInfo();

        return view('partner.profit-sharing', compact('session'));
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

    public function monthlyFinancialStatementCollection(Request $request)
    {
        // ============================================================
        // 1. Calculate Revenue
        // ============================================================

        // Average stock transfer price for the given date
        $avgStockPrice = MasterStockPrice::where('date_for', 'LIKE', "$request->date%")->avg('price') ?? 0;

        // Total quantity requested for the given warehouse
        $qtyRequested  = WarehouseStockRequest::where('warehouse_task_id', $request->Task)
            ->where('request_date', 'LIKE', $request->date . '%')
            ->sum('request_qty');

        // Weighted average purchase price from supplies
        $supplies    = WarehousePartnerSupplies::where('warehouse_task_id', $request->Task)
            ->where('received_date', 'LIKE', $request->date . '%')
            ->get();

        $avgBuyPrice = 0;
        $totalQty    = 0;
        foreach ($supplies as $supply) {
            $avgBuyPrice = (($avgBuyPrice * $totalQty) + $supply->total) / ($totalQty + $supply->received_qty);
            $totalQty   += $supply->received_qty;
        }

        $calculate = [];
        $calculate[401][40001]  = $avgStockPrice * $qtyRequested; // Revenue
        $calculate[402][40002]  = $avgStockPrice;                 // Transfer price
        $calculate[501][51001]  = $qtyRequested;                  // Quantity
        $calculate[501][51002]  = $avgBuyPrice;                   // Purchase price
        $calculate[501]['NULL'] = $qtyRequested * $avgBuyPrice;   // COGS

        // ============================================================
        // 2. Calculate OPEX from journal entries per account
        // ============================================================
        $opexAccounts = MasterWarehouseAccount::where('group_account', '>', 501)->get();

        foreach ($opexAccounts as $account) {
            $calculate[$account->group_account][$account->account_no] = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)
                ->where('posting_date', 'LIKE', $request->date . '%')
                ->where('account_no', $account->account_no)
                ->where('group_account', $account->group_account)
                ->sum('nominal');
        }

        $calculate[599]['NULL'] = $calculate[501]['NULL'] + array_sum($calculate[502] ?? []);

        // ============================================================
        // 3. Apply profit/loss formulas from master
        // Level 2 = calculated subtotals (credit/debit)
        // Level 3 = profit share (60% of referenced account)
        // ============================================================
        $formulas = MasterWarehouseProfitLoss::where('type', $request->type)->get();

        foreach ($formulas as $formula) {
            if ($formula->Rumus == '') continue;

            $exp = explode(',', $formula->Rumus);

            if ($formula->Level == 2) {
                $calculate[$formula->Judul]['NULL'] = $formula->Normal == 'CREDIT'
                    ? (array_sum($calculate[$exp[0]] ?? []) - array_sum($calculate[$exp[1]] ?? [])) - (isset($exp[2]) ? array_sum($calculate[$exp[2]] ?? []) : 0)
                    : (array_sum($calculate[$exp[0]] ?? []) + array_sum($calculate[$exp[1]] ?? [])) + (isset($exp[2]) ? array_sum($calculate[$exp[2]] ?? []) : 0);

                // Deduct 2.5% management fee specifically for account 701
                if ($formula->Judul == '701') {
                    $calculate[$formula->Judul]['NULL'] -= ($calculate[401][40001] * 2.5) / 100;
                }
            }

            if ($formula->Level == 3) {
                $calculate[$formula->Judul]['NULL'] = (array_sum($calculate[$exp[0]] ?? []) * 60) / 100;
            }
        }


        // ============================================================
        // 4. Build result collection from formula definitions
        // Each row contains the value, percentage of revenue,
        // styling metadata, and unit info
        // ============================================================
        $revenue = $calculate[401][40001] ?: 1; // Avoid division by zero

        $result = $formulas->map(function ($formula) use ($calculate, $revenue) {

            // Resolve the calculated value based on Judul and Code
            if ($formula->Judul != '' && $formula->Judul != null) {
                if ($formula->Code != '' && $formula->Code != null) {
                    $value = $calculate[$formula->Judul][$formula->Code] ?? 0;
                } elseif (isset($calculate[$formula->Judul]['NULL'])) {
                    $value = $calculate[$formula->Judul]['NULL'];
                } else {
                    $value = array_sum($calculate[$formula->Judul] ?? []);
                }
            } elseif ($formula->Code != '' && $formula->Code != null && $formula->Rumus != '') {
                $value = $calculate[$formula->Rumus][$formula->Code] ?? 0;
            } else {
                $value = null;
            }

            $percentage = ($value !== null && $formula->Unit_Name != 'Kg')
                ? round($value / $revenue * 100, 2)
                : null;

            return [
                'account_group' => $formula->Code ? $formula->Code[0] : null,
                'account'     => $formula->Code,
                'description' => $formula->Description,
                'value'       => $value,
                'unit'        => $formula->Unit_Name,
                'percentage'  => $percentage,
                // 'bold'        => !is_null($formula->Bold),
                // 'indentation' => $formula->Indentation,
            ];
        });

        return [
            'warehouse_task_id' => $request->Task,
            'periode' => $request->date,
            'statements' => $result,
        ];
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

    // ?user_id=&hub_type=&projection_type=&year=
    public function projectionData(Request $r)
    {
        $q = $r->query();
        $userId = $q['user_id'];
        $projectionType = $q['projection_type'] ?? "%";
        $hubType = $q['hub_type'] ?? "%";
        $year = $q['year'] ?? "%";
        $session = $this->userService->getCurrentSessionUserInfo();

        $proj = DB::table('projections')->select('*')
            ->where('User_Id', '=', $userId)
            ->where('Projection_Type', 'LIKE', $projectionType)
            ->where('Hub_Type', 'LIKE', $hubType)
            ->where('Year', 'LIKE', $year);


        if (in_array($session->Position, ["MITRA"])) {
            $proj = $proj->where('Is_Hidden', '=', 0);
        }

        $proj = $proj->get();


        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($proj as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->Hub_Type);
            $row->addChild('cell', $p->Projection_Type);
            $row->addChild('cell', htmlspecialchars($p->Description));
            $row->addChild('cell', $p->Standard_Value);
            $row->addChild('cell', $p->Offset);
            $row->addChild('cell', $p->Year);

            for ($month = 1; $month <= 12; $month++) {
                $rowA = "Month_{$month}_A";
                $rowB = "Month_{$month}_B";
                $rowTotal = "Month_{$month}_Total";
                $rowOffset = "Month_{$month}_Offset";

                $row->addChild('cell', $p->$rowA);
                $row->addChild('cell', $p->$rowB);
                $row->addChild('cell', $p->$rowTotal);
                $row->addChild('cell', $p->$rowOffset);
            }

            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }


    // ?user_id=&hub_type=&projection_type=&year=
    public function expenditureData(Request $r)
    {
        $q = $r->query();
        $userId = $q['user_id'];
        $projectionType = $q['projection_type'] ?? "%";
        $hubType = $q['hub_type'];
        $year = '%';

        $exp = DB::table('expenditure')->select('*')->where('User_Id', '=', $userId)->where('Projection_Type', 'LIKE', $projectionType)->where('Hub_Type', 'LIKE', $hubType)->where('Year', 'LIKE', $year)->get();

        $xml = new SimpleXMLElement('<rows></rows>');

        $counter = 1;
        foreach ($exp as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $counter);
            $row->addChild('cell', $p->Hub_Type);
            $row->addChild('cell', $p->Projection_Type);
            $row->addChild('cell', htmlspecialchars($p->Description));
            $row->addChild('cell', $p->Short_Desc);
            $row->addChild('cell', $p->Offset);
            $row->addChild('cell', $p->Year);

            for ($month = 1; $month <= 12; $month++) {
                $rowTotal = "Month_{$month}_Total";

                $row->addChild('cell', $p->$rowTotal);
            }

            $counter++;
        }

        return response()->make($xml->asXML(), 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function downloadProjectionTemplate()
    {
        $template = public_path('template-proyeksi-final.xlsx');


        if (!file_exists($template)) {
            abort(404, 'File Tidak Ditemukan!');
        }

        return response()->download($template);
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
                ->join('approval_level as lv', 'appr.Level_Id', '=', 'lv.ID')->where('appr.Request_Type', 'LIKE',  $type)->where('appr.Status', '=', 'NEED_APPROVAL')->where('lv.Approver_Position', '=', $session->Position)->orderBy('ID', 'DESC')->get();

            // MITRA, SUPERADMIN
        } else {
            $d = DB::table('approval_request as appr')->select('appr.*', 'usr.Name')->join('users as usr', 'appr.Requester_Id', '=', 'usr.id')->where('appr.Request_Type', 'LIKE',  $type)->where('appr.ID', 'LIKE', $apprId)->orWhere('appr.Requester_Id', 'LIKE', $user_id)->orderBy('ID', 'DESC')->get();
        }

        $xml = new SimpleXMLElement('<rows></rows>');

        foreach ($d as $p) {
            $row = $xml->addChild('row');
            $row->addChild('cell', $p->ID);
            $row->addChild('cell', $p->Name);
            $row->addChild('cell', $p->Request_Type);
            $row->addChild('cell', $p->Status);
            $row->addChild('cell', htmlspecialchars($p->Description));
            $row->addChild('cell', $p->Created_At);
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

        $path = $this->generatorService->generateUUID();
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

        // Parse month and year from submitted Periode (format: "YYYY-MM")
        $periode = $b['Periode'] ?? '';
        if ($periode) {
            $parts = explode('-', $periode);
            $year = $parts[0];
            $monthNum = $parts[1];
        } else {
            $year = Carbon::now()->year;
            $monthNum = Carbon::now()->format('m');
        }
        $month = Carbon::createFromFormat('m', $monthNum)->translatedFormat('F');
        $bankName = $partner->Bank_Name;
        $accountName = $partner->Bank_Account_Name;
        $accountNo = $partner->Bank_Account_No;
        $mitraFullname = $partner->Name;

        $highest = DB::table('account_payable as ap')->max('ID');
        $referenceNo = $this->generatorService->generateApplicationCode($highest, 'DOC-PFS');

        $doc->setValues([
            'reference_no' => $referenceNo,
            'location' => $location,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'nominal' => $nominal,
            'terbialng' => $this->generatorService->spellRupiah($nominal),
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

    /** TODO: 
     *  Ambil revenue dari finance
     * */
    public function makeProfitSharingApplication($b, $templatePath, Request $r = null)
    {
        $doc = new TemplateProcessor($templatePath);

        $session = $this->userService->getCurrentSessionUserInfo();
        $userId = $b['user_id'] ?? $session->id;
        $partner = $this->userService->getMitraById($userId);
        $wh = DB::table('warehouse_partner as wp')
            ->join('opportunity_partner as op', 'op.lead_id', '=', 'wp.partner_task_id')
            ->where('op.user_row_id', '=', $userId)
            ->select('wp.warehouse_task_id')
            ->first();
        $whId = $wh->warehouse_task_id ?? '';
        $periodeStart = $b['periode_start'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $periodeEnd = $b['periode_end'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');



        $wh = $this->warehouseService->calculateMonthlyFinancialStatement($whId, $periodeStart, $periodeEnd);


        $nominalRoyalty = $wh['nominal_royalty'];

        $location = $partner->City;
        $date = Carbon::now()->day;

        // Parse month and year from submitted Periode (format: "YYYY-MM")
        $periode = $b['periode'] ?? '';
        if ($periode) {
            $parts = explode('-', $periode);
            $year = $parts[0];
            $monthNum = $parts[1];
        } else {
            $year = Carbon::now()->year;
            $monthNum = Carbon::now()->format('m');
        }
        $month = Carbon::createFromFormat('m', $monthNum)->translatedFormat('F');
        $bankName = $partner->Bank_Name;
        $accountName = $partner->Bank_Account_Name;
        $accountNo = $partner->Bank_Account_No;
        $mitraFullname = $partner->Name;
        $highest = DB::table('account_payable as ap')->max('ID');
        $referenceNo = $this->generatorService->generateApplicationCode($highest, 'DOC-PFS');

        $doc->setValues([
            'reference_no' => $referenceNo,
            'location' => $location,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'nominal' => $this->generatorService->rupiahFormatter($nominalRoyalty),
            'terbilang' => $this->generatorService->spellRupiah($nominalRoyalty),
            'bank_name' => $bankName,
            'account_name' => $accountName,
            'account_no' => $accountNo,
            'mitra_fullname' => $mitraFullname,
            'periode' => $b['Periode'] ?? '',
        ]);

        if ($r && $r->hasFile('signature')) {
            $imageData = file_get_contents($r->file('signature')->getRealPath());

            $tempImage = tempnam(sys_get_temp_dir(), 'img') . '.png';
            file_put_contents($tempImage, $imageData);

            $doc->setImageValue('mitra_signature', [
                'path' => $tempImage,
                'height' => 100,
                'ratio' => false
            ]);

            unlink($tempImage);
        }

        return ['doc' => $doc, 'contract_no' => $referenceNo, 'royalti' => $nominalRoyalty];
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
        $doc = $this->makeProfitSharingApplication($b, $path, $r);


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

        $doc = $this->makeProfitSharingApplication($body, $path, $r);

        $path = $this->generatorService->generateUUID();
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

            $taskId = preg_replace('/DOC-?/', '', $doc['contract_no']);

            $partner = DB::table('opportunity_partner as op')
                ->select('op.*')
                ->join('users as usr', 'usr.id', '=', 'op.user_row_id')
                ->where('op.user_row_id', '=', $session->id)
                ->get();

            // dd($partner, $session);

            $royalti = $doc['royalti'];
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

            return response()->download(public_path($path))->deleteFileAfterSend(true);
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

            $nomor++;
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

    public function createMitraProjection(Request $r)
    {
        // Save BEP form data
        if ($r->filled('mitra_id')) {
            $partner = DB::table('opportunity_partner')
                ->select('lead_id')->where('id', $r->mitra_id)->first();
            $warehouse = $partner
                ? DB::table('warehouse_partner')->where('partner_task_id', $partner->lead_id)->first()
                : null;

            if ($warehouse) {
                DB::table('warehouse_partner')->where('id', $warehouse->id)->update([
                    'bep_projection' => $r->bep_projection,
                    'bep_projected_at' => $r->filled('bep_at')
                        ? now()->addMonths((int) $r->bep_at)->format('Y-m-d')
                        : null,
                ]);
            }
        }

        $b = $r->all();
        $oppPartner = DB::table('opportunity_partner')->where('id', $r->mitra_id)->first();
        $userId = $oppPartner ? $oppPartner->user_row_id : "2345";

        $file = $r->file('file');
        $path = $file->storeAs('temp', 'upload_' . time() . '.' . $file->getClientOriginalExtension());
        $fullPath = Storage::path($path);
        $spreadsheet = IOFactory::load($fullPath);

        $partner = DB::table('opportunity_partner as op')
            ->select('op.package_option', 'wp.warehouse_task_id')
            ->join('warehouse_partner as wp', 'wp.partner_task_id', '=', 'op.lead_id')
            ->where('user_row_id', '=', $userId)->get();


        $sheet = $spreadsheet->getSheetByName('Proyeksi');

        $typeProjection = ['high', 'low'];


        DB::beginTransaction();

        try {
            // restart, hapus semua yang ada
            DB::table('expenditure')->where('User_Id', $userId)->delete();
            DB::table('projections')->where('User_Id', $userId)->delete();

            for ($type = 0; $type < count($typeProjection); $type++) {

                $rowIterator = null;

                // hidden expenditure
                if ($typeProjection[$type] == 'high') {
                    $rowIterator = $sheet->getRowIterator(4, 17);
                }

                if ($typeProjection[$type] == 'low') {
                    $rowIterator = $sheet->getRowIterator(231, 244);
                }

                $data = [];

                foreach ($rowIterator as $row) {
                    $columnIterator = $row->getCellIterator();
                    $columnIterator->setIfNotExists(CellIterator::IF_NOT_EXISTS_RETURN_NULL);
                    $rowData = [];
                    foreach ($columnIterator as $cell) {
                        if ($cell !== null) {
                            $value = $cell->getCalculatedValue();
                            $rowData[] = $value;
                        }
                    }
                    $data[] = $rowData;
                }

                $count = 1;
                for ($i = 0; $i < count($data); $i++) {
                    $b = $data[$i];

                    DB::table('expenditure')->insertGetId([
                        "User_Id" => $userId,
                        "Hub_Type" => $partner[0]->package_option,
                        "Description" => $b[0] ?? "",
                        "Short_Desc" => $b[1] ?? "",
                        "Offset" => $b[2],
                        "Year" => $count,
                        "Projection_Type" => $typeProjection[$type],
                        "Month_1_Total" => $b[5],
                        "Month_2_Total" => $b[9],
                        "Month_3_Total" => $b[13],
                        "Month_4_Total" => $b[17],
                        "Month_5_Total" => $b[21],
                        "Month_6_Total" => $b[25],
                        "Month_7_Total" => $b[29],
                        "Month_8_Total" => $b[33],
                        "Month_9_Total" => $b[37],
                        "Month_10_Total" => $b[41],
                        "Month_11_Total" => $b[45],
                        "Month_12_Total" => $b[49],
                    ]);
                }

                $count = 1;


                // proyeksi
                if ($typeProjection[$type] == 'high') {
                    $yearToRow = [
                        1 => [26, 73],
                        2 => [79, 124],
                        3 => [130, 175],
                        4 => [181, 226]
                    ];
                }

                if ($typeProjection[$type] == 'low') {
                    $yearToRow = [
                        1 => [253, 298],
                        2 => [304, 349],
                        3 => [355, 400],
                        4 => [407, 452]
                    ];
                }

                for ($i = 1; $i <= 4; $i++) {
                    $rowNum = $yearToRow[$i];

                    $projRowIterator = $sheet->getRowIterator($rowNum[0], $rowNum[1]);
                    $projData = [];
                    foreach ($projRowIterator as $row) {
                        $columnIterator = $row->getCellIterator();
                        $columnIterator->setIfNotExists(CellIterator::IF_NOT_EXISTS_RETURN_NULL);
                        $rowData = [];

                        $firstCell = $columnIterator->current();
                        $firstValue = $firstCell ? $firstCell->getValue() : null;

                        foreach ($columnIterator as $cell) {
                            if ($cell !== null) {
                                $value = $cell->getCalculatedValue();
                                $rowData[] = $value;
                            }
                        }
                        $projData[] = $rowData;
                    }

                    for ($j = 0; $j < count($projData); $j++) {
                        $b = $projData[$j];
                        $isHidden = 0;

                        $coaId = null;

                        switch (strtolower($b[0])) {
                            case 'working capital':
                                $coaId = 2;
                                break;
                            case 'saldo mengendap':
                                $coaId = 3;
                                break;
                            case 'pendapatan':
                                $coaId = 182;
                                break;
                            case 'harga transfer':
                                $coaId = 295;
                                break;
                            case 'hpp':
                                $coaId = 223;
                                break;
                            case 'bensin':
                                $coaId = 264;
                                break;
                            case 'parkir':
                                $coaId = 273;
                                break;
                            case 'service kendaraan':
                                $coaId = 286;
                                break;
                            case 'wifi':
                                $coaId = 364;
                                break;
                            case 'listrik':
                                $coaId = 362;
                                break;
                            case 'kebersihan & keamanan':
                                $coaId = 366;
                                break;
                            case 'logistic penjemputan':
                                $coaId = 287;
                                break;
                            case 'biaya konversi':
                                $coaId = 290;
                                break;
                            case 'biaya iscc':
                                $coaId = 365;
                                break;
                            case 'total cogs':
                                $coaId = 224;
                                break;
                            case 'bagi hasil mitra':
                                $coaId = 312;
                                break;
                            case 'beban tenaga kerja':
                                $coaId = 314;
                                break;
                            case 'sewa lokasi':
                                $coaId = 356;
                                break;
                            default:
                                break;
                        }

                        //hide profit
                        if (Str::lower(str_replace(' ', '', $b[0])) == "profitperusahaan") {
                            $isHidden = 1;
                        }

                        DB::table('projections')->insertGetId([
                            "User_Id" => $userId,
                            "Hub_Type" => $partner[0]->package_option,
                            "warehouse_task_id" => $partner[0]->warehouse_task_id,
                            "Description" => $b[0] ?? "",
                            "Coa_Id" => $coaId,
                            "Standard_Value" => $b[1],
                            "Offset" => $b[2],
                            "Year" => $count,
                            "Projection_Type" => $typeProjection[$type],
                            "Month_1_A" => $b[3],
                            "Month_1_B" => $b[4],
                            "Month_1_Total" => $b[5],
                            "Month_1_Offset" => $b[6],
                            "Month_2_A" => $b[7],
                            "Month_2_B" => $b[8],
                            "Month_2_Total" => $b[9],
                            "Month_2_Offset" => $b[10],
                            "Month_3_A" => $b[11],
                            "Month_3_B" => $b[12],
                            "Month_3_Total" => $b[13],
                            "Month_3_Offset" => $b[14],
                            "Month_4_A" => $b[15],
                            "Month_4_B" => $b[16],
                            "Month_4_Total" => $b[17],
                            "Month_4_Offset" => $b[18],
                            "Month_5_A" => $b[19],
                            "Month_5_B" => $b[20],
                            "Month_5_Total" => $b[21],
                            "Month_5_Offset" => $b[22],
                            "Month_6_A" => $b[23],
                            "Month_6_B" => $b[24],
                            "Month_6_Total" => $b[25],
                            "Month_6_Offset" => $b[26],
                            "Month_7_A" => $b[27],
                            "Month_7_B" => $b[28],
                            "Month_7_Total" => $b[29],
                            "Month_7_Offset" => $b[30],
                            "Month_8_A" => $b[31],
                            "Month_8_B" => $b[32],
                            "Month_8_Total" => $b[33],
                            "Month_8_Offset" => $b[34],
                            "Month_9_A" => $b[35],
                            "Month_9_B" => $b[36],
                            "Month_9_Total" => $b[37],
                            "Month_9_Offset" => $b[38],
                            "Month_10_A" => $b[39],
                            "Month_10_B" => $b[40],
                            "Month_10_Total" => $b[41],
                            "Month_10_Offset" => $b[42],
                            "Month_11_A" => $b[43],
                            "Month_11_B" => $b[44],
                            "Month_11_Total" => $b[45],
                            "Month_11_Offset" => $b[46],
                            "Month_12_A" => $b[47],
                            "Month_12_B" => $b[48],
                            "Month_12_Total" => $b[49],
                            "Month_12_Offset" => $b[50],
                            "Is_Hidden" => $isHidden,
                        ]);
                    }

                    $count++;
                }
            }

            DB::commit();

            return response()->json(["success" => true, "message" => "success"]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "failed",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function dashboardProfitSharing()
    {
        return view('partner.profit-sharing.dashboard');
    }

    public function dashboardProfitSharingXml(Request $request)
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
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }
        $nomor = 1;

        $warehouse = WarehousePartner::select(['warehouse_task_id', 'warehouse_type', 'partner_task_id', 'Created_Date', 'Created_By', 'warehouse_location', 'warehouse_status', 'capacity']);
        if (auth()->user()->Priv_ID == "MITRA") {
            $warehouse->where('partner_task_id', auth()->user()->Emp_ID);
        }
        $warehouse = $warehouse->get();
        foreach ($warehouse as $key => $Fetch) {
            $bulanList = [];
            for ($i = 1; $i <= 12; $i++) {
                $bulan = date('Y-m', mktime(0, 0, 0, $i, 1));
                $bulanList[$bulan] = [
                    'proyeksi' => 0,
                    'bagi_hasil' => 0
                ];
            }
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

            // $tahun = DB::table('projections')->where("User_ID", $us->id)->select('Created_At')->orderBy('Created_At', 'desc')->first();
            // $tahun = date('Y', strtotime($tahun->Created_At ?? date('Y-m-d')));

            foreach ($bulanList as $kunci => $value) {
                $start = $kunci . '-01';
                $end = date('Y-m-t', strtotime($start));
                $calculate = [];
                //Hitung Pendaptan
                $calculate[401][40001] = 0;

                // harga transfer
                $calculate[402][40002] = MasterStockPrice::whereBetween('date_for', [$start, $end])->avg('price');

                // quantity
                $calculate[501][51001] = WarehouseStockRequest::where('warehouse_task_id', $Fetch->warehouse_task_id)->whereBetween('request_date', [$start, $end])->sum('request_qty');

                $whs = WarehousePartnerSupplies::where("warehouse_task_id", $Fetch->warehouse_task_id)->whereBetween('received_date', [$start, $end])->get();
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
                        ->whereBetween('posting_date', [$start, $end])
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

                // $netprofit = isset($calculate[701]) ? $calculate[701]['NULL'] : 0;
                // $managementfee = isset($calculate[601][61007]) ? $calculate[601][61007] : 0;
                $bagiHasilMitra = isset($calculate[801]['NULL']) ? $calculate[801]['NULL'] : 0;
                // $bagiHasil = $netprofit - $bagiHasilMitra;

                $bulanList[$kunci]['proyeksi'] += DB::table('projections')
                    ->where(function ($q) use ($Fetch, $us) {
                        $q->where('warehouse_task_id', $Fetch->warehouse_task_id)
                            ->orWhere("User_Id", $us->id);
                    })
                    ->where('Projection_Type', 'high')
                    ->where("Hub_Type", $Fetch->warehouse_type)
                    ->where('Year', $tahunPembuatan[date('Y', strtotime($start))] ?? 1)
                    ->where('Description', 'Profit Mitra')
                    ->sum('Month_' . date('n', strtotime($start)) . '_Total');
                $bulanList[$kunci]['bagi_hasil'] += $bagiHasilMitra;
            }


            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            foreach ($bulanList as $kunci => $value) {
                echo "<cell>" . htmlspecialchars($value['proyeksi']) . "</cell>";
                echo "<cell>" . htmlspecialchars($value['bagi_hasil']) . "</cell>";
            }
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
}
