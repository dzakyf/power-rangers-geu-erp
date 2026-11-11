<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\Payroll\PayrollCalculationService;
use App\Services\Payroll\PayrollTaxService;
use App\Services\Payroll\PayrollAttendanceSyncService;
use App\Services\Payroll\PayrollPayslipService;
use App\Services\Payroll\PayrollApprovalService;
use App\Services\EmailSettingsService;

class PayrollController extends Controller
{
    /**
     * @var PayrollCalculationService
     */
    protected $calcService;

    /**
     * @var PayrollTaxService
     */
    protected $taxService;

    /**
     * @var PayrollAttendanceSyncService
     */
    protected $attendanceSyncService;

    /**
     * @var PayrollPayslipService
     */
    protected $payslipService;

    /**
     * @var EmailSettingsService
     */
    protected $emailSettingsService;

    /**
     * @var PayrollApprovalService
     */
    protected $approvalService;

    public function __construct(
        PayrollCalculationService $calcService,
        PayrollTaxService $taxService,
        PayrollAttendanceSyncService $attendanceSyncService,
        PayrollPayslipService $payslipService,
        EmailSettingsService $emailSettingsService,
        PayrollApprovalService $approvalService
    ) {
        $this->calcService = $calcService;
        $this->taxService = $taxService;
        $this->attendanceSyncService = $attendanceSyncService;
        $this->payslipService = $payslipService;
        $this->emailSettingsService = $emailSettingsService;
        $this->approvalService = $approvalService;
    }

    // =========================================================================
    // 1. MASTER TAX REDUCTION & TER CONFIGURATION (/payroll-tax)
    // =========================================================================

    public function payrollTaxIndex()
    {
        return view('humanresources.payroll.payroll-tax');
    }

    public function xmlPayrollTax()
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rows>';

        $taxes = DB::table('payroll_tax_reduction')->orderBy('Marital_ID', 'ASC')->get();
        $no = 1;

        foreach ($taxes as $t) {
            $terCat = isset($t->ter_category) ? $t->ter_category : $this->taxService->resolveTerCategory($t->Marital_ID);
            echo "<row id='{$t->ID}'>";
            echo "<cell>{$no}</cell>";
            echo "<cell><![CDATA[{$t->Marital_ID}]]></cell>";
            echo "<cell><![CDATA[{$t->Description}]]></cell>";
            echo "<cell>{$t->Reduction_Amount}</cell>";
            echo "<cell><![CDATA[{$terCat}]]></cell>";
            echo "<cell>{$t->Marital_Status}</cell>";
            echo "<cell>{$t->Children_Count}</cell>";
            echo "<cell><![CDATA[{$t->Created_By}]]></cell>";
            echo "<cell><![CDATA[{$t->Created_Date}]]></cell>";
            echo "<cell><![CDATA[{$t->Modified_By}]]></cell>";
            echo "<cell><![CDATA[{$t->Modified_Date}]]></cell>";
            echo '</row>';
            $no++;
        }

        echo '</rows>';
        exit;
    }

    public function payrollTaxUpdate(Request $request)
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="iso-8859-1"?>';
        echo '<data>';

        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';
        $requestBody = urldecode(file_get_contents('php://input'));
        $params = [];
        $pairs = explode('&', $requestBody);

        foreach ($pairs as $p) {
            $parts = explode('=', $p);
            if (count($parts) >= 2) {
                $params[$parts[0]] = $parts[1];
            }
        }

        $checkIds = isset($params['ids']) ? explode(',', $params['ids']) : [];

        foreach ($checkIds as $rowId) {
            $status = isset($params[$rowId . '_!nativeeditor_status']) ? $params[$rowId . '_!nativeeditor_status'] : 'updated';
            $maritalId = isset($params[$rowId . '_c1']) ? trim($params[$rowId . '_c1']) : '';
            $desc = isset($params[$rowId . '_c2']) ? trim($params[$rowId . '_c2']) : '';
            $amount = isset($params[$rowId . '_c3']) ? (float)str_replace(',', '', $params[$rowId . '_c3']) : 0.0;
            $terCat = isset($params[$rowId . '_c4']) ? strtoupper(trim($params[$rowId . '_c4'])) : $this->taxService->resolveTerCategory($maritalId);
            $maritalStatus = isset($params[$rowId . '_c5']) ? trim($params[$rowId . '_c5']) : 0;
            $children = isset($params[$rowId . '_c6']) ? (int)$params[$rowId . '_c6'] : 0;

            if ($status === 'inserted') {
                $newId = DB::table('payroll_tax_reduction')->insertGetId([
                    'Marital_ID'       => $maritalId,
                    'Description'      => $desc,
                    'Reduction_Amount' => $amount,
                    'ter_category'     => $terCat,
                    'Marital_Status'   => $maritalStatus,
                    'Children_Count'   => $children,
                    'Created_By'       => $currentUser,
                    'Created_Date'     => Carbon::now()->toDateTimeString(),
                ]);
                echo "<action type='insert' sid='{$rowId}' tid='{$newId}' />";
            } elseif ($status === 'deleted') {
                DB::table('payroll_tax_reduction')->where('ID', $rowId)->delete();
                echo "<action type='delete' sid='{$rowId}' tid='{$rowId}' />";
            } else {
                DB::table('payroll_tax_reduction')->where('ID', $rowId)->update([
                    'Marital_ID'       => $maritalId,
                    'Description'      => $desc,
                    'Reduction_Amount' => $amount,
                    'ter_category'     => $terCat,
                    'Marital_Status'   => $maritalStatus,
                    'Children_Count'   => $children,
                    'Modified_By'      => $currentUser,
                    'Modified_Date'    => Carbon::now()->toDateTimeString(),
                ]);
                echo "<action type='update' sid='{$rowId}' tid='{$rowId}' />";
            }
        }

        echo '</data>';
        exit;
    }

    // =========================================================================
    // 2. MASTER BENEFITS & COSTS (/payroll-lists)
    // =========================================================================

    public function payrollListsIndex()
    {
        return view('humanresources.payroll.payroll-lists');
    }

    public function xmlPayrollLists()
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rows>';

        $lists = DB::table('payrol_master_lists')->orderBy('Urutan', 'ASC')->get();
        $no = 1;

        foreach ($lists as $l) {
            echo "<row id='{$l->ID}'>";
            echo "<cell>{$no}</cell>";
            echo "<cell>{$l->Urutan}</cell>";
            echo "<cell><![CDATA[{$l->Name}]]></cell>";
            echo "<cell>{$l->Kena_Potongan}</cell>";
            echo "<cell>{$l->Default_PPH}</cell>";
            echo "<cell><![CDATA[{$l->Type_Payroll}]]></cell>";
            echo "<cell><![CDATA[{$l->Calculation_Method}]]></cell>";
            echo "<cell>{$l->Calculation_Value}</cell>";
            echo "<cell>{$l->Calculation_Max_Value}</cell>";
            echo "<cell>{$l->Limit_Amount}</cell>";
            echo "<cell><![CDATA[{$l->Formula_Desc}]]></cell>";
            echo "<cell><![CDATA[{$l->WP}]]></cell>";
            echo "<cell><![CDATA[{$l->Created_By}]]></cell>";
            echo "<cell><![CDATA[{$l->Created_Date}]]></cell>";
            echo "<cell><![CDATA[{$l->Modified_By}]]></cell>";
            echo "<cell><![CDATA[{$l->Modified_Date}]]></cell>";
            echo '</row>';
            $no++;
        }

        echo '</rows>';
        exit;
    }

    public function xmlPayrollList()
    {
        return $this->xmlPayrollLists();
    }

    // =========================================================================
    // 3. EMPLOYEE SALARY SETUP & RECOUNTING (/payroll-employee)
    // =========================================================================

    public function payrollEmployeeIndex()
    {
        return view('humanresources.payroll.payroll-employee');
    }

    public function xmlPayrollEmployee(Request $request)
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rows>';

        $mainId = $request->input('Main_ID', null);

        // Wajib INNER JOIN dengan tabel employment agar hanya karyawan terdaftar di employment yang tampil
        $query = DB::table('payrol_master_group as pmg')
            ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'pmg.Group_ID')
            ->select(
                'pmg.ID as Group_Master_ID',
                'pmg.Main_ID',
                'pmg.Group_ID as NIK',
                'pmg.Group_Name',
                'pmg.Category',
                'pmg.Use_For',
                'e.Name as Name',
                DB::raw("COALESCE(e.Gender, '-') as Gender"),
                DB::raw("COALESCE(e.Position, '-') as Position"),
                DB::raw("COALESCE(e.Department, '-') as Department")
            );

        if (!empty($mainId)) {
            $query->where('pmg.Main_ID', $mainId);
        }

        $employees = $query->orderBy('e.Name', 'ASC')->get();
        $no = 1;
        $seenKey = [];

        foreach ($employees as $emp) {
            $uKey = $emp->Main_ID . '|' . $emp->NIK;
            if (isset($seenKey[$uKey])) {
                continue;
            }
            $seenKey[$uKey] = true;
            // Ambil summary nilai dari payrol_group_lists
            $items = DB::table('payrol_group_lists')
                ->where('Main_ID', $emp->Main_ID)
                ->where('Group_ID', $emp->NIK)
                ->get();

            $pokok = 0.0;
            $tunjangan = 0.0;
            $tunjanganPph = 0.0;
            $deduction = 0.0;
            $pph = 0.0;

            foreach ($items as $it) {
                $val = (float)$it->Amounts;
                $type = strtoupper(trim($it->Type_Payroll));
                $pId = strtoupper(trim($it->Payroll_ID));
                $pName = strtoupper(trim($it->Name));

                if ($type === 'BASIC') {
                    $pokok += $val;
                } elseif ($type === 'ALLOWANCE') {
                    if ($pId === 'PYH00001' || strpos($pName, 'TUNJANGAN PAJAK') !== false || strpos($pName, 'TUNJANGAN PPH') !== false) {
                        $tunjanganPph += $val;
                    } else {
                        $tunjangan += $val;
                    }
                } elseif ($type === 'DEDUCTION') {
                    if ($pId === 'PY000000' || strpos($pName, 'PPH') !== false || strpos($pName, 'PAJAK') !== false) {
                        $pph += abs($val);
                    } else {
                        $deduction += abs($val);
                    }
                }
            }

            $thp = ($pokok + $tunjangan + $tunjanganPph) - ($deduction + $pph);

            echo "<row id='{$emp->NIK}'>";
            echo "<cell>{$no}</cell>";
            echo "<cell><![CDATA[{$emp->Name}]]></cell>";
            echo "<cell><![CDATA[{$emp->NIK}]]></cell>";
            echo "<cell><![CDATA[{$emp->Gender}]]></cell>";
            echo "<cell><![CDATA[{$emp->Category}]]></cell>";
            echo "<cell><![CDATA[{$emp->Main_ID}]]></cell>";
            echo "<cell><![CDATA[{$emp->Position}]]></cell>";
            echo "<cell>{$pokok}</cell>";
            echo "<cell>{$tunjangan}</cell>";
            echo "<cell>{$tunjanganPph}</cell>";
            echo "<cell>{$deduction}</cell>";
            echo "<cell>{$pph}</cell>";
            echo "<cell>{$thp}</cell>";
            echo '</row>';
            $no++;
        }

        echo '</rows>';
        exit;
    }

    /**
     * Eksekusi Recounting Penggajian via Service Baru (Modern & Terintegrasi)
     */
    public function recountingAjax(Request $request)
    {
        $mainId = trim((string)$request->input('Main_ID', ''));
        $periodeYm = trim((string)$request->input('periode', date('Y-m')));
        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';

        try {
            if (!empty($mainId)) {
                $result = $this->calcService->recountSalaryGroup($mainId, $periodeYm, $currentUser);
            } else {
                // Jika tidak spesifik Main_ID, recount seluruh Salary Group yang memiliki karyawan aktif di employment
                $activeGroupIds = DB::table('payrol_master_group as pmg')
                    ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
                    ->distinct()
                    ->pluck('pmg.Main_ID')
                    ->toArray();

                $allEmployees = [];
                $totalThp = 0.0;
                $totalTax = 0.0;
                foreach ($activeGroupIds as $gId) {
                    $sub = $this->calcService->recountSalaryGroup($gId, $periodeYm, $currentUser);
                    $totalThp += $sub['total_thp'];
                    $totalTax += $sub['total_tax'];
                    $allEmployees = array_merge($allEmployees, $sub['employees']);
                }

                $result = [
                    'main_id'         => 'ALL',
                    'periode'         => $periodeYm,
                    'total_employees' => count($allEmployees),
                    'total_thp'       => $totalThp,
                    'total_tax'       => $totalTax,
                    'employees'       => $allEmployees,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Kalkulasi ulang (Recounting) payroll berhasil diselesaikan.',
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan kalkulasi ulang: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // 4. PAYROLL GENERATOR (/payroll-generator)
    // =========================================================================

    public function payrollGeneratorIndex()
    {
        $user = Auth::user();
        $location = $user ? ($user->Location ?: 'GEU-HO') : 'GEU-HO';
        $taskId = 'PG-' . $location . '-' . date('ymdhis');
        $defaultPeriod = date('Y-m');

        $templates = DB::table('payrol_master_template')
            ->orderBy('Payroll_ID', 'ASC')
            ->get();

        // Hitung jumlah anggota yang terdaftar di tabel employment & komponen aktif per Main_ID
        $empCounts = DB::table('payrol_master_group as pmg')
            ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
            ->select('pmg.Main_ID', DB::raw('COUNT(DISTINCT pmg.Group_ID) as cnt'))
            ->groupBy('pmg.Main_ID')
            ->pluck('cnt', 'Main_ID')
            ->toArray();

        $compCounts = DB::table('payrol_group_lists')
            ->select('Main_ID', DB::raw('COUNT(*) as cnt'))
            ->groupBy('Main_ID')
            ->pluck('cnt', 'Main_ID')
            ->toArray();

        $mainGroups = DB::table('payrol_main_group')
            ->orderBy('ID', 'ASC')
            ->get()
            ->map(function ($grp) use ($empCounts, $compCounts) {
                $grp->emp_count = isset($empCounts[$grp->Main_ID]) ? (int)$empCounts[$grp->Main_ID] : 0;
                $grp->comp_count = isset($compCounts[$grp->Main_ID]) ? (int)$compCounts[$grp->Main_ID] : 0;
                return $grp;
            });

        $pendingBatchCount = DB::table('salary_main')
            ->whereNotIn('Status', ['APPROVED', 'CANCELED', 'CANCELLED'])
            ->count();

        $approvedBatchCount = DB::table('salary_main')
            ->where('Status', 'APPROVED')
            ->count();

        return view('humanresources.payroll.payroll-generator', [
            'taskId'             => $taskId,
            'defaultPeriod'      => $defaultPeriod,
            'templates'          => $templates,
            'mainGroups'         => $mainGroups,
            'pendingBatchCount'  => $pendingBatchCount,
            'approvedBatchCount' => $approvedBatchCount,
        ]);
    }

    /**
     * Endpoint XML dhtmlxGrid untuk Daftar Karyawan di Payroll Generator
     * Hanya menampilkan karyawan yang ada di tabel employment.
     */
    public function xmlPayrollGeneratorEmployee(Request $request)
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<rows>';

        $mainId = trim((string)$request->input('Main_ID', ''));
        $useFor = trim((string)$request->input('Use_For', 'ALL'));
        $category = trim((string)$request->input('Category', 'SALARY'));
        $period = trim((string)$request->input('Salary_Period', date('Y-m')));

        if (empty($mainId)) {
            echo '</rows>';
            exit;
        }

        // Wajib INNER JOIN dengan tabel employment agar hanya data karyawan valid di employment yang ditampilkan
        $query = DB::table('payrol_master_group as pmg')
            ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
            ->select(
                'pmg.Main_ID',
                'pmg.Group_ID as NIK',
                'pmg.Group_Name',
                'pmg.Category as Group_Category',
                'pmg.Use_For as Group_Use_For',
                'pmg.Status_Salary',
                'e.Name as Name',
                DB::raw("COALESCE(e.Gender, '-') as Gender"),
                DB::raw("COALESCE(e.Position, '-') as Position"),
                DB::raw("COALESCE(e.Department, '-') as Department"),
                'e.PTKP_Status',
                'e.Category as Emp_Category'
            )
            ->where('pmg.Main_ID', $mainId);

        // Jika memilih template khusus selain SALARY, ambil daftar Payroll_ID yang termasuk di template
        $templatePayrollIds = null;
        if (!empty($category) && $category !== 'SALARY') {
            $tplItems = DB::table('payrol_template_master_lists')
                ->where('Main_ID', $category)
                ->pluck('Payroll_ID')
                ->toArray();
            if (!empty($tplItems)) {
                $templatePayrollIds = array_map('strtoupper', array_map('trim', $tplItems));
            }
        }

        // Preload formula master lists untuk membedakan OVERTIME & KJK
        $masterFormulas = [];
        $masterLists = DB::table('payrol_master_lists')->get();
        foreach ($masterLists as $ml) {
            $masterFormulas[strtoupper(trim($ml->Payroll_ID))] = strtoupper(trim((string)$ml->Formula_Desc));
        }

        $employees = $query->orderBy('e.Name', 'ASC')->get();

        // Bulk load seluruh rincian komponen untuk Main_ID ini agar cepat (tanpa N+1 query)
        $allGroupItems = DB::table('payrol_group_lists')
            ->where('Main_ID', $mainId)
            ->get()
            ->groupBy('Group_ID');

        $seenNiks = [];

        foreach ($employees as $emp) {
            $nik = trim((string)$emp->NIK);
            if ($nik === '' || isset($seenNiks[$nik])) {
                continue;
            }
            $seenNiks[$nik] = true;

            $items = isset($allGroupItems[$nik]) ? $allGroupItems[$nik] : collect([]);

            $pokok = 0.0;
            $tunjangan = 0.0;
            $overtime = 0.0;
            $kjk = 0.0;
            $tunjanganPph = 0.0;
            $deduction = 0.0;
            $pph = 0.0;

            foreach ($items as $it) {
                $pId = strtoupper(trim((string)$it->Payroll_ID));
                if ($templatePayrollIds !== null && !in_array($pId, $templatePayrollIds) && !in_array($pId, ['PY000000', 'PYH00001'])) {
                    continue;
                }

                $val = (isset($it->Prorate) && (float)$it->Prorate > 0) ? (float)$it->Prorate : (float)$it->Amounts;
                $type = strtoupper(trim((string)$it->Type_Payroll));
                $pName = strtoupper(trim((string)$it->Name));
                $formula = isset($masterFormulas[$pId]) ? $masterFormulas[$pId] : '';

                if ($pId === 'PY000000' || ($type === 'DEDUCTION' && (strpos($pName, 'PPH') !== false || strpos($pName, 'PAJAK') !== false))) {
                    $pph += abs($val);
                } elseif (in_array($pId, ['PYH00001', 'PYH00000'], true) || ($type === 'ALLOWANCE' && (strpos($pName, 'TUNJANGAN PAJAK') !== false || strpos($pName, 'TUNJANGAN PPH') !== false))) {
                    $tunjanganPph += abs($val);
                } elseif ($type === 'BASIC') {
                    $pokok += $val;
                } elseif ($type === 'ALLOWANCE') {
                    if ($pId === 'PY000045' || $formula === 'OVERTIME') {
                        $overtime += $val;
                    } elseif ($pId === 'PY000044' || $formula === 'KJK') {
                        $kjk += $val;
                    } else {
                        $tunjangan += $val;
                    }
                } else {
                    $deduction += abs($val);
                }
            }

            // Sinkronisasi langsung nilai Overtime & KJK dari modul Overtime/KJK untuk periode terpilih jika belum ter-recount
            if ($category === 'SALARY' || $templatePayrollIds === null) {
                try {
                    $attSummary = $this->attendanceSyncService->getEmployeeAttendanceForPeriod($nik, $period, [
                        'department' => $emp->Department,
                    ]);
                    if ((float)$attSummary['approved_overtime_amount'] > 0) {
                        $overtime = (float)$attSummary['approved_overtime_amount'];
                    }
                    if ((float)($attSummary['approved_kjk_amount'] ?? 0) > 0) {
                        $kjk = (float)$attSummary['approved_kjk_amount'];
                    }
                } catch (\Throwable $e) {
                    // Fallback ke nilai payrol_group_lists
                }
            }

            $thp = max(0.0, ($pokok + $tunjangan + $overtime + $kjk + $tunjanganPph) - ($deduction + $pph));
            $catLabel = $emp->Group_Use_For ?: ($useFor ?: 'INTERNAL');
            $typeSalLabel = $category ?: 'SALARY';
            $ptkpNote = $emp->PTKP_Status ? "PTKP: {$emp->PTKP_Status}" : 'Active';

            echo "<row id='" . htmlspecialchars($nik, ENT_QUOTES) . "'>";
            echo "<cell>1</cell>"; // 0: Select Checkbox (default checked)
            echo "<cell><![CDATA[{$emp->Name}]]></cell>"; // 1: Name
            echo "<cell><![CDATA[{$nik}]]></cell>"; // 2: NIK
            echo "<cell><![CDATA[{$emp->Gender}]]></cell>"; // 3: Gender
            echo "<cell><![CDATA[" . ($emp->Group_Category ?: 'SALARY') . "]]></cell>"; // 4: Group Category
            echo "<cell><![CDATA[{$emp->Main_ID}]]></cell>"; // 5: Salary Group
            echo "<cell><![CDATA[{$emp->Position}]]></cell>"; // 6: Position
            echo "<cell>{$pokok}</cell>"; // 7: Pokok
            echo "<cell>{$tunjangan}</cell>"; // 8: Allowance
            echo "<cell>{$overtime}</cell>"; // 9: Overtime
            echo "<cell>{$kjk}</cell>"; // 10: KJK
            echo "<cell>{$tunjanganPph}</cell>"; // 11: WHT Allowance
            echo "<cell>{$deduction}</cell>"; // 12: Deduction
            echo "<cell>{$pph}</cell>"; // 13: PPH
            echo "<cell>{$thp}</cell>"; // 14: THP
            echo "<cell><![CDATA[{$catLabel}]]></cell>"; // 15: Category
            echo "<cell><![CDATA[{$typeSalLabel}]]></cell>"; // 16: Type Sal
            echo "<cell><![CDATA[{$ptkpNote}]]></cell>"; // 17: Note
            echo "</row>";
        }

        echo '</rows>';
        exit;
    }

    /**
     * Partial Detail Komponen Karyawan pada Halaman Payroll Generator
     */
    public function payrollGeneratorDetail(Request $request)
    {
        $nik = $request->input('ID');
        $employee = DB::table('employment')->where('NIK', $nik)->first();

        return view('humanresources.payroll.payroll-generator-detail', [
            'nik'      => $nik,
            'employee' => $employee,
        ]);
    }

    /**
     * Endpoint Kompatibilitas dhtmlxDataProcessor pada Payroll Generator
     * (Penyimpanan sesungguhnya dilakukan secara transaksional di payrollGeneratorSubmit)
     */
    public function payrollGeneratorDetailUpdate(Request $request)
    {
        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="iso-8859-1"?>';
        echo '<data>';

        $requestBody = urldecode(file_get_contents('php://input'));
        $params = [];
        foreach (explode('&', $requestBody) as $pair) {
            $parts = explode('=', $pair);
            if (count($parts) >= 2) {
                $params[$parts[0]] = $parts[1];
            }
        }

        $checkIds = isset($params['ids']) ? array_filter(explode(',', $params['ids'])) : [];
        foreach ($checkIds as $rowId) {
            echo "<action type='update' sid='" . htmlspecialchars($rowId, ENT_QUOTES) . "' tid='" . htmlspecialchars($rowId, ENT_QUOTES) . "'/>";
        }

        echo '</data>';
        exit;
    }

    /**
     * Download Format Excel (.xlsx) untuk Upload Nilai Override Payroll Generator
     */
    public function downloadGeneratorTemplate(Request $request)
    {
        $mainId = $request->input('Main_ID');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Payroll Generator');

        // Judul & Instruksi
        $sheet->setCellValue('A1', 'TEMPLATE UPLOAD NILAI PAYROLL GENERATOR');
        $sheet->setCellValue('A2', 'Salary Group: ' . ($mainId ?: 'ALL') . ' | Tanggal Export: ' . date('Y-m-d H:i'));

        // Header Kolom (Baris 3)
        $headers = ['NIK', 'Nama Karyawan', 'Gaji Pokok', 'Tunjangan', 'Overtime', 'KJK', 'Potongan Lain', 'Catatan'];
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $idx => $title) {
            $col = $colLetters[$idx];
            $sheet->setCellValue($col . '3', $title);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $rowNum = 4;
        if (!empty($mainId)) {
            $employees = DB::table('payrol_master_group as pmg')
                ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
                ->where('pmg.Main_ID', $mainId)
                ->select('pmg.Group_ID as NIK', 'e.Name as Name')
                ->orderBy('e.Name', 'ASC')
                ->get();

            $allItems = DB::table('payrol_group_lists')
                ->where('Main_ID', $mainId)
                ->get()
                ->groupBy('Group_ID');

            foreach ($employees as $emp) {
                $items = isset($allItems[$emp->NIK]) ? $allItems[$emp->NIK] : collect([]);
                $pokok = 0;
                $tunj = 0;
                foreach ($items as $it) {
                    if (strtoupper(trim($it->Type_Payroll)) === 'BASIC') {
                        $pokok += (float)$it->Amounts;
                    } elseif (strtoupper(trim($it->Type_Payroll)) === 'ALLOWANCE' && $it->Payroll_ID !== 'PYH00001') {
                        $tunj += (float)$it->Amounts;
                    }
                }

                $sheet->setCellValueExplicit('A' . $rowNum, (string)$emp->NIK, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('B' . $rowNum, $emp->Name);
                $sheet->setCellValue('C' . $rowNum, $pokok);
                $sheet->setCellValue('D' . $rowNum, $tunj);
                $sheet->setCellValue('E' . $rowNum, 0);
                $sheet->setCellValue('F' . $rowNum, 0);
                $sheet->setCellValue('G' . $rowNum, 0);
                $sheet->setCellValue('H' . $rowNum, '');
                $rowNum++;
            }
        } else {
            // Contoh baris jika belum memilih grup
            $sheet->setCellValueExplicit('A4', '09213950', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B4', 'CONTOH NAMA KARYAWAN');
            $sheet->setCellValue('C4', 5500000);
            $sheet->setCellValue('D4', 1500000);
            $sheet->setCellValue('E4', 250000);
            $sheet->setCellValue('F4', 0);
            $sheet->setCellValue('G4', 0);
            $sheet->setCellValue('H4', 'Override lembur/tunjangan');
        }

        $fileName = 'Template_Payroll_Generator_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $mainId ?: 'Default') . '_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Proses Upload File Excel (.xlsx / .xls / .csv) untuk Update Nilai di Grid Payroll Generator
     */
    public function uploadGeneratorExcel(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'Pilih file Excel terlebih dahulu.'], 400);
        }

        $file = $request->file('file');
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $parsed = [];
            foreach ($rows as $rowIndex => $r) {
                // Lewati baris header (baris 1-3 atau baris yang kolom A-nya 'NIK')
                $colA = isset($r['A']) ? trim((string)$r['A']) : '';
                if ($rowIndex <= 3 || $colA === '' || strtoupper($colA) === 'NIK' || strpos(strtoupper($colA), 'TEMPLATE') !== false || strpos(strtoupper($colA), 'SALARY') !== false) {
                    continue;
                }

                $numClean = function ($v) {
                    if ($v === null || $v === '') {
                        return null;
                    }
                    return (float)str_replace([',', ' '], '', (string)$v);
                };

                // Kompatibel dengan format baru (A=NIK, B=Nama, C=Pokok, D=Tunjangan, E=Overtime, F=KJK, G=Deduction, H=Note)
                // maupun format legacy 2 kolom (A=NIK, B=Pokok)
                $colB = isset($r['B']) ? trim((string)$r['B']) : '';
                $isLegacyTwoCol = is_numeric(str_replace([',', '.'], '', $colB)) && (!isset($r['C']) || trim((string)$r['C']) === '');

                if ($isLegacyTwoCol) {
                    $parsed[] = [
                        'nik'       => $colA,
                        'pokok'     => $numClean($r['B']),
                        'tunjangan' => null,
                        'overtime'  => null,
                        'kjk'       => null,
                        'deduction' => null,
                        'note'      => '',
                    ];
                } else {
                    $parsed[] = [
                        'nik'       => $colA,
                        'name'      => $colB,
                        'pokok'     => $numClean($r['C'] ?? null),
                        'tunjangan' => $numClean($r['D'] ?? null),
                        'overtime'  => $numClean($r['E'] ?? null),
                        'kjk'       => $numClean($r['F'] ?? null),
                        'deduction' => $numClean($r['G'] ?? null),
                        'note'      => isset($r['H']) ? trim((string)$r['H']) : '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($parsed) . ' baris data karyawan berhasil dibaca dari file Excel.',
                'rows'    => $parsed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function payrollGeneratorSubmit(Request $request)
    {
        $taskId = trim((string)$request->input('Task_ID'));
        $period = trim((string)$request->input('Salary_Period', date('Y-m')));
        $mainId = trim((string)$request->input('Main_ID'));
        $category = trim((string)$request->input('Category', 'SALARY'));
        $useFor = trim((string)$request->input('Use_For', 'ALL'));
        $note = trim((string)$request->input('Note', ''));
        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';
        $location = Auth::user() ? (Auth::user()->Location ?: 'HO') : 'HO';

        // Daftar NIK yang dicentang pada grid (opsional)
        $selectedNiksInput = $request->input('selected_niks', '');
        $selectedNiks = [];
        if (is_array($selectedNiksInput)) {
            $selectedNiks = array_filter(array_map('trim', $selectedNiksInput));
        } elseif (is_string($selectedNiksInput) && trim($selectedNiksInput) !== '') {
            $selectedNiks = array_filter(array_map('trim', explode(',', $selectedNiksInput)));
        }

        // Data override dari grid/Excel jika ada
        $overridesInput = $request->input('grid_overrides', '');
        $gridOverrides = [];
        if (is_string($overridesInput) && $overridesInput !== '') {
            $decoded = json_decode($overridesInput, true);
            if (is_array($decoded)) {
                $gridOverrides = $decoded;
            }
        } elseif (is_array($overridesInput)) {
            $gridOverrides = $overridesInput;
        }

        if (empty($taskId) || empty($mainId)) {
            return response()->json(['success' => false, 'message' => 'Gen ID (Task_ID) dan Salary Group (Main_ID) wajib dipilih.'], 400);
        }

        DB::beginTransaction();
        try {
            // Bersihkan jika ada sisa draft dengan Task_ID yang sama (idempotency guard)
            DB::table('salary_main')->where('Task_ID', $taskId)->delete();
            DB::table('salary')->where('Task_ID', $taskId)->delete();
            DB::table('salary_lists')->where('Task_ID', $taskId)->delete();

            // 1. Eksekusi Recounting Grup untuk Mendapatkan Angka Terkini
            $recount = $this->calcService->recountSalaryGroup($mainId, $period, $currentUser);

            // Filter hanya karyawan yang dicentang (jika ada filter selected_niks)
            $employeesToProcess = $recount['employees'];
            if (!empty($selectedNiks)) {
                $employeesToProcess = array_values(array_filter($employeesToProcess, function ($empCalc) use ($selectedNiks) {
                    return in_array((string)$empCalc['nik'], $selectedNiks, true);
                }));
            }

            if (empty($employeesToProcess)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada karyawan terpilih untuk digenerate pada Salary Group ini.',
                ], 400);
            }

            // Hitung ulang total batch & terapkan override jika ada
            $batchTotalThp = 0.0;
            foreach ($employeesToProcess as &$empCalc) {
                $nik = (string)$empCalc['nik'];
                if (isset($gridOverrides[$nik])) {
                    $ov = $gridOverrides[$nik];
                    if (isset($ov['pokok']) && is_numeric($ov['pokok'])) {
                        $empCalc['pokok'] = (float)$ov['pokok'];
                    }
                    if (isset($ov['tunjangan']) && is_numeric($ov['tunjangan'])) {
                        $empCalc['tunjangan'] = (float)$ov['tunjangan'];
                    }
                    if (isset($ov['overtime']) && is_numeric($ov['overtime'])) {
                        $empCalc['overtime'] = (float)$ov['overtime'];
                    }
                    if (isset($ov['kjk']) && is_numeric($ov['kjk'])) {
                        $empCalc['kjk'] = (float)$ov['kjk'];
                    }
                    if (isset($ov['deduction']) && is_numeric($ov['deduction'])) {
                        $empCalc['deduction'] = (float)$ov['deduction'];
                    }
                    if (!empty($ov['note'])) {
                        $empCalc['custom_note'] = (string)$ov['note'];
                    }
                    $kjkVal = isset($empCalc['kjk']) ? (float)$empCalc['kjk'] : 0.0;
                    $empCalc['thp'] = max(
                        0.0,
                        ($empCalc['pokok'] + $empCalc['tunjangan'] + $empCalc['overtime'] + $kjkVal + $empCalc['tunjangan_pph'])
                        - ($empCalc['deduction'] + $empCalc['pph'])
                    );
                }
                $batchTotalThp += (float)$empCalc['thp'];
            }
            unset($empCalc);

            // 2. Simpan Header ke salary_main
            $mainGroup = DB::table('payrol_main_group')->where('Main_ID', $mainId)->first();
            $dept = $mainGroup ? $mainGroup->Department : 'HCGA';
            $effectiveUseFor = ($useFor === 'ALL' && $mainGroup && !empty($mainGroup->Use_For)) ? $mainGroup->Use_For : $useFor;

            DB::table('salary_main')->insert([
                'Task_ID'       => $taskId,
                'Salary_Period' => $period,
                'Total'         => $batchTotalThp,
                'Category'      => $category,
                'Department'    => $dept,
                'Use_For'       => $effectiveUseFor,
                'Salary_Group'  => $mainId,
                'Note'          => $note,
                'Status'        => 'CREATED',
                'Created_By'    => $currentUser,
                'Created_Date'  => Carbon::now()->toDateTimeString(),
                'Posting'       => 0,
                'Location'      => $location,
            ]);

            // 3. Simpan Detail Karyawan ke salary & salary_lists
            $nikList = [];
            foreach ($employeesToProcess as $empCalc) {
                $nik = $empCalc['nik'];
                $nikList[] = $nik;
                $rowNote = !empty($empCalc['custom_note']) ? ($note ? $note . ' | ' . $empCalc['custom_note'] : $empCalc['custom_note']) : $note;

                DB::table('salary')->insert([
                    'Task_ID'              => $taskId,
                    'Salary_Period'        => $period,
                    'Main_ID'              => $mainId,
                    'Group_ID'             => $nik,
                    'Group_Name'           => $empCalc['name'],
                    'Gross_Up'             => !empty($empCalc['metadata']['is_gross_up']) ? 1 : 0,
                    'Category'             => $category,
                    'Use_For'              => $effectiveUseFor,
                    'Pokok'                => $empCalc['pokok'],
                    'Tunjangan'            => $empCalc['tunjangan'],
                    'Overtime'             => $empCalc['overtime'],
                    'KJK'                  => isset($empCalc['kjk']) ? $empCalc['kjk'] : 0,
                    'Tunjangan_PPH'        => $empCalc['tunjangan_pph'],
                    'Deduction'            => $empCalc['deduction'],
                    'PPH'                  => $empCalc['pph'],
                    'THP'                  => $empCalc['thp'],
                    'absent_days'          => $empCalc['absent_days'],
                    'late_occurrences'     => $empCalc['late_occurrences'],
                    'unpaid_leave_amount'  => $empCalc['unpaid_leave_amount'],
                    'late_penalty_amount'  => $empCalc['late_penalty_amount'],
                    'bpjs_tk_amount'       => $empCalc['bpjs_tk_amount'],
                    'bpjs_kes_amount'      => $empCalc['bpjs_kes_amount'],
                    'calculation_metadata' => json_encode($empCalc['metadata']),
                    'Created_By'           => $currentUser,
                    'Note'                 => $rowNote,
                ]);

                // Snapshot rincian komponen ke salary_lists
                $components = DB::table('payrol_group_lists')
                    ->where('Main_ID', $mainId)
                    ->where('Group_ID', $nik)
                    ->get();

                foreach ($components as $c) {
                    DB::table('salary_lists')->insert([
                        'Task_ID'       => $taskId,
                        'Main_ID'       => $mainId,
                        'Group_ID'      => $nik,
                        'Payroll_ID'    => $c->Payroll_ID,
                        'Name'          => $c->Name,
                        'Kena_Potongan' => $c->Kena_Potongan,
                        'Default_PPH'   => $c->Default_PPH,
                        'Type_Payroll'  => $c->Type_Payroll,
                        'Amounts'       => $c->Amounts,
                        'Note'          => $c->Note,
                        'Created_By'    => $currentUser,
                    ]);
                }

                // Tandai record Overtime & KJK karyawan yang masuk dalam batch ini dengan Salary_ID & Salary_Period
                $otTaskIds = isset($empCalc['overtime_task_ids']) ? $empCalc['overtime_task_ids'] : [];
                if (!empty($otTaskIds)) {
                    if (Schema::hasTable('attendance_overtime_requests')) {
                        DB::table('attendance_overtime_requests')
                            ->where('nik', $nik)
                            ->whereIn('overtime_no', $otTaskIds)
                            ->update([
                                'salary_id'            => $taskId,
                                'salary_period'        => $period,
                                'is_payroll_processed' => 1,
                            ]);
                    }
                    if (Schema::hasTable('overtime_lists')) {
                        DB::table('overtime_lists')
                            ->where('NIK', $nik)
                            ->where(function ($q) use ($otTaskIds) {
                                $q->whereIn('Task_ID', $otTaskIds)
                                  ->orWhereIn('Reference', $otTaskIds);
                            })
                            ->update([
                                'Salary_ID'     => $taskId,
                                'Salary_Period' => $period,
                            ]);
                    }
                }

                $kjkTaskIds = isset($empCalc['kjk_task_ids']) ? $empCalc['kjk_task_ids'] : [];
                if (!empty($kjkTaskIds) && Schema::hasTable('kjk_lists')) {
                    DB::table('kjk_lists')
                        ->where('NIK', $nik)
                        ->whereIn('Task_ID', $kjkTaskIds)
                        ->update([
                            'Salary_ID'     => $taskId,
                            'Salary_Period' => $period,
                        ]);
                }
            }

            // 4. Inisialisasi Dynamic Approval Workflow (ApprovalWorkflowService via PayrollApprovalService)
            $salaryMainRow = DB::table('salary_main')->where('Task_ID', $taskId)->first();
            if ($salaryMainRow) {
                $this->approvalService->ensureApprovalRequest($salaryMainRow, Auth::user());
            }

            // 5. Kunci Presensi (Data Freezing) Sesuai Tips & Rekomendasi
            $this->attendanceSyncService->lockAttendancePeriod($period, 'POSTED', $currentUser, $nikList);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'         => true,
                    'message'         => "Batch payroll {$taskId} berhasil digenerate untuk " . count($nikList) . " karyawan.",
                    'task_id'         => $taskId,
                    'total_employees' => count($nikList),
                    'total_thp'       => $batchTotalThp,
                ]);
            }

            return response("success: Draft payroll {$taskId} berhasil digenerate.");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate payroll: ' . $e->getMessage(),
                ], 500);
            }
            return response("failed: " . $e->getMessage(), 500);
        }
    }

    // =========================================================================
    // 5. PAYROLL APPROVAL (/payroll-approval) - DYNAMIC APPROVAL WORKFLOW
    // =========================================================================

    public function payrollApprovalIndex()
    {
        return view('humanresources.payroll.payroll-approval');
    }

    /**
     * Endpoint XML Grid Daftar Batch Payroll Menunggu Approval
     * Terintegrasi dengan ApprovalWorkflowService via PayrollApprovalService.
     */
    public function xmlPayrollApproval(Request $request)
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo "<?xml version='1.0' encoding='utf-8'?>\n";
        echo '<rows>';

        $batches = $this->approvalService->getPendingBatchesWithApproval(Auth::user());
        $nomor = 1;

        foreach ($batches as $item) {
            $row = $item['salary_main'];
            $appr = $item['approval'];

            echo '<row id="' . htmlspecialchars((string)$row->Task_ID, ENT_QUOTES) . '">';
            echo "<cell>{$nomor}</cell>";
            echo '<cell><![CDATA[' . $row->Task_ID . ']]></cell>';
            echo '<cell><![CDATA[' . $row->Salary_Period . ']]></cell>';
            echo '<cell>' . (float)$row->Total . '</cell>';
            echo '<cell><![CDATA[' . $row->Category . ']]></cell>';
            echo '<cell><![CDATA[' . $row->Use_For . ']]></cell>';
            echo '<cell><![CDATA[' . ($appr['current_step_label'] ?? '-') . ']]></cell>';
            echo '<cell><![CDATA[' . ($appr['current_approvers'] ?? '-') . ']]></cell>';
            echo '<cell><![CDATA[' . ($row->Note ?? '') . ']]></cell>';
            echo '<cell><![CDATA[' . ($row->Created_By ?? '') . ']]></cell>';
            echo '<cell><![CDATA[' . ($row->Created_Date ?? '') . ']]></cell>';
            echo '<cell><![CDATA[' . ($appr['display_status'] ?? $row->Status) . ']]></cell>';
            echo '</row>';

            $nomor++;
        }

        echo '</rows>';
        exit;
    }

    /**
     * Partial View Detail Batch Payroll & Stepper Tahapan Approval Dinamis
     */
    public function payrollApprovalDetail(Request $request)
    {
        $batchId = trim((string)$request->input('ID', ''));
        $details = $this->approvalService->getBatchApprovalDetails($batchId, Auth::user());

        return view('humanresources.payroll.payroll-approval-detail', [
            'batchId'     => $batchId,
            'salaryMain'  => $details['salary_main'],
            'approval'    => $details['approval'],
            'logs'        => $details['logs'],
        ]);
    }

    /**
     * Endpoint XML Grid Rincian Karyawan pada Batch Payroll
     */
    public function xmlPayrollApprovalDetail(Request $request)
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) {
            header('Content-type: application/xhtml+xml');
        } else {
            header('Content-type: text/xml');
        }
        echo "<?xml version='1.0' encoding='utf-8'?>\n";
        echo '<rows>';

        $taskId = trim((string)$request->input('Task_ID', ''));
        if ($taskId !== '') {
            $rows = DB::table('salary as s')
                ->leftJoin('employment as e', 'e.NIK', '=', 's.Group_ID')
                ->where('s.Task_ID', $taskId)
                ->select(
                    's.*',
                    DB::raw("COALESCE(e.Name, s.Group_Name, '-') as Emp_Name"),
                    DB::raw("COALESCE(e.Gender, '-') as Emp_Gender"),
                    DB::raw("COALESCE(e.Salary_Group, s.Main_ID, '-') as Emp_Salary_Group"),
                    DB::raw("COALESCE(e.Position, '-') as Emp_Position")
                )
                ->orderBy('Emp_Name', 'ASC')
                ->get();

            $nomor = 1;
            foreach ($rows as $r) {
                $rowId = $r->Group_ID ?: $nomor;
                echo '<row id="' . htmlspecialchars((string)$rowId, ENT_QUOTES) . '">';
                echo "<cell>{$nomor}</cell>";
                echo '<cell><![CDATA[' . $r->Emp_Name . ']]></cell>';
                echo '<cell><![CDATA[' . $r->Group_ID . ']]></cell>';
                echo '<cell><![CDATA[' . $r->Emp_Gender . ']]></cell>';
                echo '<cell><![CDATA[' . ($r->Category ?: '-') . ']]></cell>';
                echo '<cell><![CDATA[' . ($r->Main_ID ?: $r->Emp_Salary_Group) . ']]></cell>';
                echo '<cell><![CDATA[' . $r->Emp_Position . ']]></cell>';
                echo '<cell>' . (float)$r->Pokok . '</cell>';
                echo '<cell>' . (float)$r->Tunjangan . '</cell>';
                echo '<cell>' . (float)$r->Overtime . '</cell>';
                echo '<cell>' . (float)$r->KJK . '</cell>';
                echo '<cell>' . (float)$r->Tunjangan_PPH . '</cell>';
                echo '<cell>' . (float)$r->Deduction . '</cell>';
                echo '<cell>' . (float)$r->PPH . '</cell>';
                echo '<cell>' . (float)$r->THP . '</cell>';
                echo '</row>';
                $nomor++;
            }
        }

        echo '</rows>';
        exit;
    }

    /**
     * Eksekusi Approve Tahap Aktif Batch Payroll via ApprovalWorkflowService
     */
    public function payrollApprovalApprove(Request $request)
    {
        $id = trim((string)$request->input('ID', ''));
        $notes = trim((string)$request->input('notes', ''));

        $result = $this->approvalService->approveBatch($id, Auth::user(), $notes);
        $safeMsg = addslashes($result['message']);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response(
                "<script>"
                . "alert('{$safeMsg}');"
                . "if (typeof refreshdata === 'function') { refreshdata(); }"
                . "if ($('#divlistProjectOrgx').children().length > 0 && '{$result['status']}' !== 'APPROVED') {"
                . "  LoadM('divlistProjectOrgx', '" . url('payroll-approval-detail') . "?ID=" . urlencode($id) . "');"
                . "} else {"
                . "  $('#divlistProjectOrgx').empty();"
                . "}"
                . "</script>"
            );
        }

        return redirect('payroll-approval')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Eksekusi Reject Batch Payroll via ApprovalWorkflowService
     */
    public function payrollApprovalReject(Request $request)
    {
        $id = trim((string)$request->input('ID', ''));
        $reason = trim((string)$request->input('reason', ''));

        $result = $this->approvalService->rejectOrCancelBatch($id, Auth::user(), 'REJECT', $reason);
        $safeMsg = addslashes($result['message']);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response("<script>alert('{$safeMsg}'); $('#divlistProjectOrgx').empty(); if (typeof refreshdata === 'function') { refreshdata(); } else { location.href='" . url('payroll-approval') . "'; }</script>");
        }

        return redirect('payroll-approval')->with($result['success'] ? 'info' : 'error', $result['message']);
    }

    /**
     * Eksekusi Cancel Batch Payroll via ApprovalWorkflowService
     */
    public function payrollApprovalCancel(Request $request)
    {
        $id = trim((string)$request->input('ID', ''));
        $reason = trim((string)$request->input('reason', ''));

        $result = $this->approvalService->rejectOrCancelBatch($id, Auth::user(), 'CANCEL', $reason);
        $safeMsg = addslashes($result['message']);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response("<script>alert('{$safeMsg}'); $('#divlistProjectOrgx').empty(); if (typeof refreshdata === 'function') { refreshdata(); } else { location.href='" . url('payroll-approval') . "'; }</script>");
        }

        return redirect('payroll-approval')->with($result['success'] ? 'info' : 'error', $result['message']);
    }

    public function payrollApprovedIndex()
    {
        return view('humanresources.payroll.payroll-approved');
    }

    /**
     * Tampilan Slip Gaji Digital Modern (Sesuai Layout Talenta / PT GEU)
     */
    public function showDigitalPayslip(Request $request)
    {
        $nik = $request->input('NIK') ?: $request->input('nik');
        $taskId = $request->input('Task_ID') ?: $request->input('task_id');
        $period = $request->input('Period') ?: $request->input('period', date('Y-m'));

        if (empty($nik)) {
            // Jika tidak diberikan parameter NIK, ambil NIK user yang sedang login
            $user = Auth::user();
            $nik = $user ? $user->Emp_ID : null;
        }

        if (empty($nik)) {
            return redirect('approved-payroll')->with('error', 'Parameter NIK karyawan tidak ditemukan.');
        }

        try {
            $payslipData = $this->payslipService->getPayslipData($nik, $period, $taskId);
            return view('humanresources.payroll.prints.payslip-digital', [
                'payslip' => $payslipData,
            ]);
        } catch (\Exception $e) {
            return response("Error memuat slip gaji: " . $e->getMessage(), 500);
        }
    }

    /**
     * API Kirim Slip Gaji Digital ke Email Karyawan
     */
    public function apiSendPayslipEmail(Request $request)
    {
        $nik = $request->input('nik') ?: $request->input('NIK');
        $period = $request->input('period') ?: $request->input('Period', date('Y-m'));
        $taskId = $request->input('task_id') ?: $request->input('Task_ID');

        if (empty($nik)) {
            return response()->json(['success' => false, 'message' => 'Parameter NIK wajib diisi.'], 400);
        }

        try {
            $payslipData = $this->payslipService->getPayslipData($nik, $period, $taskId);
            $recipientEmail = $payslipData['email'];

            if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Karyawan {$payslipData['name']} ({$nik}) belum memiliki email resmi yang valid di sistem.",
                ]);
            }

            // Render view HTML slip gaji
            $htmlBody = view('humanresources.payroll.prints.payslip-digital', [
                'payslip' => $payslipData,
            ])->render();

            $subject = "Payslip Detail - {$payslipData['period']} - {$payslipData['name']}";

            // Kirim via EmailSettingsService (PHPMailer SMTP)
            list($isSent, $errorMsg) = $this->emailSettingsService->send($recipientEmail, $subject, $htmlBody);

            if ($isSent) {
                return response()->json([
                    'success'   => true,
                    'message'   => 'Slip gaji digital berhasil dikirimkan ke email karyawan.',
                    'recipient' => $recipientEmail,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email: ' . $errorMsg,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview Rincian Slip Gaji & Metadata Perhitungan Individual Karyawan
     */
    public function apiGetEmployeeSalarySlipDetail(Request $request)
    {
        $nik = $request->input('nik');
        $period = $request->input('period', date('Y-m'));

        if (empty($nik)) {
            return response()->json(['success' => false, 'message' => 'Parameter NIK wajib diisi.'], 400);
        }

        try {
            $salaryData = $this->calcService->calculateEmployeeSalary($nik, $period);
            return response()->json([
                'success' => true,
                'data'    => $salaryData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil rincian slip: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // 6. API KONFIGURASI TARIF PPh 21 TER, PASAL 17 & PARAMETER DEDUCTION
    // =========================================================================

    public function apiGetTaxTerRates(Request $request)
    {
        try {
            if (!Schema::hasTable('payroll_tax_ter_rates')) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $category = $request->input('category', 'ALL');
            $query = DB::table('payroll_tax_ter_rates')->where('is_active', 1);

            if ($category !== 'ALL') {
                $query->where('category', $category);
            }

            $rates = $query->orderBy('category', 'asc')->orderBy('min_gross', 'asc')->get();

            return response()->json(['success' => true, 'data' => $rates]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function apiUpdateTaxTerRate(Request $request)
    {
        try {
            if (!Schema::hasTable('payroll_tax_ter_rates')) {
                return response()->json(['success' => false, 'message' => 'Tabel payroll_tax_ter_rates belum tersedia.']);
            }

            $id = $request->input('id');
            $ratePercent = (float)$request->input('ter_rate_percent');
            $minGross = (float)$request->input('min_gross');
            $maxGross = $request->input('max_gross') !== null ? (float)$request->input('max_gross') : null;

            DB::table('payroll_tax_ter_rates')->where('id', $id)->update([
                'ter_rate_percent' => $ratePercent,
                'min_gross'        => $minGross,
                'max_gross'        => $maxGross,
                'updated_by'       => Auth::user() ? Auth::user()->username : 'SYSTEM',
                'updated_at'       => Carbon::now()->toDateTimeString(),
            ]);

            return response()->json(['success' => true, 'message' => 'Tarif TER berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function apiGetTaxBrackets()
    {
        try {
            if (!Schema::hasTable('payroll_tax_brackets')) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $brackets = DB::table('payroll_tax_brackets')
                ->where('is_active', 1)
                ->orderBy('min_amount', 'asc')
                ->get();

            return response()->json(['success' => true, 'data' => $brackets]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function apiUpdateTaxBracket(Request $request)
    {
        try {
            if (!Schema::hasTable('payroll_tax_brackets')) {
                return response()->json(['success' => false, 'message' => 'Tabel payroll_tax_brackets belum tersedia.']);
            }

            $id = $request->input('id');
            $ratePercent = (float)$request->input('rate_percent');

            DB::table('payroll_tax_brackets')->where('id', $id)->update([
                'rate_percent' => $ratePercent,
                'updated_by'   => Auth::user() ? Auth::user()->username : 'SYSTEM',
                'updated_at'   => Carbon::now()->toDateTimeString(),
            ]);

            return response()->json(['success' => true, 'message' => 'Lapisan tarif progresif berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function apiGetDeductionConfigs()
    {
        try {
            if (!Schema::hasTable('payroll_deduction_configs')) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $configs = DB::table('payroll_deduction_configs')
                ->where('is_active', 1)
                ->orderBy('id', 'asc')
                ->get();

            return response()->json(['success' => true, 'data' => $configs]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function apiUpdateDeductionConfig(Request $request)
    {
        try {
            $key = $request->input('config_key');
            $value = $request->input('value_data');

            $this->calcService->updateConfig($key, $value, Auth::user() ? Auth::user()->username : 'SYSTEM');

            return response()->json(['success' => true, 'message' => "Parameter deduction {$key} berhasil diperbarui."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // 9. REPORT PAYROLL (/payroll-report)
    // =========================================================================

    public function payrollReportIndex(Request $request)
    {
        $reportService = app(\App\Services\Payroll\PayrollReportService::class);

        $selectedPeriod = $request->input('period', date('Y-m'));
        $selectedTaskId = $request->input('task_id');
        $selectedDept = $request->input('department');
        $selectedSite = $request->input('site');
        $selectedBank = $request->input('bank');

        // Daftar Periode yang ada di salary_main
        $periods = DB::table('salary_main')
            ->select('Salary_Period')
            ->distinct()
            ->orderBy('Salary_Period', 'DESC')
            ->pluck('Salary_Period');

        // Daftar Task ID Batch Payroll
        $batches = DB::table('salary_main')
            ->select('Task_ID', 'Salary_Period', 'Category', 'Total', 'Status')
            ->orderBy('Created_Date', 'DESC')
            ->take(50)
            ->get();

        // Daftar Departemen & Site
        $departments = DB::table('employment')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->distinct()
            ->pluck('Department');

        $sites = DB::table('employment')
            ->whereNotNull('Location')
            ->where('Location', '!=', '')
            ->distinct()
            ->pluck('Location');

        // Daftar Master Bank dari master_bank
        $banks = [];
        if (Schema::hasTable('master_bank')) {
            $banks = DB::table('master_bank')
                ->where('Is_Active', 1)
                ->orderBy('Bank_Name', 'ASC')
                ->get();
        }

        $reportData = $reportService->getReportData([
            'period'     => $selectedPeriod,
            'task_id'    => $selectedTaskId,
            'department' => $selectedDept,
            'site'       => $selectedSite,
            'bank'       => $selectedBank,
        ]);

        return view('humanresources.payroll.payroll-report', compact(
            'selectedPeriod',
            'selectedTaskId',
            'selectedDept',
            'selectedSite',
            'selectedBank',
            'periods',
            'batches',
            'departments',
            'sites',
            'banks',
            'reportData'
        ));
    }

    public function apiPayrollReportData(Request $request)
    {
        $reportService = app(\App\Services\Payroll\PayrollReportService::class);
        $data = $reportService->getReportData($request->all());

        return response()->json([
            'success' => true,
            'total'   => count($data),
            'rows'    => $data,
        ]);
    }

    public function exportPayrollReportExcel(Request $request)
    {
        $reportService = app(\App\Services\Payroll\PayrollReportService::class);
        $data = $reportService->getReportData($request->all());

        $period = $request->input('period', date('Y-m'));
        $filename = 'Report_Payroll_' . str_replace('-', '', $period) . '_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll Report');

        // Header Title
        $sheet->setCellValue('A1', 'REPORT PAYROLL & TRANSFER SUMMARY');
        $sheet->setCellValue('A2', 'Periode: ' . $period . ' | Export Date: ' . date('d-m-Y H:i:s'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        // Column Headers
        $headers = [
            'A4' => 'No',
            'B4' => 'NIK',
            'C4' => 'Nama',
            'D4' => 'Jabatan',
            'E4' => 'Departemen',
            'F4' => 'Site',
            'G4' => 'Gaji Pokok',
            'H4' => 'Cost of Living Allowance',
            'I4' => 'Tunjangan',
            'J4' => 'Tunjangan Kom & Transport',
            'K4' => 'Tunj. Sourcing',
            'L4' => 'Total Salary',
            'M4' => 'Tanggal Join',
            'N4' => 'Masa Kerja',
            'O4' => 'Gaji / Day',
            'P4' => 'Total Hari Kerja',
            'Q4' => 'Durasi Total Overtime (Jam)',
            'R4' => 'Nilai Bonus Overtime',
            'S4' => 'Jml Unpaid Leave',
            'T4' => 'Unpaid Leave (Rp)',
            'U4' => 'Jml Keterlambatan',
            'V4' => 'Keterlambatan (Rp)',
            'W4' => 'BPJS Ketenagakerjaan',
            'X4' => 'BPJS Kesehatan',
            'Y4' => 'PPh21',
            'Z4' => 'Lain-lain',
            'AA4' => 'Total Penghasilan',
            'AB4' => 'Deduksi',
            'AC4' => 'Nett (THP)',
            'AD4' => 'Terbilang (Indonesia)',
            'AE4' => 'Terbilang (English)',
            'AF4' => 'BCA NOREK',
            'AG4' => 'Atas Nama (BCA)',
            'AH4' => 'SELAIN BCA (BANK)',
            'AI4' => 'SELAIN BCA NOREK #2',
            'AJ4' => 'Atas Nama #2',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Header Style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E7E34'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ];
        $sheet->getStyle('A4:AJ4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Data Rows
        $rowNum = 5;
        foreach ($data as $r) {
            $sheet->setCellValue('A' . $rowNum, $r['no']);
            $sheet->setCellValueExplicit('B' . $rowNum, (string)$r['nik'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $r['nama']);
            $sheet->setCellValue('D' . $rowNum, $r['jabatan']);
            $sheet->setCellValue('E' . $rowNum, $r['departemen']);
            $sheet->setCellValue('F' . $rowNum, $r['site']);
            $sheet->setCellValue('G' . $rowNum, $r['gaji_pokok']);
            $sheet->setCellValue('H' . $rowNum, $r['cola']);
            $sheet->setCellValue('I' . $rowNum, $r['tunjangan']);
            $sheet->setCellValue('J' . $rowNum, $r['tunj_kom_transport']);
            $sheet->setCellValue('K' . $rowNum, $r['tunj_sourcing']);
            $sheet->setCellValue('L' . $rowNum, $r['total_salary']);
            $sheet->setCellValue('M' . $rowNum, $r['tanggal_join']);
            $sheet->setCellValue('N' . $rowNum, $r['masa_kerja']);
            $sheet->setCellValue('O' . $rowNum, $r['gaji_per_day']);
            $sheet->setCellValue('P' . $rowNum, $r['total_hari_kerja']);
            $sheet->setCellValue('Q' . $rowNum, $r['durasi_total_overtime']);
            $sheet->setCellValue('R' . $rowNum, $r['nilai_bonus_overtime']);
            $sheet->setCellValue('S' . $rowNum, $r['jml_unpaid_leave']);
            $sheet->setCellValue('T' . $rowNum, $r['unpaid_leave']);
            $sheet->setCellValue('U' . $rowNum, $r['jml_keterlambatan']);
            $sheet->setCellValue('V' . $rowNum, $r['keterlambatan']);
            $sheet->setCellValue('W' . $rowNum, $r['bpjs_ketenagakerjaan']);
            $sheet->setCellValue('X' . $rowNum, $r['bpjs_kesehatan']);
            $sheet->setCellValue('Y' . $rowNum, $r['pph21']);
            $sheet->setCellValue('Z' . $rowNum, $r['lain_lain']);
            $sheet->setCellValue('AA' . $rowNum, $r['total']);
            $sheet->setCellValue('AB' . $rowNum, $r['deduksi']);
            $sheet->setCellValue('AC' . $rowNum, $r['nett']);
            $sheet->setCellValue('AD' . $rowNum, $r['terbilang_id']);
            $sheet->setCellValue('AE' . $rowNum, $r['terbilang_en']);
            $sheet->setCellValueExplicit('AF' . $rowNum, (string)$r['bca_norek'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('AG' . $rowNum, $r['bca_atas_nama']);
            $sheet->setCellValue('AH' . $rowNum, $r['selain_bca_bank']);
            $sheet->setCellValueExplicit('AI' . $rowNum, (string)$r['selain_bca_norek'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('AJ' . $rowNum, $r['selain_bca_atas_nama']);

            // Format number columns
            foreach (['G', 'H', 'I', 'J', 'K', 'L', 'O', 'R', 'T', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC'] as $col) {
                $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
            }

            $rowNum++;
        }

        // Auto width
        foreach (range('A', 'Z') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        foreach (['AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}


