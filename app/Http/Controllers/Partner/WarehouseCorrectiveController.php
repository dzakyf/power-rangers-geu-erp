<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditTemuan;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerAudit;
use App\WarehousePartnerAuditCorrectiveDtl;
use App\WarehousePartnerAuditCorretive;
use App\WarehousePartnerAuditDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseCorrectiveController extends Controller
{
    public function indexCorrective()
    {
        return view('partner.warehouse.corrective');
    }

    public function indexCorrectiveXml()
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

        $get = WarehousePartnerAuditCorretive::get();
        
        foreach ($get as $key => $Fetch) {
            $Fetchx = WarehousePartnerAudit::where('nomor_audit', $Fetch->nomor_audit)->first();
            $getGudang = WarehousePartner::where("warehouse_task_id", $Fetchx->warehouse_task_id)->first();

            $dueDate = date('Y-m-d', strtotime($Fetchx->tanggal_audit . " + $Fetch->corrective_days days"));

            $warna = "";

            if ($dueDate < date("Y-m-d") || $Fetch->kategori_temuan == 'critical') {
                $warna = "style='background-color:#D75656'";
            }
            if ($Fetch->kategori_temuan == 'major') {
                $warna = "style='background-color:#F7C85C'";
            }
            if ($Fetch->status == 'CLOSED') {
                $warna = "style='background-color:#7FB77E'";
            }

            echo "<row id=\"$Fetch->id\">";
            echo "<cell $warna >$nomor</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->nomor_audit) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetchx->tanggal_audit) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($dueDate) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetchx->warehouse_task_id) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($getGudang->warehouse_location) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetchx->auditor) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars(ucfirst($Fetchx->jenis_audit)) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->ringkasan_temuan) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->parameter_catatan) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->corrective_score) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($usname[$Fetchx->created_by]) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->corrective_action) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }
    public function detailCorrective(Request $request)
    {
        return view('partner.warehouse.corrective-detail');
    }

    public function detailCorrectiveXml(Request $request)
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

        $getDtl = WarehousePartnerAuditCorrectiveDtl::where("nomor_audit", $request->Task)->get();
        foreach ($getDtl as $key => $Fetch) {
            $Fetchx = WarehousePartnerAudit::where('nomor_audit', $Fetch->nomor_audit)->first();
            $getGudang = WarehousePartner::where("warehouse_task_id", $Fetchx->warehouse_task_id)->first();

            $dueDate = date('Y-m-d', strtotime($Fetchx->tanggal_audit . " + $Fetch->corrective_days days"));

            $warna = "";

            if ($dueDate < date("Y-m-d") || $Fetch->kategori_temuan == 'critical') {
                $warna = "style='background-color:#D75656'";
            }
            if ($Fetch->kategori_temuan == 'major') {
                $warna = "style='background-color:#F7C85C'";
            }
            if ($Fetch->status == 'CLOSED') {
                $warna = "style='background-color:#7FB77E'";
            }

            echo "<row id=\"$Fetch->id\">";
            echo "<cell $warna >$nomor</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->nomor_audit) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetchx->tanggal_audit) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($dueDate) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetchx->warehouse_task_id) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($getGudang->warehouse_location) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetchx->auditor) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars(ucfirst($Fetchx->jenis_audit)) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars(ucfirst($Fetch->kategori_temuan)) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->area_temuan) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->parameter_temuan) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->ringkasan_temuan) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->parameter_catatan) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($usname[$Fetchx->created_by]) . "</cell>";
            echo "<cell $warna >" . htmlspecialchars($Fetch->corrective_action) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function detailCorrectiveDetail(Request $request)
    {
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $corrective = WarehousePartnerAuditCorretive::where("id", $request->Task)->first();
        $getAudit = WarehousePartnerAudit::where("nomor_audit", $corrective->nomor_audit)->first();
        $getAuditDtl = WarehousePartnerAuditDtl::where("nomor_audit", $corrective->nomor_audit)->first();
        $kat = $getAudit->kategori_temuan;
        return view('partner.warehouse.corrective-detail-corrective', compact('corrective', 'getAudit', 'getAuditDtl'));
    }

    public function detailCorrectiveDetailSubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            WarehousePartnerAuditCorretive::where('id', $request->id)->update([
                'corrective_date' => date('Y-m-d'),
                'corrective_action' => $request->corrective_action,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status'=>'success',
                'message'=>'Audit berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            //throw $th;
        }
    }
}
