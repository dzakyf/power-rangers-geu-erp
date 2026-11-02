<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaStockAdjustmentTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ga_stock_adjustment')) {
            Schema::create('ga_stock_adjustment', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('Adjustment_No', 60)->unique();
                $table->date('Adjustment_Date')->index();
                $table->string('Product_Code', 60)->index();
                $table->string('Location', 50)->default('');
                $table->string('WH_ID', 50)->default('');
                $table->string('Type', 20); // IN, OUT
                $table->decimal('Qty', 18, 4);
                $table->decimal('System_Qty', 18, 4)->default(0);
                $table->decimal('Physical_Qty', 18, 4)->default(0);
                $table->decimal('Unit_Cost', 18, 4)->default(0); // Biaya satuan WAC saat adjustment
                $table->decimal('Total_Value', 18, 4)->default(0); // Nilai selisih (Qty * Unit_Cost)
                $table->string('Reason', 500)->nullable();
                $table->string('Status', 30)->default('COMPLETED')->index(); // COMPLETED, REJECTED, WAITING_APPROVAL
                $table->string('Department', 100)->nullable();
                $table->string('Created_By', 100)->nullable();
                $table->timestamp('Created_At')->nullable();
                $table->string('Updated_By', 100)->nullable();
                $table->timestamp('Updated_At')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ga_stock_adjustment');
    }
}
