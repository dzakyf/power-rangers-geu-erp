<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseAndTankToUcoStoragePelindoTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_storage_pelindo', 'Asal_Warehouse')) {
                $table->string('Asal_Warehouse', 100)->nullable()->after('Quantity');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'Nama_Tangki')) {
                $table->string('Nama_Tangki', 100)->nullable()->after('Asal_Warehouse');
            }
        });
    }

    public function down()
    {
        // Intentionally left empty (additive; never drops live columns).
    }
}
