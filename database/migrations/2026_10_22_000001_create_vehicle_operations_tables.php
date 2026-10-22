<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateVehicleOperationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('vehicle_logbook')) {
            Schema::create('vehicle_logbook', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Log_No', 50)->unique();
                $table->string('Asset_No', 60)->index();
                $table->string('Registration_No', 50)->nullable()->index();
                $table->string('Driver_Name', 120);
                $table->string('Department', 100)->nullable();
                $table->dateTime('Start_Date');
                $table->dateTime('End_Date')->nullable();
                $table->decimal('Start_KM', 12, 1)->default(0);
                $table->decimal('End_KM', 12, 1)->nullable();
                $table->decimal('Total_KM', 12, 1)->nullable();
                $table->string('Destination', 255);
                $table->text('Purpose')->nullable();
                $table->string('Vehicle_Condition_Out', 150)->nullable();
                $table->string('Vehicle_Condition_In', 150)->nullable();
                $table->enum('Status', ['ON_GOING', 'COMPLETED', 'CANCELLED'])->default('ON_GOING');
                $table->string('Photo_Odometer_Start', 255)->nullable();
                $table->string('Photo_Odometer_End', 255)->nullable();
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('vehicle_fuel_log')) {
            Schema::create('vehicle_fuel_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('Fuel_No', 50)->unique();
                $table->string('Asset_No', 60)->index();
                $table->string('Registration_No', 50)->nullable()->index();
                $table->dateTime('Fill_Date');
                $table->string('Driver_Name', 120);
                $table->decimal('Current_KM', 12, 1)->default(0);
                $table->decimal('Previous_KM', 12, 1)->nullable();
                $table->decimal('Distance_KM', 12, 1)->nullable();
                $table->string('Fuel_Type', 50); // Pertalite, Pertamax, Pertamax Turbo, Solar/Biosolar, Dexlite, Pertamina Dex
                $table->decimal('Liters', 10, 2);
                $table->decimal('Cost_Per_Liter', 12, 2)->nullable();
                $table->decimal('Total_Cost', 14, 2);
                $table->decimal('KM_Per_Liter', 10, 2)->nullable();
                $table->decimal('Cost_Per_KM', 12, 2)->nullable();
                $table->string('Gas_Station', 150)->nullable();
                $table->string('Payment_Method', 80)->nullable(); // MyPertamina, Reimburse, Kasbon, Kartu Armada
                $table->string('Receipt_Photo', 255)->nullable();
                $table->string('Odometer_Photo', 255)->nullable();
                $table->tinyInteger('Full_Tank')->default(1);
                $table->text('Notes')->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_fuel_log');
        Schema::dropIfExists('vehicle_logbook');
    }
}
