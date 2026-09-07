<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseToInstockDregsTable extends Migration
{
    public function up()
    {
        Schema::table('instock_dregs', function (Blueprint $table) {
            $table->unsignedBigInteger('DO_Id')->nullable()->after('Instock_Date');
            $table->unsignedInteger('Warehouse_ID')->nullable()->after('DO_Id');
            $table->index('DO_Id');
            $table->index('Warehouse_ID');
        });
    }

    public function down()
    {
        Schema::table('instock_dregs', function (Blueprint $table) {
            $table->dropIndex(['DO_Id']);
            $table->dropIndex(['Warehouse_ID']);
            $table->dropColumn(['DO_Id', 'Warehouse_ID']);
        });
    }
}
