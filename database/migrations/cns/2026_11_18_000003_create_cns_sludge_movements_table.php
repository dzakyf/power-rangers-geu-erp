<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsSludgeMovementsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cns_sludge_movements')) {
            Schema::create('cns_sludge_movements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('movement_code', 50)->unique();
                $table->date('movement_date');
                $table->unsignedBigInteger('draining_id')->index();
                $table->unsignedBigInteger('source_tank_id')->nullable()->index();
                $table->string('source_tank_name', 150)->nullable();
                $table->unsignedBigInteger('target_warehouse_id')->index();
                $table->string('target_warehouse_name', 150);
                $table->decimal('qty', 15, 2);
                $table->text('notes')->nullable();
                $table->string('created_by', 100)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('sales_order_cns') && !Schema::hasColumn('sales_order_cns', 'sludge_movement_id')) {
            Schema::table('sales_order_cns', function (Blueprint $table) {
                $table->unsignedBigInteger('sludge_movement_id')->nullable()->after('draining_code');
            });
        }
    }

    public function down()
    {
        $this->dropColumnIfExists('sales_order_cns', 'sludge_movement_id');

        Schema::dropIfExists('cns_sludge_movements');
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
