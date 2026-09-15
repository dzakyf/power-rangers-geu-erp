<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClosedAtToWarehouseStockOpnames extends Migration
{
    public function up()
    {
        Schema::table('warehouse_stock_opnames', function (Blueprint $table) {
            $table->timestamp('Closed_At')->nullable()->after('Updated_At');
        });
    }

    public function down()
    {
        Schema::table('warehouse_stock_opnames', function (Blueprint $table) {
            $table->dropColumn('Closed_At');
        });
    }
}
