<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Service pengelolaan Master Kategori Utama & Subkategori IT Support (tabel ga_ticket_category, Code IT-%).
 */
class ItTicketCategoryService
{
    public const SYSTEM_DEFAULT_CODES = ['IT-HELP', 'IT-SYS', 'IT-DEV', 'IT-PREV'];

    public function listMainCategories(?string $status = null)
    {
        $childCounts = DB::table('ga_ticket_category')
            ->selectRaw("Parent_ID, COUNT(*) as Total_Subcategory_Count, SUM(CASE WHEN Status = 'ACTIVE' THEN 1 ELSE 0 END) as Active_Subcategory_Count")
            ->whereNotNull('Parent_ID')
            ->where('Code', 'like', 'IT-%')
            ->groupBy('Parent_ID');

        $query = DB::table('ga_ticket_category as c')
            ->leftJoinSub($childCounts, 'sc', function ($join) {
                $join->on('sc.Parent_ID', '=', 'c.ID');
            })
            ->leftJoin('ga_team as t', 't.ID', '=', 'c.Default_Team_ID')
            ->leftJoin('ga_sla_policy as s', 's.ID', '=', 'c.Sla_Policy_ID')
            ->whereNull('c.Parent_ID')
            ->where('c.Code', 'like', 'IT-%')
            ->select(
                'c.*',
                't.Name as Team_Name',
                's.Name as Sla_Name',
                DB::raw('COALESCE(sc.Active_Subcategory_Count, 0) as Active_Subcategory_Count'),
                DB::raw('COALESCE(sc.Total_Subcategory_Count, 0) as Total_Subcategory_Count')
            )
            ->orderBy('c.Name');

        if ($status !== null && $status !== '') {
            $query->where('c.Status', strtoupper(trim($status)));
        }

        return $query->get();
    }

    public function listSubcategories(?int $parentId = null, ?string $status = null)
    {
        $query = DB::table('ga_ticket_category as c')
            ->leftJoin('ga_ticket_category as p', 'p.ID', '=', 'c.Parent_ID')
            ->leftJoin('ga_team as t', 't.ID', '=', 'c.Default_Team_ID')
            ->leftJoin('ga_sla_policy as s', 's.ID', '=', 'c.Sla_Policy_ID')
            ->whereNotNull('c.Parent_ID')
            ->where('c.Code', 'like', 'IT-%')
            ->select(
                'c.*',
                'p.Name as Parent_Name',
                'p.Code as Parent_Code',
                'p.Status as Parent_Status',
                'p.Location as Parent_Location',
                'p.Company_ID as Parent_Company_ID',
                't.Name as Team_Name',
                's.Name as Sla_Name'
            )
            ->orderBy('p.Name')
            ->orderBy('c.Name');

        if ($parentId !== null && $parentId > 0) {
            $query->where('c.Parent_ID', $parentId);
        }

        if ($status !== null && $status !== '') {
            $query->where('c.Status', strtoupper(trim($status)));
        }

        return $query->get();
    }

    public function saveMainCategory(array $data, $user): object
    {
        $id = !empty($data['ID']) ? (int) $data['ID'] : null;
        $code = $this->normalizeItCode((string) ($data['Code'] ?? ''));
        $name = trim((string) ($data['Name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama kategori wajib diisi.');
        }

        [$location, $companyId] = $this->resolveHybridScope($data);
        $status = strtoupper(trim((string) ($data['Status'] ?? 'ACTIVE')));
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            throw new InvalidArgumentException('Status kategori tidak valid.');
        }

        $priority = strtoupper(trim((string) ($data['Default_Priority'] ?? 'NORMAL')));
        if (!in_array($priority, ['LOW', 'NORMAL', 'HIGH', 'URGENT'], true)) {
            $priority = 'NORMAL';
        }

        $teamId = !empty($data['Default_Team_ID']) ? (int) $data['Default_Team_ID'] : null;
        $slaId = !empty($data['Sla_Policy_ID']) ? (int) $data['Sla_Policy_ID'] : null;

        $this->assertRelatedMasterExists($teamId, $slaId);

        if ($id !== null) {
            $existing = DB::table('ga_ticket_category')->where('ID', $id)->first();
            if (!$existing || !$this->isItCode($existing->Code)) {
                throw new InvalidArgumentException('Kategori IT tidak ditemukan.');
            }
            if (!empty($existing->Parent_ID)) {
                throw new InvalidArgumentException('Data ini adalah subkategori, ubah melalui tab Subkategori.');
            }

            $existingCode = strtoupper((string) $existing->Code);
            if ($this->isSystemDefaultCode($existingCode)) {
                if ($code !== $existingCode) {
                    throw new InvalidArgumentException('Kode kategori default sistem (' . $existingCode . ') tidak dapat diubah.');
                }
                if ($status !== 'ACTIVE') {
                    throw new InvalidArgumentException('Kategori default sistem (' . $existingCode . ') tidak dapat dinonaktifkan.');
                }
                if ($location !== null || $companyId !== null) {
                    throw new InvalidArgumentException('Kategori default sistem (' . $existingCode . ') harus tetap berlaku Global.');
                }
            }

            if ($status === 'INACTIVE' && strtoupper((string) $existing->Status) === 'ACTIVE') {
                $this->assertNoActiveChildren($id);
            }

            $this->assertActiveChildrenCompatibleWithParentScope($id, $location, $companyId);
        }

        $this->assertUniqueCode($code, $location, $companyId, $id);

        $now = Carbon::now();
        $actor = (string) ($user->username ?? 'system');
        $payload = [
            'Location'         => $location,
            'Company_ID'       => $companyId,
            'Parent_ID'        => null,
            'Code'             => $code,
            'Name'             => mb_substr($name, 0, 150),
            'Default_Priority' => $priority,
            'Default_Team_ID'  => $teamId,
            'Sla_Policy_ID'    => $slaId,
            'Status'           => $status,
            'Updated_By'       => $actor,
            'Updated_At'       => $now,
        ];

        $savedId = DB::transaction(function () use ($id, $payload, $actor, $now) {
            if ($id !== null) {
                DB::table('ga_ticket_category')->where('ID', $id)->update($payload);
                return $id;
            }
            $payload['Created_By'] = $actor;
            $payload['Created_At'] = $now;
            return (int) DB::table('ga_ticket_category')->insertGetId($payload);
        });

        return DB::table('ga_ticket_category')->where('ID', $savedId)->first();
    }

    public function saveSubcategory(array $data, $user): object
    {
        $id = !empty($data['ID']) ? (int) $data['ID'] : null;
        $parentId = !empty($data['Parent_ID']) ? (int) $data['Parent_ID'] : 0;
        if ($parentId < 1) {
            throw new InvalidArgumentException('Kategori Utama (Parent) wajib dipilih.');
        }

        $parent = DB::table('ga_ticket_category')->where('ID', $parentId)->first();
        if (!$parent || !$this->isItCode($parent->Code)) {
            throw new InvalidArgumentException('Kategori Utama IT tidak ditemukan.');
        }
        if (!empty($parent->Parent_ID)) {
            throw new InvalidArgumentException('Struktur maksimal 2 tingkat: tidak dapat menjadikan subkategori sebagai induk.');
        }

        $code = $this->normalizeItCode((string) ($data['Code'] ?? ''));
        if ($this->isSystemDefaultCode($code)) {
            throw new InvalidArgumentException('Kode ' . $code . ' dicadangkan untuk kategori utama default sistem.');
        }

        $name = trim((string) ($data['Name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama subkategori wajib diisi.');
        }

        [$location, $companyId] = $this->resolveHybridScope($data);
        $status = strtoupper(trim((string) ($data['Status'] ?? 'ACTIVE')));
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            throw new InvalidArgumentException('Status subkategori tidak valid.');
        }

        if ($status === 'ACTIVE' && strtoupper((string) $parent->Status) !== 'ACTIVE') {
            throw new InvalidArgumentException('Kategori Utama "' . $parent->Name . '" berstatus INACTIVE. Aktifkan kategori utama terlebih dahulu.');
        }

        if ($parent->Location !== null && $location !== $parent->Location) {
            throw new InvalidArgumentException('Scope Lokasi subkategori harus sama dengan Kategori Utama (' . $parent->Location . ').');
        }
        if ($parent->Company_ID !== null && $companyId !== $parent->Company_ID) {
            throw new InvalidArgumentException('Scope Company subkategori harus sama dengan Kategori Utama (' . $parent->Company_ID . ').');
        }

        $priority = strtoupper(trim((string) ($data['Default_Priority'] ?? 'NORMAL')));
        if (!in_array($priority, ['LOW', 'NORMAL', 'HIGH', 'URGENT'], true)) {
            $priority = 'NORMAL';
        }

        $teamId = !empty($data['Default_Team_ID']) ? (int) $data['Default_Team_ID'] : null;
        $slaId = !empty($data['Sla_Policy_ID']) ? (int) $data['Sla_Policy_ID'] : null;

        $this->assertRelatedMasterExists($teamId, $slaId);

        if ($id !== null) {
            if ($parentId === $id) {
                throw new InvalidArgumentException('Kategori tidak dapat menjadi parent bagi dirinya sendiri.');
            }
            $existing = DB::table('ga_ticket_category')->where('ID', $id)->first();
            if (!$existing || !$this->isItCode($existing->Code)) {
                throw new InvalidArgumentException('Subkategori IT tidak ditemukan.');
            }
            if (empty($existing->Parent_ID)) {
                throw new InvalidArgumentException('Data ini adalah Kategori Utama, ubah melalui tab Kategori Utama.');
            }
        }

        $this->assertUniqueCode($code, $location, $companyId, $id);

        $now = Carbon::now();
        $actor = (string) ($user->username ?? 'system');
        $payload = [
            'Location'         => $location,
            'Company_ID'       => $companyId,
            'Parent_ID'        => $parentId,
            'Code'             => $code,
            'Name'             => mb_substr($name, 0, 150),
            'Default_Priority' => $priority,
            'Default_Team_ID'  => $teamId,
            'Sla_Policy_ID'    => $slaId,
            'Status'           => $status,
            'Updated_By'       => $actor,
            'Updated_At'       => $now,
        ];

        $savedId = DB::transaction(function () use ($id, $payload, $actor, $now) {
            if ($id !== null) {
                DB::table('ga_ticket_category')->where('ID', $id)->update($payload);
                return $id;
            }
            $payload['Created_By'] = $actor;
            $payload['Created_At'] = $now;
            return (int) DB::table('ga_ticket_category')->insertGetId($payload);
        });

        return DB::table('ga_ticket_category')->where('ID', $savedId)->first();
    }

    public function deactivate(int $id, $user): object
    {
        $row = DB::table('ga_ticket_category')->where('ID', $id)->first();
        if (!$row || !$this->isItCode($row->Code)) {
            throw new InvalidArgumentException('Kategori / subkategori IT tidak ditemukan.');
        }

        $code = strtoupper((string) $row->Code);
        if ($this->isSystemDefaultCode($code)) {
            throw new InvalidArgumentException('Kategori default sistem (' . $code . ') tidak dapat dinonaktifkan.');
        }

        if (empty($row->Parent_ID)) {
            $this->assertNoActiveChildren($id);
        }

        DB::table('ga_ticket_category')->where('ID', $id)->update([
            'Status'     => 'INACTIVE',
            'Updated_By' => (string) ($user->username ?? 'system'),
            'Updated_At' => Carbon::now(),
        ]);

        return DB::table('ga_ticket_category')->where('ID', $id)->first();
    }

    public function assertNoActiveChildren(int $parentId): void
    {
        $activeCount = (int) DB::table('ga_ticket_category')
            ->where('Parent_ID', $parentId)
            ->where('Status', 'ACTIVE')
            ->count();

        if ($activeCount > 0) {
            throw new InvalidArgumentException(
                "Kategori utama tidak dapat dinonaktifkan karena masih memiliki {$activeCount} subkategori aktif. Nonaktifkan atau pindahkan subkategori tersebut terlebih dahulu."
            );
        }
    }

    public function getDropdownOptions($user = null): array
    {
        $mainCategories = DB::table('ga_ticket_category')
            ->whereNull('Parent_ID')
            ->where('Code', 'like', 'IT-%')
            ->where('Status', 'ACTIVE')
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Location', 'Company_ID']);

        $allMainCategories = DB::table('ga_ticket_category')
            ->whereNull('Parent_ID')
            ->where('Code', 'like', 'IT-%')
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Location', 'Company_ID', 'Status']);

        $teams = DB::table('ga_team')
            ->where('Status', 'ACTIVE')
            ->where(function ($q) {
                $q->where('Code', 'like', 'IT-%')->orWhere('Department', 'ICT');
            })
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name', 'Team_Type']);

        if ($teams->isEmpty()) {
            $teams = DB::table('ga_team')->where('Status', 'ACTIVE')->orderBy('Name')->get(['ID', 'Code', 'Name', 'Team_Type']);
        }

        $slas = DB::table('ga_sla_policy')
            ->where('Status', 'ACTIVE')
            ->orderBy('Name')
            ->get(['ID', 'Code', 'Name']);

        $locations = collect();
        $companies = collect();

        if (Schema::hasTable('users')) {
            $locations = $locations->merge(
                DB::table('users')->whereNotNull('Location')->where('Location', '!=', '')->distinct()->pluck('Location')
            );
            $companies = $companies->merge(
                DB::table('users')->whereNotNull('Company_ID')->where('Company_ID', '!=', '')->distinct()->pluck('Company_ID')
            );
        }

        $locations = $locations->merge(
            DB::table('ga_ticket_category')->whereNotNull('Location')->where('Location', '!=', '')->distinct()->pluck('Location')
        );
        $companies = $companies->merge(
            DB::table('ga_ticket_category')->whereNotNull('Company_ID')->where('Company_ID', '!=', '')->distinct()->pluck('Company_ID')
        );

        if ($user) {
            if (!empty($user->Location)) {
                $locations->push($user->Location);
            }
            if (!empty($user->Company_ID)) {
                $companies->push($user->Company_ID);
            }
        }

        return [
            'mainCategories'    => $mainCategories,
            'allMainCategories' => $allMainCategories,
            'teams'             => $teams,
            'slas'              => $slas,
            'locations'         => $locations->map(function ($v) { return trim((string) $v); })->filter()->unique()->sort()->values(),
            'companies'         => $companies->map(function ($v) { return trim((string) $v); })->filter()->unique()->sort()->values(),
        ];
    }

    public function normalizeItCode(string $rawCode): string
    {
        $code = strtoupper(trim($rawCode));
        $code = preg_replace('/\s+/', '-', $code);
        $code = preg_replace('/[^A-Z0-9_-]/', '', (string) $code);

        if ($code === '' || $code === 'IT-') {
            throw new InvalidArgumentException('Kode kategori wajib diisi (huruf/angka/tanda hubung).');
        }

        if (strpos($code, 'IT-') !== 0) {
            $code = 'IT-' . ltrim($code, '-');
        }

        if ($code === 'IT-') {
            throw new InvalidArgumentException('Kode kategori setelah prefix IT- tidak boleh kosong.');
        }

        if (mb_strlen($code) > 50) {
            throw new InvalidArgumentException('Panjang kode maksimal 50 karakter.');
        }

        return $code;
    }

    public function isItCode(?string $code): bool
    {
        return strpos(strtoupper(trim((string) $code)), 'IT-') === 0;
    }

    public function isSystemDefaultCode(?string $code): bool
    {
        return in_array(strtoupper(trim((string) $code)), self::SYSTEM_DEFAULT_CODES, true);
    }

    protected function resolveHybridScope(array $data): array
    {
        $scopeMode = strtoupper(trim((string) ($data['Scope_Mode'] ?? '')));
        $location = trim((string) ($data['Location'] ?? ''));
        $companyId = trim((string) ($data['Company_ID'] ?? ''));

        $location = $location !== '' ? mb_substr($location, 0, 50) : null;
        $companyId = $companyId !== '' ? mb_substr($companyId, 0, 50) : null;

        if ($scopeMode === 'GLOBAL') {
            return [null, null];
        }

        if ($scopeMode === 'SPECIFIC' && $location === null && $companyId === null) {
            throw new InvalidArgumentException('Untuk scope Spesifik, pilih minimal Lokasi atau Company.');
        }

        return [$location, $companyId];
    }

    protected function assertUniqueCode(string $code, ?string $location, ?string $companyId, ?int $excludeId = null): void
    {
        $query = DB::table('ga_ticket_category')->where('Code', $code);

        if ($location === null) {
            $query->whereNull('Location');
        } else {
            $query->where('Location', $location);
        }

        if ($companyId === null) {
            $query->whereNull('Company_ID');
        } else {
            $query->where('Company_ID', $companyId);
        }

        if ($excludeId !== null) {
            $query->where('ID', '!=', $excludeId);
        }

        if ($query->exists()) {
            $scopeLabel = ($location === null && $companyId === null)
                ? 'Global'
                : trim(($location ?: 'Semua Lokasi') . ' / ' . ($companyId ?: 'Semua Company'));
            throw new InvalidArgumentException("Kode {$code} sudah terdaftar pada scope {$scopeLabel}.");
        }
    }

    protected function assertActiveChildrenCompatibleWithParentScope(int $parentId, ?string $parentLocation, ?string $parentCompany): void
    {
        if ($parentLocation === null && $parentCompany === null) {
            return;
        }

        $activeChildren = DB::table('ga_ticket_category')
            ->where('Parent_ID', $parentId)
            ->where('Status', 'ACTIVE')
            ->get(['ID', 'Code', 'Name', 'Location', 'Company_ID']);

        foreach ($activeChildren as $child) {
            if ($parentLocation !== null && $child->Location !== $parentLocation) {
                throw new InvalidArgumentException(
                    "Tidak dapat mengubah scope Lokasi kategori utama karena subkategori aktif \"{$child->Name}\" ({$child->Code}) memiliki scope Lokasi yang berbeda."
                );
            }
            if ($parentCompany !== null && $child->Company_ID !== $parentCompany) {
                throw new InvalidArgumentException(
                    "Tidak dapat mengubah scope Company kategori utama karena subkategori aktif \"{$child->Name}\" ({$child->Code}) memiliki scope Company yang berbeda."
                );
            }
        }
    }

    protected function assertRelatedMasterExists(?int $teamId, ?int $slaId): void
    {
        if ($teamId !== null && !DB::table('ga_team')->where('ID', $teamId)->exists()) {
            throw new InvalidArgumentException('Default Team yang dipilih tidak ditemukan.');
        }
        if ($slaId !== null && !DB::table('ga_sla_policy')->where('ID', $slaId)->exists()) {
            throw new InvalidArgumentException('Kebijakan SLA yang dipilih tidak ditemukan.');
        }
    }
}
