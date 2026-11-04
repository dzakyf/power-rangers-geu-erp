<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PsikotestController extends Controller
{
    /**
     * Halaman index monitoring & hasil Psikotest
     */
    public function index()
    {
        return view('humanresources.recruitment.recruitment-psikotest');
    }

    /**
     * Endpoint XML untuk dhtmlXGrid khusus status PSIKOTEST
     */
    public function data()
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

        // Filter kandidat yang statusnya aktif di tahapan PSIKOTEST
        $query = DB::table("recruitment")
            ->where(function ($q) use ($location) {
                if (!empty($location) && $location !== 'ALL') {
                    $q->where("Location", $location);
                }
            })
            ->where('Status', 'PSIKOTEST')
            ->orderBy("ID", "DESC")
            ->get();

        foreach ($query as $fetch) {
            $requisition = DB::table("requisition")->where("Task_ID", $fetch->Requisition_ID)->first();

            $agingHari = 0;
            if ($requisition && !empty($requisition->End_Date)) {
                $endDate = strtotime($requisition->End_Date);
                $now = strtotime(date('Y-m-d'));
                $agingHari = round(($now - $endDate) / 86400, 0);
            }

            echo "<row id=\"{$fetch->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Gender ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Birth_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->Job_Position ?? ($fetch->Position ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Last_Education ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->PO_No ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->PO_Desc ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Status ?? 'PSIKOTEST') . "</cell>";
            echo "<cell>" . htmlspecialchars($agingHari) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Partial view detail kandidat psikotest via AJAX
     */
    public function detail(Request $request)
    {
        $id = $request->input('ID') ?? $request->input('id');
        $recruitment = DB::table("recruitment")->where("ID", $id)->first();

        if (!$recruitment) {
            return response('<div class="alert alert-danger m-3">Data kandidat tidak ditemukan.</div>', 404);
        }

        $requisition = DB::table("requisition")->where("Task_ID", $recruitment->Requisition_ID)->first();

        return view('humanresources.recruitment.recruitment-psikotest-detail', [
            'recruitment' => $recruitment,
            'requisition' => $requisition,
            'id'          => $id,
        ]);
    }

    /**
     * Simpan / submit hasil psikotest kandidat
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID'               => 'required|integer|exists:recruitment,ID',
            'Psikotest_Score'  => 'nullable|numeric|min:0|max:100',
            'Psikotest_Result' => 'nullable|string|max:100',
            'Psikotest_Note'   => 'nullable|string|max:500',
            'Psikotest_File'   => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $id = $request->input('ID');
        $score = $request->input('Psikotest_Score');
        $result = $request->input('Psikotest_Result');
        $note = $request->input('Psikotest_Note');
        $file = $request->input('Psikotest_File');

        $updateData = [
            'Psikotest_Score' => $score,
            'Psikotest_Note'  => $note,
            'Psikotest_File'  => $file,
            'Modified_By'     => Auth::user()->username ?? 'SYSTEM',
            'Modified_Date'   => now(),
        ];

        // Pastikan menyimpan ke kolom Psikotes_Result (kolom resmi di tabel)
        if (Schema::hasColumn('recruitment', 'Psikotes_Result')) {
            $updateData['Psikotes_Result'] = $result;
        }
        if (Schema::hasColumn('recruitment', 'Psikotest_Result')) {
            $updateData['Psikotest_Result'] = $result;
        }

        DB::table('recruitment')->where("ID", $id)->update($updateData);

        return response("success:Data Psikotest Berhasil Disimpan", 200)
            ->header('Content-Type', 'text/plain');
    }
}
