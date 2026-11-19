<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropBaPathFromUcoStoragePelindoTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        if (!Schema::hasColumn('uco_storage_pelindo', 'BA_Path')) {
            return;
        }

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `uco_storage_pelindo` DROP COLUMN `BA_Path`");
            return;
        }

        DB::statement('ALTER TABLE "uco_storage_pelindo" DROP COLUMN "BA_Path"');
    }

    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_storage_pelindo', 'BA_Path')) {
                $table->string('BA_Path', 255)->nullable()->after('QC_Path');
            }
        });
    }
}
