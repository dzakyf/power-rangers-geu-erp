<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenReferenceNumberInUcoStoragePelindo extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo') || !Schema::hasColumn('uco_storage_pelindo', 'Reference_Number')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE `uco_storage_pelindo` MODIFY `Reference_Number` VARCHAR(50) NULL');
        }
    }

    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo') || !Schema::hasColumn('uco_storage_pelindo', 'Reference_Number')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE `uco_storage_pelindo` MODIFY `Reference_Number` VARCHAR(15) NULL');
        }
    }
}
