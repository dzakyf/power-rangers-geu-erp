<?php

namespace App\Services\CNS;

use Illuminate\Support\Facades\DB;

class CnsCodeGenerator
{
    private const ROMANS = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    public static function nextVsNo(?string $date = null, bool $lock = false): string
    {
        return self::nextMonthly('vessel_schedules', 'VS_No', '', '/VS-GEU/', $date, $lock);
    }

    public static function nextStuffingNo(?string $date = null, bool $lock = false): string
    {
        return self::nextMonthly('vessel_schedule_dtls', 'Stuffing_No', '', '/STF-GEU/', $date, $lock);
    }

    public static function nextCooNo(?string $date = null, bool $lock = false): string
    {
        return self::nextMonthly('certificate_of_origin', 'COO_No', 'COO-GEU/', '/', $date, $lock);
    }

    public static function nextDnNo(?string $date = null, bool $lock = false): string
    {
        return self::nextMonthly('delivery_notes', 'DN_No', '', '/DN-GEU/', $date, $lock);
    }

    public static function nextAnnexNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $roman = self::roman((int) date('n', $ts));
        $year = date('Y', $ts);

        $existing = self::fetchExisting('annex_vii', 'Annex_No', "%/ANX/%/{$year}", $lock);
        $pattern = '/^(\d+)\/ANX\/[IVX]+\/' . preg_quote($year, '/') . '$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($roman, $year) {
            return sprintf('%03d', $seq) . "/ANX/{$roman}/{$year}";
        });
    }

    public static function nextSdNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $year = date('Y', $ts);
        $ymd = date('Ymd', $ts);

        $existing = self::fetchExisting('sustainability_declarations', 'SD_No', "{$year}%", $lock);
        $pattern = '/^' . preg_quote($year, '/') . '(\d{4})\d{8}$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($year, $ymd) {
            return $year . sprintf('%04d', $seq) . $ymd;
        });
    }

    public static function nextDrainingNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $dateStr = date('Ymd', $ts);
        $prefix = "DRN-{$dateStr}-";

        $existing = self::fetchExisting('cns_tank_drainings', 'draining_code', "{$prefix}%", $lock);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($prefix) {
            return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    public static function nextSludgeMovementNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $dateStr = date('Ymd', $ts);
        $prefix = "MOV-{$dateStr}-";

        $existing = self::fetchExisting('cns_sludge_movements', 'movement_code', "{$prefix}%", $lock);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($prefix) {
            return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    public static function nextInboundAdjNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $dateStr = date('Ymd', $ts);
        $prefix = "IADJ-{$dateStr}-";

        $existing = self::fetchExisting('cns_inbound_adjustments', 'adjustment_code', "{$prefix}%", $lock);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($prefix) {
            return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    public static function nextDrainAdjNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $dateStr = date('Ymd', $ts);
        $prefix = "DADJ-{$dateStr}-";

        $existing = self::fetchExisting('cns_drain_adjustments', 'adjustment_code', "{$prefix}%", $lock);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($prefix) {
            return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    public static function nextSuratJalanNo(?string $date = null, bool $lock = false): string
    {
        $ts = self::timestamp($date);
        $prefix = 'SJ-' . date('Ym', $ts) . '-';

        $existing = self::fetchExisting('sales_order_cns', 'surat_jalan_no', "{$prefix}%", $lock);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($prefix) {
            return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Compute the next formatted code from existing values using MAX(sequence) + 1.
     */
    public static function nextFromExisting(array $existing, string $pattern, callable $formatter): string
    {
        $max = 0;
        $used = [];

        foreach ($existing as $value) {
            $trimmed = trim((string) $value);
            if ($trimmed === '') {
                continue;
            }
            $used[$trimmed] = true;
            if (preg_match($pattern, $trimmed, $m)) {
                $num = (int) $m[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $next = $max + 1;
        do {
            $candidate = $formatter($next);
            $next++;
        } while (isset($used[$candidate]));

        return $candidate;
    }

    private static function nextMonthly(
        string $table,
        string $column,
        string $prefix,
        string $midfix,
        ?string $date,
        bool $lock
    ): string {
        $ts = self::timestamp($date);
        $roman = self::roman((int) date('n', $ts));
        $year = date('Y', $ts);
        $suffix = "{$midfix}{$roman}/{$year}";

        $existing = self::fetchExisting($table, $column, $prefix . '%' . $suffix, $lock);
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';

        return self::nextFromExisting($existing, $pattern, static function (int $seq) use ($prefix, $suffix) {
            return $prefix . sprintf('%03d', $seq) . $suffix;
        });
    }

    private static function fetchExisting(string $table, string $column, string $like, bool $lock): array
    {
        $query = DB::table($table)
            ->whereNotNull($column)
            ->where($column, 'like', $like);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->pluck($column)->all();
    }

    private static function roman(int $month): string
    {
        return self::ROMANS[$month] ?? 'I';
    }

    private static function timestamp(?string $date): int
    {
        if ($date !== null && trim($date) !== '') {
            $ts = strtotime($date);
            if ($ts !== false) {
                return $ts;
            }
        }

        return time();
    }
}
