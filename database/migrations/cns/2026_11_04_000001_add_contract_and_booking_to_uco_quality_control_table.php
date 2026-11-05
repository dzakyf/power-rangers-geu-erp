<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractAndBookingToUcoQualityControlTable extends Migration
{
    public function up()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (!Schema::hasColumn('uco_quality_control', 'Contract_ID')) {
                $table->unsignedBigInteger('Contract_ID')->nullable()->after('Source_ID');
                $table->index('Contract_ID');
            }
            if (!Schema::hasColumn('uco_quality_control', 'Booking_ID')) {
                $table->unsignedBigInteger('Booking_ID')->nullable()->after('Contract_ID');
                $table->index('Booking_ID');
            }
        });
    }

    public function down()
    {
        Schema::table('uco_quality_control', function (Blueprint $table) {
            if (Schema::hasColumn('uco_quality_control', 'Booking_ID')) {
                $table->dropIndex(['Booking_ID']);
                $table->dropColumn('Booking_ID');
            }
            if (Schema::hasColumn('uco_quality_control', 'Contract_ID')) {
                $table->dropIndex(['Contract_ID']);
                $table->dropColumn('Contract_ID');
            }
        });
    }
}

