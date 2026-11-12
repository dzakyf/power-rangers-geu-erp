<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockLimitsToKemasanTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cns_master_kemasan')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_master_kemasan', 'Is_Ampas')) {
                    $table->boolean('Is_Ampas')->default(false)->after('Volume');
                }
                if (!Schema::hasColumn('cns_master_kemasan', 'Max_Stock')) {
                    $table->decimal('Max_Stock', 12, 2)->nullable()->after('Is_Ampas');
                }
            });
        }

        if (Schema::hasTable('cns_inventory_kemasan')) {
            Schema::table('cns_inventory_kemasan', function (Blueprint $table) {
                if (!Schema::hasColumn('cns_inventory_kemasan', 'out_type')) {
                    $table->string('out_type', 20)->nullable()->after('type');
                }
                if (!Schema::hasColumn('cns_inventory_kemasan', 'qty_balance')) {
                    $table->decimal('qty_balance', 12, 2)->default(0)->after('current_balance');
                }
                if (!Schema::hasColumn('cns_inventory_kemasan', 'excess_balance')) {
                    $table->decimal('excess_balance', 12, 2)->default(0)->after('qty_balance');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('cns_master_kemasan')) {
            Schema::table('cns_master_kemasan', function (Blueprint $table) {
                foreach (['Max_Stock', 'Is_Ampas'] as $col) {
                    if (Schema::hasColumn('cns_master_kemasan', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('cns_inventory_kemasan')) {
            Schema::table('cns_inventory_kemasan', function (Blueprint $table) {
                foreach (['excess_balance', 'qty_balance', 'out_type'] as $col) {
                    if (Schema::hasColumn('cns_inventory_kemasan', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
