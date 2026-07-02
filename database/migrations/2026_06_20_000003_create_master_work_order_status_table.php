<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterWorkOrderStatusTable extends Migration
{
    public function up()
    {
        Schema::create('master_work_order_status', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Name');
            $table->string('Code');
            $table->text('Description')->nullable();
            $table->enum('Is_Active', ['Yes', 'No'])->default('Yes');
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_work_order_status');
    }
}
