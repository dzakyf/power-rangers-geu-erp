<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMiuToUcoQualityControlTable extends Migration
{
    public function up()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_quality_control', 'MIU')) {
                $table->decimal('MIU', 8, 2)->nullable()->after('FFA_As_Oleic');
            }
        });
    }

    public function down()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (Schema::hasColumn('uco_quality_control', 'MIU')) {
                $table->dropColumn('MIU');
            }
        });
    }
}

