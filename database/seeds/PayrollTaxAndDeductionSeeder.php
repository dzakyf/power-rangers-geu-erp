<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollTaxAndDeductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // 1. Lapisan Tarif Progresif Pasal 17 UU HPP (Tahunan)
        $brackets = [
            [
                'bracket_name' => 'Lapisan I',
                'min_amount'   => 0,
                'max_amount'   => 60000000,
                'rate_percent' => 5.00,
                'legal_basis'  => 'UU HPP Pasal 17 ayat 1 huruf a',
                'rule_year'    => 2024,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'bracket_name' => 'Lapisan II',
                'min_amount'   => 60000001,
                'max_amount'   => 250000000,
                'rate_percent' => 15.00,
                'legal_basis'  => 'UU HPP Pasal 17 ayat 1 huruf a',
                'rule_year'    => 2024,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'bracket_name' => 'Lapisan III',
                'min_amount'   => 250000001,
                'max_amount'   => 500000000,
                'rate_percent' => 25.00,
                'legal_basis'  => 'UU HPP Pasal 17 ayat 1 huruf a',
                'rule_year'    => 2024,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'bracket_name' => 'Lapisan IV',
                'min_amount'   => 500000001,
                'max_amount'   => 5000000000,
                'rate_percent' => 30.00,
                'legal_basis'  => 'UU HPP Pasal 17 ayat 1 huruf a',
                'rule_year'    => 2024,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'bracket_name' => 'Lapisan V',
                'min_amount'   => 5000000001,
                'max_amount'   => null,
                'rate_percent' => 35.00,
                'legal_basis'  => 'UU HPP Pasal 17 ayat 1 huruf a',
                'rule_year'    => 2024,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        DB::table('payroll_tax_brackets')->delete();
        DB::table('payroll_tax_brackets')->insert($brackets);

        // 2. Data TER Kategori A, B, C (PP 58/2023 & PMK 168/2023)
        $terA = [
            [0, 5400000, 0.00],
            [5400001, 5650000, 0.25],
            [5650001, 5950000, 0.50],
            [5950001, 6300000, 0.75],
            [6300001, 6750000, 1.00],
            [6750001, 7500000, 1.25],
            [7500001, 8550000, 1.50],
            [8550001, 9650000, 1.75],
            [9650001, 10050000, 2.00],
            [10050001, 10350000, 2.25],
            [10350001, 10700000, 2.50],
            [10700001, 11050000, 3.00],
            [11050001, 11600000, 3.50],
            [11600001, 12500000, 4.00],
            [12500001, 13750000, 5.00],
            [13750001, 15100000, 6.00],
            [15100001, 16950000, 7.00],
            [16950001, 19750000, 8.00],
            [19750001, 24150000, 9.00],
            [24150001, 26450000, 10.00],
            [26450001, 28000000, 11.00],
            [28000001, 30050000, 12.00],
            [30050001, 32400000, 13.00],
            [32400001, 35400000, 14.00],
            [35400001, 39100000, 15.00],
            [39100001, 43850000, 16.00],
            [43850001, 47800000, 17.00],
            [47800001, 51400000, 18.00],
            [51400001, 56300000, 19.00],
            [56300001, 62200000, 20.00],
            [62200001, 68600000, 21.00],
            [68600001, 77500000, 22.00],
            [77500001, 89000000, 23.00],
            [89000001, 103000000, 24.00],
            [103000001, 125000000, 25.00],
            [125000001, 157000000, 26.00],
            [157000001, 206000000, 27.00],
            [206000001, 337000000, 28.00],
            [337000001, 454000000, 29.00],
            [454000001, 550000000, 30.00],
            [550000001, 695000000, 31.00],
            [695000001, 910000000, 32.00],
            [910000001, 1400000000, 33.00],
            [1400000001, null, 34.00],
        ];

        $terB = [
            [0, 6200000, 0.00],
            [6200001, 6500000, 0.25],
            [6500001, 6850000, 0.50],
            [6850001, 7300000, 0.75],
            [7300001, 9200000, 1.00],
            [9200001, 10750000, 1.50],
            [10750001, 11250000, 2.00],
            [11250001, 11600000, 2.50],
            [11600001, 12600000, 3.00],
            [12600001, 13600000, 4.00],
            [13600001, 14950000, 5.00],
            [14950001, 16400000, 6.00],
            [16400001, 18450000, 7.00],
            [18450001, 21850000, 8.00],
            [21850001, 26000000, 9.00],
            [26000001, 27700000, 10.00],
            [27700001, 29350000, 11.00],
            [29350001, 31450000, 12.00],
            [31450001, 33950000, 13.00],
            [33950001, 37100000, 14.00],
            [37100001, 41100000, 15.00],
            [41100001, 45800000, 16.00],
            [45800001, 49500000, 17.00],
            [49500001, 53800000, 18.00],
            [53800001, 58500000, 19.00],
            [58500001, 64000000, 20.00],
            [64000001, 71000000, 21.00],
            [71000001, 80000000, 22.00],
            [80000001, 93000000, 23.00],
            [93000001, 109000000, 24.00],
            [109000001, 129000000, 25.00],
            [129000001, 163000000, 26.00],
            [163000001, 211000000, 27.00],
            [211000001, 374000000, 28.00],
            [374000001, 459000000, 29.00],
            [459000001, 555000000, 30.00],
            [555000001, 704000000, 31.00],
            [704000001, 957000000, 32.00],
            [957000001, 1405000000, 33.00],
            [1405000001, null, 34.00],
        ];

        $terC = [
            [0, 6600000, 0.00],
            [6600001, 6950000, 0.25],
            [6950001, 7350000, 0.50],
            [7350001, 7800000, 0.75],
            [7800001, 8850000, 1.00],
            [8850001, 9800000, 1.25],
            [9800001, 10950000, 1.50],
            [10950001, 11200000, 1.75],
            [11200001, 12050000, 2.00],
            [12050001, 12950000, 3.00],
            [12950001, 14150000, 4.00],
            [14150001, 15550000, 5.00],
            [15550001, 17050000, 6.00],
            [17050001, 19500000, 7.00],
            [19500001, 22700000, 8.00],
            [22700001, 26600000, 9.00],
            [26600001, 28100000, 10.00],
            [28100001, 30100000, 11.00],
            [30100001, 32600000, 12.00],
            [32600001, 35400000, 13.00],
            [35400001, 38900000, 14.00],
            [38900001, 43000000, 15.00],
            [43000001, 47400000, 16.00],
            [47400001, 51200000, 17.00],
            [51200001, 55800000, 18.00],
            [55800001, 60800000, 19.00],
            [60800001, 66700000, 20.00],
            [66700001, 74500000, 21.00],
            [74500001, 83200000, 22.00],
            [83200001, 95600000, 23.00],
            [95600001, 110000000, 24.00],
            [110000001, 134000000, 25.00],
            [134000001, 169000000, 26.00],
            [169000001, 221000000, 27.00],
            [221000001, 390000000, 28.00],
            [390000001, 463000000, 29.00],
            [463000001, 561000000, 30.00],
            [561000001, 709000000, 31.00],
            [709000001, 965000000, 32.00],
            [965000001, 1419000000, 33.00],
            [1419000001, null, 34.00],
        ];

        $terInserts = [];
        foreach ($terA as $row) {
            $terInserts[] = [
                'category'         => 'A',
                'min_gross'        => $row[0],
                'max_gross'        => $row[1],
                'ter_rate_percent' => $row[2],
                'legal_basis'      => 'PP 58/2023 & PMK 168/2023',
                'rule_year'        => 2024,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        foreach ($terB as $row) {
            $terInserts[] = [
                'category'         => 'B',
                'min_gross'        => $row[0],
                'max_gross'        => $row[1],
                'ter_rate_percent' => $row[2],
                'legal_basis'      => 'PP 58/2023 & PMK 168/2023',
                'rule_year'        => 2024,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }
        foreach ($terC as $row) {
            $terInserts[] = [
                'category'         => 'C',
                'min_gross'        => $row[0],
                'max_gross'        => $row[1],
                'ter_rate_percent' => $row[2],
                'legal_basis'      => 'PP 58/2023 & PMK 168/2023',
                'rule_year'        => 2024,
                'is_active'        => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        DB::table('payroll_tax_ter_rates')->delete();
        // Insert chunks of 50
        foreach (array_chunk($terInserts, 50) as $chunk) {
            DB::table('payroll_tax_ter_rates')->insert($chunk);
        }

        // 3. Konfigurasi Parameter Deduction Dinamis & Batas/Cap
        $configs = [
            [
                'config_key'   => 'unpaid_leave_divisor',
                'config_name'  => 'Pembagi Hari Kerja Unpaid Leave / Alpha',
                'category'     => 'ATTENDANCE',
                'value_type'   => 'NUMERIC',
                'value_data'   => '26',
                'unit'         => 'DAYS',
                'description'  => 'Total Gaji / 26 * jumlah hari alpha/mangkir',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'late_penalty_per_occurrence',
                'config_name'  => 'Denda Keterlambatan Per Kejadian',
                'category'     => 'ATTENDANCE',
                'value_type'   => 'NUMERIC',
                'value_data'   => '22000',
                'unit'         => 'IDR',
                'description'  => 'Tarif denda keterlambatan per kejadian (Rp 22.000 * frekuensi terlambat)',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'bpjs_tk_jht_employee_rate',
                'config_name'  => 'Tarif BPJS Ketenagakerjaan (JHT Karyawan)',
                'category'     => 'BPJS',
                'value_type'   => 'PERCENT',
                'value_data'   => '2.00',
                'unit'         => '%',
                'description'  => 'Total Salary * 2%',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'bpjs_kes_employee_rate',
                'config_name'  => 'Tarif BPJS Kesehatan Karyawan',
                'category'     => 'BPJS',
                'value_type'   => 'PERCENT',
                'value_data'   => '1.00',
                'unit'         => '%',
                'description'  => 'Tarif iuran BPJS Kesehatan yang dipotong dari karyawan (1%)',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'bpjs_kes_max_salary_cap',
                'config_name'  => 'Batas Maksimal Dasar Perhitungan BPJS Kesehatan',
                'category'     => 'BPJS',
                'value_type'   => 'NUMERIC',
                'value_data'   => '12000000',
                'unit'         => 'IDR',
                'description'  => 'Jika total salary > 12 juta, dasar pengali BPJS Kesehatan dibatasi maksimal 12 juta',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'bpjs_kes_max_deduction_cap',
                'config_name'  => 'Batas Maksimal Nominal Potongan BPJS Kesehatan',
                'category'     => 'BPJS',
                'value_type'   => 'NUMERIC',
                'value_data'   => '120000',
                'unit'         => 'IDR',
                'description'  => 'Potongan maksimal fix Rp 120.000 jika gaji > 12 juta',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'ho_umk_nominal',
                'config_name'  => 'Nilai Acuan UMK / UMR Site Head Office (HO)',
                'category'     => 'BPJS',
                'value_type'   => 'NUMERIC',
                'value_data'   => '5067381',
                'unit'         => 'IDR',
                'description'  => 'UMK acuan HO jika total salary < UMK, maka potongan = UMK * 1%',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'biaya_jabatan_rate',
                'config_name'  => 'Tarif Biaya Jabatan PPh 21',
                'category'     => 'TAX',
                'value_type'   => 'PERCENT',
                'value_data'   => '5.00',
                'unit'         => '%',
                'description'  => '5% dari penghasilan bruto',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'biaya_jabatan_max_monthly',
                'config_name'  => 'Batas Maksimal Biaya Jabatan Bulanan',
                'category'     => 'TAX',
                'value_type'   => 'NUMERIC',
                'value_data'   => '500000',
                'unit'         => 'IDR',
                'description'  => 'Maksimal Rp 500.000 per bulan',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'config_key'   => 'biaya_jabatan_max_yearly',
                'config_name'  => 'Batas Maksimal Biaya Jabatan Tahunan',
                'category'     => 'TAX',
                'value_type'   => 'NUMERIC',
                'value_data'   => '6000000',
                'unit'         => 'IDR',
                'description'  => 'Maksimal Rp 6.000.000 per tahun',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        DB::table('payroll_deduction_configs')->delete();
        DB::table('payroll_deduction_configs')->insert($configs);

        // 4. Update pemetaan ter_category pada payroll_tax_reduction
        if (\Illuminate\Support\Facades\Schema::hasColumn('payroll_tax_reduction', 'ter_category')) {
            // TER A: TK/0, TK/1, K/0
            DB::table('payroll_tax_reduction')
                ->whereIn('Marital_ID', ['TK/0', 'TK/1', 'K/0', 'TK0', 'TK1', 'K0'])
                ->update(['ter_category' => 'A']);

            // TER B: TK/2, TK/3, K/1, K/2
            DB::table('payroll_tax_reduction')
                ->whereIn('Marital_ID', ['TK/2', 'TK/3', 'K/1', 'K/2', 'TK2', 'TK3', 'K1', 'K2'])
                ->update(['ter_category' => 'B']);

            // TER C: K/3
            DB::table('payroll_tax_reduction')
                ->whereIn('Marital_ID', ['K/3', 'K3'])
                ->update(['ter_category' => 'C']);
        }
    }
}

