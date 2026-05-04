<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditCategory;
use App\MasterAuditParameter;
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
            echo "<cell $warna >" . htmlspecialchars($Fetch->audit_compliance == null ? ($Fetch->corrective_score > 0 ? 'Patuh' : 'Tidak Patuh') : $Fetch->audit_compliance) . "</cell>";
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
        $corrective = WarehousePartnerAuditCorrectiveDtl::where("id", $request->Task)->first();
        $getAudit = WarehousePartnerAudit::where("nomor_audit", $corrective->nomor_audit)->first();
        $getAuditDtl = WarehousePartnerAuditDtl::where("nomor_audit", $corrective->nomor_audit)->first();
        $kat = $getAudit->kategori_temuan;
        return view('partner.warehouse.corrective-detail-corrective', compact('corrective', 'getAudit', 'getAuditDtl'));
    }

    public function detailCorrectiveDetailSubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            WarehousePartnerAuditCorrectiveDtl::where('id', $request->id)->update([
                'corrective_date' => date('Y-m-d'),
                'corrective_action' => $request->corrective_action,
                'audit_compliance' => $request->audit_compliance,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Audit berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            //throw $th;
        }
    }

    public function complianceDashboardPage(Request $r)
    {
        return view('partner.dashboard.compliance-index');
    }


    public function complianceDashboardData(Request $r)
    {
        $yearMonth = $r->date ? substr($r->date, 0, 7) : null;

        // $auditData = $this->getAuditData($r);

        // dd($auditData);

        $getTotalAudit = WarehousePartnerAudit::when($r->whid, fn($q) => $q->where('warehouse_task_id', $r->whid))
            ->when($yearMonth, fn($q) => $q->whereRaw('DATE_FORMAT(created_date, "%Y-%m") = ?', [$yearMonth]))
            ->get();

        $totalAudit     = $getTotalAudit->count();

        $getTotalViolation = DB::table('warehouse_partner_audit as aud')
            ->join('warehouse_partner_audit_corrective as wpac', 'wpac.nomor_audit', '=', 'aud.nomor_audit')
            ->join('warehouse_partner_audit_corrective_dtl as wpacd', 'wpacd.nomor_corrective', '=', 'wpac.nomor_corrective')
            ->when($r->whid, fn($q) => $q->where('aud.warehouse_task_id', $r->whid))
            ->when($yearMonth, fn($q) => $q->whereRaw('DATE_FORMAT(aud.created_date, "%Y-%m") = ?', [$yearMonth]))
            ->selectRaw("
                COUNT(*) as total_violation,
                SUM(CASE WHEN LOWER(wpacd.kategori_temuan) = 'ringan' THEN 1 ELSE 0 END) as minor,
                SUM(CASE WHEN LOWER(wpacd.kategori_temuan) = 'sedang' THEN 1 ELSE 0 END) as major,
                SUM(CASE WHEN LOWER(wpacd.kategori_temuan) = 'berat'  THEN 1 ELSE 0 END) as critical
            ")
            ->first();

        $totalViolation = $getTotalViolation->total_violation ?? 0;

        $violationType  = [
            'minor'    => $getTotalViolation->minor    ?? 0,
            'major'    => $getTotalViolation->major    ?? 0,
            'critical' => $getTotalViolation->critical ?? 0,
        ];

        $violationPerWarehouse = DB::table('warehouse_partner as wp')
            ->leftJoin('warehouse_partner_audit as aud', function ($join) use ($yearMonth) {
                $join->on('aud.warehouse_task_id', '=', 'wp.warehouse_task_id');
                if ($yearMonth) {
                    $join->whereRaw('DATE_FORMAT(aud.created_date, "%Y-%m") = ?', [$yearMonth]);
                }
            })
            ->leftJoin('warehouse_partner_audit_corrective as wpac', 'wpac.nomor_audit', '=', 'aud.nomor_audit')
            ->when($r->whid, fn($q) => $q->where('wp.warehouse_task_id', $r->whid))
            ->selectRaw("
                wp.warehouse_task_id,
                COUNT(DISTINCT aud.id)  as total_audit,
                COUNT(DISTINCT wpac.id) as total_violation
            ")
            ->groupBy('wp.warehouse_task_id')
            ->get();

        $warehouseViolations = $violationPerWarehouse->map(fn($row) => [
            'warehouse_id'    => $row->warehouse_task_id,
            'total_audit'     => $row->total_audit,
            'total_violation' => $row->total_violation,
            'compliance_rate' => $row->total_audit > 0
                ? round((1 - $row->total_violation / $row->total_audit) * 100, 2)
                : 0,
        ])->values()->all();

        $getPerStatus = DB::table('warehouse_partner_audit_corrective as wpac')
            ->join('warehouse_partner_audit as aud', 'aud.nomor_audit', '=', 'wpac.nomor_audit')
            ->when($r->whid, fn($q) => $q->where('aud.warehouse_task_id', $r->whid))
            ->when($yearMonth, fn($q) => $q->whereRaw('DATE_FORMAT(wpac.created_date, "%Y-%m") = ?', [$yearMonth]))
            ->selectRaw("
                SUM(CASE WHEN wpac.status = 'CREATED'     THEN 1 ELSE 0 END) as created,
                SUM(CASE WHEN wpac.status = 'ON_PROGRESS' THEN 1 ELSE 0 END) as on_progress,
                SUM(CASE WHEN wpac.status = 'CLOSED'      THEN 1 ELSE 0 END) as closed
            ")
            ->first();

        $status = [
            'created'     => $getPerStatus->created     ?? 0,
            'on_progress' => $getPerStatus->on_progress ?? 0,
            'closed'      => $getPerStatus->closed      ?? 0,
        ];

        $data  = [
            'total_audit' => $totalAudit,
            'total_violation' => $totalViolation,
            'violation_type' => $violationType,
            'violation_per_warehouse' => $warehouseViolations,
            'per_status' => $status
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data dashboard compliance',
            'data' => $data
        ]);
    }
}
