<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDestpointTimesToSppdTable extends Migration
{
    public function up()
    {
        Schema::table('sppd', function (Blueprint $table) {
            if (!Schema::hasColumn('sppd', 'Dest_Lat')) {
                $table->string('Dest_Lat', 30)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Dest_Lng')) {
                $table->string('Dest_Lng', 30)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Departure_Time')) {
                $table->string('Departure_Time', 10)->nullable();
            }
            if (!Schema::hasColumn('sppd', 'Return_Time')) {
                $table->string('Return_Time', 10)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('sppd', function (Blueprint $table) {
            foreach (['Dest_Lat', 'Dest_Lng', 'Departure_Time', 'Return_Time'] as $column) {
                if (Schema::hasColumn('sppd', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
