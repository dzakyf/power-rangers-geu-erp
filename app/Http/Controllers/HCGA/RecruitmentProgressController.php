<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\RecruitmentFlowService;

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
        $validator = Validator::make($request->all(), [
            'ID' => 'required|integer|exists:recruitment,ID',
            'Interview_Schedule' => 'nullable|date',
            'Interview_Result' => 'nullable|numeric|min:0|max:100',
            'Psikotes_Result' => 'nullable|string|max:500',
            'MCU_Schedule' => 'nullable|date',
            'MCU_Result' => 'nullable|string|max:500',
            'Technical_Score' => 'nullable|numeric|min:0|max:100',
            'Technical_Note' => 'nullable|string|max:500',
            'Technical_File' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id = $request->input('ID');
        $interviewResult = $request->input('Interview_Result');
        $hasilKategori = null;

        if ($interviewResult !== null && $interviewResult !== '') {
            $hasilKategori = (float) $interviewResult >= 70 ? 'LULUS' : 'TIDAK LULUS';
        }

        $updateData = [
            'Interview_Schedule' => $request->input('Interview_Schedule'),
            'Interview_Result' => $interviewResult,
            'Psikotes_Result' => $request->input('Psikotes_Result'),
            'MCU_Schedule' => $request->input('MCU_Schedule'),
            'MCU_Result' => $request->input('MCU_Result'),
            'Hasil_Kategori' => $hasilKategori,
            'Technical_Score' => $request->input('Technical_Score'),
            'Technical_Note' => $request->input('Technical_Note'),
            'Technical_File' => $request->input('Technical_File'),
        ];

        // Hapus field yang null/empty kecuali string kosong yang valid
        $updateData = array_filter($updateData, function($value) {
            return $value !== null;
        });

        DB::table('recruitment')->where('ID', $id)->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Progress updated.',
            'hasil_kategori' => $hasilKategori,
        ]);
    }

    /**
     * Progress tahapan rekrutmen (dipanggil dari grid atau halaman detail)
     */
    public function progress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:recruitment,ID',
            'stage' => 'required|string|in:verify_document,approve_psikotest,approve_interview_hr,approve_interview_user,approve_technical_test,approve_mcu,issue_offering',
            'note' => 'nullable|string|max:500',
            'score' => 'nullable|numeric|min:0|max:100',
            'result' => 'nullable|string|max:500',
            'schedule' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id = $request->input('id');
        $stage = $request->input('stage');
        $note = $request->input('note');
        $score = $request->input('score');
        $result = $request->input('result');
        $schedule = $request->input('schedule');

        // Mapping stage ke method service
        $methodMap = [
            'verify_document'      => 'verifyDocuments',
            'approve_psikotest'    => 'approvePsikotest',
            'approve_interview_hr' => 'approveInterviewHR',
            'approve_interview_user' => 'approveInterviewUser',
            'approve_technical_test' => 'approveTechnicalTest',
            'approve_mcu'          => 'approveMCU',
            'issue_offering'       => 'issueOfferingLetter',
        ];

        if (!isset($methodMap[$stage])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stage tidak valid',
            ], 400);
        }

        $method = $methodMap[$stage];
        $data = [
            'note' => $note,
            'score' => $score,
            'result' => $result,
            'schedule' => $schedule,
        ];

        // Filter null values
        $data = array_filter($data, function($v) { return $v !== null; });

        $serviceResult = RecruitmentFlowService::$method($id, $data);

        if (!$serviceResult['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $serviceResult['message'],
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => $serviceResult['message'],
            'data' => [
                'recruitment_id' => $id,
                'next_stage' => $serviceResult['next_stage'] ?? null,
                'offering_letter_number' => $serviceResult['data']['offering_letter_number'] ?? null,
            ],
        ]);
    }
}
