<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPackagingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_packaging', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Name', 150);
            $table->decimal('Capacity', 12, 2)->nullable();
            $table->string('Unit', 10)->default('L');
            $table->text('Description')->nullable();
            $table->enum('Status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_packaging');
    }
}
