<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('chat')) {
            return;
        }

        Schema::create('chat', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('id', true);
            $table->integer('room_chat_id');
            $table->text('message');
            $table->text('attachment');
            $table->string('created_by', 150);
            $table->dateTime('created_date')->useCurrent();
            $table->integer('is_sent')->default(0);
            $table->dateTime('sent_date')->nullable();
            $table->integer('is_read')->default(0);
            $table->dateTime('read_date')->nullable();
            $table->integer('announcement_id')->nullable();
        });
    }

    public function down()
    {
        // Intentionally empty (guarded/additive; never drops live tables).
    }
}
