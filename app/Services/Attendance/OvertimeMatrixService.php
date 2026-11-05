<?php

namespace App\Services\Attendance;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class OvertimeMatrixService
{
    /**
     * Tentukan Wilayah (WILAYAH_1 atau WILAYAH_2) berdasarkan Zipcode lokasi kerja
     * Menggunakan tabel master_kodepos
     *
     * @param string|null $zipcode
     * @param int|null $locationId
     * @return string 'WILAYAH_1' | 'WILAYAH_2'
     */
    public function resolveRegion(?string $zipcode = null, ?int $locationId = null): string
    {
        // 1. Cek langsung jika pada attendance_locations sudah diset manual
        if ($locationId) {
            $loc = DB::table('attendance_locations')->where('id', $locationId)->first();
            if ($loc) {
                if (!empty($loc->region_group)) {
                    return strtoupper(trim($loc->region_group));
                }
                if (empty($zipcode) && !empty($loc->zipcode)) {
                    $zipcode = $loc->zipcode;
                }
            }
        }

        if (empty($zipcode)) {
            // Default Wilayah 1 jika tidak ada zipcode
            return 'WILAYAH_1';
        }

        // 2. Query ke master_kodepos
        $kodepos = DB::table('master_kodepos')->where('ZipCode', $zipcode)->first();
        if (!$kodepos) {
            // Coba prefix 3 digit zipcode jika exact match tidak ditemukan
            $kodepos = DB::table('master_kodepos')->where('ZipCode', 'like', substr($zipcode, 0, 3) . '%')->first();
        }

        if (!$kodepos) {
            return 'WILAYAH_1';
        }

        $province = strtoupper($kodepos->Province ?? '');
        $city = strtoupper($kodepos->City ?? '');
        $district = strtoupper($kodepos->District ?? '');
        $fullLocationText = "{$province} {$city} {$district}";

        // 3. Evaluasi Dinamis Terhadap Seluruh Wilayah Aktif di overtime_regions
        $regions = DB::table('overtime_regions')
            ->where('status', 'ACTIVE')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($regions as $r) {
            if (!empty($r->coverage_keywords)) {
                $keywords = array_filter(array_map('trim', explode(',', $r->coverage_keywords)));
                foreach ($keywords as $kw) {
                    if (!empty($kw) && stripos($fullLocationText, $kw) !== false) {
                        return strtoupper(trim($r->region_code));
                    }
                }
            }
        }

        // Fallback default jika tidak ada keyword cocok
        return 'WILAYAH_2';
    }

    /**
     * Tentukan Matriks Tarif yang cocok berdasarkan User dan Wilayah
     *
     * @param object $user
     * @param string $region 'WILAYAH_1' | 'WILAYAH_2'
     * @return object|null
     */
    public function resolveMatrix($user, string $region = 'WILAYAH_1')
    {
        $jabatan = trim($user->Position ?? ($user->title ?? ($user->Department ?? '')));
        $matrices = DB::table('overtime_rate_matrices')
            ->where('status', 'ACTIVE')
            ->orderBy('id', 'asc')
            ->get();

        // 1. Prioritas Pertama: Pencocokan Jabatan / Job Title spesifik pada daftar matriks aktif
        foreach ($matrices as $m) {
            if (!empty($m->job_titles)) {
                $titles = is_array($m->job_titles) ? $m->job_titles : json_decode($m->job_titles, true);
                if (!is_array($titles)) {
                    $titles = array_map('trim', explode(',', $m->job_titles));
                }
                foreach ($titles as $t) {
                    if (!empty($t) && stripos($jabatan, trim($t)) !== false) {
                        // Jika matriks mensyaratkan wilayah tertentu, cocokkan wilayahnya
                        if ($m->region_type === 'ALL' || empty($m->region_type) || $m->region_type === $region) {
                            return $m;
                        }
                    }
                }
            }
        }

        // 2. Prioritas Kedua: Pencocokan Wilayah untuk Staff / SPV / Kategori Umum
        $regionMatrix = $matrices->first(function ($m) use ($region) {
            return $m->region_type === $region;
        });

        if ($regionMatrix) {
            return $regionMatrix;
        }

        // 3. Fallback ke matriks bertipe ALL atau matriks aktif pertama yang tersedia
        return $matrices->firstWhere('region_type', 'ALL') ?: $matrices->first();
    }

    /**
     * Hitung durasi dan nominal lembur sesuai matriks
     *
     * @param string $startTime 'HH:mm' atau 'HH:mm:ss'
     * @param string $endTime 'HH:mm' atau 'HH:mm:ss'
     * @param object|null $matrix
     * @param string $compensationType 'PAID' | 'TIME_OFF'
     * @return array
     */
    public function calculateOvertime(string $startTime, string $endTime, $matrix, string $compensationType = 'PAID'): array
    {
        $start = Carbon::parse('2000-01-01 ' . $startTime);
        $end = Carbon::parse('2000-01-01 ' . $endTime);

        // Jika melewati tengah malam
        if ($end->lt($start)) {
            $end->addDay();
        }

        $durationMinutes = $start->diffInMinutes($end);
        $durationHours = round($durationMinutes / 60, 2);

        // Jika kompensasi adalah Tukar Hari Libur (Time Off), nominal uang adalah 0
        if (strtoupper($compensationType) === 'TIME_OFF') {
            return [
                'duration_minutes'  => $durationMinutes,
                'duration_hours'    => $durationHours,
                'compensation_type' => 'TIME_OFF',
                'calculated_amount' => 0.00,
                'formatted_amount'  => 'Rp 0 (Kompensasi Hari Libur / Day Off)',
                'breakdown'         => 'Kompensasi diganti menjadi hak cuti / day off.',
                'matrix'            => $matrix,
            ];
        }

        if (!$matrix) {
            return [
                'duration_minutes'  => $durationMinutes,
                'duration_hours'    => $durationHours,
                'compensation_type' => 'PAID',
                'calculated_amount' => 0.00,
                'formatted_amount'  => 'Rp 0',
                'breakdown'         => 'Matriks lembur tidak ditemukan.',
                'matrix'            => null,
            ];
        }

        $tiers = DB::table('overtime_matrix_tiers')
            ->where('matrix_id', $matrix->id)
            ->orderBy('tier_order', 'asc')
            ->get();

        $amount = 0.00;
        $breakdown = '';

        if ($matrix->pricing_type === 'FLAT_TASK') {
            // WHL / Coor WH: Rp 50.000 flat per penugasan
            $tier = $tiers->first();
            $amount = $tier ? (float) $tier->base_rate : 50000.00;
            $breakdown = "Tarif flat per penugasan: Rp " . number_format($amount, 0, ',', '.');
        } elseif ($matrix->pricing_type === 'MINUTE_RANGE') {
            // Kru Gudang, Driver, OB, QC: Range Menit
            $matchedTier = null;
            foreach ($tiers as $tier) {
                $min = (int) $tier->min_minutes;
                $max = $tier->max_minutes !== null ? (int) $tier->max_minutes : 999999;
                if ($durationMinutes >= $min && $durationMinutes <= $max) {
                    $matchedTier = $tier;
                    break;
                }
            }

            if ($matchedTier) {
                $amount = (float) $matchedTier->base_rate;
                $breakdown = "Range durasi {$matchedTier->tier_label}: Rp " . number_format($amount, 0, ',', '.');
            } else {
                // Default jika lebih dari 6 jam 31 menit
                $lastTier = $tiers->last();
                $amount = $lastTier ? (float) $lastTier->base_rate : 50000.00;
                $breakdown = "Melebihi tier maksimum: Rp " . number_format($amount, 0, ',', '.');
            }
        } else {
            // TIERED_HOURLY: Staff - SPV (Maksimal 8 Jam)
            // 4 jam pertama flat base_rate, selanjutnya per jam next_per_hour_rate
            $maxHours = (float) ($matrix->max_hours ?? 8.00);
            $effectiveHours = min($durationHours, $maxHours);

            $tier1 = $tiers->firstWhere('tier_order', 1);
            $tier2 = $tiers->firstWhere('tier_order', 2);

            $baseRate = $tier1 ? (float) $tier1->base_rate : 100000.00;
            $nextRate = $tier2 ? (float) $tier2->next_per_hour_rate : 15000.00;

            if ($effectiveHours <= 4.00) {
                $amount = $baseRate;
                $breakdown = "4 jam pertama (durasi {$effectiveHours} jam): Rp " . number_format($amount, 0, ',', '.');
            } else {
                $additionalHours = ceil($effectiveHours - 4.00);
                $additionalAmount = $additionalHours * $nextRate;
                $amount = $baseRate + $additionalAmount;
                $breakdown = "4 jam pertama (Rp " . number_format($baseRate, 0, ',', '.') . ") + {$additionalHours} jam berikutnya (Rp " . number_format($additionalAmount, 0, ',', '.') . ")";
            }

            if ($durationHours > $maxHours) {
                $breakdown .= " [Dibatasi batas maksimal {$maxHours} jam]";
            }
        }

        return [
            'duration_minutes'  => $durationMinutes,
            'duration_hours'    => $durationHours,
            'compensation_type' => 'PAID',
            'calculated_amount' => $amount,
            'formatted_amount'  => 'Rp ' . number_format($amount, 0, ',', '.'),
            'breakdown'         => $breakdown,
            'matrix'            => $matrix,
        ];
    }
}

