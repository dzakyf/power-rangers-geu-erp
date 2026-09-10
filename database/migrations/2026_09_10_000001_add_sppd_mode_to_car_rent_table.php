<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ponytail: car_rent dibuat tanpa migration (0 baris saat dibuat).
// Kolom SPPD-mode nullable-only agar kompatibel. Upgrade: FK logis
// SPPD_ID->sppd.ID + car_rent_log meniru sppd_log bila butuh audit.
class AddSppdModeToCarRentTable extends Migration
{
    public function up()
    {
        Schema::table('car_rent', function (Blueprint $table) {
            if (!Schema::hasColumn('car_rent', 'Trip_Type')) {
                $table->string('Trip_Type', 10)->nullable()->default('NON_SPPD');
            }
            if (!Schema::hasColumn('car_rent', 'SPPD_ID')) {
                $table->integer('SPPD_ID')->nullable();
            }
            if (!Schema::hasColumn('car_rent', 'SPPD_No')) {
                $table->string('SPPD_No', 100)->nullable();
            }
        });

        DB::table('car_rent')->whereNull('Trip_Type')->update(['Trip_Type' => 'NON_SPPD']);

        Schema::table('car_rent', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_map('strtolower', array_keys($sm->listTableIndexes('car_rent')));
            if (!in_array('car_rent_trip_type_index', $indexes)) {
                $table->index('Trip_Type');
            }
            if (!in_array('car_rent_sppd_id_index', $indexes)) {
                $table->index('SPPD_ID');
            }
        });
    }

    public function down()
    {
        Schema::table('car_rent', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_map('strtolower', array_keys($sm->listTableIndexes('car_rent')));
            if (in_array('car_rent_trip_type_index', $indexes)) {
                $table->dropIndex(['Trip_Type']);
            }
            if (in_array('car_rent_sppd_id_index', $indexes)) {
                $table->dropIndex(['SPPD_ID']);
            }
            foreach (['Trip_Type', 'SPPD_ID', 'SPPD_No'] as $col) {
                if (Schema::hasColumn('car_rent', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
