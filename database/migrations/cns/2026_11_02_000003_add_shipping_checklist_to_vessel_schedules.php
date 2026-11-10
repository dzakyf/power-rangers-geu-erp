<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingChecklistToVesselSchedules extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vessel_schedules')) {
            return;
        }

        Schema::table('vessel_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Pickup_Container')) {
                $table->boolean('Checklist_Pickup_Container')->default(0);
            }
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Stuffing_Process')) {
                $table->boolean('Checklist_Stuffing_Process')->default(0);
            }
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Rilis_NPE')) {
                $table->boolean('Checklist_Rilis_NPE')->default(0);
            }
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Get_In_CY')) {
                $table->boolean('Checklist_Get_In_CY')->default(0);
            }
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Done_Kapal_Berangkat')) {
                $table->boolean('Checklist_Done_Kapal_Berangkat')->default(0);
            }
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Updated_By')) {
                $table->string('Checklist_Updated_By', 150)->nullable();
            }
            if (!Schema::hasColumn('vessel_schedules', 'Checklist_Updated_Date')) {
                $table->dateTime('Checklist_Updated_Date')->nullable();
            }
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live columns).
    }
}
