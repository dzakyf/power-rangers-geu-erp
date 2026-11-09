<?php

namespace App\Services\Payroll;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PayrollTaxService
{
    /**
     * Resolusi Kategori TER ('A', 'B', 'C') berdasarkan status PTKP.
     *
     * @param string $ptkpCode Contoh: 'TK/0', 'K/1', 'K/3'
     * @return string 'A' | 'B' | 'C'
     */
    public function resolveTerCategory($ptkpCode)
    {
        $code = strtoupper(trim(str_replace([' ', '-', '_'], '', $ptkpCode)));

        // 1. Coba lookup dari tabel payroll_tax_reduction jika database terhubung
        try {
            if (Schema::hasTable('payroll_tax_reduction') && Schema::hasColumn('payroll_tax_reduction', 'ter_category')) {
                $row = DB::table('payroll_tax_reduction')
                    ->where('Marital_ID', $ptkpCode)
                    ->orWhere('Marital_ID', $code)
                    ->first();

                if ($row && !empty($row->ter_category)) {
                    return strtoupper($row->ter_category);
                }
            }
        } catch (\Exception $e) {
            // Abaikan error koneksi DB dan gunakan fallback standar
        }

        // 2. Fallback standar PP 58/2023 & PMK 168/2023
        // Kategori A: TK/0, TK/1, K/0
        if (in_array($code, ['TK/0', 'TK0', 'TK/1', 'TK1', 'K/0', 'K0'])) {
            return 'A';
        }

        // Kategori B: TK/2, TK/3, K/1, K/2
        if (in_array($code, ['TK/2', 'TK2', 'TK/3', 'TK3', 'K/1', 'K1', 'K/2', 'K2'])) {
            return 'B';
        }

        // Kategori C: K/3
        if (in_array($code, ['K/3', 'K3'])) {
            return 'C';
        }

        // Default fallback Kategori A
        return 'A';
    }

    /**
     * Ambil Nilai Nominal PTKP Tahunan
     *
     * @param string $ptkpCode
     * @return float
     */
    public function getPtkpNominal($ptkpCode)
    {
        try {
            if (Schema::hasTable('payroll_tax_reduction')) {
                $row = DB::table('payroll_tax_reduction')
                    ->where('Marital_ID', $ptkpCode)
                    ->first();

                if ($row && (float)$row->Reduction_Amount > 0) {
                    return (float)$row->Reduction_Amount;
                }
            }
        } catch (\Exception $e) {
            // Abaikan dan gunakan standar PTKP
        }

        $code = strtoupper(trim(str_replace([' ', '-', '_'], '', $ptkpCode)));
        $ptkpDefaults = [
            'TK0' => 54000000, 'TK/0' => 54000000,
            'TK1' => 58500000, 'TK/1' => 58500000,
            'TK2' => 63000000, 'TK/2' => 63000000,
            'TK3' => 67500000, 'TK/3' => 67500000,
            'K0'  => 58500000, 'K/0'  => 58500000,
            'K1'  => 63000000, 'K/1'  => 63000000,
            'K2'  => 67500000, 'K/2'  => 67500000,
            'K3'  => 72000000, 'K/3'  => 72000000,
        ];

        return isset($ptkpDefaults[$code]) ? (float)$ptkpDefaults[$code] : 54000000.0;
    }

    /**
     * Hitung PPh 21 Bulanan Skema TER (Tarif Efektif Rata-Rata) PP 58/2023 & PMK 168/2023.
     * Digunakan untuk masa pajak Januari s/d November.
     *
     * @param float $grossSalary Penghasilan bruto sebulan
     * @param string $ptkpCode Kode PTKP karyawan
     * @param array $options Opsi tambahan: has_npwp, is_gross_up, nik
     * @return array
     */
    public function calculatePph21Ter($grossSalary, $ptkpCode, $options = [])
    {
        $gross = max(0.0, (float)$grossSalary);
        $terCategory = $this->resolveTerCategory($ptkpCode);
        $isGrossUp = !empty($options['is_gross_up']);
        $nik = isset($options['nik']) ? trim($options['nik']) : '';
        $npwp = isset($options['npwp']) ? trim($options['npwp']) : '';

        // Resolusi tarif TER dari tabel konfigurasi database
        $ratePercent = $this->getTerRatePercent($terCategory, $gross);

        // Validasi NPWP / NIK 16 digit sesuai PMK 168/2023
        // Jika memiliki NIK 16 digit atau NPWP valid, tidak dikenakan penalti 120%
        $hasValidTaxId = (strlen(preg_replace('/\D/', '', $nik)) >= 16 || strlen(preg_replace('/\D/', '', $npwp)) >= 15);
        $taxMultiplier = $hasValidTaxId ? 1.0 : 1.2;

        if (!$isGrossUp) {
            // Metode GROSS (Potong dari karyawan)
            $taxAmount = ceil(($ratePercent / 100.0) * $gross * $taxMultiplier);
            $taxAllowance = 0.0;
        } else {
            // Metode GROSS-UP (Ditanggung perusahaan via Tunjangan Pajak)
            // Rumus gross-up TER: PPh = (Tarif / (1 - Tarif)) * Bruto Dasar
            $effectiveDecimal = ($ratePercent / 100.0) * $taxMultiplier;
            if ($effectiveDecimal >= 1.0) {
                $effectiveDecimal = 0.34;
            }
            $divisor = max(0.01, 1.0 - $effectiveDecimal);
            $taxAllowance = ceil(($effectiveDecimal / $divisor) * $gross);
            $taxAmount = $taxAllowance;
        }

        return [
            'scheme'            => 'TER',
            'ter_category'      => $terCategory,
            'ptkp_code'         => $ptkpCode,
            'gross_monthly'     => $gross,
            'rate_percent'      => $ratePercent,
            'tax_amount'        => $taxAmount,
            'tax_allowance'     => $taxAllowance,
            'is_gross_up'       => $isGrossUp,
            'has_valid_tax_id'  => $hasValidTaxId,
            'tax_multiplier'    => $taxMultiplier,
            'legal_basis'       => 'PP 58/2023 & PMK 168/2023',
            'calculated_at'     => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Hitung PPh 21 Tahunan / Masa Pajak Terakhir (Desember / Resign)
     * Menggunakan Tarif Progresif Pasal 17 ayat (1) huruf a UU HPP.
     *
     * @param float $yearlyGross Total penghasilan bruto setahun penuh (atau s/d masa resign)
     * @param string $ptkpCode Kode PTKP
     * @param float $ytdTaxPaidTotal Akumulasi PPh 21 TER yang telah dipotong pada masa sebelumnya
     * @param array $options Opsi tambahan: biaya_jabatan, jht_karyawan, is_gross_up
     * @return array
     */
    public function calculatePph21YearlyPasal17($yearlyGross, $ptkpCode, $ytdTaxPaidTotal = 0.0, $options = [])
    {
        $gross = max(0.0, (float)$yearlyGross);
        $ytdTaxPaid = max(0.0, (float)$ytdTaxPaidTotal);
        $isGrossUp = !empty($options['is_gross_up']);

        // 1. Biaya Jabatan 5% (Maksimal Rp 6.000.000 per tahun)
        $biayaJabatanRate = 0.05;
        $maxBiayaJabatanYearly = 6000000.0;
        $biayaJabatan = min($gross * $biayaJabatanRate, $maxBiayaJabatanYearly);
        if (isset($options['biaya_jabatan'])) {
            $biayaJabatan = min((float)$options['biaya_jabatan'], $maxBiayaJabatanYearly);
        }

        // 2. Pengurang Iuran Pensiun / JHT Karyawan Setahun
        $jhtEmployeeYearly = isset($options['jht_employee_yearly']) ? (float)$options['jht_employee_yearly'] : 0.0;

        // 3. Penghasilan Neto Setahun
        $nettoYearly = max(0.0, $gross - $biayaJabatan - $jhtEmployeeYearly);

        // 4. PTKP Tahunan
        $ptkpNominal = $this->getPtkpNominal($ptkpCode);

        // 5. Penghasilan Kena Pajak (PKP) Setahun (dibulatkan ke bawah ribuan rupiah)
        $pkp = floor(max(0.0, $nettoYearly - $ptkpNominal) / 1000.0) * 1000.0;

        // 6. Hitung Pajak Berdasarkan Layer Tarif Progresif Pasal 17 UU HPP
        $brackets = $this->getTaxBrackets();
        $totalYearlyTax = 0.0;
        $bracketBreakdown = [];
        $remainingPkp = $pkp;

        foreach ($brackets as $b) {
            if ($remainingPkp <= 0) {
                break;
            }

            $bracketMin = (float)$b['min_amount'];
            $bracketMax = $b['max_amount'] !== null ? (float)$b['max_amount'] : null;
            $rate = (float)$b['rate_percent'] / 100.0;

            $bracketCapacity = $bracketMax !== null ? ($bracketMax - $bracketMin + 1) : PHP_FLOAT_MAX;
            $taxableInBracket = min($remainingPkp, $bracketCapacity);

            $taxInBracket = ceil($taxableInBracket * $rate);
            $totalYearlyTax += $taxInBracket;
            $remainingPkp -= $taxableInBracket;

            $bracketBreakdown[] = [
                'bracket_name' => $b['bracket_name'],
                'rate_percent' => $b['rate_percent'],
                'taxable'      => $taxableInBracket,
                'tax_amount'   => $taxInBracket,
            ];
        }

        // PPh 21 Masa Terakhir (Desember / Resign) = Pajak Setahun - Akumulasi Pajak Terbayar Jan-Nov
        $decemberTax = max(0.0, $totalYearlyTax - $ytdTaxPaid);

        return [
            'scheme'              => 'PASAL_17_UU_HPP',
            'ptkp_code'           => $ptkpCode,
            'ptkp_nominal'        => $ptkpNominal,
            'yearly_gross'        => $gross,
            'biaya_jabatan'       => $biayaJabatan,
            'jht_employee_yearly' => $jhtEmployeeYearly,
            'yearly_netto'        => $nettoYearly,
            'pkp'                 => $pkp,
            'total_yearly_tax'    => $totalYearlyTax,
            'ytd_tax_paid'        => $ytdTaxPaid,
            'final_month_tax'     => $decemberTax,
            'bracket_breakdown'   => $bracketBreakdown,
            'is_gross_up'         => $isGrossUp,
            'calculated_at'       => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Hitung Pajak Bulanan Otomatis (Memilih TER jika bulan 1-11, atau Pasal 17 jika bulan 12 / resign)
     *
     * @param float $grossMonth Bruto bulan berjalan
     * @param string $ptkpCode Kode PTKP
     * @param int $monthNumber Nomor bulan (1 s/d 12)
     * @param float $ytdGross Akumulasi bruto tahun berjalan (termasuk bulan ini)
     * @param float $ytdTaxPaid Akumulasi pajak terbayar s/d bulan lalu
     * @param array $options Opsi tambahan (is_resigned, is_gross_up, nik, jht_monthly)
     * @return array
     */
    public function calculateMonthlyTax($grossMonth, $ptkpCode, $monthNumber, $ytdGross = 0.0, $ytdTaxPaid = 0.0, $options = [])
    {
        $isYearEndOrResign = ($monthNumber >= 12 || !empty($options['is_resigned']));

        if (!$isYearEndOrResign) {
            // Gunakan TER Bulanan
            $terResult = $this->calculatePph21Ter($grossMonth, $ptkpCode, $options);
            return [
                'type'           => 'MONTHLY_TER',
                'month'          => $monthNumber,
                'tax_to_deduct'  => $terResult['tax_amount'],
                'tax_allowance'  => $terResult['tax_allowance'],
                'details'        => $terResult,
            ];
        } else {
            // Gunakan Tarif Progresif Tahunan Pasal 17 UU HPP
            $totalYearlyGross = max((float)$ytdGross, (float)$grossMonth);
            $pasal17Result = $this->calculatePph21YearlyPasal17($totalYearlyGross, $ptkpCode, $ytdTaxPaid, $options);
            return [
                'type'           => 'YEAR_END_PASAL_17',
                'month'          => $monthNumber,
                'tax_to_deduct'  => $pasal17Result['final_month_tax'],
                'tax_allowance'  => !empty($options['is_gross_up']) ? $pasal17Result['final_month_tax'] : 0.0,
                'details'        => $pasal17Result,
            ];
        }
    }

    /**
     * Lookup Persentase Tarif TER dari database
     */
    public function getTerRatePercent($category, $gross)
    {
        try {
            if (Schema::hasTable('payroll_tax_ter_rates')) {
                $row = DB::table('payroll_tax_ter_rates')
                    ->where('category', $category)
                    ->where('is_active', 1)
                    ->where('min_gross', '<=', $gross)
                    ->where(function ($query) use ($gross) {
                        $query->whereNull('max_gross')
                              ->orWhere('max_gross', '>=', $gross);
                    })
                    ->orderBy('min_gross', 'desc')
                    ->first();

                if ($row) {
                    return (float)$row->ter_rate_percent;
                }
            }
        } catch (\Exception $e) {
            // Gunakan fallback tabel statis PP 58/2023
        }

        // Fallback jika belum seeding atau DB kosong:
        if ($category === 'A') {
            if ($gross <= 5400000) return 0.00;
            if ($gross <= 5650000) return 0.25;
            if ($gross <= 5950000) return 0.50;
            if ($gross <= 6300000) return 0.75;
            if ($gross <= 6750000) return 1.00;
            if ($gross <= 7500000) return 1.25;
            if ($gross <= 8550000) return 1.50;
            if ($gross <= 9650000) return 1.75;
            if ($gross <= 10050000) return 2.00;
            if ($gross <= 12500000) return 4.00;
            if ($gross <= 15100000) return 6.00;
            if ($gross <= 24150000) return 9.00;
            if ($gross <= 35400000) return 14.00;
            return 20.00;
        }

        return 0.00;
    }

    /**
     * Ambil Master Lapisan Tarif Progresif Pasal 17 UU HPP
     */
    public function getTaxBrackets()
    {
        try {
            if (Schema::hasTable('payroll_tax_brackets')) {
                $rows = DB::table('payroll_tax_brackets')
                    ->where('is_active', 1)
                    ->orderBy('min_amount', 'asc')
                    ->get();

                if ($rows->isNotEmpty()) {
                    return $rows->map(function ($r) {
                        return [
                            'bracket_name' => $r->bracket_name,
                            'min_amount'   => (float)$r->min_amount,
                            'max_amount'   => $r->max_amount !== null ? (float)$r->max_amount : null,
                            'rate_percent' => (float)$r->rate_percent,
                        ];
                    })->toArray();
                }
            }
        } catch (\Exception $e) {
            // Gunakan 5 Lapisan default UU HPP
        }

        // Default 5 Lapisan UU HPP
        return [
            ['bracket_name' => 'Lapisan I',   'min_amount' => 0,          'max_amount' => 60000000,   'rate_percent' => 5.00],
            ['bracket_name' => 'Lapisan II',  'min_amount' => 60000001,   'max_amount' => 250000000,  'rate_percent' => 15.00],
            ['bracket_name' => 'Lapisan III', 'min_amount' => 250000001,  'max_amount' => 500000000,  'rate_percent' => 25.00],
            ['bracket_name' => 'Lapisan IV',  'min_amount' => 500000001,  'max_amount' => 5000000000, 'rate_percent' => 30.00],
            ['bracket_name' => 'Lapisan V',   'min_amount' => 5000000001, 'max_amount' => null,       'rate_percent' => 35.00],
        ];
    }
}
