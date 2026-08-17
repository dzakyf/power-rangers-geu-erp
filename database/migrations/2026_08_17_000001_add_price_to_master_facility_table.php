<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToMasterFacilityTable extends Migration
{
    public function up()
    {
        Schema::table('master_facility', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->nullable()->after('shelf_life');
        });
    }

    public function down()
    {
        Schema::table('master_facility', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
