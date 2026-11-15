<?php

namespace App\Services\HCGA;

use Illuminate\Support\Facades\DB;

class MasterLocationService
{
    /**
     * Ambil daftar master lokasi dari tabel `location`.
     *
     * @param string|null $search
     * @return \Illuminate\Support\Collection
     */
    public function listLocations($search = null)
    {
        $query = DB::table('location');

        if ($search !== null && trim((string) $search) !== '') {
            $term = '%' . trim((string) $search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('Name', 'like', $term)
                    ->orWhere('Location_ID', 'like', $term)
                    ->orWhere('Location_Code', 'like', $term)
                    ->orWhere('Location_Type', 'like', $term)
                    ->orWhere('Company_ID', 'like', $term)
                    ->orWhere('Address', 'like', $term)
                    ->orWhere('Street', 'like', $term)
                    ->orWhere('City', 'like', $term)
                    ->orWhere('PIC', 'like', $term);
            });
        }

        return $query->orderBy('ID', 'desc')->get();
    }

    /**
     * Simpan lokasi baru ke tabel `location` (Location_ID otomatis HO-{n}/BR-{n} jika kosong).
     *
     * @param array $data
     * @param object|null $user
     * @return int
     */
    public function createLocation(array $data, $user = null)
    {
        return DB::transaction(function () use ($data, $user) {
            $type = !empty($data['Location_Type']) ? trim((string) $data['Location_Type']) : 'Branch Office';
            $locId = trim((string) ($data['Location_ID'] ?? ''));
            if ($locId === '') {
                $locId = $this->generateLocationId($type);
            }

            $defaultCompany = ($user && !empty($user->Company_ID)) ? $user->Company_ID : 'GEU';
            $company = trim((string) ($data['Company_ID'] ?? ''));
            $company = $company !== '' ? $company : $defaultCompany;

            $country = trim((string) ($data['IDCountry'] ?? ''));
            $country = $country !== '' ? $country : 'IDN';

            $username = ($user && !empty($user->username)) ? $user->username : 'system';

            return DB::table('location')->insertGetId([
                'Company_ID'    => $company,
                'Location_Type' => $type,
                'Location_ID'   => $locId,
                'Name'          => trim((string) $data['Name']),
                'Address'       => trim((string) ($data['Address'] ?? '')),
                'Street'        => trim((string) ($data['Street'] ?? '')),
                'City'          => trim((string) ($data['City'] ?? '')),
                'Phone'         => trim((string) ($data['Phone'] ?? '')),
                'Phone_Ext'     => trim((string) ($data['Phone_Ext'] ?? '')),
                'Fax'           => trim((string) ($data['Fax'] ?? '')),
                'PIC'           => trim((string) ($data['PIC'] ?? '')),
                'IDCity'        => trim((string) ($data['IDCity'] ?? '')),
                'IDCountry'     => $country,
                'ZipCode'       => trim((string) ($data['ZipCode'] ?? '')),
                'MainCity'      => trim((string) ($data['MainCity'] ?? '')),
                'Longitude'     => trim((string) ($data['Longitude'] ?? '')),
                'Latitude'      => trim((string) ($data['Latitude'] ?? '')),
                'UpdateBy'      => $username,
                'Location_Code' => trim((string) ($data['Location_Code'] ?? '')),
            ]);
        });
    }

    /**
     * Perbarui data lokasi berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @param object|null $user
     * @return bool
     */
    public function updateLocation($id, array $data, $user = null)
    {
        $existing = DB::table('location')->where('ID', (int) $id)->first();
        if (!$existing) {
            return false;
        }

        $type = !empty($data['Location_Type']) ? trim((string) $data['Location_Type']) : 'Branch Office';
        $locId = trim((string) ($data['Location_ID'] ?? ''));
        $locId = $locId !== '' ? $locId : $existing->Location_ID;

        $company = trim((string) ($data['Company_ID'] ?? ''));
        $company = $company !== '' ? $company : $existing->Company_ID;

        $country = trim((string) ($data['IDCountry'] ?? ''));
        $country = $country !== '' ? $country : ($existing->IDCountry ?: 'IDN');

        $username = ($user && !empty($user->username)) ? $user->username : 'system';

        DB::table('location')->where('ID', (int) $id)->update([
            'Company_ID'    => $company,
            'Location_Type' => $type,
            'Location_ID'   => $locId,
            'Name'          => trim((string) $data['Name']),
            'Address'       => trim((string) ($data['Address'] ?? '')),
            'Street'        => trim((string) ($data['Street'] ?? '')),
            'City'          => trim((string) ($data['City'] ?? '')),
            'Phone'         => trim((string) ($data['Phone'] ?? '')),
            'Phone_Ext'     => trim((string) ($data['Phone_Ext'] ?? '')),
            'Fax'           => trim((string) ($data['Fax'] ?? '')),
            'PIC'           => trim((string) ($data['PIC'] ?? '')),
            'IDCity'        => trim((string) ($data['IDCity'] ?? '')),
            'IDCountry'     => $country,
            'ZipCode'       => trim((string) ($data['ZipCode'] ?? '')),
            'MainCity'      => trim((string) ($data['MainCity'] ?? '')),
            'Longitude'     => trim((string) ($data['Longitude'] ?? '')),
            'Latitude'      => trim((string) ($data['Latitude'] ?? '')),
            'UpdateBy'      => $username,
            'Location_Code' => trim((string) ($data['Location_Code'] ?? '')),
        ]);

        return true;
    }

    /**
     * Hapus data lokasi berdasarkan ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteLocation($id)
    {
        $deleted = DB::table('location')->where('ID', (int) $id)->delete();

        return $deleted > 0;
    }

    /**
     * Generate Location_ID otomatis dengan format HO-{n} atau BR-{n}.
     *
     * @param string $type
     * @return string
     */
    public function generateLocationId($type)
    {
        $prefix = (stripos((string) $type, 'head') !== false) ? 'HO' : 'BR';

        $max = 0;
        $codes = DB::table('location')->where('Location_ID', 'like', $prefix . '-%')->pluck('Location_ID');
        foreach ($codes as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $prefix . '-' . ($max + 1);
    }
}
