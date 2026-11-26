<?php

namespace App\Http\Controllers\CNS\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait HandlesCompanyExcelImport
{
    protected function streamExcelTemplate(
        array $columns,
        array $sample,
        string $filename,
        array $requiredColumns = [],
        array $columnMeta = []
    ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($columns, null, 'A1');
        $sheet->fromArray($sample, null, 'A2');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        foreach ($columns as $idx => $colName) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $cell = $colLetter . '1';
            $isRequired = in_array($colName, $requiredColumns, true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($isRequired ? 'FFFADBD8' : 'FFEAECEE');

            if (isset($columnMeta[$colName])) {
                $this->applyExcelColumnMeta($sheet, $colLetter, $columnMeta[$colName]);
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function applyExcelColumnMeta($sheet, string $colLetter, array $meta): void
    {
        $headerCell = $colLetter . '1';

        if (!empty($meta['comment'])) {
            $comment = $sheet->getComment($headerCell);
            $comment->getText()->createTextRun($meta['comment']);
            $comment->setWidth('240pt');
            $comment->setHeight('70pt');
        }

        if (!empty($meta['options']) && is_array($meta['options'])) {
            $formula = '"' . implode(',', array_map(fn($v) => str_replace('"', '', (string) $v), $meta['options'])) . '"';
            if (strlen($formula) <= 255) {
                $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formula);
                $sheet->setDataValidation("{$colLetter}2:{$colLetter}500", $validation);
            }
        }
    }

    /**
     * Validate uploaded spreadsheet and return [colMap, rows] or a 422 JsonResponse.
     */
    protected function readExcelRows(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return response()->json(['message' => 'File Excel kosong atau tidak memiliki baris data.'], 422);
        }

        $headerRow = array_shift($rows);
        $colMap = [];
        foreach ($headerRow as $idx => $header) {
            $key = strtolower(trim((string) $header));
            if ($key !== '') {
                $colMap[$key] = $idx;
            }
        }

        return [$colMap, $rows];
    }

    protected function companyImportColumns(): array
    {
        return [
            'Company_Name',
            'Is_Company',
            'Is_Consignee',
            'Is_Supplier',
            'Contact_Person',
            'NPWP',
            'City',
            'Street',
            'Address',
            'Phone',
            'Fax',
            'HP',
            'Email',
            'Sales_Name',
            'Sales_Phone',
            'Payment',
            'Note',
            'Status',
            'Supplier_Category',
            'Supplier_Sub_Category',
            'Supplier_Group_Category',
            'AP_Salary_Group',
            'Supply_Category',
            'Bank_Rupiah',
            'Bank_Rupiah_Account',
            'Bank_Rupiah_Name',
            'Bank_Foreign',
            'Bank_Foreign_Account',
            'Bank_Foreign_Name',
            'Bank_Foreign_Currency',
        ];
    }

    protected function downloadSupplierTableTemplate(string $filename, string $isConsignee = 'Yes', string $isSupplier = 'No')
    {
        $toTinyInt = fn($v) => $this->parseExcelImportBool((string) $v) ? '1' : '0';

        $sample = [
            'PT Contoh Makmur',
            '1',
            $toTinyInt($isConsignee),
            $toTinyInt($isSupplier),
            'Budi Santoso',
            '01.234.567.8-901.000',
            'Jakarta',
            'Jl. Sudirman No. 1',
            'Jl. Sudirman No. 1, Jakarta',
            '021-5550100',
            '021-5550101',
            '08123456789',
            'budi@contoh.co.id',
            'Andi',
            '08129876543',
            'TOP 30 Days',
            'Sample row',
            'NORMAL',
            '',
            '',
            '',
            '',
            '',
            'BCA',
            '1234567890',
            'PT Contoh Makmur',
            '',
            '',
            '',
            '',
        ];

        $rupiahBanks = [];
        $foreignBanks = [];
        try {
            $rupiahBanks = DB::table('master_bank')
                ->where('Is_foreign', false)
                ->where('Is_Active', true)
                ->orderBy('Bank_Name')
                ->pluck('Bank_Name')
                ->filter()
                ->values()
                ->all();
            $foreignBanks = DB::table('master_bank')
                ->where('Is_foreign', true)
                ->where('Is_Active', true)
                ->orderBy('Bank_Name')
                ->pluck('Bank_Name')
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            // Skip dynamic bank list when DB is unavailable.
        }

        $tinyintMeta = [
            'comment' => 'Tinyint: 1 = Ya, 0 = Tidak',
            'options' => ['1', '0'],
        ];

        $columnMeta = [
            'Is_Company'            => $tinyintMeta,
            'Is_Consignee'          => $tinyintMeta,
            'Is_Supplier'           => $tinyintMeta,
            'Bank_Rupiah'           => [
                'comment' => 'Pilih sesuai Master Bank (IDR)' . (!empty($rupiahBanks) ? ': ' . implode(', ', $rupiahBanks) : '.'),
                'options' => $rupiahBanks,
            ],
            'Bank_Foreign'          => [
                'comment' => 'Pilih sesuai Master Bank (Foreign)' . (!empty($foreignBanks) ? ': ' . implode(', ', $foreignBanks) : '.'),
                'options' => $foreignBanks,
            ],
            'Bank_Foreign_Currency' => [
                'comment' => 'Pilihan currency: IDR, USD, EUR',
                'options' => ['IDR', 'USD', 'EUR'],
            ],
        ];

        return $this->streamExcelTemplate($this->companyImportColumns(), $sample, $filename, ['Company_Name', 'Is_Consignee'], $columnMeta);
    }

    /**
     * Shared import handler for supplier/consignee/company rows into `supplier` table.
     */
    protected function importSupplierTableRows(Request $request, array $fixedFlags = [])
    {
        $parsed = $this->readExcelRows($request);
        if (!is_array($parsed)) {
            return $parsed;
        }
        [$colMap, $rows] = $parsed;

        if (!isset($colMap['company_name'])) {
            return response()->json(['message' => 'Header wajib Company_Name tidak ditemukan.'], 422);
        }

        $user = Auth::user();
        $location = $user->Location;

        $existingNames = DB::table('supplier')
            ->where('Location', $location)
            ->pluck('Company_Name')
            ->map(fn($n) => strtolower(trim((string) $n)))
            ->filter()
            ->flip()
            ->all();

        $seq = DB::table('supplier')->where('Location', $location)->count();

        $imported = 0;
        $skipped = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            $hasData = count(array_filter($row, fn($v) => trim((string) $v) !== '')) > 0;
            if (!$hasData) {
                continue;
            }

            $get = fn(string $col) => isset($colMap[strtolower($col)])
                ? trim((string) ($row[$colMap[strtolower($col)]] ?? ''))
                : '';

            $companyName = $get('Company_Name');
            if ($companyName === '') {
                $errors[] = ['row' => $rowNum, 'name' => '', 'reason' => 'Company_Name wajib diisi.'];
                continue;
            }

            $normName = strtolower($companyName);
            if (isset($existingNames[$normName])) {
                $skipped[] = ['row' => $rowNum, 'name' => $companyName, 'reason' => 'Company_Name sudah terdaftar.'];
                continue;
            }

            $rawEmail = $get('Email');
            $emails = [];
            if ($rawEmail !== '') {
                $emails = $this->parseExcelImportEmails($rawEmail);
                if (is_string($emails)) {
                    $errors[] = ['row' => $rowNum, 'name' => $companyName, 'reason' => $emails];
                    continue;
                }
            }

            $isConsignee = $fixedFlags['Is_Consignee'] ?? $this->parseExcelImportBool($get('Is_Consignee'));
            $isSupplier = $fixedFlags['Is_Supplier'] ?? $this->parseExcelImportBool($get('Is_Supplier'));
            $rawIsCompany = $get('Is_Company');
            $isCompany = $fixedFlags['Is_Company'] ?? ($rawIsCompany !== '' ? $this->parseExcelImportBool($rawIsCompany) : 1);
            $seq++;

            $arr = [
                'Company_Name'            => $companyName,
                'Is_Company'              => $isCompany,
                'Is_Consignee'            => $isConsignee,
                'Is_Supplier'             => $isSupplier,
                'Contact_Person'          => $get('Contact_Person'),
                'NPWP'                    => $get('NPWP'),
                'City'                    => $get('City'),
                'Street'                  => $get('Street'),
                'Address'                 => $get('Address'),
                'Phone'                   => $get('Phone'),
                'Fax'                     => $get('Fax'),
                'HP'                      => $get('HP'),
                'Sales_Name'              => $get('Sales_Name'),
                'Sales_Phone'             => $get('Sales_Phone'),
                'Payment'                 => $get('Payment'),
                'Note'                    => $get('Note'),
                'Supplier_Category'       => $get('Supplier_Category') !== '' ? $get('Supplier_Category') : null,
                'Supplier_Sub_Category'   => $get('Supplier_Sub_Category') !== '' ? $get('Supplier_Sub_Category') : null,
                'Supplier_Group_Category' => $get('Supplier_Group_Category') !== '' ? $get('Supplier_Group_Category') : null,
                'AP_Salary_Group'         => $get('AP_Salary_Group') !== '' ? $get('AP_Salary_Group') : null,
                'Supply_Category'         => $get('Supply_Category') !== '' ? $get('Supply_Category') : null,
                'Bank_Rupiah'             => $get('Bank_Rupiah'),
                'Bank_Rupiah_Account'     => $get('Bank_Rupiah_Account'),
                'Bank_Rupiah_Name'        => $get('Bank_Rupiah_Name'),
                'Bank_Foreign'            => $get('Bank_Foreign'),
                'Bank_Foreign_Account'    => $get('Bank_Foreign_Account'),
                'Bank_Foreign_Name'       => $get('Bank_Foreign_Name'),
                'Bank_Foreign_Currency'   => $get('Bank_Foreign_Currency'),
                'Supplier_ID'             => 'GEU-COMP-' . ($isConsignee ? '2' : '1') . sprintf('%04d', $seq),
                'Status'                  => $get('Status') !== '' ? $get('Status') : 'NORMAL',
                'Created_By'              => $user->username,
                'Location'                => $location,
            ];

            $primary = $emails[0] ?? null;
            if ($primary !== null && strlen($primary) > 60) {
                $primary = null;
            }
            $arr['Email'] = $primary;
            $arr['Emails'] = count($emails) > 0 ? json_encode($emails) : null;

            DB::table('supplier')->insert($arr);

            $existingNames[$normName] = true;
            $imported++;
        }

        return response()->json([
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    /** @return array<int, string>|string */
    protected function parseExcelImportEmails(string $raw)
    {
        $parts = preg_split('/[;,]+/', $raw) ?: [];
        $emails = array_values(array_unique(array_filter(array_map('trim', $parts))));

        if (count($emails) === 0) {
            return 'Minimal 1 email wajib diisi.';
        }
        if (count($emails) > 10) {
            return 'Maksimal 10 email per baris.';
        }
        foreach ($emails as $email) {
            if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Format email tidak valid: {$email}";
            }
        }

        return $emails;
    }

    protected function parseExcelImportBool(string $val): int
    {
        return in_array(strtolower(trim($val)), ['1', 'yes', 'ya', 'true'], true) ? 1 : 0;
    }
}
