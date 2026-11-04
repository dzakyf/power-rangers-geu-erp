<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class InterviewController extends Controller
{
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

        echo "<rows>";

        // Filter kandidat yang secara spesifik berstatus INTERVIEW_HR
        $query = DB::table("recruitment")
            ->where(function ($q) use ($location) {
                if (!empty($location) && $location !== 'ALL') {
                    $q->where("Location", $location);
                }
            })
            ->where('Status', 'INTERVIEW_HR')
            ->orderBy("ID", "DESC")
            ->get();

        foreach ($query as $fetch) {
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
        $datahasil = DB::table("recruitment")->where("ID", $id)->first();

        if (!$datahasil) {
            return response('<div class="alert alert-warning m-3"><i class="fa fa-triangle-exclamation me-2"></i>Data kandidat wawancara tidak ditemukan atau telah diproses.</div>', 404);
        }

        $datahasil2 = null;
        if (Schema::hasTable('recruitment_detail')) {
            $datahasil2 = DB::table("recruitment_detail")
                ->where("ID", $datahasil->ID)
                ->orWhere("Task_ID", $datahasil->Task_ID)
                ->first();
        }

        $requisition = null;
        if (!empty($datahasil->Requisition_ID)) {
            $requisition = DB::table("requisition")->where("Task_ID", $datahasil->Requisition_ID)->first();
        }

        return view('humanresources.recruitment.recruitment-interview-detail', [
            'datahasil'   => $datahasil,
            'datahasil2'  => $datahasil2,
            'requisition' => $requisition,
            'id'          => $id,
        ]);
    }

    /**
     * Simpan / submit keputusan & evaluasi wawancara
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID'               => 'required|integer|exists:recruitment,ID',
            'Interview_Status' => 'required|string',
            'Interview_Date'   => 'nullable|date',
            'Interview_Result' => 'nullable|string',
            'Interview_Note'   => 'nullable|string',
            'Interview_File'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response("error:" . $validator->errors()->first(), 422);
        }

        $id = $request->input('ID');
        $currentUser = Auth::user()->username ?? 'SYSTEM';
        $interviewStatus = $request->input('Interview_Status');

        // Tentukan alur status berikutnya
        if ($interviewStatus === 'ACCEPT') {
            // Jika diterima di interview HR, lanjut ke INTERVIEW_USER atau INTERVIEWED
            $statusnya = 'INTERVIEW_USER';
        } elseif ($interviewStatus === 'PENDING') {
            $statusnya = 'PENDING';
        } else {
            $statusnya = $interviewStatus;
        }

        $recruitmentUpdate = [
            'Interview_File'   => $request->input('Interview_File') ?? '',
            'Interview_By'     => $currentUser,
            'Interview_Date'   => $request->input('Interview_Date') ?: date('Y-m-d'),
            'Interview_Result' => $request->input('Interview_Result') ?? '',
            'Interview_Status' => $interviewStatus,
            'Interview_Note'   => $request->input('Interview_Note') ?? '',
            'Status'           => $statusnya,
        ];

        if (Schema::hasColumn('recruitment', 'Modified_By')) {
            $recruitmentUpdate['Modified_By'] = $currentUser;
        }
        if (Schema::hasColumn('recruitment', 'Modified_Date')) {
            $recruitmentUpdate['Modified_Date'] = now();
        }

        DB::table('recruitment')->where('ID', $id)->update($recruitmentUpdate);

        // Update kuesioner pelamar pada tabel recruitment_detail jika ada data
        if (Schema::hasTable('recruitment_detail')) {
            $candidate = DB::table('recruitment')->where('ID', $id)->first();
            $detailFields = [
                'Describe_Self', 'Left_Job', 'Previous_Boss', 'Describe_Know',
                'Describe_Improve', 'Describe_Looking', 'Describe_Interest',
                'Describe_Long', 'Describe_Target', 'Salary_Expectation',
                'Facility_Expectation', 'Environment', 'Free_Time',
                'Describe_Strong', 'Describe_Weak', 'Overtime', 'Holiday',
                'Shift_Work', 'Out_Town', 'Computer', 'Questions_Us'
            ];

            $detailUpdate = [];
            foreach ($detailFields as $field) {
                if ($request->has($field)) {
                    $detailUpdate[$field] = $request->input($field);
                }
            }

            if (!empty($detailUpdate) && $candidate) {
                $existingDetail = DB::table('recruitment_detail')
                    ->where('ID', $candidate->ID)
                    ->orWhere('Task_ID', $candidate->Task_ID)
                    ->first();

                if ($existingDetail) {
                    DB::table('recruitment_detail')->where('ID', $existingDetail->ID)->update($detailUpdate);
                } else {
                    $detailUpdate['ID'] = $candidate->ID;
                    $detailUpdate['Task_ID'] = $candidate->Task_ID;
                    DB::table('recruitment_detail')->insert($detailUpdate);
                }
            }
        }

        return response("success:Keputusan Wawancara Berhasil Disimpan", 200)
            ->header('Content-Type', 'text/plain');
    }
}

