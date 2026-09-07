<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementTable extends Migration
{
    public function up()
    {
        Schema::create('announcement', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
            $table->increments('ID');
            $table->string('Title', 255);
            $table->text('Content');
            $table->TinyInteger('Is_Final')->default(0);
            $table->dateTime('Final_Date')->nullable();
            $table->dateTime('Created_Date')->useCurrent();
            $table->string('Created_By', 150);
        });
        DB::statement("ALTER TABLE `announcement` MODIFY `ID` int NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        Schema::dropIfExists('announcement');
    }
}
