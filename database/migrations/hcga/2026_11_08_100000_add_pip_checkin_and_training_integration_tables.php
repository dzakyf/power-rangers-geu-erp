<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPipCheckinAndTrainingIntegrationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Log Check-in Sesi Mentoring Mingguan PIP
        if (!Schema::hasTable('performance_pip_checkins')) {
            Schema::create('performance_pip_checkins', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('pip_id');
                $table->integer('week_number')->default(1);
                $table->date('checkin_date');
                $table->text('discussion_notes'); // Catatan sesi bimbingan / arahan
                $table->text('action_agreed'); // Tindakan yang disepakati untuk minggu depan
                $table->enum('attendance_status', ['ATTENDED', 'RESCHEDULED', 'ABSENT'])->default('ATTENDED');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('pip_id')->references('id')->on('performance_pip_plans')->onDelete('cascade');
                $table->index(['pip_id', 'week_number']);
            });
        }

        // 2. Rekomendasi / Penugasan Pelatihan Internal (Modul Training) dalam PIP
        if (!Schema::hasTable('performance_pip_trainings')) {
            Schema::create('performance_pip_trainings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('pip_id');
                $table->unsignedBigInteger('training_id')->nullable(); // Menghubungkan ke tabel training / master_training
                $table->string('training_title', 255);
                $table->string('target_competency', 150)->nullable(); // Misal: Problem Solving, SOP Execution
                $table->date('due_date')->nullable();
                $table->enum('status', ['RECOMMENDED', 'ENROLLED', 'COMPLETED'])->default('RECOMMENDED');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('pip_id')->references('id')->on('performance_pip_plans')->onDelete('cascade');
                $table->index('pip_id');
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
        Schema::dropIfExists('performance_pip_trainings');
        Schema::dropIfExists('performance_pip_checkins');
    }
}

