<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsOverPlafonToSppdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sppd')) {
            Schema::table('sppd', function (Blueprint $table) {
                if (!Schema::hasColumn('sppd', 'Is_Over_Plafon')) {
                    $table->tinyInteger('Is_Over_Plafon')->default(0)->after('Estimated_Cost')->index();
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
        if (Schema::hasTable('sppd')) {
            Schema::table('sppd', function (Blueprint $table) {
                if (Schema::hasColumn('sppd', 'Is_Over_Plafon')) {
                    $table->dropColumn('Is_Over_Plafon');
                }
            });
        }
    }
}

