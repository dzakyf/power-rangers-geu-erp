<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerAudit;
use App\WarehousePartnerAuditDtl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseAuditController extends Controller
{
    public function indexWarehouseAudit()
    {
        return view('partner.warehouse.audit');
    }

    public function getNomorWarehouseAudit(Request $request)
    {
        $year = date('Y', strtotime($request->date));
        $count = WarehousePartnerAudit::where("tanggal_audit", 'LIKE', $year.'%')->count() + 1;
        $nomor = 'ADT/GEU/'.$year.'/'.sprintf('%04d', $count);
        return response()->json($nomor);
    }

    public function getGudangWarehouseAudit(Request $request)
    {
        if (strlen(trim($request->chars)) < 3) exit;

        $arr=array();
        $result = WarehousePartner::select(['warehouse_partner.partner_task_id','warehouse_partner.warehouse_location','warehouse_partner.warehouse_task_id','opportunity_partner.full_name'])
        ->leftJoin('opportunity_partner', 'warehouse_partner.partner_task_id','=','opportunity_partner.lead_id')
        ->where(function($q) use ($request) {
            $q->where('warehouse_partner.warehouse_task_id','LIKE', "%$request->chars%")
            ->orWhere('warehouse_partner.partner_task_id','LIKE', "%$request->chars%")
            ->orWhere('warehouse_partner.warehouse_location','LIKE', "%$request->chars%")
            ->orWhere('opportunity_partner.full_name','LIKE', "%$request->chars%");
        })
        ->where('opportunity_partner.full_name', '!=', null)
        ->where('warehouse_partner.warehouse_status', 'ACTIVE')
        ->limit(20)
        ->get();
        

        if(count($result)>0){
            foreach($result as $data){
                
                $Description= "Hub No: ". $data->warehouse_task_id."<br>BP No: ".$data->partner_task_id."<br>Full Name : ".$data->full_name;
                $arr[]=array("id" => trim($data->warehouse_task_id), "data" => $data->partner_task_id, "description" => $Description, 'location' => $data->warehouse_location);
            }
        }

        echo json_encode($arr);
    }

    public function indexWarehouseAuditXml()
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

        $get = WarehousePartnerAudit::get();
        
        foreach ($get as $key => $Fetch) {
            $getGudang = WarehousePartner::where("warehouse_task_id", $Fetch->warehouse_task_id)->first();
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->nomor_audit) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->tanggal_audit) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($getGudang->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->auditor) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->jenis_audit) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->kategori_temuan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ringkasan_temuan) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->created_by]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function newWarehouseAudit()
    {
        $getGudang = WarehousePartner::get();
        $year = date('Y');
        $count = WarehousePartnerAudit::where("tanggal_audit", 'LIKE', $year.'%')->count() + 1;
        $nomor = 'ADT/GEU/'.$year.'/'.sprintf('%04d', $count);
        return view('partner.warehouse.audit-new', compact('getGudang', 'nomor'));
    }

    public function newWarehouseAuditSubmit(Request $request)
    {
        DB::beginTransaction();

        $details = [
            'kondisi1',
            'kondisi2',
            'kondisi3',
            'kondisi4',
            'kondisi5',
            'k31',
            'k32',
            'k33',
            'k34',
            'k35',
            'warna',
            'bau',
            'apd',
            'tumpahan',
            'apar',
            'rambu',
            'adm1',
            'adm2',
            'adm3',
            'adm4',
        ];
        
        $r = $request->all();

        try{

            $audit = WarehousePartnerAudit::create([
                'tanggal_audit'=>$request->tanggal_audit,
                'jenis_audit'=>$request->jenis_audit,
                'nomor_audit'=>$request->nomor_audit,
                'warehouse_task_id'=>$request->warehouse_task_id,
                'kode_hub'=>$request->kode_hub,
                'warehouse_location'=>$request->warehouse_location,
                'auditor'=>$request->auditor,
                'ringkasan_temuan'=>$request->ringkasan_temuan,
                'kategori_temuan'=>$request->kategori_temuan,
                'foto_lokasi'=>$request->foto_lokasi ?? false,
                'bukti_pendukung'=>$request->bukti_pendukung ?? false,
                'verifikasi'=>$request->verifikasi,
                'created_by' => auth()->user()->username,
                'auditee'=>$request->auditee
            ]);

            foreach($details as $detail){

                WarehousePartnerAuditDtl::create([
                    'nomor_audit'=>$request->nomor_audit,
                    'section'=>$detail,
                    'parameter'=>$request->$detail,
                    'value'=>$request->$detail,
                    'catatan'=>$r[$detail.'_note'] ?? null,
                    'created_by' => auth()->user()->username,
                ]);

            }

            DB::commit();

            return response()->json([
                'status'=>'success',
                'message'=>'Audit berhasil disimpan'
            ]);

        }catch(\Exception $e){

            DB::rollBack();

            return response()->json([
                'status'=>'error',
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function detailWarehouseAudit(Request $request)
    {
        $getAudit = WarehousePartnerAudit::where("id", $request->Task)->first();
        $getAuditDtl = WarehousePartnerAuditDtl::where("nomor_audit", $getAudit->nomor_audit)->get();
        return view('partner.warehouse.audit-detail', compact('getAudit', 'getAuditDtl'));
    }

    public function detailWarehouseAuditSubmit()
    {
        //
    }
}
