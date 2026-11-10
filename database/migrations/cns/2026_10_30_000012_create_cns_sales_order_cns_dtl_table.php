<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsSalesOrderCnsDtlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sales_order_cns_dtl')) {
            return; // already provisioned on the live database
        }

        Schema::create('sales_order_cns_dtl', function (Blueprint $table) {
            $table->increments('id');
            $table->string('so_no', 150);
            $table->integer('sort_order')->nullable()->default(0);
            $table->string('product_code', 150)->nullable();
            $table->string('description', 255);
            $table->double('qty', 30, 2)->nullable()->default(0.00);
            $table->double('unit_price', 30, 2)->nullable()->default(0.00);
            $table->double('total', 30, 2)->nullable()->default(0.00);
            $table->string('created_by', 150);
            $table->dateTime('created_date')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
