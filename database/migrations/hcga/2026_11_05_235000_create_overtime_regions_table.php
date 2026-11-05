<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('overtime_regions')) {
            Schema::create('overtime_regions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('region_code', 50)->unique();
                $table->string('region_name', 150);
                $table->text('coverage_keywords')->nullable()->comment('Daftar provinsi/kota/kabupaten (koma atau JSON), misal: Jakarta, Bogor, Depok, Tangerang, Bekasi');
                $table->text('description')->nullable();
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
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
        Schema::dropIfExists('overtime_regions');
    }
}
