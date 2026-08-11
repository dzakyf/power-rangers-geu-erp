<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationStructureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_structure', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('NIK', 50)->index();
            $table->string('Name', 150);
            $table->tinyInteger('Level1')->default(0);
            $table->tinyInteger('Level2')->default(0);
            $table->tinyInteger('Level3')->default(0);
            $table->tinyInteger('Level4')->default(0);
            $table->tinyInteger('Level5')->default(0);
            $table->tinyInteger('Level6')->default(0);
            $table->tinyInteger('Level7')->default(0);
            $table->tinyInteger('Level8')->default(0);
            $table->string('Department', 100)->index();
            $table->string('Dept_Code', 50)->nullable();
            $table->string('Email', 150)->nullable();
            $table->tinyInteger('Alerted')->default(0);
            $table->string('divisi', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_structure');
    }
}
