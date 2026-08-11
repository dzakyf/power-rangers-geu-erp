<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterSectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_section', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Directorat', 100);
            $table->string('Department', 100);
            $table->string('Subsection', 100);
            $table->string('Initial_Code', 50);
            $table->string('Initial', 50)->index();
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
        Schema::dropIfExists('master_section');
    }
}
