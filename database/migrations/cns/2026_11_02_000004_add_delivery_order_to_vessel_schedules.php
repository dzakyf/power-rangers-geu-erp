<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryOrderToVesselSchedules extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vessel_schedules')) {
            return;
        }

        Schema::table('vessel_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('vessel_schedules', 'DO_Meta_File_ID')) {
                $table->unsignedBigInteger('DO_Meta_File_ID')->nullable();
            }
            if (!Schema::hasColumn('vessel_schedules', 'DO_Status')) {
                $table->string('DO_Status', 20)->nullable();
            }
            if (!Schema::hasColumn('vessel_schedules', 'DO_Note')) {
                $table->text('DO_Note')->nullable();
            }
            if (!Schema::hasColumn('vessel_schedules', 'DO_Updated_By')) {
                $table->string('DO_Updated_By', 150)->nullable();
            }
            if (!Schema::hasColumn('vessel_schedules', 'DO_Updated_Date')) {
                $table->dateTime('DO_Updated_Date')->nullable();
            }
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
