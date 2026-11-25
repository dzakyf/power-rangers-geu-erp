<?php

namespace App\Services\HCGA;

use App\Role;
use App\Services\Rbac\RoleResolver;
use App\Services\RbacService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class EmployeeUserAccountService
{
    /** @var RecruitmentOnboardingService */
    private $onboardingService;

    /** @var RoleResolver */
    private $roleResolver;

    public function __construct(RecruitmentOnboardingService $onboardingService, RoleResolver $roleResolver)
    {
        $this->onboardingService = $onboardingService;
        $this->roleResolver = $roleResolver;
    }

    /**
     * Verifikasi apakah user login berwenang mengelola akun karyawan pada menu Employee Lists.
     *
     * @param mixed $user
     * @return bool
     */
    public function canManageEmployeeAccounts($user): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Otorisasi utama mengikuti matriks menu Employee Lists (SI00101) pada /credential-role
        if (method_exists($user, 'can') && ($user->can('menu.SI00101.create') || $user->can('menu.SI00101.edit'))) {
            return true;
        }

        if (function_exists('can_menu') && auth()->check() && (int) auth()->id() === (int) ($user->id ?? 0)) {
            if (can_menu('SI00101', 'create') || can_menu('SI00101', 'edit')) {
                return true;
            }
        }

        // 2. Bypass khusus Administrator sistem agar admin utama tidak pernah terkunci
        $username = strtolower(trim((string) ($user->username ?? '')));
        if ($username === 'admin') {
            return true;
        }

        $privId = strtoupper(trim((string) ($user->Priv_ID ?? '')));
        if ($privId === 'ADM') {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super-admin', 'Admin', 'ADM'])) {
            return true;
        }

        return false;
    }

    /**
     * Memuat dan menganalisis daftar karyawan untuk modal Buat / Hubungkan Akun User.
     *
     * @param array $employeeKeys
     * @return array
     */
    public function inspectEmployeesForProvisioning(array $employeeKeys): array
    {
        $keys = array_values(array_unique(array_filter(array_map('trim', array_map('strval', $employeeKeys)), function ($v) {
            return $v !== '';
        })));

        if (empty($keys)) {
            return [];
        }

        $employees = DB::table('employment')
            ->where(function ($q) use ($keys) {
                $q->whereIn('Task_ID', $keys)->orWhereIn('ID', $keys);
            })
            ->orderBy('Name', 'ASC')
            ->get();

        $results = [];
        foreach ($employees as $emp) {
            $activeUser = $this->findActiveEmployeeUser($emp);
            $candidateMatch = $activeUser ? null : $this->detectCandidateAccount($emp);

            $resolved = $this->onboardingService->resolvePrivilegeAndRole(
                $emp->Position ?? null,
                $emp->Directorat ?? null,
                $emp->Department ?? null
            );

            $roleModel = $this->resolveRoleModel(
                $resolved['default_role_id'] ?? null,
                $resolved['default_role'] ?? null,
                $emp->Department ?? null
            );

            $empNik = trim((string) ($emp->NIK ?: $emp->Task_ID ?: ''));
            $companyEmail = $this->extractValidEmail($emp->Email ?? null);
            $personalEmail = $this->extractValidEmail($emp->Email_Individual ?? null);

            $suggestedUsername = $companyEmail !== '' ? $companyEmail : $empNik;
            $statusCode = 'NO_ACCOUNT';
            if ($activeUser) {
                $statusCode = strtoupper(trim((string) ($activeUser->Status ?? 'ACTIVE'))) === 'ACTIVE'
                    ? 'HAS_EMPLOYEE_ACCOUNT'
                    : 'INACTIVE_EMPLOYEE_ACCOUNT';
            } elseif ($candidateMatch) {
                $statusCode = 'CANDIDATE_DETECTED';
            }

            $results[] = [
                'employee' => $emp,
                'employee_key' => $emp->Task_ID ?: (string) $emp->ID,
                'emp_nik' => $empNik,
                'company_email' => $companyEmail,
                'personal_email' => $personalEmail,
                'status_code' => $statusCode,
                'active_user' => $activeUser,
                'candidate_match' => $candidateMatch,
                'suggested_priv_id' => $resolved['priv_id'] ?? 'STAFF',
                'suggested_role_name' => $roleModel ? $roleModel->name : ($resolved['default_role'] ?? 'STAFF'),
                'suggested_role_id' => $roleModel ? (int) $roleModel->id : null,
                'suggested_username' => $suggestedUsername,
                'matched_by' => $resolved['matched_by'] ?? 'DEFAULT',
            ];
        }

        return $results;
    }

    /**
     * Pencarian akun kandidat (Position = 'CANDIDATE') untuk pemilihan manual di modal.
     *
     * @param string $keyword
     * @param int $limit
     * @return array
     */
    public function searchCandidateAccounts(string $keyword = '', int $limit = 20): array
    {
        $keyword = trim($keyword);
        $limit = max(1, min(50, $limit));

        $query = DB::table('users as u')
            ->leftJoin('recruitment as r', 'r.Task_ID', '=', 'u.username')
            ->whereRaw("UPPER(COALESCE(u.Position, '')) = 'CANDIDATE'")
            ->where('u.Status', 'ACTIVE');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('u.Name', 'like', "%{$keyword}%")
                  ->orWhere('u.username', 'like', "%{$keyword}%")
                  ->orWhere('u.email', 'like', "%{$keyword}%")
                  ->orWhere('u.Emp_ID', 'like', "%{$keyword}%")
                  ->orWhere('r.KTP', 'like', "%{$keyword}%")
                  ->orWhere('r.Position', 'like', "%{$keyword}%");
            });
        }

        $rows = $query
            ->select(
                'u.id',
                'u.username',
                'u.Emp_ID',
                'u.Name',
                'u.email',
                'u.Location',
                'r.ID as recruitment_id',
                'r.Position as applied_position',
                'r.KTP as ktp',
                'r.Status as recruitment_status'
            )
            ->orderBy('u.id', 'DESC')
            ->limit($limit)
            ->get();

        $results = [];
        foreach ($rows as $row) {
            $posText = $row->applied_position ? " | Melamar: {$row->applied_position}" : '';
            $emailText = $row->email ? " | {$row->email}" : '';
            $results[] = [
                'id' => (int) $row->id,
                'text' => "{$row->Name} ({$row->username}){$emailText}{$posText}",
                'username' => $row->username,
                'name' => $row->Name,
                'email' => $row->email,
                'applied_position' => $row->applied_position,
                'recruitment_id' => $row->recruitment_id ? (int) $row->recruitment_id : null,
            ];
        }

        return $results;
    }

    /**
     * Eksekusi pembuatan akun karyawan baru, upgrade akun kandidat, atau sinkronisasi akun eksisting.
     *
     * @param array $items
     * @param string $operator
     * @return array
     */
    public function processAccounts(array $items, string $operator): array
    {
        $created = 0;
        $upgraded = 0;
        $synced = 0;
        $skipped = 0;
        $processed = [];
        $errors = [];

        $hasUserIdCol = Schema::hasColumn('employment', 'user_id');
        $hasCandidateUserIdCol = Schema::hasColumn('employment', 'candidate_user_id');
        $hasSourceCol = Schema::hasColumn('employment', 'user_account_source');
        $hasLinkedByCol = Schema::hasColumn('employment', 'user_linked_by');
        $hasLinkedAtCol = Schema::hasColumn('employment', 'user_linked_at');

        DB::transaction(function () use (
            $items,
            $operator,
            &$created,
            &$upgraded,
            &$synced,
            &$skipped,
            &$processed,
            &$errors,
            $hasUserIdCol,
            $hasCandidateUserIdCol,
            $hasSourceCol,
            $hasLinkedByCol,
            $hasLinkedAtCol
        ) {
            foreach ($items as $idx => $item) {
                $empId = (int) ($item['employment_id'] ?? 0);
                $empKey = trim((string) ($item['employee_key'] ?? ''));

                if ($empId <= 0 && $empKey === '') {
                    continue;
                }

                $emp = DB::table('employment')
                    ->when($empId > 0, function ($q) use ($empId) {
                        return $q->where('ID', $empId);
                    }, function ($q) use ($empKey) {
                        return $q->where('Task_ID', $empKey);
                    })
                    ->first();

                if (!$emp) {
                    $errors[] = "Data karyawan (#{$idx}) tidak ditemukan.";
                    continue;
                }

                if (isset($item['selected']) && !$item['selected']) {
                    $skipped++;
                    continue;
                }

                $empNik = trim((string) ($emp->NIK ?: $emp->Task_ID ?: ''));
                if ($empNik === '') {
                    $errors[] = "{$emp->Name}: NIK dan Task_ID kosong sehingga Emp_ID tidak dapat ditentukan.";
                    continue;
                }

                $activeUser = $this->findActiveEmployeeUser($emp);
                $syncExisting = !empty($item['sync_existing']);

                if ($activeUser && !$syncExisting) {
                    // Backfill user_id bila belum tercatat di employment
                    if ($hasUserIdCol && empty($emp->user_id)) {
                        $backfill = ['user_id' => $activeUser->id];
                        if ($hasLinkedByCol && empty($emp->user_linked_by)) {
                            $backfill['user_linked_by'] = $operator;
                        }
                        if ($hasLinkedAtCol && empty($emp->user_linked_at)) {
                            $backfill['user_linked_at'] = Carbon::now();
                        }
                        DB::table('employment')->where('ID', $emp->ID)->update($backfill);
                    }
                    $skipped++;
                    continue;
                }

                // Resolusi Role & Priv_ID
                $defaultResolved = $this->onboardingService->resolvePrivilegeAndRole(
                    $emp->Position ?? null,
                    $emp->Directorat ?? null,
                    $emp->Department ?? null
                );

                $roleIdInput = !empty($item['role_id']) ? (int) $item['role_id'] : null;
                $roleNameInput = trim((string) ($item['role_name'] ?? ''));
                $privIdInput = trim((string) ($item['priv_id'] ?? ''));

                $resolvedRoleId = $roleIdInput ?: (!empty($defaultResolved['default_role_id']) && $roleNameInput === '' ? (int) $defaultResolved['default_role_id'] : null);
                $roleModel = $this->resolveRoleModel(
                    $resolvedRoleId,
                    $roleNameInput !== '' ? $roleNameInput : ($defaultResolved['default_role'] ?? null),
                    $emp->Department ?? null
                );

                $roleName = $roleModel
                    ? $roleModel->name
                    : ($roleNameInput !== '' ? $roleNameInput : ($defaultResolved['default_role'] ?? 'STAFF'));
                $isManualRoleOverride = ($roleIdInput && $roleIdInput !== ($defaultResolved['default_role_id'] ?? null)) || ($roleNameInput !== '' && $roleNameInput !== ($defaultResolved['default_role'] ?? ''));
                $privId = $privIdInput !== ''
                    ? $privIdInput
                    : ($isManualRoleOverride && $roleModel ? $roleModel->name : ($defaultResolved['priv_id'] ?? ($roleModel ? $roleModel->name : 'STAFF')));

                $companyEmail = $this->extractValidEmail($emp->Email ?? null);
                $fallbackEmail = $companyEmail !== '' ? $companyEmail : ($this->extractValidEmail($emp->Email_Individual ?? null) ?: ($empNik . '@company.local'));
                $location = trim((string) ($emp->Location ?: 'GEU-HO'));
                $department = trim((string) ($emp->Department ?: ($roleModel && $roleModel->department ? $roleModel->department : '')));

                // Mode 1: Sinkronisasi akun karyawan yang sudah aktif
                if ($activeUser && $syncExisting) {
                    $user = User::where('id', (int) $activeUser->id)->lockForUpdate()->first();
                    if ($user) {
                        $syncPayload = [
                            'Emp_ID' => $empNik,
                            'Name' => $emp->Name,
                            'Location' => $location,
                            'Position' => $roleName,
                            'Priv_ID' => $privId,
                            'Status' => 'ACTIVE',
                        ];
                        if ($department !== '' && Schema::hasColumn('users', 'Department')) {
                            $syncPayload['Department'] = $department;
                        }
                        $user->forceFill($syncPayload)->save();

                        $this->assignRoleAndRebuildMenu($user, $roleModel, $defaultResolved['default_sub_menu'] ?? null);
                        $this->updateEmploymentLinkage(
                            (int) $emp->ID,
                            (int) $user->id,
                            $emp->candidate_user_id ? (int) $emp->candidate_user_id : null,
                            $emp->user_account_source ?: 'SYNC_EXISTING',
                            $operator,
                            $hasUserIdCol,
                            $hasCandidateUserIdCol,
                            $hasSourceCol,
                            $hasLinkedByCol,
                            $hasLinkedAtCol
                        );

                        $synced++;
                        $processed[] = [
                            'employment_id' => (int) $emp->ID,
                            'name' => $emp->Name,
                            'user_id' => (int) $user->id,
                            'username' => $user->username,
                            'action' => 'SYNCED',
                        ];
                    }
                    continue;
                }

                // Mode 2: Cek apakah ada candidate_user_id yang dipilih manual atau terdeteksi
                $candidateUserId = !empty($item['candidate_user_id']) ? (int) $item['candidate_user_id'] : 0;
                $isManualCandidate = !empty($item['is_manual_candidate']);
                if ($candidateUserId <= 0 && empty($item['ignore_candidate_match'])) {
                    $autoMatch = $this->detectCandidateAccount($emp);
                    if ($autoMatch && !empty($autoMatch['user'])) {
                        $candidateUserId = (int) $autoMatch['user']->id;
                    }
                }

                if ($candidateUserId > 0) {
                    $candUser = User::where('id', $candidateUserId)->lockForUpdate()->first();
                    if (!$candUser) {
                        $errors[] = "{$emp->Name}: Akun kandidat (ID {$candidateUserId}) tidak ditemukan.";
                        continue;
                    }

                    if (strtoupper(trim((string) $candUser->Position)) !== 'CANDIDATE') {
                        $errors[] = "{$emp->Name}: Akun '{$candUser->username}' bukan akun CANDIDATE.";
                        continue;
                    }

                    if ($hasUserIdCol) {
                        $conflictEmp = DB::table('employment')
                            ->where('ID', '!=', $emp->ID)
                            ->where(function ($q) use ($candUser) {
                                $q->where('user_id', $candUser->id)
                                  ->orWhere('candidate_user_id', $candUser->id);
                            })
                            ->first();
                        if ($conflictEmp) {
                            $errors[] = "{$emp->Name}: Akun kandidat '{$candUser->username}' sudah tertaut ke karyawan lain ({$conflictEmp->Name}).";
                            continue;
                        }
                    }

                    $usernameMode = trim((string) ($item['username_mode'] ?? 'keep'));
                    $customUsername = trim((string) ($item['username'] ?? ''));
                    $targetUsername = $this->resolveTargetUsername(
                        $usernameMode,
                        $customUsername,
                        $candUser->username,
                        $companyEmail,
                        $empNik
                    );

                    if ($targetUsername === '') {
                        $errors[] = "{$emp->Name}: Username target tidak boleh kosong (email perusahaan belum diisi).";
                        continue;
                    }

                    $usernameTaken = DB::table('users')
                        ->where('username', $targetUsername)
                        ->where('id', '!=', $candUser->id)
                        ->exists();
                    if ($usernameTaken) {
                        $errors[] = "{$emp->Name}: Username '{$targetUsername}' sudah digunakan akun lain.";
                        continue;
                    }

                    $updatePayload = [
                        'username' => $targetUsername,
                        'Emp_ID' => $empNik,
                        'Name' => $emp->Name,
                        'email' => $companyEmail !== '' ? $companyEmail : ($candUser->email ?: $fallbackEmail),
                        'Location' => $location,
                        'Position' => $roleName,
                        'Priv_ID' => $privId,
                        'Status' => 'ACTIVE',
                    ];
                    if ($department !== '' && Schema::hasColumn('users', 'Department')) {
                        $updatePayload['Department'] = $department;
                    }

                    // Password kandidat dipertahankan kecuali di-reset eksplisit
                    if (!empty($item['reset_password'])) {
                        $newPass = trim((string) ($item['password'] ?? ''));
                        $updatePayload['password'] = Hash::make($newPass !== '' ? $newPass : '12345678');
                    }

                    $candUser->forceFill($updatePayload)->save();

                    $this->assignRoleAndRebuildMenu($candUser, $roleModel, $defaultResolved['default_sub_menu'] ?? null);

                    $sourceCode = $isManualCandidate ? 'MANUAL_LINK' : 'CANDIDATE_UPGRADE';
                    $this->updateEmploymentLinkage(
                        (int) $emp->ID,
                        (int) $candUser->id,
                        (int) $candUser->id,
                        $sourceCode,
                        $operator,
                        $hasUserIdCol,
                        $hasCandidateUserIdCol,
                        $hasSourceCol,
                        $hasLinkedByCol,
                        $hasLinkedAtCol
                    );

                    $this->syncRecruitmentRecordIfAny($emp, $empNik, $candUser->username, $operator);

                    $upgraded++;
                    $processed[] = [
                        'employment_id' => (int) $emp->ID,
                        'name' => $emp->Name,
                        'user_id' => (int) $candUser->id,
                        'username' => $targetUsername,
                        'action' => $sourceCode,
                    ];
                    continue;
                }

                // Mode 3: Buat akun karyawan baru
                $usernameMode = trim((string) ($item['username_mode'] ?? 'company_email'));
                $customUsername = trim((string) ($item['username'] ?? ''));
                $targetUsername = $this->resolveTargetUsername(
                    $usernameMode,
                    $customUsername,
                    '',
                    $companyEmail,
                    $empNik
                );

                if ($targetUsername === '') {
                    $errors[] = "{$emp->Name}: Username tidak valid.";
                    continue;
                }

                $usernameTaken = DB::table('users')->where('username', $targetUsername)->exists();
                if ($usernameTaken) {
                    $errors[] = "{$emp->Name}: Username '{$targetUsername}' sudah terdaftar di tabel users.";
                    continue;
                }

                $empIdTaken = DB::table('users')
                    ->where('Emp_ID', $empNik)
                    ->whereRaw("UPPER(COALESCE(Position, '')) != 'CANDIDATE'")
                    ->exists();
                if ($empIdTaken) {
                    $errors[] = "{$emp->Name}: Emp_ID '{$empNik}' sudah dipakai akun karyawan lain.";
                    continue;
                }

                $rawPassword = trim((string) ($item['password'] ?? ''));
                $passwordToSet = $rawPassword !== '' ? $rawPassword : '12345678';

                $newUser = new User();
                $createPayload = [
                    'username' => $targetUsername,
                    'Emp_ID' => $empNik,
                    'Name' => $emp->Name,
                    'email' => $companyEmail !== '' ? $companyEmail : $fallbackEmail,
                    'password' => Hash::make($passwordToSet),
                    'Location' => $location,
                    'Position' => $roleName,
                    'Priv_ID' => $privId,
                    'Status' => 'ACTIVE',
                    'Sub_Menu' => $defaultResolved['default_sub_menu'] ?? '',
                ];
                if ($department !== '' && Schema::hasColumn('users', 'Department')) {
                    $createPayload['Department'] = $department;
                }
                $newUser->forceFill($createPayload)->save();

                $this->assignRoleAndRebuildMenu($newUser, $roleModel, $defaultResolved['default_sub_menu'] ?? null);

                $this->updateEmploymentLinkage(
                    (int) $emp->ID,
                    (int) $newUser->id,
                    null,
                    'NEW_EMPLOYEE',
                    $operator,
                    $hasUserIdCol,
                    $hasCandidateUserIdCol,
                    $hasSourceCol,
                    $hasLinkedByCol,
                    $hasLinkedAtCol
                );

                $created++;
                $processed[] = [
                    'employment_id' => (int) $emp->ID,
                    'name' => $emp->Name,
                    'user_id' => (int) $newUser->id,
                    'username' => $targetUsername,
                    'action' => 'NEW_EMPLOYEE',
                ];
            }
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $parts = [];
        if ($created > 0) {
            $parts[] = "{$created} akun baru dibuat";
        }
        if ($upgraded > 0) {
            $parts[] = "{$upgraded} akun kandidat dikonversi menjadi karyawan";
        }
        if ($synced > 0) {
            $parts[] = "{$synced} akun karyawan disinkronkan";
        }
        if ($skipped > 0) {
            $parts[] = "{$skipped} dilewati";
        }

        $summary = !empty($parts) ? implode(', ', $parts) . '.' : 'Tidak ada akun yang diproses.';
        if (!empty($errors)) {
            $summary .= ' Catatan kendala: ' . implode(' | ', $errors);
        }

        return [
            'status' => empty($errors) || ($created + $upgraded + $synced) > 0 ? 'success' : 'error',
            'created' => $created,
            'upgraded' => $upgraded,
            'synced' => $synced,
            'skipped' => $skipped,
            'processed' => $processed,
            'errors' => $errors,
            'message' => $summary,
        ];
    }

    /**
     * Cari akun karyawan aktif yang sudah terhubung ke baris employment.
     *
     * @param object $emp
     * @return object|null
     */
    public function findActiveEmployeeUser($emp)
    {
        if (!empty($emp->user_id)) {
            $byId = DB::table('users')->where('id', (int) $emp->user_id)->first();
            if ($byId && strtoupper(trim((string) $byId->Position)) !== 'CANDIDATE') {
                return $byId;
            }
        }

        $identifiers = array_values(array_unique(array_filter([
            trim((string) ($emp->NIK ?? '')),
            trim((string) ($emp->Task_ID ?? '')),
        ])));

        if (!empty($identifiers)) {
            $byEmpId = DB::table('users')
                ->whereIn('Emp_ID', $identifiers)
                ->whereRaw("UPPER(COALESCE(Position, '')) != 'CANDIDATE'")
                ->first();
            if ($byEmpId) {
                return $byEmpId;
            }
        }

        return null;
    }

    /**
     * Deteksi otomatis akun kandidat (Position = 'CANDIDATE') berdasarkan data employment.
     *
     * @param object $emp
     * @return array|null
     */
    public function detectCandidateAccount($emp): ?array
    {
        // 1. Cek candidate_user_id jika sudah pernah tercatat
        if (!empty($emp->candidate_user_id)) {
            $u = DB::table('users')
                ->where('id', (int) $emp->candidate_user_id)
                ->whereRaw("UPPER(COALESCE(Position, '')) = 'CANDIDATE'")
                ->first();
            if ($u) {
                return ['user' => $u, 'matched_by' => 'candidate_user_id', 'recruitment' => null];
            }
        }

        // 2. Cek melalui Recruitment_ID -> recruitment.ID -> Task_ID
        if (!empty($emp->Recruitment_ID)) {
            $rec = DB::table('recruitment')->where('ID', (int) $emp->Recruitment_ID)->first();
            if ($rec && !empty($rec->Task_ID)) {
                $u = $this->findCandidateUserByTaskId($rec->Task_ID);
                if ($u) {
                    return ['user' => $u, 'matched_by' => 'Recruitment_ID', 'recruitment' => $rec];
                }
            }
        }

        // 3. Cek langsung melalui employment.Task_ID
        if (!empty($emp->Task_ID)) {
            $u = $this->findCandidateUserByTaskId($emp->Task_ID);
            if ($u) {
                $rec = DB::table('recruitment')->where('Task_ID', $emp->Task_ID)->first();
                return ['user' => $u, 'matched_by' => 'Task_ID', 'recruitment' => $rec];
            }
        }

        // 4. Cek melalui KTP di tabel recruitment
        $ktp = trim((string) ($emp->KTP ?? ''));
        if ($ktp !== '' && strlen($ktp) >= 8) {
            $rec = DB::table('recruitment')
                ->where('KTP', $ktp)
                ->orderBy('ID', 'DESC')
                ->first();
            if ($rec && !empty($rec->Task_ID)) {
                $u = $this->findCandidateUserByTaskId($rec->Task_ID);
                if ($u) {
                    return ['user' => $u, 'matched_by' => 'KTP', 'recruitment' => $rec];
                }
            }
        }

        // 5. Cek melalui Email / Email_Individual
        $emails = array_values(array_unique(array_filter([
            $this->extractValidEmail($emp->Email ?? null),
            $this->extractValidEmail($emp->Email_Individual ?? null),
        ])));

        if (!empty($emails)) {
            $u = DB::table('users')
                ->whereIn('email', $emails)
                ->whereRaw("UPPER(COALESCE(Position, '')) = 'CANDIDATE'")
                ->orderBy('id', 'DESC')
                ->first();
            if ($u) {
                $rec = DB::table('recruitment')->where('Task_ID', $u->username)->first();
                return ['user' => $u, 'matched_by' => 'Email', 'recruitment' => $rec];
            }
        }

        return null;
    }

    private function findCandidateUserByTaskId(string $taskId)
    {
        $taskId = trim($taskId);
        if ($taskId === '') {
            return null;
        }

        return DB::table('users')
            ->whereRaw("UPPER(COALESCE(Position, '')) = 'CANDIDATE'")
            ->where(function ($q) use ($taskId) {
                $q->where('username', $taskId)
                  ->orWhere('Emp_ID', $taskId);
            })
            ->orderBy('id', 'DESC')
            ->first();
    }

    private function resolveTargetUsername(
        string $mode,
        string $customUsername,
        string $existingCandidateUsername,
        string $companyEmail,
        string $empNik
    ): string {
        switch ($mode) {
            case 'keep':
                return $existingCandidateUsername !== '' ? $existingCandidateUsername : ($companyEmail !== '' ? $companyEmail : $empNik);
            case 'company_email':
                return $companyEmail !== '' ? $companyEmail : $empNik;
            case 'nik':
                return $empNik;
            case 'custom':
                return $customUsername;
            default:
                if ($customUsername !== '') {
                    return $customUsername;
                }
                return $existingCandidateUsername !== ''
                    ? $existingCandidateUsername
                    : ($companyEmail !== '' ? $companyEmail : $empNik);
        }
    }

    private function resolveRoleModel(?int $roleId, ?string $roleName, ?string $department): ?Role
    {
        if ($roleId && $roleId > 0) {
            $byId = $this->roleResolver->resolveById($roleId);
            if ($byId) {
                return $byId;
            }
        }

        if ($roleName !== null && trim($roleName) !== '') {
            return $this->roleResolver->resolve($roleName, $department);
        }

        return null;
    }

    private function assignRoleAndRebuildMenu(User $user, ?Role $roleModel, ?string $fallbackSubMenu = null): void
    {
        if ($roleModel) {
            $user->syncRoles([$roleModel]);
        }
        if (class_exists(RbacService::class)) {
            RbacService::rebuildSubMenu($user);
            $rebuilt = trim((string) DB::table('users')->where('id', $user->id)->value('Sub_Menu'));
            if ($rebuilt === '' && $fallbackSubMenu !== null && trim($fallbackSubMenu) !== '') {
                DB::table('users')->where('id', $user->id)->update(['Sub_Menu' => trim($fallbackSubMenu)]);
            }
        }
    }

    private function updateEmploymentLinkage(
        int $employmentId,
        int $userId,
        ?int $candidateUserId,
        string $source,
        string $operator,
        bool $hasUserIdCol,
        bool $hasCandidateUserIdCol,
        bool $hasSourceCol,
        bool $hasLinkedByCol,
        bool $hasLinkedAtCol
    ): void {
        $data = [];
        if ($hasUserIdCol) {
            $data['user_id'] = $userId;
        }
        if ($hasCandidateUserIdCol && $candidateUserId !== null) {
            $data['candidate_user_id'] = $candidateUserId;
        }
        if ($hasSourceCol) {
            $data['user_account_source'] = $source;
        }
        if ($hasLinkedByCol) {
            $data['user_linked_by'] = $operator;
        }
        if ($hasLinkedAtCol) {
            $data['user_linked_at'] = Carbon::now();
        }

        if (!empty($data)) {
            DB::table('employment')->where('ID', $employmentId)->update($data);
        }
    }

    private function syncRecruitmentRecordIfAny($emp, string $empNik, string $candidateTaskId, string $operator): void
    {
        if (!Schema::hasTable('recruitment')) {
            return;
        }

        $rec = null;
        if (!empty($emp->Recruitment_ID)) {
            $rec = DB::table('recruitment')->where('ID', (int) $emp->Recruitment_ID)->first();
        }
        if (!$rec && $candidateTaskId !== '') {
            $rec = DB::table('recruitment')->where('Task_ID', $candidateTaskId)->first();
        }

        if (!$rec) {
            return;
        }

        $empUpdate = [];
        if (empty($emp->Recruitment_ID)) {
            $empUpdate['Recruitment_ID'] = $rec->ID;
        }
        if (empty($emp->Recruitment_Status)) {
            $empUpdate['Recruitment_Status'] = 'HIRED';
        }
        if (!empty($empUpdate)) {
            DB::table('employment')->where('ID', $emp->ID)->update($empUpdate);
        }

        $recUpdate = [];
        if (Schema::hasColumn('recruitment', 'NIK') && empty($rec->NIK)) {
            $recUpdate['NIK'] = $empNik;
        }
        if (Schema::hasColumn('recruitment', 'Onboarded_At') && empty($rec->Onboarded_At)) {
            $recUpdate['Onboarded_At'] = Carbon::now();
        }
        if (!empty($recUpdate)) {
            $recUpdate['Modified_By'] = $operator;
            $recUpdate['Modified_Date'] = Carbon::now();
            DB::table('recruitment')->where('ID', $rec->ID)->update($recUpdate);
        }
    }

    private function extractValidEmail(?string $email): string
    {
        $email = trim((string) $email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }
        return $email;
    }
}
