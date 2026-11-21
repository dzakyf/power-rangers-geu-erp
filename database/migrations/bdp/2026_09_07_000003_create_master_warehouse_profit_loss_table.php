<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterWarehouseProfitLossTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('master_warehouse_profit_loss')) {
            return;
        }

        Schema::create('master_warehouse_profit_loss', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('type', 50)->nullable()->index();
            $table->string('Judul', 50)->nullable();
            $table->string('Description', 255)->nullable();
            $table->string('Code', 50)->nullable();
            $table->string('Rumus', 100)->nullable();
            $table->string('Normal', 20)->nullable();
            $table->integer('Urut')->nullable();
            $table->string('Unit_Name', 50)->nullable();
            $table->integer('Level')->nullable();
            $table->integer('Indentation')->nullable();
            $table->string('Bold', 10)->nullable();
            $table->index(['type', 'Urut']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_warehouse_profit_loss');
    }
}
