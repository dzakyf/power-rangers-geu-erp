<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditCategory;
use App\MasterAuditParameter;
use App\MasterAuditTemuan;
use App\User;
use App\WarehousePartner;
use App\WarehousePartnerAudit;
use App\WarehousePartnerAuditCorretive;
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
        $nomor = 'AUDIT/GEU/'.$year.'/'.sprintf('%04d', $count);
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
            echo "<cell>" . htmlspecialchars(ucfirst($Fetch->jenis_audit)) . "</cell>";
            echo "<cell>" . htmlspecialchars(ucfirst($Fetch->kategori_temuan)) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->ringkasan_temuan) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->total_score) . "</cell>";
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
        $nomor = 'AUDIT/GEU/'.$year.'/'.sprintf('%04d', $count);
        $temuan = MasterAuditTemuan::all();
        $users = User::where("Priv_ID", '!=', 'MITRA')->where('username', '!=', 'admin')->get();
        $category = MasterAuditCategory::where('status', 'normal')->orderBy('urutan', 'asc')->get();
        $param = [];
        foreach ($category as $key => $value) {
            $param[$value->category] = MasterAuditParameter::where("parameter_status", "NORMAL")->where('category', $value->category)->orderBy('parameter_id')->get();
        }

        $urut = range('A', 'Z');

        return view('partner.warehouse.audit-new', compact('getGudang', 'nomor', 'users', 'temuan', 'param', 'category', 'urut'));
    }

    public function newWarehouseAuditSubmit(Request $request)
    {
        DB::beginTransaction();

        $details = MasterAuditParameter::get();

        $nilai = [
            'kondisi' => [
                'sesuai' => 5,
                'tidak' => 0
            ],
            'k3' =>  [
                'sesuai' => 5,
                'tidak' => 0
            ],
            'warna' =>  [
                'baik' => 7.5,
                'cukup' => 4,
                'buruk' => 0
            ],
            'bau' =>  [
                'baik' => 7.5,
                'cukup' => 4,
                'buruk' => 0
            ],
            'apd' =>  [
                'ada' => 5,
                'tidak' => 0
            ],
            'tumpahan' =>  [
                'ada' => 5,
                'tidak' => 0
            ],
            'apar' =>  [
                'ada' => 5,
                'tidak' => 0
            ],
            'rambu' =>  [
                'ada' => 5,
                'tidak' => 0
            ],
            'adm' =>  [
                'sesuai' => 3.75,
                'tidak' => 0
            ],
        ];
        
        $r = $request->all();

        try{

            $totalScoring = 0;

            $audit = WarehousePartnerAudit::create([
                'tanggal_audit'=>$request->tanggal_audit,
                'jenis_audit'=>$request->jenis_audit,
                'nomor_audit'=>$request->nomor_audit,
                'warehouse_task_id'=>$request->warehouse_task_id,
                'kode_hub'=>$request->kode_hub,
                'warehouse_location'=>$request->warehouse_location,
                'auditor'=>$request->auditor,
                'ringkasan_temuan'=>$request->ringkasan_temuan ?? '',
                'kategori_temuan'=>$request->kategori_temuan,
                'foto_lokasi'=>$request->foto_lokasi ?? false,
                'foto_lokasi_path'=>$request->foto_lokasi_path ?? '',
                'bukti_pendukung'=>$request->bukti_pendukung ?? false,
                'bukti_pendukung_path'=>$request->bukti_pendukung_path ?? '',
                'verifikasi'=>$request->verifikasi,
                'created_by' => auth()->user()->username,
                'auditee'=>$request->auditee,
                'area_temuan' => $request->detail_temuan,
            ]);

            foreach($details as $detail){
                $pecahList = explode(',', $detail->parameter_list);
                $pecahScore = explode(',', $detail->parameter_score);
                $scoreList = [];
                for ($i=0; $i < count($pecahList); $i++) { 
                    $scoreList[$pecahList[$i]] = $pecahScore[$i];
                }

                $scoring = $scoreList[$r[$detail->parameter_id]];

                $totalScoring += $scoring;

                WarehousePartnerAuditDtl::create([
                    'nomor_audit' => $request->nomor_audit,
                    'section' => $detail->parameter_id,
                    'parameter' => $r[$detail->parameter_id],
                    'value' => $scoring,
                    'catatan' => $r[$detail->parameter_id.'_note'] ?? null,
                    'created_by' => auth()->user()->username,
                ]);

            }

            WarehousePartnerAudit::where('id', $audit->id)->update([
                'total_score' => $totalScoring,
            ]);

            $temuan = explode("||", $request->detail_temuan);

            $masterCorrective = MasterAuditTemuan::where("area", $temuan[0])->where('parameter', $temuan[1])->first();

            WarehousePartnerAuditCorretive::create([
                'nomor_audit' => $request->nomor_audit,
                'kategori_temuan' => $request->kategori_temuan,
                'ringkasan_temuan' => $request->ringkasan_temuan ?? '',
                'area_temuan' => $temuan[0],
                'corrective_days' => $masterCorrective->ca_days,
                'parameter_temuan' => $temuan[1],
                'status' => 'CREATED',
                'created_by' => auth()->user()->username,
                'created_date' => now(),
            ]);

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
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
        }
        $getAudit = WarehousePartnerAudit::where("id", $request->Task)->first();
        $getAuditDtl = WarehousePartnerAuditDtl::where("nomor_audit", $getAudit->nomor_audit)->get();
        $kat = 'critical';
        if ($getAudit->kategori_temuan == 'ringan') {
            $kat = 'minor';
        }else if($getAudit->kategori_temuan == 'sedang') {
            $kat = 'major';
        }
        $masterTemuan = MasterAuditTemuan::where("category", $kat)->get();
        if (isset($usname[$getAudit->auditor])) {
            $getAudit->auditor = $usname[$getAudit->auditor];
        }
        if (isset($usname[$getAudit->auditee])) {
            $getAudit->auditee = $usname[$getAudit->auditee];
        }
        if (isset($usname[$getAudit->verifikasi])) {
            $getAudit->verifikasi = $usname[$getAudit->verifikasi];
        }
        $category = MasterAuditCategory::where('status', 'normal')->orderBy('urutan', 'asc')->get();
        $param = [];
        foreach ($category as $key => $value) {
            $param[$value->category] = MasterAuditParameter::where("parameter_status", "NORMAL")->where('category', $value->category)->orderBy('parameter_id')->get();
        }

        $urut = range('A', 'Z');
        
        return view('partner.warehouse.audit-detail', compact('getAudit', 'getAuditDtl', 'masterTemuan', 'usname', 'param', 'category', 'urut'));
    }

    public function detailWarehouseAuditSubmit()
    {
        //
    }

    public function getTemuan(Request $request)
    {
        $nilai = 'critical';
        if ($request->nilai == 'ringan') {
            $nilai = 'minor';
        }else if($request->nilai == 'sedang') {
            $nilai = 'major';
        }

        $res = MasterAuditTemuan::where('category', $nilai)->get();

        $result = [
            'status' => 'success',
            'data' => $res,
        ];

        echo json_encode($result);
        
    }
}
