<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutstockWarehousesTable extends Migration
{
    public function up()
    {
        Schema::create('outstock_warehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('Outstock_ID');
            $table->unsignedBigInteger('Warehouse_ID');
        });
    }

    public function down()
    {
        Schema::dropIfExists('outstock_warehouses');
    }
}
