<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayTypeToAttendanceOvertimeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('attendance_overtime_requests')) {
            Schema::table('attendance_overtime_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_overtime_requests', 'day_type')) {
                    $table->enum('day_type', ['WORKDAY', 'HOLIDAY'])->default('WORKDAY')->after('overtime_date');
                }
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
        if (Schema::hasTable('attendance_overtime_requests')) {
            Schema::table('attendance_overtime_requests', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_overtime_requests', 'day_type')) {
                    $table->dropColumn('day_type');
                }
            });
        }
    }
}

