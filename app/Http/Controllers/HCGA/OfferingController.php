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

    /**
     * Download / Print PDF Dokumen Offering Letter resmi
     * GET /hrd-recruitment-offering-letter-pdf?ID={id}
     */
    public function printPdf(Request $request)
    {
        $id = $request->input('ID') ?? $request->input('id');
        $recruitment = DB::table('recruitment')->where('ID', $id)->first();

        if (!$recruitment) {
            abort(404, 'Data kandidat Offering Letter tidak ditemukan.');
        }

        $requisition = null;
        if (!empty($recruitment->Requisition_ID)) {
            $requisition = DB::table('requisition')->where('Task_ID', $recruitment->Requisition_ID)->first();
        }

        // Ambil dari query string (bila sedang live preview dari form) atau fallback ke database
        $salaryTotal = $request->filled('salary') ? (float)$request->input('salary') : (float) ($recruitment->Salary_Accepted ?? ($recruitment->Salary_Expectation ?? 0));
        $salaryBase  = $request->filled('salary_base') ? (float)$request->input('salary_base') : (float) ($recruitment->Salary_Base ?? 0);
        $salaryCola  = $request->filled('salary_cola') ? (float)$request->input('salary_cola') : (float) ($recruitment->Salary_Cola ?? 0);
        $salaryAllowance = $request->filled('salary_allowance') ? (float)$request->input('salary_allowance') : (float) ($recruitment->Salary_Allowance ?? 0);

        if ($salaryBase <= 0 && $salaryTotal > 0) {
            $salaryBase = $salaryTotal;
        }

        // Komponen rincian gaji dinamis (custom salary items)
        $salaryDetails = [];
        if ($request->filled('salary_details')) {
            $rawDetails = $request->input('salary_details');
            if (is_string($rawDetails)) {
                $salaryDetails = json_decode($rawDetails, true) ?: [];
            } elseif (is_array($rawDetails)) {
                $salaryDetails = $rawDetails;
            }
        } elseif (!empty($recruitment->Salary_Details_Json)) {
            $salaryDetails = json_decode($recruitment->Salary_Details_Json, true) ?: [];
        }

        // Catatan periode penggajian default
        $salaryPeriodNote = $request->input('salary_period_note') 
            ?: ($recruitment->Salary_Period_Note ?? 'Periode Penggajian 26 s/d 25 (bulan ke bulan) dibayarkan tgl 1 - 5 (bulan berjalan).');

        // Siapkan variabel tampilan mengikuti template (prioritas: input live preview -> database -> auto-detect master_position/org -> default)
        $position = $request->input('position') ?: ($recruitment->Position ?: ($requisition->Job_Position ?? 'Kru Gudang'));
        $grading = $request->input('grading') ?: ($recruitment->Grading ?: '11');
        $workLocation = $request->input('work_location') ?: ($recruitment->Work_Location ?: ($recruitment->Location ?: ($requisition->Location ?? 'Malang')));
        $contractType = $request->input('contract_type') ?: ($recruitment->Contract_Type ?: 'Kontrak 1 tahun dengan masa evaluasi kerja 3 bulan');

        // Resolusi Atasan Langsung (Direct Supervisor)
        $onboardingService = app(\App\Services\HCGA\RecruitmentOnboardingService::class);

        $directSupervisor = $request->input('direct_supervisor');
        if (empty($directSupervisor)) {
            $directSupervisor = $recruitment->Direct_Supervisor;
        }
        if (empty($directSupervisor)) {
            $resolvedSupervisor = $onboardingService->resolveDirectSupervisor($position, $requisition->Department ?? ($recruitment->Department ?? null));
            $directSupervisor = $resolvedSupervisor['display'] ?? ($resolvedSupervisor['supervisor_title'] ?? 'Head');
        }

        // Resolusi Hari dan Jam Kerja dari Requisition Work_Schedule (work_calendars)
        $workingSchedule = $request->input('working_schedule');
        if (empty($workingSchedule)) {
            $workingSchedule = $recruitment->Working_Schedule;
        }
        if (empty($workingSchedule)) {
            $resolvedSchedule = $onboardingService->resolveWorkingSchedule($requisition->Work_Schedule ?? null);
            $workingSchedule = $resolvedSchedule['schedule_text'];
        }

        $benefitDesc = $request->input('benefit_description') ?: ($recruitment->Benefit_Description ?: "Benefit :\nUang Lembur\nBPJS Kesehatan & Ketenagakerjaan sesuai Peraturan Pemerintah.\nTHR Setelah 12 bulan masa kerja");

        $offeringTerms = $request->input('offering_terms') ?: ($recruitment->Offering_Terms ?: "Dokumen Offering Letter ini bersifat sementara namun mengikat kedua belah pihak. Apabila membatalkan perjanjian maka yang bersangkutan akan dikenakan denda 1x total salary, sebagai kompensasi biaya proses rekrutmen.\nPT. Green Energi Utama berhak memperpanjang masa kontrak dan menghentikan masa kontrak sesuai dengan kebutuhan perusahaan.");

        $reqStartDate = $request->input('start_date') ?: $recruitment->Start_Date;
        $startDateFormatted = !empty($reqStartDate) && $reqStartDate !== '0000-00-00'
            ? \Carbon\Carbon::parse($reqStartDate)->translatedFormat('d F Y')
            : \Carbon\Carbon::now()->addDays(7)->translatedFormat('d F Y');

        $cityDate = ($workLocation ?: 'Malang') . ', ' . \Carbon\Carbon::now()->translatedFormat('d F Y');

        $offeringNumber = $request->input('offering_number') 
            ?: ($recruitment->Offering_Number ?? ('OF/' . date('Y') . '/' . str_pad($recruitment->ID ?? 1, 4, '0', STR_PAD_LEFT)));

        $viewData = [
            'recruitment'        => $recruitment,
            'requisition'        => $requisition,
            'offeringNumber'     => $offeringNumber,
            'position'           => $position,
            'grading'            => $grading,
            'workLocation'       => $workLocation,
            'contractType'       => $contractType,
            'directSupervisor'   => $directSupervisor,
            'workingSchedule'    => $workingSchedule,
            'salaryBase'         => $salaryBase,
            'salaryCola'         => $salaryCola,
            'salaryAllowance'    => $salaryAllowance,
            'salaryTotal'        => $salaryTotal,
            'salaryDetails'      => $salaryDetails,
            'salaryPeriodNote'   => $salaryPeriodNote,
            'benefitDesc'        => $benefitDesc,
            'offeringTerms'      => $offeringTerms,
            'startDateFormatted' => $startDateFormatted,
            'cityDate'           => $cityDate,
        ];

        $html = view('humanresources.recruitment.prints.offering-letter', $viewData)->render();

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'tempDir'       => $tempDir,
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 18,
            'margin_right'  => 18,
            'margin_top'    => 18,
            'margin_bottom' => 18,
        ]);

        $mpdf->SetTitle('Offering Letter - ' . $recruitment->Name);
        $mpdf->WriteHTML($html);

        $filename = 'Offering-Letter-' . preg_replace('/[^A-Za-z0-9\-]/', '_', $recruitment->Name) . '.pdf';

        return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
