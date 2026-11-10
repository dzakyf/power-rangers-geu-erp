<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomChatTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('room_chat')) {
            return;
        }

        Schema::create('room_chat', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('ID', true);
            $table->text('Users');
            $table->integer('User_ID_Left');
            $table->integer('User_ID_Right');
            $table->string('Created_By', 150);
            $table->dateTime('Created_Date');
        });
    }

    public function down()
    {
        // Intentionally empty (guarded/additive; never drops live tables).
    }
}
