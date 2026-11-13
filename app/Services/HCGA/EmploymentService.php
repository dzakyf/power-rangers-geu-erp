<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Exception;

class EmploymentService
{
    /**
     * Update detail data master employment (karyawan) dengan parameter binding aman
     *
     * @param array $input Data POST dari form detail karyawan
     * @return array
     */
    public function updateEmployeeDetailData(array $input): array
    {
        $id = $input['ID'] ?? null;
        if (empty($id)) {
            throw new Exception("ID Karyawan wajib disertakan.");
        }

        $empRow = DB::table('employment')->where('ID', $id)->first();
        if (!$empRow) {
            throw new Exception("Data karyawan tidak ditemukan.");
        }

        $columns = Schema::getColumnListing('employment');
        $ignoreKeys = ['buttonSimpan', 'ID', 'undefined', '_token'];
        $updateData = [];

        // Daftar kolom tanggal yang bila kosong wajib bernilai NULL agar tidak terjadi error strict SQL
        $dateColumns = [
            'Birth_Date', 'Date_in', 'Start_Date', 'End_Date', 
            'Date_Out', 'probation_end_date', 'Employment_Status_Date',
            'Prorate_Salary_Date', 'Prorate_Resigned_Date', 'Resigned_Date',
            'Registration_Status_Date', 'Interview_Date', 'Conversion_Date'
        ];

        // Daftar kolom integer/boolean flag
        $flagColumns = [
            'bpjs_tk_deduction', 'bpjs_kes_deduction', 'bpjs_probation_exempt', 
            'PPH_Allowance', 'Gross_Up'
        ];

        foreach ($input as $key => $val) {
            if (in_array($key, $ignoreKeys, true)) {
                continue;
            }

            if (!in_array($key, $columns, true)) {
                continue;
            }

            $trimmedVal = is_string($val) ? trim($val) : $val;

            // Normalisasi kolom tanggal
            if (in_array($key, $dateColumns, true)) {
                if ($trimmedVal === '' || $trimmedVal === '0000-00-00') {
                    $updateData[$key] = null;
                } else {
                    $updateData[$key] = date('Y-m-d', strtotime($trimmedVal));
                }
                continue;
            }

            // Normalisasi kolom flag / boolean
            if (in_array($key, $flagColumns, true)) {
                $updateData[$key] = (int)$trimmedVal;
                continue;
            }

            $updateData[$key] = $trimmedVal;
        }

        if (!empty($updateData)) {
            DB::table('employment')->where('ID', $id)->update($updateData);
        }

        // Sinkronisasi data ke tabel users berdasarkan Emp_ID = employment.NIK
        $usersUpdate = [];
        if (isset($input['Location']) && !empty($input['Location'])) {
            $usersUpdate['Location'] = trim($input['Location']);
        }
        if (isset($input['overtime_compensation'])) {
            $compVal = trim($input['overtime_compensation']);
            $usersUpdate['overtime_compensation'] = in_array($compVal, ['PAID', 'TIME_OFF'], true) ? $compVal : null;
        }

        if (!empty($usersUpdate) && !empty($empRow->NIK)) {
            DB::table('users')->where('Emp_ID', $empRow->NIK)->update($usersUpdate);
        }

        return [
            'success' => true,
            'message' => 'Employee data updated successfully!',
        ];
    }
}
