<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBcNoToCnsShipmentCostAllocations extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_shipment_cost_allocations')) {
            return;
        }

        Schema::table('cns_shipment_cost_allocations', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_shipment_cost_allocations', 'bc_no')) {
                $table->string('bc_no', 150)->nullable()->unique()->after('allocation_no');
            }
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true) && Schema::hasColumn('cns_shipment_cost_allocations', 'si_no')) {
            DB::statement('ALTER TABLE `cns_shipment_cost_allocations` MODIFY COLUMN `si_no` VARCHAR(150) NULL');
        }
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
