<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceAttendanceAndLeaveRequestsWithTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah jam clock in & clock out pada request izin/cuti bila izin jam-jaman/parsial
        if (Schema::hasTable('attendance_leave_requests')) {
            Schema::table('attendance_leave_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_leave_requests', 'clock_in_time')) {
                    $table->time('clock_in_time')->nullable();
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'clock_out_time')) {
                    $table->time('clock_out_time')->nullable();
                }
                if (!Schema::hasColumn('attendance_leave_requests', 'attachment_path')) {
                    $table->string('attachment_path', 255)->nullable();
                }
            });
        }

        // 2. Tambah attachment_path pada koreksi absensi
        if (Schema::hasTable('attendance_corrections')) {
            Schema::table('attendance_corrections', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_corrections', 'attachment_path')) {
                    $table->string('attachment_path', 255)->nullable();
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
    }
}
