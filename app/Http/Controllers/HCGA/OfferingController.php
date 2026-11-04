<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OfferingController extends Controller
{
    /**
     * Halaman index monitoring & penerbitan Offering Letter
     */
    public function index()
    {
        return view('humanresources.recruitment.recruitment-offering');
    }

    /**
     * Endpoint XML untuk dhtmlXGrid khusus status OFFERING
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

        // Filter kandidat yang statusnya aktif di tahapan OFFERING
        $query = DB::table("recruitment")
            ->where(function ($q) use ($location) {
                if (!empty($location) && $location !== 'ALL') {
                    $q->where("Location", $location);
                }
            })
            ->where('Status', 'OFFERING')
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

            $salaryExp = !empty($fetch->Salary_Expectation) ? number_format((float)$fetch->Salary_Expectation, 0, ',', '.') : '-';

            echo "<row id=\"{$fetch->ID}\">";
            echo "<cell>{$nomor}</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Task_ID ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Name ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Gender ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Birth_Date ?? '') . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->Job_Position ?? ($fetch->Position ?? '-')) . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Last_Education ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($salaryExp) . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->PO_No ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($requisition->PO_Desc ?? '-') . "</cell>";
            echo "<cell>" . htmlspecialchars($fetch->Status ?? 'OFFERING') . "</cell>";
            echo "<cell>" . htmlspecialchars($agingHari) . "</cell>";
            echo "</row>";

            $nomor++;
        }

        echo "</rows>";
    }

    /**
     * Partial view detail kandidat Offering Letter via AJAX
     */
    public function detail(Request $request)
    {
        $id = $request->input('ID') ?? $request->input('id');
        $recruitment = DB::table("recruitment")->where("ID", $id)->first();

        if (!$recruitment) {
            return response('<div class="alert alert-danger m-3">Data kandidat tidak ditemukan.</div>', 404);
        }

        $requisition = DB::table("requisition")->where("Task_ID", $recruitment->Requisition_ID)->first();

        // Cari detail ekspektasi gaji jika ada pada recruitment_detail
        $detail = null;
        if (Schema::hasTable('recruitment_detail')) {
            $detail = DB::table('recruitment_detail')
                ->where('ID', $recruitment->ID)
                ->orWhere('Task_ID', $recruitment->Task_ID)
                ->first();
        }

        return view('humanresources.recruitment.recruitment-offering-detail', [
            'recruitment' => $recruitment,
            'requisition' => $requisition,
            'detail'      => $detail,
            'id'          => $id,
        ]);
    }
}
