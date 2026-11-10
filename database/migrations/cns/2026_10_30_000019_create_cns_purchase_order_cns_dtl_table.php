<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseOrderCnsDtlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_order_cns_dtl')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_order_cns_dtl', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Location', 30)->nullable();
            $table->string('PR_No', 30)->nullable();
            $table->string('PO_No', 50)->nullable();
            $table->string('Category', 30)->nullable();
            $table->string('Sub_Category', 50)->nullable();
            $table->string('Purchase_Type', 50)->nullable();
            $table->string('Product_Code', 40)->nullable();
            $table->string('Barcode', 50)->nullable();
            $table->mediumText('Product_Desc')->nullable();
            $table->mediumText('Specification')->nullable();
            $table->string('Brand', 30)->nullable();
            $table->string('Product_Type', 20)->nullable();
            $table->decimal('Qty', 10, 2)->nullable();
            $table->string('Unit', 100)->nullable();
            $table->double('Price', 25, 2)->default(0.00);
            $table->double('Discount', 10, 2)->default(0.00);
            $table->double('Subtotal', 25, 2)->default(0.00);
            $table->double('PPN', 25, 2)->default(0.00);
            $table->double('PPN_pct', 5, 2)->default(0.00);
            $table->double('Total', 25, 2)->nullable();
            $table->mediumText('Remark')->nullable();
            $table->string('GR_No', 50)->nullable();
            $table->double('Qty_Receive', 30, 0)->nullable();
            $table->double('Qty_Remain', 30, 0)->nullable();
            $table->string('Currency', 6)->default('RP');
            $table->string('Old_Product_Code', 40)->nullable();
            $table->string('SN', 40)->nullable();
            $table->index(['ID'], 'ID');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
