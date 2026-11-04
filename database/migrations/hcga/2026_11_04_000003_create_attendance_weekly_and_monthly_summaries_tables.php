<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceWeeklyAndMonthlySummariesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Ringkasan Mingguan Kehadiran (Target 40 Jam / Minggu)
        if (!Schema::hasTable('attendance_weekly_summaries')) {
            Schema::create('attendance_weekly_summaries', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 100);
                $table->unsignedInteger('year');
                $table->unsignedInteger('week_number')->comment('Minggu ke-1 s/d 53 dalam setahun');
                $table->date('week_start_date');
                $table->date('week_end_date');
                $table->unsignedInteger('target_work_minutes')->default(2400)->comment('Default 40 jam = 2400 menit');
                $table->unsignedInteger('actual_work_minutes')->default(0);
                $table->unsignedInteger('overtime_minutes')->default(0);
                $table->unsignedInteger('late_minutes')->default(0);
                $table->unsignedInteger('early_leave_minutes')->default(0);
                $table->unsignedInteger('days_present')->default(0);
                $table->unsignedInteger('days_absent')->default(0);
                $table->unsignedInteger('days_off')->default(0);
                $table->unsignedInteger('days_leave')->default(0);
                $table->boolean('is_target_achieved')->default(false)->comment('1: Jika actual >= target (min 40 jam)');
                $table->string('remarks', 255)->nullable();
                $table->dateTime('calculated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->unique(['user_id', 'year', 'week_number'], 'uk_user_year_week');
                $table->index(['year', 'week_number', 'is_target_achieved'], 'idx_week_achieved');
            });
        }

        // 2. Ringkasan Bulanan / Periode Cutoff (Untuk Payroll Engine)
        if (!Schema::hasTable('attendance_monthly_summaries')) {
            Schema::create('attendance_monthly_summaries', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 100);
                $table->unsignedInteger('periode')->comment('Format YYYYMM misal 202611');
                $table->date('cutoff_start_date');
                $table->date('cutoff_end_date');
                $table->unsignedInteger('total_scheduled_days')->default(0);
                $table->unsignedInteger('total_present_days')->default(0);
                $table->unsignedInteger('total_absent_days')->default(0);
                $table->unsignedInteger('total_late_days')->default(0);
                $table->unsignedInteger('total_early_leave_days')->default(0);
                $table->unsignedInteger('total_leave_days')->default(0);
                $table->unsignedInteger('total_overtime_minutes')->default(0);
                $table->unsignedInteger('total_late_minutes')->default(0);
                $table->unsignedInteger('total_work_minutes')->default(0);
                $table->enum('payroll_post_status', ['DRAFT', 'POSTED', 'LOCKED'])->default('DRAFT');
                $table->dateTime('payroll_posted_at')->nullable();
                $table->string('remarks', 255)->nullable();
                $table->dateTime('calculated_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->unique(['user_id', 'periode'], 'uk_user_periode');
                $table->index(['periode', 'payroll_post_status'], 'idx_periode_payroll_status');
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
        Schema::dropIfExists('attendance_monthly_summaries');
        Schema::dropIfExists('attendance_weekly_summaries');
    }
}

