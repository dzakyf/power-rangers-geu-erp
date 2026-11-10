<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_logs')) {
            return;
        }

        Schema::create('user_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('id', true);
            $table->string('username', 150);
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('ip_address', 50);
            $table->string('method', 50);
            $table->string('url', 255);
            $table->string('user_agent', 255);
            $table->text('payload')->nullable();
            $table->dateTime('created_date')->useCurrent();
        });
    }

    public function down()
    {
        // Intentionally empty (guarded/additive; never drops live tables).
    }
}
