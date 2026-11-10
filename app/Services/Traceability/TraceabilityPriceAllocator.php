<?php

namespace App\Services\Traceability;

class TraceabilityPriceAllocator
{
    /**
     * Allocate a pickup item total across fragment quantities, preserving cents.
     *
     * @param float $total
     * @param float[] $quantities
     * @return float[]
     */
    public static function allocate(float $total, array $quantities): array
    {
        $count = count($quantities);
        if ($count === 0) {
            return [];
        }

        $totalCents = (int) round($total * 100);
        $weightTotal = max(0.0, array_sum($quantities));
        $allocations = [];
        $used = 0;

        foreach (array_values($quantities) as $i => $quantity) {
            if ($i === $count - 1) {
                $cents = $totalCents - $used;
            } elseif ($weightTotal > 0) {
                $cents = (int) round($totalCents * ((float) $quantity / $weightTotal));
                $used += $cents;
            } else {
                $cents = (int) round($totalCents / $count);
                $used += $cents;
            }

            $allocations[] = round($cents / 100, 2);
        }

        return $allocations;
    }
}
