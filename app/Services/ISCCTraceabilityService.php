<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ISCCTraceabilityService
{
    const SPLIT_THRESHOLD = 225;   // deliveries below this weight stay as a single POO
    const MAX_FRAGMENTS = 20;      // hard cap on fragments per delivery (outlier guard)
    const PHOTO_TOPIC = 'TRACEABILITY_EXTERNAL';
    const PHOTO_MAX = 3;

    const SPLIT_RANGES = [
        'restaurant' => [150, 750],
        'hotel'      => [300, 750],
        'irt'        => [5, 200],
        'kafe'       => [15, 100],
        'warung'     => [50, 500],
        'lembaga'    => [25, 300],
        'sppg'       => [150, 1000],
        'pabrik'     => [300, 1000],
    ];

    public function TraceabilityFragmentationService(int $deliveryId): array
    {
        $delivery = DB::table('traceability_external_deliveries')->find($deliveryId);

        if (!$delivery) {
            throw new \InvalidArgumentException("Delivery #{$deliveryId} not found.");
        }

        if ($delivery->status !== 'PENDING') {
            throw new \RuntimeException("Delivery #{$deliveryId} is already {$delivery->status}. Regeneration is not allowed.");
        }

        $fragments = $this->generateFragments($delivery);

        DB::transaction(function () use ($deliveryId, $fragments) {
            foreach ($fragments as $fragment) {
                DB::table('traceability_external_poo_fragments')->insert($fragment);
            }

            DB::table('traceability_external_deliveries')
                ->where('id', $deliveryId)
                ->update([
                    'status' => 'LOCKED',
                    'updated_at' => now(),
                ]);
        });

        return $fragments;
    }

    /**
     * Return the [min, max] split range for a normalized supplier type.
     * Unknown types default to the pabrik range [300, 1000].
     */
    public function rangeForType(?string $type): array
    {
        $key = strtolower(trim((string) $type));

        return self::SPLIT_RANGES[$key] ?? self::SPLIT_RANGES['pabrik'];
    }

    /**
     * Split a delivery total into fragment quantities using the type range.
     *
     * Rules:
     *  - total < SPLIT_THRESHOLD (225 kg)              -> single exact fragment
     *  - n = min(ceil(total / max), MAX_FRAGMENTS)     -> split count
     *  - n <= 1                                        -> single exact fragment (range ignored)
     *  - otherwise distribute so every part is within [min, max] when feasible
     *    (when the fragment cap binds, the max bound is relaxed and min is preserved)
     *  - sum of returned parts always equals the input total (mass balance)
     *
     * @return float[]
     */
    public function splitQuantity(float $total, ?string $type = null): array
    {
        $total = round((float) $total, 2);

        if ($total <= 0.0) {
            return [];
        }

        if ($total < self::SPLIT_THRESHOLD) {
            return [$total];
        }

        [$min, $max] = $this->rangeForType($type);

        $mid = ($min + $max) / 2;

        // Split-when-possible: any total at/above the threshold that can be broken
        // into 2+ in-range pieces (total >= 2 * min) must produce multiple POOs.
        // n is sized near the range midpoint so fragments land in [min, max]
        // (e.g. 1000 kg Restoran -> 3 fragments such as 150 + 150 + 700).
        if ($total < 2 * $min) {
            return [$total];
        }

        $n = max(2, (int) ceil($total / $mid));
        $n = min($n, self::MAX_FRAGMENTS);

        // feasibility: every fragment must be >= min, so n * min <= total
        $n = min($n, (int) floor($total / $min));

        if ($n <= 1) {
            return [$total];
        }

        $useMax = $total <= $n * $max;

        // work in 0.01 kg units to keep the sum exact
        $unit = 100;
        $totalInt = (int) round($total * $unit);
        $minInt = (int) round($min * $unit);
        $maxInt = (int) round($max * $unit);

        $parts = [];
        $remaining = $totalInt;

        for ($i = 0; $i < $n; $i++) {
            $rest = $n - $i - 1;

            if ($i === $n - 1) {
                $v = $remaining;
            } elseif ($useMax) {
                $low = max($minInt, $remaining - $rest * $maxInt);
                $high = min($maxInt, $remaining - $rest * $minInt);

                if ($high < $low) {
                    $high = $low;
                }

                $v = mt_rand($low, $high);
            } else {
                // fragment cap bound: distribute the remainder evenly with light jitter
                $avg = $remaining / ($rest + 1);
                $v = (int) round($avg * mt_rand(8500, 11500) / 10000);
                $v = max($minInt, min($v, $remaining - $rest * $minInt));
            }

            $parts[] = $v / $unit;
            $remaining -= $v;
        }

        return $parts;
    }

    protected function generateFragments(object $delivery): array
    {
        $totalQty = (float) $delivery->total_quantity_kg;
        $type = $delivery->poo_type ?? null;

        $qtys = $this->splitQuantity($totalQty, $type);

        $seed = crc32("{$delivery->supplier_name}:{$delivery->delivery_date}:{$totalQty}");
        mt_srand($seed);

        $timestamp = strtotime($delivery->delivery_date);
        $dateSuffix = strtoupper(base_convert($timestamp, 10, 36));
        $yearMonth = date('Ym', $timestamp);

        $fragments = [];
        $i = 1;

        foreach ($qtys as $qty) {
            $paddedSeq = str_pad($i, 5, '0', STR_PAD_LEFT);
            $name = count($qtys) > 1 ? "{$delivery->supplier_name} {$i}" : $delivery->supplier_name;

            $fragments[] = [
                'traceability_external_delivery_id' => $delivery->id,
                'fragment_sequence' => $i,
                'poo_name' => $name,
                'poo_type' => $type,
                'quantity_kg' => $qty,
                'city' => $delivery->city,
                'province' => $delivery->province,
                'wo_code' => "{$paddedSeq}/WO-SYN/{$yearMonth}{$dateSuffix}",
                'wo_date' => $delivery->delivery_date,
                'wo_status' => 'DONE',
                'po_code' => "{$paddedSeq}/PO-SYN/{$yearMonth}{$dateSuffix}",
                'po_date' => $delivery->delivery_date,
                'po_qty' => $qty,
                'po_status' => 'DONE',
                'do_code' => "{$paddedSeq}/DO-SYN/{$yearMonth}{$dateSuffix}",
                'do_date' => $delivery->delivery_date,
                'do_status' => 'DONE',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $i++;
        }

        return $fragments;
    }
}
