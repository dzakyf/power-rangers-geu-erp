<?php

namespace App\Services\HCGA;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeeDetailService
{
    /**
     * XML sub-grid Dokumen Karyawan (employment_document).
     */
    public function getDocumentsXml(string $taskId): string
    {
        $rows = DB::table('employment_document')->where('Task_ID', $taskId)->get();
        $docFields = [
            ['Foto', 'PHOTO', 'photo'],
            ['CV', 'CV', 'cv'],
            ['Ijazah', 'IJAZAH', 'ijazah'],
            ['Sertifikat', 'SERTIFIKAT', 'sertifikat'],
            ['KTP', 'KTP', 'ktp'],
            ['NPWP', 'NPWP', 'npwp'],
            ['KK', 'KK', 'kk'],
            ['Akta', 'Akta', 'akta'],
            ['SIM', 'SIM', 'sim'],
        ];

        $xml = $this->xmlStart();
        $nomor = 1;
        foreach ($rows as $fetch) {
            foreach ($docFields as $cfg) {
                $col = $cfg[0];
                $descCol = $col . '_Desc';
                $label = $cfg[1];
                $folder = $cfg[2];

                $fileVal = (string) ($fetch->$col ?? '');
                if ($fileVal === '') {
                    continue;
                }

                $xml .= '<row id="' . $nomor . '">';
                $xml .= '<cell>' . $nomor . '</cell>';
                $xml .= '<cell>' . $this->esc($label) . '</cell>';
                $xml .= '<cell>' . $this->esc($fileVal) . '</cell>';
                $xml .= '<cell>' . $this->esc((string) ($fetch->$descCol ?? '')) . '</cell>';
                $xml .= '<cell></cell>';
                $xml .= '<cell>' . $this->esc((string) ($fetch->Last_Updated ?? '')) . '</cell>';
                $xml .= '<cell>' . $this->esc((string) ($fetch->Created_By ?? '')) . '</cell>';
                $xml .= '<cell>' . $this->esc($folder . '/' . $fileVal) . '</cell>';
                $xml .= '</row>';
                $nomor++;
            }
        }
        $xml .= '</rows>';

        return $xml;
    }

    /**
     * XML sub-grid Riwayat Karyawan & Status Changes (employment_history + status_changes).
     */
    public function getHistoryXml(string $key): string
    {
        $emp = DB::table('employment')
            ->where('Task_ID', $key)
            ->orWhere('ID', $key)
            ->orWhere('NIK', $key)
            ->first();
        $nik = $emp ? (string) ($emp->NIK ?? $key) : $key;
        $empTaskId = $emp ? (string) ($emp->Task_ID ?? $key) : $key;

        $rows = DB::table('employment_history')
            ->where(function ($q) use ($nik, $empTaskId) {
                if ($nik !== '') {
                    $q->where('NIK', $nik);
                }
                if ($empTaskId !== '') {
                    $q->orWhere('Task_ID', $empTaskId);
                }
            })
            ->orderBy('ID', 'DESC')
            ->get();

        $xml = $this->xmlStart();
        $nomor = 1;
        foreach ($rows as $fetch) {
            $endDate = (($fetch->End_Date ?? '') === '9999-12-31') ? '' : (string) ($fetch->End_Date ?? '');
            $xml .= '<row id="' . $this->esc((string) $fetch->ID) . '">';
            $xml .= '<cell>' . $nomor . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Task_ID ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->NIK ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Date_Process ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Activity ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Reference ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Notes ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Start_Date ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc($endDate) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Remark ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->User_Trx ?? '')) . '</cell>';
            $xml .= '<cell>' . $this->esc((string) ($fetch->Date_Trx ?? '')) . '</cell>';
            $xml .= '</row>';
            $nomor++;
        }

        if (Schema::hasTable('status_changes')) {
            $scRows = DB::table('status_changes')->where('NIK', $nik)->orderBy('id', 'DESC')->get();
            if ($scRows->isNotEmpty()) {
                $tercatat = DB::table('employment_history')
                    ->where('NIK', $nik)
                    ->whereNotNull('Reference')
                    ->pluck('Reference')
                    ->toArray();

                foreach ($scRows as $fetch) {
                    $formNo = $fetch->form_number ?: ('PS-DRAFT-' . $fetch->id);
                    if (in_array($formNo, $tercatat, true)) {
                        continue;
                    }

                    $catatan = 'Jenis: ' . ($fetch->jenis ?: 'MUTATION');
                    if (!empty($fetch->current_position) || !empty($fetch->proposed_position)) {
                        $catatan .= '; Jabatan: ' . ($fetch->current_position ?: '-') . ' -> ' . ($fetch->proposed_position ?: '-');
                    }
                    if (!empty($fetch->current_department) || !empty($fetch->proposed_department)) {
                        $catatan .= '; Departemen: ' . ($fetch->current_department ?: '-') . ' -> ' . ($fetch->proposed_department ?: '-');
                    }
                    if (!empty($fetch->current_work_location) || !empty($fetch->proposed_work_location)) {
                        $catatan .= '; Lokasi: ' . ($fetch->current_work_location ?: '-') . ' -> ' . ($fetch->proposed_work_location ?: '-');
                    }
                    if ((float) ($fetch->current_salary ?? 0) != (float) ($fetch->proposed_salary ?? 0)) {
                        $catatan .= '; Salary: ' . number_format((float) $fetch->current_salary, 0, ',', '.')
                            . ' -> ' . number_format((float) $fetch->proposed_salary, 0, ',', '.');
                    }

                    $xml .= '<row id="SC' . $this->esc((string) $fetch->id) . '">';
                    $xml .= '<cell>' . $nomor . '</cell>';
                    $xml .= '<cell></cell>';
                    $xml .= '<cell>' . $this->esc((string) ($fetch->nik ?? '')) . '</cell>';
                    $xml .= '<cell>' . $this->esc(substr((string) ($fetch->created_at ?? ''), 0, 10)) . '</cell>';
                    $xml .= '<cell>PERUBAHAN STATUS</cell>';
                    $xml .= '<cell>' . $this->esc($formNo) . '</cell>';
                    $xml .= '<cell>' . $this->esc($catatan) . '</cell>';
                    $xml .= '<cell>' . $this->esc((string) ($fetch->effective_date ?: '')) . '</cell>';
                    $xml .= '<cell></cell>';
                    $xml .= '<cell>' . $this->esc(((string) ($fetch->jenis ?? '') === 'PROMOTION') ? 'Promosi' : 'Mutasi') . '</cell>';
                    $xml .= '<cell>' . $this->esc((string) ($fetch->proposed_by ?: '')) . '</cell>';
                    $xml .= '<cell>' . $this->esc(substr((string) ($fetch->created_at ?? ''), 0, 10)) . '</cell>';
                    $xml .= '</row>';
                    $nomor++;
                }
            }
        }

        $xml .= '</rows>';

        return $xml;
    }

    public function getInsuranceXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_insurances')->where('Task_ID', $taskId)->get(),
            [
                'Employee_Name', 'On_Behalf', 'On_Behalf_Status', 'Insurances', 'Card_No',
                'Polis_No', 'In_Salary', 'Polis_Type', 'Polis_Benefit', 'Polis_Reimbursement',
                'Polis_Status', 'Date_Start', 'Date_End', 'Date_Submission', 'Employee_Collateral',
                'Salary_Component',
            ]
        );
    }

    public function getOrganizationXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_organization')->where('Task_ID', $taskId)->get(),
            ['Organization', 'Position', 'Location', 'At_Year', 'Note']
        );
    }

    public function getFamilyXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_family')->where('Task_ID', $taskId)->orderBy('ID', 'DESC')->get(),
            ['Family_Status', 'Name', 'Gender', 'Birth_Date', 'Last_Education', 'Occupation']
        );
    }

    public function getAcademicXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_akademic')->where('Task_ID', $taskId)->get(),
            ['Education_Level', 'College', 'Education_Major', 'Location', 'Certified_Year', 'GPA']
        );
    }

    public function getContactPointsXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_contact_point')->where('Task_ID', $taskId)->orderBy('ID', 'DESC')->get(),
            ['Name', 'Employee_Department', 'Position', 'Contact_No', 'Email', 'Phone_No']
        );
    }

    public function getInformalXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_informal')->where('Task_ID', $taskId)->get(),
            ['Training_Company', 'Location', 'Year_Training', 'Note']
        );
    }

    public function getDrivingXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_driving')->where('Task_ID', $taskId)->orderBy('ID', 'DESC')->get(),
            ['Description', 'Tipe', 'Note']
        );
    }

    public function getHealthXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_health')->where('Task_ID', $taskId)->orderBy('ID', 'DESC')->get(),
            ['Description', 'Disease', 'Year_Health', 'Days']
        );
    }

    public function getExperienceXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_experiences')->where('Task_ID', $taskId)->get(),
            ['Start_Date', 'End_Date', 'Position', 'Company_Name', 'Last_Salary', 'Reason']
        );
    }

    public function getOtherSkillXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_other_skill')->where('Task_ID', $taskId)->get(),
            ['Category', 'Subject', 'Year_Skill', 'Competences']
        );
    }

    public function getAwardXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_awards')->where('Task_ID', $taskId)->get(),
            ['MonthYear', 'Place', 'Detail', 'Title']
        );
    }

    public function getLanguageXml(string $taskId): string
    {
        return $this->buildSimpleTableXml(
            DB::table('employment_language')->where('Task_ID', $taskId)->get(),
            ['Language', 'Oral', 'Writing', 'Additional', 'Note']
        );
    }

    public function getKjkXml(string $nik): string
    {
        return $this->buildSimpleTableXml(
            DB::table('kjk_lists')->where('NIK', $nik)->orderBy('ID', 'DESC')->get(),
            ['Task_ID', 'NIK', 'Name', 'Allowance', 'Rate_KJK', 'KJK_Value', 'Total', 'KJK_Period', 'Note', 'Salary_Period', 'Created_Date', 'Created_By']
        );
    }

    public function getOvertimeXml(string $nik): string
    {
        return $this->buildSimpleTableXml(
            DB::table('overtime_lists')->where('NIK', $nik)->orderBy('ID', 'DESC')->get(),
            ['Task_ID', 'NIK', 'Name', 'Start_Date', 'End_Date', 'Early_IN', 'Early_OUT', 'After_IN', 'After_OUT', 'Holiday', 'Total_OT', 'Calculated_OT', 'IS_Outdated', 'Salary_Period', 'Amounts', 'Reference', 'Note', 'Created_Date', 'Created_By']
        );
    }

    /**
     * Ambil data Medical & Physical Information dari employment_detail.
     */
    public function getOtherMedicalData(string $taskId): array
    {
        $row = ($taskId !== '' && Schema::hasTable('employment_detail'))
            ? DB::table('employment_detail')->where('Task_ID', $taskId)->first()
            : null;

        return [
            'Height' => $row ? (string) ($row->Height ?? '') : '',
            'Weight' => $row ? (string) ($row->Weight ?? '') : '',
            'Glasses' => ($row && strtoupper(trim((string) ($row->Glasses ?? ''))) === 'YES') ? 'Yes' : 'No',
            'Smoker' => ($row && strtoupper(trim((string) ($row->Smoker ?? ''))) === 'YES') ? 'Yes' : 'No',
            'Longtime' => ($row && strtoupper(trim((string) ($row->Longtime ?? ''))) === 'YES') ? 'Yes' : 'No',
            'Longtime_Desc' => $row ? (string) ($row->Longtime_Desc ?? '') : '',
        ];
    }

    /**
     * Simpan Medical & Physical Information ke employment_detail berdasarkan Task_ID.
     */
    public function saveOtherMedicalInfo(string $taskId, array $input): void
    {
        $taskId = trim($taskId);
        if ($taskId === '') {
            throw new \InvalidArgumentException('Task_ID karyawan wajib disertakan.');
        }

        $longtime = (strtoupper(trim((string) ($input['Longtime'] ?? 'No'))) === 'YES') ? 'Yes' : 'No';
        $longtimeDesc = $longtime === 'Yes' ? trim((string) ($input['Longtime_Desc'] ?? '')) : '';

        $payload = [
            'Height' => trim((string) ($input['Height'] ?? '')),
            'Weight' => trim((string) ($input['Weight'] ?? '')),
            'Glasses' => (strtoupper(trim((string) ($input['Glasses'] ?? 'No'))) === 'YES') ? 'Yes' : 'No',
            'Smoker' => (strtoupper(trim((string) ($input['Smoker'] ?? 'No'))) === 'YES') ? 'Yes' : 'No',
            'Longtime' => $longtime,
            'Longtime_Desc' => $longtimeDesc,
        ];

        $existing = DB::table('employment_detail')->where('Task_ID', $taskId)->first();
        if ($existing) {
            DB::table('employment_detail')->where('ID', $existing->ID)->update($payload);
        } else {
            $payload['Task_ID'] = $taskId;
            DB::table('employment_detail')->insert($payload);
        }
    }

    /**
     * Proses dhtmlXDataProcessor XML untuk update/insert/delete employment_driving secara aman.
     */
    public function processDrivingDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_driving', [
            1 => 'Description',
            2 => 'Tipe',
            3 => 'Note',
        ]);
    }

    /**
     * Proses dhtmlXDataProcessor XML untuk update/insert/delete employment_health secara aman.
     */
    public function processHealthDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_health', [
            1 => 'Description',
            2 => 'Disease',
            3 => 'Year_Health',
            4 => 'Days',
        ]);
    }

    public function processFamilyDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_family', [
            1 => 'Family_Status',
            2 => 'Name',
            3 => 'Gender',
            4 => 'Birth_Date',
            5 => 'Last_Education',
            6 => 'Occupation',
        ]);
    }

    public function processContactPointsDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_contact_point', [
            1 => 'Name',
            2 => 'Employee_Department',
            3 => 'Position',
            4 => 'Contact_No',
            5 => 'Email',
            6 => 'Phone_No',
        ]);
    }

    public function processInsuranceDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_insurances', [
            1 => 'Employee_Name',
            2 => 'On_Behalf',
            3 => 'On_Behalf_Status',
            4 => 'Insurances',
            5 => 'Card_No',
            6 => 'Polis_No',
            7 => 'In_Salary',
            8 => 'Polis_Type',
            9 => 'Polis_Benefit',
            10 => 'Polis_Reimbursement',
            11 => 'Polis_Status',
            12 => 'Date_Start',
            13 => 'Date_End',
            14 => 'Date_Submission',
            15 => 'Employee_Collateral',
            16 => 'Salary_Component',
        ]);
    }

    public function processOrganizationDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_organization', [
            1 => 'Organization',
            2 => 'Position',
            3 => 'Location',
            4 => 'At_Year',
            5 => 'Note',
        ]);
    }

    public function processAcademicDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_akademic', [
            1 => 'Education_Level',
            2 => 'College',
            3 => 'Education_Major',
            4 => 'Location',
            5 => 'Certified_Year',
            6 => 'GPA',
        ]);
    }

    public function processInformalDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_informal', [
            1 => 'Training_Company',
            2 => 'Location',
            3 => 'Year_Training',
            4 => 'Note',
        ]);
    }

    public function processExperienceDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_experiences', [
            1 => 'Start_Date',
            2 => 'End_Date',
            3 => 'Position',
            4 => 'Company_Name',
            5 => 'Last_Salary',
            6 => 'Reason',
        ]);
    }

    public function processOtherSkillDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_other_skill', [
            1 => 'Category',
            2 => 'Subject',
            3 => 'Year_Skill',
            4 => 'Competences',
        ]);
    }

    public function processAwardDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_awards', [
            1 => 'MonthYear',
            2 => 'Place',
            3 => 'Detail',
            4 => 'Title',
        ]);
    }

    public function processLanguageDataProcessor(string $rawBody, string $taskId = ''): string
    {
        return $this->processDataProcessor($rawBody, $taskId, 'employment_language', [
            1 => 'Language',
            2 => 'Oral',
            3 => 'Writing',
            4 => 'Additional',
        ]);
    }

    /**
     * Daftar kategori dokumen karyawan beserta nama kolom & folder penyimpanan di public/doc_recruitment/.
     */
    public function getDocumentCategoryMap(): array
    {
        return [
            'PHOTO' => ['column' => 'Foto', 'folder' => 'photo'],
            'CV' => ['column' => 'CV', 'folder' => 'cv'],
            'IJAZAH' => ['column' => 'Ijazah', 'folder' => 'ijazah'],
            'SERTIFIKAT' => ['column' => 'Sertifikat', 'folder' => 'sertifikat'],
            'KTP' => ['column' => 'KTP', 'folder' => 'ktp'],
            'NPWP' => ['column' => 'NPWP', 'folder' => 'npwp'],
            'KK' => ['column' => 'KK', 'folder' => 'kk'],
            'Akta' => ['column' => 'Akta', 'folder' => 'akta'],
            'SIM' => ['column' => 'SIM', 'folder' => 'sim'],
        ];
    }

    /**
     * Upload foto profil karyawan ke public/doc_recruitment/photo/ dan perbarui employment & employment_document.
     */
    public function uploadEmployeePhoto(string $taskId, UploadedFile $file, string $username): string
    {
        $targetDir = public_path('doc_recruitment/photo');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', $taskId);
        $filename = $safeBase . '_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $file->move($targetDir, $filename);

        DB::transaction(function () use ($taskId, $filename, $username) {
            DB::table('employment')
                ->where('Task_ID', $taskId)
                ->update(['Foto' => $filename]);

            $existing = DB::table('employment_document')->where('Task_ID', $taskId)->first();
            $payload = [
                'Foto' => $filename,
                'Last_Updated' => date('Y-m-d H:i:s'),
                'Created_By' => $username,
            ];

            if ($existing) {
                DB::table('employment_document')->where('Task_ID', $taskId)->update($payload);
            } else {
                DB::table('employment_document')->insert($this->buildDocumentInsertPayload($taskId, $payload));
            }
        });

        return $filename;
    }

    /**
     * Upload dokumen karyawan ke public/doc_recruitment/{folder}/ dan simpan ke employment_document.
     */
    public function uploadEmployeeDocument(string $taskId, string $category, UploadedFile $file, string $description, string $username): array
    {
        $map = $this->getDocumentCategoryMap();
        if (!isset($map[$category])) {
            throw new \InvalidArgumentException('Kategori dokumen tidak valid.');
        }

        $col = $map[$category]['column'];
        $descCol = $col . '_Desc';
        $folder = $map[$category]['folder'];

        $targetDir = public_path('doc_recruitment/' . $folder);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', $taskId . '_' . $col);
        $filename = $safeBase . '_' . date('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $file->move($targetDir, $filename);

        DB::transaction(function () use ($taskId, $col, $descCol, $filename, $description, $username) {
            if ($col === 'Foto') {
                DB::table('employment')->where('Task_ID', $taskId)->update(['Foto' => $filename]);
            }

            $existing = DB::table('employment_document')->where('Task_ID', $taskId)->first();
            $payload = [
                $col => $filename,
                $descCol => $description,
                'Last_Updated' => date('Y-m-d H:i:s'),
                'Created_By' => $username,
            ];

            if ($existing) {
                DB::table('employment_document')->where('Task_ID', $taskId)->update($payload);
            } else {
                DB::table('employment_document')->insert($this->buildDocumentInsertPayload($taskId, $payload));
            }
        });

        return [
            'category' => $category,
            'filename' => $filename,
            'path' => $folder . '/' . $filename,
        ];
    }

    private function buildDocumentInsertPayload(string $taskId, array $overrides): array
    {
        $base = [
            'Task_ID' => $taskId,
            'Foto_Desc' => '',
            'Foto' => '',
            'CV_Desc' => '',
            'CV' => '',
            'Ijazah_Desc' => '',
            'Ijazah' => '',
            'Sertifikat_Desc' => '',
            'Sertifikat' => '',
            'KTP_Desc' => '',
            'KTP' => '',
            'NPWP_Desc' => '',
            'NPWP' => '',
            'KK_Desc' => '',
            'KK' => '',
            'Akta_Desc' => '',
            'Akta' => '',
            'SIM_Desc' => '',
            'SIM' => '',
            'Created_By' => '',
            'Last_Updated' => date('Y-m-d H:i:s'),
        ];

        $cols = array_flip(Schema::getColumnListing('employment_document'));
        return array_intersect_key(array_merge($base, $overrides), $cols);
    }

    /**
     * Hapus referensi dokumen karyawan pada employment_document berdasarkan kategori.
     */
    public function deleteEmployeeDocument(string $taskId, string $category, string $username): void
    {
        $map = $this->getDocumentCategoryMap();
        if (!isset($map[$category])) {
            throw new \InvalidArgumentException('Kategori dokumen tidak valid.');
        }

        $col = $map[$category]['column'];
        $descCol = $col . '_Desc';

        DB::table('employment_document')
            ->where('Task_ID', $taskId)
            ->update([
                $col => '',
                $descCol => '',
                'Last_Updated' => date('Y-m-d H:i:s'),
                'Created_By' => $username,
            ]);
    }

    private function processDataProcessor(string $rawBody, string $taskId, string $table, array $columnMap): string
    {
        $decoded = urldecode($rawBody);
        $param = [];
        foreach (explode('&', $decoded) as $pair) {
            if ($pair === '' || strpos($pair, '=') === false) {
                continue;
            }
            list($k, $v) = explode('=', $pair, 2);
            $param[$k] = $v;
        }

        $effectiveTaskId = trim($taskId !== '' ? $taskId : (string) ($param['Task_ID'] ?? ''));
        $ids = array_values(array_filter(explode(',', (string) ($param['ids'] ?? '')), function ($v) {
            return trim($v) !== '';
        }));

        $columnMeta = [];
        foreach (DB::select("SHOW COLUMNS FROM `{$table}`") as $col) {
            $columnMeta[$col->Field] = $col;
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<data>";
        foreach ($ids as $rowId) {
            $mode = (string) ($param[$rowId . '_!nativeeditor_status'] ?? '');
            $rowData = [];
            foreach ($columnMap as $colIdx => $dbCol) {
                if (!isset($columnMeta[$dbCol])) {
                    continue;
                }
                $val = (string) ($param[$rowId . '_c' . $colIdx] ?? '');
                $rowData[$dbCol] = $this->coerceColumnValue($val, $columnMeta[$dbCol]);
            }

            $newId = $rowId;
            $action = $mode;

            if ($mode === 'updated') {
                if (!empty($rowData)) {
                    $q = DB::table($table)->where('ID', (int) $rowId);
                    if ($effectiveTaskId !== '' && isset($columnMeta['Task_ID'])) {
                        $q->where('Task_ID', $effectiveTaskId);
                    }
                    $q->update($rowData);
                }
                $action = 'update';
            } elseif ($mode === 'deleted') {
                $q = DB::table($table)->where('ID', (int) $rowId);
                if ($effectiveTaskId !== '' && isset($columnMeta['Task_ID'])) {
                    $q->where('Task_ID', $effectiveTaskId);
                }
                $q->delete();
                $action = 'delete';
            } elseif ($mode === 'inserted') {
                $insertData = [];
                foreach ($columnMeta as $field => $meta) {
                    if (strpos((string) $meta->Extra, 'auto_increment') !== false) {
                        continue;
                    }
                    if ($meta->Null === 'NO' && $meta->Default === null) {
                        $insertData[$field] = $this->defaultValueForColumn($meta);
                    }
                }
                if (isset($columnMeta['Created_By'])) {
                    $insertData['Created_By'] = (string) (Auth::user()->username ?? 'SYSTEM');
                }
                if ($effectiveTaskId !== '' && isset($columnMeta['Task_ID'])) {
                    $insertData['Task_ID'] = $effectiveTaskId;
                }
                $insertData = array_merge($insertData, $rowData);
                $newId = (string) DB::table($table)->insertGetId($insertData);
                $action = 'insert';
            }

            $xml .= "<action type=\"" . $this->esc($action) . "\" sid=\"" . $this->esc($rowId) . "\" tid=\"" . $this->esc($newId) . "\"/>";
        }
        $xml .= '</data>';

        return $xml;
    }

    private function coerceColumnValue(string $val, object $meta)
    {
        $type = strtolower((string) $meta->Type);
        $trimmed = trim($val);
        if (strpos($type, 'date') === 0 || strpos($type, 'datetime') === 0 || strpos($type, 'timestamp') === 0) {
            if ($trimmed === '') {
                return $meta->Null === 'YES' ? null : ($type === 'date' ? '1970-01-01' : date('Y-m-d H:i:s'));
            }
            return $trimmed;
        }
        if (preg_match('/^(tinyint|smallint|mediumint|int|bigint|decimal|float|double)/', $type)) {
            if ($trimmed === '' || !is_numeric($trimmed)) {
                return $meta->Null === 'YES' ? null : 0;
            }
            return strpos($type, 'int') !== false ? (int) $trimmed : (float) $trimmed;
        }
        return $val;
    }

    private function defaultValueForColumn(object $meta)
    {
        $type = strtolower((string) $meta->Type);
        if ($type === 'date') {
            return '1970-01-01';
        }
        if (strpos($type, 'datetime') === 0 || strpos($type, 'timestamp') === 0) {
            return date('Y-m-d H:i:s');
        }
        if (preg_match('/^(tinyint|smallint|mediumint|int|bigint|decimal|float|double)/', $type)) {
            return 0;
        }
        return '';
    }

    private function buildSimpleTableXml($rows, array $columns): string
    {
        $xml = $this->xmlStart();
        $nomor = 1;
        foreach ($rows as $fetch) {
            $rowId = $this->esc((string) ($fetch->ID ?? $nomor));
            $xml .= "<row id=\"{$rowId}\">";
            $xml .= "<cell>{$nomor}</cell>";
            foreach ($columns as $col) {
                $xml .= '<cell>' . $this->esc((string) ($fetch->$col ?? '')) . '</cell>';
            }
            $xml .= '</row>';
            $nomor++;
        }
        $xml .= '</rows>';

        return $xml;
    }

    private function xmlStart(): string
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows>";
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
