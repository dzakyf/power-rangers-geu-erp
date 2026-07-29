<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecruitmentProgressController extends Controller
{
    public function detail(Request $request)
    {
        $id = $request->input('ID');
        $recruitment = DB::table('recruitment')->where('ID', $id)->first();

        if (!$recruitment) {
            abort(404, 'Recruitment not found');
        }

        return view('humanresources.recruitment.recruitment-lists-detail-progress', compact('recruitment'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'ID' => 'required|integer|exists:recruitment,ID',
            'Interview_Schedule' => 'nullable|date',
            'Interview_Result' => 'nullable|numeric|min:0|max:100',
            'Psikotes_Result' => 'nullable|string',
            'MCU_Schedule' => 'nullable|date',
            'MCU_Result' => 'nullable|string',
        ]);

        $id = $request->input('ID');
        $interviewResult = $request->input('Interview_Result');
        $hasilKategori = null;

        if ($interviewResult !== null && $interviewResult !== '') {
            $hasilKategori = (float) $interviewResult >= 70 ? 'LULUS' : 'TIDAK LULUS';
        }

        DB::table('recruitment')->where('ID', $id)->update([
            'Interview_Schedule' => $request->input('Interview_Schedule'),
            'Interview_Result' => $interviewResult,
            'Psikotes_Result' => $request->input('Psikotes_Result'),
            'MCU_Schedule' => $request->input('MCU_Schedule'),
            'MCU_Result' => $request->input('MCU_Result'),
            'Hasil_Kategori' => $hasilKategori,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Progress updated.',
            'hasil_kategori' => $hasilKategori,
        ]);
    }
}
