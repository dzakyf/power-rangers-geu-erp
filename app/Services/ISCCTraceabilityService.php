<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ISCCTraceabilityService
{
    const MIN_FRAGMENT_QTY = 225;
    const MIN_TOTAL_QTY = 2000;
    const PHOTO_TOPIC = 'TRACEABILITY_EXTERNAL';
    const PHOTO_MAX = 3;

    public function TraceabilityFragmentationService(int $deliveryId): array
    {
        $delivery = DB::table('traceability_external_deliveries')->find($deliveryId);

        if (!$delivery) {
            throw new \InvalidArgumentException("Delivery #{$deliveryId} not found.");
        }

        if ($delivery->status !== 'PENDING') {
            throw new \RuntimeException("Delivery #{$deliveryId} is already {$delivery->status}. Regeneration is not allowed.");
        }

        $totalQty = (float) $delivery->total_quantity_kg;
        if ($totalQty < self::MIN_TOTAL_QTY) {
            throw new \RuntimeException("Total quantity {$totalQty} kg is below the minimum of " . self::MIN_TOTAL_QTY . " kg for fragmentation.");
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

    protected function generateFragments(object $delivery): array
    {
        $totalQty = (float) $delivery->total_quantity_kg;
        $maxFragments = (int) floor($totalQty / self::MIN_FRAGMENT_QTY);
        $target = $totalQty / $maxFragments;

        $seed = crc32("{$delivery->supplier_name}:{$delivery->delivery_date}:{$totalQty}");
        mt_srand($seed);

        $timestamp = strtotime($delivery->delivery_date);
        $dateSuffix = strtoupper(base_convert($timestamp, 10, 36));
        $yearMonth = date('Ym', $timestamp);

        $fragments = [];
        $remaining = $totalQty;

        for ($i = 1; $i <= $maxFragments; $i++) {
            $minRemainingForOthers = ($maxFragments - $i) * self::MIN_FRAGMENT_QTY;

            if ($i === $maxFragments) {
                $qty = $remaining;
            } else {
                $variation = mt_rand(-20, 20);
                $qty = max(self::MIN_FRAGMENT_QTY, round($target + $variation));
                $qty = min($qty, $remaining - $minRemainingForOthers);
            }

            $paddedSeq = str_pad($i, 5, '0', STR_PAD_LEFT);

            $fragments[] = [
                'traceability_external_delivery_id' => $delivery->id,
                'fragment_sequence' => $i,
                'poo_name' => "{$delivery->supplier_name} {$i}",
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

            $remaining -= $qty;
        }

        return $fragments;
    }
}
