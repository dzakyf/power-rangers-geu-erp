<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddQtySludgeToCnsTankDrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cns_tank_drainings')) {
            return;
        }

        Schema::table('cns_tank_drainings', function (Blueprint $table) {
            if (!Schema::hasColumn('cns_tank_drainings', 'qty_sludge')) {
                $table->decimal('qty_sludge', 15, 2)->default(0)->after('qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('cns_tank_drainings')) {
            return;
        }

        $this->dropColumnIfExists('cns_tank_drainings', 'qty_sludge');
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
