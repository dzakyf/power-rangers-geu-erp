<?php

namespace App\Services\HCGA;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterWarehouseService
{
    /**
     * Ambil daftar warehouse/site dari gudang_list dengan filter pencarian, status, dan jumlah aset.
     */
    public function listWarehouses(?string $search = null, ?string $status = null): Collection
    {
        $hasStatus = Schema::hasColumn('gudang_list', 'Status');

        $query = DB::table('gudang_list')->orderBy('Gudang_ID', 'ASC');

        $q = trim((string) $search);
        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($builder) use ($like) {
                $builder->where('Gudang_ID', 'like', $like)
                    ->orWhere('Description', 'like', $like)
                    ->orWhere('PIC', 'like', $like)
                    ->orWhere('Department', 'like', $like)
                    ->orWhere('Location', 'like', $like)
                    ->orWhere('City', 'like', $like)
                    ->orWhere('Address', 'like', $like)
                    ->orWhere('Street', 'like', $like);
            });
        }

        $statusFilter = strtoupper(trim((string) $status));
        if ($hasStatus && in_array($statusFilter, ['ACTIVE', 'INACTIVE'], true)) {
            if ($statusFilter === 'ACTIVE') {
                $query->where(function ($builder) {
                    $builder->where('Status', 'ACTIVE')
                        ->orWhereNull('Status')
                        ->orWhere('Status', '');
                });
            } else {
                $query->where('Status', 'INACTIVE');
            }
        }

        $rows = $query->get();

        $assetCounts = [];
        if (Schema::hasTable('asset_list')) {
            $assetCounts = DB::table('asset_list')
                ->select('Asset_Location', DB::raw('COUNT(*) as total_assets'))
                ->whereNotNull('Asset_Location')
                ->where('Asset_Location', '!=', '')
                ->groupBy('Asset_Location')
                ->pluck('total_assets', 'Asset_Location')
                ->all();
        }

        return $rows->map(function ($row) use ($assetCounts, $hasStatus) {
            $code = trim((string) ($row->Gudang_ID ?? ''));
            $rowStatus = $hasStatus ? strtoupper(trim((string) ($row->Status ?? 'ACTIVE'))) : 'ACTIVE';
            $row->Status = $rowStatus === 'INACTIVE' ? 'INACTIVE' : 'ACTIVE';
            $row->asset_count = (int) ($assetCounts[$code] ?? 0);
            return $row;
        });
    }

    /**
     * Ringkasan jumlah warehouse (total, aktif, nonaktif).
     */
    public function getSummaryStats(): array
    {
        if (!Schema::hasColumn('gudang_list', 'Status')) {
            $total = DB::table('gudang_list')->count();
            return ['total' => $total, 'active' => $total, 'inactive' => 0];
        }

        $row = DB::table('gudang_list')
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN UPPER(COALESCE(NULLIF(Status, ''), 'ACTIVE')) = 'INACTIVE' THEN 1 ELSE 0 END) AS inactive_count
            ")
            ->first();

        $total = (int) ($row->total ?? 0);
        $inactive = (int) ($row->inactive_count ?? 0);

        return [
            'total'    => $total,
            'active'   => max(0, $total - $inactive),
            'inactive' => $inactive,
        ];
    }

    /**
     * Opsi referensi datalist untuk Location dan Department.
     */
    public function getReferenceOptions(): array
    {
        $locations = [];

        if (Schema::hasTable('location')) {
            $masterLocs = DB::table('location')
                ->select('Location_ID', 'Name')
                ->orderBy('Location_ID', 'ASC')
                ->get();
            foreach ($masterLocs as $loc) {
                $id = trim((string) ($loc->Location_ID ?? ''));
                if ($id !== '') {
                    $locations[$id] = [
                        'value' => $id,
                        'label' => trim((string) ($loc->Name ?? '')) !== '' ? ($id . ' - ' . trim((string) $loc->Name)) : $id,
                    ];
                }
            }
        }

        $existingLocs = DB::table('gudang_list')
            ->whereNotNull('Location')
            ->where('Location', '!=', '')
            ->distinct()
            ->pluck('Location')
            ->all();

        foreach ($existingLocs as $locVal) {
            $code = trim((string) $locVal);
            if ($code !== '' && !isset($locations[$code])) {
                $locations[$code] = [
                    'value' => $code,
                    'label' => $code,
                ];
            }
        }
        ksort($locations, SORT_NATURAL | SORT_FLAG_CASE);

        $departments = [];
        if (Schema::hasTable('department') && Schema::hasColumn('department', 'Department')) {
            $deptRows = DB::table('department')
                ->whereNotNull('Department')
                ->where('Department', '!=', '')
                ->distinct()
                ->pluck('Department')
                ->all();
            foreach ($deptRows as $d) {
                $val = trim((string) $d);
                if ($val !== '') {
                    $departments[$val] = $val;
                }
            }
        }

        $existingDepts = DB::table('gudang_list')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->distinct()
            ->pluck('Department')
            ->all();
        foreach ($existingDepts as $d) {
            $val = trim((string) $d);
            if ($val !== '') {
                $departments[$val] = $val;
            }
        }
        ksort($departments, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            'locations'   => array_values($locations),
            'departments' => array_values($departments),
        ];
    }

    /**
     * Generate kode Gudang_ID otomatis dengan format WH-<PREFIX3>0001.
     */
    public function generateGudangId(?string $city, ?string $description): string
    {
        $source = preg_replace('/[^A-Za-z0-9]/', '', (string) $city);
        if (strlen($source) < 2) {
            $source = preg_replace('/[^A-Za-z0-9]/', '', (string) $description);
        }
        if ($source === '') {
            $source = 'GEN';
        }

        $prefix3 = strtoupper(substr(str_pad($source, 3, 'X'), 0, 3));
        $basePrefix = 'WH-' . $prefix3;

        $existingCodes = DB::table('gudang_list')
            ->where('Gudang_ID', 'like', $basePrefix . '%')
            ->pluck('Gudang_ID')
            ->all();

        $maxSeq = 0;
        $pattern = '/^' . preg_quote($basePrefix, '/') . '(\d+)$/i';
        foreach ($existingCodes as $code) {
            if (preg_match($pattern, trim((string) $code), $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $next = $maxSeq + 1;
        do {
            $candidate = $basePrefix . sprintf('%04d', $next);
            $exists = DB::table('gudang_list')
                ->whereRaw('LOWER(Gudang_ID) = ?', [strtolower($candidate)])
                ->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }

    /**
     * Simpan warehouse baru ke tabel gudang_list.
     */
    public function createWarehouse(array $data, $user): array
    {
        $gudangId = strtoupper(trim((string) ($data['Gudang_ID'] ?? '')));
        if ($gudangId === '') {
            $gudangId = $this->generateGudangId($data['City'] ?? '', $data['Description'] ?? '');
        }

        $duplicate = DB::table('gudang_list')
            ->whereRaw('LOWER(Gudang_ID) = ?', [strtolower($gudangId)])
            ->exists();

        if ($duplicate) {
            throw new \InvalidArgumentException("Kode Warehouse '{$gudangId}' sudah terdaftar.");
        }

        $defaultLocation = trim((string) ($user->Location ?? 'GEU-HO'));
        $location = trim((string) ($data['Location'] ?? ''));
        if ($location === '') {
            $location = $defaultLocation !== '' ? $defaultLocation : 'GEU-HO';
        }

        $payload = [
            'Gudang_ID'    => $gudangId,
            'Description'  => trim((string) ($data['Description'] ?? '')),
            'PIC'          => trim((string) ($data['PIC'] ?? '')),
            'Department'   => trim((string) ($data['Department'] ?? '')),
            'Location'     => $location,
            'Address'      => trim((string) ($data['Address'] ?? '')),
            'Street'       => trim((string) ($data['Street'] ?? '')),
            'City'         => trim((string) ($data['City'] ?? '')),
            'Created_By'   => (string) ($user->username ?? 'system'),
            'Created_Date' => now(),
        ];

        if (Schema::hasColumn('gudang_list', 'Status')) {
            $payload['Status'] = 'ACTIVE';
        }

        $id = DB::table('gudang_list')->insertGetId($payload);

        return [
            'id'        => $id,
            'gudang_id' => $gudangId,
        ];
    }

    /**
     * Perbarui data warehouse berdasarkan ID.
     */
    public function updateWarehouse(int $id, array $data, $user): bool
    {
        $existing = DB::table('gudang_list')->where('ID', $id)->first();
        if (!$existing) {
            return false;
        }

        $gudangId = strtoupper(trim((string) ($data['Gudang_ID'] ?? $existing->Gudang_ID)));
        if ($gudangId === '') {
            $gudangId = (string) $existing->Gudang_ID;
        }

        $duplicate = DB::table('gudang_list')
            ->whereRaw('LOWER(Gudang_ID) = ?', [strtolower($gudangId)])
            ->where('ID', '!=', $id)
            ->exists();

        if ($duplicate) {
            throw new \InvalidArgumentException("Kode Warehouse '{$gudangId}' sudah digunakan oleh data lain.");
        }

        $payload = [
            'Gudang_ID'    => $gudangId,
            'Description'  => trim((string) ($data['Description'] ?? '')),
            'PIC'          => trim((string) ($data['PIC'] ?? '')),
            'Department'   => trim((string) ($data['Department'] ?? '')),
            'Location'     => trim((string) ($data['Location'] ?? '')),
            'Address'      => trim((string) ($data['Address'] ?? '')),
            'Street'       => trim((string) ($data['Street'] ?? '')),
            'City'         => trim((string) ($data['City'] ?? '')),
            'Updated_By'   => (string) ($user->username ?? 'system'),
            'Updated_Date' => now(),
        ];

        if (Schema::hasColumn('gudang_list', 'Status') && !empty($data['Status'])) {
            $status = strtoupper(trim((string) $data['Status']));
            if (in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
                $payload['Status'] = $status;
            }
        }

        DB::table('gudang_list')->where('ID', $id)->update($payload);

        return true;
    }

    /**
     * Ubah status warehouse antara ACTIVE dan INACTIVE (soft-deactivate).
     */
    public function toggleStatus(int $id, ?string $targetStatus, $user): ?string
    {
        $existing = DB::table('gudang_list')->where('ID', $id)->first();
        if (!$existing) {
            return null;
        }

        $hasStatus = Schema::hasColumn('gudang_list', 'Status');
        if (!$hasStatus) {
            return 'ACTIVE';
        }

        $current = strtoupper(trim((string) ($existing->Status ?? 'ACTIVE')));
        $next = strtoupper(trim((string) $targetStatus));
        if (!in_array($next, ['ACTIVE', 'INACTIVE'], true)) {
            $next = ($current === 'INACTIVE') ? 'ACTIVE' : 'INACTIVE';
        }

        DB::table('gudang_list')->where('ID', $id)->update([
            'Status'       => $next,
            'Updated_By'   => (string) ($user->username ?? 'system'),
            'Updated_Date' => now(),
        ]);

        return $next;
    }
}
