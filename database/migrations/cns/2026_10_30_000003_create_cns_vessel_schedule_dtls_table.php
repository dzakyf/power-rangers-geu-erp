<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsVesselScheduleDtlsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vessel_schedule_dtls')) {
            return; // already provisioned on the live database
        }

        Schema::create('vessel_schedule_dtls', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Task_ID', 150)->nullable();
            $table->string('Order_No', 150)->nullable();
            $table->string('SI_No', 150)->nullable();
            $table->string('Container', 150);
            $table->string('Seal', 150);
            $table->time('Time_From');
            $table->time('Time_To');
            $table->date('Date');
            $table->string('Police_No', 150);
            $table->double('Tare', 30, 2);
            $table->double('Netto_KG', 30, 2);
            $table->double('Netto_MT', 30, 2);
            $table->double('Gross_KG', 30, 2);
            $table->double('Gross_MT', 30, 2);
            $table->string('Note', 255);
            $table->string('Status', 150);
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date');
            $table->string('Updated_By', 150);
            $table->dateTime('Updated_Date');
            $table->decimal('Qty_SJ', 10, 2)->nullable()->default(0.00);
            $table->decimal('Stock_In', 10, 2)->nullable()->default(0.00);
            $table->decimal('Stock_Out', 10, 2)->nullable()->default(0.00);

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
