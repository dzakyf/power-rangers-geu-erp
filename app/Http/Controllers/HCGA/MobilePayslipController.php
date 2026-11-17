<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\Payroll\MobilePayslipService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobilePayslipController extends Controller
{
    /**
     * @var MobilePayslipService
     */
    protected $mobilePayslipService;

    public function __construct(MobilePayslipService $mobilePayslipService)
    {
        $this->mobilePayslipService = $mobilePayslipService;
    }

    /**
     * Halaman daftar riwayat Payslip Mobile (strictly scoped to Auth::user()->Emp_ID).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $nik = trim((string) ($user->Emp_ID ?? ''));
        $periods = $nik !== '' ? $this->mobilePayslipService->getAvailablePeriodsForEmployee($nik, 12) : [];

        return view('humanresources.mobile.payslip.index', [
            'user'    => $user,
            'nik'     => $nik,
            'periods' => $periods,
            'latest'  => $periods[0] ?? null,
        ]);
    }

    /**
     * Halaman detail interaktif Payslip Mobile (strictly scoped to Auth::user()->Emp_ID).
     */
    public function detail(Request $request)
    {
        $user = Auth::user();
        $nik = trim((string) ($user->Emp_ID ?? ''));

        if ($nik === '') {
            return redirect()->route('payslip.mobile.index')
                ->with('error', 'Akun Anda belum terhubung dengan NIK karyawan.');
        }

        $period = trim((string) $request->input('period', date('Y-m')));
        $taskId = $request->input('task_id');
        $taskId = $taskId !== null && trim((string) $taskId) !== '' ? trim((string) $taskId) : null;

        try {
            $payslip = $this->mobilePayslipService->getMobilePayslipDetail($nik, $period, $taskId);
        } catch (\Throwable $e) {
            return redirect()->route('payslip.mobile.index')
                ->with('error', 'Data slip gaji periode ' . $period . ' belum tersedia.');
        }

        return view('humanresources.mobile.payslip.detail', [
            'user'    => $user,
            'payslip' => $payslip,
        ]);
    }

    /**
     * Download PDF Payslip Mobile (strictly scoped to Auth::user()->Emp_ID).
     */
    public function pdf(Request $request)
    {
        $user = Auth::user();
        $nik = trim((string) ($user->Emp_ID ?? ''));

        if ($nik === '') {
            abort(403, 'Akun tidak terhubung dengan NIK karyawan.');
        }

        $period = trim((string) $request->input('period', date('Y-m')));
        $taskId = $request->input('task_id');
        $taskId = $taskId !== null && trim((string) $taskId) !== '' ? trim((string) $taskId) : null;

        $payslip = $this->mobilePayslipService->getMobilePayslipDetail($nik, $period, $taskId);

        $html = view('humanresources.payroll.prints.payslip-digital', [
            'payslip' => $payslip,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeNik = preg_replace('/[^A-Za-z0-9_-]/', '_', $nik);
        $safePeriod = preg_replace('/[^0-9-]/', '', $period) ?: date('Y-m');
        $filename = "Payslip_{$safeNik}_{$safePeriod}.pdf";

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
