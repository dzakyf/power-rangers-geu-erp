<?php

namespace App\Services\HCGA;

use App\Role;
use App\Services\RbacService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RecruitmentOnboardingService
{
    /**
     * Generate Nomor Induk Karyawan (NIK) otomatis jika belum diisi manual.
     * Format default: {Tahun:4}{Bulan:2}{Urut:4} misal: 2026110001
     *
     * @param string|null $startDate
     * @return string
     */
    public function generateNik($startDate = null): string
    {
        $date = !empty($startDate) ? Carbon::parse($startDate) : Carbon::now();
        $prefix = $date->format('Ym'); // e.g. 202611

        // Cari NIK terakhir dengan prefix yang sama di tabel employment
        $lastEmp = DB::table('employment')
            ->where('NIK', 'LIKE', "{$prefix}%")
            ->orderBy('NIK', 'DESC')
            ->first();

        $nextSeq = 1;
        if ($lastEmp && !empty($lastEmp->NIK)) {
            $seqStr = substr($lastEmp->NIK, strlen($prefix));
            if (is_numeric($seqStr)) {
                $nextSeq = (int) $seqStr + 1;
            }
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Resolusi hak akses default (Priv_ID & Spatie Role)
     * Menggunakan hierarki:
     * 1. Posisi spesifik (POSITION) di privilege_access_mappings
     * 2. Direktorat (DIRECTORAT) di privilege_access_mappings
     * 3. Departemen (DEPARTMENT) di privilege_access_mappings
     * 4. Fallback universal: Priv_ID = 'STAFF', Role = 'STAFF'
     *
     * @param string|null $position
     * @param string|null $directorat
     * @param string|null $department
     * @return array
     */
    public function resolvePrivilegeAndRole($position = null, $directorat = null, $department = null): array
    {
        // 1. Cek rule berdasarkan Posisi (exact or like)
        if (!empty($position)) {
            $posRule = DB::table('privilege_access_mappings')
                ->where('mapping_type', 'POSITION')
                ->where('status', 'ACTIVE')
                ->where(function ($q) use ($position) {
                    $q->where('target_key', $position)
                      ->orWhereRaw('? LIKE CONCAT("%", target_key, "%")', [$position]);
                })
                ->orderByRaw('LENGTH(target_key) DESC')
                ->first();

            if ($posRule) {
                return [
                    'priv_id'          => $posRule->priv_id,
                    'default_role'     => $posRule->default_role ?: $posRule->priv_id,
                    'default_sub_menu' => $posRule->default_sub_menu,
                    'matched_by'       => 'POSITION',
                ];
            }
        }

        // 2. Cek rule berdasarkan Direktorat
        if (!empty($directorat)) {
            $dirRule = DB::table('privilege_access_mappings')
                ->where('mapping_type', 'DIRECTORAT')
                ->where('status', 'ACTIVE')
                ->where(function ($q) use ($directorat) {
                    $q->where('target_key', $directorat)
                      ->orWhereRaw('? LIKE CONCAT("%", target_key, "%")', [$directorat]);
                })
                ->first();

            if ($dirRule) {
                return [
                    'priv_id'          => $dirRule->priv_id,
                    'default_role'     => $dirRule->default_role ?: $dirRule->priv_id,
                    'default_sub_menu' => $dirRule->default_sub_menu,
                    'matched_by'       => 'DIRECTORAT',
                ];
            }
        }

        // 3. Cek rule berdasarkan Departemen
        if (!empty($department)) {
            $deptRule = DB::table('privilege_access_mappings')
                ->where('mapping_type', 'DEPARTMENT')
                ->where('status', 'ACTIVE')
                ->where(function ($q) use ($department) {
                    $q->where('target_key', $department)
                      ->orWhereRaw('? LIKE CONCAT("%", target_key, "%")', [$department]);
                })
                ->first();

            if ($deptRule) {
                return [
                    'priv_id'          => $deptRule->priv_id,
                    'default_role'     => $deptRule->default_role ?: $deptRule->priv_id,
                    'default_sub_menu' => $deptRule->default_sub_menu,
                    'matched_by'       => 'DEPARTMENT',
                ];
            }
        }

        // 4. Default fallback: STAFF
        return [
            'priv_id'          => 'STAFF',
            'default_role'     => 'STAFF',
            'default_sub_menu' => null,
            'matched_by'       => 'DEFAULT',
        ];
    }

    /**
     * Cari nama atasan langsung (Direct Supervisor) berdasarkan posisi kandidat.
     * Pencarian bertingkat:
     * 1. Cek di tabel master_position (berdasarkan Parent_Position_ID)
     * 2. Cek di tabel organization_structure (berdasarkan Reports_To_Position_ID)
     * 3. Cek pemegang posisi atasan di employment / users / org_structure
     *
     * @param string|null $position
     * @param string|null $department
     * @return array ['supervisor_title' => string, 'supervisor_name' => string|null, 'source' => string]
     */
    public function resolveDirectSupervisor($position = null, $department = null): array
    {
        $position = trim((string) $position);
        $department = trim((string) $department);

        if ($position === '') {
            return [
                'supervisor_title' => 'Head',
                'supervisor_name'  => null,
                'source'           => 'DEFAULT',
            ];
        }

        // 1. Cari data posisi kandidat di master_position
        // Cocokkan ke Initial, Position, atau ID jika integer
        $mpQuery = DB::table('master_position');
        if (is_numeric($position)) {
            $mpQuery->where('ID', (int) $position);
        } else {
            $mpQuery->where(function ($q) use ($position) {
                $q->where('Initial', $position)
                  ->orWhere('Position', $position)
                  ->orWhere('Initial_Code', $position);
            });
        }
        if ($department !== '') {
            $mpQuery->orderByRaw("CASE WHEN Department = ? THEN 0 ELSE 1 END", [$department]);
        }
        $mp = $mpQuery->orderBy('ID', 'ASC')->first();

        // 2. Jika ada Parent_Position_ID di master_position
        if ($mp && !empty($mp->Parent_Position_ID)) {
            $parentMp = DB::table('master_position')->where('ID', (int) $mp->Parent_Position_ID)->first();
            if ($parentMp) {
                $supervisorTitle = $parentMp->Position ?: ($parentMp->Initial ?: 'Head');
                
                // Cari orang yang menjabat (di employment atau users)
                $holder = DB::table('employment')
                    ->where(function ($q) use ($parentMp) {
                        $q->where('Position', $parentMp->Initial)
                          ->orWhere('Position', $parentMp->Position);
                    })
                    ->where('Status', 'ACTIVE')
                    ->when(!empty($parentMp->Department), function ($q) use ($parentMp) {
                        return $q->where('Department', $parentMp->Department);
                    })
                    ->first();

                if (!$holder) {
                    $holder = DB::table('organization_structure')
                        ->where(function ($q) use ($parentMp) {
                            $q->where('Position', $parentMp->Initial)
                              ->orWhere('Position', $parentMp->Position);
                        })
                        ->where('Name', 'not like', '%VACANT%')
                        ->first();
                }

                $holderName = $holder ? trim((string) $holder->Name) : null;
                $display = $supervisorTitle;
                // if ($holderName) {
                //     $display = "{$supervisorTitle} ({$holderName})";
                // }

                return [
                    'supervisor_title' => $supervisorTitle,
                    'supervisor_name'  => $holderName,
                    'display'          => $display,
                    'source'           => 'MASTER_POSITION',
                ];
            }
        }

        // 3. Cek di organization_structure
        $orgNode = DB::table('organization_structure')
            ->where(function ($q) use ($position) {
                $q->where('Position', $position)
                  ->orWhere('Position_Code', $position);
            })
            ->when($department !== '', function ($q) use ($department) {
                return $q->where('Department', $department);
            })
            ->orderBy('ID', 'ASC')
            ->first();

        if ($orgNode && !empty($orgNode->Reports_To_Position_ID)) {
            $parentOrg = DB::table('organization_structure')->where('ID', (int) $orgNode->Reports_To_Position_ID)->first();
            if ($parentOrg) {
                // Cari nama jabatan resmi di master_position jika ada
                $posTitle = $parentOrg->Position;
                if (!empty($parentOrg->Position_ID)) {
                    $refMp = DB::table('master_position')->where('ID', (int) $parentOrg->Position_ID)->first();
                    if ($refMp && !empty($refMp->Position)) {
                        $posTitle = $refMp->Position;
                    }
                }

                $holderName = ($parentOrg->Name && stripos($parentOrg->Name, 'VACANT') === false) ? trim((string) $parentOrg->Name) : null;
                $display = $posTitle;
                if ($holderName) {
                    $display = "{$posTitle} ({$holderName})";
                }

                return [
                    'supervisor_title' => $posTitle,
                    'supervisor_name'  => $holderName,
                    'display'          => $display,
                    'source'           => 'ORGANIZATION_STRUCTURE',
                ];
            }
        }

        // 4. Fallback: Cari Leader/Head/Manager di Departemen yang sama dari organization_structure
        if ($department !== '') {
            $deptHeadOrg = DB::table('organization_structure')
                ->where('Department', $department)
                ->whereIn('Level', [2, 3, 4])
                ->where('Name', 'not like', '%VACANT%')
                ->orderBy('Level', 'ASC')
                ->first();

            if ($deptHeadOrg) {
                $posTitle = $deptHeadOrg->Position;
                $holderName = trim((string) $deptHeadOrg->Name);
                $display = $posTitle . ($holderName ? " ({$holderName})" : '');

                return [
                    'supervisor_title' => $posTitle,
                    'supervisor_name'  => $holderName,
                    'display'          => $display,
                    'source'           => 'DEPARTMENT_HEAD',
                ];
            }
        }

        return [
            'supervisor_title' => 'Head',
            'supervisor_name'  => null,
            'display'          => 'Head',
            'source'           => 'DEFAULT',
        ];
    }

    /**
     * Resolusi deskripsi Hari dan Jam Kerja berdasarkan Requisition Work_Schedule (work_calendars).
     *
     * @param int|string|null $workScheduleId
     * @return array ['schedule_text' => string, 'calendar_name' => string|null, 'employee_type' => string|null]
     */
    public function resolveWorkingSchedule($workScheduleId = null): array
    {
        $defaultScheduleText = "Ketentuan hari dan jam sebagai berikut:\n" .
            "Karyawan akan bekerja 6 (enam) hari kerja dalam 1 (satu) minggu dengan sistem roster dan shift.\n" .
            "Waktu dan jadwal kerja Karyawan ditetapkan oleh Departemen masing-masing sesuai kebutuhan operasional Perusahaan.\n" .
            "Apabila dibutuhkan, bersedia untuk masuk kerja pada hari libur nasional sesuai dengan kebutuhan Perusahaan (karena nature bisnis & operasional perusahaan yang mengharuskan demikian).";

        if (empty($workScheduleId)) {
            return [
                'schedule_text' => $defaultScheduleText,
                'calendar_name' => null,
                'employee_type' => null,
                'source'        => 'DEFAULT',
            ];
        }

        $calendar = DB::table('work_calendars')->where('ID', $workScheduleId)->first();
        if (!$calendar) {
            return [
                'schedule_text' => $defaultScheduleText,
                'calendar_name' => null,
                'employee_type' => null,
                'source'        => 'DEFAULT',
            ];
        }

        $calName = $calendar->Name ?? 'Work Calendar';
        $empType = strtoupper(trim((string) ($calendar->Employee_Type ?? '')));
        $weeklyHours = $calendar->Weekly_Hours ?? null;

        // Cek detail hari kerja di work_calendar_details
        $workingDays = DB::table('work_calendar_details')
            ->where('Calendar_ID', $calendar->ID)
            ->where('Is_Working', 1)
            ->orderBy('ID', 'ASC')
            ->get();

        $dayIndo = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        if ($empType === 'OFFICE' && $workingDays->count() > 0) {
            $dayCount = $workingDays->count();
            $firstDay = $dayIndo[$workingDays->first()->Day_Of_Week] ?? $workingDays->first()->Day_Of_Week;
            $lastDay  = $dayIndo[$workingDays->last()->Day_Of_Week] ?? $workingDays->last()->Day_Of_Week;
            
            $firstStart = !empty($workingDays->first()->Default_Start) ? date('H:i', strtotime($workingDays->first()->Default_Start)) : '08:00';
            $firstEnd   = !empty($workingDays->first()->Default_End) ? date('H:i', strtotime($workingDays->first()->Default_End)) : '17:00';

            $hoursInfo = $weeklyHours ? " ({$weeklyHours} jam kerja per minggu)" : '';
            $dayRange = ($dayCount > 1 && $firstDay !== $lastDay) ? "{$firstDay} s/d {$lastDay}" : $firstDay;

            $text = "Ketentuan hari dan jam kerja ({$calName}):\n" .
                "Karyawan bekerja {$dayCount} hari kerja per minggu ({$dayRange}), pukul {$firstStart} - {$firstEnd} WIB{$hoursInfo}.\n" .
                "Waktu dan jadwal kerja Karyawan ditetapkan oleh Departemen masing-masing sesuai kebutuhan operasional Perusahaan.\n" .
                "Apabila dibutuhkan, bersedia untuk masuk kerja pada hari libur nasional sesuai dengan kebutuhan Perusahaan.";

            return [
                'schedule_text' => $text,
                'calendar_name' => $calName,
                'employee_type' => $empType,
                'source'        => 'WORK_CALENDAR_OFFICE',
            ];
        } elseif ($empType === 'SHIFT') {
            // Cek apakah ada jadwal shift di tabel shifts
            $shiftCount = DB::table('shifts')->where('Calendar_ID', $calendar->ID)->where('Is_Working', 1)->count();
            $daysCountStr = $shiftCount > 0 ? (string)$shiftCount : '6 (enam)';

            $text = "Ketentuan hari dan jam kerja ({$calName}):\n" .
                "Karyawan akan bekerja {$daysCountStr} hari kerja dalam 1 (satu) minggu dengan sistem roster dan shift.\n" .
                "Waktu dan jadwal shift kerja Karyawan ditetapkan oleh Departemen masing-masing sesuai kebutuhan operasional Perusahaan.\n" .
                "Apabila dibutuhkan, bersedia untuk masuk kerja pada hari libur nasional sesuai dengan kebutuhan Perusahaan (karena nature bisnis & operasional perusahaan yang mengharuskan demikian).";

            return [
                'schedule_text' => $text,
                'calendar_name' => $calName,
                'employee_type' => $empType,
                'source'        => 'WORK_CALENDAR_SHIFT',
            ];
        }

        // Jika kalender ada tapi tidak ada baris detail khusus
        $text = "Ketentuan hari dan jam kerja ({$calName}):\n" .
            "Waktu dan jadwal kerja Karyawan mengikuti kalender kerja '{$calName}' yang ditetapkan oleh Departemen terkait sesuai kebutuhan operasional Perusahaan.\n" .
            "Apabila dibutuhkan, bersedia untuk masuk kerja pada hari libur nasional sesuai dengan kebutuhan Perusahaan.";

        return [
            'schedule_text' => $text,
            'calendar_name' => $calName,
            'employee_type' => $empType,
            'source'        => 'WORK_CALENDAR_GENERAL',
        ];
    }

    /**
     * Memproses kandidat yang disetujui Offering-nya menjadi karyawan resmi:
     * 1. Menyiapkan NIK
     * 2. Insert ke tabel employment & employment_detail
     * 3. Insert / update ke tabel organization_structure
     * 4. Insert ke tabel users, assign Role Spatie & rebuild Sub_Menu
     * 5. Update status di tabel recruitment menjadi HIRED dengan NIK & Onboarded_At
     *
     * @param int $recruitmentId
     * @param array $payload
     * @return array
     * @throws \Exception
     */
    public function processOnboardingToEmployee(int $recruitmentId, array $payload): array
    {
        return DB::transaction(function () use ($recruitmentId, $payload) {
            $currentUser = Auth::user()->username ?? 'SYSTEM';
            $recruitment = DB::table('recruitment')->where('ID', $recruitmentId)->first();

            if (!$recruitment) {
                throw new \Exception('Data kandidat recruitment tidak ditemukan.');
            }

            // Ambil data Requisition terkait jika ada
            $requisition = null;
            if (!empty($recruitment->Requisition_ID)) {
                $requisition = DB::table('requisition')->where('Task_ID', $recruitment->Requisition_ID)->first();
            }

            // Parameter Kompensasi & Penugasan
            $position   = trim((string) ($payload['position'] ?? ($requisition->Job_Position ?? ($recruitment->Position ?? '-'))));
            $salary     = (float) ($payload['salary'] ?? ($recruitment->Salary_Accepted ?? 0));
            $startDate  = !empty($payload['start_date']) ? $payload['start_date'] : ($recruitment->Start_Date ?? date('Y-m-d'));
            $note       = $payload['note'] ?? ($recruitment->Offering_Note ?? null);
            $department = $payload['department'] ?? ($requisition->Department ?? ($recruitment->Department ?? 'HCGA'));
            $directorat = $payload['directorat'] ?? ($requisition->Directorat ?? ($recruitment->Directorat ?? null));
            $location   = $payload['location'] ?? ($recruitment->Location ?? ($requisition->Location ?? 'GEU-HO'));

            // 1. Tentukan NIK
            $nik = !empty($payload['nik']) ? trim($payload['nik']) : ($recruitment->NIK ?? null);
            if (empty($nik)) {
                $nik = $this->generateNik($startDate);
            }

            // 2. Resolusi Priv_ID dan Role
            $privOverride = !empty($payload['priv_id']) ? trim($payload['priv_id']) : null;
            $roleOverride = !empty($payload['role']) ? trim($payload['role']) : null;

            if ($privOverride) {
                $resolved = [
                    'priv_id'          => $privOverride,
                    'default_role'     => $roleOverride ?: $privOverride,
                    'default_sub_menu' => null,
                    'matched_by'       => 'MANUAL',
                ];
            } else {
                $resolved = $this->resolvePrivilegeAndRole($position, $directorat, $department);
                if ($roleOverride) {
                    $resolved['default_role'] = $roleOverride;
                }
            }

            // 3. Masukkan ke Tabel employment
            $existingEmp = DB::table('employment')->where('NIK', $nik)->first();
            if (!$existingEmp && !empty($recruitment->Task_ID)) {
                $existingEmp = DB::table('employment')->where('Task_ID', $recruitment->Task_ID)->first();
            }

            $empData = [
                'NIK'                    => $nik,
                'Task_ID'                => $recruitment->Task_ID ?: $nik,
                'Name'                   => $recruitment->Name,
                'Gender'                 => $recruitment->Gender ?? 'MALE',
                'Birth_Place'            => $recruitment->Birth_Place ?? null,
                'Birth_Date'             => $recruitment->Birth_Date ?? null,
                'KTP'                    => $recruitment->KTP ?? null,
                'Address'                => $recruitment->Address ?? null,
                'City'                   => $recruitment->City ?? null,
                'District'               => $recruitment->District ?? null,
                'Sub_District'           => $recruitment->Sub_District ?? null,
                'Province'               => $recruitment->Province ?? null,
                'ZipCode'                => $recruitment->ZipCode ?? null,
                'Phone'                  => $recruitment->Phone ?? null,
                'Handphone'              => $recruitment->Handphone ?? null,
                'Email'                  => $recruitment->Email ?? null,
                'Religion'               => $recruitment->Religion ?? null,
                'Marital_Status'         => $recruitment->Marital_Status ?? 'SINGLE',
                'Last_Education'         => $recruitment->Last_Education ?? null,
                'Last_Education_From'    => $recruitment->Last_Education_From ?? null,
                'Education_Major'        => $recruitment->Education_Major ?? ($recruitment->Major ?? null),
                'kewarganegaraan'        => $recruitment->kewarganegaraan ?? 'INDONESIA',
                'Foto'                   => $recruitment->Foto ?? null,
                'Department'             => $department,
                'Directorat'             => $directorat,
                'Location'               => $location,
                'Position'               => $position,
                'Salary_Accepted'        => $salary,
                'Start_Date'             => $startDate,
                'Date_in'                => $startDate,
                'Employment_Status'      => 'ACTIVE',
                'Employment_Status_Date' => $startDate,
                'Status'                 => 'ACTIVE',
                'Category'               => 'OFFICE',
                'Recruitment_ID'         => $recruitment->ID,
                'Recruitment_Status'     => 'HIRED',
                'Notes'                  => $note,
                'Created_By'             => $currentUser,
                'Created_Date'           => now(),
            ];

            // Filter kolom yang benar-benar ada di tabel employment
            $employmentCols = Schema::getColumnListing('employment');
            $filteredEmpData = array_intersect_key($empData, array_flip($employmentCols));

            if ($existingEmp) {
                DB::table('employment')->where('ID', $existingEmp->ID)->update($filteredEmpData);
                $employmentId = $existingEmp->ID;
            } else {
                $employmentId = DB::table('employment')->insertGetId($filteredEmpData);
            }

            // 4. Masukkan ke Tabel employment_detail jika belum ada
            if (Schema::hasTable('employment_detail')) {
                $existingEmpDtl = DB::table('employment_detail')
                    ->where('Task_ID', $recruitment->Task_ID ?: $nik)
                    ->first();

                if (!$existingEmpDtl) {
                    $dtlData = [
                        'Task_ID'      => $recruitment->Task_ID ?: $nik,
                        'Created_By'   => $currentUser,
                        'Created_Date' => now(),
                    ];
                    $empDtlCols = Schema::getColumnListing('employment_detail');
                    DB::table('employment_detail')->insert(array_intersect_key($dtlData, array_flip($empDtlCols)));
                }
            }

            // 4b. Arsipkan Berkas Offering Letter ke employment_document
            $signedOfferingFile = $payload['offering_file'] ?? ($recruitment->Offering_File ?? null);
            if ($signedOfferingFile && Schema::hasTable('employment_document')) {
                $existingDoc = DB::table('employment_document')
                    ->where('Task_ID', $recruitment->Task_ID ?: $nik)
                    ->first();

                $docData = [
                    'Task_ID'               => $recruitment->Task_ID ?: $nik,
                    'Offering_Letter'       => $signedOfferingFile,
                    'Offering_Letter_Desc'  => 'Offering Letter Bertandatangan (' . ($payload['offering_number'] ?? 'OFFERING') . ')',
                    'Created_By'            => $currentUser,
                    'Last_Updated'          => now(),
                ];

                $docCols = Schema::getColumnListing('employment_document');
                $filteredDocData = array_intersect_key($docData, array_flip($docCols));

                if ($existingDoc) {
                    DB::table('employment_document')
                        ->where('ID', $existingDoc->ID)
                        ->update($filteredDocData);
                } else {
                    DB::table('employment_document')->insert($filteredDocData);
                }
            }

            // 5. Tautkan ke Tabel organization_structure
            if (Schema::hasTable('organization_structure')) {
                $existingOrg = DB::table('organization_structure')->where('NIK', $nik)->first();

                // Cari apakah ada parent node atau vacant node untuk posisi yang sama
                $reportsTo = $payload['reports_to_position_id'] ?? null;
                $level     = $payload['level'] ?? 5; // Level default 5 (Staff)

                if (empty($reportsTo)) {
                    // Cari atasan di departemen yang sama (misal Level 3 / Manager)
                    $headOrg = DB::table('organization_structure')
                        ->where('Department', $department)
                        ->whereIn('Level', [2, 3, 4])
                        ->orderBy('Level', 'ASC')
                        ->first();
                    if ($headOrg) {
                        $reportsTo = $headOrg->ID;
                    }
                }

                $orgData = [
                    'NIK'                     => $nik,
                    'Name'                    => $recruitment->Name,
                    'Position'                => $position,
                    'Department'              => $department,
                    'Directorat'              => $directorat,
                    'Email'                   => $recruitment->Email ?? null,
                    'Level'                   => $level,
                    'Reports_To_Position_ID'  => $reportsTo,
                ];

                $orgCols = Schema::getColumnListing('organization_structure');
                $filteredOrgData = array_intersect_key($orgData, array_flip($orgCols));

                if ($existingOrg) {
                    DB::table('organization_structure')->where('ID', $existingOrg->ID)->update($filteredOrgData);
                } else {
                    DB::table('organization_structure')->insert($filteredOrgData);
                }
            }

            // 6. Buat Akun Karyawan di Tabel users & Assign RBAC Role
            $username = $nik;
            $user = User::where('username', $username)
                ->orWhere('Emp_ID', $nik)
                ->first();

            $privIdToSet = $resolved['priv_id'];
            $roleToSet   = $resolved['default_role'];

            if (!$user) {
                $user = User::create([
                    'username' => $username,
                    'Emp_ID'   => $nik,
                    'Name'     => $recruitment->Name,
                    'email'    => $recruitment->Email ?: ($nik . '@company.local'),
                    'password' => Hash::make('12345678'), // Default password
                    'Location' => $location,
                    'Position' => $position,
                    'Priv_ID'  => $privIdToSet,
                    'Status'   => 'ACTIVE',
                    'Sub_Menu' => $resolved['default_sub_menu'] ?? '',
                ]);
            } else {
                $user->update([
                    'Emp_ID'   => $nik,
                    'Name'     => $recruitment->Name,
                    'Location' => $location,
                    'Position' => $position,
                    'Priv_ID'  => $privIdToSet,
                    'Status'   => 'ACTIVE',
                ]);
            }

            // Assign Spatie Role jika ada
            if ($user && !empty($roleToSet)) {
                $spatieRole = Role::where('name', $roleToSet)->first();
                if (!$spatieRole) {
                    // Buat role jika belum terdaftar
                    $spatieRole = Role::create(['name' => $roleToSet, 'guard_name' => 'web']);
                }

                if ($spatieRole) {
                    $user->syncRoles([$spatieRole]);
                }
            }

            // Rebuild Sub_Menu string agar navigation menu langsung aktif
            if ($user && class_exists(RbacService::class)) {
                RbacService::rebuildSubMenu($user);
            }

            // 7. Update status pada tabel recruitment
            $offeringNumber = $payload['offering_number'] ?? null;
            $recUpdateData = [
                'Status'                 => 'HIRED',
                'Registration_Status'    => 'HIRED',
                'Offering_Status'        => 'ACCEPT',
                'Offering_Number'        => $offeringNumber,
                'Offering_File'          => $signedOfferingFile,
                'Offering_Signed_Date'   => now(),
                'Salary_Accepted'        => $salary,
                'Salary_Base'            => (float) ($payload['salary_base'] ?? $salary),
                'Salary_Cola'            => (float) ($payload['salary_cola'] ?? 0),
                'Salary_Allowance'       => (float) ($payload['salary_allowance'] ?? 0),
                'Grading'                => $payload['grading'] ?? null,
                'Work_Location'          => $payload['work_location'] ?? $location,
                'Contract_Type'          => $payload['contract_type'] ?? null,
                'Direct_Supervisor'      => $payload['direct_supervisor'] ?? null,
                'Working_Schedule'       => $payload['working_schedule'] ?? null,
                'Benefit_Description'    => $payload['benefit_description'] ?? null,
                'Offering_Terms'         => $payload['offering_terms'] ?? null,
                'Position'               => $position,
                'Start_Date'             => $startDate,
                'Offering_Note'          => $note,
                'NIK'                    => $nik,
                'Onboarded_At'           => now(),
                'Modified_By'            => $currentUser,
                'Modified_Date'          => now(),
            ];

            $recCols = Schema::getColumnListing('recruitment');
            DB::table('recruitment')->where('ID', $recruitmentId)->update(
                array_intersect_key($recUpdateData, array_flip($recCols))
            );

            // 8. Kirim Notifikasi Sistem & Welcome Log
            try {
                if (class_exists(\App\Services\NotificationService::class)) {
                    $notifService = app(\App\Services\NotificationService::class);
                    $notifTitle = "Selamat Datang Karyawan Baru: {$recruitment->Name} ({$nik})";
                    $notifContent = "Karyawan baru {$recruitment->Name} telah resmi berstatus HIRED pada posisi {$position} ({$department}). Akun ERP dan struktur organisasi telah diaktifkan.";
                    $notifService->makeNotification(
                        $notifTitle,
                        $notifContent,
                        'RECRUITMENT_ONBOARDING',
                        $currentUser,
                        null, // all HCGA / General
                        'SENT',
                        json_encode([
                            'recruitment_id'  => $recruitmentId,
                            'nik'             => $nik,
                            'position'        => $position,
                            'department'      => $department,
                            'offering_number' => $offeringNumber,
                        ])
                    );
                }
            } catch (\Exception $eNotif) {
                \Illuminate\Support\Facades\Log::warning("Gagal mengirim notifikasi onboarding karyawan {$nik}: " . $eNotif->getMessage());
            }

            return [
                'success'         => true,
                'recruitment_id'  => $recruitmentId,
                'nik'             => $nik,
                'username'        => $username,
                'name'            => $recruitment->Name,
                'position'        => $position,
                'department'      => $department,
                'priv_id'         => $privIdToSet,
                'role'            => $roleToSet,
                'offering_number' => $offeringNumber,
                'employment_id'   => $employmentId,
            ];
        });
    }
}

