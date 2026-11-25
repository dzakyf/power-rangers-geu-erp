<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterBankTable extends Migration
{
    public function up()
    {
        Schema::create('master_bank', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Bank_Code')->unique();
            $table->string('Bank_Name');
            $table->string('Participant_Type')->nullable();
            $table->timestamp('Created_At')->nullable();
            $table->boolean('Is_Active')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_bank');
    }
}
