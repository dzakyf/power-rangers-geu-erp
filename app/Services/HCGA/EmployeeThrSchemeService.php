<?php

namespace App\Services\HCGA;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeThrSchemeService
{
    public const SCHEMES = [
        'FULL_UPAH'       => 'Full 1 Bulan Upah (THP Bruto)',
        'PRORATE_UPAH'    => 'Prorate 1 Bulan Upah (THP Bruto x Masa Kerja / 12)',
        'FULL_GAPOK'      => 'Full 1 Bulan Gapok',
        'PRORATE_GAPOK'   => 'Prorate 1 Bulan Gapok (Gapok x Masa Kerja / 12)',
        'NO_THR_UNDER_12M'=> 'Kontrak Sebelum 12 Bulan Tidak Dapat',
    ];

    public static function schemeOptions(): array
    {
        return self::SCHEMES;
    }

    public static function label(?string $scheme): string
    {
        if (empty($scheme) || strtoupper(trim($scheme)) === 'DEFAULT_PERMENAKER') {
            return 'Default (Standar Permenaker)';
        }
        return self::SCHEMES[$scheme] ?? $scheme;
    }

    /**
     * Update skema THR (PKWT/MoU) untuk satu atau banyak karyawan (berdasarkan Task_ID atau ID).
     *
     * @param array $employeeKeys
     * @param string|null $scheme
     * @param string|null $contractNote
     * @param string $operator
     * @return int
     */
    public function updateSchemes(array $employeeKeys, ?string $scheme, ?string $contractNote, string $operator): int
    {
        $keys = array_values(array_unique(array_filter(array_map('trim', array_map('strval', $employeeKeys)), function ($v) {
            return $v !== '';
        })));

        if (empty($keys)) {
            throw new \InvalidArgumentException('Pilih minimal satu karyawan terlebih dahulu.');
        }

        $normalizedScheme = $scheme !== null ? strtoupper(trim($scheme)) : null;
        if ($normalizedScheme === '') {
            $normalizedScheme = null;
        }

        if ($normalizedScheme !== null && !array_key_exists($normalizedScheme, self::SCHEMES)) {
            throw new \InvalidArgumentException('Skema THR tidak valid.');
        }

        return DB::transaction(function () use ($keys, $normalizedScheme, $contractNote, $operator) {
            return DB::table('employment')
                ->where(function ($q) use ($keys) {
                    $q->whereIn('Task_ID', $keys)->orWhereIn('ID', $keys);
                })
                ->update([
                    'thr_scheme'        => $normalizedScheme,
                    'thr_contract_note' => $contractNote !== null ? trim($contractNote) : null,
                    'thr_updated_by'    => $operator,
                    'thr_updated_at'    => Carbon::now(),
                ]);
        });
    }
}
