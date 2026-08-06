<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceIdToUcoQualityControlTable extends Migration
{
    public function up()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            $table->unsignedBigInteger('Source_ID')->nullable()->after('Is_Deleted');
            $table->index('Source_ID');
        });
    }

    public function down()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            $table->dropIndex(['Source_ID']);
            $table->dropColumn('Source_ID');
        });
    }
}
