<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUnitColumnsToPickupOrder extends Migration
{
    public function up()
    {
        Schema::table('pickup_order', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_order', 'Qty_Unit')) {
                $table->string('Qty_Unit', 10)->default('L');
            }
            if (!Schema::hasColumn('pickup_order', 'Price_Unit')) {
                $table->string('Price_Unit', 10)->default('L');
            }
            if (!Schema::hasColumn('pickup_order', 'Package_Capacity')) {
                $table->decimal('Package_Capacity', 12, 2)->nullable();
            }
        });

        $this->backfillUnits();
    }

    public function down()
    {
        Schema::table('pickup_order', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_order', 'Package_Capacity')) {
                $table->dropColumn('Package_Capacity');
            }
            if (Schema::hasColumn('pickup_order', 'Price_Unit')) {
                $table->dropColumn('Price_Unit');
            }
            if (Schema::hasColumn('pickup_order', 'Qty_Unit')) {
                $table->dropColumn('Qty_Unit');
            }
        });
    }

    /**
     * Preserve legacy behavior for ambiguous old rows. A package row is only
     * marked as PCS when Actual_Qty proves it (Actual_Qty = Qty x capacity).
     */
    private function backfillUnits(): void
    {
        if (!Schema::hasTable('pickup_order') || !Schema::hasTable('master_packaging')) {
            return;
        }

        $capacities = [];
        foreach (DB::table('master_packaging')->get(['Name', 'Capacity']) as $packaging) {
            $name = trim((string) $packaging->Name);
            if ($name !== '') {
                $capacities[$name] = (float) $packaging->Capacity;
            }
        }

        DB::table('pickup_order')->orderBy('ID')->chunkById(200, function ($rows) use ($capacities) {
            foreach ($rows as $row) {
                $name = trim((string) $row->Package);
                $capacity = $capacities[$name] ?? null;
                if ($capacity === null || $capacity <= 0) {
                    continue;
                }

                $qty = (float) $row->Qty;
                $actual = (float) $row->Actual_Qty;
                $isPackage = $capacity > 1 && $actual > 0
                    && abs($actual - ($qty * $capacity)) <= max(0.5, $actual * 0.02);

                DB::table('pickup_order')->where('ID', $row->ID)->update([
                    'Qty_Unit'         => $isPackage ? 'PCS' : 'L',
                    'Price_Unit'       => $isPackage ? 'PCS' : 'L',
                    'Package_Capacity' => $capacity,
                ]);
            }
        }, 'ID');
    }
}
