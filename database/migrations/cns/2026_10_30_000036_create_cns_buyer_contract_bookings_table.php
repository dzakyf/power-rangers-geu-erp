<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCnsBuyerContractBookingsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cns_buyer_contract_bookings')) {
            return;
        }

        Schema::create('cns_buyer_contract_bookings', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('Contract_ID');
            $table->string('Label', 20);
            $table->string('Booking_No', 100);
            $table->string('Booking_File', 255)->nullable();
            $table->string('Booking_File_Name', 255)->nullable();
            $table->string('Created_By', 100)->nullable();
            $table->dateTime('Created_Date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('Updated_By', 100)->nullable();
            $table->dateTime('Updated_Date')->nullable();
            $table->unique(['Contract_ID', 'Label']);
            $table->index('Contract_ID');
            $table->index('Booking_No');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
