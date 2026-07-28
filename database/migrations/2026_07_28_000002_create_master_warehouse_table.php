<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterWarehouseTable extends Migration
{
    public function up()
    {
        Schema::create('master_warehouse', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Name');
            $table->string('Coordinate')->nullable();
            $table->enum('Type', ['NON_SITE', 'SITE'])->default('NON_SITE');
            $table->timestamp('Created_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_warehouse');
    }
}
