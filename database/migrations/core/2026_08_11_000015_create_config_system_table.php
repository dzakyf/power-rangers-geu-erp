<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigSystemTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('config_system')) {
            return;
        }

        Schema::create('config_system', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Constraint', 100)->unique();
            $table->string('Value', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('config_system');
    }
}
