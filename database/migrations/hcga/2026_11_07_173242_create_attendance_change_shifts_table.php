<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceChangeShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('attendance_change_shifts')) {
            Schema::create('attendance_change_shifts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('change_shift_no', 60)->unique();
                $table->string('nik', 50)->index();
                $table->enum('request_mode', ['CHANGE', 'EXCHANGE'])->default('CHANGE')->comment('CHANGE: Tukar/Ganti Shift Mandiri, EXCHANGE: Tukar Shift dg Rekan Kerja');
                $table->date('original_date')->index();
                $table->unsignedInteger('current_shift_id')->nullable();
                $table->unsignedInteger('target_shift_id');
                $table->date('target_date')->nullable()->comment('Untuk mode EXCHANGE / ganti tanggal target');
                
                // Jika bertukar dengan rekan kerja (peer replacement)
                $table->string('peer_nik', 50)->nullable()->index();
                $table->unsignedInteger('peer_current_shift_id')->nullable();
                $table->unsignedInteger('peer_target_shift_id')->nullable();
                
                $table->string('reason', 255);
                $table->string('attachment', 255)->nullable();
                $table->enum('status', ['SUBMITTED', 'WAITING_PEER', 'IN_PROCESS', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('SUBMITTED');
                $table->unsignedInteger('approval_request_id')->nullable()->index();
                $table->dateTime('approved_at')->nullable();
                $table->string('approved_by', 100)->nullable();
                $table->string('rejection_reason', 255)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->dateTime('created_at')->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->dateTime('updated_at')->nullable();
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
        Schema::dropIfExists('attendance_change_shifts');
    }
}
