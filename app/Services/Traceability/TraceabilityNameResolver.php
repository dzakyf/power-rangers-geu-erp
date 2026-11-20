<?php

namespace App\Services\Traceability;

use Illuminate\Support\Facades\DB;

/**
 * Resolves display supplier names for traceability rows using only suppliers
 * that have actually released a surat jalan. The raw APIPI cache is never
 * modified; inactive and supplier/test-like names are excluded.
 */
class TraceabilityNameResolver
{
    /** @var array<string,array>|null */
    protected $groups = null;

    /** @var array<string,bool> */
    protected $inactiveNames = [];

    public function resolve(array $target, array $excludeNames = []): ?array
    {
        $this->load();

        $type = strtoupper(trim((string) ($target['poo_type'] ?? '')));
        $city = trim((string) ($target['city'] ?? ''));
        $site = trim((string) ($target['site'] ?? ''));
        $province = trim((string) ($target['province'] ?? ''));
        $targetId = $target['id'] ?? 0;

        $keys = [];
        if ($type !== '' && $city !== '') { $keys[] = "{$type}|city|{$city}"; }
        if ($type !== '' && $site !== '') { $keys[] = "{$type}|site|{$site}"; }
        if ($type !== '' && $province !== '') { $keys[] = "{$type}|province|{$province}"; }
        if ($type !== '') { $keys[] = "{$type}|all"; }

        $exclude = [];
        foreach ($excludeNames as $name) {
            $exclude[strtolower(trim((string) $name))] = true;
        }

        foreach ($keys as $key) {
            $pool = $this->groups[$key] ?? [];
            $pool = array_values(array_filter($pool, function ($row) use ($exclude) {
                $name = strtolower(trim((string) ($row['name'] ?? '')));
                return $name !== '' && !isset($exclude[$name]);
            }));

            if (!$pool) { continue; }

            mt_srand(crc32((string) $targetId . '|remap'));
            $pick = $pool[mt_rand(0, count($pool) - 1)];

            return $pick;
        }

        return null;
    }

    protected function load(): void
    {
        if ($this->groups !== null) { return; }

        $this->loadInactiveNames();

        $rows = DB::table('traceability_external_deliveries as d')
            ->join('traceability_external_poo_fragments as f', 'f.traceability_external_delivery_id', '=', 'd.id')
            ->where(function ($q) {
                $q->where('d.api_reference', 'like', 'GEU-SR-%')
                  ->orWhere('d.api_reference', 'like', 'GEU-SJ-%');
            })
            ->whereNotNull('d.supplier_name')->where('d.supplier_name', '<>', '')
            ->where('d.supplier_name', 'not like', '%supplier%')
            ->where('f.poo_name', 'not like', '%supplier%')
            ->select(
                'd.supplier_name as name',
                'd.site as site',
                'f.poo_type as poo_type',
                'f.city as city',
                'f.province as province',
                'f.full_address as full_address',
                'f.lat as lat',
                'f.lng as lng',
                'f.phone as phone',
                'f.account_no as account_no',
                'f.account_name as account_name'
            )
            ->distinct()
            ->get();

        $this->groups = [];
        $seen = [];

        foreach ($rows as $row) {
            $nameKey = strtolower(trim((string) $row->name));
            if ($nameKey === '' || isset($this->inactiveNames[$nameKey])) { continue; }

            $type = strtoupper(trim((string) $row->poo_type));
            if ($type === '') { continue; }

            $payload = [
                'name' => (string) $row->name,
                'poo_type' => (string) $row->poo_type,
                'city' => (string) $row->city,
                'province' => (string) $row->province,
                'full_address' => (string) $row->full_address,
                'lat' => (string) $row->lat,
                'lng' => (string) $row->lng,
                'phone' => (string) $row->phone,
                'account_no' => (string) $row->account_no,
                'account_name' => (string) $row->account_name,
            ];

            $keys = ["{$type}|all"];
            if (!empty($row->city)) { $keys[] = "{$type}|city|{$row->city}"; }
            if (!empty($row->site)) { $keys[] = "{$type}|site|{$row->site}"; }
            if (!empty($row->province)) { $keys[] = "{$type}|province|{$row->province}"; }

            foreach ($keys as $key) {
                $uniq = $key . '|' . $nameKey;
                if (isset($seen[$uniq])) { continue; }
                $seen[$uniq] = true;
                $this->groups[$key][] = $payload;
            }
        }
    }

    protected function loadInactiveNames(): void
    {
        $dir = rtrim(config('services.apipi.cache_path') ?: storage_path('app/apipi/cache'), '/');
        $suppliers = json_decode((string) @file_get_contents($dir . '/suppliers.json'), true);
        if (!is_array($suppliers)) { return; }

        foreach ($suppliers as $supplier) {
            $name = strtolower(trim((string) ($supplier['name'] ?? '')));
            if ($name === '') { continue; }

            $active = $supplier['active'] ?? null;
            $status = strtolower(trim((string) ($supplier['poo_status'] ?? '')));

            if ((string) $active === '0' || $status === 'inactive') {
                $this->inactiveNames[$name] = true;
            }
        }
    }
}
