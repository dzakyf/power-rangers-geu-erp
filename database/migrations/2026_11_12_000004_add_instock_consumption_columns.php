<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstockConsumptionColumns extends Migration
{
    public function up()
    {
        Schema::table('pickup_order', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_order', 'Instock_At')) {
                $table->dateTime('Instock_At')->nullable();
            }
            if (!Schema::hasColumn('pickup_order', 'Instock_By')) {
                $table->string('Instock_By', 150)->nullable();
            }
            if (!Schema::hasColumn('pickup_order', 'Instock_Batch_Ref')) {
                $table->string('Instock_Batch_Ref', 50)->nullable();
            }
        });

        Schema::table('pickup_order', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_order', 'Instock_At') && !$this->hasIndex('pickup_order', 'pickup_order_instock_at_index')) {
                $table->index('Instock_At', 'pickup_order_instock_at_index');
            }
            if (Schema::hasColumn('pickup_order', 'Instock_Batch_Ref') && !$this->hasIndex('pickup_order', 'pickup_order_instock_batch_ref_index')) {
                $table->index('Instock_Batch_Ref', 'pickup_order_instock_batch_ref_index');
            }
        });

        Schema::table('warehouse_partner_supplies', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_partner_supplies', 'Instock_Batch_Ref')) {
                $table->string('Instock_Batch_Ref', 50)->nullable();
            }
        });

        Schema::table('warehouse_partner_supplies', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_partner_supplies', 'Instock_Batch_Ref') && !$this->hasIndex('warehouse_partner_supplies', 'wps_instock_batch_ref_index')) {
                $table->index('Instock_Batch_Ref', 'wps_instock_batch_ref_index');
            }
        });
    }

    public function down()
    {
        Schema::table('warehouse_partner_supplies', function (Blueprint $table) {
            if (Schema::hasColumn('warehouse_partner_supplies', 'Instock_Batch_Ref')) {
                $table->dropIndex('wps_instock_batch_ref_index');
                $table->dropColumn('Instock_Batch_Ref');
            }
        });

        Schema::table('pickup_order', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_order', 'Instock_Batch_Ref')) {
                $table->dropIndex('pickup_order_instock_batch_ref_index');
            }
            if (Schema::hasColumn('pickup_order', 'Instock_At')) {
                $table->dropIndex('pickup_order_instock_at_index');
            }
            if (Schema::hasColumn('pickup_order', 'Instock_Batch_Ref')) {
                $table->dropColumn('Instock_Batch_Ref');
            }
            if (Schema::hasColumn('pickup_order', 'Instock_By')) {
                $table->dropColumn('Instock_By');
            }
            if (Schema::hasColumn('pickup_order', 'Instock_At')) {
                $table->dropColumn('Instock_At');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $rows = \Illuminate\Support\Facades\DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]);

        return !empty($rows);
    }
}
