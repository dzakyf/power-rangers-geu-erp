<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLevel3ToOrganizationStructure extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('organization_structure')) {
            return;
        }

        Schema::table('organization_structure', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_structure', 'Level3')) {
                $table->tinyInteger('Level3')->default(0)->after('Level');
            }
        });

        if (Schema::hasColumn('organization_structure', 'Level3')) {
            DB::table('organization_structure')->update([
                'Level3' => DB::raw('CASE WHEN `Level` = 3 THEN 1 ELSE 0 END'),
            ]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('organization_structure') && Schema::hasColumn('organization_structure', 'Level3')) {
            Schema::table('organization_structure', function (Blueprint $table) {
                $table->dropColumn('Level3');
            });
        }
    }
}
