<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDoNoToVesselSchedules extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vessel_schedules')) {
            return;
        }

        Schema::table('vessel_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('vessel_schedules', 'DO_No')) {
                $table->string('DO_No', 150)->nullable();
            }
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
