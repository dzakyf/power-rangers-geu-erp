<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersStatisticsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users_statistics')) {
            return;
        }

        Schema::create('users_statistics', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->dateTime('Created_At')->useCurrent();
            $table->dateTime('Updated_At')->nullable();
            $table->enum('Current_Tier', ['GOLD', 'BRONZE', 'SILVER'])->nullable();
            $table->string('User_Id', 100)->nullable();
            $table->bigInteger('Current_Achievement_Streak')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_statistics');
    }
}
