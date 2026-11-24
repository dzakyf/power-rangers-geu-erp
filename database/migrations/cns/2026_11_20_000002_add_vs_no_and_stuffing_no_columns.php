<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVsNoAndStuffingNoColumns extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vessel_schedules') && !Schema::hasColumn('vessel_schedules', 'VS_No')) {
            Schema::table('vessel_schedules', function (Blueprint $table) {
                $table->string('VS_No', 64)->nullable()->unique()->after('Task_ID');
            });
        }

        if (Schema::hasTable('vessel_schedule_dtls') && !Schema::hasColumn('vessel_schedule_dtls', 'Stuffing_No')) {
            Schema::table('vessel_schedule_dtls', function (Blueprint $table) {
                $table->string('Stuffing_No', 64)->nullable()->unique()->after('Order_No');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vessel_schedule_dtls') && Schema::hasColumn('vessel_schedule_dtls', 'Stuffing_No')) {
            Schema::table('vessel_schedule_dtls', function (Blueprint $table) {
                $table->dropUnique(['Stuffing_No']);
                $table->dropColumn('Stuffing_No');
            });
        }

        if (Schema::hasTable('vessel_schedules') && Schema::hasColumn('vessel_schedules', 'VS_No')) {
            Schema::table('vessel_schedules', function (Blueprint $table) {
                $table->dropUnique(['VS_No']);
                $table->dropColumn('VS_No');
            });
        }
    }
}
