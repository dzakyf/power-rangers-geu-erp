<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnsProductCodeTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('product_code')) {
            return; // already provisioned on the live database
        }

        Schema::create('product_code', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Main_Category', 100);
            $table->string('Category', 120)->nullable();
            $table->string('Supplies_Type', 40)->nullable();
            $table->string('Product_Code', 60)->nullable();
            $table->mediumText('Description')->nullable();
            $table->mediumText('Specification')->nullable();
            $table->string('Product_Brand', 40);
            $table->string('Product_Type', 40);
            $table->string('Unit_Name', 30)->nullable();
            $table->double('UnitPrice', 30, 0)->nullable();
            $table->string('Foto', 40)->nullable();
            $table->string('Created_By', 20)->nullable();
            $table->timestamp('Created_Date')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->double('Qty_Stock', 30, 2)->nullable();
            $table->integer('Dimension')->nullable()->default(1);
            $table->string('Location', 20)->nullable();
            $table->boolean('Purchase_Status')->nullable()->default(0);
            $table->string('Price', 30)->nullable();
            $table->string('status_Muncul', 20)->nullable();
            $table->string('Status', 30)->nullable()->default('NORMAL');
            $table->string('Supplier_ID', 40)->nullable();

        });
    }

    public function down()
    {
        // Intentionally left empty (additive/guarded; never drops live tables).
    }
}
