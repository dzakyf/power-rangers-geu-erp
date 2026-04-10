<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KPIController extends Controller
{
    public function index()
    {
        return view('partner.performance.index');
    }

    public function createMasterKPIData()
    {
        return view('partner.performance.create');
    }


    public function getMasterKPIData()
    {
        return response(200);
    }

    public function submitMasterKPIData(Request $r)
    {
        $b = $r->all();

        try {
            DB::beginTransaction();

            $id = DB::table('config_kpi')->insertGetId([
                'Work_Area'   => $b['Work_Area'],
                'Indicator'   => $b['Indicator'],
                'Weight'      => $b['Weight'],
                'Target'      => $b['Target'],
                'Periode'     => $b['Periode'],
                'Departement' => $b['Departement'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data berhasil disimpan',
                'id'      => $id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateMasterKPIData(Request $r)
    {
        $b = $r->all();

        try {
            DB::beginTransaction();

            $existing = DB::table('config_kpi')->where('ID', $b['ID'])->first();

            if (!$existing) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('config_kpi')->where('ID', $b['ID'])->update([
                'Work_Area'   => $b['Work_Area'],
                'Indicator'   => $b['Indicator'],
                'Weight'      => $b['Weight'],
                'Target'      => $b['Target'],
                'Periode'     => $b['Periode'],
                'Departement' => $b['Departement'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data berhasil diupdate'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteMasterKPIData(Request $r)
    {
        $b = $r->all();

        try {
            DB::beginTransaction();

            $existing = DB::table('config_kpi')->where('ID', $b['ID'])->first();

            if (!$existing) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('config_kpi')->where('ID', $b['ID'])->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
