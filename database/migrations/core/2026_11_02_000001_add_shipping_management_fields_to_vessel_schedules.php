<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddShippingManagementFieldsToVesselSchedules extends Migration
{
    public function up()
    {
        $this->addColumns('vessel_schedules');
        $this->addColumns('vessel_schedule_logs');

        $this->makeLegacyDatesNullable('vessel_schedules');
        $this->makeLegacyDatesNullable('vessel_schedule_logs');
    }

    private function addColumns(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'Contract_ID')) {
                $table->unsignedBigInteger('Contract_ID')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Contract_No')) {
                $table->string('Contract_No', 100)->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Incoterms')) {
                $table->string('Incoterms', 10)->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Shipping_Method')) {
                $table->string('Shipping_Method', 30)->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Booking_ID')) {
                $table->unsignedBigInteger('Booking_ID')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Booking_No')) {
                $table->string('Booking_No', 100)->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Laycan_Start')) {
                $table->date('Laycan_Start')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Laycan_End')) {
                $table->date('Laycan_End')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Q88_Meta_File_ID')) {
                $table->unsignedBigInteger('Q88_Meta_File_ID')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Truck_Supplier_ID')) {
                $table->unsignedBigInteger('Truck_Supplier_ID')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Truck')) {
                $table->string('Truck', 150)->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Truck_Schedule')) {
                $table->dateTime('Truck_Schedule')->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Truck_Destination')) {
                $table->string('Truck_Destination', 150)->nullable();
            }
            if (!Schema::hasColumn($tableName, 'Truck_Shipment_Data')) {
                $table->text('Truck_Shipment_Data')->nullable();
            }
        });
    }

    private function makeLegacyDatesNullable(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        foreach (['Stuffing', 'Checking_BC', 'Closing_CY', 'ETD', 'ETA'] as $column) {
            if (Schema::hasColumn($tableName, $column)) {
                DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$column}` DATE NULL");
            }
        }
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
