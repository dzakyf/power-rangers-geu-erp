<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateHrdOfficeMenuPathToHrdMasterLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')
                ->where('Menu_ID', 'SI01005')
                ->where('Modules', 'HRD')
                ->update([
                    'Path_Loc' => 'hrd-master-location',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('menu_lists')) {
            DB::table('menu_lists')
                ->where('Menu_ID', 'SI01005')
                ->where('Modules', 'HRD')
                ->update([
                    'Path_Loc' => 'accounting-master-location',
                ]);
        }
    }
}
