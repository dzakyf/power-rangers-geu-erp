<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSoTypeAndDrainingToSalesOrderCnsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sales_order_cns')) {
            return;
        }

        Schema::table('sales_order_cns', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_order_cns', 'so_type')) {
                $table->string('so_type', 30)->default('KEMASAN_AMPAS')->after('so_no');
            }
            if (!Schema::hasColumn('sales_order_cns', 'draining_id')) {
                $table->unsignedBigInteger('draining_id')->nullable()->after('so_type');
            }
            if (!Schema::hasColumn('sales_order_cns', 'draining_code')) {
                $table->string('draining_code', 100)->nullable()->after('draining_id');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('sales_order_cns')) {
            return;
        }

        foreach (['so_type', 'draining_id', 'draining_code'] as $column) {
            $this->dropColumnIfExists('sales_order_cns', $column);
        }
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
            return;
        }

        DB::statement("ALTER TABLE \"{$table}\" DROP COLUMN \"{$column}\"");
    }
}
