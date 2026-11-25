<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPositionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_position', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Directorat', 100);
            $table->string('Department', 100)->index();
            $table->string('Subsection', 100);
            $table->string('Position', 100);
            $table->string('Initial_Code', 50);
            $table->string('Initial', 50)->index();
            $table->string('Level', 20);
            $table->string('Created_By', 100)->nullable();
            $table->timestamp('Created_Date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_position');
    }
}
