<?php

namespace App\Services\CNS;

use App\CnsInventoryNonUco;
use App\VesselSchedule;
use Illuminate\Support\Facades\DB;

class NonUcoInventoryService
{
    /**
     * Record stock mutation (IN or OUT) for any Non-UCO item.
     */
    public function recordStockMutation(
        string $itemName,
        ?string $category,
        string $unit,
        string $type,
        float $qty,
        string $referenceNo,
        ?string $notes = null,
        ?string $actor = null,
        ?int $vesselScheduleId = null,
        ?int $goodsReceiptId = null,
        ?string $itemCode = null,
        ?string $transactionDate = null
    ): CnsInventoryNonUco {
        $type = strtoupper($type);
        $qtyIn = $type === 'IN' ? $qty : 0.0;
        $qtyOut = $type === 'OUT' ? $qty : 0.0;
        $user = $actor ?: (auth()->check() ? auth()->user()->username : 'system');
        $date = $transactionDate ?: now()->toDateString();

        return DB::transaction(function () use (
            $itemName, $category, $unit, $type, $qtyIn, $qtyOut,
            $referenceNo, $notes, $user, $vesselScheduleId, $goodsReceiptId,
            $itemCode, $date
        ) {
            $latest = CnsInventoryNonUco::where('item_name', $itemName)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $lastBalance = $latest ? (float) $latest->current_balance : 0.0;
            $newBalance = $type === 'IN' ? ($lastBalance + $qtyIn) : ($lastBalance - $qtyOut);

            return CnsInventoryNonUco::create([
                'transaction_date'   => $date,
                'category'           => $category,
                'item_code'          => $itemCode,
                'item_name'          => $itemName,
                'unit'               => $unit ?: 'PCS',
                'vessel_schedule_id' => $vesselScheduleId,
                'goods_receipt_id'   => $goodsReceiptId,
                'reference_no'       => $referenceNo,
                'type'               => $type,
                'qty_in'             => $qtyIn,
                'qty_out'            => $qtyOut,
                'current_balance'    => $newBalance,
                'notes'              => $notes,
                'created_by'         => $user,
            ]);
        });
    }

    /**
     * Record flexibag deduction when stuffing Done/Kapal Berangkat is completed.
     * Only applies when Shipping_Method is CONTAINER.
     */
    public function recordStuffingDeduction(VesselSchedule $schedule, ?string $actor = null): ?CnsInventoryNonUco
    {
        $method = strtoupper((string) ($schedule->Shipping_Method ?? ''));
        $isContainer = $method === 'CONTAINER' || $method === '';

        if (!$isContainer) {
            return null;
        }

        // Avoid duplicate deduction for the same vessel schedule
        $existing = CnsInventoryNonUco::where('vessel_schedule_id', $schedule->id)
            ->where('type', 'OUT')
            ->where('category', 'Flexibag')
            ->first();

        if ($existing) {
            return $existing;
        }

        $qtyOut = (float) ($schedule->Qty1 ?? 0);
        if ($qtyOut <= 0 && !empty($schedule->Qty)) {
            if (preg_match('/^(\d+(\.\d+)?)/', (string) $schedule->Qty, $matches)) {
                $qtyOut = (float) $matches[1];
            }
        }

        if ($qtyOut <= 0) {
            return null;
        }

        $reference = $schedule->Booking_No ?: ($schedule->Order_No ?: ($schedule->Contract_No ?: ('VS-' . $schedule->id)));
        $notes = 'Pengeluaran Stuffing Selesai (Order: ' . ($schedule->Order_No ?: '-') . ', Booking: ' . ($schedule->Booking_No ?: '-') . ')';

        return $this->recordStockMutation(
            'Flexibag 24KL',
            'Flexibag',
            'PCS',
            'OUT',
            $qtyOut,
            $reference,
            $notes,
            $actor,
            $schedule->id,
            null,
            null,
            now()->toDateString()
        );
    }

    /**
     * Get the latest balance for a specific item.
     */
    public function getLatestBalance(string $itemName = 'Flexibag 24KL'): float
    {
        $latest = CnsInventoryNonUco::where('item_name', $itemName)
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? (float) $latest->current_balance : 0.0;
    }

    /**
     * Get latest balances grouped by item name and category.
     */
    public function getSummaryBalances(): array
    {
        $items = CnsInventoryNonUco::select('category', 'item_name', 'unit')
            ->groupBy('category', 'item_name', 'unit')
            ->get();

        $summaries = [];
        foreach ($items as $item) {
            $latest = CnsInventoryNonUco::where('item_name', $item->item_name)
                ->orderBy('id', 'desc')
                ->first();

            $summaries[] = [
                'category'        => $item->category ?: 'General',
                'item_name'       => $item->item_name,
                'unit'            => $item->unit ?: 'PCS',
                'current_balance' => $latest ? (float) $latest->current_balance : 0.0,
            ];
        }

        return $summaries;
    }
}
