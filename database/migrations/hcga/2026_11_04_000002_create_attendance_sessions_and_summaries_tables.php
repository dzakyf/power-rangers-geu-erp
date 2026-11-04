<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceSessionsAndSummariesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambahkan kolom pendukung enterprise pada attendance_sessions jika belum ada
        if (Schema::hasTable('attendance_sessions')) {
            Schema::table('attendance_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_sessions', 'user_id')) {
                    $table->string('user_id', 100)->nullable()->after('Emp_ID');
                }
                if (!Schema::hasColumn('attendance_sessions', 'work_date')) {
                    $table->date('work_date')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('attendance_sessions', 'shift_id')) {
                    $table->unsignedInteger('shift_id')->nullable()->after('work_date');
                }
                if (!Schema::hasColumn('attendance_sessions', 'clock_in_device')) {
                    $table->string('clock_in_device', 100)->nullable()->after('Clock_Out');
                }
                if (!Schema::hasColumn('attendance_sessions', 'clock_out_device')) {
                    $table->string('clock_out_device', 100)->nullable()->after('clock_in_device');
                }
                if (!Schema::hasColumn('attendance_sessions', 'pairing_status')) {
                    $table->enum('pairing_status', ['COMPLETE', 'INCOMPLETE_IN', 'INCOMPLETE_OUT', 'MANUAL_ADJUSTED'])
                        ->default('INCOMPLETE_IN')
                        ->after('clock_out_device');
                }
                if (!Schema::hasColumn('attendance_sessions', 'work_duration_minutes')) {
                    $table->unsignedInteger('work_duration_minutes')->default(0)->after('pairing_status');
                }
                if (!Schema::hasColumn('attendance_sessions', 'updated_by')) {
                    $table->string('updated_by', 100)->nullable()->after('Created_By');
                }
                if (!Schema::hasColumn('attendance_sessions', 'updated_at')) {
                    $table->dateTime('updated_at')->nullable()->after('updated_by');
                }
            });
        }

        // 2. Ringkasan Harian Kehadiran (Daily Attendance Summary)
        if (!Schema::hasTable('attendance_daily_summaries')) {
            Schema::create('attendance_daily_summaries', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 100);
                $table->date('work_date');
                $table->unsignedInteger('shift_id')->nullable();
                $table->enum('attendance_status', [
                    'PRESENT', 'LATE', 'EARLY_LEAVE', 'ABSENT', 'LEAVE', 'BUSINESS_TRIP', 'TRAINING', 'OFF', 'HOLIDAY'
                ])->default('ABSENT');
                $table->unsignedInteger('scheduled_minutes')->default(0);
                $table->unsignedInteger('actual_work_minutes')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_leave_minutes')->default(0);
                $table->unsignedInteger('overtime_minutes')->default(0);
                $table->boolean('is_cross_day')->default(false);
                $table->boolean('is_holiday')->default(false);
                $table->boolean('is_leave')->default(false);
                $table->unsignedInteger('periode')->nullable()->comment('Contoh: 202611');
                $table->string('remarks', 255)->nullable();
                $table->dateTime('calculated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->unique(['user_id', 'work_date'], 'uk_daily_summary_user_date');
                $table->index(['work_date', 'attendance_status'], 'idx_daily_date_status');
                $table->index(['periode', 'attendance_status'], 'idx_daily_periode_status');
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
        Schema::dropIfExists('attendance_daily_summaries');
    }
}
