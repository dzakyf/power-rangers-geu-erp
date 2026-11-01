<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuListsTable extends Migration
{
    public function up()
    {
        Schema::create('menu_lists', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Menu_ID', 60);
            $table->string('Name', 100);
            $table->string('Path_Loc', 200)->default('#');
            $table->integer('Position');
            $table->string('Parent_Menu', 160)->default('');
            $table->integer('Urutan');
            $table->string('Modules', 100);

            $table->index(['Modules', 'Position']);
            $table->index('Parent_Menu');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_lists');
    }
}
