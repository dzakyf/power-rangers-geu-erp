<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorrectionTypeToAttendanceCorrections extends Migration
{
    public function up()
    {
        if (Schema::hasTable('attendance_corrections')) {
            Schema::table('attendance_corrections', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_corrections', 'correction_type')) {
                    $table->enum('correction_type', ['BOTH', 'IN', 'OUT'])->default('BOTH')->after('attendance_date');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('attendance_corrections')) {
            Schema::table('attendance_corrections', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_corrections', 'correction_type')) {
                    $table->dropColumn('correction_type');
                }
            });
        }
    }
}
