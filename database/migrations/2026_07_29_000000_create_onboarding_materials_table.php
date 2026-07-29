<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnboardingMaterialsTable extends Migration
{
    public function up()
    {
        Schema::create('onboarding_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Title');
            $table->text('Description')->nullable();
            $table->string('Category')->nullable();
            $table->unsignedBigInteger('Meta_File_ID');
            $table->string('Created_By')->nullable();
            $table->string('Updated_By')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('onboarding_materials');
    }
}
