<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMovementQcToUcoStoragePelindo extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_storage_pelindo', 'Movement_FFA')) {
                $table->decimal('Movement_FFA', 8, 2)->nullable()->after('IMP');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'Movement_MC')) {
                $table->decimal('Movement_MC', 8, 2)->nullable()->after('Movement_FFA');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'Movement_IMP')) {
                $table->decimal('Movement_IMP', 8, 2)->nullable()->after('Movement_MC');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            foreach (['Movement_IMP', 'Movement_MC', 'Movement_FFA'] as $col) {
                if (Schema::hasColumn('uco_storage_pelindo', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
