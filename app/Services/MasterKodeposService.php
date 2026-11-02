<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

class MasterKodeposService
{
    /**
     * Search kodepos, subdistrict, district, city, province.
     *
     * @param string $keyword
     * @param int $limit
     * @return array
     */
    public function search(string $keyword, int $limit = 20): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $results = DB::table('master_kodepos')
            ->where(function($q) use ($keyword) {
                $q->where('ZipCode', 'like', "{$keyword}%")
                  ->orWhere('City', 'like', "%{$keyword}%")
                  ->orWhere('District', 'like', "%{$keyword}%")
                  ->orWhere('Sub_District', 'like', "%{$keyword}%")
                  ->orWhere('Province', 'like', "%{$keyword}%");
            })
            ->limit($limit)
            ->get(['ZipCode', 'Province', 'City', 'District', 'Sub_District']);

        return $results->map(function($item) {
            return [
                'zipcode'      => (string) $item->ZipCode,
                'province'     => $item->Province,
                'city'         => $item->City,
                'district'     => $item->District,
                'sub_district' => $item->Sub_District,
                'label'        => "{$item->ZipCode} - {$item->Sub_District}, {$item->District}, {$item->City}, {$item->Province}",
            ];
        })->toArray();
    }

    /**
     * Get distinct provinces.
     *
     * @return array
     */
    public function getProvinces(): array
    {
        return DB::table('master_kodepos')
            ->whereNotNull('Province')
            ->where('Province', '!=', '')
            ->distinct()
            ->orderBy('Province', 'ASC')
            ->pluck('Province')
            ->toArray();
    }

    /**
     * Get cities by province.
     *
     * @param string $province
     * @return array
     */
    public function getCitiesByProvince(string $province): array
    {
        return DB::table('master_kodepos')
            ->where('Province', $province)
            ->whereNotNull('City')
            ->where('City', '!=', '')
            ->distinct()
            ->orderBy('City', 'ASC')
            ->pluck('City')
            ->toArray();
    }

    /**
     * Get districts by province and city.
     *
     * @param string $province
     * @param string $city
     * @return array
     */
    public function getDistricts(string $province, string $city): array
    {
        return DB::table('master_kodepos')
            ->where('Province', $province)
            ->where('City', $city)
            ->whereNotNull('District')
            ->where('District', '!=', '')
            ->distinct()
            ->orderBy('District', 'ASC')
            ->pluck('District')
            ->toArray();
    }

    /**
     * Get subdistricts (kelurahan/desa) by province, city, and district.
     *
     * @param string $province
     * @param string $city
     * @param string $district
     * @return array
     */
    public function getSubDistricts(string $province, string $city, string $district): array
    {
        return DB::table('master_kodepos')
            ->where('Province', $province)
            ->where('City', $city)
            ->where('District', $district)
            ->whereNotNull('Sub_District')
            ->where('Sub_District', '!=', '')
            ->distinct()
            ->orderBy('Sub_District', 'ASC')
            ->get(['Sub_District', 'ZipCode'])
            ->map(function($item) {
                return [
                    'sub_district' => $item->Sub_District,
                    'zipcode'      => (string) $item->ZipCode,
                ];
            })
            ->toArray();
    }

    /**
     * Get postal details by zipcode.
     *
     * @param string $zipcode
     * @return array
     */
    public function getDetailsByZipcode(string $zipcode): array
    {
        return DB::table('master_kodepos')
            ->where('ZipCode', $zipcode)
            ->get(['ZipCode', 'Province', 'City', 'District', 'Sub_District'])
            ->map(function($item) {
                return [
                    'zipcode'      => (string) $item->ZipCode,
                    'province'     => $item->Province,
                    'city'         => $item->City,
                    'district'     => $item->District,
                    'sub_district' => $item->Sub_District,
                ];
            })
            ->toArray();
    }
}

