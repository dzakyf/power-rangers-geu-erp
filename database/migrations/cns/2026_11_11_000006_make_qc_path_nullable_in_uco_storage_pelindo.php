<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeQcPathNullableInUcoStoragePelindo extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo') || !Schema::hasColumn('uco_storage_pelindo', 'QC_Path')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE `uco_storage_pelindo` MODIFY `QC_Path` VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo') || !Schema::hasColumn('uco_storage_pelindo', 'QC_Path')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `uco_storage_pelindo` MODIFY `QC_Path` VARCHAR(255) NOT NULL DEFAULT ''");
        }
    }
}
