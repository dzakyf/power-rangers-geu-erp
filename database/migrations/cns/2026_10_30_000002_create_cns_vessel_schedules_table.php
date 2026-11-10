<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsVesselSchedulesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vessel_schedules')) {
            return; // already provisioned on the live database
        }

        Schema::create('vessel_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Task_ID', 150);
            $table->string('Order_No', 150);
            $table->string('Qty', 20);
            $table->string('Qty1', 50);
            $table->string('Qty2', 50);
            $table->string('Vessel', 150);
            $table->string('POL', 150);
            $table->string('POD', 150);
            $table->date('Stuffing');
            $table->date('Checking_BC');
            $table->date('Closing_CY');
            $table->date('ETD');
            $table->date('ATD')->nullable();
            $table->date('ETA');
            $table->date('ATA')->nullable();
            $table->text('Note');
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Updated_By', 150)->nullable();
            $table->dateTime('Updated_Date')->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
