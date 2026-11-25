<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\RecruitmentHrInterviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InterviewController extends Controller
{
    /**
     * @var RecruitmentHrInterviewService
     */
    protected $hrInterviewService;

    public function __construct(RecruitmentHrInterviewService $hrInterviewService)
    {
        $this->hrInterviewService = $hrInterviewService;
    }

    /**
     * Halaman index interview recruitment
     */
    public function index()
    {
        return view('humanresources.recruitment.recruitment-interview');
    }

    /**
     * Endpoint XML untuk dhtmlXGrid khusus kandidat berstatus INTERVIEW_HR
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

        $candidates = $this->hrInterviewService->getCandidateList($location);

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
            echo "<cell>" . htmlspecialchars($fetch->Status ?? 'INTERVIEW_HR') . "</cell>";
            echo "<cell>" . htmlspecialchars($agingHari) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Partial view detail interview kandidat via AJAX
     */
    public function detail(Request $request)
    {
        $id = $request->input('ID') ?? $request->input('id');
        $detailData = $this->hrInterviewService->getCandidateDetail($id);

        if (!$detailData) {
            return response('<div class="alert alert-warning m-3"><i class="fa fa-triangle-exclamation me-2"></i>Data kandidat wawancara tidak ditemukan atau telah diproses.</div>', 404);
        }

        return view('humanresources.recruitment.recruitment-interview-detail', $detailData);
    }

    /**
     * Simpan / submit keputusan & evaluasi wawancara
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID'                 => 'required|integer|exists:recruitment,ID',
            'Interview_Status'   => 'required|string',
            'Interview_Date'     => 'nullable|date',
            'Interview_Note'     => 'nullable|string',
            'Interview_Result'   => 'nullable|string',
            'Interview_File'     => 'nullable|string',
            'Last_Salary'        => 'nullable|string|max:100',
            'Salary_Expectation' => 'nullable|string|max:100',
            'Usia'               => 'nullable|string|max:50',
            'Domisili'           => 'nullable|string|max:255',
            'Computer'           => 'nullable|string',
            'Notice_Period'      => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response("error:" . $validator->errors()->first(), 422);
        }

        $currentUser = Auth::user()->username ?? 'SYSTEM';
        $result = $this->hrInterviewService->saveDecision($request->all(), $currentUser);

        if (!$result['success']) {
            return response("error:" . $result['message'], 400);
        }

        return response("success:" . $result['message'], 200)
            ->header('Content-Type', 'text/plain');
    }
}
