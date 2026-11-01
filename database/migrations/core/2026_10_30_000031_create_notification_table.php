<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notification')) {
            return;
        }

        Schema::create('notification', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigInteger('ID', true);
            $table->string('Title', 100)->nullable();
            $table->text('Content')->nullable();
            $table->dateTime('Expires_At')->nullable();
            $table->dateTime('Created_At')->useCurrent();
            $table->dateTime('Read_At')->nullable();
            $table->string('Topic', 100)->nullable();
            $table->string('Sender_Id', 100)->nullable();
            $table->string('Recipient_Id', 100)->nullable();
            $table->json('Metadata')->nullable();
            $table->enum('Status', ['READ', 'SENT'])->nullable();
        });
    }

    public function down()
    {
        // Intentionally empty (guarded/additive; never drops live tables).
    }
}
