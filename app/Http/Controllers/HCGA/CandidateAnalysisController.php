<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\RecruitmentUserInterviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CandidateAnalysisController extends Controller
{
    /**
     * @var RecruitmentUserInterviewService
     */
    protected $userInterviewService;

    public function __construct(RecruitmentUserInterviewService $userInterviewService)
    {
        $this->userInterviewService = $userInterviewService;
    }

    /**
     * Halaman index Candidate Analysis / Final User Interview
     */
    public function index()
    {
        return view('humanresources.recruitment.recruitment-candidate-analysis');
    }

    /**
     * Endpoint XML untuk dhtmlXGrid antrean kandidat tahap INTERVIEW_USER
     */
    public function data(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? '', "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo "<?xml version='1.0' encoding='iso-8859-1'?>\n";

        $user = Auth::user();
        $location = $user->Location ?? '';
        $nomor = 1;

        $candidates = $this->userInterviewService->getCandidateList($location);

        echo "<rows>";

        foreach ($candidates as $fetch) {
            $requisition = null;
            if (!empty($fetch->Requisition_ID)) {
                $requisition = DB::table("requisition")->where("Task_ID", $fetch->Requisition_ID)->first();
            }

            $jobPosition = $requisition ? $requisition->Job_Position : ($fetch->Position ?? '-');
            $poNo = $requisition ? ($requisition->PO_No ?? '-') : '-';
            $poDesc = $requisition ? ($requisition->PO_Desc ?? '-') : '-';

            $agingHari = 0;
            if ($requisition && !empty($requisition->End_Date) && $requisition->End_Date !== '0000-00-00') {
                $endDate = strtotime($requisition->End_Date);
                $now = strtotime(date('Y-m-d'));
                $agingHari = round(($now - $endDate) / 86400, 0);
            }

            echo "<row id=\"{$fetch->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Gender ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Birth_Date ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($jobPosition) . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Last_Education ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($poNo) . "</cell>";
            echo "<cell>" . htmlspecialchars($poDesc) . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Status ?? 'INTERVIEW_USER') . "</cell>";
            echo "<cell>" . htmlspecialchars($agingHari) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Partial view detail evaluasi kandidat via AJAX
     */
    public function detail(Request $request)
    {
        $id = $request->input('ID') ?? $request->input('id');
        $detailData = $this->userInterviewService->getCandidateDetail($id);

        if (!$detailData) {
            return response('<div class="alert alert-warning m-3"><i class="fa fa-triangle-exclamation me-2"></i>Data kandidat tidak ditemukan atau telah diproses.</div>', 404);
        }

        return view('humanresources.recruitment.recruitment-candidate-analysis-detail', $detailData);
    }

    /**
     * Simpan hasil keputusan wawancara user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID'                    => 'required|integer|exists:recruitment,ID',
            'User_Interview_Status' => 'required|string',
            'User_Interview_Date'   => 'nullable|date',
            'User_Interview_Result' => 'nullable|string',
            'User_Interview_Note'   => 'nullable|string',
            'User_Interview_File'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response("error:" . $validator->errors()->first(), 422);
        }

        $currentUser = Auth::user()->username ?? 'SYSTEM';
        $result = $this->userInterviewService->saveDecision($request->all(), $currentUser);

        if (!$result['success']) {
            return response("error:" . $result['message'], 400);
        }

        return response("success:" . $result['message'], 200)
            ->header('Content-Type', 'text/plain');
    }
}

