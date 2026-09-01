<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterWarehouseAccount;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseJournalSummaryController extends Controller
{
    public function indexWarehouseJournalMonthly()
    {
        return view('partner.warehouse.journal-monthly');
    }

    public function indexWarehouseJournalMonthlyXml(Request $request)
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
        }else {
            $get = WarehousePartner::get();
        }
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id] ?? $Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseJournalMonthly(Request $request)
    {
        $date = date("Y-m");

        return view('partner.warehouse.journal-monthly-detail', compact('date'));
    }

    public function detailWarehouseJournalMonthlyXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getAccounts = MasterWarehouseAccount::get();
        $accName = [];
        foreach ($getAccounts as $key => $value) {
            $accName[$value->account_no] = $value->account_name;
        }
        $accName[61999] = 'Logistik Penjemputan';
        $nomor = 1;

        $get = WarehousePartnerJournal::where('posting_date', 'LIKE', $request->date.'%')->where('warehouse_task_id', $request->Task)->selectRaw("account_no, SUM(nominal) as nom, MONTH(posting_date) as date")->groupBy('account_no')->groupBy('date')->get();
        $count = count($get);
        foreach ($get as $key => $value) {
            if ($value->account_no == '61007' || $value->account_no == '61008') {
                if (!isset($get[$count]->nom)) {
                    $get[$count]->nom = 0;
                }
                $get[$count]->nom += $value->nom;
            }
        }
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->account_no||$request->date\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(date('F', strtotime($request->date.'-'.$Fetch->date.'-01'))) . "</cell>";
            if ($Fetch->account_no == '61007' || $Fetch->account_no == '61008') {
                continue;
            }else {
                echo "<cell>" . htmlspecialchars($accName[$Fetch->account_no]) . "</cell>";
                echo "<cell>" . htmlspecialchars($Fetch->nom) . "</cell>";
            }
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseJournalMonthlyDetail(Request $request)
    {
        $task = explode("||", $request->Task);
        $accno = $task[0];
        $date = $task[1];
        $taskid = $request->taskid;

        return view('partner.warehouse.journal-monthly-detail-detail', compact('accno', 'date', 'taskid'));
    }

    public function detailWarehouseJournalMonthlyDetailXml(Request $request)
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

        $get = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)
            ->where('account_no', $request->accno)
            ->where("posting_date", 'LIKE', $request->date . '%')
            ->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->posting_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->account_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->nominal) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->reference) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->remark) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->attachment) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->created_by]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
    public function indexWarehouseJournalAnnually()
    {
        return view('partner.warehouse.journal-annually');
    }

    public function indexWarehouseJournalAnnuallyXml(Request $request)
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
        }else {
            $get = WarehousePartner::get();
        }
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id] ?? $Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseJournalAnnually(Request $request)
    {
        $date = date("Y");

        return view('partner.warehouse.journal-annually-detail', compact('date'));
    }

    public function detailWarehouseJournalAnnuallyXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getAccounts = MasterWarehouseAccount::get();
        $accName = [];
        foreach ($getAccounts as $key => $value) {
            $accName[$value->account_no] = $value->account_name;
        }
        $nomor = 1;

        $get = WarehousePartnerJournal::where('posting_date', 'LIKE', $request->date.'%')->where('warehouse_task_id', $request->Task)->selectRaw("account_no, SUM(nominal) as nom, YEAR(posting_date) as date")->groupBy('account_no')->groupBy('date')->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->account_no||$request->date\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(date('Y', strtotime($request->date.'-01-01'))) . "</cell>";
            echo "<cell>" . htmlspecialchars($accName[$Fetch->account_no]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->nom) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseJournalAnnuallyDetail(Request $request)
    {
        $task = explode("||", $request->Task);
        $accno = $task[0];
        $date = $task[1];
        $taskid = $request->taskid;

        return view('partner.warehouse.journal-annually-detail-detail', compact('accno', 'date', 'taskid'));
    }

    public function detailWarehouseJournalAnnuallyDetailXml(Request $request)
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

        $get = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)
            ->where('account_no', $request->accno)
            ->where("posting_date", 'LIKE', $request->date . '%')
            ->get();
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->posting_date) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->account_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->nominal) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->reference) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->remark) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->attachment) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->created_by]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
}
