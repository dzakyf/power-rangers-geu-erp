<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsPurchaseRequestCnsDtlTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_request_cns_dtl')) {
            return; // already provisioned on the live database
        }

        Schema::create('purchase_request_cns_dtl', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('PR_No', 50)->nullable();
            $table->string('Purchase_Category', 50)->default('CONSUMABLE');
            $table->string('Purchase_Type', 40)->nullable();
            $table->string('Category', 20)->nullable();
            $table->string('Product_Code', 50)->nullable();
            $table->text('Product_Desc')->nullable();
            $table->mediumText('Specification')->nullable();
            $table->mediumText('Recommended_Spec')->nullable();
            $table->integer('Qty')->nullable();
            $table->double('Default_Price', 40, 0)->nullable()->default(0);
            $table->string('Unit', 255)->nullable();
            $table->mediumText('Remark')->nullable();
            $table->string('Location', 30)->nullable();
            $table->string('PO_Task_No', 50)->nullable();
            $table->dateTime('PO_Task_Date')->nullable();
            $table->integer('PSD_ID')->nullable();
            $table->index(['ID'], 'ID');
        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
