<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterWarehouseAccount;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseJournalController extends Controller
{
    public function indexWarehouseJournal()
    {
        return view('partner.warehouse.journal');
    }

    public function indexWarehouseJournalXml()
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
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailWarehouseJournal(Request $reuqest)
    {
        return view('partner.warehouse.journal-detail');
    }

    public function detailWarehouseJournalXml(Request $request)
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

        $get = WarehousePartnerJournal::where('warehouse_task_id', $request->Task)->get();
        
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

    public function detailWarehouseJournalNew(Request $request)
    {
        $masterAccount = MasterWarehouseAccount::where('group_account', '>', 501)->get();
        return view('partner.warehouse.journal-detail-new', compact('masterAccount'));
    }

    public function detailWarehouseJournalNewSubmit(Request $request)
    {
        $jno = "JVC-OPX-".date('ymdhis');
        $r = $request->all();

        if ($r['account_no'] == '') {
            echo 'error';
            return;
        }
        
        $cekAkun = MasterWarehouseAccount::where('account_no', $r['account_no'])->first();

        if (!$cekAkun) {
            echo "error";
            return;
        }

        unset($r['_token']);
        
        $r['created_by'] = auth()->user()->username;
        $r['created_date'] = now();
        $r['group_account'] = $cekAkun->group_account;
        $r['account_name'] = $cekAkun->account_name;
        $r['journal_no'] = $jno;
        $r['reference'] = $r['reference'] ?? '';
        $r['remark'] = $r['remark'] ?? '';

        WarehousePartnerJournal::create($r);

        echo 'success';
    }

    public function detailWarehouseJournalDetail(Request $request)
    {
        $masterAccount = MasterWarehouseAccount::where('group_account', '>', 501)->get();
        $whj = WarehousePartnerJournal::where("id", $request->Task)->first();
        return view('partner.warehouse.journal-detail-detail', compact('masterAccount', 'whj'));
    }

    public function detailWarehouseJournalDetailSubmit(Request $request)
    {

        if ($request->account_no == '') {
            echo 'error';
            return;
        }
        
        $cekAkun = MasterWarehouseAccount::where('account_no', $request->account_no)->first();

        if (!$cekAkun) {
            echo "error";
            return;
        }
        
        WarehousePartnerJournal::where('id', $request->id)->update([
            'account_no' => $cekAkun->account_no,
            'account_name' => $cekAkun->account_name,
            'reference' => $request->reference ?? '',
            'remark' => $request->remark ?? '',
        ]);

        echo 'success';
    }
}
