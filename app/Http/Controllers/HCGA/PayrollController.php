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

    public function __construct(
        PayrollCalculationService $calcService,
        PayrollTaxService $taxService,
        PayrollAttendanceSyncService $attendanceSyncService
    ) {
        $this->calcService = $calcService;
        $this->taxService = $taxService;
        $this->attendanceSyncService = $attendanceSyncService;
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

        $query = DB::table('payrol_master_group as pmg')
            ->join('employment as e', 'e.NIK', '=', 'pmg.Group_ID')
            ->leftJoin('users as u', 'u.Emp_ID', '=', 'e.NIK')
            ->select(
                'pmg.ID as Group_Master_ID',
                'pmg.Main_ID',
                'pmg.Group_ID as NIK',
                'pmg.Group_Name',
                'pmg.Category',
                'pmg.Use_For',
                'e.Name',
                'e.Gender',
                'e.Position',
                'e.Department'
            );

        if (!empty($mainId)) {
            $query->where('pmg.Main_ID', $mainId);
        }

        $employees = $query->orderBy('e.Name', 'ASC')->get();
        $no = 1;

        foreach ($employees as $emp) {
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

                if ($type === 'BASIC') {
                    $pokok += $val;
                } elseif ($type === 'ALLOWANCE') {
                    if ($it->Payroll_ID === 'PYH00001') {
                        $tunjanganPph += $val;
                    } else {
                        $tunjangan += $val;
                    }
                } elseif ($type === 'DEDUCTION') {
                    if ($it->Payroll_ID === 'PY000000') {
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
        $mainId = $request->input('Main_ID');
        $periodeYm = $request->input('periode', date('Y-m'));
        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';

        if (empty($mainId)) {
            return response()->json(['success' => false, 'message' => 'Parameter Main_ID wajib diisi.'], 400);
        }

        try {
            $result = $this->calcService->recountSalaryGroup($mainId, $periodeYm, $currentUser);

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
        return view('humanresources.payroll.payroll-generator');
    }

    public function payrollGeneratorSubmit(Request $request)
    {
        $taskId = $request->input('Task_ID');
        $period = $request->input('Salary_Period', date('Y-m'));
        $mainId = $request->input('Main_ID');
        $category = $request->input('Category', 'SALARY');
        $useFor = $request->input('Use_For', 'ALL');
        $note = $request->input('Note', '');
        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';
        $location = Auth::user() ? (Auth::user()->Location ?: 'HO') : 'HO';

        if (empty($taskId) || empty($mainId)) {
            return response()->json(['success' => false, 'message' => 'Task_ID dan Main_ID tidak boleh kosong.'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Eksekusi Recounting Grup untuk Mendapatkan Angka Terkini
            $recount = $this->calcService->recountSalaryGroup($mainId, $period, $currentUser);

            // 2. Simpan Header ke salary_main
            $mainGroup = DB::table('payrol_main_group')->where('Main_ID', $mainId)->first();
            $dept = $mainGroup ? $mainGroup->Department : 'HCGA';

            DB::table('salary_main')->insert([
                'Task_ID'       => $taskId,
                'Salary_Period' => $period,
                'Total'         => $recount['total_thp'],
                'Category'      => $category,
                'Department'    => $dept,
                'Use_For'       => $useFor,
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
            foreach ($recount['employees'] as $empCalc) {
                $nik = $empCalc['nik'];
                $nikList[] = $nik;

                DB::table('salary')->insert([
                    'Task_ID'              => $taskId,
                    'Salary_Period'        => $period,
                    'Main_ID'              => $mainId,
                    'Group_ID'             => $nik,
                    'Group_Name'           => $empCalc['name'],
                    'Gross_Up'             => !empty($empCalc['metadata']['is_gross_up']) ? 1 : 0,
                    'Category'             => $category,
                    'Use_For'              => $useFor,
                    'Pokok'                => $empCalc['pokok'],
                    'Tunjangan'            => $empCalc['tunjangan'],
                    'Overtime'             => $empCalc['overtime'],
                    'KJK'                  => 0,
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
                    'Note'                 => $note,
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
            }

            // 4. Kunci Presensi (Data Freezing) Sesuai Tips & Rekomendasi
            $this->attendanceSyncService->lockAttendancePeriod($period, 'POSTED', $currentUser, $nikList);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Draft payroll {$taskId} berhasil digenerate.",
                'task_id' => $taskId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate payroll: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // 5. PAYROLL APPROVAL (/payroll-approval)
    // =========================================================================

    public function payrollApprovalIndex()
    {
        return view('humanresources.payroll.payroll-approval');
    }

    public function payrollApprovalApprove(Request $request)
    {
        $id = $request->input('ID');
        $currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';

        $salaryMain = DB::table('salary_main')->where('Task_ID', $id)->first();
        if (!$salaryMain) {
            return redirect('payroll-approval')->with('error', "Payroll {$id} tidak ditemukan.");
        }

        $currentStatus = $salaryMain->Status;
        $nextStatus = 'APPROVED';

        if ($currentStatus === 'CREATED') {
            $nextStatus = 'PROPOSED';
        } elseif ($currentStatus === 'PROPOSED') {
            $nextStatus = 'APPROVAL_VP';
        } elseif ($currentStatus === 'APPROVAL_VP') {
            $nextStatus = 'APPROVED';
        }

        DB::table('salary_main')->where('Task_ID', $id)->update([
            'Status'     => $nextStatus,
            'Posting_By' => $currentUser,
        ]);

        // Jika sudah final APPROVED, kunci presensi menjadi LOCKED permanen
        if ($nextStatus === 'APPROVED') {
            $this->attendanceSyncService->lockAttendancePeriod($salaryMain->Salary_Period, 'LOCKED', $currentUser);
        }

        return redirect('payroll-approval')->with('success', "Payroll {$id} berhasil di-approve ke status {$nextStatus}.");
    }

    public function payrollApprovalCancel(Request $request)
    {
        $id = $request->input('ID');
        DB::table('salary_main')->where('Task_ID', $id)->update(['Status' => 'CANCELLED']);

        return redirect('payroll-approval')->with('info', "Payroll {$id} telah dibatalkan.");
    }

    public function payrollApprovedIndex()
    {
        return view('humanresources.payroll.payroll-approved');
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
}

