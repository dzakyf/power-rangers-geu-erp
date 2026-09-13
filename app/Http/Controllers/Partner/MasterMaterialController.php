<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterMaterial;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterMaterialController extends Controller
{
    public function indexMaterial()
    {
        return view('partner.training.master-material');
    }

    public function indexMaterialXml()
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

        $get = MasterMaterial::orderBy('created_date', 'desc')->get();
        
        foreach ($get as $key => $Fetch) {
            $name = $usname[$Fetch->created_by];
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_no) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_category) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_download) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_view) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_attachment) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->material_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($name) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function newMaterial()
    {
        $users = User::where('Priv_ID', '!=' , 'MITRA')->get()->pluck('Name', 'username');
        return view('partner.training.master-material-new', compact('users'));
    }

    public function userMaterialXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        // $material = isset($request->material) ? 
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::where('Priv_ID', '!=', 'MITRA')->get();
        $nomor = 1;
        
        foreach ($getUsers as $key => $Fetch) {
            echo "<row id=\"$Fetch->username\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars(0) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Position) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    public function newMaterialSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->except(['_token','undefined']);
            
            $counter = MasterMaterial::where('created_date', "LIKE", date('Y')."%")->count();
            $r['material_no'] = 'MTR/GEU/'.date("Y").'/'.sprintf("%04d", $counter + 1);
            if (isset($r['material_speaker'])) {
                $r['material_speaker'] = implode(',', $r['material_speaker']);
            }
            if ($r['material_attachment'] != '') {
                $r['material_is_upload'] = 1;
            }
            $r['material_status'] = "OPEN";
            $r['created_by'] = auth()->user()->username;
            $r['created_date'] = now();

            MasterMaterial::create($r);

            DB::commit();
    
            return response()->json([
                'status' => 'success',
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error($th);
    
            return response()->json([
                'status' => 'error',
            ]);
        }
    }

    public function detailMaterial(Request $request)
    {
        $data = MasterMaterial::where("id", $request->Task)->first();
        $users = User::where('Priv_ID', '!=', 'MITRA')->get()->pluck('Name', 'username');
        return view('partner.training.master-material-detail', compact('data', 'users'));
    }

    public function detailMaterialXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $material = MasterMaterial::where('id', $request->Task)->first();
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::where('Priv_ID', '!=', 'MITRA')->get();
        $nomor = 1;
        
        foreach ($getUsers as $key => $Fetch) {
            $warna = "";
            $checked = 0;
            if ($material->material_speaker && in_array($Fetch->username, explode(',', $material->material_speaker))) {
                $warna = "style=\"background-color: #CEF6CE\"";
                $checked = 1;
            }
            echo "<row id=\"$Fetch->username\" $warna>";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($checked) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Name) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Position) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->Department) . "</cell>";
            echo "</row>"; 
            $nomor++;
        }
        echo "</rows>";
    }


    public function detailMaterialSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $r = $request->except(['_token','undefined']);
            $cek = MasterMaterial::where('id', $r['id'])->first();
            
            if (isset($r['material_speaker'])) {
                $r['material_speaker'] = implode(',', $r['material_speaker']);
            }
            if (!isset($r['material_download']) && $cek->material_download) {
                $r['material_download'] = 0;
            }
            if (!isset($r['material_view']) && $cek->material_view) {
                $r['material_view'] = 0;
            }
            $r['material_status'] = "OPEN";

            MasterMaterial::where('id', $r['id'])->update($r);

            DB::commit();
    
            return response()->json([
                'status' => 'success',
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error($th);
    
            return response()->json([
                'status' => 'error',
            ]);
        }
    }
}
