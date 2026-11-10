<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeQcToUcoQualityControlTable extends Migration
{
    public function up()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_quality_control', 'Kode_QC')) {
                $table->string('Kode_QC', 100)->nullable()->after('ID');
                $table->index('Kode_QC');
            }
        });
    }

    public function down()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (Schema::hasColumn('uco_quality_control', 'Kode_QC')) {
                $table->dropIndex(['Kode_QC']);
                $table->dropColumn('Kode_QC');
            }
        });
    }
}

