<?php

namespace App\Services\CNS;

use App\CnsFlexibagInventory;
use App\CnsInventoryNonUco;
use App\VesselSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FlexibagInventoryService
{
    protected $nonUcoService;

    public function __construct(NonUcoInventoryService $nonUcoService)
    {
        $this->nonUcoService = $nonUcoService;
    }

    /**
     * Record flexibag deduction when stuffing Done/Kapal Berangkat is completed.
     * Only applies when Shipping_Method is CONTAINER.
     */
    public function recordStuffingDeduction(VesselSchedule $schedule, ?string $actor = null)
    {
        if (Schema::hasTable('cns_inventory_non_uco')) {
            return $this->nonUcoService->recordStuffingDeduction($schedule, $actor);
        }

        // Fallback for older table if exists
        $method = strtoupper((string) ($schedule->Shipping_Method ?? ''));
        $isContainer = $method === 'CONTAINER' || $method === '';

        if (!$isContainer) {
            return null;
        }

        $existing = CnsFlexibagInventory::where('vessel_schedule_id', $schedule->id)
            ->where('type', 'OUT')
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

        return DB::transaction(function () use ($schedule, $qtyOut, $actor) {
            $latest = CnsFlexibagInventory::orderBy('id', 'desc')->lockForUpdate()->first();
            $lastBalance = $latest ? (float) $latest->current_balance : 0.0;
            $newBalance = $lastBalance - $qtyOut;

            $reference = $schedule->Booking_No ?: ($schedule->Order_No ?: ($schedule->Contract_No ?: ('VS-' . $schedule->id)));
            $notes = 'Pengeluaran Stuffing Selesai (Order: ' . ($schedule->Order_No ?: '-') . ', Booking: ' . ($schedule->Booking_No ?: '-') . ')';

            return CnsFlexibagInventory::create([
                'transaction_date'   => now()->toDateString(),
                'vessel_schedule_id' => $schedule->id,
                'reference_no'       => $reference,
                'type'               => 'OUT',
                'qty_in'             => 0,
                'qty_out'            => $qtyOut,
                'current_balance'    => $newBalance,
                'notes'              => $notes,
                'created_by'         => $actor ?: (auth()->check() ? auth()->user()->username : 'system'),
            ]);
        });
    }

    /**
     * Get the latest balance.
     */
    public function getLatestBalance(): float
    {
        if (Schema::hasTable('cns_inventory_non_uco')) {
            return $this->nonUcoService->getLatestBalance('Flexibag 24KL');
        }

        $latest = CnsFlexibagInventory::orderBy('id', 'desc')->first();
        return $latest ? (float) $latest->current_balance : 0.0;
    }
}
