<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TechnicalTestController extends Controller
{
    /**
     * Halaman index monitoring & evaluasi Technical Test
     */
    public function index()
    {
        return view('humanresources.recruitment.recruitment-technical-test');
    }

    /**
     * Endpoint XML untuk dhtmlXGrid khusus status TECHNICAL_TEST
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

        // Filter kandidat yang statusnya aktif di tahapan TECHNICAL_TEST
        $query = DB::table("recruitment")
            ->where(function ($q) use ($location) {
                if (!empty($location) && $location !== 'ALL') {
                    $q->where("Location", $location);
                }
            })
            ->where(function ($q) {
                $q->where('Status', 'TECHNICAL_TEST')
                  ->orWhere('Technical_Test_Status', 'PENDING');
            })
            ->orderBy("ID", "DESC")
            ->get();

        foreach ($query as $fetch) {
            $requisition = null;
            if (!empty($fetch->Requisition_ID)) {
                $requisition = DB::table("requisition")->where("Task_ID", $fetch->Requisition_ID)->first();
            }

            $agingHari = 0;
            if ($requisition && !empty($requisition->End_Date)) {
                $endDate = strtotime($requisition->End_Date);
                $now = strtotime(date('Y-m-d'));
                $agingHari = round(($now - $endDate) / 86400, 0);
            }

            $scoreDisplay = $fetch->Technical_Score ?? ($fetch->Test_Score ?? '-');
            $testStatus = $fetch->Technical_Test_Status ?? ($fetch->Status ?? 'TECHNICAL_TEST');

            echo "<row id=\"{$fetch->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Gender ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Birth_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->Job_Position ?? ($fetch->Position ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Last_Education ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($scoreDisplay) . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->PO_No ?? ($fetch->Reference_No ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->PO_Desc ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($testStatus) . "</cell>";
            echo "<cell>" . htmlspecialchars($agingHari) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Partial view detail kandidat Technical Test via AJAX
     */
    public function detail(Request $request)
    {
        $id = $request->input('ID') ?? $request->input('id');
        $recruitment = DB::table("recruitment")->where("ID", $id)->first();

        if (!$recruitment) {
            return response('<div class="alert alert-danger m-3 shadow-sm"><i class="fa fa-triangle-exclamation me-2"></i> Data kandidat tidak ditemukan.</div>', 404);
        }

        $requisition = null;
        if (!empty($recruitment->Requisition_ID)) {
            $requisition = DB::table("requisition")->where("Task_ID", $recruitment->Requisition_ID)->first();
        }

        $recruitmentDetail = null;
        if (Schema::hasTable('recruitment_detail') && !empty($recruitment->Task_ID)) {
            $recruitmentDetail = DB::table('recruitment_detail')
                ->where('Task_ID', $recruitment->Task_ID)
                ->first();
        }

        return view('humanresources.recruitment.recruitment-technical-test-detail', [
            'recruitment'       => $recruitment,
            'requisition'       => $requisition,
            'recruitmentDetail' => $recruitmentDetail,
            'id'                => $id,
        ]);
    }
}
