<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceExtendedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Master Lokasi Kantor / Geofencing
        if (!Schema::hasTable('attendance_locations')) {
            Schema::create('attendance_locations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 150);
                $table->string('code', 50)->unique();
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->integer('radius_meters')->default(100);
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 2. Tambah kolom GPS, Foto, & Status di attendant_log jika belum ada
        if (Schema::hasTable('attendant_log')) {
            Schema::table('attendant_log', function (Blueprint $table) {
                if (!Schema::hasColumn('attendant_log', 'Clock_In_Lat')) {
                    $table->decimal('Clock_In_Lat', 10, 8)->nullable();
                    $table->decimal('Clock_In_Lng', 11, 8)->nullable();
                    $table->string('Clock_In_Photo', 255)->nullable();
                    $table->integer('Clock_In_Location_ID')->nullable();
                    $table->string('Clock_In_Status', 50)->nullable()->default('ON_TIME'); // ON_TIME, LATE, dll
                }
                if (!Schema::hasColumn('attendant_log', 'Clock_Out_Lat')) {
                    $table->decimal('Clock_Out_Lat', 10, 8)->nullable();
                    $table->decimal('Clock_Out_Lng', 11, 8)->nullable();
                    $table->string('Clock_Out_Photo', 255)->nullable();
                    $table->integer('Clock_Out_Location_ID')->nullable();
                    $table->string('Clock_Out_Status', 50)->nullable()->default('EARLY_LEAVE'); // NORMAL, EARLY_LEAVE
                }
                if (!Schema::hasColumn('attendant_log', 'Work_Calendar_ID')) {
                    $table->integer('Work_Calendar_ID')->nullable();
                    $table->integer('Shift_ID')->nullable();
                    $table->integer('Late_Minutes')->default(0);
                    $table->integer('Early_Leave_Minutes')->default(0);
                    $table->integer('Overtime_Minutes')->default(0);
                    $table->text('Notes')->nullable();
                }
            });
        }

        // 3. Pengajuan Izin / Cuti / Sakit / Dinas Luar
        if (!Schema::hasTable('attendance_leave_requests')) {
            Schema::create('attendance_leave_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->string('request_no', 100)->unique();
                $table->string('nik', 50)->index();
                $table->string('leave_type', 50); // CUTI, SAKIT, IZIN_PRIBADI, DINAS_LUAR
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('total_days')->default(1);
                $table->text('reason')->nullable();
                $table->string('attachment', 255)->nullable();
                $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('SUBMITTED');
                $table->unsignedBigInteger('approval_request_id')->nullable()->index();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 4. Pengajuan Koreksi Absensi (Lupa clock-in/out atau kendala teknis)
        if (!Schema::hasTable('attendance_corrections')) {
            Schema::create('attendance_corrections', function (Blueprint $table) {
                $table->increments('id');
                $table->string('correction_no', 100)->unique();
                $table->string('nik', 50)->index();
                $table->date('attendance_date');
                $table->time('original_clock_in')->nullable();
                $table->time('original_clock_out')->nullable();
                $table->time('corrected_clock_in')->nullable();
                $table->time('corrected_clock_out')->nullable();
                $table->text('reason');
                $table->string('evidence_file', 255)->nullable();
                $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'])->default('SUBMITTED');
                $table->unsignedBigInteger('approval_request_id')->nullable()->index();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
            });
        }

        // 5. Pengajuan Lembur / Overtime Request
        if (!Schema::hasTable('attendance_overtime_requests')) {
            Schema::create('attendance_overtime_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->string('overtime_no', 100)->unique();
                $table->string('nik', 50)->index();
                $table->date('overtime_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('duration_minutes')->default(0);
                $table->text('task_description');
                $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'])->default('SUBMITTED');
                $table->unsignedBigInteger('approval_request_id')->nullable()->index();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('attendance_overtime_requests');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendance_leave_requests');
        Schema::dropIfExists('attendance_locations');
    }
}
