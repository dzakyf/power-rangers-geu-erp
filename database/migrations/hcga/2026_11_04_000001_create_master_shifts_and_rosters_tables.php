<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterShiftsAndRostersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Shift Definisi
        if (!Schema::hasTable('master_shifts')) {
            Schema::create('master_shifts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('shift_code', 20)->unique();
                $table->string('shift_name', 100);
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('break_minutes')->default(60);
                $table->unsignedInteger('working_minutes')->default(480);
                $table->tinyInteger('is_cross_day')->default(0)->comment('1: Melewati tengah malam');
                $table->tinyInteger('is_off')->default(0)->comment('1: Hari libur shift');
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }

        // 2. Master Pola Siklus Roster
        if (!Schema::hasTable('roster_patterns')) {
            Schema::create('roster_patterns', function (Blueprint $table) {
                $table->increments('id');
                $table->string('pattern_name', 100);
                $table->unsignedInteger('cycle_days')->comment('Jumlah hari dalam 1 siklus berulang');
                $table->string('description', 255)->nullable();
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }

        // 3. Detail Pola Roster per Hari Siklus
        if (!Schema::hasTable('roster_pattern_details')) {
            Schema::create('roster_pattern_details', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pattern_id');
                $table->unsignedInteger('day_sequence')->comment('Hari ke-1 sampai N siklus');
                $table->unsignedInteger('shift_id');

                $table->index(['pattern_id', 'day_sequence'], 'idx_pattern_seq');
                $table->foreign('pattern_id')->references('id')->on('roster_patterns')->onDelete('cascade');
            });
        }

        // 4. Penugasan Pola Roster Karyawan
        if (!Schema::hasTable('employee_roster_assignments')) {
            Schema::create('employee_roster_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 100);
                $table->unsignedInteger('pattern_id');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->unsignedInteger('start_day_sequence')->default(1)->comment('Hari awal siklus pada start_date');
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->index(['user_id', 'start_date', 'end_date'], 'idx_user_roster');
            });
        }

        // 5. Hasil Generate Jadwal Kerja Harian (Employee Schedules)
        if (!Schema::hasTable('employee_schedules')) {
            Schema::create('employee_schedules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('user_id', 100);
                $table->date('work_date');
                $table->unsignedInteger('shift_id');
                $table->enum('source_type', ['PATTERN', 'MANUAL_OVERRIDE', 'LEAVE', 'TRAINING', 'PUBLIC_HOLIDAY'])->default('PATTERN');
                $table->string('notes', 255)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->unique(['user_id', 'work_date'], 'uk_employee_work_date');
                $table->index(['work_date', 'shift_id'], 'idx_date_shift');
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
        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('employee_roster_assignments');
        Schema::dropIfExists('roster_pattern_details');
        Schema::dropIfExists('roster_patterns');
        Schema::dropIfExists('master_shifts');
    }
}

