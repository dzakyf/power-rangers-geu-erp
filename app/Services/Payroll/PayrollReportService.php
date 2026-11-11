<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollReportService
{
    /**
     * Ambil data lengkap Report Payroll berdasarkan kriteria filter.
     *
     * @param array $filters (period, task_id, department, site)
     * @return array
     */
    public function getReportData(array $filters = [])
    {
        $period = $filters['period'] ?? null;
        $taskId = $filters['task_id'] ?? null;
        $department = $filters['department'] ?? null;
        $site = $filters['site'] ?? null;
        $bank = $filters['bank'] ?? null;

        $query = DB::table('salary as s')
            ->join('employment as e', 'e.NIK', '=', 's.Group_ID')
            ->leftJoin('salary_main as sm', 'sm.Task_ID', '=', 's.Task_ID')
            ->select(
                's.*',
                'e.Name as emp_name',
                'e.Position as emp_position',
                'e.Department as emp_department',
                'e.Location as emp_location',
                'e.Work_Area as emp_work_area',
                'e.Date_in as date_join',
                'e.Start_Date as start_date',
                'e.Payment_Reference as bank_name_1',
                'e.Payment_Reference2 as bank_acc_1',
                'e.Payment_Reference3 as bank_an_1',
                'e.Payment_Reference_Branch as bank_branch_1',
                'sm.Salary_Period as main_period',
                'sm.Status as main_status'
            );

        if (!empty($taskId)) {
            $query->where('s.Task_ID', $taskId);
        } elseif (!empty($period)) {
            $cleanPeriod = str_replace('-', '', $period);
            $query->where(function ($q) use ($period, $cleanPeriod) {
                $q->where('s.Salary_Period', $period)
                  ->orWhere('s.Salary_Period', $cleanPeriod)
                  ->orWhere('sm.Salary_Period', $period);
            });
        }

        if (!empty($department)) {
            $query->where('e.Department', $department);
        }

        if (!empty($site)) {
            $query->where(function ($q) use ($site) {
                $q->where('e.Location', $site)
                  ->orWhere('e.Work_Area', $site);
            });
        }

        if (!empty($bank)) {
            $bankUpper = strtoupper(trim($bank));
            if ($bankUpper === 'BCA' || strpos($bankUpper, 'CENTRAL ASIA') !== false || strpos($bankUpper, 'BCA') !== false) {
                $query->where(function ($q) {
                    $q->where('e.Payment_Reference', 'LIKE', '%BCA%')
                      ->orWhere('e.Payment_Reference', 'LIKE', '%CENTRAL ASIA%');
                });
            } else {
                $query->where('e.Payment_Reference', $bank);
            }
        }

        $records = $query->orderBy('e.Department', 'ASC')
            ->orderBy('e.Name', 'ASC')
            ->get();

        $rows = [];
        $no = 1;

        foreach ($records as $r) {
            $nik = $r->Group_ID;
            $tId = $r->Task_ID;

            // 1. Ambil Breakdown Komponen dari salary_lists (snapshot penggajian)
            $salaryLists = DB::table('salary_lists')
                ->where('Task_ID', $tId)
                ->where('Group_ID', $nik)
                ->get();

            // Rincian Komponen
            $gajiPokok = (float)$r->Pokok;
            $cola = 0.0;
            $tunjUmum = 0.0;
            $tunjKomTrans = 0.0;
            $tunjSourcing = 0.0;
            $lainLainAllowance = 0.0;

            foreach ($salaryLists as $sl) {
                $nameUpper = strtoupper(trim($sl->Name));
                $pId = strtoupper(trim($sl->Payroll_ID));
                $amount = (float)$sl->Amounts;
                $type = strtoupper(trim($sl->Type_Payroll));

                if ($type === 'BASIC') {
                    if ($gajiPokok <= 0) $gajiPokok = $amount;
                } elseif ($type === 'ALLOWANCE') {
                    if (strpos($nameUpper, 'COST OF LIVING') !== false || strpos($nameUpper, 'COLA') !== false) {
                        $cola += $amount;
                    } elseif (strpos($nameUpper, 'SOURCING') !== false) {
                        $tunjSourcing += $amount;
                    } elseif (
                        strpos($nameUpper, 'KOM') !== false ||
                        strpos($nameUpper, 'TELEKOMUNIKASI') !== false ||
                        strpos($nameUpper, 'TRANSPORT') !== false
                    ) {
                        $tunjKomTrans += $amount;
                    } elseif ($pId !== 'PYH00001' && strpos($nameUpper, 'TUNJANGAN PAJAK') === false) {
                        $tunjUmum += $amount;
                    }
                }
            }

            // Jika breakdown salary_lists kosong (misal import manual), gunakan summary tabel salary
            if ($salaryLists->isEmpty() && (float)$r->Tunjangan > 0) {
                $tunjUmum = (float)$r->Tunjangan;
            }

            $totalSalary = $gajiPokok + $cola + $tunjUmum + $tunjKomTrans + $tunjSourcing;

            // 2. Perhitungan Tanggal Join & Masa Kerja
            $joinDateStr = $r->date_join ?: $r->start_date;
            $masaKerja = '-';
            if ($joinDateStr && $joinDateStr !== '0000-00-00') {
                try {
                    $joinCarbon = Carbon::parse($joinDateStr);
                    $nowCarbon = Carbon::now();
                    $diffYears = $joinCarbon->diffInYears($nowCarbon);
                    $diffMonths = $joinCarbon->copy()->addYears($diffYears)->diffInMonths($nowCarbon);
                    $diffDays = $joinCarbon->copy()->addYears($diffYears)->addMonths($diffMonths)->diffInDays($nowCarbon);
                    $masaKerja = "{$diffYears} Th {$diffMonths} Bln {$diffDays} Hr";
                } catch (\Exception $ex) {
                    $masaKerja = '-';
                }
            }

            // 3. Metadata Presensi & Perhitungan Otomatis
            $meta = [];
            if (!empty($r->calculation_metadata)) {
                $meta = json_decode($r->calculation_metadata, true) ?: [];
            }

            $totalHariKerja = $meta['attendance_metrics']['scheduled_days'] ?? 26;
            if ($totalHariKerja <= 0) $totalHariKerja = 26;

            $gajiPerDay = $totalHariKerja > 0 ? round($totalSalary / $totalHariKerja, 2) : 0;

            // Lembur
            $durasiOvertimeJam = 0.0;
            $overtimeMinutes = $meta['attendance_metrics']['total_overtime_minutes'] ?? 0;
            if ($overtimeMinutes > 0) {
                $durasiOvertimeJam = round($overtimeMinutes / 60, 2);
            }
            $nilaiBonusOvertime = (float)($r->Overtime ?: ($meta['base_components']['overtime'] ?? 0));

            // Unpaid Leave (Alpha)
            $jmlUnpaidLeave = (int)($r->absent_days ?: ($meta['attendance_metrics']['absent_days'] ?? 0));
            $unpaidLeaveNominal = (float)($r->unpaid_leave_amount ?: ($meta['deduction_breakdown']['unpaid_leave']['amount'] ?? 0));

            // Keterlambatan
            $jmlKeterlambatan = (int)($r->late_occurrences ?: ($meta['attendance_metrics']['late_count'] ?? 0));
            $keterlambatanNominal = (float)($r->late_penalty_amount ?: ($meta['deduction_breakdown']['late_penalty']['amount'] ?? 0));

            // BPJS
            $bpjsTk = (float)($r->bpjs_tk_amount ?: ($meta['deduction_breakdown']['bpjs_tk']['amount'] ?? 0));
            $bpjsKes = (float)($r->bpjs_kes_amount ?: ($meta['deduction_breakdown']['bpjs_kes']['amount'] ?? 0));

            // PPh 21
            $pph21 = abs((float)$r->PPH);

            // Lain-lain Potongan
            $lainLainDeduction = (float)($meta['deduction_breakdown']['custom_ded'] ?? 0);

            // Total & Deduksi
            $totalIncomeKotor = $totalSalary + $nilaiBonusOvertime + ((float)$r->Tunjangan_PPH);
            $totalDeduksi = $unpaidLeaveNominal + $keterlambatanNominal + $bpjsTk + $bpjsKes + $pph21 + $lainLainDeduction;

            $nett = (float)$r->THP;
            if ($nett <= 0) {
                $nett = max(0, $totalIncomeKotor - $totalDeduksi);
            }

            // Terbilang ID & EN
            $terbilangId = function_exists('terbilang_id') ? terbilang_id($nett) : '';
            $terbilangEn = function_exists('terbilang_en') ? (terbilang_en($nett) . ' Rupiah') : '';

            // Rekening Bank
            $bankName1 = strtoupper(trim($r->bank_name_1 ?? ''));
            // Cek apakah termasuk Bank BCA (termasuk PT. BANK CENTRAL ASIA Tbk. atau singkatan BCA)
            $isBca = (strpos($bankName1, 'BCA') !== false || strpos($bankName1, 'CENTRAL ASIA') !== false);

            $bcaNorek = $isBca ? ($r->bank_acc_1 ?: '-') : '-';
            $bcaAtasNama = $isBca ? ($r->bank_an_1 ?: $r->emp_name) : '-';

            $nonBcaNorek = !$isBca ? ($r->bank_acc_1 ?: '-') : '-';
            $nonBcaAtasNama = !$isBca ? ($r->bank_an_1 ?: $r->emp_name) : '-';
            $nonBcaBankName = !$isBca && !empty($bankName1) ? $bankName1 : '';

            $rows[] = [
                'no'                     => $no++,
                'nik'                    => $nik,
                'nama'                   => $r->emp_name ?: $r->Group_Name,
                'jabatan'                => $r->emp_position ?: '-',
                'departemen'             => $r->emp_department ?: '-',
                'site'                   => $r->emp_location ?: ($r->emp_work_area ?: 'HO'),
                'gaji_pokok'             => $gajiPokok,
                'cola'                   => $cola,
                'tunjangan'              => $tunjUmum,
                'tunj_kom_transport'     => $tunjKomTrans,
                'tunj_sourcing'          => $tunjSourcing,
                'total_salary'           => $totalSalary,
                'tanggal_join'           => $joinDateStr ?: '-',
                'masa_kerja'             => $masaKerja,
                'gaji_per_day'           => $gajiPerDay,
                'total_hari_kerja'       => $totalHariKerja,
                'durasi_total_overtime'  => $durasiOvertimeJam,
                'nilai_bonus_overtime'   => $nilaiBonusOvertime,
                'jml_unpaid_leave'       => $jmlUnpaidLeave,
                'unpaid_leave'           => $unpaidLeaveNominal,
                'jml_keterlambatan'      => $jmlKeterlambatan,
                'keterlambatan'          => $keterlambatanNominal,
                'bpjs_ketenagakerjaan'   => $bpjsTk,
                'bpjs_kesehatan'         => $bpjsKes,
                'pph21'                  => $pph21,
                'lain_lain'              => $lainLainDeduction,
                'total'                  => $totalIncomeKotor,
                'deduksi'                => $totalDeduksi,
                'nett'                   => $nett,
                'terbilang_id'           => $terbilangId,
                'terbilang_en'           => $terbilangEn,
                'bca_norek'              => $bcaNorek,
                'bca_atas_nama'          => $bcaAtasNama,
                'selain_bca_bank'        => $nonBcaBankName,
                'selain_bca_norek'       => $nonBcaNorek,
                'selain_bca_atas_nama'   => $nonBcaAtasNama,
                'task_id'                => $tId,
                'period'                 => $r->Salary_Period ?: $r->main_period,
            ];
        }

        return $rows;
    }
}

