<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPackagingTable extends Migration
{
    public function up()
    {
        Schema::create('master_packaging', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Name');
            $table->decimal('Capacity', 12, 2)->nullable();
            $table->string('Unit')->default('L');
            $table->text('Description')->nullable();
            $table->enum('Status', ['active', 'inactive'])->default('active');
            $table->timestamp('Created_At')->nullable();
            $table->timestamp('Updated_At')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_packaging');
    }
}
