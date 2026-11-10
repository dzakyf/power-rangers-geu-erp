<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQcParametersToUcoStoragePelindoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_storage_pelindo', 'Notim_Path')) {
                $table->string('Notim_Path', 255)->nullable()->after('Reference_Number');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'QC_Path')) {
                $table->string('QC_Path', 255)->nullable()->after('Notim_Path');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'FFA')) {
                $table->decimal('FFA', 8, 2)->nullable()->after('QC_Path');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'MC')) {
                $table->decimal('MC', 8, 2)->nullable()->after('FFA');
            }
            if (!Schema::hasColumn('uco_storage_pelindo', 'IMP')) {
                $table->decimal('IMP', 8, 2)->nullable()->after('MC');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('uco_storage_pelindo')) {
            return;
        }

        Schema::table('uco_storage_pelindo', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('uco_storage_pelindo', 'FFA')) {
                $drops[] = 'FFA';
            }
            if (Schema::hasColumn('uco_storage_pelindo', 'MC')) {
                $drops[] = 'MC';
            }
            if (Schema::hasColumn('uco_storage_pelindo', 'IMP')) {
                $drops[] = 'IMP';
            }
            if (Schema::hasColumn('uco_storage_pelindo', 'QC_Path')) {
                $drops[] = 'QC_Path';
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
}

