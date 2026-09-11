<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLevel0ToOrganizationStructureTable extends Migration
{
    public function up()
    {
        Schema::table('organization_structure', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_structure', 'Level0')) {
                $table->tinyInteger('Level0')->nullable()->default(0)->after('Level8');
            }
        });
    }

    public function down()
    {
        Schema::table('organization_structure', function (Blueprint $table) {
            if (Schema::hasColumn('organization_structure', 'Level0')) {
                $table->dropColumn('Level0');
            }
        });
    }
}
