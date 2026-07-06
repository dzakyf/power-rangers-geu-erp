<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterContainerTable extends Migration
{
    public function up()
    {
        Schema::create('master_container', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Name');
            $table->decimal('Volume', 12, 2)->nullable();
            $table->unsignedBigInteger('Warehouse_ID')->nullable();
            $table->text('Note')->nullable();
            $table->enum('Status', ['active', 'inactive'])->default('active');
            $table->string('Created_By')->nullable();
            $table->timestamp('Created_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_container');
    }
}
