<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeGridService
{
    /**
     * Cek hak akses melihat daftar & detail karyawan (SI00101 view).
     *
     * @param mixed $user
     * @return bool
     */
    public function canViewEmployeeLists($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'can') && (
            $user->can('menu.SI00101.view') ||
            $user->can('menu.SI00101.create') ||
            $user->can('menu.SI00101.edit')
        )) {
            return true;
        }

        return $this->isSuperAdmin($user);
    }

    /**
     * Cek hak akses mengubah data karyawan & sub-detail (SI00101 edit/create).
     *
     * @param mixed $user
     * @return bool
     */
    public function canEditEmployeeLists($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'can') && (
            $user->can('menu.SI00101.edit') ||
            $user->can('menu.SI00101.create')
        )) {
            return true;
        }

        return $this->isSuperAdmin($user);
    }

    private function isSuperAdmin($user): bool
    {
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
     * Siapkan data awal untuk halaman index employee-lists.
     *
     * @param string $username
     * @param mixed $user
     * @return array
     */
    public function getIndexViewData(string $username, $user = null): array
    {
        $allCols = employee_grid_columns();
        $defaultCols = employee_grid_default_columns();
        $activeCols = $this->resolveActiveColumns($username, $allCols, $defaultCols);

        $meta = [];
        foreach ($allCols as $ck => $cm) {
            $meta[$ck] = [
                'label' => $cm[0],
                'width' => $cm[1],
                'align' => $cm[2],
                'filter' => $cm[3],
                'sort' => $cm[4],
                'type' => 'ro',
            ];
        }

        $totalEmp = DB::table('employment')->count();
        $activeEmp = DB::table('employment')->where('Status', 'ACTIVE')->count();
        $probationEmp = DB::table('employment')->where('Employment_Status', 'LIKE', '%PROBATION%')->count();
        $contractEmp = DB::table('employment')
            ->where(function ($q) {
                $q->where('Employment_Status', 'LIKE', '%KONTRAK%')
                  ->orWhere('Employment_Status', 'LIKE', '%PKWT%');
            })
            ->count();

        $canManageEmpAccounts = app(EmployeeUserAccountService::class)->canManageEmployeeAccounts($user);

        return [
            'AllCols' => $allCols,
            'DefaultCols' => $defaultCols,
            'ActiveCols' => $activeCols,
            'Meta' => $meta,
            'totalEmp' => $totalEmp,
            'activeEmp' => $activeEmp,
            'probationEmp' => $probationEmp,
            'contractEmp' => $contractEmp,
            'canManageEmpAccounts' => $canManageEmpAccounts,
        ];
    }

    /**
     * Render XML dhtmlXGrid untuk /xml-employee-lists.
     *
     * @param string $location
     * @param array $requestedCols
     * @return string
     */
    public function buildEmployeeGridXml(string $location, array $requestedCols = []): string
    {
        $allKeys = array_keys(employee_grid_columns());
        $keys = array_values(array_intersect($requestedCols, $allKeys));
        if (!count($keys)) {
            $keys = employee_grid_default_columns();
        }

        $akademis = $this->loadAcademicMajorMap();
        $locationMap = location_name_map();

        $employees = DB::table('employment')
            ->where('Location', $location)
            ->where('Status', '=', 'ACTIVE')
            ->orderBy('Name', 'ASC')
            ->get();

        $userAccountMap = $this->buildUserAccountMap($employees);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows>";
        $nomor = 1;
        foreach ($employees as $fetch) {
            $taskId = $this->escapeXml((string) ($fetch->Task_ID ?? ''));
            $name = $this->escapeXml((string) ($fetch->Name ?? ''));
            $xml .= "<row id=\"{$taskId}\">";
            $xml .= "<userdata name=\"nama\">{$name}</userdata>";
            $xml .= "<cell>{$nomor}</cell>";
            foreach ($keys as $k) {
                $val = employee_grid_cell($k, $fetch, $akademis, $locationMap, $userAccountMap);
                $xml .= '<cell>' . $this->escapeXml($val) . '</cell>';
            }
            $xml .= '</row>';
            $nomor++;
        }
        $xml .= '</rows>';

        return $xml;
    }

    /**
     * Bangun data JSON untuk /xml-employee-pivot dengan penanganan kolom virtual aman.
     *
     * @param string $location
     * @param string $username
     * @param array $requestedCols
     * @return array
     */
    public function buildPivotData(string $location, string $username, array $requestedCols = []): array
    {
        $map = employee_grid_columns();
        $defaultCols = employee_grid_default_columns();

        $keys = array_values(array_intersect($requestedCols, array_keys($map)));
        if (!count($keys)) {
            $keys = $this->resolveActiveColumns($username, $map, $defaultCols);
        }

        $virtualCols = ['Age', 'Academic_Major', 'Site_Location', 'User_Account'];
        $dbKeys = array_values(array_diff($keys, $virtualCols));

        $needAge = in_array('Age', $keys, true);
        $needAkad = in_array('Academic_Major', $keys, true);
        $needSite = in_array('Site_Location', $keys, true);
        $needUserAcc = in_array('User_Account', $keys, true);

        if ($needAge && !in_array('Birth_Date', $dbKeys, true)) {
            $dbKeys[] = 'Birth_Date';
        }
        if (($needAkad || $needUserAcc) && !in_array('Task_ID', $dbKeys, true)) {
            $dbKeys[] = 'Task_ID';
        }
        if ($needSite) {
            foreach (['Placement_Location_ID', 'Location'] as $reqCol) {
                if (!in_array($reqCol, $dbKeys, true)) {
                    $dbKeys[] = $reqCol;
                }
            }
        }
        if ($needUserAcc) {
            foreach (['ID', 'NIK', 'Email', 'Email_Individual', 'user_id'] as $reqCol) {
                if (!in_array($reqCol, $dbKeys, true) && Schema::hasColumn('employment', $reqCol)) {
                    $dbKeys[] = $reqCol;
                }
            }
        }

        // Pastikan kolom minimal selalu ada untuk select
        if (empty($dbKeys)) {
            $dbKeys = ['ID', 'Task_ID', 'Name'];
        }

        $akademis = $needAkad ? $this->loadAcademicMajorMap() : [];
        $locationMap = $needSite ? location_name_map() : [];

        $rows = DB::table('employment')
            ->select($dbKeys)
            ->where('Location', $location)
            ->where('Status', 'ACTIVE')
            ->orderBy('Name', 'ASC')
            ->get();

        $userAccountMap = $needUserAcc ? $this->buildUserAccountMap($rows) : [];

        $norm = function ($s) {
            return ($s === null || $s === '') ? '(Kosong)' : (string) $s;
        };

        $out = [];
        foreach ($rows as $d) {
            $row = [];
            foreach ($keys as $k) {
                if ($k === 'Age') {
                    $b = isset($d->Birth_Date) ? $d->Birth_Date : '';
                    $row[$map[$k][0]] = ($b !== '' && $b !== '0000-00-00')
                        ? (int) floor((time() - strtotime($b)) / 31556926)
                        : 0;
                } elseif ($k === 'Sisa_Cuti') {
                    $row[$map[$k][0]] = (isset($d->Sisa_Cuti) && is_numeric($d->Sisa_Cuti)) ? (int) $d->Sisa_Cuti : 0;
                } else {
                    $row[$map[$k][0]] = $norm(employee_grid_cell($k, $d, $akademis, $locationMap, $userAccountMap));
                }
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * Simpan preferensi kolom grid untuk user aktif.
     *
     * @param string $username
     * @param string $colsParam
     * @return array
     */
    public function saveColumnPreference(string $username, string $colsParam): array
    {
        if (!Schema::hasTable('user_grid_prefs')) {
            return ['status' => 'error', 'message' => 'Tabel user_grid_prefs belum ada.'];
        }

        if (!employee_grid_can_manage_columns()) {
            return ['status' => 'error', 'message' => 'Hanya user tertentu yang dapat menyimpan pengaturan kolom.'];
        }

        $keys = array_values(array_intersect(
            explode(',', $colsParam),
            array_keys(employee_grid_columns())
        ));

        if (!count($keys)) {
            return ['status' => 'error', 'message' => 'Pilih minimal 1 kolom.'];
        }

        DB::table('user_grid_prefs')->updateOrInsert(
            ['username' => $username, 'view' => 'employee-lists'],
            ['columns_json' => json_encode($keys), 'Updated_Date' => date('Y-m-d H:i:s')]
        );

        return ['status' => 'ok'];
    }

    private function resolveActiveColumns(string $username, array $allCols, array $defaultCols): array
    {
        if (!Schema::hasTable('user_grid_prefs') || $username === '') {
            return $defaultCols;
        }

        $savedJson = DB::table('user_grid_prefs')
            ->where('username', $username)
            ->where('view', 'employee-lists')
            ->value('columns_json');

        $savedCols = $savedJson ? json_decode($savedJson, true) : null;
        if (is_array($savedCols) && count($savedCols)) {
            $validCols = array_values(array_intersect($savedCols, array_keys($allCols)));
            if (count($validCols)) {
                return $validCols;
            }
        }

        return $defaultCols;
    }

    private function loadAcademicMajorMap(): array
    {
        $rows = DB::table('employment_akademic')->select('Task_ID', 'Education_Major')->get();
        $map = [];
        foreach ($rows as $row) {
            $map[$row->Task_ID] = $row->Education_Major;
        }
        return $map;
    }

    private function buildUserAccountMap($employees): array
    {
        $allUsers = DB::table('users')
            ->select('id', 'username', 'Emp_ID', 'Position', 'Status', 'email')
            ->get();

        $usersById = [];
        $usersByEmpId = [];
        $usersByUsername = [];
        $candByEmail = [];

        foreach ($allUsers as $u) {
            $usersById[(int) $u->id] = $u;
            $isCandRow = strtoupper(trim((string) ($u->Position ?? ''))) === 'CANDIDATE';

            $empKey = trim((string) ($u->Emp_ID ?? ''));
            if ($empKey !== '' && (!isset($usersByEmpId[$empKey]) || !$isCandRow)) {
                $usersByEmpId[$empKey] = $u;
            }

            $unameKey = trim((string) ($u->username ?? ''));
            if ($unameKey !== '' && (!isset($usersByUsername[$unameKey]) || !$isCandRow)) {
                $usersByUsername[$unameKey] = $u;
            }

            if ($isCandRow) {
                $uMail = strtolower(trim((string) ($u->email ?? '')));
                if ($uMail !== '') {
                    $candByEmail[$uMail] = $u;
                }
            }
        }

        $userAccountMap = [];
        foreach ($employees as $empRow) {
            $matchedUser = null;
            if (!empty($empRow->user_id) && isset($usersById[(int) $empRow->user_id])) {
                $matchedUser = $usersById[(int) $empRow->user_id];
            } else {
                $nikVal = trim((string) ($empRow->NIK ?? ''));
                $tidVal = trim((string) ($empRow->Task_ID ?? ''));
                if ($nikVal !== '' && isset($usersByEmpId[$nikVal])) {
                    $matchedUser = $usersByEmpId[$nikVal];
                } elseif ($tidVal !== '' && isset($usersByEmpId[$tidVal])) {
                    $matchedUser = $usersByEmpId[$tidVal];
                } elseif ($tidVal !== '' && isset($usersByUsername[$tidVal])) {
                    $matchedUser = $usersByUsername[$tidVal];
                } else {
                    $e1 = strtolower(trim((string) ($empRow->Email ?? '')));
                    $e2 = strtolower(trim((string) ($empRow->Email_Individual ?? '')));
                    if ($e1 !== '' && isset($candByEmail[$e1])) {
                        $matchedUser = $candByEmail[$e1];
                    } elseif ($e2 !== '' && isset($candByEmail[$e2])) {
                        $matchedUser = $candByEmail[$e2];
                    }
                }
            }

            if ($matchedUser && isset($empRow->ID)) {
                $isCandidate = strtoupper(trim((string) ($matchedUser->Position ?? ''))) === 'CANDIDATE';
                $isActiveUser = strtoupper(trim((string) ($matchedUser->Status ?? 'ACTIVE'))) === 'ACTIVE';
                if ($isCandidate) {
                    $label = ($isActiveUser ? 'Kandidat (' : 'Kandidat Nonaktif (') . $matchedUser->username . ')';
                } else {
                    $label = ($isActiveUser ? 'Aktif (' : 'Nonaktif (') . $matchedUser->username . ')';
                }
                $userAccountMap['id:' . $empRow->ID] = $label;
            }
        }

        return $userAccountMap;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
