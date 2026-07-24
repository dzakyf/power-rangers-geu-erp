<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToMasterStockPriceDateFor extends Migration
{
    public function up()
    {
        DB::statement('
            DELETE t1 FROM master_stock_price t1
            INNER JOIN master_stock_price t2
            WHERE t1.id < t2.id AND t1.date_for = t2.date_for
        ');

        Schema::table('master_stock_price', function (Blueprint $table) {
            $table->unique('date_for');
        });
    }

    public function down()
    {
        Schema::table('master_stock_price', function (Blueprint $table) {
            $table->dropUnique(['date_for']);
        });
    }
}
