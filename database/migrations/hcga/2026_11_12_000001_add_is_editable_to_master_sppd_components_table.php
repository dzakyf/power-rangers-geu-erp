<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsEditableToMasterSppdComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('master_sppd_components')) {
            Schema::table('master_sppd_components', function (Blueprint $table) {
                if (!Schema::hasColumn('master_sppd_components', 'is_editable')) {
                    $table->tinyInteger('is_editable')->default(0)->after('description');
                }
            });

            // Set default editable components: ticket_max & reimburse_max_per_night
            DB::table('master_sppd_components')
                ->whereIn('code', ['ticket_max', 'reimburse_max_per_night'])
                ->update(['is_editable' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('master_sppd_components')) {
            Schema::table('master_sppd_components', function (Blueprint $table) {
                if (Schema::hasColumn('master_sppd_components', 'is_editable')) {
                    $table->dropColumn('is_editable');
                }
            });
        }
    }
}

