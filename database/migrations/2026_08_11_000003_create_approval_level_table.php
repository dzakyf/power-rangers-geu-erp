<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_level', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Request_Type', 100);
            $table->integer('Level_Number');
            $table->string('Approver_Position', 100);
            $table->string('Level_Name', 150);
            $table->string('Description', 255)->nullable();
            $table->unique(['Request_Type', 'Level_Number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_level');
    }
}
