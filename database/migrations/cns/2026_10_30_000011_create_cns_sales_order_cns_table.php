<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsSalesOrderCnsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sales_order_cns')) {
            return; // already provisioned on the live database
        }

        Schema::create('sales_order_cns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('so_no', 150);
            $table->date('so_date');
            $table->string('buyer_pic', 255);
            $table->string('buyer_city', 255);
            $table->string('buyer_province', 255);
            $table->string('buyer_phone', 30);
            $table->string('site', 255);
            $table->string('site_address', 255);
            $table->string('payment_term', 255);
            $table->double('subtotal', 30, 2);
            $table->double('tax', 30, 2);
            $table->double('shipping', 30, 2);
            $table->double('other', 30, 2);
            $table->double('total', 30, 2)->default(0.00);
            $table->string('remarks', 255)->nullable();
            $table->string('created_by', 150);
            $table->dateTime('created_date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
