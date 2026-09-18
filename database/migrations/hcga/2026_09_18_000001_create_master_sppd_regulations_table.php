<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterSppdRegulationsTable extends Migration
{
    public function up()
    {
        Schema::create('master_sppd_job_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('level', 100)->unique();
            $table->string('grade', 50);
            $table->string('label', 150);
            $table->timestamps();
        });

        Schema::create('master_sppd_trip_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_key', 100)->unique();
            $table->string('name', 150);
            $table->string('duration', 100)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('master_sppd_allowances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trip_type_key', 100);
            $table->string('job_level_key', 100);
            $table->text('transport_land')->nullable(); // JSON stored string
            $table->string('transport_air', 255)->nullable();
            $table->decimal('ticket_max', 15, 2)->nullable();
            $table->decimal('reimburse_max_per_night', 15, 2)->nullable();
            $table->integer('hotel_star')->nullable();
            $table->decimal('daily_meal', 15, 2)->nullable();
            $table->decimal('daily_pocket', 15, 2)->nullable();
            $table->decimal('kos_per_month', 15, 2)->nullable();
            $table->text('assistance_note')->nullable();
            $table->decimal('relocation_one_time_payment', 15, 2)->nullable();
            $table->text('relocation_note')->nullable();
            $table->decimal('mobilization_cost_per_week', 15, 2)->nullable();
            $table->text('mobilization_note')->nullable();
            $table->timestamps();

            $table->index(['trip_type_key', 'job_level_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_sppd_allowances');
        Schema::dropIfExists('master_sppd_trip_types');
        Schema::dropIfExists('master_sppd_job_levels');
    }
}
