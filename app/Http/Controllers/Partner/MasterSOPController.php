<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\MasterAuditCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterSOPController extends Controller
{
    public function indexSOP()
    {
        return view('partner.master-sop');
    }

    public function newSOP()
    {
        return view('partner.sop.new');
    }

    public function detailSOP(Request $request)
    {
        $data = MasterAuditCategory::find($request->query('ID'));

        if (!$data) {
            abort(404, 'Data SOP tidak ditemukan');
        }

        return view('partner.sop.detail', compact('data'));
    }

    public function storeSOP(Request $request)
    {
        if (!$request->filled('category')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category wajib diisi.',
            ], 422);
        }

        try {
            $data = new MasterAuditCategory();
            $data->category = $request->input('category');
            $data->category_description = (string) $request->input('category_description', '');
            $data->parameter_list = (string) $request->input('parameter_list', '');
            $data->parameter_score = (string) $request->input('parameter_score', '');
            $data->status = $request->input('status') === 'STOP' ? 'STOP' : 'NORMAL';
            $data->urutan = (MasterAuditCategory::max('urutan') ?? 0) + 1;
            $data->created_by = auth()->user()->username ?? '';
            $data->created_date = now();
            $data->save();

            return response()->json([
                'status' => 'success',
                'message' => 'SOP berhasil disimpan.',
            ]);
        } catch (\Throwable $th) {
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateSOP(Request $request)
    {
        if (!$request->filled('ID')) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID wajib diisi.',
            ], 422);
        }

        if (!$request->filled('category')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category wajib diisi.',
            ], 422);
        }

        $data = MasterAuditCategory::find($request->input('ID'));

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SOP tidak ditemukan.',
            ], 404);
        }

        try {
            $data->category = $request->input('category');
            $data->category_description = (string) $request->input('category_description', '');
            $data->parameter_list = (string) $request->input('parameter_list', '');
            $data->parameter_score = (string) $request->input('parameter_score', '');
            $data->status = $request->input('status') === 'STOP' ? 'STOP' : 'NORMAL';
            $data->save();

            return response()->json([
                'status' => 'success',
                'message' => 'SOP berhasil diperbarui.',
            ]);
        } catch (\Throwable $th) {
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function deleteSOP(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID wajib diisi.',
            ], 422);
        }

        $data = MasterAuditCategory::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SOP tidak ditemukan.',
            ], 404);
        }

        try {
            $data->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'SOP berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            Log::error($th);

            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
