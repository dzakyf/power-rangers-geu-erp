<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickupOrderTable extends Migration
{
    public function up()
    {
        Schema::create('pickup_order', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Code');
            $table->string('Type_Pickup')->nullable();
            $table->string('Coverage')->nullable();
            $table->string('Site')->nullable();
            $table->string('Package')->nullable();
            $table->decimal('Qty', 15, 2)->nullable();
            $table->decimal('Price', 15, 2)->nullable();
            $table->text('Description')->nullable();

            // Status & assignments
            $table->enum('Status', ['DONE', 'PENDING_SETTLEMENT'])->default('PENDING_SETTLEMENT');
            $table->timestamp('Pickup_At')->nullable();
            $table->unsignedBigInteger('Fleet_Id')->nullable();
            $table->unsignedBigInteger('WO_Id')->nullable();
            $table->unsignedBigInteger('DO_Id')->nullable();
            $table->unsignedBigInteger('Picker_Id')->nullable();

            // Route / BBM fields
            $table->string('BBM_Ratio')->nullable();
            $table->decimal('BBM_Consumption', 15, 2)->nullable();
            $table->decimal('BBM_Price', 15, 2)->nullable();
            $table->decimal('Total_Distance', 15, 2)->nullable();
            $table->integer('Pickup_Waypoints')->nullable();
            $table->decimal('Total_Weight', 15, 2)->nullable();

            // Cost estimation
            $table->decimal('Toll_Estimation', 15, 2)->nullable();
            $table->decimal('Parking_Cost', 15, 2)->nullable();
            $table->decimal('BBM_Alkon_Price', 15, 2)->nullable();
            $table->decimal('Driver_Operational_Price', 15, 2)->nullable();
            $table->decimal('Field_Operational_Price', 15, 2)->nullable();
            $table->decimal('Total_Cost', 15, 2)->nullable();
            $table->decimal('APC', 15, 2)->nullable();

            // Coordinates
            $table->string('Lat')->nullable();
            $table->string('Lng')->nullable();

            // Actual realization
            $table->decimal('Actual_Qty', 15, 2)->nullable();
            $table->decimal('Actual_Nominal_Transfer', 15, 2)->nullable();

            // Acquisition cost columns (NEW)
            $table->enum('Acquisition_Status', ['NOT_SUBMITTED', 'PENDING', 'APPROVED', 'REJECTED'])
                  ->default('NOT_SUBMITTED');
            $table->timestamp('Acquisition_Submitted_At')->nullable();
            $table->timestamp('Acquisition_Approved_At')->nullable();
            $table->string('Acquisition_Approved_By')->nullable();
            $table->text('Acquisition_Notes')->nullable();

            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pickup_order');
    }
}
