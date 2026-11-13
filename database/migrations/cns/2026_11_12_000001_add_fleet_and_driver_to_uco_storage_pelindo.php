<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFleetAndDriverToUcoStoragePelindo extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_storage_pelindo', 'fleet_name')) {
                $table->string('fleet_name', 150)->nullable()->after('Reference_Number');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'fleet_plat')) {
                $table->string('fleet_plat', 50)->nullable()->after('fleet_name');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'driver_name')) {
                $table->string('driver_name', 150)->nullable()->after('fleet_plat');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            foreach (['driver_name', 'fleet_plat', 'fleet_name'] as $col) {
                if (Schema::hasColumn('uco_storage_pelindo', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
